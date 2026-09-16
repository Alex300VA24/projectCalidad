<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'section',
        'description',
        'drive_url',
        'publication_date',
    ];

    protected function casts(): array
    {
        return ['publication_date' => 'date'];
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

    public function getExternalUrlAttribute(): string
    {
        return $this->drive_file_id
            ? "https://drive.google.com/file/d/{$this->drive_file_id}/view"
            : $this->drive_url;
    }
}
