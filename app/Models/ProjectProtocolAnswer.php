<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_document_id', 'placeholder_key', 'value'])]
class ProjectProtocolAnswer extends Model
{
    public function projectDocument(): BelongsTo { 
        return $this->belongsTo(ProjectDocument::class); 
    }
}
