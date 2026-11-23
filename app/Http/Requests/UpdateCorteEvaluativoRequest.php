<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateCorteEvaluativoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'peso_acumulado' => ['nullable', 'integer', 'min:0', 'max:100'],
            'peso_examen' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_inicio.required' => 'La fecha de inicio del corte es obligatoria.',
            'fecha_fin.required' => 'La fecha de fin del corte es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'peso_acumulado.min' => 'El puntaje acumulado no puede ser negativo.',
            'peso_acumulado.max' => 'El puntaje acumulado no puede exceder 100 puntos.',
            'peso_examen.min' => 'El puntaje de examen no puede ser negativo.',
            'peso_examen.max' => 'El puntaje de examen no puede exceder 100 puntos.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            try {
                if ($this->filled('peso_acumulado') || $this->filled('peso_examen')) {
                    $acumulado = (int) $this->input('peso_acumulado', 0);
                    $examen = (int) $this->input('peso_examen', 0);

                    if (($acumulado + $examen) !== 100) {
                        $validator->errors()->add(
                            'peso_acumulado',
                            'La suma de los puntos de acumulado y examen debe ser igual a 100.'
                        );
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Error al validar distribución de puntos en UpdateCorteEvaluativoRequest', [
                    'user_id' => $this->user()?->id,
                    'exception' => $e->getMessage(),
                ]);

                $validator->errors()->add('peso_acumulado', 'No fue posible validar la distribución de puntos.');
            }
        });
    }
}