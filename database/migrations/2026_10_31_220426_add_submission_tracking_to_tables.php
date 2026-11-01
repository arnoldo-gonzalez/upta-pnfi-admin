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
        Schema::table('project_documents', function (Blueprint $table) {
            // Saber si es el envío 1, 2, 3...
            $table->integer('submission_number')->default(1)->after('file_path');
        });

        Schema::table('project_evaluations', function (Blueprint $table) {
            // Relacionar la evaluación con el documento exacto que se evaluó
            $table->foreignId('project_document_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropColumn('submission_number');
        });

        Schema::table('project_evaluations', function (Blueprint $table) {
            $table->dropForeign(['project_document_id']);
        });
    }
};
