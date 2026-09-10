<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ObligationMovement extends Model
{
    /**
     * Campos autorizados para ser llenados de forma masiva.
     *
     * @var array<int, string>
    */
    protected $fillable = [
        'obligation_id',
        'journal_entry_id',
        'type',
        'amount',
        'date',
        'description',
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
     * Atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
    */
    protected function casts(): array
    {
        return [
            'obligation_id' => 'integer',
            'journal_entry_id' => 'integer',
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    // --------------------------------------------------------------
    // Relaciones
    // --------------------------------------------------------------

    /**
     * Obtiene la obligación (cuenta por cobrar/pagar) asociada a este movimiento.
    */
    public function obligation(): BelongsTo
    {
        return $this->belongsTo(Obligation::class);
    }

    /**
     * Obtiene el asiento contable en el que se registró este movimiento.
    */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Obtiene el usuario que creó el movimiento.
    */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtiene el usuario que modificó por última vez el movimiento.
    */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // --------------------------------------------------------------
    // Scopes (Filtros de consulta)
    // --------------------------------------------------------------

    /**
     * Scope para obtener los movimientos asociados a una obligación específica.
    */
    public function scopeByObligation(Builder $query, int $obligationId): Builder
    {
        return $query->where('obligation_id', $obligationId);
    }

    /**
     * Scope para filtrar únicamente movimientos de tipo abono/pago.
    */
    public function scopePayments(Builder $query): Builder
    {
        return $query->where('type', 'payment');
    }

    /**
     * Scope para filtrar únicamente notas de ajuste o créditos.
    */
    public function scopeAdjustments(Builder $query): Builder
    {
        return $query->where('type', 'adjustment');
    }

    // --------------------------------------------------------------
    // Auditoría y Ciclo de Vida
    // --------------------------------------------------------------

    /**
     * Boot del modelo para auditoría automática y fecha por defecto.
    */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }

            if (empty($model->date)) {
                $model->date = now();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}