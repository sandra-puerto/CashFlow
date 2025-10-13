<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountsRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|integer|min:1|unique:accounts,code',
            'name' => 'required|string|min:1|max:255|unique:accounts,name',
            'description' => 'nullable|string|max:255|unique:accounts,description',
            'parent_id' => 'nullable|number|min:1|exists:accounts,id',
        ];
    }

    public function messages(): array
    {
        return [            
            'code.required' => 'El campo Código es obligatorio.',
            'code.integer' => 'El Código debe ser un número entero.',
            'code.min' => 'El Código debe ser, al menos, 1 o superior.',
            'code.unique' => 'El Código de cuenta ingresado ya existe.',

            'name.required' => 'El campo Nombre es obligatorio.',
            'name.string' => 'El Nombre debe ser texto.',
            'name.min' => 'El Nombre debe tener al menos :min caracter.',
            'name.max' => 'El Nombre no debe exceder los :max caracteres.',
            'name.unique' => 'El Nombre de cuenta ingresado ya existe.',
            
            'description.string' => 'La Descripción debe ser texto.',
            'description.min' => 'La Descripción debe tener al menos :min caracter.',
            'description.max' => 'La Descripción no debe exceder los :max caracteres.',
            'description.unique' => 'La Descripción ingresada ya existe.',

            'parent_id.string' => 'El ID de cuenta padre no es válido (debe ser texto).',
            'parent_id.min' => 'El ID de cuenta padre no es válido.',
            'parent_id.max' => 'El ID de cuenta padre es demasiado largo.',
            'parent_id.exists' => 'La Cuenta Padre seleccionada no existe en la base de datos.'
        ];
    }
}
