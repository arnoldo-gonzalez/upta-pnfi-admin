<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['project_id', 'sender_enrollment_id', 'receiver_enrollment_id', 'status_id'])]
class ProjectInvitation extends Model
{
    use HasFactory;

    public function project() { 
        return $this->belongsTo(Project::class); 
    }
    
    public function sender() { 
        return $this->belongsTo(Enrollment::class, 'sender_enrollment_id'); 
    }
    
    public function receiver() { 
        return $this->belongsTo(Enrollment::class, 'receiver_enrollment_id'); 
    }
    
    public function status() { 
        return $this->belongsTo(Status::class); 
    }
    
}