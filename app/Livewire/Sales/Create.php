<?php

namespace App\Livewire\Sales;

use App\Enums\PaymentMethod;
use App\Enums\ProductCondition;
use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Enums\StockMovementType;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductModel;
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
    public array  $items             = [];
    public string $search_imei       = '';
    public ?int   $search_product_id = null;
    public string $search_error      = '';

    // ─── Étape 3 : Paiement ───────────────────────────────────
    public string $payment_method        = 'cash';
    public string $paid_amount           = '0';
    public string $transaction_reference = '';
    public string $mobile_number         = '';
    public string $bank_name             = '';
    public string $due_date              = '';
    public string $notes                 = '';

    // ─── Troc ─────────────────────────────────────────────────
    public bool   $is_trade_in               = false;
    public ?int   $trade_in_product_model_id = null;
    public string $trade_in_imei             = '';
    public ?int   $trade_in_product_id       = null;
    public string $trade_in_value            = '0';
    public string $trade_in_color            = '';
    public string $trade_in_storage          = '';
    public string $trade_in_battery          = '';
    public string $trade_in_notes            = '';
    public string $trade_in_stock_info       = '';

    // ─── Changement type client ───────────────────────────────
    public function updatedCustomerType(): void
    {
        $hadItems = !empty($this->items);

        $this->items             = [];
        $this->reseller_id       = null;
        $this->customer_name     = '';
        $this->customer_phone    = '';
        $this->search_imei       = '';
        $this->search_product_id = null;
        $this->search_error      = '';
        $this->resetErrorBag();

        if ($hadItems) {
            $this->warning('Le panier a été vidé suite au changement de type client.');
        }
    }

    // ─── Navigation ───────────────────────────────────────────
    public function nextStep(): void
    {
        if ($this->step === 1) $this->validateStep1();
        if ($this->step === 2) {
            if (empty($this->items)) {
                $this->error('Ajoutez au moins un produit.');
                return;
            }
            $this->paid_amount = (string) $this->getNetTotal();
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

    // ─── Totaux ───────────────────────────────────────────────
    public function getGrossTotal(): float
    {
        return collect($this->items)->sum('line_total');
    }

    public function getTradeInValue(): float
    {
        return $this->is_trade_in ? max(0, (float) $this->trade_in_value) : 0;
    }

    public function getNetTotal(): float
    {
        return max(0, $this->getGrossTotal() - $this->getTradeInValue());
    }

    // Alias pour la vue
    public function getTotal(): float
    {
        return $this->getNetTotal();
    }

    // ─── Recherche produit (panier) ───────────────────────────
    public function searchByImei(): void
    {
        $this->search_error = '';
        if (empty($this->search_imei)) return;

        $product = Product::with('productModel.brand')
            ->where(
                fn($q) => $q
                    ->where('imei', $this->search_imei)
                    ->orWhere('serial_number', $this->search_imei)
            )
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

        if ($product) $this->addProductToCart($product);
        $this->search_product_id = null;
    }

    private function addProductToCart(Product $product): void
    {
        foreach ($this->items as $item) {
            if (isset($item['product_id']) && $item['product_id'] === $product->id) {
                $this->search_error = "Ce produit est déjà dans le panier.";
                return;
            }
        }

        $isReseller = $this->customer_type === 'reseller';
        $unitPrice  = $isReseller
            ? (float) $product->reseller_price
            : (float) $product->client_price;

        $this->items[] = [
            'product_id'              => $product->id,
            'product_model_id'        => $product->product_model_id,
            'name'                    => $product->productModel->display_label,
            'identifier'              => $product->imei ?? $product->serial_number ?? '—',
            'unit_price'              => $unitPrice,
            'purchase_price_snapshot' => (float) $product->purchase_price,
            'discount'                => 0,
            'quantity'                => 1,
            'line_total'              => $unitPrice,
            'is_serialized'           => true,
        ];

        $this->search_error = '';
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

    // ─── Troc : pré-remplissage optionnel depuis le stock ─────
    public function prefillTradeIn(): void
    {
        $this->trade_in_stock_info = '';
        $this->resetErrorBag('trade_in_imei');

        if (empty($this->trade_in_imei)) return;

        $product = Product::with('productModel')
            ->where(
                fn($q) => $q
                    ->where('imei', $this->trade_in_imei)
                    ->orWhere('serial_number', $this->trade_in_imei)
            )
            ->first();

        if ($product) {
            $this->trade_in_product_id       = $product->id;
            $this->trade_in_product_model_id = $product->product_model_id;
            $this->trade_in_value            = (string) $product->purchase_price;
            $this->trade_in_stock_info       = "✓ {$product->productModel->display_label} trouvé dans le stock — valeur pré-remplie.";
        } else {
            $this->trade_in_product_id = null;
            $this->trade_in_stock_info = "Non trouvé dans le stock. Sélectionnez le modèle et saisissez la valeur manuellement.";
        }
    }

    // ─── Sauvegarde ───────────────────────────────────────────
    public function save(): void
    {
        if (empty($this->items)) {
            $this->error('Aucun produit dans la vente.');
            return;
        }

        if ($this->is_trade_in) {
            $this->validate([
                'trade_in_product_model_id' => 'required|exists:product_models,id',
                'trade_in_value'            => 'required|numeric|min:1',
            ], [
                'trade_in_product_model_id.required' => 'Sélectionnez le modèle de l\'appareil repris.',
                'trade_in_value.required'            => 'La valeur de reprise est obligatoire.',
                'trade_in_value.min'                 => 'La valeur de reprise doit être supérieure à 0.',
            ]);
        }

        $grossTotal = $this->getGrossTotal();
        $tradeIn    = $this->getTradeInValue();
        $netTotal   = $this->getNetTotal();
        $cashPaid   = min((float) $this->paid_amount, $netTotal);

        $data = [
            'client_name'         => $this->customer_name,
            'client_phone'        => $this->customer_phone,
            'reseller_id'         => $this->reseller_id,
            'sale_items'          => array_map(fn($item) => [
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ], $this->items),
            'paid_amount'         => $cashPaid,
            'payment_method'      => $this->payment_method,
            'payment_status'      => $this->computePaymentStatus($netTotal, $cashPaid),
            'trade_in_product_id' => $this->trade_in_product_id,
            'notes'               => $this->notes,
        ];

        $validator = Validator::make(
            $data,
            (new StoreSaleRequest())->rules(),
            (new StoreSaleRequest())->messages()
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }
            return;
        }

        $saleId = DB::transaction(function () use ($tradeIn, $netTotal, $cashPaid) {

            $sale = Sale::create([
                'customer_type'       => $this->customer_type,
                'reseller_id'         => $this->customer_type === 'reseller' ? $this->reseller_id : null,
                'customer_name'       => $this->customer_name ?: null,
                'customer_phone'      => $this->customer_phone ?: null,
                'total_amount'        => $netTotal,
                'paid_amount'         => $cashPaid,
                'sale_status'         => 'completed',
                'is_trade_in'         => $this->is_trade_in,
                'trade_in_product_id' => $this->trade_in_product_id,
                'trade_in_value'      => $tradeIn ?: null,
                'trade_in_notes'      => $this->buildTradeInNotes(),
                'due_date'            => $this->due_date ?: null,
                'notes'               => $this->notes ?: null,
                'created_by'          => Auth::id(),
            ]);

            // ── Articles vendus ──────────────────────────────
            foreach ($this->items as $item) {
                SaleItem::create([
                    'sale_id'                 => $sale->id,
                    'product_model_id'        => $item['product_model_id'],
                    'product_id'              => $item['product_id'],
                    'quantity'                => $item['quantity'],
                    'unit_price'              => $item['unit_price'],
                    'purchase_price_snapshot' => $item['purchase_price_snapshot'],
                    'discount'                => $item['discount'],
                    'line_total'              => $item['line_total'],
                ]);

                Product::find($item['product_id'])?->update([
                    'state'      => ProductState::SOLD->value,
                    'updated_by' => Auth::id(),
                ]);

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

            // ── Paiement cash uniquement (jamais le troc) ────
            if ($cashPaid > 0) {
                Payment::create([
                    'sale_id'               => $sale->id,
                    'amount'                => $cashPaid,
                    'payment_method'        => $this->payment_method,
                    'transaction_reference' => $this->transaction_reference ?: null,
                    'mobile_number'         => $this->mobile_number ?: null,
                    'bank_name'             => $this->bank_name ?: null,
                    'payment_date'          => now()->toDateString(),
                    'notes'                 => $this->notes ?: null,
                    'created_by'            => Auth::id(),
                ]);
            }

            // ── Troc : créer ou mettre à jour le produit repris
            if ($this->is_trade_in) {
                $tradeInProductId = $this->trade_in_product_id;

                if ($tradeInProductId) {
                    // Produit existant dans le stock → déplacer en reprise
                    Product::find($tradeInProductId)?->update([
                        'state'          => ProductState::AVAILABLE->value,
                        'location'       => ProductLocation::REPRISE->value,
                        'condition'      => ProductCondition::USED->value,
                        'purchase_price' => (float) $this->trade_in_value,
                        'notes'          => $this->buildTradeInNotes(),
                        'updated_by'     => Auth::id(),
                    ]);
                } else {
                    // Produit extérieur → créer une nouvelle fiche dans les reprises
                    $newProduct = Product::create([
                        'product_model_id' => $this->trade_in_product_model_id,
                        'imei'             => $this->trade_in_imei ?: null,
                        'serial_number'    => null,
                        'state'            => ProductState::AVAILABLE->value,
                        'location'         => ProductLocation::REPRISE->value,
                        'condition'        => ProductCondition::USED->value,
                        'purchase_price'   => (float) $this->trade_in_value,
                        'client_price'     => (float) $this->trade_in_value,
                        'reseller_price'   => (float) $this->trade_in_value,
                        'purchase_date'    => now()->toDateString(),
                        'notes'            => $this->buildTradeInNotes(),
                        'created_by'       => Auth::id(),
                        'updated_by'       => Auth::id(),
                    ]);

                    $tradeInProductId = $newProduct->id;
                    $sale->update(['trade_in_product_id' => $tradeInProductId]);
                }

                // Mouvement de stock : entrée en reprise
                StockMovement::create([
                    'product_model_id' => $this->trade_in_product_model_id,
                    'product_id'       => $tradeInProductId,
                    'type'             => StockMovementType::TRADE_IN->value,
                    'quantity'         => 1,
                    'quantity_before'  => 0,
                    'quantity_after'   => 1,
                    'location_from'    => ProductLocation::CLIENT->value,
                    'location_to'      => ProductLocation::REPRISE->value,
                    'notes'            => "Troc — Vente {$sale->reference}",
                    'created_by'       => Auth::id(),
                ]);
            }

            return $sale->id;
        });

        $this->success('Vente enregistrée avec succès !');
        $this->redirect(route('sales.show', $saleId), navigate: true);
    }

    private function buildTradeInNotes(): ?string
    {
        $parts = [];
        if ($this->trade_in_color)   $parts[] = "Couleur : {$this->trade_in_color}";
        if ($this->trade_in_storage) $parts[] = "Stockage : {$this->trade_in_storage}";
        if ($this->trade_in_battery) $parts[] = "Batterie : {$this->trade_in_battery}%";
        if ($this->trade_in_notes)   $parts[] = $this->trade_in_notes;
        return empty($parts) ? null : implode(' — ', $parts);
    }

    private function computePaymentStatus(float $netTotal, float $cashPaid): string
    {
        if ($netTotal <= 0)         return 'paid';
        if ($cashPaid >= $netTotal) return 'paid';
        if ($cashPaid > 0)          return 'partial';
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
                'name' => $p->productModel->display_label
                    . ' — '
                    . ($p->imei ?? $p->serial_number ?? 'Sans ID'),
            ])
            ->toArray();

        $paymentMethods = collect(PaymentMethod::cases())
            ->filter(fn($m) => $m !== PaymentMethod::TRADE_IN)
            ->map(fn($m) => ['id' => $m->value, 'name' => $m->label()])
            ->values()
            ->toArray();

        $tradeInModels = ProductModel::with('brand')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($m) => ['id' => $m->id, 'name' => $m->display_label])
            ->toArray();

        return view('livewire.sales.create', compact(
            'resellers',
            'availableProducts',
            'paymentMethods',
            'tradeInModels',
        ))->layout('layouts.app', ['title' => 'Nouvelle vente']);
    }
}
