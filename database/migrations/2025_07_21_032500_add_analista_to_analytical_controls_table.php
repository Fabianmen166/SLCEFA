<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('analytical_controls', function (Blueprint $table) {
            $table->string('analista')->nullable();
        });
    }

    public function down()
    {
        Schema::table('analytical_controls', function (Blueprint $table) {
            $table->dropColumn('analista');
        });
    }
}; 