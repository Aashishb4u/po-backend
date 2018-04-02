<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddressTwoColumsToVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('address_line_two',255)->nullable();
            $table->string('address_line_one',255)->nullable();
            $table->string('company_name',255)->nullable();
            $table->integer('pin_code')->nullable();
            $table->string('bank_name',255)->nullable();
            $table->string('gst_number',255)->nullable();
            $table->string('pan_number',255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('address_line_two');
            $table->dropColumn('address_line_one');
            $table->dropColumn('company_name');
            $table->dropColumn('pin_code');
            $table->dropColumn('bank_name');
            $table->dropColumn('gst_number');
            $table->dropColumn('pan_number');
        });
    }
}
