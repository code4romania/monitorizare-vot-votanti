<?php

use App\Helpers\CsvHandler;
use Illuminate\Database\Seeder;
use App\Page;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = CsvHandler::convertToArray('resources/files/pages/pages.csv', true);
        foreach ($data as $row) {
            Page::create($row);
        }
    }
}
