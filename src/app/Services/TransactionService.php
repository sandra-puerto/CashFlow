<?php

namespace App\Services;

use App\DTO\AccountBalanceDTO;
use App\DTO\InternalResponseDTO;
use App\DTO\TransactionDTO;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    /**
     * Verifica si el conjunto de transacciones está balanceado.
     */
    private function isBalanced(TransactionDTO ...$transactions): InternalResponseDTO
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($transactions as $transaction) {
            $totalDebit  += $transaction->debit ?? 0;
            $totalCredit += $transaction->credit ?? 0;
        }

        $balanced = $totalDebit === $totalCredit;

        return new InternalResponseDTO(
            success: $balanced,
            message: $balanced ? null : "Las transacciones no están balanceadas.",
            code: $balanced ? 200 : 400
        );
    }

    /**
     * Obtener el balance actual de una cuenta y su naturaleza.
     *
     * @param int $accountId
     * @return AccountBalanceDTO
     *
     * @throws \Exception Si la cuenta no existe
     */
    private function getLastTransactionByAccount(string $accountId): AccountBalanceDTO
    {
        $account = Account::find($accountId);

        if (!$account) {
            throw new \Exception("Cuenta no encontrada: {$accountId}");
        }

        $lastTransaction = Transaction::where('account_id', $accountId)
            ->orderByDesc('id')
            ->first();

        return new AccountBalanceDTO(
            lastBalance: $lastTransaction?->balance ?? 0,
            nature: $account->nature
        );
    }

    /**
     * Calcular el nuevo balance post transacción según la naturaleza de la cuenta.
     */
    private function calculateNewBalance(AccountBalanceDTO $accountInfo, ?float $debit, ?float $credit): float
    {
        return match($accountInfo->nature) {
            'debit' => $accountInfo->lastBalance + ($debit ?? 0) - ($credit ?? 0),
            'credit' => $accountInfo->lastBalance - ($debit ?? 0) + ($credit ?? 0),
            default => $accountInfo->lastBalance + ($debit ?? 0) - ($credit ?? 0)
        };
    }

    /**
     * Ejecuta un conjunto de transacciones ya validadas y con DTOs.
     */
    public function execute(TransactionDTO ...$transactions): InternalResponseDTO
    {
        try {

            // Validar balance global
            $balanceCheck = $this->isBalanced(...$transactions);
            if (!$balanceCheck->success) {
                return $balanceCheck;
            }

            $flow_id = null;

            foreach ($transactions as $dto) {

                // Asignar flow_id de manera consistente
                $dto->setFlowId($flow_id);

                // Crear registro en BD
                $transaction = Transaction::create($dto->toArray());

                // Actualizar flow_id para la siguiente transacción
                $flow_id = $transaction->id;
            }

            return new InternalResponseDTO(
                success: true,
                message: "Transacciones ejecutadas exitosamente.",
                code: 200
            );

        } catch (\Exception $e) {

            Log::error('Error ejecutando transacciones', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $message = env('APP_DEBUG', true)
                ? "Error al ejecutar las transacciones: {$e->getMessage()}"
                : "Error al ejecutar las transacciones.";

            return new InternalResponseDTO(
                success: false,
                message: $message,
                data: [],
                code: 500
            );
        }
    }

    /**
     * Orquestador del asiento contable (journal entry)
     *
     * @param array<int, array<string, mixed>> $transactionsData
     */
    public function executeJournalEntry(array $transactionsData)
    {
        try {

            // Convertir array de datos en DTOs
            $transactions = array_map(function ($data) {

                // Obtener información contable de la cuenta
                $accountInfo = $this->getLastTransactionByAccount((string)$data['account_id']);

                // Calcular nuevo saldo
                $total = $this->calculateNewBalance(
                    accountInfo: $accountInfo,
                    debit: $data['debit'] ?? null,
                    credit: $data['credit'] ?? null
                );

                return new TransactionDTO(
                    account_id: (string)$data['account_id'],
                    flowId: $data['flow_id'] ?? null,
                    datetime: isset($data['datetime']) ? Carbon::parse($data['datetime']) : now(),
                    description: $data['description'] ?? null,
                    debit: $data['debit'] ?? null,
                    credit: $data['credit'] ?? null,
                    total: $total
                );
            }, $transactionsData);

            // Ejecutar las transacciones
            return $this->execute(...$transactions);
            
        } catch (\Exception $e) {

            Log::error('Error ejecutando asiento contable', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $message = env('APP_DEBUG', true)
                ? "Error al ejecutar el asiento contable: {$e->getMessage()}"
                : "Error al ejecutar el asiento contable.";

            return new InternalResponseDTO(
                success: false,
                message: $message,
                data: [],
                code: 500
            );
        }
    }
}