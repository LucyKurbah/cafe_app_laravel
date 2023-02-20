<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Items extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    protected $conn= 'pgsql';
    public function up()
    {
        Schema::create('items',function(Blueprint $table){

            $table->id();
            
            $table->string('item_name',255)->nullable();

            $table->text('item_price')->nullable();
            
            $table->text('item_desc')->nullable();

            $table->string('item_img_loc',255)->nullable();

            $table->text('item_stock')->nullable();
            
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
        Schema::dropIfExists('items');
    }
}
