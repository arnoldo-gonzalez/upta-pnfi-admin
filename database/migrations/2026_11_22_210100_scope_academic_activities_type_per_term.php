<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // El tipo de actividad debe poder repetirse en cada trimestre.
        Schema::table('academic_activities', function (Blueprint $table) {
            $table->dropUnique(['type']);
        });

        Schema::table('academic_activities', function (Blueprint $table) {
            $table->unique(['academic_term_id', 'type'], 'academic_activities_term_type_unique');
        });
    }

    public function down(): void
    {
        Schema::table('academic_activities', function (Blueprint $table) {
            $table->dropUnique('academic_activities_term_type_unique');
        });

        Schema::table('academic_activities', function (Blueprint $table) {
            $table->unique(['type']);
        });
    }
};
