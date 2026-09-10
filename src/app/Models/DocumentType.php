<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class DocumentType extends Model
{
    /**
     * Campos autorizados para ser llenados de forma masiva.
     *
     * @var array<int, string>
    */
    protected $fillable = [
        'name',
        'abbreviation',
        'created_by',
        'updated_by',
    ];

    /**
     * Campos ocultos al momento de serializar el modelo.
     *
     * @var array<int, string>
    */
    protected $hidden = ['created_at', 'updated_at'];

    // --------------------------------------------------------------
    // Relaciones
    // --------------------------------------------------------------

    /**
     * Obtiene los usuarios registrados con este tipo de documento.
    */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Obtiene los terceros (clientes/proveedores) registrados con este tipo de documento.
    */
    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class);
    }

    /**
     * Obtiene el usuario que creó este tipo de documento.
    */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtiene el usuario que modificó por última vez este tipo de documento.
    */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // --------------------------------------------------------------
    // Accessors & Scopes
    // --------------------------------------------------------------

    /**
     * Obtiene la abreviatura y el nombre combinados (ej: "CC - Cédula de Ciudadanía").
    */
    public function getFormattedNameAttribute(): string
    {
        return "{$this->abbreviation} - {$this->name}";
    }

    /**
     * Scope para realizar búsquedas rápidas por abreviatura (ej: 'CC', 'NIT').
    */
    public function scopeByAbbreviation(Builder $query, string $abbreviation): Builder
    {
        return $query->where('abbreviation', strtoupper($abbreviation));
    }

    // --------------------------------------------------------------
    // Auditoría automática
    // --------------------------------------------------------------

    /**
     * Boot del modelo para auditoría automática (created_by, updated_by).
    */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}