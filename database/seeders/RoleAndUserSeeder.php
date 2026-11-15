<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Trayecto;
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
                'credential' => '10000001',
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
                'credential' => '20000002',
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
                'credential' => '20000003',
                'phone' => '04140000003',
                'status_id' => UserStatus::ACTIVE->value,
            ]
        );
        $docente->assignRole($docenteRole);

        $docente_1 = User::firstOrCreate(
            ['email' => 'docente_1@uptamcy.com'],
            [
                'name' => 'Docente_1 Tutor',
                'password' => Hash::make('password'),
                'credential' => '20000004',
                'phone' => '04140000004',
                'status_id' => UserStatus::ACTIVE->value,
            ]
        );
        $docente_1->assignRole($docenteRole);

        $docente_2 = User::firstOrCreate(
            ['email' => 'docente_2@uptamcy.com'],
            [
                'name' => 'Docente_2 Tutor',
                'password' => Hash::make('password'),
                'credential' => '20000005',
                'phone' => '04140000005',
                'status_id' => UserStatus::ACTIVE->value,
            ]
        );
        $docente_2->assignRole($docenteRole);

        $docente_3 = User::firstOrCreate(
            ['email' => 'docente_3@uptamcy.com'],
            [
                'name' => 'Docente_3 Tutor',
                'password' => Hash::make('password'),
                'credential' => '20000006',
                'phone' => '04140000006',
                'status_id' => UserStatus::ACTIVE->value,
            ]
        );
        $docente_3->assignRole($docenteRole);

        $docente_4 = User::firstOrCreate(
            ['email' => 'docente_4@uptamcy.com'],
            [
                'name' => 'Docente_4 Tutor',
                'password' => Hash::make('password'),
                'credential' => '20000007',
                'phone' => '04140000007',
                'status_id' => UserStatus::ACTIVE->value,
            ]
        );
        $docente_4->assignRole($docenteRole);

        // --- ESTUDIANTE ---

        for ($i = 0; $i <= 10; $i++) {

            $estudiante = User::firstOrCreate(
                ['email' => "estudiante_$i@uptamcy.com"],
                [
                    'name' => "Estudiante Prueba $i",
                    'password' => Hash::make('password'),
                    'credential' => 3000000 + $i,
                    'phone' => '0' . (4140000004 + $i),
                    'status_id' => UserStatus::PENDING->value,
                ]
            );
            $estudiante->assignRole($estudianteRole);
    
            // --- INSCRIPCIÓN DEL ESTUDIANTE DE PRUEBA ---
            
            $trayecto = Trayecto::firstOrCreate(['number' => 2], ['name' => 'Trayecto 2']);
            $seccion = Section::firstOrCreate(
                ['name' => 'Sección 1', 'trayecto_id' => $trayecto->id, 'period' => '2026-2027']
            );
    
            Enrollment::firstOrCreate([
                'user_id' => $estudiante->id,
                'section_id' => $seccion->id,
            ], [
                'is_active' => true,
                'completed_at' => null,
            ]);

        }

    }
}