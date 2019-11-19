<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIpBlackWhiteListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $type = array_keys(config('options.IPBlackWhiteListType'));
        Schema::create('ip_black_white_list', function (Blueprint $table) use ($type) {
            $table->bigIncrements('id');
            $table->integer('role_id')->default(0);
            $table->ipAddress('ip');
            $table->enum('type', $type)->default('White');
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
        Schema::dropIfExists('ip_black_white_list');
    }
}
