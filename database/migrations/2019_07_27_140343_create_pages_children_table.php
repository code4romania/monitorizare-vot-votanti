<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesChildrenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages_children', function (Blueprint $table) {
            $table->unsignedInteger('parent_id');
            $table->foreign('parent_id')->references('id')->on('pages');
            $table->unsignedInteger('child_id');
            $table->foreign('child_id')->references('id')->on('pages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pages_children');
    }
}
