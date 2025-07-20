<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('analytical_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_id')->constrained()->onDelete('cascade');
            
            // Campos de control analítico
            $table->decimal('masa_suelo', 10, 4)->nullable();
            $table->decimal('masa_agua', 10, 4)->nullable();
            $table->decimal('masa_suelo_seco', 10, 4)->nullable();
            $table->decimal('humedad_fortificada_teorica', 10, 4)->nullable();
            $table->decimal('humedad_obtenida', 10, 4)->nullable();
            $table->decimal('humedad_fortificada', 10, 4)->nullable();
            $table->decimal('recuperacion', 10, 4)->nullable();
            $table->string('valor_referencia')->nullable();
            $table->string('valor_obtenido')->nullable();
            $table->string('blanco_metodo')->nullable();
            $table->string('resultado')->nullable();
            $table->string('limite_cuantificacion_metodo')->nullable();
            $table->string('rango_metodo')->nullable();
            $table->decimal('humedad_replica_1', 10, 4)->nullable();
            $table->decimal('humedad_replica_2', 10, 4)->nullable();
            $table->decimal('dpr', 10, 4)->nullable();
            $table->string('identificacion_mf')->nullable(); // ID para muestra fortificada
            $table->string('identificacion_mr')->nullable(); // ID para muestra de referencia
            $table->string('identificacion_dm')->nullable(); // ID para muestra duplicada
            $table->string('identificacion_bm')->nullable(); // ID para muestra de blanco
            $table->enum('estado', ['Aceptable', 'No Aceptable'])->nullable();
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('analytical_controls');
    }
};