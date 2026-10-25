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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('code')->nullable()->unique();
            
            $table->foreignId('investigation_line_id')->constrained();
            
            $table->text('justification')->nullable();
            
            $table->foreignId('status_id')->constrained('statuses');

            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_teacher_id')->nullable()->constrained('section_teacher')->nullOnDelete();
            
            $table->foreignId('tutor_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->foreignId('tutor_request_status_id')->constrained('tutor_request_statuses');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
