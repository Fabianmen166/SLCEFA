<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessServiceTable extends Migration
{
    public function up()
    {
        Schema::create('process_service', function (Blueprint $table) {
            $table->id();
            $table->string('process_id');
            $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
            $table->foreignId('services_id')->constrained('services', 'services_id')->onDelete('cascade');
            $table->integer('cantidad')->default(1);
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('process_service');
    }
}