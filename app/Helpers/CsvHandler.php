<?php

namespace App\Helpers;

use SplFileObject;

class CsvHandler
{
    protected $path = '';
    protected $delimiter = ',';

    public static function convertToArray($path)
    {
        if (!file_exists($path) || !is_readable($path)) {
            return FALSE;
        }


        $data = [];
        if (($handle = new SplFileObject($path, 'r')) !== FALSE) {
            $data = self::convertFileToArray($handle);
            $handle = null;
        }

        return $data;
    }

    public static function convertFileToArray(SplFileObject $file)
    {
        $data = [];
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        while (!$file->eof()) {
            $data[] = $file->fgetcsv();
        }

        return $data;
    }
}
