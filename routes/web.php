<?php

use App\Http\Controllers\AcademicCalendarController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\LateSubmissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectDocumentController;
use App\Http\Controllers\ProjectInvitationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\TutorEvaluationController;
use App\Http\Controllers\TutorLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('dashboard') 
        : redirect()->route('login');
});

Route::middleware(['auth', 'can:generate-reports'])->group(function () {
    // Una sola ruta para todos los reportes
    Route::get('/reports/{report}/print', [ReportController::class, 'print'])->name('reports.print');
});

Route::middleware(['auth'/* , 'verified' */])->group(function () {

    Route::get('/documents/{document}/download', [ProjectDocumentController::class, 'download'])->name('documents.download');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');

});

Route::middleware(['auth', 'role:admin|estudiante'])->group(function () {

    Route::post('/projects/{project}/late-submission', [LateSubmissionController::class, 'store'])->name('late-submission.store');
    Route::post('/projects/{project}/request-tutor', [TutorController::class, 'request'])->name('tutors.request');
    Route::get('/tutor-logs/{log}/download', [TutorLogController::class, 'downloadLog'])->name('tutor-logs.download');

    Route::post('/projects/invite', [ProjectInvitationController::class, 'store'])->name('projects.invite');
    Route::post('/invitations/{invitation}/accept', [ProjectInvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{invitation}/reject', [ProjectInvitationController::class, 'reject'])->name('invitations.reject');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::post('/projects/{project}/submit-protocol', [ProjectController::class, 'submitProtocol'])->name('projects.submitProtocol');
    Route::post('/projects/{project}/logs', [TutorLogController::class, 'store'])->name('tutor-logs.store');
    Route::get('/my-tutor', [TutorController::class, 'studentIndex'])->name('tutors.student.index');

});

Route::middleware(['auth', 'role:admin|coordinador|docente'])->group(function () {
    Route::get('/evaluations', [EvaluationController::class, 'index'])->name('evaluations.coordinator.index');
    Route::post('/evaluations/{project}/assign', [EvaluationController::class, 'assign'])->name('evaluations.coordinator.assign.store');
    Route::get('/evaluations/{project}/assign', [EvaluationController::class, 'assignForm'])->name('evaluations.coordinator.assign');
});

Route::middleware(['auth', 'role:admin|coordinador'])->group(function () {

    Route::get('/coordinator/late-requests', [LateSubmissionController::class, 'index'])->name('late-requests.index');
    Route::post('/coordinator/late-requests/{lateRequest}/review', [LateSubmissionController::class, 'review'])->name('late-requests.review');

    // Rutas para la gestión de secciones
    Route::get('/sections', [SectionController::class, 'index'])->name('sections.index');
    Route::get('/sections/create', [SectionController::class, 'create'])->name('sections.create');
    Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
    Route::get('/sections/{section}/edit', [SectionController::class, 'edit'])->name('sections.edit');
    Route::put('/sections/{section}', [SectionController::class, 'update'])->name('sections.update');
    Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

    Route::post('/sections/close-period', [SectionController::class, 'closePeriod'])->name('sections.closePeriod');
    Route::post('/sections/clone-period', [SectionController::class, 'clonePeriod'])->name('sections.clonePeriod');

    // Rutas para la gestión de docentes
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
    Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

    Route::get('/coordinator/protocols', [ProjectController::class, 'protocols'])->name('coordinator.protocols');
    Route::get('/coordinator/tutorships', [ProjectController::class, 'tutorships'])->name('coordinator.tutorships');
    Route::get('/coordinator/assign-tutors', [TutorController::class, 'assignDashboard'])->name('coordinator.assignTutors');
    Route::post('/coordinator/assign-tutors/{project}', [TutorController::class, 'manualAssign'])->name('coordinator.manualAssign');
    Route::post('/coordinator/calculate-quota', [TutorController::class, 'calculateFairQuota'])->name('coordinator.calculateQuota');

    Route::resource('events', EventController::class)->except(['show', 'edit', 'update']);

    Route::get('/academic-calendar', [AcademicCalendarController::class, 'index'])->name('calendar.index');
    Route::post('/academic-calendar/terms', [AcademicCalendarController::class, 'storeTerm'])->name('calendar.storeTerm');
    Route::delete('/academic-calendar/terms/{term}', [AcademicCalendarController::class, 'destroyTerm'])->name('calendar.destroyTerm');
    Route::post('/academic-calendar/terms/{term}/activities', [AcademicCalendarController::class, 'storeActivity'])->name('calendar.storeActivity');
    Route::delete('/academic-calendar/activities/{activity}', [AcademicCalendarController::class, 'destroyActivity'])->name('calendar.destroyActivity');

});

Route::middleware(['auth', 'role:admin|docente'])->group(function () {

    Route::get('/my-tutor-requests', [TutorController::class, 'index'])->name('tutors.index');
    Route::post('/tutor-requests/{project}/accept', [TutorController::class, 'accept'])->name('tutors.accept');
    Route::post('/tutor-requests/{project}/reject', [TutorController::class, 'reject'])->name('tutors.reject');
    Route::post('/my-tutor-requests/increase-capacity', [TutorController::class, 'increaseCapacity'])->name('tutors.increaseCapacity');

    Route::get('/my-evaluations/history', [EvaluationController::class, 'historyIndex'])->name('evaluations.teacher.history');
    Route::get('/my-evaluations/history/{project}', [EvaluationController::class, 'historyShow'])->name('evaluations.teacher.history.show');

    Route::get('/projects/{project}/evaluate', [TutorEvaluationController::class, 'create'])->name('tutor-evaluations.create');
    Route::post('/projects/{project}/evaluate', [TutorEvaluationController::class, 'store'])->name('tutor-evaluations.store');

});

Route::middleware(['auth', 'role:admin|coordinador|docente'])->group(function () {

    Route::get('/my-evaluations/history', [EvaluationController::class, 'historyIndex'])->name('evaluations.teacher.history');
    Route::get('/my-evaluations/history/{project}', [EvaluationController::class, 'historyShow'])->name('evaluations.teacher.history.show');

    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::post('/students/{student}/approve', [StudentController::class, 'approve'])->name('students.approve');
    Route::post('/students/{student}/reject', [StudentController::class, 'reject'])->name('students.reject');
    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    
    Route::get('/my-evaluations', [EvaluationController::class, 'teacherIndex'])->name('evaluations.teacher.index');
    Route::get('/my-evaluations/{evaluation}/review', [EvaluationController::class, 'reviewForm'])->name('evaluations.teacher.review');
    Route::post('/my-evaluations/{evaluation}/review', [EvaluationController::class, 'storeReview'])->name('evaluations.teacher.store');
    
    Route::get('/my-tutor-logs', [TutorLogController::class, 'teacherIndex'])->name('tutor-logs.teacher');
    Route::post('/tutor-logs/{log}/review', [TutorLogController::class, 'review'])->name('tutor-logs.review');
    
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
