<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['project_id', 'evaluation_criteria_id', 'score'])]
class ProjectEvaluationScore extends Model
{
    use HasFactory;

    public function project() { 
        return $this->belongsTo(Project::class); 
    }
    
    public function criteria() { 
        return $this->belongsTo(EvaluationCriteria::class, 'evaluation_criteria_id'); 
    }
}