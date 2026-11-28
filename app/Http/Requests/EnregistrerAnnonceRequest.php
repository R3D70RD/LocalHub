<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnregistrerAnnonceRequest extends FormRequest
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
            'type_annonce' => ['nullable', Rule::in(['vente', 'don', 'recherche'])],
            'prix' => ['nullable', 'numeric', 'min:0'],
            'statut' => ['nullable', Rule::in(['active', 'inactif'])],
        ];
    }
}
