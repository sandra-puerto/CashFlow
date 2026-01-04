<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThirdParty extends Model
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
    protected $fillable = ['names', 'surnames', 'doc_types_id', 'doc_num', 'email', 'phone'];

    /**
     * Tipo de Documento
    */
    public function doc_number(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'doc_type_id');
    }
}
