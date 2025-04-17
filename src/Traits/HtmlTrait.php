<?php



namespace XRA\Extend\Traits;

//------ PDF ------
use Spipu\Html2Pdf\Html2Pdf;

trait HtmlTrait
{
    // same of substr but with string..
    public static function substr($str, $str_start, $str_end)
    {
        $pos_start = \mb_strpos($str, $str_start);
        $pos_end = \mb_strpos($str, $str_end, $pos_start);
        $length = $pos_end - $pos_start;

        return \mb_substr($str, $pos_start, $length);
    }

    public static function toPdf($html, $orientation = 'L')
    {
        try {
            $html2pdf = new Html2Pdf($orientation, 'A4', 'it');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            //$html2pdf->writeHTML($html->__toString());
            $html2pdf->writeHTML((string) $html);
            $html2pdf->Output();
        } catch (HTML2PDF_exception $e) {
            echo '<pre>';
            \print_r($e);
            echo '</pre>';
        }
    }

    //end toPdf
}//end HtmlTrait
