<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseProcessingRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_processing_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('course_id')->index();
            $table->bigInteger('course_canvas_id')->index();
            $table->bigInteger('user_id');
            $table->Integer('processing_time')->nullable();
            $table->Integer('members_count')->nullable();
            $table->string('queue_assigned', 100)->nullable();
            $table->Integer('processed_logs')->nullable();
            $table->enum('process_status', ['PENDING', 'FINISHED','FAILED'])->default('PENDING');
            $table->text('failed_motive')->nullable();
            $table->timestamp('finished_at')->nullable();
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
        Schema::dropIfExists('course_processing_requests');
    }
}
