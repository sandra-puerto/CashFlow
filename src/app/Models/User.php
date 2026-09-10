<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Campos autorizados para ser llenados de forma masiva.
     *
     * @var array<int, string>
    */
    protected $fillable = [
        'document_type_id',
        'document_number',
        'name',
        'email',
        'password',
        'created_by',
        'updated_by',
    ];

    /**
     * Campos ocultos al momento de serializar el modelo.
     *
     * @var array<int, string>
    */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    /**
     * Atributos dinámicos adjuntados al serializar el modelo (JSON/API).
     *
     * @var array<int, string>
    */
    protected $appends = ['full_document'];

    /**
     * Atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
    */
    protected function casts(): array
    {
        return [
            'document_type_id' => 'integer',
=======
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
>>>>>>> fbf79995eb7e323766be37cda723484187a892a2
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
<<<<<<< HEAD

    // --------------------------------------------------------------
    // Relaciones
    // --------------------------------------------------------------

    /**
     * Obtiene el tipo de documento del usuario.
    */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Obtiene el usuario que creó este registro de usuario.
    */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtiene el usuario que modificó por última vez este registro.
    */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Obtiene los asientos contables registrados por este usuario.
    */
    public function journalEntriesCreated(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'created_by');
    }

    // --------------------------------------------------------------
    // Scopes (Filtros de consulta)
    // --------------------------------------------------------------

    /**
     * Scope de búsqueda general por nombre, correo electrónico o número de documento.
    */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $subQuery) use ($term) {
            $subQuery->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('document_number', 'like', "%{$term}%");
        });
    }

    // --------------------------------------------------------------
    // Accessors & Helpers
    // --------------------------------------------------------------

    /**
     * Obtiene el documento de identidad formateado con la abreviatura del tipo (ej: "CC: 12345678").
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
=======
}
>>>>>>> fbf79995eb7e323766be37cda723484187a892a2
