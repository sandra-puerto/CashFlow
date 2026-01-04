<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ObligationMovement extends Model
{
    use HasUuids;

    /**
     * Indica que la PK no es autoincrementable
    */
    public $incrementing = false;

    /**
     * Indica que la PK es string, no integer
    */
    protected $keyType = 'string';

    /**
     * Campos autorizados para ser llenados masivamente
    */
    protected $fillable = ['obligation_id', 'transaction_id', 'type', 'description'];
}
