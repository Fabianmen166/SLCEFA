<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHumidityAnalysesTable extends Migration
{
    public function up()
    {
        Schema::create('humidity_analyses', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('analysis_id')->nullable(); // Relación con tabla analyses
            $table->string('consecutivo_no')->nullable();
            $table->date('fecha_analisis')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // Usuario que realiza el análisis

            // Detalles del horno y equipo
            $table->time('hora_ingreso_horno')->nullable();
            $table->time('hora_salida_horno')->nullable();
            $table->decimal('temperatura_horno', 6, 2)->nullable();
            $table->string('nombre_metodo')->nullable();
            $table->string('intervalo_metodo')->nullable();
            $table->string('equipo_utilizado')->nullable();
            $table->string('unidades_reporte_equipo')->nullable();
            $table->string('resolucion_instrumental')->nullable();
            $table->date('fecha_fin_analisis')->nullable();
            $table->string('codigo_interno')->nullable();
            $table->decimal('peso_capsula', 8, 4)->nullable();
            $table->decimal('peso_muestra', 8, 4)->nullable();
            $table->decimal('peso_capsula_muestra_humedad', 8, 4)->nullable();
            $table->decimal('peso_capsula_muestra_seca', 8, 4)->nullable();
            $table->decimal('porcentaje_humedad', 6, 2)->nullable();

            // Observaciones generales
            $table->text('observaciones')->nullable();

            // Revisión
            $table->string('review_status')->nullable(); // 'pendiente', 'aprobado', 'rechazado'
            $table->unsignedBigInteger('reviewed_by')->nullable(); // Usuario que revisa
            $table->string('reviewer_role')->nullable();
            $table->timestamp('review_date')->nullable();
            $table->text('review_observations')->nullable();

            $table->timestamps();

            // Relaciones
            $table->foreign('analysis_id')->references('id')->on('analyses')->onDelete('set null');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('reviewed_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('humidity_analyses');
    }
}