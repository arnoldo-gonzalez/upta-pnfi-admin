<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'project_id', 'user_id', 'reason', 'status_id', 'approved_by', 'approved_at'
])]
class LateSubmissionRequest extends Model
{

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relación con el estado
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Relación: La solicitud pertenece a un proyecto.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relación: La solicitud fue hecha por un usuario (estudiante).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
