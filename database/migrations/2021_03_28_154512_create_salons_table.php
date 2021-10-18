<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salons', function (Blueprint $table) {
           
            $table->id();
            $table->string('name')->unique();

            $table->unsignedBigInteger('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
           
            $table->unsignedBigInteger('city_id');
            // $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete()->cascadeOnUpdate();
           
            $table->string('type')->default('male');
            $table->string('location')->nullable();

            $table->string('lat_location')->nullable();
            $table->string('long_location')->nullable();

            $table->string('phone_number')->nullable();
            $table->string('facebook_link')->nullable();
            $table->string('whatsapp_number')->nullable();

            $table->string('status')->default('pending');
            $table->boolean('is_open')->default(true);

            $table->string('salon_code')->unique();

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
        Schema::dropIfExists('salons');
    }
}
