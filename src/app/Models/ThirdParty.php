<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThirdParty extends Model
{
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
