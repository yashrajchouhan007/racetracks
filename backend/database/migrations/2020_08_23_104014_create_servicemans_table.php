<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicemansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicemans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('contact_no');
            $table->string('email_id');
            $table->string('address');
            $table->longText('specializations');
            $table->longText('available_hours');
            $table->integer('rating');
            $table->string('status')->default("Active");
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
        Schema::dropIfExists('servicemans');
    }
}
