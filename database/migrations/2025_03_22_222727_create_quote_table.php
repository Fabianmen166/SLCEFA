<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote', function (Blueprint $table) {
            $table->string('quote_id')->primary(); // Cambiado de id() a unsignedBigInteger, no auto-incrementado
            $table->unsignedBigInteger('customers_id');
            $table->string('total');
            $table->unsignedBigInteger('id_user');
            $table->string('archivo')->nullable();
            $table->timestamps();
    
            $table->foreign('customers_id')->references('customers_id')->on('customers')->onDelete('cascade');
        });
    
        // Pivot table for quote and services
        Schema::create('quote_service', function (Blueprint $table) {
            $table->id();
            $table->string('quote_id');
            $table->unsignedBigInteger('services_id');
            $table->string('cantidad');
            $table->string('subtotal');
            $table->timestamps();
    
            $table->foreign('quote_id')->references('quote_id')->on('quote')->onDelete('cascade');
            $table->foreign('services_id')->references('services_id')->on('services')->onDelete('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('quote_service');
        Schema::dropIfExists('quote');
    }
}
