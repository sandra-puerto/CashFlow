<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountRequest extends FormRequest
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
        return match($this->method()){
            'POST' => [
                /* Campo: 'Codigo' de la cuenta */
                'code' => 'required|integer|min:1|unique:accounts,code',

                /* Campo: Naturaleza de la cuenta */
                'nature' => 'nullable|in:debit,credit',

                /* Campo: Nombre de la cuenta */
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('accounts')
                        ->where(function ($query) {
                            return $query->where('parent_id', $this->input('parent_id'));
                        }),
                ],

                /* Campo: Descripcion */
                'description' => 'nullable|min:1|max:255|string',

                /* Campo: Id de la cuenta Padre */
                'parent_id' => 'nullable|exists:accounts,id',
            ],

            'PUT', 'PATCH' => [
                /* Campo: 'Codigo' de la cuenta */
                'code' => 'nullable|integer|min:1|unique:accounts,code',

                /* Campo: Naturaleza de la cuenta */
                'nature' => 'nullable|in:debit,credit',

                /* Campo: Nombre de la cuenta */
                'name' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('accounts')
                        ->where('parent_id', $this->input('parent_id'))
                        ->ignore($this->input('id')),
                ],

                /* Campo: Descripcion */
                'description' => 'nullable|min:1|max:255|string',

                /* Campo: Id de la cuenta Padre */
                'parent_id' => 'nullable|exists:accounts,id',
            ]
        };
    }

    public function messages(): array
    {
        return [
            /* Campo: 'Codigo' de la cuenta */
            "code.required" => "El campo 'Codigo' es obligatorio.",
            "code.integer" => "El 'Codigo' debe ser un número entero.",
            "code.min" => "El 'Codigo' debe ser un número positivo (mínimo 1).",
            "code.unique" => "Ya existe una cuenta con este 'Codigo'. Debe ser único.",

            /* Campo: Naturaleza de la cuenta */
            "nature.in" => "La 'Naturaleza' debe ser 'debito' o 'credito'.",

            /* Campo: Nombre de la cuenta */
            "name.required" => "El campo 'Nombre' es obligatorio.",
            "name.alpha_num" => "El 'Nombre' solo puede contener letras y números (sin espacios o caracteres especiales).",
            "name.max" => "El 'Nombre' no debe exceder los :max caracteres.",
            "name.unique" => "Ya existe una cuenta con este 'Nombre' bajo la misma Cuenta Padre",

            /* Campo: Descripcion */
            "description.min" => "La 'Descripcion' debe contener al menos :min carácter.",
            "description.max" => "La 'Descripcion' no debe exceder los :max caracteres.",
            "description.alpha_num" => "La 'Descripcion' solo puede contener letras y números (sin espacios o caracteres especiales).",

            /* Campo: Id de la cuenta Padre */
            "parent_id.exists" => "La Cuenta Padre seleccionada no existe.",
            "parent_id.nullable" => "El campo ID de Cuenta Padre es opcional, pero si se envía, el valor debe ser válido.",
        ];
    }
}
