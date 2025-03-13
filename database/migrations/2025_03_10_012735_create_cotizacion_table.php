<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotizacionTable extends Migration
{
    public function up()
    {
        Schema::create('cotizacion', function (Blueprint $table) {
            $table->string('id_cotizacion')->primary(); // Clave primaria como string
            $table->unsignedBigInteger('id_user'); // Relación con users
            $table->string('nombre_empresa')->nullable(); // Opcional
            $table->string('nombre_persona');
            $table->string('nit');
            $table->string('direccion');
            $table->string('telefono');
            $table->string('correo')->nullable(); // Opcional
            $table->decimal('precio', 10, 2);
            $table->timestamp('fecha')->useCurrent();
            $table->string('estado_de_pago')->default('pendiente');
            $table->string('archivo')->nullable();
            $table->timestamps(); // created_at y updated_at

            // Relación foránea con users
            $table->foreign('id_user')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cotizacion');
    }
}