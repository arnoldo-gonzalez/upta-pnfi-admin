<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('protocol_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('docx_path');
            $table->longText('schema_json')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protocol_templates');
    }
};
