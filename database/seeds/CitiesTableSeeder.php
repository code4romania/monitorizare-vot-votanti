<?php

use Illuminate\Database\Seeder;
use App\City;
use App\Helpers\CsvHandler;
use App\Helpers\CitiesXMLParser;

class CitiesTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $citites = $this->getCities();
        $counties = CsvHandler::convertToArray('resources/files/county/county.csv');

        $currentCity = '';
        $index = 0;

        if ($citites) {
            foreach ($citites as $key => $city) {
                if ($currentCity != $city['name'] && strlen(trim($city['countyCode'])) > 0) {
                    $currentCity = $city['name'];
                    $index++;
                    
                    City::create([
                        'id' => $index,
                        'county_id' => $this->getCountyId($city['countyCode'], $counties),
                        'name' => $city['name'],
                        'siruta_code' => $city['siruta_code'],
                        'el_circle_code' => $city['el_circle_code']
                    ]);
                }
            }
        } 
        
        City::create([
                'county_id' => $this->getCountyId('DI', $counties),
                'name' => 'Disapora',
                'siruta_code' => '0',
                'el_circle_code' => '0'
        ]);
    }

    private function getCities()
    {
        
        $cities = CitiesXMLParser::convertToArray('resources/files/cities/cities.xml');
        return $cities;
    }

    private function getCountyId($countyCode, $counties)
    {
        foreach ($counties as $key => $county) {
            if ($county[2] == $countyCode) {
                return $county[0];
            }
        }
        return false;
    }
}
