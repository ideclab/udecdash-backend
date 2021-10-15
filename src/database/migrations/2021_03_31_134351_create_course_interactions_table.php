<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseInteractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_interactions', function (Blueprint $table) {
            $table->primary(['course_id','user_id', 'interaction_date']);
            $table->bigInteger('course_id')->index();
            $table->bigInteger('user_id')->index();
            $table->mediumInteger('year_month')->index();
            $table->date('interaction_date');
            $table->index(['course_id', 'year_month']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_interactions');
    }
}
