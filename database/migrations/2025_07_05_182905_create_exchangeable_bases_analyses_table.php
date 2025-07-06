<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExchangeableBasesAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchangeable_bases_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('analysis_id');
            $table->string('process_id');
            $table->unsignedBigInteger('service_id');
            $table->string('consecutivo_no')->nullable();
            $table->date('fecha_analisis')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('codigo_probeta')->nullable();
            $table->string('codigo_equipo')->nullable();
            $table->json('controles_analiticos')->nullable();
            $table->json('precision_analitica')->nullable();
            $table->json('veracidad_analitica')->nullable();
            $table->json('muestra_referencia_certificada_analitica')->nullable();
            $table->json('items_ensayo')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('status')->default('pending');
            $table->string('review_status')->default('pending');
            $table->string('reviewed_by')->nullable();
            $table->string('reviewer_role')->nullable();
            $table->dateTime('review_date')->nullable();
            $table->text('review_observations')->nullable();
            $table->timestamps();

            $table->foreign('analysis_id')->references('id')->on('analyses')->onDelete('cascade');
            $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
            $table->foreign('service_id')->references('services_id')->on('services')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchangeable_bases_analyses');
    }
}
