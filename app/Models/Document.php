<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    public const TIPO_INSTITUCIONAL = 'institucional';

    public const TIPO_SILABO = 'silabo';

    public const TIPO_CALIDAD = 'calidad';

    protected $fillable = [
        'title',
        'document_type',
        'section',
        'periodo_academico_id',
        'ciclo_academico',
        'description',
        'drive_url',
        'publication_date',
    ];

    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
            'ciclo_academico' => 'integer',
        ];
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class);
    }

    public function getDriveFileIdAttribute(): ?string
    {
        $url = $this->drive_url;

        if (preg_match('~/d/([a-zA-Z0-9_-]+)~', $url, $matches)) {
            return $matches[1];
        }

        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query ?: '', $parameters);

        return $parameters['id'] ?? null;
    }

    public function getPreviewUrlAttribute(): string
    {
        return $this->drive_file_id
            ? "https://drive.google.com/file/d/{$this->drive_file_id}/preview"
            : $this->drive_url;
    }

    public function getIsDriveFolderAttribute(): bool
    {
        $path = parse_url($this->drive_url, PHP_URL_PATH);

        return is_string($path) && str_contains($path, '/folders/');
    }

    public function getExternalUrlAttribute(): string
    {
        return $this->drive_file_id
            ? "https://drive.google.com/file/d/{$this->drive_file_id}/view"
            : $this->drive_url;
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->document_type) {
            self::TIPO_SILABO => 'Sílabo',
            self::TIPO_CALIDAD => 'Documento de calidad',
            default => 'Documento institucional',
        };
    }
}
