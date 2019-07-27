<?php

use App\Precinct;
use Illuminate\Database\Seeder;

use App\IncidentType;
use App\Incident;
use App\User;
use App\City;
use App\County;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        //IncidentType::truncate();
        Incident::truncate();
        City::truncate();
        County::truncate();
        Precinct::truncate();
        User::getQuery()->delete();
        Eloquent::unguard();

        $this->call(CountiesTableSeeder::class);
        $this->call(CitiesTableSeeder::class);
        $this->call(IncidentTypesTableSeeder::class);
        //$this->call(IncidentsTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(PrecinctsTableSeeder::class);
    }
}
