<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnalysisIdToCationExchangeAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cation_exchange_analyses', function (Blueprint $table) {
            $table->unsignedBigInteger('analysis_id')->nullable()->after('id');
            $table->foreign('analysis_id')->references('id')->on('analyses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cation_exchange_analyses', function (Blueprint $table) {
            $table->dropForeign(['analysis_id']);
            $table->dropColumn('analysis_id');
        });
    }
}
