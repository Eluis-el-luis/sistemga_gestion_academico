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
            
            // Nuevos campos
            'direccion_domiciliar' => 'required|string|max:500',
            'enfermedades_cronicas' => 'nullable|string|max:500',
            'hermanos_en_colegio' => 'nullable|string|max:255',
            
            'madre_nombre_completo' => 'nullable|string|max:120',
            'madre_cedula' => 'nullable|string|max:20',
            'madre_telefono' => 'nullable|string|max:20',
            'madre_ocupacion' => 'nullable|string|max:100',
            'madre_asiste_iglesia' => 'boolean',
            'madre_nombre_iglesia' => 'nullable|string|max:100',

            'padre_nombre_completo' => 'nullable|string|max:120',
            'padre_cedula' => 'nullable|string|max:20',
            'padre_telefono' => 'nullable|string|max:20',
            'padre_ocupacion' => 'nullable|string|max:100',
            'padre_asiste_iglesia' => 'boolean',
            'padre_nombre_iglesia' => 'nullable|string|max:100',

            'tutor_nombre_completo' => 'nullable|string|max:120',
            'tutor_cedula' => 'nullable|string|max:20',
            'tutor_telefono' => 'nullable|string|max:20',
            'tutor_ocupacion' => 'nullable|string|max:100',

            'autorizado_retirar_nombre' => 'nullable|string|max:120',
            'autorizado_retirar_cedula' => 'nullable|string|max:20',
            'autorizado_retirar_telefono' => 'nullable|string|max:20',

            'acepta_compromiso_cristiano' => 'boolean',
        ];
    }
}
