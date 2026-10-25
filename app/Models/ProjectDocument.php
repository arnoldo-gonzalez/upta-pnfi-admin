<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'document_type_id', 'user_id', 'file_path'])]
class ProjectDocument extends Model
{
    use HasFactory;

    public function project(): BelongsTo { 
        return $this->belongsTo(Project::class); 
    }
    
    public function type(): BelongsTo { 
        return $this->belongsTo(DocumentType::class, 'document_type_id'); 
    }
    
    public function uploader(): BelongsTo { 
        return $this->belongsTo(User::class, 'user_id'); 
    }
    
}