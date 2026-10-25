<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'section_id', 'is_active'])]
class SectionTeacher extends Model
{
    use HasFactory;
    
    protected $table = 'section_teacher';

    public function teacher() { 
        return $this->belongsTo(User::class, 'user_id'); 
    }
    
    public function section() { 
        return $this->belongsTo(Section::class); 
    }
    
    public function projects() { 
        return $this->hasMany(Project::class); 
    }
    
}