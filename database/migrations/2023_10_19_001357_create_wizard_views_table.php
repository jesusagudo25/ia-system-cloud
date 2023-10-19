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
        Schema::create('wizard_views', function (Blueprint $table) {
            $table->id();
            $table->string('assignment');
            $table->date('date_of_service');
            $table->string('agency');
            $table->string('interpreter');
            $table->string('status');
            $table->decimal('total_amount', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wizard_views');
    }
};
