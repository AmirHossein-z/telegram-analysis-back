<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('details');
            $table->string('post_telegram_id');
            $table->integer('view');
            $table->integer('share');
            $table->integer('type');
            $table->string('tags');
            $table->unsignedBigInteger('channel_id');
            $table->foreign('channel_id')->references('id')->on('channels');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};