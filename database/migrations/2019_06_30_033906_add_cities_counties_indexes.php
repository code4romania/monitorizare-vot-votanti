<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddCitiesCountiesIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->index('county_id');
        });
        Schema::table('counties', function (Blueprint $table) {
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex(['county_id']);
        });
        Schema::table('counties', function (Blueprint $table) {
            $table->dropIndex(['code']);
        });
    }
}
