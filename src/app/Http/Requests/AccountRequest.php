<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class AccountRequest
 * 
 * Valida los datos mínimos necesarios enviados por el cliente para 
 * la gestión de cuentas contables en el PUC.
*/
class AccountRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
    */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación aplicables.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
    */
    public function rules(): array
    {
        $account = $this->route('account');
        $accountId = $account instanceof Account ? $account->id : Account::where('code', $account)->value('id');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('accounts', 'code')->ignore($accountId),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            /**
             * Identificador público de la cuenta padre. El cliente jamás
             * envía parent_id; ese valor se resuelve internamente a
             * partir de este código en validated().
            */
            'parent_code' => [
                'nullable',
                'string',
                'exists:accounts,code',
            ],
            
            // Opcionales: El backend los autocalcula si el cliente no los envía
            'nature'          => ['sometimes', 'string', 'in:debit,credit'],
            'level'           => ['sometimes', 'integer', 'min:1'],
            'description'     => ['nullable', 'string'],
            'is_active'       => ['sometimes', 'boolean'],
            'current_balance' => ['sometimes', 'numeric'],
        ];
    }

    /**
     * Mensajes de error personalizados.
    */
    public function messages(): array
    {
        return [
            'code.required'       => 'El código de la cuenta es obligatorio.',
            'code.unique'         => 'El código de cuenta ya se encuentra registrado.',
            'name.required'       => 'El nombre de la cuenta es obligatorio.',
            'parent_code.exists'  => 'La cuenta padre especificada no existe.',
        ];
    }

    /**
     * Reglas de validación adicionales.
     *
     * @return void
    */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $parentCode = $this->input('parent_code');
            $ownCode    = $this->input('code');

            // Una cuenta no puede declararse a sí misma como su propio padre.
            if ($parentCode && $ownCode && $parentCode === $ownCode) {
                $validator->errors()->add(
                    'parent_code',
                    'Una cuenta no puede ser su propia cuenta padre.'
                );
            }
        });
    }

    /**
     * Obtiene los datos validados, traduciendo 'parent_code' al
     * 'parent_id' interno de forma transparente para el controlador.
     *
     * @param  string|null $key
     * @param  mixed       $default
     * @return array<string, mixed>
    */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        if (array_key_exists('parent_code', $data)) {
            $parentCode = $data['parent_code'];
            unset($data['parent_code']);

            $data['parent_id'] = $parentCode
                ? Account::where('code', $parentCode)->value('id')
                : null;
        }

        return $data;
    }
}