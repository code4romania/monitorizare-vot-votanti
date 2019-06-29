<?php

use App\Helpers\PrecinctImporter;
use Illuminate\Database\Seeder;
use App\County;
use App\Helpers\CsvHandler;
use App\Precinct;
use App\City;

use Akeneo\Component\SpreadsheetParser\SpreadsheetParser;

class PrecinctsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		DB::disableQueryLog(); //logs slow down inserts
		$counties = CsvHandler::convertToArray('resources/files/county/county.csv');

    	$this->parseRomaniaPrecincts('resources/files/precincts/Precincts.xlsx', $counties);
   		$this->parseDiasporaPrecincts('resources/files/precincts/Diaspora.json');
    }
    
    private function parseRomaniaPrecincts($file, $counties) {

        $importer = new PrecinctImporter();
    	try {
    	    $importer->importFromFile($file, false);
    	}
    	
    	catch(Exception $e) {
    		die('Error loading file "'.pathinfo($file, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    	}
    }
    
    /**
     * Get the Diaspora Precincts.
     * @param unknown $file
     */
    private function parseDiasporaPrecincts($file) {
    	//get Diaspora county ID
    	$county = County::where('code', 'DI')->first();
    	//get Diaspora city ID
    	$city = City::where('name', 'Disapora')->first();
    	//Put the precincts in the table
    	$f = fopen($file, "r");
    	$str = "";
    	while($line = fgets($f)) {
    		$str .= $line;
    	}
    	$obj = json_decode($str);
    	foreach ($obj->markers as $marker) {
    		$precinct = new Precinct([
    				'county_id' => $county->id,
    				'city_id' =>  $city ? $city->id : 1,
    				'siruta_code' =>  0,
    				'circ_no' =>  $marker->country_id,
    				'precinct_no' =>  $marker->n,
    				'headquarter' =>  $marker->m,
    				'address' =>  $marker->a
    		]);
    		$precinct->save();
    	}
    }

	private function getCountyId($countyCode, $counties) 
	{
		foreach ($counties as $key => $county) {
            if ($county[2] == $countyCode) {
                return $county[0];
            }
        }

        echo "County not found: " . $countyCode . "\r\n";
		return 0;
	}


}

