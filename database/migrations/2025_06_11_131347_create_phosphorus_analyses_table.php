<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhosphorusAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phosphorus_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('consecutivo_no'); // Para vinculación
            $table->foreignId('analysis_id')->constrained('analyses')->onDelete('cascade');
            $table->date('fecha_analisis');
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->string('equipo_utilizado');
            $table->string('intervalo_metodo');
            $table->string('analista')->nullable();
            // Ítems de ensayo
            $table->string('codigo_interno')->nullable();
            $table->decimal('peso_muestra', 10, 4)->nullable();
            $table->decimal('pw', 10, 4)->nullable();
            $table->decimal('v_extractante', 10, 4)->nullable();
            $table->decimal('lectura_blanco', 10, 4)->nullable();
            $table->decimal('factor_dilucion', 10, 4)->nullable();
            $table->decimal('fosforo_disponible_mg_l', 10, 4)->nullable();
            $table->decimal('fosforo_disponible_mg_kg', 10, 4)->nullable();
            $table->text('observaciones_item')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('phosphorus_analyses');
    }
}
