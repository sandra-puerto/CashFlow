<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    /**
     * Campos autorizados para ser llenados masivamente
    */
    protected $fillable = ['name', 'abbreviation'];
}
