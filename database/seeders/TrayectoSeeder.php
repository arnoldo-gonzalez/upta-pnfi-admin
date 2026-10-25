<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trayecto;

class TrayectoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Trayecto::firstOrCreate(
            ['number' => 1],
            ['name' => 'Trayecto I']
        );

        Trayecto::firstOrCreate(
            ['number' => 2],
            ['name' => 'Trayecto II']
        );

        Trayecto::firstOrCreate(
            ['number' => 3],
            ['name' => 'Trayecto III']
        );

        Trayecto::firstOrCreate(
            ['number' => 4],
            ['name' => 'Trayecto IV']
        );
    }
}