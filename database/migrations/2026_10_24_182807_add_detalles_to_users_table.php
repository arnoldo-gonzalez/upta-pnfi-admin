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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('credential')->unique()->nullable()->after('email');
            $table->string('phone')->nullable()->after('credential');
            
            $table->foreignId('status_id')
                  ->nullable()
                  ->after('phone')
                  ->constrained('statuses')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Primero eliminamos la llave foránea, luego las columnas
            $table->dropForeign(['status_id']);
            $table->dropColumn(['credential', 'phone', 'status_id']);
        });
    }
};
