<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización real la haremos en el controlador vía Policy
    }

    public function rules(): array
    {
        return [
            'aula_asignatura_docente_id' => 'required|exists:aula_asignatura_docente,id',
            'corte_evaluativo_id' => 'required|exists:corte_evaluativo,id',
            
            // Validamos que envíen el arreglo (la matriz) de estudiantes
            'notas' => 'required|array',
            'notas.*.matricula_id' => 'required|exists:matricula,id',
            
            'notas.*.nota_cuantitativa' => 'nullable|numeric|between:0,100', 
        ];
    }
}