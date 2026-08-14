<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAlumnoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'codigo_unico_persona' => 'required|string|max:20|unique:alumno,codigo_unico_persona',
            'nombre_completo' => 'required|string|max:120',
            'sexo' => 'required|in:M,F',
            'fecha_nacimiento' => 'required|date|before:today',
        ];
    }
}
