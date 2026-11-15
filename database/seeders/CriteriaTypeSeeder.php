<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CriteriaType;

class CriteriaTypeSeeder extends Seeder
{
    public function run(): void
    {
        CriteriaType::firstOrCreate(['name' => 'Informe Escrito']);
        CriteriaType::firstOrCreate(['name' => 'Desarrollo Técnico']);
    }
}