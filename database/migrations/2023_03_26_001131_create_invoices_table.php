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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('agency_id')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('interpreter_id')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('coordinator_id')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->decimal('total_amount', 8, 2)->default(0);
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
