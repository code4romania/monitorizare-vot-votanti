<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateCompositePrecinctIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('precincts', function (Blueprint $table) {
            $table->unique(['city_id', 'precinct_no']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('precincts', function (Blueprint $table) {
            $table->dropUnique(['city_id', 'precinct_no']);
        });
    }
}
