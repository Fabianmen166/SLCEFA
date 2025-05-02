<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhAnalysesTable extends Migration
{
    public function up()
    {
        Schema::create('ph_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('analysis_id');
            $table->string('consecutivo_no');
            $table->date('fecha_analisis');
            $table->unsignedBigInteger('user_id'); // Changed from 'analista_id' to 'user_id'
            $table->string('codigo_probeta');
            $table->string('codigo_equipo');
            $table->string('serial_electrodo');
            $table->string('serial_sonda_temperatura');
            $table->json('controles_analiticos');
            $table->json('precision_analitica');
            $table->json('items_ensayo');
            $table->text('observaciones')->nullable();
            $table->string('revisado_por')->nullable();
            $table->date('fecha_revision')->nullable();
            $table->string('aprobado')->nullable();
            $table->text('observaciones_revision')->nullable();
            $table->string('review_status')->default('pending');
            $table->string('reviewed_by')->nullable();
            $table->string('reviewer_role')->nullable();
            $table->dateTime('review_date')->nullable();
            $table->text('review_observations')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('analysis_id')->references('id')->on('analyses')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users'); // References 'user_id' in 'users'
        });
    }

    public function down()
    {
        Schema::dropIfExists('ph_analyses');
    }
}