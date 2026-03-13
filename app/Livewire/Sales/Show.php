<?php

namespace App\Livewire\Sales;

use App\Enums\PaymentMethod;
use App\Models\Payment;
use App\Models\Sale;
use Livewire\Component;
use Mary\Traits\Toast;

class Show extends Component
{
    use Toast;

    public Sale $sale;

    // Modal ajout paiement
    public bool   $showPaymentModal   = false;
    public string $pay_amount         = '';
    public string $pay_method         = 'cash';
    public string $pay_reference      = '';
    public string $pay_mobile         = '';
    public string $pay_bank           = '';
    public string $pay_notes          = '';

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

    public function openPaymentModal(): void
    {
        $this->pay_amount = (string) $this->sale->remaining_amount;
        $this->pay_method = 'cash';
        $this->pay_reference = '';
        $this->pay_mobile = '';
        $this->pay_bank = '';
        $this->pay_notes = '';
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
            'created_by'            => auth()->id(),
        ]);

        $this->sale->refresh()->load([
            'items.productModel.brand',
            'items.product',
            'payments.createdBy',
            'reseller',
            'createdBy',
            'tradeInProduct.productModel',
        ]);

        $this->showPaymentModal = false;
        $this->success('Paiement enregistré.');
    }

    public function printReceipt(): void
    {
        $this->redirect(route('sales.receipt', $this->sale->id));
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
