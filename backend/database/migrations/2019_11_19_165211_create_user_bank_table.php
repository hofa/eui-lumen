<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $yn = array_keys(config('options.yn'));
        Schema::create('user_bank', function (Blueprint $table) use ($yn) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->string('bank_name');
            $table->string('bank_branch');
            $table->string('bank_card')->unique();
            $table->string('real_name');
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
        Schema::dropIfExists('user_bank');
    }
}
