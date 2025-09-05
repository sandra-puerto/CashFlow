<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /**
     * Campos autorizados para ser llenados masivamente
    */
    protected $fillable = ["account_id", "flow_id", "datetime", "description", "debit", "credit", "total"];

    /**
     * Cuenta contable asociada a la transacción.
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Transacción origen enlazada a la actual.
     */
    public function parent()
    {
        return $this->belongsTo(Transaction::class, 'flow_id');
    }
}
