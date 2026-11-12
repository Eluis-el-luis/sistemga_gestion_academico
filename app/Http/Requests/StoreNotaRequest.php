<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;

class StoreNotaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Zero Trust: Authorization ANTES de validación de datos
     */
    public function authorize(): bool
    {
        $asignacion = $this->route('asignacion');
        
        if (!$asignacion instanceof \App\Models\AulaAsignaturaDocente) {
            return false;
        }

        try {
            $this->authorize('calificar', $asignacion);
            return true;
        } catch (AuthorizationException $e) {
            return false;
        }
    }

    public function rules(): array
    {
        return [
            'corte_evaluativo_id' => 'required|exists:corte_evaluativo,id',
            'notas' => 'required|array',
            'notas.*' => 'array',
            'notas.*.*' => 'nullable|numeric|min:0|max:100',
        ];
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException('No tiene autorización para calificar en esta asignación.');
    }
}