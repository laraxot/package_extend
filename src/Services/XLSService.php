<?php
namespace XRA\Extend\Services;

use Maatwebsite\Excel\Facades\Excel;
//use PHPExcel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
//use PhpOffice\PhpSpreadsheet\Writer\Csv;

//https://www.htmlgoodies.com/beyond/exploring-phpspreadsheets-formatting-capabilities.html
//esempio toArray
//https://artisansweb.net/read-csv-excel-file-php-using-phpspreadsheet/

//https://www.drupal.org/forum/support/module-development-and-code-questions/2018-08-17/using-phpofficephpspreadsheet
//$Excel_writer->save('php://output');


class XLSService{

	public static function out($params){


	}


	public static function save_phpoffice($params)
    {
        \extract($params);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $firstrow = collect($data)->first();
        $sheet->fromArray(\array_keys($firstrow), null, 'A1');
        $sheet->fromArray(
            $data,  	// The data to set
            null,        // Array values with this value will not be set
            'A2'         // Top left coordinate of the worksheet range where
                         //    we want to set these values (default is A1)
        );
        //$sheet->setCellValue('A1', 'Hello World !');
        $writer = new Xlsx($spreadsheet);
        $pathToFile = 'c:\\download\\xls\\'.$filename.'.xlsx';
        $writer->save($pathToFile); //$writer->save('php://output'); // per out diretto ?

	}


}