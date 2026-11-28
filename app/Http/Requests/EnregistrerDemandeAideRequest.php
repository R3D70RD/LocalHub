<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnregistrerDemandeAideRequest extends FormRequest
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
            'urgent' => ['nullable', 'boolean'],
            'date_limite' => ['nullable', 'date', 'after_or_equal:today'],
            'statut' => ['nullable', Rule::in(['ouverte', 'pourvue', 'fermee'])],
        ];
    }
}
