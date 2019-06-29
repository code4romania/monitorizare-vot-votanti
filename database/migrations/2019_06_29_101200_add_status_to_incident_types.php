<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToIncidentTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'incident_types',
            'incidents',
        ];
        foreach ($tables as $table) {
            DB::statement('ALTER TABLE ' . $table . ' ENGINE = InnoDB');
        }

        Schema::table('incident_types', function (Blueprint $table) {
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->unique('code');
        });

        Schema::table('incidents', function(Blueprint $table) {
            DB::statement('ALTER TABLE' . ' incidents' . ' modify incident_type_id int unsigned');
            $table->foreign('incident_type_id')->references('id')->on('incident_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incidents', function(Blueprint $table) {
            $table->dropForeign('incident_type_id');
            DB::statement('ALTER TABLE' . ' incidents' . ' modify incident_type_id varchar(255)');
        });

        Schema::table('incident_types', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropUnique('code');
        });

        $tables = [
            'incident_types',
            'incidents',
        ];
        foreach ($tables as $table) {
            DB::statement('ALTER TABLE ' . $table . ' ENGINE = MyISAM');
        }
    }
}
