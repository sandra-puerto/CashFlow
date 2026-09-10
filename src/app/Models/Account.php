<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * Class Account
 *
 * Representa una cuenta contable dentro del Plan Único de Cuentas (PUC).
 * Proporciona gestión jerárquica recursiva, control de saldos y 
 * autocalculará de manera transparente campos clave como el nivel, 
 * la naturaleza contable y el identificador de la cuenta padre en 
 * base a la estructura del código contable.
 *
 * IMPORTANTE: los identificadores internos (id, parent_id) nunca se
 * exponen al cliente. Toda comunicación externa (rutas, request, 
 * respuestas JSON) se hace exclusivamente mediante el código contable 
 * (`code` / `parent_code`).
 *
 * @package App\Models
*/
class Account extends Model
{
    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
    */
    protected $fillable = [
        'code',
        'name',
        'nature',
        'level',
        'description',
        'parent_id',
        'is_active',
        'is_primary',
        'current_balance',
        'created_by',
        'updated_by',
    ];

    /**
     * Los atributos que deben ocultarse para la serialización.
     *
     * El id interno y el parent_id nunca deben llegar al cliente:
     * la API se comunica exclusivamente mediante códigos contables.
     *
     * @var array<int, string>
    */
    protected $hidden = [
        'id',
        'parent_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Atributos calculados que se agregan automáticamente a la 
     * serialización del modelo.
     *
     * @var array<int, string>
    */
    protected $appends = [
        'parent_code',
    ];

    /**
     * Obtiene los casts de atributos nativos del modelo.
     *
     * @return array<string, string>
    */
    protected function casts(): array
    {
        return [
            'level'           => 'integer',
            'is_active'       => 'boolean',
            'is_primary'      => 'boolean',
            'current_balance' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Configuración de Route Model Binding
    |--------------------------------------------------------------------------
    */

    /**
     * Define 'code' como la clave usada para el route model binding
     * implícito, de modo que las rutas reciban y resuelvan cuentas
     * mediante su código contable en lugar del id numérico interno.
     *
     * Ej: GET /api/accounts/{account} -> {account} = "112005"
     *
     * @return string
    */
    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * Determina si la cuenta es una cuenta primaria (base) del PUC.
     *
     * Se apoya en el flag `is_primary` de la base de datos y, como
     * respaldo ante datos previos a la marcación masiva, en el nivel
     * jerárquico 1 o la ausencia de cuenta padre.
     *
     * @return bool
     */
    public function isPrimaryAccount(): bool
    {
        return $this->is_primary || $this->level === 1 || is_null($this->parent_id);
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones Eloquent
    |--------------------------------------------------------------------------
    */

    /**
     * Obtiene la cuenta padre asociada (relación jerárquica recursiva).
     *
     * @return BelongsTo
    */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * Obtiene las cuentas hijas asociadas (relación jerárquica recursiva).
     *
     * @return HasMany
    */
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Obtiene las líneas de asientos contables vinculadas a esta cuenta.
     *
     * @return HasMany
    */
    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Atributos Calculados (Accessors)
    |--------------------------------------------------------------------------
    */

    /**
     * Expone el código de la cuenta padre en lugar de su id interno.
     * Es la contraparte pública y legible de parent_id.
     *
     * Se resuelve con una consulta escalar directa (no mediante la
     * relación parent()) a propósito: acceder a $this->parent dejaría
     * esa relación cargada en el modelo, y como la cuenta padre también
     * tiene este mismo accessor, la serialización terminaría anidando
     * toda la cadena de ancestros hasta la raíz.
     *
     * @return Attribute
    */
    protected function parentCode(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->parent_id
                ? self::where('id', $this->parent_id)->value('code')
                : null,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Lógica de Saldos
    |--------------------------------------------------------------------------
    */

    /**
     * Aplica un movimiento de débito/crédito al saldo actual de la cuenta
     * y persiste el nuevo saldo.
     *
     * Regla contable: en cuentas de naturaleza 'debit' (Activos, Gastos,
     * Costos) el saldo aumenta con débitos y disminuye con créditos.
     * En cuentas de naturaleza 'credit' (Pasivos, Patrimonio, Ingresos)
     * ocurre lo contrario.
     *
     * @param  float $debit
     * @param  float $credit
     * @return float El nuevo saldo de la cuenta, ya persistido.
    */
    public function applyMovement(float $debit, float $credit): float
    {
        $delta = $this->nature === 'debit'
            ? ($debit - $credit)
            : ($credit - $debit);

        $this->current_balance = round((float) $this->current_balance + $delta, 2);
        $this->save();

        return (float) $this->current_balance;
    }

    /*
    |--------------------------------------------------------------------------
    | Ciclo de Vida del Modelo (Boot & Lógica de Negocio)
    |--------------------------------------------------------------------------
    */

    /**
     * Configura los eventos del ciclo de vida del modelo para auditoría 
     * automática y resolución inteligente de jerarquías.
     *
     * @return void
    */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            // Asignación automática de auditoría para el creador
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }

            // Autodetección de parent_id mediante el análisis de la estructura del código PUC
            if (empty($model->parent_id) && strlen($model->code) > 1) {
                $parentCode = self::findPotentialParentCode($model->code);
                if ($parentCode) {
                    $parentAccount = self::where('code', $parentCode)->first();
                    if ($parentAccount) {
                        $model->parent_id = $parentAccount->id;
                    }
                }
            }

            self::applyHierarchy($model);
        });

        static::updating(function ($model) {
            // Asignación automática de auditoría para el modificador
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            // Si el padre cambió en esta actualización, se recalculan
            // nivel y naturaleza contable para mantener la coherencia
            // jerárquica (antes solo ocurría al crear la cuenta).
            if ($model->isDirty('parent_id')) {
                self::applyHierarchy($model, forceRecalculation: true);
            }
        });

        static::saving(function ($model) {
            // Previene ciclos en la jerarquía: una cuenta no puede
            // terminar siendo su propio ancestro.
            if (!empty($model->parent_id) && self::wouldCreateCycle($model)) {
                throw new \InvalidArgumentException(
                    'La cuenta padre seleccionada generaría un ciclo en la jerarquía del PUC.'
                );
            }
        });
    }

    /**
     * Calcula nivel y naturaleza contable de una cuenta en función de
     * su padre actual (o de las reglas base si es cuenta raíz).
     *
     * @param  Account $model
     * @param  bool    $forceRecalculation  Si es true, recalcula aunque
     *                                       el campo ya tenga un valor
     *                                       (usado al reasignar el padre
     *                                       en una actualización).
     * @return void
    */
    private static function applyHierarchy(self $model, bool $forceRecalculation = false): void
    {
        if (!empty($model->parent_id)) {
            $parent = self::find($model->parent_id);
            if ($parent) {
                $model->level = $forceRecalculation ? ($parent->level + 1) : ($model->level ?? ($parent->level + 1));
                $model->nature = $forceRecalculation ? $parent->nature : ($model->nature ?? $parent->nature);
            }
        } else {
            $model->level = $forceRecalculation ? 1 : ($model->level ?? 1);

            if ($forceRecalculation || empty($model->nature)) {
                $firstDigit = substr($model->code, 0, 1);
                // Regla contable estándar: 
                // 1 (Activos), 5 (Gastos), 6 (Costos) -> Débito
                // 2 (Pasivos), 3 (Patrimonio), 4 (Ingresos) -> Crédito
                $model->nature = in_array($firstDigit, ['1', '5', '6']) ? 'debit' : 'credit';
            }
        }
    }

    /**
     * Verifica si asignar el parent_id actual del modelo generaría un
     * ciclo en la jerarquía (la cuenta como su propio ancestro).
     *
     * @param  Account $model
     * @return bool
    */
    private static function wouldCreateCycle(self $model): bool
    {
        // Una cuenta no puede ser padre de sí misma
        if ($model->exists && $model->parent_id === $model->id) {
            return true;
        }

        // Recorre la cadena de ancestros del padre propuesto: si en
        // algún punto se encuentra el propio id de la cuenta, hay ciclo.
        $currentParentId = $model->parent_id;
        $visited = [];

        while (!empty($currentParentId)) {
            if ($model->exists && $currentParentId === $model->id) {
                return true;
            }

            // Protección adicional contra ciclos ya existentes en datos corruptos
            if (in_array($currentParentId, $visited, true)) {
                return true;
            }
            $visited[] = $currentParentId;

            $currentParentId = self::where('id', $currentParentId)->value('parent_id');
        }

        return false;
    }

    /**
     * Deduce el código del nivel superior inmediato según los estándares PUC.
     *
     * @param string $code
     * @return string|null
    */
    private static function findPotentialParentCode(string $code): ?string
    {
        $length = strlen($code);

        if ($length === 8) return substr($code, 0, 6);
        if ($length === 6) return substr($code, 0, 4);
        if ($length === 4) return substr($code, 0, 2);
        if ($length === 2) return substr($code, 0, 1);

        return null;
    }
}