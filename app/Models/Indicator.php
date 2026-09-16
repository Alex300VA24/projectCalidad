<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'area',
        'objective',
        'unit',
        'target_value',
        'current_value',
        'frequency',
        'responsible',
        'period',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'current_value' => 'decimal:2',
        ];
    }

    public function getProgressAttribute(): float
    {
        if ((float) $this->target_value === 0.0) {
            return 0;
        }

        return round(min(((float) $this->current_value / (float) $this->target_value) * 100, 100), 1);
    }

    public function getStatusAttribute(): string
    {
        return match (true) {
            $this->progress >= 90 => 'Cumplido',
            $this->progress >= 70 => 'En riesgo',
            default => 'Crítico',
        };
    }

    public function getStatusKeyAttribute(): string
    {
        return match ($this->status) {
            'Cumplido' => 'success',
            'En riesgo' => 'warning',
            default => 'danger',
        };
    }
}
