<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_evaluations', function (Blueprint $table) {
            // 1. Eliminamos la regla antigua que bloquea los reenvíos
            $table->dropUnique('project_evaluations_project_id_user_id_stage_id_unique');
            
            // 2. Creamos la nueva regla: Un profesor evalúa un documento específico una sola vez
            $table->unique(['project_document_id', 'user_id'], 'eval_doc_user_unique');
        });
    }

    public function down(): void
    {
        Schema::table('project_evaluations', function (Blueprint $table) {
            $table->dropUnique('eval_doc_user_unique');
            $table->unique(['project_id', 'user_id', 'stage_id'], 'project_evaluations_project_id_user_id_stage_id_unique');
        });
    }
};