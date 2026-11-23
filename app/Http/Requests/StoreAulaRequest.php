<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:20',
                Rule::unique('aula')->where(function ($query) {
                    return $query->where('anio_escolar_id', $this->input('anio_escolar_id'))
                        ->where('grado_id', $this->input('grado_id'));
                }),
            ],
            'grado_id' => ['required', 'integer', 'exists:grado,id'],
            'modalidad_id' => ['required', 'integer', 'exists:modalidad,id'],
            'turno' => ['required', 'string', 'max:20'],
            'docente_guia_id' => [
                'required',
                'integer',
                'exists:docente,id',
                Rule::unique('aula')->where(function ($query) {
                    return $query->where('anio_escolar_id', $this->input('anio_escolar_id'));
                }),
            ],
            'anio_escolar_id' => ['required', 'integer', 'exists:anio_escolar,id'],
            'cupo' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un aula con este nombre para el grado y año escolar seleccionados.',
            'docente_guia_id.unique' => 'Error: El docente seleccionado ya es guía de otra aula en este año escolar.',
        ];
    }
}