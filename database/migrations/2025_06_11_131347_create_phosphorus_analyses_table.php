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
            $table->foreignId('analysis_id')->constrained('analyses')->onDelete('cascade');
            $table->string('consecutivo_no');
            $table->date('fecha_analisis');
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->string('equipo_utilizado');
            $table->string('resolucion_instrumental');
            $table->string('unidades_reporte');
            $table->string('intervalo_metodo');
            $table->json('items_ensayo');
            $table->json('controles_analiticos');
            $table->json('precision_analitica');
            $table->json('veracidad_analitica');
            $table->text('observaciones')->nullable();
            $table->string('review_status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users', 'user_id');
            $table->string('reviewer_role')->nullable();
            $table->timestamp('review_date')->nullable();
            $table->text('review_observations')->nullable();
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
