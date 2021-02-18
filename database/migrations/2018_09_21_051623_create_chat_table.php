<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bot_id');
            $table->string('event_type');
            $table->string('source_type');
            $table->string('reply_token');

            $table->string('user_id');
            $table->string('group_id')->nullable();
            $table->string('room_id')->nullable();

            $table->string('message_type');
            $table->string('message_id');

            $table->string('text')->nullable();

            $table->string('package_id')->nullable();
            $table->string('sticker_id')->nullable();

            $table->string('title')->nullable();
            $table->string('address')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();

            $table->string('image_path')->nullable();

            $table->timestamp('message_time');
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
        Schema::dropIfExists('chat');
    }
}
