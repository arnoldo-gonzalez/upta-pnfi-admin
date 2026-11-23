<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('projects', 'allow_late_submission')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->boolean('allow_late_submission')->default(false)->after('tutor_request_status_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('projects', 'allow_late_submission')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('allow_late_submission');
            });
        }
    }
};
