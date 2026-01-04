<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
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
     * Campos autorizados para ser llenados de forma masiva
    */
    protected $fillable = ['id', 'code', 'name', 'nature', 'parent_id', 'description'];

    /**
     * Campos ocultos al momento de serializar el modelo
    */
    protected $hidden = ['created_at', 'updated_at'];

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
