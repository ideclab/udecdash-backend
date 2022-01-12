<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCanvasMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('canvas_mails', function (Blueprint $table) {
            $table->id();
            $table->string('reference_ids', 1024)->nullable();
            $table->json('options');
            $table->boolean('was_sended')->default(false);
            $table->bigInteger('author_id')->index();
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
        Schema::dropIfExists('canvas_mails');
    }
}
