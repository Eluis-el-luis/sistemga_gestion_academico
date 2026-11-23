<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarAsistenciaAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'asistencias' => ['required', 'array', 'min:1'],
            'asistencias.*.matricula_id' => ['required', 'integer', 'exists:matricula,id'],
            'asistencias.*.estado_asistencia' => [
                'required',
                'string',
                'in:Presente,Ausencia Injustificada,Ausencia Justificada,Retiro Anticipado,Actividad Institucional',
            ],
        ];
    }
}