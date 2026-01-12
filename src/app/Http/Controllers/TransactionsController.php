<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Helpers\TransactionDTO;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransactionsController extends Controller
{
    /**
     * Verifica si el conjunto de transacciones está balanceado.
    */
    private function isBalanced(TransactionDTO ...$transactions): bool
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($transactions as $transaction) {
            $totalDebit  += $transaction->debit  ?? 0;
            $totalCredit += $transaction->credit ?? 0;
        }

        return $totalDebit === $totalCredit;
    }

    /**
     * Orquestador de la ejecución de transacciones.
    */
    private function execute(TransactionDTO ...$transactions): JsonResponse
    {

        /**
         * Validar el balanceo de las transacciones
        */
        if (!$this->isBalanced(...$transactions)) {
            return ResponseHelper::badRequest(
                "Las transacciones no están balanceadas."
            );
        }

        $flow_id = null;
        
        foreach ($transactions as $dto) {

            // Asignar flow_id si existe
            if($flow_id !== null){
                $dto->setFlowId($flow_id);
            }

            // Crear el registro desde el DTO
            $transaction = Transaction::create($dto->toArray());

            // Actualizar flow_id para el siguiente
            $flow_id = $transaction->id;
        }

        return ResponseHelper::custom(
            message: "Transacciones procesadas exitosamente.",
            data: [],
            code: 200
        );
    }

    /**
     * Endpoint de transferencia interna.
    */ 
    public function internalTransfer(TransactionRequest $request)
    {
        $transactions = array_map(function($request){
            return new TransactionDTO(
                account_id: $request['account_id'],
                flowId: $request['flow_id'] ?? null,
                datetime: isset($request['datetime']) ? Carbon::parse($request['datetime']) : null,
                description: $request['description'] ?? null,
                debit: $request['debit'] ?? null,
                credit: $request['credit'] ?? null,
                total: null
            );
        }, $request->input('transactions', []));

        return $this->execute(...$transactions);
    }
}
