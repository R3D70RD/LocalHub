<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnregistrerOutilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'id_categorie' => ['nullable', 'integer', 'exists:categorie,id_categorie'],
            'portee' => ['nullable', Rule::in(['quartier', 'ville', 'pays'])],
            'etat' => ['nullable', Rule::in(['neuf', 'bon', 'usage'])],
            'caution' => ['nullable', 'numeric', 'min:0'],
            'disponible' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:6'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
