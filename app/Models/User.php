<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password','credential', 'phone', 'status_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Un estudiante puede haber pasado por varias inscripciones (alguien de 3 trayecto paso por el trayecto 1 y 2)
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Un docente puede tener muchas secciones asignadas.
     */
    public function taughtSections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'section_teacher')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    // Proyectos donde este docente es tutor
    public function tutoredProjects()
    {
        return $this->hasMany(Project::class, 'tutor_id');
    }

    // Evaluaciones asignadas a este docente
    public function assignedEvaluations()
    {
        return $this->hasMany(ProjectEvaluation::class, 'user_id');
    }

    // Documentos subidos por este usuario
    public function uploadedDocuments()
    {
        return $this->hasMany(ProjectDocument::class, 'user_id');
    }
}
