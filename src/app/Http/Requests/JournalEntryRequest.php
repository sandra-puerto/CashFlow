<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class JournalEntryRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta petición.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la petición según la ruta.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->routeIs('*.index')) {
            return $this->indexRules();
        }

        return $this->storeRules();
    }

    /**
     * Reglas de validación para las consultas de listado.
     *
     * @return array<string, mixed>
     */
    protected function indexRules(): array
    {
        return [
            'per_page'  => ['nullable', 'integer', 'min:1', 'max:100'],
            'date_from' => ['nullable', 'date', 'date_format:Y-m-d'],
            'date_to'   => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ];
    }

    /**
     * Reglas de validación para la creación de asientos contables.
     *
     * @return array<string, mixed>
     */
    protected function storeRules(): array
    {
        return [
            'datetime'             => ['required', 'date', 'date_format:Y-m-d H:i:s'],
            'concept'              => ['required', 'string', 'max:255'],
            'type'                 => ['nullable', 'string', 'in:diario,apertura,cierre,ajuste'],
            'lines'                => ['required', 'array', 'min:2'],
            'lines.*.account_code' => ['required', 'string', 'exists:accounts,code'],
            'lines.*.description'  => ['nullable', 'string', 'max:255'],
            'lines.*.debit'        => ['required_without:lines.*.credit', 'nullable', 'numeric', 'min:0'],
            'lines.*.credit'       => ['required_without:lines.*.debit', 'nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Obtiene los mensajes de error personalizados para las reglas de validación.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'datetime.required'             => 'La fecha y hora del asiento contable son obligatorias.',
            'datetime.date'                 => 'La fecha y hora deben tener un formato válido.',
            'datetime.date_format'          => 'La fecha y hora deben tener el formato AAAA-MM-DD HH:MM:SS.',
            'concept.required'              => 'El concepto del asiento es obligatorio.',
            'concept.max'                   => 'El concepto no debe exceder los 255 caracteres.',
            'type.in'                       => 'El tipo de asiento seleccionado no es válido.',
            'lines.required'                => 'El asiento contable debe contener líneas de transacción.',
            'lines.array'                   => 'Las líneas deben estar estructuradas en un arreglo.',
            'lines.min'                     => 'El asiento contable debe afectar al menos a dos cuentas (Partida Doble).',
            'lines.*.account_code.required' => 'La cuenta contable es obligatoria en cada línea.',
            'lines.*.account_code.exists'   => 'La cuenta contable especificada no existe en el PUC.',
            'lines.*.debit.required_without'  => 'Cada línea debe contener un valor en débito o en crédito.',
            'lines.*.credit.required_without' => 'Cada línea debe contener un valor en débito o en crédito.',
            'lines.*.debit.numeric'           => 'El valor del débito debe ser numérico.',
            'lines.*.credit.numeric'          => 'El valor del crédito debe ser numérico.',
        ];
    }

    /**
     * Obtiene los datos validados y traduce de forma transparente los códigos de cuenta a IDs internos.
     *
     * @param string|null $key
     * @param mixed $default
     * @return array
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        if (array_key_exists('lines', $data) && is_array($data['lines'])) {
            $codes = array_column($data['lines'], 'account_code');

            $accountIdsByCode = Account::whereIn('code', $codes)
                ->pluck('id', 'code');

            $data['lines'] = array_map(function (array $line) use ($accountIdsByCode) {
                $accountCode = $line['account_code'] ?? null;
                unset($line['account_code']);

                $line['account_id'] = $accountCode ? ($accountIdsByCode[$accountCode] ?? null) : null;

                return $line;
            }, $data['lines']);
        }

        return $data;
    }
}