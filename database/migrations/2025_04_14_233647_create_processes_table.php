<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessesTable extends Migration
{
    public function up()
    {
        Schema::create('processes', function (Blueprint $table) {
            $table->string('process_id')->primary(); // Change from $table->id('process_id') to string
            $table->string('quote_id');
            $table->foreign('quote_id')->references('quote_id')->on('quotes')->onDelete('cascade');
            $table->string('item_code');
            $table->string('status')->default('pending');
            $table->text('comunicacion_cliente')->nullable();
            $table->integer('dias_procesar')->unsigned();
            $table->date('fecha_recepcion');
            $table->text('descripcion')->nullable();
            $table->string('lugar_muestreo')->nullable();
            $table->date('fecha_muestreo')->nullable();
            $table->unsignedBigInteger('responsable_recepcion')->nullable();
            $table->foreign('responsable_recepcion')->references('user_id')->on('users')->onDelete('set null');
            $table->date('fecha_entrega')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('processes');
    }
}