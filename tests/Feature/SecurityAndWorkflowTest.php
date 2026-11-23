<?php

use App\Enums\UserStatus;
use App\Models\AcademicActivity;
use App\Models\AcademicTerm;
use App\Models\CriteriaType;
use App\Models\DocumentType;
use App\Models\Enrollment;
use App\Models\EvaluationCriteria;
use App\Models\EvaluationStage;
use App\Models\InvestigationLine;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectEvaluation;
use App\Models\ProjectInvitation;
use App\Models\ProjectStudent;
use App\Models\Section;
use App\Models\ThematicAxis;
use App\Models\Trayecto;
use App\Models\TutorRequestStatus;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

function mkTestUser(string $role): User
{
    Role::firstOrCreate(['name' => $role]);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function mkTestBase(): array
{
    $trayecto = Trayecto::create([
        'name' => 'Trayecto 2',
        'number' => 2,
    ]);

    $axis = ThematicAxis::create([
        'name' => 'Eje de prueba',
        'trayecto_id' => $trayecto->id,
    ]);

    $line = InvestigationLine::create([
        'name' => 'Línea de prueba',
        'thematic_axis_id' => $axis->id,
    ]);

    $section = Section::create([
        'name' => 'Sección 1',
        'trayecto_id' => $trayecto->id,
        'max_team_size' => 3,
        'period' => '2026-2027',
        'is_active' => true,
    ]);

    TutorRequestStatus::firstOrCreate(['id' => 1, 'name' => 'sin_solicitud']);
    TutorRequestStatus::firstOrCreate(['id' => 2, 'name' => 'pendiente']);
    TutorRequestStatus::firstOrCreate(['id' => 3, 'name' => 'aceptada']);
    TutorRequestStatus::firstOrCreate(['id' => 4, 'name' => 'rechazada']);

    return [$section, $line, $axis, $trayecto];
}

function mkTestEnrollment(User $student, Section $section): Enrollment
{
    return Enrollment::create([
        'user_id' => $student->id,
        'section_id' => $section->id,
        'is_active' => true,
    ]);
}

function mkTestProject(Enrollment $enrollment, Section $section, InvestigationLine $line, array $overrides = []): Project
{
    $project = Project::create(array_merge([
        'title' => 'Proyecto de prueba',
        'investigation_line_id' => $line->id,
        'justification' => 'Justificación de prueba',
        'status_id' => UserStatus::DRAFT->value,
        'section_id' => $section->id,
        'section_teacher_id' => null,
        'tutor_request_status_id' => 1,
    ], $overrides));

    ProjectStudent::create([
        'project_id' => $project->id,
        'enrollment_id' => $enrollment->id,
    ]);

    return $project;
}

test('las pantallas del estudiante renderizan sin proyecto', function () {
    [$section] = mkTestBase();

    $student = mkTestUser('estudiante');
    mkTestEnrollment($student, $section);

    foreach ([
        'dashboard',
        'projects.index',
        'tutors.student.index',
        'protocolo.llenar',
        'profile.edit',
    ] as $route) {
        $this->actingAs($student)->get(route($route))->assertSuccessful();
    }
});

test('las pantallas del docente renderizan', function () {
    $teacher = mkTestUser('docente');

    foreach ([
        'dashboard',
        'tutors.index',
        'tutor-logs.teacher',
        'evaluations.teacher.index',
        'evaluations.teacher.history',
        'profile.edit',
    ] as $route) {
        $this->actingAs($teacher)->get(route($route))->assertSuccessful();
    }
});

test('el dashboard renderiza para un usuario con varios roles', function () {
    $admin = mkTestUser('admin');
    Role::firstOrCreate(['name' => 'docente']);
    $admin->assignRole('docente');

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk();
});

test('el asesor puede ser asignado como evaluador de su sección', function () {
    [$section, $line] = mkTestBase();

    $coordinator = mkTestUser('coordinador');
    $advisor = mkTestUser('docente');
    $student = mkTestUser('estudiante');

    $advisor->update([
        'specialty' => 'Desarrollo de software',
        'availability' => 'Lunes a viernes, 8:00 a.m. a 12:00 m.',
        'jury_available' => true,
    ]);

    $section->teachers()->attach($advisor->id, ['is_active' => true]);
    $enrollment = mkTestEnrollment($student, $section);
    $project = mkTestProject($enrollment, $section, $line, [
        'status_id' => UserStatus::READY_FOR_EXPERTS->value,
    ]);

    EvaluationStage::firstOrCreate(['name' => 'Defensa Final']);

    $this->actingAs($coordinator)
        ->get(route('evaluations.coordinator.assign', $project))
        ->assertOk()
        ->assertSee('Desarrollo de software')
        ->assertSee('Lunes a viernes, 8:00 a.m. a 12:00 m.');

    $this->actingAs($coordinator)
        ->post(route('evaluations.coordinator.assign.store', $project), [
            'teachers' => [$advisor->id],
        ])
        ->assertRedirect();

    expect(ProjectEvaluation::where('project_id', $project->id)->where('user_id', $advisor->id)->exists())->toBeTrue();
});

test('un docente no disponible para jurado no puede ser asignado', function () {
    [$section, $line] = mkTestBase();

    $coordinator = mkTestUser('coordinador');
    $teacher = mkTestUser('docente');
    $student = mkTestUser('estudiante');

    $teacher->update(['jury_available' => false]);

    $enrollment = mkTestEnrollment($student, $section);
    $project = mkTestProject($enrollment, $section, $line, [
        'status_id' => UserStatus::READY_FOR_EXPERTS->value,
    ]);

    EvaluationStage::firstOrCreate(['name' => 'Defensa Final']);

    $this->actingAs($coordinator)
        ->post(route('evaluations.coordinator.assign.store', $project), [
            'teachers' => [$teacher->id],
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(ProjectEvaluation::where('project_id', $project->id)->where('user_id', $teacher->id)->exists())->toBeFalse();
});

test('el estudiante puede editar los datos de su proyecto en borrador', function () {
    [$section, $line] = mkTestBase();

    $student = mkTestUser('estudiante');
    $enrollment = mkTestEnrollment($student, $section);
    $project = mkTestProject($enrollment, $section, $line);

    $this->actingAs($student)
        ->put(route('projects.update', $project), [
            'title' => 'Título actualizado',
            'investigation_line_id' => $line->id,
            'justification' => 'Justificación actualizada',
        ])
        ->assertRedirect();

    expect($project->fresh()->title)->toBe('Título actualizado');
});

test('el coordinador puede consultar el historial de tutorías', function () {
    [$section, $line] = mkTestBase();

    $coordinator = mkTestUser('coordinador');
    $teacher = mkTestUser('docente');
    $student = mkTestUser('estudiante');
    $enrollment = mkTestEnrollment($student, $section);
    $project = mkTestProject($enrollment, $section, $line, [
        'tutor_id' => $teacher->id,
        'tutor_request_status_id' => 3,
    ]);

    \App\Models\TutorLog::create([
        'project_id' => $project->id,
        'user_id' => $student->id,
        'meeting_date' => now()->toDateString(),
        'description' => 'Minuta de prueba',
        'status_id' => UserStatus::PENDING->value,
    ]);

    $this->actingAs($coordinator)
        ->get(route('coordinator.tutor-logs', $project))
        ->assertOk()
        ->assertSee('Minuta de prueba');
});

test('el administrador puede crear usuarios con rol', function () {
    $admin = mkTestUser('admin');

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Nuevo Docente',
            'email' => 'nuevo.docente@example.com',
            'credential' => '98765432',
            'phone' => '04149876543',
            'role' => 'docente',
            'status_id' => UserStatus::ACTIVE->value,
            'password' => 'password123',
            'max_tutored_projects' => 5,
        ])
        ->assertRedirect();

    $user = User::where('email', 'nuevo.docente@example.com')->firstOrFail();
    expect($user->hasRole('docente'))->toBeTrue();
});

test('el usuario puede configurar y usar su pregunta de seguridad', function () {
    $user = User::factory()->create(['email' => 'seguridad@example.com']);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'security_question' => '¿Cuál es el nombre de tu primera mascota?',
            'security_answer' => 'Firulais',
        ])
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh()->security_question)->not->toBeNull();

    auth()->logout();

    $this->post(route('password.security.find'), ['email' => 'seguridad@example.com'])
        ->assertRedirect(route('password.security.question'));

    $this->get(route('password.security.question'))->assertOk()->assertSee('primera mascota');

    $this->post(route('password.security.reset'), [
        'email' => 'seguridad@example.com',
        'security_answer' => 'Firulais',
        'password' => 'nueva-clave-123',
        'password_confirmation' => 'nueva-clave-123',
    ])->assertRedirect(route('login'));

    expect(Hash::check('nueva-clave-123', $user->fresh()->password))->toBeTrue();
});

test('un evento puede asociarse a un proyecto y mostrarse en la cartelera', function () {
    [$section, $line] = mkTestBase();

    $admin = mkTestUser('admin');
    $student = mkTestUser('estudiante');
    $enrollment = mkTestEnrollment($student, $section);
    $project = mkTestProject($enrollment, $section, $line);
    $type = \App\Models\EventType::create(['name' => 'Socialización / Defensa']);

    $this->actingAs($admin)
        ->post(route('events.store'), [
            'title' => 'Defensa del proyecto',
            'start_date' => now()->addDay()->format('Y-m-d\TH:i'),
            'location' => 'Aula 3',
            'project_id' => $project->id,
            'event_type_id' => $type->id,
            'visible_to_roles' => ['estudiante', 'docente', 'coordinador'],
        ])
        ->assertRedirect();

    $this->actingAs($student)
        ->get(route('cartelera.index'))
        ->assertOk()
        ->assertSee('Defensa del proyecto')
        ->assertSee('Aula 3');
});

test('el servicio de respaldos genera un archivo para SQLite', function () {
    Storage::fake('local');

    $database = storage_path('app/private/backup-source.sqlite');
    file_put_contents($database, 'sqlite-data');

    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => $database,
    ]);

    $path = (new BackupService())->create();

    Storage::disk('local')->assertExists($path);
    expect(Storage::disk('local')->get($path))->toBe('sqlite-data');

    @unlink($database);
});

test('un miembro puede descargar el documento y un ajeno no', function () {
    [$section, $line] = mkTestBase();

    $student = mkTestUser('estudiante');
    $outsider = mkTestUser('estudiante');
    $enrollment = mkTestEnrollment($student, $section);
    mkTestEnrollment($outsider, $section);
    $project = mkTestProject($enrollment, $section, $line);

    $type = DocumentType::firstOrCreate(['name' => 'Protocolo']);

    Storage::fake('local');
    Storage::disk('local')->put('protocols/test.docx', 'contenido');

    $document = ProjectDocument::create([
        'project_id' => $project->id,
        'document_type_id' => $type->id,
        'user_id' => $student->id,
        'file_path' => 'protocols/test.docx',
        'submission_number' => 1,
    ]);

    $this->actingAs($student)
        ->get(route('documents.download', $document))
        ->assertOk();

    $this->actingAs($outsider)
        ->get(route('documents.download', $document))
        ->assertForbidden();
});

test('un estudiante no puede enviar el protocolo de un proyecto ajeno', function () {
    [$section, $line] = mkTestBase();

    $studentA = mkTestUser('estudiante');
    $studentB = mkTestUser('estudiante');

    $enrollmentA = mkTestEnrollment($studentA, $section);
    $enrollmentB = mkTestEnrollment($studentB, $section);

    mkTestProject($enrollmentA, $section, $line);
    $projectB = mkTestProject($enrollmentB, $section, $line);

    $this->actingAs($studentA)
        ->post(route('projects.submitProtocol', $projectB), [])
        ->assertForbidden();
});

test('un estudiante no puede solicitar tutor para un proyecto ajeno', function () {
    [$section, $line] = mkTestBase();

    $studentA = mkTestUser('estudiante');
    $studentB = mkTestUser('estudiante');

    $enrollmentA = mkTestEnrollment($studentA, $section);
    $enrollmentB = mkTestEnrollment($studentB, $section);

    $teacher = mkTestUser('docente');

    mkTestProject($enrollmentA, $section, $line);
    $projectB = mkTestProject($enrollmentB, $section, $line, [
        'status_id' => UserStatus::ACTIVE->value,
    ]);

    $this->actingAs($studentA)
        ->post(route('tutors.request', $projectB), ['tutor_id' => $teacher->id])
        ->assertForbidden();
});

test('un estudiante no puede agregar bitácoras a un proyecto ajeno', function () {
    [$section, $line] = mkTestBase();

    $studentA = mkTestUser('estudiante');
    $studentB = mkTestUser('estudiante');

    $enrollmentA = mkTestEnrollment($studentA, $section);
    $enrollmentB = mkTestEnrollment($studentB, $section);

    $teacher = mkTestUser('docente');
    $tutorStatus = TutorRequestStatus::firstOrCreate(['id' => 3, 'name' => 'aceptada']);

    mkTestProject($enrollmentA, $section, $line);
    $projectB = mkTestProject($enrollmentB, $section, $line, [
        'tutor_id' => $teacher->id,
        'tutor_request_status_id' => $tutorStatus->id,
    ]);

    $this->actingAs($studentA)
        ->post(route('tutor-logs.store', $projectB), [
            'meeting_date' => now()->toDateString(),
            'description' => 'Minuta falsa',
        ])
        ->assertForbidden();
});

test('un coordinador no puede editar ni borrar a un administrador', function () {
    $coordinator = mkTestUser('coordinador');
    $admin = mkTestUser('admin');

    $this->actingAs($coordinator)
        ->put(route('students.update', $admin), [
            'name' => 'Hackeado',
            'email' => 'hack@example.com',
            'credential' => '12345678',
            'phone' => '04141234567',
            'status_id' => UserStatus::ACTIVE->value,
        ])
        ->assertNotFound();

    $this->actingAs($coordinator)
        ->delete(route('teachers.destroy', $admin))
        ->assertNotFound();

    $this->assertNotNull($admin->fresh());
});

test('un docente no puede editar el perfil de sus estudiantes', function () {
    [$section, $line] = mkTestBase();

    $teacher = mkTestUser('docente');
    $student = mkTestUser('estudiante');

    $section->teachers()->attach($teacher->id, ['is_active' => true]);
    mkTestEnrollment($student, $section);

    $this->actingAs($teacher)
        ->put(route('students.update', $student), [
            'name' => 'Cambiado',
            'email' => 'cambiado@example.com',
            'credential' => '12345678',
            'phone' => '04141234567',
            'status_id' => UserStatus::REJECTED->value,
        ])
        ->assertForbidden();
});

test('aprobar una prórroga guarda el permiso y no falla', function () {
    [$section, $line] = mkTestBase();

    $student = mkTestUser('estudiante');
    $coordinator = mkTestUser('coordinador');
    $enrollment = mkTestEnrollment($student, $section);
    $project = mkTestProject($enrollment, $section, $line);

    $this->actingAs($student)
        ->post(route('late-submission.store', $project), [
            'reason' => 'Motivo suficientemente largo para la solicitud.',
        ])
        ->assertRedirect();

    $lateRequest = $project->lateSubmissionRequests()->firstOrFail();

    $this->actingAs($coordinator)
        ->post(route('late-requests.review', $lateRequest), [
            'decision' => 'approved',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($project->fresh()->allow_late_submission)->toBeTrue();
    expect($lateRequest->fresh()->status_id)->toBe(UserStatus::ACTIVE->value);
});

test('el día de cierre de una actividad sigue abierto', function () {
    $term = AcademicTerm::create([
        'name' => 'Trimestre 1',
        'period' => '2026-2027',
        'start_date' => now()->subDays(5)->toDateString(),
        'end_date' => now()->addDays(5)->toDateString(),
    ]);

    AcademicActivity::create([
        'academic_term_id' => $term->id,
        'name' => 'Carga de protocolos',
        'type' => 'protocol_submission',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->toDateString(),
    ]);

    expect(AcademicActivity::isOpen('protocol_submission'))->toBeTrue();
});

test('la evaluación del tutor exige todos los criterios y no divide por cero', function () {
    [$section, $line, $axis] = mkTestBase();

    $teacher = mkTestUser('docente');
    $student = mkTestUser('estudiante');
    $enrollment = mkTestEnrollment($student, $section);
    $project = mkTestProject($enrollment, $section, $line, [
        'status_id' => UserStatus::ACTIVE->value,
        'tutor_id' => $teacher->id,
        'tutor_request_status_id' => 3,
    ]);

    $writtenType = CriteriaType::create(['name' => 'Informe Escrito']);
    $technicalType = CriteriaType::create(['name' => 'Desarrollo Técnico']);

    $written = EvaluationCriteria::create([
        'thematic_axis_id' => $axis->id,
        'criteria_type_id' => $writtenType->id,
        'name' => 'Redacción',
        'weight_percentage' => 100,
    ]);

    EvaluationCriteria::create([
        'thematic_axis_id' => $axis->id,
        'criteria_type_id' => $technicalType->id,
        'name' => 'Base de datos',
        'weight_percentage' => 100,
    ]);

    $this->actingAs($teacher)
        ->post(route('tutor-evaluations.store', $project), [
            'scores' => [$written->id => 100],
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($project->fresh()->status_id)->toBe(UserStatus::ACTIVE->value);
});

test('la evaluación completa del tutor aprueba y pasa a jurados', function () {
    [$section, $line, $axis] = mkTestBase();

    $teacher = mkTestUser('docente');
    $student = mkTestUser('estudiante');
    $enrollment = mkTestEnrollment($student, $section);
    $project = mkTestProject($enrollment, $section, $line, [
        'status_id' => UserStatus::ACTIVE->value,
        'tutor_id' => $teacher->id,
        'tutor_request_status_id' => 3,
    ]);

    $writtenType = CriteriaType::create(['name' => 'Informe Escrito']);
    $technicalType = CriteriaType::create(['name' => 'Desarrollo Técnico']);

    $written = EvaluationCriteria::create([
        'thematic_axis_id' => $axis->id,
        'criteria_type_id' => $writtenType->id,
        'name' => 'Redacción',
        'weight_percentage' => 100,
    ]);

    $technical = EvaluationCriteria::create([
        'thematic_axis_id' => $axis->id,
        'criteria_type_id' => $technicalType->id,
        'name' => 'Base de datos',
        'weight_percentage' => 100,
    ]);

    $this->actingAs($teacher)
        ->post(route('tutor-evaluations.store', $project), [
            'scores' => [
                $written->id => 100,
                $technical->id => 100,
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($project->fresh()->status_id)->toBe(UserStatus::READY_FOR_EXPERTS->value);
    expect($project->fresh()->written_score)->toBe(100.0);
});

test('no se puede borrar una sección con proyectos', function () {
    [$section, $line] = mkTestBase();

    $admin = mkTestUser('admin');
    $student = mkTestUser('estudiante');
    $enrollment = mkTestEnrollment($student, $section);

    mkTestProject($enrollment, $section, $line);

    $this->actingAs($admin)
        ->delete(route('sections.destroy', $section))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($section->fresh())->not->toBeNull();
});

test('guardar una sección con el mismo asesor no duplica el pivote', function () {
    [$section] = mkTestBase();

    $admin = mkTestUser('admin');
    $teacher = mkTestUser('docente');

    $section->teachers()->attach($teacher->id, ['is_active' => true]);

    $this->actingAs($admin)
        ->put(route('sections.update', $section), [
            'name' => $section->name,
            'max_team_size' => $section->max_team_size,
            'trayecto_id' => $section->trayecto_id,
            'period' => $section->period,
            'is_active' => 1,
            'teacher_id' => $teacher->id,
        ])
        ->assertRedirect();

    expect($section->teachers()->count())->toBe(1);
    expect($section->teachers()->wherePivot('is_active', true)->count())->toBe(1);
});

test('no se puede invitar a un estudiante de otra sección', function () {
    [$section, $line, $axis, $trayecto] = mkTestBase();

    $otherSection = Section::create([
        'name' => 'Sección 2',
        'trayecto_id' => $trayecto->id,
        'max_team_size' => 3,
        'period' => '2026-2027',
        'is_active' => true,
    ]);

    $sender = mkTestUser('estudiante');
    $receiver = mkTestUser('estudiante');

    $senderEnrollment = mkTestEnrollment($sender, $section);
    $receiverEnrollment = mkTestEnrollment($receiver, $otherSection);

    mkTestProject($senderEnrollment, $section, $line);

    $this->actingAs($sender)
        ->post(route('projects.invite'), [
            'receiver_enrollment_id' => $receiverEnrollment->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(ProjectInvitation::count())->toBe(0);
});

test('dos trimestres pueden tener la misma actividad', function () {
    $first = AcademicTerm::create([
        'name' => 'Trimestre 1',
        'period' => '2026-2027',
        'start_date' => now()->subDays(10)->toDateString(),
        'end_date' => now()->addDays(10)->toDateString(),
    ]);

    $second = AcademicTerm::create([
        'name' => 'Trimestre 2',
        'period' => '2026-2027',
        'start_date' => now()->addDays(20)->toDateString(),
        'end_date' => now()->addDays(40)->toDateString(),
    ]);

    foreach ([$first, $second] as $term) {
        AcademicActivity::create([
            'academic_term_id' => $term->id,
            'name' => 'Carga de protocolos',
            'type' => 'protocol_submission',
            'start_date' => $term->start_date->toDateString(),
            'end_date' => $term->end_date->toDateString(),
        ]);
    }

    expect(AcademicActivity::where('type', 'protocol_submission')->count())->toBe(2);
});

test('una invitación ya aceptada no se puede volver a aceptar', function () {
    [$section, $line] = mkTestBase();

    $sender = mkTestUser('estudiante');
    $receiver = mkTestUser('estudiante');
    $senderEnrollment = mkTestEnrollment($sender, $section);
    $receiverEnrollment = mkTestEnrollment($receiver, $section);

    $project = mkTestProject($senderEnrollment, $section, $line);

    $invitation = ProjectInvitation::create([
        'project_id' => $project->id,
        'sender_enrollment_id' => $senderEnrollment->id,
        'receiver_enrollment_id' => $receiverEnrollment->id,
        'status_id' => UserStatus::PENDING->value,
    ]);

    $this->actingAs($receiver)
        ->post(route('invitations.accept', $invitation))
        ->assertRedirect();

    $this->actingAs($receiver)
        ->post(route('invitations.accept', $invitation))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(ProjectStudent::where('project_id', $project->id)->where('enrollment_id', $receiverEnrollment->id)->count())->toBe(1);
});
