<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sf = array_keys(config('options.sf'));
        Schema::create('login_log', function (Blueprint $table) use ($sf) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->ipAddress('ip');
            $table->enum('status', $sf);
            $table->index('user_id');
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
        Schema::dropIfExists('login_log');
    }
}
