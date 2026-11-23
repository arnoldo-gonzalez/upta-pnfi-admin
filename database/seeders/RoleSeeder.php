<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Crea los roles base del sistema. Es idempotente y seguro para ejecutar
     * en producción (no crea usuarios de prueba).
     */
    public function run(): void
    {
        foreach (['admin', 'coordinador', 'docente', 'estudiante'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
