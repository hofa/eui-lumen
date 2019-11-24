<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $yn = array_keys(config('options.yn'));
        Schema::create('user_address', function (Blueprint $table) use ($yn) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->string('country')->default('中国');
            $table->string('province');
            $table->string('city');
            $table->string('area');
            $table->string('street')->nullable(true);
            $table->string('recipient');
            $table->string('mobile');
            $table->string('address');
            $table->string('zip_code')->nullable(true);
            $table->enum('is_default', $yn)->default('Yes');
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
        Schema::dropIfExists('user_address');
    }
}
