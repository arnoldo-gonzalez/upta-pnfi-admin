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
        Schema::create('project_invitations', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('receiver_enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            
            $table->foreignId('status_id')->constrained('statuses');
            
            $table->timestamps();
            
            $table->unique(['project_id', 'receiver_enrollment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_invitations');
    }
};
