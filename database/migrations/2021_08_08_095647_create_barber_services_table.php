<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarberServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barber_services', function (Blueprint $table) {
            $table->id();

            $table->double('price')->default(0.0);
            $table->integer('duration')->default(15);

            $table->unsignedBigInteger('barber_id');
            $table->foreign('barber_id')->references('id')->on('barbers')->cascadeOnUpdate()->cascadeOnDelete();
           
            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnUpdate()->cascadeOnDelete();
           
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
        Schema::dropIfExists('barber_services');
    }
}
