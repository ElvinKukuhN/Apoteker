<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('image_path')->nullable();
            $table->float('suhu')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->longText('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });

    }
}
