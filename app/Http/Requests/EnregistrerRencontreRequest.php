<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnregistrerRencontreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('interets'))) {
            $this->merge([
                'interets' => array_values(array_filter(array_map(
                    'trim',
                    explode(',', $this->input('interets')),
                ))),
            ]);
        }
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
            'date_heure' => ['nullable', 'date'],
            'lieu' => ['nullable', 'string', 'max:255'],
            'capacite' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'interets' => ['nullable', 'array', 'max:12'],
            'interets.*' => ['string', 'max:40'],
            'statut' => ['nullable', Rule::in(['ouverte', 'complete', 'annulee'])],
        ];
    }
}
