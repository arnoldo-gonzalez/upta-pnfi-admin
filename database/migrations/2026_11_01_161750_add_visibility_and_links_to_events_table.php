<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Arreglo de roles que pueden verlo, ej: ["estudiante", "docente"]
            $table->json('visible_to_roles')->nullable()->after('description');
            
            // Arreglo con los enlaces según el rol, ej: {"estudiante": "projects.index", "docente": "evaluations.teacher.index"}
            $table->json('role_links')->nullable()->after('visible_to_roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['visible_to_roles', 'role_links']);
        });
    }
};
