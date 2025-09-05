<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    /**
     * Campos autorizados para ser llenados de forma masiva
    */
    protected $fillable = ["type", "code", "name", "description", "parent_id"];

    /**
     * Retornar los datos de la cuenta padre
     * 
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function parent_account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }
}
