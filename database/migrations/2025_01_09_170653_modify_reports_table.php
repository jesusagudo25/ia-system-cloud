<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Truncar la tabla para evitar conflictos de datos
        DB::statement('TRUNCATE TABLE reports');

        // Alterar las columnas
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['type', 'start_date', 'end_date']); // Eliminar columnas existentes
            $table->string('title')->after('user_id');             // Agregar columna "title"
            $table->text('description')->after('title');           // Agregar columna "description"
            $table->json('filters')->after('description');         // Agregar columna "filters"
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir los cambios realizados en la tabla
        DB::statement('TRUNCATE TABLE reports'); // Volver a truncar para evitar conflictos

        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'filters']); // Eliminar columnas agregadas
            $table->char('type')->after('user_id');                // Restaurar columna "type"
            $table->date('start_date')->after('type');             // Restaurar columna "start_date"
            $table->date('end_date')->after('start_date');         // Restaurar columna "end_date"
        });
    }
}
