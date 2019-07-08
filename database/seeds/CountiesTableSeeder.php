<?php

use Illuminate\Database\Seeder;
use App\County;
use App\Helpers\CsvHandler;

class CountiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rows = $this->getCounties();

        if ($rows) {
            foreach ($rows as $key => $row) {
                County::create([
                    'id' => $row[0],
                    'name' => $row[1],
                    'code' =>  $row[2]
                ]);
            }
        }       
    }

    private function getCounties()
    {
        $data = CsvHandler::convertToArray('resources/files/county/county.csv');
        return $data;
    }
}

