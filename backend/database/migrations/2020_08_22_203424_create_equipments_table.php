<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('desc');
            $table->integer('category_id');
            $table->integer('subcategory_id');
            $table->integer('type_id');
            $table->longText('special_instructions');
            $table->date('allocation_start_date');
            $table->date('allocation_end_date');
            $table->string('allocation_time');
            $table->string('contact_no');
            $table->string('email_id');
            $table->integer('rating');
            $table->longText('images')->nullable(true);
            $table->longText('image_uid')->nullable(true);
            $table->longText('video_link')->nullable(true);
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
        Schema::dropIfExists('equipments');
    }
}
