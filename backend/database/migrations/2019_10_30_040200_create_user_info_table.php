<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sex = array_keys(config('options.sex'));
        Schema::create('user_info', function (Blueprint $table) use ($sex) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned()->unique();
            $table->string('qq')->nullable(true);
            $table->string('weixin')->nullable(true);
            $table->string('mobile')->nullable(true)->unique()->comment('手机号码');
            $table->string('mobile_area', 6)->default('0755');
            $table->string('email')->nullable(true)->unique();
            $table->string('avatar')->nullable(true);
            $table->enum('sex', $sex)->default($sex[0]);
            $table->string('nickname', 50)->nullable(true);
            $table->string('real_name', 10)->nullable(true);
            $table->string('idcard')->nullable(true)->unique();
            $table->integer('from_user_id')->unsigned()->default(0)->comment('由其他用户注册进来');
            $table->ipAddress('register_ip')->nullable(true);
            $table->ipAddress('last_login_ip')->nullable(true);
            $table->dateTime('last_login_time')->nullable(true);
            $table->decimal('wallet', 10, 2)->default(0);
            $table->integer('level_id')->unsigned()->default(0);
            $table->integer('channel_id')->unsigned()->default(0);
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
        Schema::dropIfExists('user_info');
    }
}
