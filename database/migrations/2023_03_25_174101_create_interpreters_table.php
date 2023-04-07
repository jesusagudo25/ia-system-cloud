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
        Schema::create('interpreters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lenguage_id')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('full_name');
            $table->string('ssn')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('phone_number')->unique()->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interpreters');
    }
};
