<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Tables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tables',function(Blueprint $table){

            $table->id();
            
            $table->string('table_name',255)->nullable();

            $table->text('table_price')->nullable();
            
            $table->text('table_seats')->nullable();

            $table->text('table_img_loc')->nullable();
            
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
        Schema::dropIfExists('tables');
    }
}
