<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Partner extends Model
{
    /**
     * Campos autorizados para ser llenados de forma masiva.
     *
     * @var array<int, string>
    */
    protected $fillable = [
        'document_type_id',
        'document_number',
        'name',
        'commercial_name',
        'is_customer',
        'is_supplier',
        'is_employee',
        'email',
        'phone',
        'address',
        'tax_regime',
        'created_by',
        'updated_by',
    ];

    /**
     * Campos ocultos al momento de serializar el modelo.
     *
     * @var array<int, string>
    */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Atributos dinámicos adjuntados al serializar el modelo (JSON/API).
     *
     * @var array<int, string>
    */
    protected $appends = ['display_name', 'full_document'];

    /**
     * Atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
    */
    protected function casts(): array
    {
        return [
            'document_type_id' => 'integer',
            'is_customer' => 'boolean',
            'is_supplier' => 'boolean',
            'is_employee' => 'boolean',
        ];
    }

    // --------------------------------------------------------------
    // Relaciones
    // --------------------------------------------------------------

    /**
     * Obtiene el tipo de documento de identidad/fiscal del tercero.
    */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Obtiene las obligaciones (cuentas por cobrar y por pagar) del tercero.
    */
    public function obligations(): HasMany
    {
        return $this->hasMany(Obligation::class);
    }

    /**
     * Obtiene el usuario que creó el registro del tercero.
    */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtiene el usuario que modificó por última vez el registro.
    */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // --------------------------------------------------------------
    // Scopes (Filtros de consulta)
    // --------------------------------------------------------------

    /**
     * Scope para filtrar únicamente clientes.
    */
    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('is_customer', true);
    }

    /**
     * Scope para filtrar únicamente proveedores.
    */
    public function scopeSuppliers(Builder $query): Builder
    {
        return $query->where('is_supplier', true);
    }

    /**
     * Scope para filtrar únicamente empleados.
    */
    public function scopeEmployees(Builder $query): Builder
    {
        return $query->where('is_employee', true);
    }

    /**
     * Scope de búsqueda general por razón social, nombre comercial o número de documento.
    */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $subQuery) use ($term) {
            $subQuery->where('name', 'like', "%{$term}%")
                ->orWhere('commercial_name', 'like', "%{$term}%")
                ->orWhere('document_number', 'like', "%{$term}%");
        });
    }

    // --------------------------------------------------------------
    // Accessors & Helpers
    // --------------------------------------------------------------

    /**
     * Obtiene el nombre preferente para mostrar en pantalla (nombre comercial si existe, o razón social).
    */
    public function getDisplayNameAttribute(): string
    {
        return $this->commercial_name ?: $this->name;
    }

    /**
     * Obtiene la identificación formateada con la abreviatura (ej: "NIT: 900123456").
    */
    public function getFullDocumentAttribute(): string
    {
        $type = $this->documentType ? $this->documentType->abbreviation : 'DOC';
        return "{$type}: {$this->document_number}";
    }

    // --------------------------------------------------------------
    // Auditoría y Ciclo de Vida
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