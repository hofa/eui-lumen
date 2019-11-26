<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigEmailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $status = config('options.status');
        Schema::create('config_email', function (Blueprint $table) use ($status) {
            $table->bigIncrements('id');
            $table->string('email');
            $table->string('salt');
            $table->string('password');
            $table->string('secure')->default('ssl');
            $table->string('server');
            $table->string('server_port');
            $table->enum('status', $status)->default('Normal');
            $table->integer('every_day_send_limit')->default(0);
            $table->integer('total_send_limit')->default(0);
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
        Schema::dropIfExists('config_email');
    }
}
