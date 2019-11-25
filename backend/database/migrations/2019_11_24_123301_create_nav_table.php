<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNavTable extends Migration
{
    /**
     * Run the migrations.
     *
     *
     * @return void
     */
    public function up()
    {
        $yn = array_keys(config('options.yn'));
        $statusType = array_keys(config('options.status'));
        $navType = array_keys(config('options.navType'));
        Schema::create('nav', function (Blueprint $table) use ($yn, $statusType, $navType) {
            $table->bigIncrements('id');
            $table->integer('parent_id')->unsigned();
            $table->string('title');
            $table->string('desc')->nullable(true);
            $table->enum('is_link', $yn);
            $table->string('link_address')->nullable(true);
            $table->string('flag')->nullable(true);
            $table->string('path')->default('');
            $table->enum('type', $navType)->default($navType[0]);
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
        Schema::dropIfExists('nav');
    }
}
