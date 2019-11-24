<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tag = array_keys(config('options.userTag'));
        Schema::create('user_tag', function (Blueprint $table) use ($tag) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->enum('tag', $tag);
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
        Schema::dropIfExists('user_tag');
    }
}
