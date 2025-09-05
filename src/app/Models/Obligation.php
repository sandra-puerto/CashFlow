<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Obligation extends Model
{
    /**
     * Campos autorizados para ser llenados masivamente
    */
    protected $fillable = ['third_party_id', 'transaction_id', 'type', 'total_amount', 'pending_amount', 'expiration_date'];
}
