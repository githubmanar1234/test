<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarberImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barber_images', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('barber_id');
            $table->foreign('barber_id')->references('id')->on('barbers')->cascadeOnDelete()->cascadeOnUpdate();
          
            $table->string('image','255')->nullable();

            $table->softDeletes();
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
        Schema::dropIfExists('barber_images');
    }
}
