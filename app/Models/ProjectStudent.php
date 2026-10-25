<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'enrollment_id'])]
class ProjectStudent extends Model
{
    use HasFactory;

    public function project(): BelongsTo { 
        return $this->belongsTo(Project::class); 
    }

    public function enrollment(): BelongsTo { 
        return $this->belongsTo(Enrollment::class); 
    }

}