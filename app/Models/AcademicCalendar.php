<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_period',
        'activities_schedule',
    ];

    protected function casts(): array
    {
        return ['activities_schedule' => 'array'];
    }
}
