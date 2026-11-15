<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title', 'code', 'investigation_line_id', 'justification', 'status_id', 
    'section_id', 'tutor_id', 'tutor_request_status_id'
])]
class Project extends Model
{
    use HasFactory;

    public function status(): BelongsTo { 
        return $this->belongsTo(Status::class); 
    }

    public function lateSubmissionRequests()
    {
        return $this->hasMany(LateSubmissionRequest::class);
    }

    public function section(): BelongsTo { 
        return $this->belongsTo(Section::class); 
    }

    public function tutor(): BelongsTo { 
        return $this->belongsTo(User::class, 'tutor_id'); 
    }
    
    public function documents(): HasMany { 
        return $this->hasMany(ProjectDocument::class); 
    }

    public function evaluations(): HasMany { 
        return $this->hasMany(ProjectEvaluation::class); 
    }

    public function students(): HasMany { 
        return $this->hasMany(ProjectStudent::class); 
    }

    public function investigationLine(): BelongsTo { 
        return $this->belongsTo(InvestigationLine::class); 
    }

    public function tutorRequestStatus(): BelongsTo { 
        return $this->belongsTo(TutorRequestStatus::class); 
    }
    
    public function advisor(): BelongsTo
    {
        return $this->belongsTo(SectionTeacher::class, 'section_teacher_id');
    }

    public function tutorLogs()
    {
        return $this->hasMany(TutorLog::class);
    }

    public function invitations()
    {
        return $this->hasMany(ProjectInvitation::class);
    }
}
