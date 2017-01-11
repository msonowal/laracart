<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartItemsTable extends Migration
{
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cart_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->string('variant')->nullable();
            $table->integer('quantity')->unsigned();
            $table->decimal('tax_rate', 4, 2)->comment('Tax rate on the item or when it was added');
            $table->decimal('price', 10, 2);
            $table->string('options', 3000)->nullable()->comment('extra meta data');
            $table->timestamps();
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
}