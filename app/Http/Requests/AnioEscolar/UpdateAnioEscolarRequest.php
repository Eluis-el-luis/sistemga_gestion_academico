<?php

namespace App\Http\Requests\AnioEscolar;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnioEscolarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('configuracion.gestionar');
    }

    public function rules(): array
    {
        $anioEscolar = $this->route('anioEscolar');

        return [
            'nombre' => [
                'sometimes',
                'string',
                'max:20',
                'regex:/^\d{4}-\d{4}$/',
                Rule::unique('anio_escolar', 'nombre')->ignore($anioEscolar->id),
            ],
            'fecha_inicio' => [
                'sometimes',
                'date',
                'before_or_equal:fecha_fin',
            ],
            'fecha_fin' => [
                'sometimes',
                'date',
                'after_or_equal:fecha_inicio',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.regex' => 'El nombre debe tener el formato AAAA-AAAA (ejemplo: 2026-2027).',
            'nombre.unique' => 'Ya existe un año escolar con ese nombre.',
            'fecha_inicio.before_or_equal' => 'La fecha de inicio debe ser anterior o igual a la fecha de fin.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('nombre')) {
            $nombre = $this->input('nombre');
            if (preg_match('/^(\d{4})[\/\-]?(\d{4})?$/', $nombre, $matches)) {
                $inicio = $matches[1];
                $fin = $matches[2] ?? (int)$inicio + 1;
                $this->merge(['nombre' => "{$inicio}-{$fin}"]);
            }
        }
    }
}