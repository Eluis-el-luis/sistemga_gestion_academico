<?php

namespace App\Http\Requests\AnioEscolar;

use App\Models\AnioEscolar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CambiarEstadoAnioEscolarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('configuracion.gestionar');
    }

    public function rules(): array
    {
        return [
            'estado' => [
                'required',
                'string',
                Rule::in(AnioEscolar::ESTADOS_VALIDOS),
            ],
            'observaciones_cierre' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf(fn () => $this->input('estado') === AnioEscolar::ESTADO_CERRADO),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'observaciones_cierre.required' => 'Las observaciones son obligatorias al cerrar el año escolar.',
            'observaciones_cierre.max' => 'Las observaciones no pueden exceder 1000 caracteres.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            try {
                $anioEscolar = $this->route('anioEscolar');

                if (!$anioEscolar instanceof AnioEscolar) {
                    $validator->errors()->add('estado', 'El año escolar seleccionado no es válido.');
                    return;
                }

                $estadoSolicitado = (string) $this->input('estado');
                $estadoActual = (string) $anioEscolar->estado;

                if ($estadoSolicitado === $estadoActual) {
                    return;
                }

                if ($anioEscolar->es_cerrado) {
                    $validator->errors()->add('estado', 'No se puede cambiar el estado de un año escolar cerrado.');
                    return;
                }

                $transicionesPermitidas = $anioEscolar->getTransicionesPermitidas();

                if (!in_array($estadoSolicitado, $transicionesPermitidas, true)) {
                    $mensajes = [
                        AnioEscolar::ESTADO_ACTIVO => 'No se puede activar: ya existe un año escolar activo o el año no está en Planificación.',
                        AnioEscolar::ESTADO_PLANIFICACION => 'No se puede pasar a Planificación: solo el año activo puede volver a Planificación.',
                        AnioEscolar::ESTADO_CERRADO => 'No se puede cerrar: el año debe estar Activo y pasar todas las validaciones.',
                    ];

                    $validator->errors()->add('estado', $mensajes[$estadoSolicitado] ?? 'Transición de estado no permitida.');
                }
            } catch (\Throwable $e) {
                Log::error('Error al validar transición en CambiarEstadoAnioEscolarRequest', [
                    'user_id' => $this->user()?->id,
                    'estado_solicitado' => $this->input('estado'),
                    'exception' => $e->getMessage(),
                ]);

                $validator->errors()->add('estado', 'No fue posible validar la transición del año escolar.');
            }
        });
    }
}