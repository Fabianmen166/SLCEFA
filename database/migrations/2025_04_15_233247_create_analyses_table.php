<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalysesTable extends Migration
{
    public function up()
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->string('process_id');
            $table->unsignedBigInteger('service_id');
            $table->string('status')->default('pending');
            $table->integer('cantidad')->default(1);
            $table->boolean('approved')->nullable();
            $table->timestamps();

            $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
            $table->foreign('service_id')->references('services_id')->on('services')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('analyses');
    }
}