<?php

use App\Helpers\PrecinctImporter;
use Illuminate\Database\Seeder;

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

    	$this->importPrecinctsFromFile('resources/files/precincts/Precincts.xlsx');
   		$this->importPrecinctsFromFile('resources/files/precincts/Diaspora.json');
    }
    
    private function importPrecinctsFromFile($filePath) {

        $importer = new PrecinctImporter();
    	try {
    	    $file = new SplFileObject($filePath);
    	    $importer->importFromFile($file, false);
    	}

    	catch(Exception $e) {
    		die('Error loading file "'.pathinfo($file, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    	}
    }
}

