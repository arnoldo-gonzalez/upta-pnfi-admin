<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            
            $table->boolean('is_active')->default(true);
            $table->timestamp('completed_at')->nullable(); 
            
            $table->timestamps();

            $table->unique(['user_id', 'section_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};