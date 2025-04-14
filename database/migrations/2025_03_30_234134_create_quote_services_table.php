<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuoteServicesTable extends Migration
{
    public function up()
    {
        Schema::create('quote_services', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->string('quote_id'); // ID de la cotización
            $table->unsignedBigInteger('services_id')->nullable(); // ID del servicio (opcional)
            $table->unsignedBigInteger('service_packages_id')->nullable(); // ID del paquete de servicios (opcional)
            $table->integer('cantidad')->nullable(); // Cantidad
            $table->decimal('subtotal', 10, 2)->nullable(); // Subtotal
            $table->timestamps(); // created_at y updated_at

            // Claves foráneas
            $table->foreign('quote_id')->references('quote_id')->on('quotes')->onDelete('cascade');
            $table->foreign('services_id')->references('services_id')->on('services')->onDelete('cascade');
            $table->foreign('service_packages_id')->references('service_packages_id')->on('service_packages')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('quote_services');
    }
}