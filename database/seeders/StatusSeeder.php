<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::firstOrCreate(['id' => 1, 'name' => 'pending']);
        Status::firstOrCreate(['id' => 2, 'name' => 'active']);
        Status::firstOrCreate(['id' => 3, 'name' => 'rejected']);
        Status::firstOrCreate(['id' => 4, 'name' => 'borrador']);
        Status::firstOrCreate(['id' => 5, 'name' => 'needs_corrections']);
    }
}
