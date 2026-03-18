<?php

namespace App\Http\Requests;

use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
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
            'supplier_id' => [
                'required',
                'integer',
                Rule::exists('suppliers', 'id')->whereNull('deleted_at'),
            ],
            'reference' => [
                'required',
                'string',
                'max:50',
                Rule::unique('purchases', 'reference')->whereNull('deleted_at'),
            ],
            'purchase_date' => [
                'required',
                'date',
            ],
            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:purchase_date',
            ],
            'purchase_items' => [
                'required',
                'array',
                'min:1',
            ],
            'purchase_items.*.product_model_id' => [
                'required',
                'integer',
                Rule::exists('product_models', 'id')->whereNull('deleted_at'),
            ],
            'purchase_items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'purchase_items.*.unit_price' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'paid_amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'payment_status' => [
                'required',
                Rule::enum(PaymentStatus::class),
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
            'supplier_id.required' => 'Le fournisseur est obligatoire.',
            'supplier_id.exists' => 'Le fournisseur sélectionné n\'existe pas.',
            'reference.required' => 'La référence est obligatoire.',
            'reference.max' => 'La référence ne doit pas dépasser 50 caractères.',
            'reference.unique' => 'Cette référence d\'achat existe déjà.',
            'purchase_date.required' => 'La date d\'achat est obligatoire.',
            'purchase_date.date' => 'La date d\'achat doit être une date valide.',
            'due_date.date' => 'La date d\'échéance doit être une date valide.',
            'due_date.after_or_equal' => 'La date d\'échéance doit être >= date d\'achat.',
            'purchase_items.required' => 'Au moins un article est obligatoire.',
            'purchase_items.min' => 'Au moins un article est obligatoire.',
            'purchase_items.*.product_model_id.required' => 'Le modèle est obligatoire.',
            'purchase_items.*.product_model_id.exists' => 'Le modèle n\'existe pas.',
            'purchase_items.*.quantity.required' => 'La quantité est obligatoire.',
            'purchase_items.*.quantity.min' => 'La quantité doit être au moins 1.',
            'purchase_items.*.unit_price.required' => 'Le prix unitaire est obligatoire.',
            'purchase_items.*.unit_price.numeric' => 'Le prix unitaire doit être un nombre.',
            'purchase_items.*.unit_price.min' => 'Le prix unitaire doit être positif.',
            'paid_amount.required' => 'Le montant payé est obligatoire.',
            'paid_amount.numeric' => 'Le montant payé doit être un nombre.',
            'paid_amount.min' => 'Le montant payé doit être positif.',
            'payment_status.required' => 'Le statut du paiement est obligatoire.',
            'payment_status.enum' => 'Le statut du paiement n\'est pas valide.',
            'notes.max' => 'Les notes ne doivent pas dépasser 1000 caractères.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure purchase_items is an array
        if (is_string($this->purchase_items)) {
            $this->merge([
                'purchase_items' => json_decode($this->purchase_items, true) ?? [],
            ]);
        }
    }
}
