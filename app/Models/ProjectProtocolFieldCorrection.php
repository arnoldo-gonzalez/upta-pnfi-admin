<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_document_id', 'placeholder_key', 'correction', 'evaluator_id', 'project_evaluation_id'])]
    class ProjectProtocolFieldCorrection extends Model
    {
        public function projectDocument(): BelongsTo { 
            return $this->belongsTo(ProjectDocument::class); 
        }

        public function projectEvaluation(): BelongsTo { 
            return $this->belongsTo(ProjectEvaluation::class, 'project_evaluation_id'); 
        }

        public function evaluator(): BelongsTo { 
            return $this->belongsTo(User::class, 'evaluator_id'); 
        }
    }
