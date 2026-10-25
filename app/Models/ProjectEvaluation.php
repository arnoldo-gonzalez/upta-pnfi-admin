<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'user_id', 'stage_id', 'status_id', 'observations', 'evaluated_at'])]
class ProjectEvaluation extends Model
{
    use HasFactory;
    
    protected $casts = ['evaluated_at' => 'datetime'];

    public function project(): BelongsTo { 
        return $this->belongsTo(Project::class); 
    }
    
    public function teacher(): BelongsTo { 
        return $this->belongsTo(User::class, 'user_id'); 
    }
    
    public function stage(): BelongsTo { 
        return $this->belongsTo(EvaluationStage::class, 'stage_id'); 
    }
    
    public function status(): BelongsTo { 
        return $this->belongsTo(Status::class); 
    }
    
}