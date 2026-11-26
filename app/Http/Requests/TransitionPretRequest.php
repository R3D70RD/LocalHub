<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionPretRequest extends FormRequest
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
            'action' => ['required', Rule::in([
                'approuver',
                'refuser',
                'annuler',
                'emprunter',
                'retourner',
                'ouvrir_litige',
                'resoudre',
            ])],
            'version' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
