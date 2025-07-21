<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('boron_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('boron_analyses', 'consecutivo_no')) {
                $table->string('consecutivo_no');
            }
            if (!Schema::hasColumn('boron_analyses', 'analysis_id')) {
                $table->unsignedBigInteger('analysis_id');
                $table->foreign('analysis_id')->references('id')->on('analyses');
            }
            if (!Schema::hasColumn('boron_analyses', 'fecha_analisis')) {
                $table->date('fecha_analisis');
            }
            if (!Schema::hasColumn('boron_analyses', 'user_id')) {
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('user_id')->on('users');
            }
            if (!Schema::hasColumn('boron_analyses', 'equipo_utilizado')) {
                $table->string('equipo_utilizado');
            }
            if (!Schema::hasColumn('boron_analyses', 'intervalo_metodo')) {
                $table->string('intervalo_metodo');
            }
            if (!Schema::hasColumn('boron_analyses', 'analista')) {
                $table->string('analista')->nullable();
            }
            if (!Schema::hasColumn('boron_analyses', 'codigo_interno')) {
                $table->string('codigo_interno')->nullable();
            }
            if (!Schema::hasColumn('boron_analyses', 'peso_muestra')) {
                $table->decimal('peso_muestra', 10, 4)->nullable();
            }
            if (!Schema::hasColumn('boron_analyses', 'pw')) {
                $table->decimal('pw', 10, 4)->nullable();
            }
            if (!Schema::hasColumn('boron_analyses', 'v_extractante')) {
                $table->decimal('v_extractante', 10, 4)->nullable();
            }
            if (!Schema::hasColumn('boron_analyses', 'lectura_blanco')) {
                $table->decimal('lectura_blanco', 10, 4)->nullable();
            }
            if (!Schema::hasColumn('boron_analyses', 'factor_dilucion')) {
                $table->decimal('factor_dilucion', 10, 4)->nullable();
            }
            if (!Schema::hasColumn('boron_analyses', 'boron_disponible_mg_l')) {
                $table->decimal('boron_disponible_mg_l', 10, 4)->nullable();
            }
            if (!Schema::hasColumn('boron_analyses', 'boron_disponible_mg_kg')) {
                $table->decimal('boron_disponible_mg_kg', 10, 4)->nullable();
            }
            if (!Schema::hasColumn('boron_analyses', 'observaciones_item')) {
                $table->text('observaciones_item')->nullable();
            }
        });
    }

    public function down()
    {
        // No se implementa el down para evitar pérdida de datos
    }
}; 