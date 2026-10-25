<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear los roles

        // 1.1 Administrador general del sistema
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // 1.2 Coordinador general de proyecto
        $coordinadorRole = Role::firstOrCreate(['name' => 'coordinador']);

        // 1.3 Docente (Aquí entran tutores, asesores y evaluadores. 
        // La funcion del docente depende de su relacion con la materia de proyecto en si, y con un proyecto en particular.
        $docenteRole = Role::firstOrCreate(['name' => 'docente']);

        // 1.4 Estudiante, es quien cursa la materia.
        $estudianteRole = Role::firstOrCreate(['name' => 'estudiante']);

        // 2. Crear un usuario de prueba por cada rol

        // --- ADMIN ---
        $admin = User::firstOrCreate(
            ['email' => 'admin@uptamcy.com'],
            [
                'name' => 'Admin Sistema',
                'password' => Hash::make('password'),
                'credential' => '00000001',
                'phone' => '04140000001',
                'status_id' => UserStatus::ACTIVE->value,
            ]
        );
        $admin->assignRole($adminRole);

        // --- COORDINADOR ---
        $coordinador = User::firstOrCreate(
            ['email' => 'coordinador@uptamcy.com'],
            [
                'name' => 'Coordinador PNFI',
                'password' => Hash::make('password'),
                'credential' => '00000002',
                'phone' => '04140000002',
                'status_id' => UserStatus::ACTIVE->value,
            ]
        );
        $coordinador->assignRole($coordinadorRole);

        // --- DOCENTE ---
        $docente = User::firstOrCreate(
            ['email' => 'docente@uptamcy.com'],
            [
                'name' => 'Docente Tutor',
                'password' => Hash::make('password'),
                'credential' => '00000003',
                'phone' => '04140000003',
                'status_id' => UserStatus::ACTIVE->value,
            ]
        );
        $docente->assignRole($docenteRole);

        // --- ESTUDIANTE ---
        $estudiante = User::firstOrCreate(
            ['email' => 'estudiante@uptamcy.com'],
            [
                'name' => 'Estudiante Prueba',
                'password' => Hash::make('password'),
                'credential' => '30000001',
                'phone' => '04140000004',
                'status_id' => UserStatus::PENDING->value,
            ]
        );
        $estudiante->assignRole($estudianteRole);
    }
}