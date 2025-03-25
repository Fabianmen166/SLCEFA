<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customers_id');
            $table->string('solicitante')->nullable(); // Opcional
            $table->string('contacto');
            $table->string('telefono');
            $table->string('nit');
            $table->string('correo')->nullable(); // Opcional
            $table->string('tipo_cliente')->default('externo'); // Nuevo campo: interno, externo, trabajador
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('_customers');
    }
}
