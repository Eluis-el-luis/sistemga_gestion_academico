<?php

namespace App\Http\Requests\AnioEscolar;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnioEscolarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('configuracion.gestionar');
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d{4}-\d{4}$/', // Formato: 2026-2027
                'unique:anio_escolar,nombre',
            ],
            'fecha_inicio' => [
                'required',
                'date',
                'before_or_equal:fecha_fin',
            ],
            'fecha_fin' => [
                'required',
                'date',
                'after_or_equal:fecha_inicio',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del año escolar es obligatorio.',
            'nombre.regex' => 'El nombre debe tener el formato AAAA-AAAA (ejemplo: 2026-2027).',
            'nombre.unique' => 'Ya existe un año escolar con ese nombre.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.before_or_equal' => 'La fecha de inicio debe ser anterior o igual a la fecha de fin.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalizar el nombre si viene en formato diferente
        if ($this->has('nombre')) {
            $nombre = $this->input('nombre');
            // Si viene como "2026" o "2026/2027", normalizar a "2026-2027"
            if (preg_match('/^(\d{4})[\/\-]?(\d{4})?$/', $nombre, $matches)) {
                $inicio = $matches[1];
                $fin = $matches[2] ?? (int)$inicio + 1;
                $this->merge(['nombre' => "{$inicio}-{$fin}"]);
            }
        }
    }
}