<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Obligation extends Model
{
    /**
     * Campos autorizados para ser llenados de forma masiva.
     *
     * @var array<int, string>
    */
    protected $fillable = [
        'type',
        'partner_id',
        'document_number',
        'issue_date',
        'due_date',
        'total_amount',
        'balance',
        'status',
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
     * Atributos dinámicos adjuntados al serializar el modelo.
     *
     * @var array<int, string>
    */
    protected $appends = ['paid_amount', 'is_overdue'];

    /**
     * Atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
    */
    protected function casts(): array
    {
        return [
            'partner_id' => 'integer',
            'issue_date' => 'date',
            'due_date' => 'date',
            'total_amount' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    // --------------------------------------------------------------
    // Relaciones
    // --------------------------------------------------------------

    /**
     * Obtiene el tercero (cliente o proveedor) asociado a esta obligación.
    */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Obtiene el historial de movimientos o abonos de esta obligación.
    */
    public function movements(): HasMany
    {
        return $this->hasMany(ObligationMovement::class);
    }

    /**
     * Obtiene el usuario que creó la obligación.
    */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtiene el usuario que modificó por última vez la obligación.
    */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // --------------------------------------------------------------
    // Scopes (Filtros de consulta)
    // --------------------------------------------------------------

    /**
     * Scope para filtrar cuentas por cobrar (CxC).
    */
    public function scopeReceivables(Builder $query): Builder
    {
        return $query->where('type', 'receivable');
    }

    /**
     * Scope para filtrar cuentas por pagar (CxP).
    */
    public function scopePayables(Builder $query): Builder
    {
        return $query->where('type', 'payable');
    }

    /**
     * Scope para obtener únicamente obligaciones pendientes o parciales de pago.
    */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('balance', '>', 0);
    }

    /**
     * Scope para obtener obligaciones totalmente saldadas.
    */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('balance', '<=', 0);
    }

    /**
     * Scope para obtener obligaciones vencidas según la fecha actual.
    */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('balance', '>', 0)
                     ->where('due_date', '<', now()->startOfDay());
    }

    // --------------------------------------------------------------
    // Accessors & Helpers
    // --------------------------------------------------------------

    /**
     * Calcula el monto total abonado/pagado a la fecha.
    */
    public function getPaidAmountAttribute(): float
    {
        return round((float) $this->total_amount - (float) $this->balance, 2);
    }

    /**
     * Determina si la obligación se encuentra vencida.
    */
    public function getIsOverdueAttribute(): bool
    {
        return $this->balance > 0 && $this->due_date && $this->due_date->isPast();
    }

    /**
     * Verifica si la obligación está totalmente pagada.
    */
    public function isFullyPaid(): bool
    {
        return $this->balance <= 0;
    }

    // --------------------------------------------------------------
    // Métodos de Negocio
    // --------------------------------------------------------------

    /**
     * Registra un abono/pago a la obligación y actualiza su saldo y estado.
     *
     * @param float $amount Monto abonado (debe ser mayor a cero)
     * @return float Nuevo saldo de la obligación
     * @throws \InvalidArgumentException Si el monto es inválido o excede el saldo
    */
    public function applyPayment(float $amount): float
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('El monto del abono debe ser mayor a cero.');
        }

        $currentBalance = (float) $this->balance;

        if (round($amount, 2) > round($currentBalance, 2)) {
            throw new \InvalidArgumentException('El monto abonado no puede ser superior al saldo pendiente.');
        }

        $newBalance = round($currentBalance - $amount, 2);

        $this->balance = $newBalance;
        $this->status = $newBalance == 0 ? 'paid' : 'partial';
        $this->save();

        return $newBalance;
    }

    // --------------------------------------------------------------
    // Auditoría y Ciclo de Vida
    // --------------------------------------------------------------

    /**
     * Boot del modelo para auditoría e inicialización de saldos.
    */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            // Auditoría
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }

            // Inicializar saldo igual al monto total si no fue provisto
            if (is_null($model->balance)) {
                $model->balance = $model->total_amount;
            }

            // Asignar estado inicial si no fue provisto
            if (empty($model->status)) {
                $model->status = $model->balance > 0 ? 'pending' : 'paid';
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}