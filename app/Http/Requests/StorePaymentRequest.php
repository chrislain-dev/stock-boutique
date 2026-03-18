<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_id' => [
                'required',
                'integer',
                Rule::exists('sales', 'id')->whereNull('deleted_at'),
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'payment_method' => [
                'required',
                Rule::enum(PaymentMethod::class),
            ],
            'transaction_reference' => [
                'nullable',
                'string',
                'max:100',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'sale_id.required' => 'La vente est obligatoire.',
            'sale_id.exists' => 'La vente sélectionnée n\'existe pas.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant doit être supérieur à 0.',
            'payment_method.required' => 'Le mode de paiement est obligatoire.',
            'payment_method.enum' => 'Le mode de paiement n\'est pas valide.',
            'transaction_reference.max' => 'La référence de transaction ne doit pas dépasser 100 caractères.',
            'notes.max' => 'Les notes ne doivent pas dépasser 500 caractères.',
        ];
    }
}
