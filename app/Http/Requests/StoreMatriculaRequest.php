<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMatriculaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La Policy ya se encarga de esto
    }

    public function rules(): array
    {
        return [
            'alumno_id' => 'required|exists:alumno,id',
            'aula_id' => 'required|exists:aula,id',
            
            // Aquí está la magia: validamos que el año sea único PARA ESTE alumno
            'anio_escolar_id' => [
                'required',
                'exists:anio_escolar,id',
                Rule::unique('matricula')->where(function ($query) {
                    return $query->where('alumno_id', $this->alumno_id);
                }),
            ],
            
            'fecha_matricula' => 'required|date',
            'estado' => 'required|in:activo,retirado,promovido,repitente',
        ];
    }

    public function messages(): array
    {
        return [
            'anio_escolar_id.unique' => 'Error: Este alumno ya se encuentra matriculado en el periodo lectivo seleccionado.',
        ];
    }


}