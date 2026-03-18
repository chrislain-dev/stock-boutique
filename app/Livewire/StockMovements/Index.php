<?php

namespace App\Livewire\StockMovements;

use App\Enums\StockMovementType;
use App\Models\StockMovement;
use App\Traits\EnsurePermission;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast, WithPagination, EnsurePermission;

    public string $search     = '';
    public string $typeFilter = '';
    public string $dateFrom   = '';
    public string $dateTo     = '';

    // Modal ajustement manuel (admin only)
    public bool   $showAdjustModal    = false;
    public string $adjust_imei        = '';
    public ?int   $adjust_product_id  = null;
    public string $adjust_type        = 'adjustment';
    public string $adjust_location_from = '';
    public string $adjust_location_to   = '';
    public string $adjust_notes       = '';
    public string $adjust_error       = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openAdjustModal(): void
    {
        abort_unless(auth()->user()->hasPermission('adjust_stock'), 403);
        $this->adjust_imei        = '';
        $this->adjust_product_id  = null;
        $this->adjust_type        = 'adjustment';
        $this->adjust_location_from = '';
        $this->adjust_location_to   = '';
        $this->adjust_notes       = '';
        $this->adjust_error       = '';
        $this->showAdjustModal    = true;
    }

    public function searchAdjustProduct(): void
    {
        $this->adjust_error = '';

        $product = \App\Models\Product::where(function ($q) {
            $q->where('imei', $this->adjust_imei)
                ->orWhere('serial_number', $this->adjust_imei);
        })->first();

        if (!$product) {
            $this->adjust_error = 'Produit introuvable.';
            return;
        }

        $this->adjust_product_id = $product->id;
    }

    public function saveAdjustment(): void
    {
        abort_unless(auth()->user()->hasPermission('adjust_stock'), 403);

        $this->validate([
            'adjust_product_id' => 'required|exists:products,id',
            'adjust_type'       => 'required',
            'adjust_notes'      => 'required|min:5',
        ]);

        $product = \App\Models\Product::findOrFail($this->adjust_product_id);

        StockMovement::create([
            'product_model_id' => $product->product_model_id,
            'product_id'       => $product->id,
            'type'             => $this->adjust_type,
            'quantity'         => 1,
            'quantity_before'  => 1,
            'quantity_after'   => $this->adjust_type === \App\Enums\StockMovementType::LOSS->value ? 0 : 1,
            'location_from'    => $this->adjust_location_from ?: null,
            'location_to'      => $this->adjust_location_to ?: null,
            'notes'            => $this->adjust_notes,
            'created_by'       => Auth::id(),
        ]);

        // Mettre à jour l'état du produit si perte
        if ($this->adjust_type === StockMovementType::LOSS->value) {
            $product->update(['state' => \App\Enums\ProductState::DEFECTIVE->value]);
        }

        // Mettre à jour la location si transfert
        if ($this->adjust_type === StockMovementType::TRANSFER->value && $this->adjust_location_to) {
            $product->update(['location' => $this->adjust_location_to]);
        }

        $this->showAdjustModal = false;
        $this->success('Mouvement enregistré.');
    }

    public function render()
    {
        $movements = StockMovement::query()
            ->with(['product.productModel.brand', 'productModel.brand', 'createdBy'])
            ->when(
                $this->search,
                fn($q) =>
                $q->whereHas(
                    'product',
                    fn($q2) =>
                    $q2->where('imei', 'ilike', "%{$this->search}%")
                        ->orWhere('serial_number', 'ilike', "%{$this->search}%")
                )
                    ->orWhereHas(
                        'productModel',
                        fn($q2) =>
                        $q2->where('name', 'ilike', "%{$this->search}%")
                    )
            )
            ->when(
                $this->typeFilter,
                fn($q) =>
                $q->where('type', $this->typeFilter)
            )
            ->when(
                $this->dateFrom,
                fn($q) =>
                $q->whereDate('created_at', '>=', $this->dateFrom)
            )
            ->when(
                $this->dateTo,
                fn($q) =>
                $q->whereDate('created_at', '<=', $this->dateTo)
            )
            ->orderByDesc('created_at')
            ->paginate(20);

        $types = collect(StockMovementType::cases())
            ->map(fn($t) => ['id' => $t->value, 'name' => $t->label()])
            ->prepend(['id' => '', 'name' => 'Tous les types'])
            ->toArray();

        $adjustTypes = collect([
            StockMovementType::ADJUSTMENT,
            StockMovementType::TRANSFER,
            StockMovementType::LOSS,
            StockMovementType::SUPPLIER_RETURN,
        ])->map(fn($t) => ['id' => $t->value, 'name' => $t->label()])->toArray();

        $locations = collect(\App\Enums\ProductLocation::cases())
            ->map(fn($l) => ['id' => $l->value, 'name' => $l->label()])
            ->toArray();

        return view('livewire.stock-movements.index', compact('movements', 'types', 'adjustTypes', 'locations'))
            ->layout('layouts.app', ['title' => 'Mouvements de stock']);
    }
}
