<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_protocol_field_corrections', function (Blueprint $table) {
            $table->foreignId('project_evaluation_id')
                ->nullable()
                ->after('project_document_id')
                ->constrained('project_evaluations')
                ->cascadeOnDelete();
        });

        // Backfill: asociar correcciones existentes a la evaluación del mismo documento/evaluador
        $rows = DB::table('project_protocol_field_corrections')
            ->whereNull('project_evaluation_id')
            ->get();

        foreach ($rows as $row) {
            $evaluationId = DB::table('project_evaluations')
                ->where('project_document_id', $row->project_document_id)
                ->where('user_id', $row->evaluator_id)
                ->value('id');

            if ($evaluationId) {
                DB::table('project_protocol_field_corrections')
                    ->where('id', $row->id)
                    ->update(['project_evaluation_id' => $evaluationId]);
            }
        }

        Schema::table('project_protocol_field_corrections', function (Blueprint $table) {
            $table->dropUnique(['project_document_id', 'placeholder_key']);
        });

        Schema::table('project_protocol_field_corrections', function (Blueprint $table) {
            $table->unique(['project_evaluation_id', 'placeholder_key']);
        });
    }

    public function down(): void
    {
        Schema::table('project_protocol_field_corrections', function (Blueprint $table) {
            $table->dropUnique(['project_evaluation_id', 'placeholder_key']);
        });

        Schema::table('project_protocol_field_corrections', function (Blueprint $table) {
            $table->dropForeign(['project_evaluation_id']);
            $table->dropColumn('project_evaluation_id');
        });

        Schema::table('project_protocol_field_corrections', function (Blueprint $table) {
            $table->unique(['project_document_id', 'placeholder_key']);
        });
    }
};
