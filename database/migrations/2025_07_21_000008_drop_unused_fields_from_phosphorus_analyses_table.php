<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            $dropFields = [
                'precision_analitica',
                'veracidad_analitica',
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
            ];
            foreach ($dropFields as $field) {
                if (Schema::hasColumn('phosphorus_analyses', $field)) {
                    $table->dropColumn($field);
                }
            }
        });
    }

    public function down()
    {
        // No se implementa el down para evitar inconsistencias
    }
}; 