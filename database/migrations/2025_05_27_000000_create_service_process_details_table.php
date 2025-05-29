<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceProcessDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('service_process_details', function (Blueprint $table) {
            $table->id();
            $table->string('process_id');
            $table->unsignedBigInteger('services_id')->nullable();
            $table->string('type'); // 'service' o 'package'
            $table->string('description')->nullable();
            $table->string('status')->default('pending');
            $table->text('result')->nullable();
            $table->timestamps();

            $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
            $table->foreign('services_id')->references('services_id')->on('services')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_process_details');
    }
} 