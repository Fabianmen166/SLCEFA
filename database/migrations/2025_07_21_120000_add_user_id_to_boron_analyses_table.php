<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('boron_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('boron_analyses', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('fecha_analisis');
                $table->foreign('user_id')->references('user_id')->on('users');
            }
        });
    }

    public function down()
    {
        Schema::table('boron_analyses', function (Blueprint $table) {
            if (Schema::hasColumn('boron_analyses', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
}; 