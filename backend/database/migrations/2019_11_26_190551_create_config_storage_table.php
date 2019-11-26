<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigStorageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $status = config('options.status');
        $storage = config('options.storageType');
        Schema::create('config_storage', function (Blueprint $table) use ($status, $storage) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('salt');
            $table->string('extends', 2046);
            $table->string('upload_path');
            $table->string('access_domain');
            $table->enum('type', $storage)->default('Local');
            $table->enum('status', $status)->default('default');
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
        Schema::dropIfExists('config_storage');
    }
}
