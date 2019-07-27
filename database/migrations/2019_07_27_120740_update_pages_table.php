<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE pages CHANGE description content TEXT');

        Schema::table('pages', function (Blueprint $table) {
            $table->string('logo');
            $table->unsignedInteger('parent_id');
            $table->foreign('parent_id')->references('id')->on('pages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE pages CHANGE content description VARCHAR(500)');

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('logo');
            $table->dropForeign('pages_parent_id_foreign');
            $table->dropColumn('parent_id');
        });
    }
}
