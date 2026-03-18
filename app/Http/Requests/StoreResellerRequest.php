<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $resellerId = $this->route('reseller')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('resellers', 'name')->ignore($resellerId)->whereNull('deleted_at'),
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
            'credit_limit' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'payment_terms_days' => [
                'nullable',
                'integer',
                'min:0',
                'max:365',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du revendeur est obligatoire.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'name.unique' => 'Ce revendeur existe déjà.',
            'email.email' => 'L\'email doit être valide.',
            'credit_limit.numeric' => 'La limite de crédit doit être un nombre.',
            'credit_limit.min' => 'La limite de crédit doit être positive.',
            'payment_terms_days.integer' => 'Les délais de paiement doivent être en jours (nombre entier).',
            'payment_terms_days.max' => 'Les délais de paiement ne doivent pas dépasser 365 jours.',
        ];
    }
}
