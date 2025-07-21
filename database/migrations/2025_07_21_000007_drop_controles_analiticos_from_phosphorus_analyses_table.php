<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            if (Schema::hasColumn('phosphorus_analyses', 'controles_analiticos')) {
                $table->dropColumn('controles_analiticos');
            }
        });
    }

    public function down()
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            $table->json('controles_analiticos')->nullable();
        });
    }
}; 