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
        Schema::table('lenguages', function (Blueprint $table) {
            $table->decimal('price_per_hour_interpreter', 8, 2)->nullable()->after('price_per_hour');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lenguages', function (Blueprint $table) {
            $table->dropColumn('price_per_hour_interpreter');
        });
    }
};
