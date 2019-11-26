<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $status = config('options.status');
        $smsType = config('options.smsType');
        Schema::create('config_sms', function (Blueprint $table) use ($status, $smsType) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('salt');
            $table->string('extends', 2046);
            $table->enum('type', $smsType);
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
        Schema::dropIfExists('config_sms');
    }
}
