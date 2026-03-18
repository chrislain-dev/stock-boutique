<?php

namespace App\Livewire\Purchases;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Purchase;
use App\Models\Supplier;
use Livewire\Component;
use Mary\Traits\Toast;

class Edit extends Component
{
    use Toast;

    public Purchase $purchase;

    public string $purchase_date        = '';
    public ?int   $supplier_id          = null;
    public string $payment_method       = '';
    public string $payment_status       = '';
    public string $paid_amount          = '0';
    public string $due_date             = '';
    public string $transaction_reference = '';
    public string $notes                = '';
    public string $status               = '';

    public function mount(Purchase $purchase): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->purchase              = $purchase;
        $this->purchase_date         = $purchase->purchase_date->format('Y-m-d');
        $this->supplier_id           = $purchase->supplier_id;
        $this->payment_method        = $purchase->payment_method?->value ?? '';
        $this->payment_status        = $purchase->payment_status->value;
        $this->paid_amount           = $purchase->paid_amount;
        $this->due_date              = $purchase->due_date?->format('Y-m-d') ?? '';
        $this->transaction_reference = $purchase->transaction_reference ?? '';
        $this->notes                 = $purchase->notes ?? '';
        $this->status                = $purchase->status;
    }

    public function save(): void
    {
        $this->validate([
            'purchase_date'  => 'required|date',
            'supplier_id'    => 'required|exists:suppliers,id',
            'payment_status' => 'required|in:paid,partial,unpaid',
            'paid_amount'    => 'required|numeric|min:0',
            'status'         => 'required|in:received,pending,cancelled',
        ]);

        $this->purchase->update([
            'purchase_date'          => $this->purchase_date,
            'supplier_id'            => $this->supplier_id,
            'payment_method'         => $this->payment_method ?: null,
            'payment_status'         => $this->payment_status,
            'paid_amount'            => $this->paid_amount,
            'due_date'               => $this->due_date ?: null,
            'transaction_reference'  => $this->transaction_reference ?: null,
            'notes'                  => $this->notes ?: null,
            'status'                 => $this->status,
        ]);

        $this->success('Achat mis à jour.');
        $this->redirect(route('purchases.show', $this->purchase->id), navigate: true);
    }

    public function render()
    {
        $suppliers = Supplier::active()
            ->orderBy('name')
            ->get()
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
            ->toArray();

        $paymentMethods = collect(PaymentMethod::cases())
            ->map(fn($m) => ['id' => $m->value, 'name' => $m->label()])
            ->toArray();

        $paymentStatuses = collect(PaymentStatus::cases())
            ->map(fn($s) => ['id' => $s->value, 'name' => $s->label()])
            ->toArray();

        return view('livewire.purchases.edit', compact('suppliers', 'paymentMethods', 'paymentStatuses'))
            ->layout('layouts.app', ['title' => 'Modifier achat ' . $this->purchase->reference]);
    }
}
