<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'section_id', 'is_active','completed_at'])]
class Enrollment extends Model
{

    protected $casts = [
        'is_active' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // Una inscripción pertenece a un estudiante
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Una inscripción pertenece a una sección
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}