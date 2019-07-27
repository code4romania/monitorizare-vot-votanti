<?php

namespace App\Helpers;

use SplFileObject;

class CsvHandler
{
    public static function convertToArray($path, $associative = false)
    {
        if (!file_exists($path) || !is_readable($path)) {
            return FALSE;
        }


        $data = [];
        if (($handle = new SplFileObject($path, 'r')) !== FALSE) {
            $data = self::convertFileToArray($handle, $associative);
            $handle = null;
        }

        return $data;
    }

    public static function convertFileToArray(SplFileObject $file, $associative = false)
    {
        $data = [];
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        if (!$file->eof() && $associative) {
            $columns = $file->fgetcsv();
        }
        while (!$file->eof()) {
            $row = $file->fgetcsv();
            if ($associative) {
                $row = array_combine($columns, $row);
            }
            $data[] = $row;
        }

        return $data;
    }
}
