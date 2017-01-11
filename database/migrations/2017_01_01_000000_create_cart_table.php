<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartTable extends Migration
{
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('session_key')->nullable();
            $table->string('cart_type')->nullable()->comment('abandoned / recovered / wishlist');
            $table->string('email')->nullable();
            $table->string('customer_id')->nullable();
            $table->decimal('shipping_cost', 10, 2);
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('carts');
    }
}
