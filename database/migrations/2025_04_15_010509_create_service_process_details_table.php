<?php
// database/migrations/xxxx_create_service_process_details_table.php
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
            $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
            $table->foreignId('services_id')->constrained('services', 'services_id')->onDelete('cascade');
            $table->date('analysis_date');
            $table->json('details')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->string('reviewer_role')->nullable();
            $table->dateTime('review_date')->nullable();
            $table->text('review_observations')->nullable();
            $table->enum('review_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_process_details');
    }
}