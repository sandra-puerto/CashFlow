<?php

namespace app\DTO;

use Illuminate\Support\Carbon;

/**
 * Data Transfer Object para representar una transacción financiera.
*/
class TransactionDTO {

    public readonly string $account_id;
    public ?string $flowId;
    public readonly Carbon $datetime;
    public readonly string $description;
    public readonly float $debit;
    public readonly float $credit;
    public float $total;

    public function __construct(
        string $account_id,
        ?string $flowId,
        ?Carbon $datetime,
        ?string $description,
        ?float $debit,
        ?float $credit,
        ?float $total
    ) {
        $this->account_id = $account_id;
        $this->flowId = $flowId;
        $this->datetime = $datetime ?? Carbon::now();
        $this->description = $description ?? '';
        $this->debit = $debit ?? 0;
        $this->credit = $credit ?? 0;
        $this->total = $total ?? 0;
    }

    /**
     * Definir el ID de la transacción origen enlazada a la actual.
    */
    public function setFlowId(?string $flowId): void{
        $this->flowId = $flowId;
    }

    /**
     * Definir el total de la transacción.
    */
    public function setTotal($total): void{
        $this->total = $total;
    }

    /**
     * Convertir el DTO a un array asociativo.
    */
    public function toArray(): array
    {
        return [
            'account_id' => $this->account_id,
            'flow_id' => $this->flowId,
            'datetime' => $this->datetime,
            'description' => $this->description,
            'debit' => $this->debit,
            'credit' => $this->credit,
            'total' => $this->total,
        ];
    }

    
}