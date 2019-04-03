<?php
namespace XRA\Extend\Services;

use Cache;
//use Maatwebsite\Excel\Facades\Excel;
//use PHPExcel;
//use PhpOffice\PhpSpreadsheet\Spreadsheet;
//use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

use Carbon\Carbon;


class HtmlService{
	public static function toPdf($params)
    {
        require_once(__DIR__.'/vendor/autoload.php');
    	$pdforientation='L'; //default;
    	$out='show'; 
    	extract($params);
    	try {
	        $html2pdf = new Html2Pdf($pdforientation, 'A4', 'it');
			$html2pdf->setTestTdInOnePage(false);
			$html2pdf->WriteHTML($html);
			if ('content_PDF' == $out) {
	            return $html2pdf->Output($filename.'.pdf', 'S');
	        }
			return $html2pdf->Output();
		} catch (HTML2PDF_exception $e) {
            echo '<pre>';
            \print_r($e);
            echo '</pre>';
        }
    }
}