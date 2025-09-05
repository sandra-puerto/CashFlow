<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObligationMovement extends Model
{
    /**
     * Campos autorizados para ser llenados masivamente
    */
    protected $fillable = ['obligation_id', 'transaction_id', 'type', 'description'];
}
