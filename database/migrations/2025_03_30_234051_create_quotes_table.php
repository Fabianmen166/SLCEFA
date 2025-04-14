<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotesTable extends Migration
{
    public function up()
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->string('quote_id')->primary();
            $table->unsignedBigInteger('customers_id');
            $table->decimal('total', 10, 2)->default(0.00);
            $table->unsignedBigInteger('user_id');
            $table->string('archivo')->nullable();
            $table->timestamps();

            $table->foreign('customers_id')->references('customers_id')->on('customers')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade'); // Cambiado de 'id' a 'user_id'
        });
    }

    public function down()
    {
        Schema::dropIfExists('quotes');
    }
}