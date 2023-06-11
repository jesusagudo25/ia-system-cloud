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
        Schema::create('bank_checks', function (Blueprint $table) {
            $table->id()->startingValue(1700);
            $table->foreignId('payroll_id')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->date('date')->default(now());
            $table->string('pay_to');
            $table->string('ssn');
            $table->decimal('amount', 8, 2);
            $table->string('amount_in_words');
            $table->string('for');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_checks');
    }
};
