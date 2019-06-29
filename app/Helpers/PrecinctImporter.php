<?php

namespace App\Helpers;
use Akeneo\Component\SpreadsheetParser\SpreadsheetParser;
use App\City;
use App\Precinct;
use Illuminate\Support\Facades\File;

class PrecinctImporter
{

    public function importFromFile(\Symfony\Component\HttpFoundation\File\File $file, bool $deleteFileAfter)
    {
        $fileExtension = $file->getExtension();
        $isCsv = $fileExtension == "csv";
        $isXlsx = $fileExtension == "xls" || $fileExtension == "xlsx";
        $data = array();

        if ($isCsv){
            $data = $this->readFromCSV($file);
        } else if ($isXlsx){
            $data = $this->readFromXLSX($file);
        }

        $this->importPrecinctsFromArray($data);
        if ($deleteFileAfter) {
            File::delete($file);
        }
    }

    private function readFromCSV(\Symfony\Component\HttpFoundation\File\File $file)
    {

        $data = CsvHandler::convertToArray($file->getPath().'/'.$file->getFilename());
        $precinctData = array();
        foreach ($data as $rowIndex => $rawPrecinctData){
            if ($rowIndex > 0){
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

    private function readFromXLSX($file)
    {
        $workbook = SpreadsheetParser::open($file);
        $cityId = null;

        $precinctId = -1;
        $data = array();
        foreach ($workbook->createRowIterator(0) as $rowIndex => $rowData) {

            if ($rowIndex > 2 && $rowData[0] != '') {

                if (trim($rowData[1]) != '') {
                    $cityId = $this->getCityId($rowData[1]);
                }
                if($rowData[4] != $precinctId) {
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

    private function extractData(){


        return null;
    }

    private function importPrecinctsFromArray($data)
    {
        foreach ($data as $rawPrecinctData) {
            $precinct = new Precinct($rawPrecinctData);
            $precinct->save();
        }
    }
    private function getCityId($cityName)
    {
        $city = City::where('name', $cityName)->first();
        if ($city) {
            return $city->id;
        }

        echo "City not found:" . $cityName . "\r\n";
        return 0;
    }
}
