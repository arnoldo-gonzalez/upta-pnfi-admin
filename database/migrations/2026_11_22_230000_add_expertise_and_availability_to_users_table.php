<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('specialty')->nullable()->after('phone');
            $table->string('availability')->nullable()->after('specialty');
            $table->boolean('jury_available')->default(true)->after('availability');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['specialty', 'availability', 'jury_available']);
        });
    }
};
