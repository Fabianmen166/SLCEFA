<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicePackagesTable extends Migration
{
    public function up()
    {
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id('service_packages_id');
            $table->string('nombre')->unique();
            $table->decimal('precio', 10, 2);
            $table->boolean('acreditado')->default(false);
            $table->text('included_services')->nullable(); // Almacena un array de IDs de servicios en formato JSON
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_packages');
    }
}