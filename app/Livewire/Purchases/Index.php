<?php

namespace App\Livewire\Purchases;

use App\Enums\PaymentStatus;
use App\Models\Purchase;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast, WithPagination;

    public string $search        = '';
    public string $statusFilter  = '';
    public string $supplierFilter = '';
    public string $dateFrom      = '';
    public string $dateTo        = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $purchases = Purchase::query()
            ->with(['supplier', 'createdBy', 'items'])
            ->when(
                $this->search,
                fn($q) =>
                $q->where('reference', 'ilike', "%{$this->search}%")
                    ->orWhereHas(
                        'supplier',
                        fn($q2) =>
                        $q2->where('name', 'ilike', "%{$this->search}%")
                    )
            )
            ->when(
                $this->statusFilter,
                fn($q) =>
                $q->where('payment_status', $this->statusFilter)
            )
            ->when(
                $this->supplierFilter,
                fn($q) =>
                $q->where('supplier_id', $this->supplierFilter)
            )
            ->when(
                $this->dateFrom,
                fn($q) =>
                $q->whereDate('purchase_date', '>=', $this->dateFrom)
            )
            ->when(
                $this->dateTo,
                fn($q) =>
                $q->whereDate('purchase_date', '<=', $this->dateTo)
            )
            ->orderByDesc('purchase_date')
            ->orderByDesc('created_at')
            ->paginate(15);

        $suppliers = Supplier::active()
            ->orderBy('name')
            ->get()
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
            ->prepend(['id' => '', 'name' => 'Tous les fournisseurs'])
            ->toArray();

        $statuses = collect(PaymentStatus::cases())
            ->map(fn($s) => ['id' => $s->value, 'name' => $s->label()])
            ->prepend(['id' => '', 'name' => 'Tous les statuts'])
            ->toArray();

        // Stats rapides
        $stats = [
            'total_month'   => Purchase::thisMonth()->sum('total_amount'),
            'unpaid'        => Purchase::where('payment_status', PaymentStatus::UNPAID)->sum('total_amount'),
            'count_month'   => Purchase::thisMonth()->count(),
        ];

        return view('livewire.purchases.index', compact('purchases', 'suppliers', 'statuses', 'stats'))
            ->layout('layouts.app', ['title' => 'Achats fournisseurs']);
    }
}
