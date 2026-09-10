<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    /**
     * Campos autorizados para asignación masiva.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'line_number',
        'description',
        'debit',
        'credit',
        'previous_balance',
        'new_balance',
    ];

    /**
     * Campos ocultos en la serialización para no exponer identificadores internos.
     *
     * @var array<int, string>
     */
    protected $hidden = ['account_id', 'created_at', 'updated_at'];

    /**
     * Atributos dinámicos adjuntados al JSON de salida.
     *
     * @var array<int, string>
     */
    protected $appends = ['account_code'];

    /**
     * Conversiones automáticas de tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'journal_entry_id' => 'integer',
            'line_number'      => 'integer',
            'debit'            => 'decimal:2',
            'credit'           => 'decimal:2',
            'previous_balance' => 'decimal:2',
            'new_balance'      => 'decimal:2',
        ];
    }

    /**
     * Relación con el asiento contable cabecera.
     *
     * @return BelongsTo
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Relación con la cuenta contable afectada del PUC.
     *
     * @return BelongsTo
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Obtiene el código de la cuenta asociada para exposición pública transparente.
     *
     * @return string|null
     */
    public function getAccountCodeAttribute(): ?string
    {
        return $this->relationLoaded('account') 
            ? $this->account?->code 
            : $this->account()->value('code');
    }

    /**
     * Scope para filtrar líneas que correspondan a débitos.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDebits(Builder $query): Builder
    {
        return $query->where('debit', '>', 0);
    }

    /**
     * Scope para filtrar líneas que correspondan a créditos.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCredits(Builder $query): Builder
    {
        return $query->where('credit', '>', 0);
    }

    /**
     * Scope para filtrar líneas por cuenta contable específica.
     *
     * @param Builder $query
     * @param int $accountId
     * @return Builder
     */
    public function scopeByAccount(Builder $query, int $accountId): Builder
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Obtiene el valor absoluto del movimiento monetario de la línea.
     *
     * @return float
     */
    public function getAmountAttribute(): float
    {
        return (float) ($this->debit > 0 ? $this->debit : $this->credit);
    }

    /**
     * Obtiene el tipo de movimiento aplicado ('debit' o 'credit').
     *
     * @return string
     */
    public function getMovementTypeAttribute(): string
    {
        return $this->debit > 0 ? 'debit' : 'credit';
    }
}