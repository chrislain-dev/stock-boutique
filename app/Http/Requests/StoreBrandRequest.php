<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = $this->route('brand')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('brands', 'name')->ignore($brandId)->whereNull('deleted_at'),
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la marque est obligatoire.',
            'name.max' => 'Le nom ne doit pas dépasser 100 caractères.',
            'name.unique' => 'Cette marque existe déjà.',
            'description.max' => 'La description ne doit pas dépasser 500 caractères.',
        ];
    }
}
