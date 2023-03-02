<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRacetracks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('racetracks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->longText('desc');
            $table->integer('type_id');
            $table->string('distance');
            $table->longText('address');
            $table->string('status')->default("Active");
            $table->longText('images')->nullable(true);
            $table->string('image_uid')->nullable(true);
            $table->longText('video_link')->nullable(true);
            $table->date('allocation_start_date');
            $table->date('allocation_end_date');
            $table->string('allocation_time');
            $table->string('owner_id');
            $table->string('owner_email');
            $table->string('owner_contact');
            $table->string('responsible_person');
            $table->integer('rating');
            $table->tinyInteger('shared')->default(0);
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
        Schema::dropIfExists('racetracks');
    }
}
