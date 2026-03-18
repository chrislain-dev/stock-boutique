<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Livewire components will handle authorization
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'client_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'client_phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'reseller_id' => [
                'nullable',
                'integer',
                Rule::exists('resellers', 'id')->whereNull('deleted_at'),
            ],
            'sale_items' => [
                'required',
                'array',
                'min:1',
            ],
            'sale_items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'sale_items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'paid_amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'payment_method' => [
                'required',
                Rule::enum(PaymentMethod::class),
            ],
            'payment_status' => [
                'required',
                Rule::enum(PaymentStatus::class),
            ],
            'trade_in_product_id' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'sale_items.required' => 'Au moins un article est obligatoire.',
            'sale_items.min' => 'Au moins un article est obligatoire.',
            'sale_items.*.product_id.required' => 'L\'ID du produit est obligatoire.',
            'sale_items.*.product_id.exists' => 'Le produit sélectionné n\'existe pas ou a été supprimé.',
            'sale_items.*.quantity.required' => 'La quantité est obligatoire.',
            'sale_items.*.quantity.min' => 'La quantité doit être au moins 1.',
            'paid_amount.required' => 'Le montant payé est obligatoire.',
            'paid_amount.numeric' => 'Le montant payé doit être un nombre.',
            'paid_amount.min' => 'Le montant payé doit être positif.',
            'payment_method.required' => 'Le mode de paiement est obligatoire.',
            'payment_method.enum' => 'Le mode de paiement n\'est pas valide.',
            'payment_status.required' => 'Le statut du paiement est obligatoire.',
            'payment_status.enum' => 'Le statut du paiement n\'est pas valide.',
            'reseller_id.exists' => 'Le revendeur sélectionné n\'existe pas.',
            'trade_in_product_id.exists' => 'Le produit en troc n\'existe pas.',
            'notes.max' => 'Les notes ne doivent pas dépasser 1000 caractères.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure sale_items is an array
        if (is_string($this->sale_items)) {
            $this->merge([
                'sale_items' => json_decode($this->sale_items, true) ?? [],
            ]);
        }
    }
}
