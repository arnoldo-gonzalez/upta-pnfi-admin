<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'trayecto_id',
    'max_team_size',
    'period',
    'is_active'
])]
class Section extends Model
{
    use HasFactory;

    public function trayecto(): BelongsTo
    {
        return $this->belongsTo(Trayecto::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'section_teacher')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function activeTeacher()
    {
        return $this->belongsToMany(User::class, 'section_teacher')
            ->wherePivot('is_active', true)
            ->withPivot('id', 'is_active')
        ->withTimestamps();
    }
}