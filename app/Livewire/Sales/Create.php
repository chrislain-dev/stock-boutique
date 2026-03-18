<?php

namespace App\Livewire\Sales;

use App\Enums\PaymentMethod;
use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Enums\StockMovementType;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Reseller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Mary\Traits\Toast;

class Create extends Component
{
    use Toast;

    public int $step = 1;

    // ─── Étape 1 : Client ─────────────────────────────────────
    public string $customer_type  = 'client';
    public ?int   $reseller_id    = null;
    public string $customer_name  = '';
    public string $customer_phone = '';

    // ─── Étape 2 : Produits ───────────────────────────────────
    public array  $items          = [];
    public string $search_imei    = '';
    public ?int   $search_product_id = null;
    public string $search_error   = '';

    // ─── Étape 3 : Paiement ───────────────────────────────────
    public string $payment_method      = 'cash';
    public string $paid_amount         = '0';
    public string $transaction_reference = '';
    public string $mobile_number       = '';
    public string $bank_name           = '';
    public string $due_date            = '';
    public bool   $is_trade_in         = false;
    public string $trade_in_imei       = '';
    public ?int   $trade_in_product_id = null;
    public string $trade_in_value      = '0';
    public string $trade_in_notes      = '';
    public string $notes               = '';

    // ─── Navigation ───────────────────────────────────────────
    public function nextStep(): void
    {
        if ($this->step === 1) $this->validateStep1();
        if ($this->step === 2) {
            if (empty($this->items)) {
                $this->error('Ajoutez au moins un produit.');
                return;
            }
            // Initialiser paid_amount au total
            $this->paid_amount = (string) $this->getTotal();
        }
        $this->step++;
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    private function validateStep1(): void
    {
        if ($this->customer_type === 'reseller') {
            $this->validate(['reseller_id' => 'required|exists:resellers,id']);
        }
    }

    // ─── Recherche produit par IMEI ───────────────────────────
    public function searchByImei(): void
    {
        $this->search_error = '';

        if (empty($this->search_imei)) return;

        $product = Product::with('productModel.brand')
            ->where(function ($q) {
                $q->where('imei', $this->search_imei)
                    ->orWhere('serial_number', $this->search_imei);
            })
            ->where('state', ProductState::AVAILABLE->value)
            ->first();

        if (!$product) {
            $this->search_error = "Aucun produit disponible avec cet identifiant.";
            return;
        }

        $this->addProductToCart($product);
        $this->search_imei = '';
    }

    public function updatedSearchProductId(): void
    {
        if (!$this->search_product_id) return;

        $product = Product::with('productModel.brand')
            ->where('id', $this->search_product_id)
            ->where('state', ProductState::AVAILABLE->value)
            ->first();

        if ($product) {
            $this->addProductToCart($product);
        }

        $this->search_product_id = null;
    }

    private function addProductToCart(Product $product): void
    {
        // Vérifier doublon
        foreach ($this->items as $item) {
            if (isset($item['product_id']) && $item['product_id'] === $product->id) {
                $this->search_error = "Ce produit est déjà dans la vente.";
                return;
            }
        }

        $isReseller = $this->customer_type === 'reseller';
        $unitPrice  = $isReseller
            ? (float) $product->reseller_price
            : (float) $product->client_price;

        $this->items[] = [
            'product_id'               => $product->id,
            'product_model_id'         => $product->product_model_id,
            'name'                     => $product->productModel->display_label,
            'identifier'               => $product->imei ?? $product->serial_number ?? '—',
            'unit_price'               => $unitPrice,
            'purchase_price_snapshot'  => (float) $product->purchase_price,
            'discount'                 => 0,
            'quantity'                 => 1,
            'line_total'               => $unitPrice,
            'is_serialized'            => true,
        ];
    }

    public function updateItemPrice(int $index, string $field, string $value): void
    {
        if (!isset($this->items[$index])) return;

        $this->items[$index][$field] = (float) $value;
        $this->recalcLine($index);
    }

    private function recalcLine(int $index): void
    {
        $item = $this->items[$index];
        $this->items[$index]['line_total'] =
            ($item['unit_price'] - $item['discount']) * $item['quantity'];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
    }

    public function getTotal(): float
    {
        $itemsTotal = collect($this->items)->sum('line_total');
        $tradeIn    = $this->is_trade_in ? (float) $this->trade_in_value : 0;
        return max(0, $itemsTotal - $tradeIn);
    }

    // ─── Recherche produit trade-in par IMEI ─────────────────
    public function searchTradeIn(): void
    {
        if (empty($this->trade_in_imei)) return;

        $product = Product::where(function ($q) {
            $q->where('imei', $this->trade_in_imei)
                ->orWhere('serial_number', $this->trade_in_imei);
        })->first();

        if ($product) {
            $this->trade_in_product_id = $product->id;
            $this->trade_in_value      = (string) $product->purchase_price;
        } else {
            $this->addError('trade_in_imei', 'Produit introuvable.');
        }
    }

    // ─── Sauvegarde ───────────────────────────────────────────
    public function save(): void
    {
        if (empty($this->items)) {
            $this->error('Aucun produit dans la vente.');
            return;
        }

        // Prepare data for validation
        $data = [
            'client_name'    => $this->customer_name,
            'client_phone'   => $this->customer_phone,
            'reseller_id'    => $this->reseller_id,
            'sale_items'     => array_map(fn($item) => [
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ], $this->items),
            'paid_amount'    => $this->paid_amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->computePaymentStatus(),
            'trade_in_product_id' => $this->trade_in_product_id,
            'notes'          => $this->notes,
        ];

        // Validate using FormRequest
        $validator = Validator::make($data, (new StoreSaleRequest())->rules(), (new StoreSaleRequest())->messages());
        $validator->validate();

        $saleId = DB::transaction(function () {
            $total     = collect($this->items)->sum('line_total');
            $tradeIn   = $this->is_trade_in ? (float) $this->trade_in_value : 0;
            $netTotal  = max(0, $total - $tradeIn);
            $paidAmount = (float) $this->paid_amount + $tradeIn;

            $sale = Sale::create([
                'customer_type'       => $this->customer_type,
                'reseller_id'         => $this->customer_type === 'reseller' ? $this->reseller_id : null,
                'customer_name'       => $this->customer_name ?: null,
                'customer_phone'      => $this->customer_phone ?: null,
                'total_amount'        => $netTotal,
                'paid_amount'         => min($paidAmount, $netTotal),
                'sale_status'         => 'completed',
                'is_trade_in'         => $this->is_trade_in,
                'trade_in_product_id' => $this->trade_in_product_id,
                'trade_in_value'      => $tradeIn ?: null,
                'trade_in_notes'      => $this->trade_in_notes ?: null,
                'due_date'            => $this->due_date ?: null,
                'notes'               => $this->notes ?: null,
                'created_by'          => Auth::id(),
            ]);

            foreach ($this->items as $item) {
                SaleItem::create([
                    'sale_id'                  => $sale->id,
                    'product_model_id'         => $item['product_model_id'],
                    'product_id'               => $item['product_id'],
                    'quantity'                 => $item['quantity'],
                    'unit_price'               => $item['unit_price'],
                    'purchase_price_snapshot'  => $item['purchase_price_snapshot'],
                    'discount'                 => $item['discount'],
                    'line_total'               => $item['line_total'],
                ]);

                // Marquer produit comme vendu
                $product = Product::find($item['product_id']);
                $product->update([
                    'state'      => ProductState::SOLD->value,
                    'updated_by' => Auth::id(),
                ]);

                // Mouvement de stock
                StockMovement::create([
                    'product_model_id' => $item['product_model_id'],
                    'product_id'       => $item['product_id'],
                    'type'             => StockMovementType::SALE_OUT->value,
                    'quantity'         => 1,
                    'quantity_before'  => 1,
                    'quantity_after'   => 0,
                    'location_from'    => ProductLocation::STORE->value,
                    'location_to'      => ProductLocation::CLIENT->value,
                    'notes'            => "Vente {$sale->reference}",
                    'created_by'       => Auth::id(),
                ]);
            }

            // Enregistrer le paiement cash/mobile
            if ((float) $this->paid_amount > 0) {
                Payment::create([
                    'sale_id'               => $sale->id,
                    'amount'                => (float) $this->paid_amount,
                    'payment_method'        => $this->payment_method,
                    'transaction_reference' => $this->transaction_reference ?: null,
                    'mobile_number'         => $this->mobile_number ?: null,
                    'bank_name'             => $this->bank_name ?: null,
                    'payment_date'          => now()->toDateString(),
                    'notes'                 => $this->notes ?: null,
                    'created_by'            => Auth::id(),
                ]);
            }

            // Enregistrer le troc comme paiement
            if ($this->is_trade_in && $tradeIn > 0) {
                Payment::create([
                    'sale_id'        => $sale->id,
                    'amount'         => $tradeIn,
                    'payment_method' => PaymentMethod::TRADE_IN->value,
                    'payment_date'   => now()->toDateString(),
                    'notes'          => $this->trade_in_notes ?: 'Troc',
                    'created_by'     => Auth::id(),
                ]);

                // Marquer le produit trocqué comme retourné
                if ($this->trade_in_product_id) {
                    Product::find($this->trade_in_product_id)?->update([
                        'state'      => ProductState::RETURNED->value,
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            return $sale->id;
        });

        $this->success('Vente enregistrée !');
        $this->redirect(route('sales.show', $saleId), navigate: true);
    }

    private function computePaymentStatus(): string
    {
        $total  = $this->getTotal();
        $paid   = (float) $this->paid_amount;

        if ($paid >= $total) return 'paid';
        if ($paid > 0)       return 'partial';
        return 'unpaid';
    }

    public function render()
    {
        $resellers = Reseller::orderBy('name')
            ->get()
            ->map(fn($r) => ['id' => $r->id, 'name' => $r->name])
            ->toArray();

        $availableProducts = Product::with('productModel.brand')
            ->where('state', ProductState::AVAILABLE->value)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($p) => [
                'id'   => $p->id,
                'name' => $p->productModel->display_label . ' — ' . ($p->imei ?? $p->serial_number ?? 'Sans ID'),
            ])
            ->toArray();

        $paymentMethods = collect(PaymentMethod::cases())
            ->filter(fn($m) => $m !== PaymentMethod::TRADE_IN)
            ->map(fn($m) => ['id' => $m->value, 'name' => $m->label()])
            ->values()
            ->toArray();

        return view('livewire.sales.create', compact(
            'resellers',
            'availableProducts',
            'paymentMethods'
        ))->layout('layouts.app', ['title' => 'Nouvelle vente']);
    }
}
