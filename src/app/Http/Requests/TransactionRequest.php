<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
    public function rules()
    {
        return [
            // transactions es obligatorio y debe ser un array
            'transactions' => 'required|array|min:1',

            // Cada transacción
            'transactions.*.account_id' => 'required|uuid|exists:accounts,id',
            'transactions.*.datetime'   => 'required|date_format:Y-m-d\TH:i:s\Z',
            'transactions.*.description'=> 'nullable|string|max:255',
            'transactions.*.debit'      => 'nullable|numeric|min:0',
            'transactions.*.credit'     => 'nullable|numeric|min:0'
        ];
    }


    /**
     * Configuración adicional de validación después de las reglas básicas.
    */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->input('transactions', []) as $index => $transaction) {

                // Validar que al menos credit o debit esté presente
                if (!isset($transaction['credit']) && !isset($transaction['debit'])) {
                    $validator->errors()->add(
                        "transactions.$index",
                        'Debe contener al menos un campo credit o debit'
                    );
                }
            }
        });
    }

    /**
     * Mensajes de error personalizados para las reglas de validación.
    */
    public function messages()
    {
        return [
            // Mensajes para el array de transacciones
            'transactions.required' => 'El campo transactions es obligatorio.',
            'transactions.array' => 'El campo transactions debe ser un array.',
            'transactions.min' => 'Debe haber al menos una transacción.',

            // account_id
            'transactions.*.account_id.required' => 'El account_id de cada transacción es obligatorio.',
            'transactions.*.account_id.uuid' => 'El account_id debe ser un UUID válido.',
            'transactions.*.account_id.exists' => 'La cuenta :value no esta registrada.',

            // datetime
            'transactions.*.datetime.required' => 'La fecha y hora de cada transacción es obligatoria.',
            'transactions.*.datetime.date_format' => 'La fecha y hora debe estar en formato ISO 8601, ejemplo: 2024-06-01T10:00:00Z.',

            // description
            'transactions.*.description.string' => 'La descripción debe ser texto.',
            'transactions.*.description.max' => 'La descripción no puede tener más de 255 caracteres.',

            // debit
            'transactions.*.debit.numeric' => 'El campo debit debe ser un número.',
            'transactions.*.debit.min' => 'El campo debit no puede ser negativo.',

            // credit
            'transactions.*.credit.numeric' => 'El campo credit debe ser un número.',
            'transactions.*.credit.min' => 'El campo credit no puede ser negativo.',
        ];
    }

}
