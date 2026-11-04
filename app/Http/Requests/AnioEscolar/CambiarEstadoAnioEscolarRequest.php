<?php

namespace App\Http\Requests\AnioEscolar;

use App\Models\AnioEscolar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CambiarEstadoAnioEscolarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('configuracion.gestionar');
    }

    public function rules(): array
    {
        $anioEscolar = $this->route('anioEscolar');

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
                // Requerido solo al cerrar
                Rule::requiredIf(fn() => $this->input('estado') === AnioEscolar::ESTADO_CERRADO),
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
        $anioEscolar = $this->route('anioEscolar');
        $estadoSolicitado = $this->input('estado');
        $estadoActual = $anioEscolar->estado;

        $validator->after(function ($validator) use ($anioEscolar, $estadoSolicitado, $estadoActual) {
            // Validar transiciones permitidas
            $transicionesPermitidas = $anioEscolar->getTransicionesPermitidas();
            
            // Si el estado solicitado es el mismo que el actual, no es error pero no hay acción
            if ($estadoSolicitado === $estadoActual) {
                return;
            }

            // Si está cerrado, no se puede cambiar
            if ($anioEscolar->es_cerrado) {
                $validator->errors()->add('estado', 'No se puede cambiar el estado de un año escolar cerrado.');
                return;
            }

            // Validar que la transición sea permitida
            if (!in_array($estadoSolicitado, $transicionesPermitidas)) {
                $mensajes = [
                    AnioEscolar::ESTADO_ACTIVO => 'No se puede activar: ya existe un año escolar activo o el año no está en Planificación.',
                    AnioEscolar::ESTADO_PLANIFICACION => 'No se puede pasar a Planificación: solo el año activo puede volver a Planificación.',
                    AnioEscolar::ESTADO_CERRADO => 'No se puede cerrar: el año debe estar Activo y pasar todas las validaciones.',
                ];
                
                $validator->errors()->add('estado', $mensajes[$estadoSolicitado] ?? 'Transición de estado no permitida.');
            }
        });
    }
}