<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curriculum extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'version',
        'approved_at',
    ];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime'];
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CurriculumReview::class);
    }

    public function redesigns(): HasMany
    {
        return $this->hasMany(CurriculumRedesign::class);
    }
}
