<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class AccountSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Leer el archivo JSON
        $json = File::get(database_path('seeders/data/accounts.json'));

        // Decodificar el JSON a un array asociativo
        $data = json_decode($json, true);

        // Validar si la decodificación fue exitosa
        if (json_last_error() === JSON_ERROR_NONE) {
            $this->createAccounts($data);
        } else {
            throw new \Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }
    }

    /**
     * Crear las cuentas y subcuentas recursivamente.
    */
    private function createAccounts(array $data, ?string $parentId = null, ?string $parentNature = null)
    {
        foreach ($data as $code => $account) {

            // Obtener la naturaleza de la cuenta
            $nature = $account['nature'] ?? $parentNature;

            // Crear u obtener la cuenta padre
            $currentAccount = Account::firstOrCreate(
                ['code' => (int)$code],
                [
                    'code' => (int)$code,
                    'name' => $account['name'],
                    'nature' => $nature,
                    'parent_id' => $parentId
                ]
            );

            // Validar si hay subcuentas y crearlas recursivamente
            if (!empty($account['accounts']) && is_array($account['accounts'])) {
                $this->createAccounts($account['accounts'], $currentAccount->id, $nature);
            }
        }
    }

}
