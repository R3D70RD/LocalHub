<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EnregistrerPublicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:5000'],
            'prix' => ['nullable', 'numeric', 'min:0'],
            'ville' => ['nullable', 'string', 'max:100'],
            'date_heure' => ['nullable', 'date'],
            'id_categorie' => ['required', 'integer', 'exists:categorie,id_categorie'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'statut' => ['required', 'string', 'max:30'],
        ];
    }
}
