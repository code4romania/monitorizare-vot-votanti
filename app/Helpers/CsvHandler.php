<?php

namespace App\Helpers;

class CsvHandler
{
	protected $path = '';
	protected $delimiter = ',';

	public static function convertToArray($path)
	{
		if(!file_exists($path) || !is_readable($path)) {
			return FALSE;
		}


        $data = array();
		if (($handle = fopen($path, 'r')) !== FALSE)
		{
            $data = self::convertFileToArray($handle);
			fclose($handle);
		}

		return $data;
	}

	public static function convertFileToArray($file){
        $data = array();
	    while (($row = fgetcsv($file, 1000, ',')) !== FALSE)
        {
            $data[] = $row;
        }
        return $data;
    }
}
