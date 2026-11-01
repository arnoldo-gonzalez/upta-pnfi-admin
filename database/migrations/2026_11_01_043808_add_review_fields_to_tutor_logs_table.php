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
        Schema::table('tutor_logs', function (Blueprint $table) {
            $table->foreignId('status_id')->default(1)->after('file_path')->constrained('statuses');
            $table->text('teacher_observations')->nullable()->after('status_id');
        });
    }

    public function down(): void
    {
        Schema::table('tutor_logs', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn(['status_id', 'teacher_observations']);
        });
    }
};
