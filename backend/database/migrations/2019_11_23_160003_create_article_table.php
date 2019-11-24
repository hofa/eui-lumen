<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $editor = array_keys(config('options.articleEditor'));
        $status = array_keys(config('options.status'));
        Schema::create('article', function (Blueprint $table) use ($editor, $status) {
            $table->bigIncrements('id');
            $table->string('title');
            // $table->string('description');
            $table->text('content');
            $table->integer('user_id')->unsigned();
            $table->enum('editor', $editor)->default('markdown');
            $table->string('template')->nullable(true);
            $table->enum('status', $status)->default('Normal');
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
        Schema::dropIfExists('article');
    }
}
