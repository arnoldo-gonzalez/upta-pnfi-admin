<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_protocol_field_corrections', function (Blueprint $table) {
            $table->foreignId('project_document_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('project_protocol_field_corrections', function (Blueprint $table) {
            $table->foreignId('project_document_id')->nullable(false)->change();
        });
    }
};