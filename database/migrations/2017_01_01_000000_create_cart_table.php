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
            $table->string('cart_type')->nullable()->comment('abandoned / recovered / wishlist');
            $table->string('email')->nullable();
            $table->string('customer_id')->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->tinyInteger('discount_type')->default(\Msonowal\Laracart\Models\Cart::DISCOUNT_TYPE_NONE);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('discount_code')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('carts');
    }
}
