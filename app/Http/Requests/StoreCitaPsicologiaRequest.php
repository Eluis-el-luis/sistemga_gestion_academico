<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCitaPsicologiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'matricula_id' => ['required', 'integer', 'exists:matricula,id'],
            'aula_id' => ['nullable', 'integer', 'exists:aula,id'],
            'bloque_horario_id' => ['nullable', 'integer', 'exists:bloque_horario,id'],
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i', 'after_or_equal:hora_inicio'],
            'justificar_clase' => ['nullable', 'boolean'],
            'motivo_asistencia' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'matricula_id.required' => 'Debes seleccionar un estudiante matriculado.',
            'matricula_id.exists' => 'El estudiante seleccionado no existe.',
            'fecha.required' => 'La fecha de la cita es obligatoria.',
            'hora_fin.after_or_equal' => 'La hora de fin debe ser igual o posterior a la hora de inicio.',
            'motivo_asistencia.max' => 'La observación/justificación no puede superar los 500 caracteres.',
        ];
    }
}