<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barbers', function (Blueprint $table) {
           
            $table->id();

            $table->string('password');
            $table->string('salon_code');

            $table->unsignedBigInteger('salon_id');
            $table->foreign('salon_id')->references('id')->on('salons')->cascadeOnDelete()->cascadeOnUpdate();
           
            $table->string('name')->nullable();
            
            $table->string('phone_number')->nullable();
            $table->string('facebook_link')->nullable();
            $table->string('instagram_link')->nullable();
            $table->string('whatsapp_number')->nullable();

            $table->string('status')->default('pending');
            $table->boolean('is_availble')->default(true);

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
        Schema::dropIfExists('barbers');
    }
}
