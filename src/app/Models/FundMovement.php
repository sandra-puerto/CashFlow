<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundMovement extends Model
{
    /**
     * Campos autorizados para ser llenados masivamente
    */
    protected $fillable = ['transaction_id', 'type', 'descripcion', 'total'];
}
