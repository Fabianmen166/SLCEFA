<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_process_details', function (Blueprint $table) {
            $table->unsignedBigInteger('quote_service_id')->nullable()->after('process_id');
            $table->foreign('quote_service_id')->references('id')->on('quote_services')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_process_details', function (Blueprint $table) {
            $table->dropForeign(['quote_service_id']);
            $table->dropColumn('quote_service_id');
        });
    }
};
