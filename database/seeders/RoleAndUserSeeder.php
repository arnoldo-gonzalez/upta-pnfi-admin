<?php

namespace Database\Seeders;

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
        $admin = User::firstOrCreate([
            'name' => 'Admin Sistema',
            'email' => 'admin@uptamcy.com',
            'password' => Hash::make('password'),
        ]);

        $admin->assignRole($adminRole);

        $coordinador = User::firstOrCreate([
            'name' => 'Coordinador PNFI',
            'email' => 'coordinador@uptamcy.com',
            'password' => Hash::make('password'),
        ]);

        $coordinador->assignRole($coordinadorRole);

        $docente = User::firstOrCreate([
            'name' => 'Docente Tutor',
            'email' => 'docente@uptamcy.com',
            'password' => Hash::make('password'),
        ]);

        $docente->assignRole($docenteRole);

        $estudiante = User::firstOrCreate([
            'name' => 'Estudiante Prueba',
            'email' => 'estudiante@uptamcy.com',
            'password' => Hash::make('password'),
        ]);

        $estudiante->assignRole($estudianteRole);
    }
}