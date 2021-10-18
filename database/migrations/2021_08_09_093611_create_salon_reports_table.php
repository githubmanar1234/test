<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalonReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salon_reports', function (Blueprint $table) {
           
            $table->id();

            $table->text('reason');
          
            $table->unsignedBigInteger('salon_id');
            $table->foreign('salon_id')->references('id')->on('salons')->cascadeOnDelete()->cascadeOnUpdate();
          
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
           
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
        Schema::dropIfExists('salon_reports');
    }
}
