<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
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
            'product_model_id' => [
                'required',
                'integer',
                Rule::exists('product_models', 'id')->whereNull('deleted_at'),
            ],
            'purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'client_price' => [
                'required',
                'numeric',
                'min:0',
                'gte:purchase_price', // Client price must be >= purchase
            ],
            'reseller_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'imei' => [
                'nullable',
                'string',
                'unique:products,imei', // IMEI globally unique
            ],
            'serial_number' => [
                'nullable',
                'string',
            ],
            'supplier_id' => [
                'nullable',
                'integer',
                Rule::exists('suppliers', 'id')->whereNull('deleted_at'),
            ],
            'purchase_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'defects' => [
                'nullable',
                'string',
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
            'product_model_id.required' => 'Le modèle est obligatoire.',
            'product_model_id.exists' => 'Le modèle sélectionné n\'existe pas.',
            'purchase_price.required' => 'Le prix d\'achat est obligatoire.',
            'purchase_price.numeric' => 'Le prix d\'achat doit être un nombre.',
            'purchase_price.min' => 'Le prix d\'achat doit être positif ou nul.',
            'client_price.required' => 'Le prix client est obligatoire.',
            'client_price.numeric' => 'Le prix client doit être un nombre.',
            'client_price.min' => 'Le prix client doit être positif ou nul.',
            'client_price.gte' => 'Le prix client doit être >= prix d\'achat.',
            'reseller_price.required' => 'Le prix revendeur est obligatoire.',
            'reseller_price.numeric' => 'Le prix revendeur doit être un nombre.',
            'reseller_price.min' => 'Le prix revendeur doit être positif ou nul.',
            'imei.unique' => 'Cet IMEI existe déjà.',
            'supplier_id.exists' => 'Le fournisseur sélectionné n\'existe pas.',
            'purchase_date.required' => 'La date d\'achat est obligatoire.',
            'purchase_date.date' => 'La date d\'achat n\'est pas valide.',
            'purchase_date.before_or_equal' => 'La date d\'achat ne peut pas être dans le futur.',
            'notes.max' => 'Les notes ne peuvent pas dépasser 1000 caractères.',
        ];
    }
}
