<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class JournalEntry extends Model
{
    /**
     * Campos autorizados para asignación masiva.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'number',
        'year',
        'datetime',
        'concept',
        'type',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Campos ocultos en la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Atributos calculados agregados a la representación JSON.
     *
     * @var array<int, string>
     */
    protected $appends = ['total_debit', 'total_credit', 'is_balanced'];

    /**
     * Conversiones automáticas de tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'number'   => 'integer',
            'year'     => 'integer',
            'datetime' => 'datetime',
        ];
    }

    /**
     * Relación con las líneas del asiento contable.
     *
     * @return HasMany
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * Relación con documentos adjuntos al asiento.
     *
     * @return HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Relación con movimientos de obligaciones asociadas.
     *
     * @return HasMany
     */
    public function obligationMovements(): HasMany
    {
        return $this->hasMany(ObligationMovement::class);
    }

    /**
     * Usuario que creó el asiento contable.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que modificó por última vez el asiento contable.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope para filtrar asientos por año contable.
     *
     * @param Builder $query
     * @param int $year
     * @return Builder
     */
    public function scopeByYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    /**
     * Scope para filtrar asientos contabilizados.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', 'contabilizado');
    }

    /**
     * Scope para filtrar asientos en borrador.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'borrador');
    }

    /**
     * Scope para filtrar por tipo de asiento.
     *
     * @param Builder $query
     * @param string $type
     * @return Builder
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Calcula el total de débitos del asiento.
     *
     * @return float
     */
    public function getTotalDebitAttribute(): float
    {
        return round((float) $this->lines->sum('debit'), 2);
    }

    /**
     * Calcula el total de créditos del asiento.
     *
     * @return float
     */
    public function getTotalCreditAttribute(): float
    {
        return round((float) $this->lines->sum('credit'), 2);
    }

    /**
     * Determina si el asiento se encuentra balanceado.
     *
     * @return bool
     */
    public function getIsBalancedAttribute(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.001;
    }

    /**
     * Obtiene el número formateado del asiento (Año-Consecutivo).
     *
     * @return string
     */
    public function getFormattedNumberAttribute(): string
    {
        return sprintf('%d-%06d', $this->year, $this->number);
    }

    /**
     * Obtiene el siguiente número consecutivo disponible para un año específico.
     *
     * @param int $year
     * @return int
     */
    public static function getNextNumber(int $year, ?string $type = null): int
    {
        // El consecutivo debe ser único por (año + tipo de comprobante), tal como
        // lo exige la restricción única [number, year, type] de la tabla.
        // Consultar solo por año provocaba números duplicados entre tipos distintos.
        $query = static::where('year', $year);

        if ($type) {
            $query->where('type', $type);
        }

        $last = $query->max('number');

        return $last ? $last + 1 : 1;
    }

    /**
     * Eventos de ciclo de vida del modelo Eloquent.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }

            if (empty($model->year)) {
                $model->year = $model->datetime ? $model->datetime->year : now()->year;
            }

            if (empty($model->number)) {
                $model->number = self::getNextNumber($model->year, $model->type);
            }

            if (empty($model->status)) {
                $model->status = 'borrador';
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}