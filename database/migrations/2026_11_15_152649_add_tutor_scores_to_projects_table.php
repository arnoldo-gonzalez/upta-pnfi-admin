<?php

use App\Enums\UserStatus;
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
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('written_score', 5, 2)->nullable()->after('allow_late_submission');
            $table->decimal('technical_score', 5, 2)->nullable()->after('written_score');
            $table->foreignId('tutor_evaluation_status_id')
                  ->default(UserStatus::PENDING->value)
                  ->after('technical_score')
            ->constrained('statuses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign("tutor_evaluation_status_id");
            $table->dropColumn([
                "written_score",
                "technical_score",
                "tutor_evaluation_status_id"
            ]);
        });
    }
};
