<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Obligation extends Model
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
    protected $fillable = ['third_party_id', 'transaction_id', 'type', 'total_amount', 'pending_amount', 'expiration_date'];
}
