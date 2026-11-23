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

#[Fillable(['name', 'email', 'password','credential', 'phone', 'specialty', 'availability', 'jury_available', 'status_id', 'max_tutored_projects', 'security_question', 'security_answer'])]
#[Hidden(['password', 'remember_token', 'security_answer'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Todo usuario nuevo nace pendiente de aprobación salvo que se indique
     * otro estado explícitamente (docentes, importaciones, etc.).
     */
    protected $attributes = [
        'status_id' => 1,
    ];

    public function isActive(): bool
    {
        return $this->status_id === \App\Enums\UserStatus::ACTIVE->value;
    }

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
            'security_answer' => 'hashed',
            'jury_available' => 'boolean',
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

    public function availableTutorSlots()
    {
        $currentLoad = $this->tutoredProjects()
            ->where('tutor_request_status_id', \App\Enums\TutorRequestStatus::ACCEPTED->value)
            ->count();
            
        return $this->max_tutored_projects - $currentLoad;
    }
}
