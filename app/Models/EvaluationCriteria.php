<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['thematic_axis_id', 'criteria_type_id', 'name', 'weight_percentage'])]
class EvaluationCriteria extends Model
{

    public function thematicAxis() { 
        return $this->belongsTo(ThematicAxis::class); 
    }
    
    public function type() { 
        return $this->belongsTo(CriteriaType::class, 'criteria_type_id'); 
    }
    
    public function scores() { 
        return $this->hasMany(ProjectEvaluationScore::class); 
    }
}