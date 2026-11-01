<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['project_id', 'user_id', 'meeting_date', 'description', 'file_path', 'status_id', 'teacher_observations'])]
class TutorLog extends Model
{
    use HasFactory;

    protected $casts = [
        'meeting_date' => 'date',
    ];

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}