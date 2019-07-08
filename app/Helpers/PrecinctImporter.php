<?php

namespace App\Helpers;

use Akeneo\Component\SpreadsheetParser\SpreadsheetParser;
use App\City;
use App\County;
use App\Precinct;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use SplFileObject;

class PrecinctImporter
{

    public function importFromFile(SplFileObject $file, bool $deleteFileAfter)
    {
        $fileExtension = $file->getExtension();
        $isCsv = $fileExtension == "csv";
        $isXlsx = $fileExtension == "xls" || $fileExtension == "xlsx";
        $isJson = $fileExtension == "json";
        $data = [];

        if ($isCsv) {
            $data = $this->readFromCSV($file);
        } else if ($isXlsx) {
            $data = $this->readFromXLSX($file);
        } else if ($isJson) {
            $data = $this->readFromJson($file);
        }

        $this->importPrecinctsFromArray($data);
        if ($deleteFileAfter) {
            $filePath = $file->getRealPath();
            $file = null;
            unlink($filePath);
        }
    }

    private function readFromCSV(SplFileObject $file)
    {

        $data = CsvHandler::convertFileToArray($file);
        $precinctData = array();
        foreach ($data as $rowIndex => $rawPrecinctData) {
            if ($rowIndex > 0) {
                $precinctData[] = [
                    'city_id' => intval($rawPrecinctData[0]),
                    'siruta_code' => intval($rawPrecinctData[1]),
                    'circ_no' => intval($rawPrecinctData[2]),
                    'precinct_no' => intval($rawPrecinctData[3]),
                    'headquarter' => $rawPrecinctData[4],
                    'address' => $rawPrecinctData[5],
                ];
            }
        }
        return $precinctData;

    }

    private function readFromXLSX(SplFileObject $file)
    {
        $workbook = SpreadsheetParser::open($file->getRealPath());
        $cityId = null;

        $precinctId = -1;
        $data = [];
        foreach ($workbook->createRowIterator(0) as $rowIndex => $rowData) {

            if ($rowIndex > 2 && $rowData[0] != '') {

                if (trim($rowData[1]) != '') {
                    $cityId = $this->getCityId($rowData[1], $rowData[0]);
                }
                if ($rowData[4] != $precinctId && $cityId != 0) {
                    $precinctId = $rowData[4];
                    $data[] = [
                        'city_id' => $cityId,
                        'siruta_code' => $rowData[2],
                        'circ_no' => $rowData[3],
                        'precinct_no' => $rowData[4],
                        'headquarter' => $rowData[5],
                        'address' => $rowData[6]
                    ];
                }
            }
        }
        return $data;

    }

    private function readFromJson(SplFileObject $file) {
        //get Diaspora county ID
        $county = County::where('code', 'DI')->first();
        //get Diaspora city ID
        $city = City::where('name', 'Disapora')->first();
        $str = "";
        while(!$file->eof()) {
            $str .= $file->fgets();
        }
        $obj = json_decode($str);
        $data = [];
        foreach ($obj->markers as $marker) {
            $data[] = [
                'county_id' => $county->id,
                'city_id' =>  $city ? $city->id : 1,
                'siruta_code' =>  0,
                'circ_no' =>  $marker->country_id,
                'precinct_no' =>  $marker->n,
                'headquarter' =>  $marker->m,
                'address' =>  $marker->a
            ];
        }
        return $data;
    }

    private function importPrecinctsFromArray(array $data)
    {
        foreach ($data as $rawPrecinctData) {
            try{
                $existingPrecinct = Precinct::where([
                    'precinct_no' => $rawPrecinctData['precinct_no'],
                    'city_id' => $rawPrecinctData['city_id']
                ])->first();

                if ($existingPrecinct != null){
                    $existingPrecinct->update($rawPrecinctData);
                } else {
                    $precinct = new Precinct($rawPrecinctData);
                    $precinct->save();
                }
            } catch (QueryException $ex){
                Log::warning("Could not persist precinct \n $precinct");
            }

        }
    }

    private function getCityId(string $cityName, string $countyCode)
    {
        $city = City::join(
            'counties', 'counties.id', '=', 'cities.county_id'
        )
            ->where([
                ['cities.name', "=", $cityName],
                ["counties.code", "=", $countyCode]
            ])->first();
        if ($city) {
            return $city->id;
        }
        Log::warning("Could not find city with name $cityName and county with code $countyCode");
        return 0;
    }
}
