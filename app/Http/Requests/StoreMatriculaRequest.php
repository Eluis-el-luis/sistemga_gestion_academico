<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMatriculaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alumno_id' => ['required', 'integer', 'exists:alumno,id'],
            'aula_id' => ['required', 'integer', 'exists:aula,id'],
            'anio_escolar_id' => [
                'required',
                'integer',
                'exists:anio_escolar,id',
                Rule::unique('matricula')->where(function ($query) {
                    return $query->where('alumno_id', $this->input('alumno_id'));
                }),
            ],
            'fecha_matricula' => ['required', 'date'],
            'estado' => ['required', 'in:activo,retirado,promovido,repitente'],
        ];
    }

    public function messages(): array
    {
        return [
            'anio_escolar_id.unique' => 'Error: Este alumno ya se encuentra matriculado en el periodo lectivo seleccionado.',
        ];
    }
}