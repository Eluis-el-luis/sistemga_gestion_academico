<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización real se hace en el controlador vía Policy
    }

    public function rules(): array
    {
        return [
            'corte_evaluativo_id' => 'required|exists:corte_evaluativo,id',
            // Matriz: notas[matricula_id][actividad_evaluativa_id] = puntaje
            'notas' => 'required|array',
            'notas.*' => 'array',
            'notas.*.*' => 'nullable|numeric|min:0',
        ];
    }
}