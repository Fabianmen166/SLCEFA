<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('phosphorus_analyses', 'codigo_interno')) {
                $table->string('codigo_interno')->nullable()->after('analista');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'peso_muestra')) {
                $table->decimal('peso_muestra', 10, 4)->nullable()->after('codigo_interno');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'pw')) {
                $table->decimal('pw', 10, 4)->nullable()->after('peso_muestra');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'v_extractante')) {
                $table->decimal('v_extractante', 10, 4)->nullable()->after('pw');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'lectura_blanco')) {
                $table->decimal('lectura_blanco', 10, 4)->nullable()->after('v_extractante');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'factor_dilucion')) {
                $table->decimal('factor_dilucion', 10, 4)->nullable()->after('lectura_blanco');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'fosforo_disponible_mg_l')) {
                $table->decimal('fosforo_disponible_mg_l', 10, 4)->nullable()->after('factor_dilucion');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'fosforo_disponible_mg_kg')) {
                $table->decimal('fosforo_disponible_mg_kg', 10, 4)->nullable()->after('fosforo_disponible_mg_l');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'observaciones_item')) {
                $table->text('observaciones_item')->nullable()->after('fosforo_disponible_mg_kg');
            }
        });
    }

    public function down()
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            if (Schema::hasColumn('phosphorus_analyses', 'codigo_interno')) {
                $table->dropColumn('codigo_interno');
            }
            if (Schema::hasColumn('phosphorus_analyses', 'peso_muestra')) {
                $table->dropColumn('peso_muestra');
            }
            if (Schema::hasColumn('phosphorus_analyses', 'pw')) {
                $table->dropColumn('pw');
            }
            if (Schema::hasColumn('phosphorus_analyses', 'v_extractante')) {
                $table->dropColumn('v_extractante');
            }
            if (Schema::hasColumn('phosphorus_analyses', 'lectura_blanco')) {
                $table->dropColumn('lectura_blanco');
            }
            if (Schema::hasColumn('phosphorus_analyses', 'factor_dilucion')) {
                $table->dropColumn('factor_dilucion');
            }
            if (Schema::hasColumn('phosphorus_analyses', 'fosforo_disponible_mg_l')) {
                $table->dropColumn('fosforo_disponible_mg_l');
            }
            if (Schema::hasColumn('phosphorus_analyses', 'fosforo_disponible_mg_kg')) {
                $table->dropColumn('fosforo_disponible_mg_kg');
            }
            if (Schema::hasColumn('phosphorus_analyses', 'observaciones_item')) {
                $table->dropColumn('observaciones_item');
            }
        });
    }
}; 