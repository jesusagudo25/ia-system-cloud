<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('special_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lenguage_id')->constrained()->onDelete('cascade');
            $table->foreignId('agency_id')->constrained()->onDelete('cascade');
            $table->decimal('price_per_hour', 8, 2);
            $table->decimal('price_per_hour_interpreter', 8, 2);
            $table->timestamps();

            $table->unique(['lenguage_id', 'agency_id']); // evita duplicados
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('special_prices');
    }
};
