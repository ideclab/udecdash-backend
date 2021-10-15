<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCanvasTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('canvas_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('user_canvas_id');
            $table->string('access_token', 500);
            $table->string('token_type');
            $table->string('refresh_token', 500);
            $table->integer('expires_in');
            $table->integer('created_at');
            $table->primary('user_canvas_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('canvas_tokens');
    }
}
