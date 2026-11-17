<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['project_id', 'document_type_id', 'user_id', 'file_path', 'submission_number', 'protocol_template_id'])]
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

    public function protocolTemplate(): BelongsTo {
        return $this->belongsTo(ProtocolTemplate::class, 'protocol_template_id');
    }

    public function answers(): HasMany {
        return $this->hasMany(ProjectProtocolAnswer::class);
    }

    public function fieldCorrections(): HasMany {
        return $this->hasMany(ProjectProtocolFieldCorrection::class);
    }
    
}
