<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $settingType = array_keys(config('options.settingType'));
        Schema::create('setting', function (Blueprint $table) use ($settingType) {
            $table->bigIncrements('id');
            $table->enum('type', $settingType)->default('GKV');
            $table->string('field')->unique();
            $table->string('val');
            $table->string('mark');
            $table->integer('sorted');
            $table->integer('parent_id')->default(0);
            $table->json('options');
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
        Schema::dropIfExists('setting');
    }
}
