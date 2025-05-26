<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConductivityAnalysesTable extends Migration
{
    public function up()
    {
        Schema::create('conductivity_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('analysis_id'); // Relación con la tabla analyses
            $table->string('consecutivo_no'); // Consecutivo No.
            $table->date('fecha_analisis'); // Fecha del análisis
            $table->unsignedBigInteger('user_id'); // ID del analista
            $table->string('equipo_utilizado')->nullable(); // Equipo utilizado
            $table->string('resolucion_instrumental')->nullable(); // Resolución instrumental
            $table->string('unidades_reporte')->nullable(); // Unidades de reporte del equipo
            $table->string('intervalo_metodo')->nullable(); // Intervalo del método
            $table->json('items_ensayo'); // Información de la muestra (peso, volumen, temperatura, lecturas)
            $table->json('controles_analiticos'); // Controles de calidad (blanco del proceso, etc.)
            $table->json('precision_analitica'); // Precisión (duplicados)
            $table->json('veracidad_analitica')->nullable(); // Veracidad (material de referencia, estándar, etc.)
            $table->text('observaciones')->nullable(); // Observaciones del analista
            $table->string('review_status')->default('pending'); // Estado de revisión
            $table->string('reviewed_by')->nullable(); // Nombre de quien revisó
            $table->string('reviewer_role')->nullable(); // Rol de quien revisó
            $table->dateTime('review_date')->nullable(); // Fecha de revisión
            $table->text('review_observations')->nullable(); // Observaciones de la revisión
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('analysis_id')->references('id')->on('analyses')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('conductivity_analyses');
    }
}