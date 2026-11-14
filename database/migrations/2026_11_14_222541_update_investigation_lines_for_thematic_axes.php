<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investigation_lines', function (Blueprint $table) {
            $table->foreignId('thematic_axis_id')->nullable()->after('name')->constrained('thematic_axes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('investigation_lines', function (Blueprint $table) {
            $table->dropForeign(['thematic_axis_id']);
            $table->dropColumn('thematic_axis_id');
        });
    }
};
