<?php

use App\Enums\UserStatus;
use App\Models\Section;
use App\Models\Trayecto;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new students register as pending and are not logged in', function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'estudiante']);

    $trayecto = Trayecto::create([
        'name' => 'Trayecto 2',
        'number' => 2,
    ]);

    $section = Section::create([
        'name' => 'Sección 1',
        'trayecto_id' => $trayecto->id,
        'max_team_size' => 3,
        'period' => '2026-2027',
        'is_active' => true,
    ]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'credential' => '32123456',
        'phone' => '04141234567',
        'section_id' => $section->id,
    ]);

    $response->assertRedirect(route('login'));

    $this->assertGuest();
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'status_id' => UserStatus::PENDING->value,
    ]);
});
