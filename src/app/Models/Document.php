<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasUlids;

    /**
     * Indica que la llave primaria no es autoincrementable.
     *
     * @var bool
    */
    public $incrementing = false;

    /**
     * Tipo de dato de la llave primaria (ULID).
     *
     * @var string
    */
    protected $keyType = 'string';

    /**
     * Campos autorizados para ser llenados de forma masiva.
     *
     * @var array<int, string>
    */
    protected $fillable = [
        'journal_entry_id',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
        'type',
        'metadata',
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
     * Atributos dinámicos adjuntados automáticamente al serializar (JSON / API).
     *
     * @var array<int, string>
    */
    protected $appends = ['formatted_size', 'url'];

    /**
     * Atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
    */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'file_size' => 'integer',
        ];
    }

    // --------------------------------------------------------------
    // Relaciones
    // --------------------------------------------------------------

    /**
     * Obtiene el asiento contable al que pertenece este soporte.
    */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Obtiene el usuario que subió el documento.
    */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtiene el usuario que modificó por última vez el documento.
    */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // --------------------------------------------------------------
    // Accessors & Helpers
    // --------------------------------------------------------------

    /**
     * Obtiene la URL de descarga o visualización del archivo a través de Storage.
    */
    public function getUrlAttribute(): ?string
    {
        if (empty($this->file_path)) {
            return null;
        }

        return Storage::url($this->file_path);
    }

    /**
     * Obtiene el tamaño del archivo convertido a un formato legible (KB, MB, GB).
    */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size ?? 0;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
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