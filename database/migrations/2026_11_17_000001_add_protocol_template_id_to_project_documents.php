<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            // Versión de la plantilla de protocolo usada en este envío
            $table->foreignId('protocol_template_id')->nullable()->after('submission_number')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropForeign(['protocol_template_id']);
        });

        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropColumn('protocol_template_id');
        });
    }
};
