<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Los usuarios creados antes de existir el estado quedaron en NULL y
        // no podían iniciar sesión. Se marcan como pendientes de aprobación.
        if (DB::table('statuses')->where('id', 1)->exists()) {
            DB::table('users')->whereNull('status_id')->update(['status_id' => 1]);
        }
    }

    public function down(): void
    {
        // Sin reversión: el backfill no destruye información.
    }
};
