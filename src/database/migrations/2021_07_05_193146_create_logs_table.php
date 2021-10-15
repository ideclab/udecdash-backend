<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->index();
            $table->bigInteger('course_id')->nullable()->index();
            $table->uuid('session_id');
            $table->string('context');
            $table->string('report')->nullable();
            $table->string('deep')->nullable();
            $table->string('reference')->nullable();
            $table->json('params')->nullable();
            $table->dateTime('created_at');
            $table->index(['course_id','context','report', 'deep']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
