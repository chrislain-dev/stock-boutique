<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplierId = $this->route('supplier')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('suppliers', 'name')->ignore($supplierId)->whereNull('deleted_at'),
            ],
            'contact_person' => [
                'nullable',
                'string',
                'max:255',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'address' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du fournisseur est obligatoire.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'name.unique' => 'Ce fournisseur existe déjà.',
            'email.email' => 'L\'email doit être valide.',
            'address.max' => 'L\'adresse ne doit pas dépasser 500 caractères.',
        ];
    }
}
