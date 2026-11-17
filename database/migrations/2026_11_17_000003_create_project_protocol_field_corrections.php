<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_protocol_field_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_document_id')->constrained()->cascadeOnDelete();
            $table->string('placeholder_key')->index();
            $table->text('correction')->nullable();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_document_id', 'placeholder_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_protocol_field_corrections');
    }
};
