<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fund extends Model
{
    /**
     * Campos autorizados para ser llenados masivamente
    */
    protected $fillable = ['name', 'descripcion'];
}
