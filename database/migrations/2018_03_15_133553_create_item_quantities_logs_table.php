<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemQuantitiesLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_quantity_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->Integer('status');
            $table->Integer('item_id');
            $table->Integer('purchase_id');
            $table->Integer('vendor_id');
            $table->Integer('quantity');
            $table->String('date');
            $table->String('added_by');
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
        //
    }
}
