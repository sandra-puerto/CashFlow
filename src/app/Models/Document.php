<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    /**
     * Campos autorizados para ser llenados masivamente
    */
    protected $fillable = ['transaction_id', 'type'];

    /**
     * Transaccion asociada al documento
    */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
