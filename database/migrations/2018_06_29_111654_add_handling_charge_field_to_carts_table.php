<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHandlingChargeFieldToCartsTable extends Migration
{

    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->decimal('handling_charge', 10, 2)->default(0)->comment('COD/Other charge')->after('shipping_cost');
        });
    }

    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('handling_charge');
        });
    }
}

