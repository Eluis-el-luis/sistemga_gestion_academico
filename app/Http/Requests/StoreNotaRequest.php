<?php

namespace App\Http\Requests;

use App\Models\AulaAsignaturaDocente;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class StoreNotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        try {
            $asignacion = $this->route('asignacion');

            if (!$asignacion instanceof AulaAsignaturaDocente || !$this->user()) {
                return false;
            }

            return Gate::forUser($this->user())->allows('calificar', $asignacion);
        } catch (\Throwable $e) {
            Log::error('Error en StoreNotaRequest::authorize', [
                'user_id' => $this->user()?->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function rules(): array
    {
        return [
            'corte_evaluativo_id' => ['required', 'integer', 'exists:corte_evaluativo,id'],
            'notas' => ['required', 'array'],
            'notas.*' => ['array'],
            'notas.*.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'indicadores' => ['nullable', 'array'],
            'indicadores.*' => ['nullable', 'integer', 'exists:indicador_logro,id'],
            'porcentajes' => ['nullable', 'array'],
            'porcentajes.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException('No tiene autorización para calificar en esta asignación.');
    }
}