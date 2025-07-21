<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('boron_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('boron_analyses', 'equipo_utilizado')) {
                $table->string('equipo_utilizado');
            }
        });
    }

    public function down()
    {
        Schema::table('boron_analyses', function (Blueprint $table) {
            if (Schema::hasColumn('boron_analyses', 'equipo_utilizado')) {
                $table->dropColumn('equipo_utilizado');
            }
        });
    }
}; 