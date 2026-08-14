<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Protegido por AulaPolicy
    }

    public function rules(): array
    {
        return [
            // Validamos que el nombre (ej. "5to A") no se repita en el mismo año y grado
            'nombre' => [
                'required',
                'string',
                'max:20',
                Rule::unique('aula')->where(function ($query) {
                    return $query->where('anio_escolar_id', $this->anio_escolar_id)
                                 ->where('grado_id', $this->grado_id);
                }),
            ],
            'grado_id' => 'required|exists:grado,id',
            'modalidad_id' => 'required|exists:modalidad,id',
            'turno' => 'required|string|max:20',
            'docente_guia_id' => 'required|exists:docente,id',
            'anio_escolar_id' => 'required|exists:anio_escolar,id',
            'cupo' => 'required|integer|min:1|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un aula con este nombre para el grado y año escolar seleccionados.',
        ];
    }
}