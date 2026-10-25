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
        Schema::create('project_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // El docente evaluador
            $table->foreignId('stage_id')->constrained('evaluation_stages');
            $table->foreignId('status_id')->constrained('statuses');
            
            $table->text('observations')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            
            $table->timestamps();
            
            $table->unique(['project_id', 'user_id', 'stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_evaluations');
    }
};
