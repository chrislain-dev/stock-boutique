<?php

namespace App\Livewire\Sales;

use App\Enums\PaymentStatus;
use App\Models\Reseller;
use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast, WithPagination;

    public string $search         = '';
    public string $statusFilter   = '';
    public string $typeFilter     = '';
    public string $dateFrom       = '';
    public string $dateTo         = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $sales = Sale::query()
            ->with(['reseller', 'createdBy', 'items'])
            ->when(
                $this->search,
                fn($q) =>
                $q->where('reference', 'ilike', "%{$this->search}%")
                    ->orWhere('customer_name', 'ilike', "%{$this->search}%")
                    ->orWhere('customer_phone', 'ilike', "%{$this->search}%")
                    ->orWhereHas(
                        'reseller',
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
                $this->typeFilter,
                fn($q) =>
                $q->where('customer_type', $this->typeFilter)
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
            ->paginate(15);

        $statuses = collect(PaymentStatus::cases())
            ->map(fn($s) => ['id' => $s->value, 'name' => $s->label()])
            ->prepend(['id' => '', 'name' => 'Tous les statuts'])
            ->toArray();

        $stats = [
            'ca_today'       => Sale::today()->sum('total_amount'),
            'ca_month'       => Sale::thisMonth()->sum('total_amount'),
            'unpaid_total'   => Sale::where('payment_status', '!=', PaymentStatus::PAID->value)->sum(\Illuminate\Support\Facades\DB::raw('total_amount - paid_amount')),
            'count_today'    => Sale::today()->count(),
        ];

        return view('livewire.sales.index', compact('sales', 'statuses', 'stats'))
            ->layout('layouts.app', ['title' => 'Ventes']);
    }
}
