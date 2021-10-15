<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnprocessableInteractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unprocessable_interactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('course_id')->index();
            $table->bigInteger('user_id')->index();
            $table->text('url');
            $table->string('error_label')->nullable();
            $table->string('error_message')->nullable();
            $table->text('log');
            $table->index(['course_id','user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unprocessable_interactions');
    }
}
