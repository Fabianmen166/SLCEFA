<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            if (Schema::hasColumn('phosphorus_analyses', 'unidades_reporte')) {
                $table->dropColumn('unidades_reporte');
            }
        });
    }

    public function down()
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            $table->string('unidades_reporte')->nullable();
        });
    }
}; 