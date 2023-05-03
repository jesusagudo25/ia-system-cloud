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
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('description_id')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            $table->foreignId('address_id')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            $table->string('invoice_number');
            $table->string('assignment_number');

            $table->date('date_of_service_provided');
            $table->time('arrival_time');
            $table->time('start_time');
            $table->time('end_time');

            $table->integer('travel_time_to_assignment');
            $table->integer('time_back_from_assignment');
            
            $table->integer('travel_mileage')->default(0);
            $table->decimal('cost_per_mile', 8, 3);
            
            $table->decimal('total_amount_miles', 8, 2)->default(0);
            $table->decimal('total_amount_hours', 8, 2)->default(0);
            
            $table->decimal('total_interpreter', 8, 2)->default(0);
            $table->decimal('total_coordinator', 8, 2)->default(0);
            
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
