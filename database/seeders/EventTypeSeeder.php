<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventType;

class EventTypeSeeder extends Seeder
{
    public function run(): void
    {
        EventType::firstOrCreate(['id' => 1, 'name' => 'Académico']);
        EventType::firstOrCreate(['id' => 2, 'name' => 'Entrega de Documentos']);
        EventType::firstOrCreate(['id' => 3, 'name' => 'Socialización / Defensa']);
        EventType::firstOrCreate(['id' => 4, 'name' => 'Feriado / Receso']);
    }
}