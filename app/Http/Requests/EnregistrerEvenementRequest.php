<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnregistrerEvenementRequest extends FormRequest
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
            'date_debut' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'timezone_id' => ['nullable', 'timezone'],
            'lieu' => ['nullable', 'string', 'max:255'],
            'capacite' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'statut' => ['nullable', Rule::in(['actif', 'annule'])],
        ];
    }
}
