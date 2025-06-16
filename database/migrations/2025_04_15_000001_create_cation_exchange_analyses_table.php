<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCationExchangeAnalysesTable extends Migration
{
    public function up()
    {
        Schema::create('cation_exchange_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('process_id');
            $table->unsignedBigInteger('service_id');
            $table->string('consecutivo_no')->nullable();
            $table->date('fecha_analisis')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('codigo_probeta')->nullable();
            $table->string('codigo_equipo')->nullable();
            $table->json('controles_analiticos')->nullable();
            $table->json('precision_analitica')->nullable();
            $table->json('items_ensayo')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('status')->default('pending');
            $table->string('review_status')->default('pending');
            $table->timestamps();

            $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
            $table->foreign('service_id')->references('services_id')->on('services')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cation_exchange_analyses');
    }
} 