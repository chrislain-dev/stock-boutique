<?php

namespace App\Livewire\Purchases;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductCondition;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\StockMovement;
use App\Enums\StockMovementType;
use App\Enums\ProductState;
use App\Enums\ProductLocation;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mary\Traits\Toast;

class Create extends Component
{
    use Toast;

    public int $step = 1;

    // ─── Étape 1 : Infos achat ────────────────────────────────
    public string $purchase_date       = '';
    public ?int   $supplier_id         = null;
    public string $payment_method      = '';
    public string $payment_status      = 'unpaid';
    public string $paid_amount         = '0';
    public string $due_date            = '';
    public string $transaction_reference = '';
    public string $notes               = '';

    // ─── Étape 2 : Lignes produits ────────────────────────────
    public array $items = [];

    // Formulaire ajout ligne
    public ?int  $line_product_model_id    = null;
    public string $line_condition          = 'sealed';
    public string $line_unit_purchase_price = '';
    public string $line_unit_client_price  = '';
    public string $line_unit_reseller_price = '';
    public string $line_notes             = '';

    // Mode saisie IMEI
    public string $line_imei_mode  = 'manual'; // manual | bulk
    public string $line_imei_single = '';
    public string $line_imei_bulk   = '';
    public array  $line_imei_list   = ['']; // champs manuels

    // Pour accessoires (non sérialisé)
    public int    $line_quantity   = 1;

    // Modèle sélectionné (pour savoir si sérialisé)
    public bool   $line_is_serialized = true;

    public function mount(): void
    {
        $this->purchase_date = now()->format('Y-m-d');
    }

    // ─── Navigation étapes ────────────────────────────────────
    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validateStep1();
        }
        $this->step++;
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    private function validateStep1(): void
    {
        $this->validate([
            'purchase_date' => 'required|date',
            'supplier_id'   => 'required|exists:suppliers,id',
            'payment_status' => 'required|in:paid,partial,unpaid',
            'paid_amount'   => 'required|numeric|min:0',
        ]);
    }

    // ─── Mise à jour modèle sélectionné ──────────────────────
    public function updatedLineProductModelId(): void
    {
        if (!$this->line_product_model_id) return;

        $model = ProductModel::find($this->line_product_model_id);
        if (!$model) return;

        $this->line_is_serialized        = (bool) $model->is_serialized;
        $this->line_unit_purchase_price  = $model->default_purchase_price ?? '';
        $this->line_unit_client_price    = $model->default_client_price ?? '';
        $this->line_unit_reseller_price  = $model->default_reseller_price ?? '';
        $this->line_condition            = $model->condition->value ?? 'sealed';
        $this->line_imei_list            = [''];
        $this->line_imei_single          = '';
        $this->line_imei_bulk            = '';
        $this->line_quantity             = 1;
    }

    // ─── Gestion champs IMEI manuels ─────────────────────────
    public function addImeiField(): void
    {
        $this->line_imei_list[] = '';
    }

    public function removeImeiField(int $index): void
    {
        array_splice($this->line_imei_list, $index, 1);
        if (empty($this->line_imei_list)) {
            $this->line_imei_list = [''];
        }
    }

    // ─── Ajouter une ligne au panier ─────────────────────────
    public function addLine(): void
    {
        $this->validate([
            'line_product_model_id'     => 'required|exists:product_models,id',
            'line_unit_purchase_price'  => 'required|numeric|min:0',
            'line_unit_client_price'    => 'required|numeric|min:0',
            'line_unit_reseller_price'  => 'required|numeric|min:0',
        ]);

        $model = ProductModel::find($this->line_product_model_id);

        if ($this->line_is_serialized) {
            // Récupérer tous les IMEI/serials
            if ($this->line_imei_mode === 'bulk') {
                $identifiers = array_filter(
                    array_map('trim', explode("\n", $this->line_imei_bulk))
                );
            } else {
                $identifiers = array_filter(
                    array_map('trim', $this->line_imei_list)
                );
            }

            $identifiers = array_values(array_unique($identifiers));

            if (empty($identifiers)) {
                $this->addError('line_imei_single', 'Saisissez au moins un IMEI/numéro de série.');
                return;
            }

            // Vérifier doublons internes
            foreach ($identifiers as $id) {
                foreach ($this->items as $item) {
                    if (isset($item['identifiers']) && in_array($id, $item['identifiers'])) {
                        $this->addError('line_imei_single', "L'identifiant {$id} est déjà dans la liste.");
                        return;
                    }
                }
            }

            $this->items[] = [
                'product_model_id'    => $this->line_product_model_id,
                'model_name'          => $model->display_label,
                'is_serialized'       => true,
                'identifiers'         => $identifiers,
                'quantity'            => count($identifiers),
                'condition'           => $this->line_condition,
                'unit_purchase_price' => $this->line_unit_purchase_price,
                'unit_client_price'   => $this->line_unit_client_price,
                'unit_reseller_price' => $this->line_unit_reseller_price,
                'line_total'          => count($identifiers) * $this->line_unit_purchase_price,
                'category'            => $model->category->value,
                'notes'               => $this->line_notes,
            ];
        } else {
            $this->validate(['line_quantity' => 'required|integer|min:1']);

            $this->items[] = [
                'product_model_id'    => $this->line_product_model_id,
                'model_name'          => $model->display_label,
                'is_serialized'       => false,
                'identifiers'         => [],
                'quantity'            => $this->line_quantity,
                'condition'           => $this->line_condition,
                'unit_purchase_price' => $this->line_unit_purchase_price,
                'unit_client_price'   => $this->line_unit_client_price,
                'unit_reseller_price' => $this->line_unit_reseller_price,
                'line_total'          => $this->line_quantity * $this->line_unit_purchase_price,
                'category'            => $model->category->value,
                'notes'               => $this->line_notes,
            ];
        }

        $this->resetLine();
    }

    public function removeLine(int $index): void
    {
        array_splice($this->items, $index, 1);
    }

    private function resetLine(): void
    {
        $this->line_product_model_id     = null;
        $this->line_condition            = 'sealed';
        $this->line_unit_purchase_price  = '';
        $this->line_unit_client_price    = '';
        $this->line_unit_reseller_price  = '';
        $this->line_notes                = '';
        $this->line_imei_mode            = 'manual';
        $this->line_imei_single          = '';
        $this->line_imei_bulk            = '';
        $this->line_imei_list            = [''];
        $this->line_quantity             = 1;
        $this->line_is_serialized        = true;
        $this->resetErrorBag();
    }

    public function getTotalAttribute(): float
    {
        return collect($this->items)->sum('line_total');
    }

    // ─── Sauvegarde finale ────────────────────────────────────
    public function save(): void
    {
        if (empty($this->items)) {
            $this->error('Ajoutez au moins une ligne.');
            return;
        }

        DB::transaction(function () {
            $total = collect($this->items)->sum('line_total');

            $purchase = Purchase::create([
                'supplier_id'            => $this->supplier_id,
                'total_amount'           => $total,
                'paid_amount'            => $this->paid_amount ?: 0,
                'payment_status'         => $this->payment_status,
                'status'                 => 'received',
                'payment_method'         => $this->payment_method ?: null,
                'transaction_reference'  => $this->transaction_reference ?: null,
                'purchase_date'          => $this->purchase_date,
                'due_date'               => $this->due_date ?: null,
                'notes'                  => $this->notes ?: null,
                'created_by'             => auth()->id(),
            ]);

            foreach ($this->items as $item) {
                if ($item['is_serialized']) {
                    foreach ($item['identifiers'] as $identifier) {
                        $isImei = $item['category'] === 'telephone';

                        $product = Product::create([
                            'product_model_id' => $item['product_model_id'],
                            'imei'             => $isImei ? $identifier : null,
                            'serial_number'    => !$isImei ? $identifier : null,
                            'state'            => ProductState::AVAILABLE->value,
                            'location'         => ProductLocation::STORE->value,
                            'purchase_price'   => $item['unit_purchase_price'],
                            'client_price'     => $item['unit_client_price'],
                            'reseller_price'   => $item['unit_reseller_price'],
                            'purchase_date'    => $this->purchase_date,
                            'supplier_id'      => $this->supplier_id,
                            'created_by'       => auth()->id(),
                            'updated_by'       => auth()->id(),
                        ]);

                        PurchaseItem::create([
                            'purchase_id'         => $purchase->id,
                            'product_model_id'    => $item['product_model_id'],
                            'product_id'          => $product->id,
                            'quantity'            => 1,
                            'unit_purchase_price' => $item['unit_purchase_price'],
                            'unit_client_price'   => $item['unit_client_price'],
                            'unit_reseller_price' => $item['unit_reseller_price'],
                            'line_total'          => $item['unit_purchase_price'],
                            'condition'           => $item['condition'],
                            'notes'               => $item['notes'] ?: null,
                        ]);

                        StockMovement::create([
                            'product_model_id' => $product->product_model_id,
                            'product_id'       => $product->id,
                            'type'             => StockMovementType::STOCK_IN->value,
                            'quantity'         => 1,
                            'quantity_before'  => 0,
                            'quantity_after'   => 1,
                            'location_from'    => null,
                            'location_to'      => ProductLocation::STORE->value,
                            'notes'            => "Achat {$purchase->reference}",
                            'created_by'       => auth()->id(),
                        ]);
                    }
                } else {
                    // Accessoires non sérialisés — N produits sans IMEI
                    for ($i = 0; $i < $item['quantity']; $i++) {
                        $product = Product::create([
                            'product_model_id' => $item['product_model_id'],
                            'imei'             => null,
                            'serial_number'    => null,
                            'state'            => ProductState::AVAILABLE->value,
                            'location'         => ProductLocation::STORE->value,
                            'purchase_price'   => $item['unit_purchase_price'],
                            'client_price'     => $item['unit_client_price'],
                            'reseller_price'   => $item['unit_reseller_price'],
                            'purchase_date'    => $this->purchase_date,
                            'supplier_id'      => $this->supplier_id,
                            'created_by'       => auth()->id(),
                            'updated_by'       => auth()->id(),
                        ]);

                        StockMovement::create([
                            'product_model_id' => $product->product_model_id,
                            'product_id'    => $product->id,
                            'type'          => StockMovementType::STOCK_IN->value,
                            'quantity'         => 1,
                            'quantity_before'  => 0,
                            'quantity_after'   => 1,           
                            'location_from' => null,
                            'location_to'   => ProductLocation::STORE->value,
                            'notes'         => "Achat {$purchase->reference}",
                            'created_by'    => auth()->id(),
                        ]);
                    }

                    PurchaseItem::create([
                        'purchase_id'         => $purchase->id,
                        'product_model_id'    => $item['product_model_id'],
                        'product_id'          => null,
                        'quantity'            => $item['quantity'],
                        'unit_purchase_price' => $item['unit_purchase_price'],
                        'unit_client_price'   => $item['unit_client_price'],
                        'unit_reseller_price' => $item['unit_reseller_price'],
                        'line_total'          => $item['line_total'],
                        'condition'           => $item['condition'],
                        'notes'               => $item['notes'] ?: null,
                    ]);
                }
            }
        });

        $this->success('Achat enregistré avec succès.');
        $this->redirect(route('purchases.index'), navigate: true);
    }

    public function render()
    {
        $suppliers = Supplier::active()
            ->orderBy('name')
            ->get()
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
            ->toArray();

        $productModels = ProductModel::with('brand')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($m) => ['id' => $m->id, 'name' => $m->display_label])
            ->toArray();

        $paymentMethods = collect(PaymentMethod::cases())
            ->map(fn($m) => ['id' => $m->value, 'name' => $m->label()])
            ->toArray();

        $paymentStatuses = collect(PaymentStatus::cases())
            ->map(fn($s) => ['id' => $s->value, 'name' => $s->label()])
            ->toArray();

        $conditions = collect(ProductCondition::cases())
            ->map(fn($c) => ['id' => $c->value, 'name' => $c->label()])
            ->toArray();

        return view('livewire.purchases.create', compact(
            'suppliers',
            'productModels',
            'paymentMethods',
            'paymentStatuses',
            'conditions'
        ))->layout('layouts.app', ['title' => 'Nouvel achat']);
    }
}
