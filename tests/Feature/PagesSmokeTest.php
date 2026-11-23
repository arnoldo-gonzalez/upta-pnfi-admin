<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('las pantallas principales renderizan para un administrador', function () {
    Role::firstOrCreate(['name' => 'admin']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $routes = [
        'dashboard',
        'coordinator.protocols',
        'coordinator.tutorships',
        'coordinator.assignTutors',
        'evaluations.coordinator.index',
        'students.index',
        'teachers.index',
        'sections.index',
        'calendar.index',
        'events.index',
        'audit.index',
        'admin.protocolo.plantilla',
        'late-requests.index',
        'profile.edit',
    ];

    foreach ($routes as $route) {
        $this->actingAs($admin)
            ->get(route($route))
            ->assertSuccessful();
    }
});
