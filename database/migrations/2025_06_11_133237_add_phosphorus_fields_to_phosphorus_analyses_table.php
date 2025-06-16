<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhosphorusFieldsToPhosphorusAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            $table->json('reporte_resultados')->nullable();
            $table->json('controles_calidad')->nullable();
            $table->string('unidad_concentracion')->nullable();
            $table->string('regresion')->nullable();
            $table->string('longitud_onda')->nullable();
            $table->string('espesor_capa')->nullable();
            $table->timestamp('fecha_hora_medida')->nullable();
            $table->string('coeficientes_calculados')->nullable();
            $table->string('grado_determinacion')->nullable();
            $table->string('valor_limite')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            $table->dropColumn([
                'reporte_resultados',
                'controles_calidad',
                'unidad_concentracion',
                'regresion',
                'longitud_onda',
                'espesor_capa',
                'fecha_hora_medida',
                'coeficientes_calculados',
                'grado_determinacion',
                'valor_limite',
            ]);
        });
    }
}
