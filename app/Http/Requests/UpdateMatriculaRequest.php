<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMatriculaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'aula_id' => ['sometimes', 'required', 'integer', 'exists:aula,id'],
            'estado' => ['sometimes', 'required', 'in:activo,retirado,promovido,repitente'],
            'fecha_retiro' => ['nullable', 'date'],
        ];
    }
}