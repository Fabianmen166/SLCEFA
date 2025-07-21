<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            $table->string('analista')->nullable()->after('intervalo_metodo');
        });
    }

    public function down()
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            $table->dropColumn('analista');
        });
    }
}; 