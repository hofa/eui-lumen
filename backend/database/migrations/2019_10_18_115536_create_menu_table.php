<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $statusType = array_keys(config('options.status'));
        $menuType = array_keys(config('options.menuType'));
        $requestType = array_keys(config('options.requestType'));
        Schema::create('menu', function (Blueprint $table) use ($statusType, $menuType, $requestType) {
            $table->bigIncrements('id');
            $table->integer('parent_id')->unsigned();
            $table->string('title');
            $table->string('path')->default('');
            $table->string('icon')->default('');
            $table->enum('type', $menuType)->default($menuType[0]);
            $table->enum('request_type', $requestType)->default($requestType[0]);
            $table->integer('sorted')->unsigned()->default(0);
            $table->enum('display', $statusType)->default($statusType[0]);
            $table->enum('status', $statusType)->default($statusType[0]);
            $table->string('extends')->default('');
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
        Schema::dropIfExists('menu');
    }
}
