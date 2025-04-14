<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customers_id'); // Clave primaria, auto_increment
            $table->string('solicitante')->nullable(); // Solicitante (opcional)
            $table->string('contacto'); // Nombre de contacto (requerido)
            $table->string('telefono')->nullable(); // Teléfono (opcional)
            $table->string('nit')->unique(); // NIT (requerido, único)
            $table->string('correo')->nullable(); // Correo (opcional)
            $table->unsignedBigInteger('customer_type_id');
            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
}