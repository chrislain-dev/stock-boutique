<?php

namespace App\Livewire\Sales;

use App\Enums\PaymentMethod;
use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Enums\StockMovementType;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Mary\Traits\Toast;

class Show extends Component
{
    use Toast;

    public Sale $sale;

    // ─── Modal paiement ───────────────────────────────────────
    public bool   $showPaymentModal = false;
    public string $pay_amount       = '';
    public string $pay_method       = 'cash';
    public string $pay_reference    = '';
    public string $pay_mobile       = '';
    public string $pay_bank         = '';
    public string $pay_notes        = '';
    public string $replacement_id   = '';

    public array $availableReplacements = [];

    // ─── Modal retour défectueux ──────────────────────────────
    public bool   $showReturnModal = false;
    public ?int   $returnProductId = null;
    public string $return_reason   = '';
    public string $return_notes    = '';

    // ─── Modal suppression ────────────────────────────────────
    public bool   $showDeleteModal       = false;
    public string $delete_password       = '';
    public string $delete_reason         = '';
    public bool   $delete_password_error = false;

    public function mount(Sale $sale): void
    {
        $this->sale = $sale->load([
            'reseller',
            'createdBy',
            'items.productModel.brand',
            'items.product',
            'payments.createdBy',
            'tradeInProduct.productModel',
        ]);
    }

    // ─── Paiement ─────────────────────────────────────────────

    public function openPaymentModal(): void
    {
        $this->pay_amount       = (string) $this->sale->remaining_amount;
        $this->pay_method       = 'cash';
        $this->pay_reference    = '';
        $this->pay_mobile       = '';
        $this->pay_bank         = '';
        $this->pay_notes        = '';
        $this->showPaymentModal = true;
    }

    public function addPayment(): void
    {
        $this->validate([
            'pay_amount' => 'required|numeric|min:1|max:' . $this->sale->remaining_amount,
            'pay_method' => 'required',
        ]);

        Payment::create([
            'sale_id'               => $this->sale->id,
            'amount'                => $this->pay_amount,
            'payment_method'        => $this->pay_method,
            'transaction_reference' => $this->pay_reference ?: null,
            'mobile_number'         => $this->pay_mobile ?: null,
            'bank_name'             => $this->pay_bank ?: null,
            'payment_date'          => now()->toDateString(),
            'notes'                 => $this->pay_notes ?: null,
            'created_by'            => Auth::id(),
        ]);

        $this->reloadSale();
        $this->showPaymentModal = false;
        $this->success('Paiement enregistré.');
    }

    // ─── Reçu ─────────────────────────────────────────────────

    public function printReceipt(): void
    {
        $this->dispatch('open-receipt', url: route('sales.receipt', $this->sale->id));
    }

    // ─── Retour défectueux ────────────────────────────────────

    public function openDeclareReturn(int $productId): void
    {
        $this->returnProductId = $productId;
        $this->return_reason   = '';
        $this->return_notes    = '';
        $this->replacement_id  = '';

        $product = Product::findOrFail($productId);
        $this->availableReplacements = Product::where('product_model_id', $product->product_model_id)
            ->where('state', ProductState::AVAILABLE->value)
            ->where('id', '!=', $productId)
            ->get()
            ->map(fn($p) => [
                'id'   => $p->id,
                'name' => $p->imei ?? $p->serial_number ?? "#{$p->id}",
            ])
            ->toArray();

        $this->showReturnModal = true;
        $this->resetErrorBag();
    }

    public function declareReturn(): void
    {
        $this->validate([
            'return_reason'  => 'required|min:5',
            'replacement_id' => 'nullable|exists:products,id',
        ], [
            'return_reason.required' => 'Décrivez la défaillance constatée.',
            'return_reason.min'      => 'La description doit faire au moins 5 caractères.',
        ]);

        $defectiveProduct = Product::findOrFail($this->returnProductId);

        DB::transaction(function () use ($defectiveProduct) {

            $defectiveProduct->update([
                'state'      => ProductState::DEFECTIVE->value,
                'location'   => ProductLocation::SUPPLIER_RETURN->value,
                'updated_by' => Auth::id(),
            ]);

            ProductReturn::create([
                'product_id'             => $defectiveProduct->id,
                'replacement_product_id' => $this->replacement_id ?: null,
                'sale_id'                => $this->sale->id,
                'reason'                 => $this->return_reason,
                'notes'                  => $this->return_notes ?: null,
                'status'                 => 'pending',
                'declared_by'            => Auth::id(),
            ]);

            StockMovement::create([
                'product_model_id' => $defectiveProduct->product_model_id,
                'product_id'       => $defectiveProduct->id,
                'type'             => StockMovementType::CLIENT_RETURN->value,
                'quantity'         => 1,
                'quantity_before'  => 0,
                'quantity_after'   => 1,
                'location_from'    => ProductLocation::CLIENT->value,
                'location_to'      => ProductLocation::SUPPLIER_RETURN->value,
                'notes'            => "Retour client défectueux — {$this->return_reason}",
                'created_by'       => Auth::id(),
            ]);

            if ($this->replacement_id) {
                $replacement = Product::findOrFail($this->replacement_id);

                $replacement->update([
                    'state'      => ProductState::SOLD->value,
                    'location'   => ProductLocation::CLIENT->value,
                    'updated_by' => Auth::id(),
                ]);

                StockMovement::create([
                    'product_model_id' => $replacement->product_model_id,
                    'product_id'       => $replacement->id,
                    'type'             => StockMovementType::SALE_OUT->value,
                    'quantity'         => 1,
                    'quantity_before'  => 1,
                    'quantity_after'   => 0,
                    'location_from'    => ProductLocation::STORE->value,
                    'location_to'      => ProductLocation::CLIENT->value,
                    'notes'            => "Remplacement échange défectueux — vente {$this->sale->reference}",
                    'created_by'       => Auth::id(),
                ]);
            }
        });

        $this->reloadSale();
        $this->showReturnModal = false;
        $this->success($this->replacement_id
            ? 'Retour déclaré et remplacement effectué.'
            : 'Retour déclaré. Le produit est dans la file "Retours fournisseur".'
        );
    }

    // ─── Suppression avec confirmation mot de passe ───────────

    public function openDeleteModal(): void
    {
        abort_unless(Auth::user()->hasPermission('cancel_sale'), 403);

        $this->delete_password       = '';
        $this->delete_reason         = '';
        $this->delete_password_error = false;
        $this->showDeleteModal       = true;
        $this->resetErrorBag();
    }

    public function deleteSale(): void
    {
        abort_unless(Auth::user()->hasPermission('cancel_sale'), 403);

        // ── 1. Valider motif + présence mot de passe ──────────
        $this->validate([
            'delete_reason'   => 'required|min:10',
            'delete_password' => 'required',
        ], [
            'delete_reason.required'   => 'Un motif de suppression est obligatoire.',
            'delete_reason.min'        => 'Le motif doit faire au moins 10 caractères.',
            'delete_password.required' => 'Votre mot de passe est requis pour confirmer.',
        ]);

        // ── 2. Vérifier le mot de passe ───────────────────────
        if (!Hash::check($this->delete_password, Auth::user()->password)) {
            $this->delete_password_error = true;
            $this->addError('delete_password', 'Mot de passe incorrect.');
            return;
        }

        $this->delete_password_error = false;
        $saleRef = $this->sale->reference;

        DB::transaction(function () {

            // ── 3. Remettre les produits vendus en stock ──────
            // withoutEvents() désactive tous les boot hooks du modèle Product
            // (saving, saved, updating...) qui causent l'erreur de cast enum
            foreach ($this->sale->items as $item) {
                if (!$item->product) continue;
                if ($item->product->state->value !== ProductState::SOLD->value) continue;

                Product::withoutEvents(function () use ($item) {
                    $item->product->update([
                        'state'      => ProductState::AVAILABLE->value,
                        'location'   => ProductLocation::STORE->value,
                        'updated_by' => Auth::id(),
                    ]);
                });

                StockMovement::create([
                    'product_model_id' => $item->product->product_model_id,
                    'product_id'       => $item->product->id,
                    'type'             => StockMovementType::CLIENT_RETURN->value,
                    'quantity'         => 1,
                    'quantity_before'  => 0,
                    'quantity_after'   => 1,
                    'location_from'    => ProductLocation::CLIENT->value,
                    'location_to'      => ProductLocation::STORE->value,
                    'notes'            => "Annulation vente {$this->sale->reference} — {$this->delete_reason}",
                    'created_by'       => Auth::id(),
                ]);
            }

            // ── 4. Annuler puis soft-delete ───────────────────
            DB::table('sales')
                ->where('id', $this->sale->id)
                ->update(['sale_status' => 'cancelled', 'updated_at' => now()]);

            $this->sale->delete();
        });

        // ── 5. Log d'activité ─────────────────────────────────
        ActivityLogService::log(
            action: 'delete',
            description: "Suppression vente {$saleRef} — {$this->delete_reason}",
            model: $this->sale,
        );

        $this->redirect(route('sales.index'), navigate: true);
    }

    // ─── Helpers ──────────────────────────────────────────────

    private function reloadSale(): void
    {
        $this->sale->refresh()->load([
            'items.productModel.brand',
            'items.product',
            'payments.createdBy',
            'reseller',
            'createdBy',
            'tradeInProduct.productModel',
        ]);
    }

    public function render()
    {
        $paymentMethods = collect(PaymentMethod::cases())
            ->filter(fn($m) => $m !== PaymentMethod::TRADE_IN)
            ->map(fn($m) => ['id' => $m->value, 'name' => $m->label()])
            ->values()
            ->toArray();

        return view('livewire.sales.show', compact('paymentMethods'))
            ->layout('layouts.app', ['title' => 'Vente ' . $this->sale->reference]);
    }
}
