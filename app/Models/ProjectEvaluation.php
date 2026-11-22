<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['project_id', 'user_id', 'stage_id', 'status_id', 'observations', 'evaluated_at', 'project_document_id'])]
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

    public function document()
    {
        return $this->belongsTo(ProjectDocument::class, 'project_document_id');
    }

    /**
     * Correcciones por campo de esta evaluación específica (histórico).
     * Ahora están ancladas directamente a ProjectEvaluation.
     */
    public function fieldCorrections(): HasMany
    {
        return $this->hasMany(ProjectProtocolFieldCorrection::class, 'project_evaluation_id');
    }

    /**
     * Correcciones por campo asociadas a esta evaluación específica.
     */
    public function getFieldCorrections()
    {
        if (! $this->exists) {
            return collect();
        }

        return $this->fieldCorrections()->pluck('correction', 'placeholder_key')->all();
    }
}
