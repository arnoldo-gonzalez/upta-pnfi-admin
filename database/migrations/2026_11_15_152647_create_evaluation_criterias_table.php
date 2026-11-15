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
        Schema::create('evaluation_criterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thematic_axis_id')->constrained()->cascadeOnDelete();
            
            $table->foreignId('criteria_type_id')->constrained('criteria_types')->cascadeOnDelete();
            
            $table->string('name'); 
            $table->integer('weight_percentage'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_criterias');
    }
};
