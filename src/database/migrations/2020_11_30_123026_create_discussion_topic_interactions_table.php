<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscussionTopicInteractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discussion_topic_interactions', function (Blueprint $table) {
            $table->bigInteger('course_id')->index();
            $table->bigInteger('user_id')->index();
            $table->bigInteger('item_id')->nullable()->index();
            $table->bigInteger('item_canvas_id')->nullable()->index();
            $table->dateTime('viewed')->index();
            $table->text('url');
            $table->enum('device', ['DESKTOP','MOBILE'])->nullable();
            $table->index(['course_id','item_id','user_id','viewed']);
            $table->primary(['course_id','item_canvas_id','user_id','viewed']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discussion_topic_interactions');
    }
}
