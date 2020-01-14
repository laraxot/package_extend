<?php



// se uso l'autoloader mi farebbe un po' schifo usare i namespace..
/*
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;


http://simplehtmldom.sourceforge.net/  !!!!

*/
class HTML_OP extends baseController
{
    public function remove_attributes($html)
    {
        return \preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i", '<$1$2>', $html);
    }

    public function html2pdf($filename, $content, $showLink = true)
    {
        //global $registry;
        $registry = $this->registry;
        $user = $registry->user;
        $download_dir = $registry->conf->settings['root']['conf']['xot']['download'];
        $bc_path = $registry->conf->settings['root']['conf']['xot']['bower_dir'];
        //$path = __SITE_DIR . '/admin/views/'.$registry->router->controller.'/PDF/';
        $path = FILE_OP::fixpath($download_dir.'/'.$registry->router->controller.'/PDF/');
        require_once $_SERVER['DOCUMENT_ROOT'].'/html2pdf/html2pdf.class.php';
        //require_once($bc_path.'/html2pdf/html2pdf.class.php');

        //$html2pdf = new HTML2PDF('P','A4', 'it', false, 'ISO-8859-15', 5);
        $html2pdf = new HTML2PDF('P', 'A4', 'it');
        //$html2pdf->setEncoding("ISO-8859-1");
        $html2pdf->pdf->SetDisplayMode('fullpage');
        //$content=str_replace('€','&euro;',$content);

        //echo '<pre>'.htmlspecialchars($content).'</pre>';

        $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
        //$html2pdf->Output('exemple04.pdf');
        //echo '<h3>cliccare sopra il PDF per poterlo aprire e stampare </h3>';
        $content_PDF = $html2pdf->Output('', true);
        $me = $registry->user->__get('me');
        $filename = $path.$filename.'-'.$me->handle.'-'.\date('Ymd').'-'.\rand(1, 100).'.pdf';
        $filename = \str_replace('\\', '/', $filename);
        $filename = \str_replace('=', '_', $filename);
        $filename = \str_replace(',', '', $filename);

        FILE_OP::writeFile($filename, $content_PDF);
        //echo '<br/>filename: '.$filename;
    //echo '<br/>Doc root :'.$_SERVER['DOCUMENT_ROOT'];
    $fileurl = FILE_OP::path2url($filename); //str_replace($_SERVER['DOCUMENT_ROOT'], '', $filename);
    if ($showLink) {
        //echo '<h3>'.$filename.'</h3>';
        FILE_OP::showDownloadLink($filename, 'cliccare sopra il PDF per poterlo aprire e stampare');
    }
        //echo '<br/>file url :'.$fileurl;
        //echo '<a href="/'.$fileurl.'" target="_blank">'.basename($filename).'</a>';
        //echo '&nbsp;&nbsp;size: '.round(filesize($filename) / 1024, 2).' KBytes<br/>';
        return $filename;
    }

    public function delta()
    {
        global $registry;
        $tmp = \debug_backtrace();
        $copy_dir = \str_replace(DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'mvc'.DIRECTORY_SEPARATOR.'includes', '', __DIR__);
        echo '<br/><b>LINE: </b>'.$tmp[0]['line'];
        //echo '<br/><b>FILE: </b>'.$tmp[0]['file'];
        $filepath = \str_replace($copy_dir, $_SERVER['DOCUMENT_ROOT'], $tmp[0]['file']);
        $filepath = \str_replace('/', DIRECTORY_SEPARATOR, $filepath);
        $filepath = \str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $filepath);
        echo '<br/><b>FILE: </b>'.$filepath;
        echo '<br/><b>FUNZIONE: </b>'.$tmp[1]['function '];
        $dim = \memory_get_usage() - $registry->memory_get_usage;
        echo '<br/><b>DIM:</b>'.\round($dim / 1024, 2).' Kb';
        echo '<br/><b>memory usage</b>:'.\round(\memory_get_usage() / 1024, 2).' Kb';
        $registry->memory_get_usage = \memory_get_usage();
        $time = DATE_OP::microtime_float() - $registry->time_delta;
        //echo '<br/><b>delta:</b>'.$registry->time_delta;
        echo '<br/><b>delta:</b>'.\round($time, 3);
        echo '<br/><b>time:</b>'.\round(DATE_OP::microtime_float() - $registry->time_start, 3);
        $registry->time_delta = DATE_OP::microtime_float();
        echo '<hr/>';
    }

    public function muori()
    {
        //echo '<pre>'; print_r(debug_backtrace()); echo '</pre>';
        $tmp = \debug_backtrace();

        $copy_dir = \str_replace(DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'mvc'.DIRECTORY_SEPARATOR.'includes', '', __DIR__);

        //echo '<pre>'.$copy_dir.'</pre>';
        //echo '<pre>'.__DIR__.'</pre>';
        //echo '<pre>';print_r($_SERVER);echo '</pre>';
        //DOCUMENT_ROOT

        echo '<br style="clear:both"/>';
        echo '<div style="border:1px solid red;float:left">';
        echo '<br/><b>LINE: </b>'.$tmp[0]['line'];
        //echo '<br/><b>FILE: </b>'.$tmp[0]['file'];
        $filepath = \str_replace($copy_dir, $_SERVER['DOCUMENT_ROOT'], $tmp[0]['file']);
        $filepath = \str_replace('/', DIRECTORY_SEPARATOR, $filepath);
        $filepath = \str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $filepath);
        echo '<br/><b>FILE: </b>'.$filepath;
        echo '<br/><b>FUNZIONE: </b>'.$tmp[1]['function '];

        global $registry;
        //$registry->time_start;
        echo '<br/><b>TIME: </b>'.(DATE_OP::microtime_float() - $registry->time_start);
        echo '</div>';
        die('');
    }

    //---------------------------------------------------------
    public function extractAttributesFromTag($attributes, $str)
    {
        //echo '<hr>'.htmlspecialchars($str);
        //$str=htmlspecialchars($str);
        $str = \str_replace("'", ' ', $str);
        $pat = '/('.\implode('|', $attributes).')=(["\'][^"\']*["\'])/i';
        \preg_match_all($pat, $str, $tmp);
        foreach ($tmp[2] as $k => $v) {
            $tmp[2][$k] = \mb_substr($v, 1, -1);
        }
        //echo '<pre>[TMP]';print_r($tmp); echo '[/TMP]</pre>';
        $ris = [];
        if (\count($tmp[1]) > 0) {
            $ris = \array_combine($tmp[1], $tmp[2]);
        }

        return $ris; //ARRAY_OP::array2Object($ris);
    }

    //------------------------------------------------------------
    //in html, url

    public function getImgs($params)
    {
        \extract($params);
        $info = \pathinfo($url);
        if ('/' != \mb_substr($params['url'], 0, -1)) {
            $params['url'] = $params['url'].'/';
        }

        $tmp = $params['url'];
        $pos = \mb_strpos($tmp, '/', 7);
        $info['host'] = \mb_substr($params['url'], 0, $pos);
        if ('' == $info['host']) {
            $info['host'] = $params['url'];
        }
        //ARRAY_OP::print_x($info);
        $gimg = \preg_match_all('/(<img[^>]+>)/i', $html, $matches);
        //ARRAY_OP::print_x($matches);
        $xml = '<root>';
        $out = '<table border="1">';
        foreach ($matches[1] as $k => $v) {
            $out .= '<tr><td>'.$k.'</td><td><pre>'.\htmlspecialchars($v).'</pre></td></tr>';
            $xml .= $v;
        }
        $xml .= '</root>';
        $out .= '</table>';
        $obj = XML_OP::xml_to_object($xml);
        //ARRAY_OP::print_x($obj->children);
        if (!isset($obj->children)) {
            return [];
        }
        $ris = $obj->children;
        \reset($ris);
        $str1 = 'http';
        $str2 = '//';
        $str3 = 'http://data:image/';
        //$str3='http://data:image/';
        $sk1 = 'data-original';
        foreach ($ris as $k => $v) {
            $src = '';
            if (isset($v->attributes->src)) {
                $src = $v->attributes->src;
            }
            $sk1 = 'data-original';
            if ('' == $src && isset($v->attributes->$sk1)) {
                $src = $v->attributes->$sk1;
                unset($v->attributes->$sk1);
            }

            if (\mb_substr($src, 0, \mb_strlen($str3)) == $str3 && isset($v->attributes->$sk1)) {
                $src = $v->attributes->$sk1;
                unset($v->attributes->$sk1);
            }
            $str = '//';
            if (\mb_substr($src, 0, \mb_strlen($str)) == $str) {
                $src = 'http:'.$src;
            }
            $str = '/';
            if (\mb_substr($src, 0, \mb_strlen($str)) == $str) {
                $src = $info['host'].$src;
            }

            $str = 'http';
            if (\mb_substr($src, 0, \mb_strlen($str)) != $str) {
                $src = $params['url'].$src;
            }

            $v->attributes->src = $src;
            $v->attributes->host = $info['host'];
            $v->attributes->url = $params['url'];
            //if(substr($v->attributes->src,0,strlen($str2))==$str2 ){
            //$v->attributes->src='http:'.$v->attributes->src;
        //}
        /*
        if(substr($v->attributes->src,0,strlen($str1))!=$str1 ){
            if(substr($v->attributes->src,0,1)!='/'){
                //ddd('qui');
                $src=FILE_OP::fixpath(strtolower($info['dirname']).'/'.$v->attributes->src);
                $src=str_replace(DIRECTORY_SEPARATOR,'/',$src);
                $src=str_replace(':/','://',$src);
                $v->attributes->src=$src;

            }else{

                //ARRAY_OP::print_x($pos);
                //ARRAY_OP::print_x($v->attributes->src);
                //ARRAY_OP::print_x($params['host']);
                $src=FILE_OP::fixpath(strtolower($info['host']).''.$v->attributes->src);
                $src=str_replace(DIRECTORY_SEPARATOR,'/',$src);
                $src=str_replace(':/','://',$src);

                $v->attributes->src=$src;
            }

            $src=$v->attributes->src;
            if(substr($src,0,strlen('//'))=='//' ){
                $src='http:'.$v->attributes->src;
            }
            if(substr($src,0,1)=='/' ){
                $src=$info['host'].$src;
            }




            $size=array();
            if(substr($src,0,strlen($str3))!=$str3 ){
            }else{
                ARRAY_OP::print_x($v);
            }
            //ARRAY_OP::print_x($size);
            if(isset($size[0])){
                $v->attributes->width=$size[0];
            }
            if(isset($size[1])){
                $v->attributes->height=$size[1];
            }
        }

        */
        }
        \reset($ris);

        return $ris;
    }

    //---
    public function getimagesize_proxy()
    {
        $size = [];
        $auth = \base64_encode('username:password');
        $option = [
                'http' => [
                    'method' => 'GET',
                    'proxy' => 'tcp://proxy04:8080', // proxy
                    'request_fulluri' => true,
                    'header' => ["Proxy-Authorization: Basic $auth", "Authorization: Basic $auth"],
                ],
                'https' => [
                    'method' => 'GET',
                    'proxy' => 'tcp://proxy04:8080', // proxy
                    'request_fulluri' => true,
                    'header' => ["Proxy-Authorization: Basic $auth", "Authorization: Basic $auth"],
                ],
            ];

        $ctx = \stream_context_create($option);
        //$content = file_get_contents($url,false,$ctx);

        $img_url = $src;
        $img_data_string = \file_get_contents($img_url, false, $ctx);
        $img_data = \imagecreatefromstring($img_data_string);

        //			$size=getimagesize($src);
        $height = \imagesx($img_data);
        $width = \imagesy($img_data);
        $size[0] = $width;
        $size[1] = $height;

        return $size;
    }

    //----------------------------------------------------------
    public function getSelectOptions($html)
    {
        \preg_match_all("/<select.*>(.*)<\/select>/isU", $html, $out);
        //echo '<pre>[OUTA]';print_r($out); echo '[/OUTA]</pre>';
        $attributes = ['id', 'name', 'title'];
        $res = [];
        //if ( preg_match_all("/<option.*>.+<\/option>/isU", $select, $all_options) ) {
        foreach ($out[0] as $k => $v) {
            $pat2 = '/<select.*([^\\s>"]*)/i';
            \preg_match_all($pat2, $v, $out1);
            //echo '<pre>[OUT]';print_r($out1); echo '[/OUT]</pre>';
            $obj = extractAttributesFromTag($attributes, $out1[0][0]);
            //echo '<pre>';print_r($obj); echo '</pre>';

            if (isset($obj->id)) {
                $res[$obj->id] = getOptions($out[1][$k]);
            }
            if (isset($obj->name)) {
                $res[$obj->name] = getOptions($out[1][$k]);
            }
        }

        return $res;
    }

    //-------------------------------------------------------------------
    public function getOptions($str)
    {
        \preg_match_all("/<option.*>.+<\/option>/isU", $str, $out);
        $res = [];
        //echo '<pre>[OUTA]';print_r($out); echo '[/OUTA]</pre>';
        $attributes = ['id', 'name', 'title', 'value'];
        foreach ($out[0] as $k => $v) {
            $obj = extractAttributesFromTag($attributes, $v);
            $res[] = $obj;
        }

        return $res;
    }

    //----------------------------------------------------
    public function getTableTd($html)
    {
        $firstTable = 'a';
        $tmp = $html;
        $ris = [];
        while (null != $firstTable) {
            $rist = [];
            $firstTable = getFirstTable($tmp);
            $tmp = \str_replace($firstTable, '', $tmp);
            $pat2 = '/<td.*>(.*)<\/td>/isU';
            \preg_match_all($pat2, $firstTable, $out);
            foreach ($out[1] as $k => $v) {
                $v = \strip_tags($v);
                $v = \str_replace('&nbsp;', ' ', $v);
                $v = \trim($v);
                //echo '<br/>'.$k.'  = '.$v;
                $rist[] = $v;
            }

            //echo '<pre>'; print_r($rist); echo '</pre>';
            for ($i = 0; $i < \count($rist); $i += 2) {
                $k2 = $rist[$i];
                if ('' != $k2) {
                    if (isset($rist[$i + 1])) {
                        //echo '<br/>'.$k2.'   => ';
                        $k3 = STRING_OP::slugify($k2);
                        if ('' == $k3) {
                            $k3 = STRING_OP::slugify(\utf8_encode($k2));
                        }
                        $k2 = \str_replace('-agrave', 'a', $k3);

                        $v2 = $rist[$i + 1];
                        if (!isset($ris[$k2])) {
                            $ris[$k2] = '';
                        }
                        $ris[$k2] .= $v2;
                    } else {
                        $ris['descrizione'] = $k2;
                        //echo '<pre>'; print_r($rist); echo '</pre>';
                    }
                }
            }
        }
        //echo '<pre>'; print_r($ris); echo '</pre>';
        return $ris;
    }

    //-----------------------------------------------------------
    public function getTableTr($html)
    {
        $pat2 = '/<tr.*>(.*)<\/tr>/isU';
        \preg_match_all($pat2, $html, $out);
        //echo '<pre>'; print_r($out[1]); echo '</pre>';
        $ris = [];
        foreach ($out[1] as $k => $v) {
            $pat3 = '/<td.*>(.*)<\/td>/isU';
            \preg_match_all($pat3, $v, $out3);
            $ris[$k] = $out3[1];
        }
        //echo '<pre>'; print_r($ris); echo '</pre>';
        return $ris;
    }

    //----------------------------------------------------------
    public function getFirstTable($html)
    {
        $tmp = $html;
        $str0 = '<table';
        $str1 = '</table';
        $pos1 = \mb_stripos($tmp, $str1);
        //echo '<br/>pos1: '.$pos1;
        if (false !== $pos1) {
            //echo '<hr/>';
            $tmp = \mb_substr($tmp, 0, $pos1 + \mb_strlen($str1) + 1);
            $pos0 = \mb_strripos($tmp, $str0);
            //echo '<br/>pos0: '.$pos0;
            $tmp = \mb_substr($tmp, $pos0);
            return $tmp;
        }

        return false;
    }

    //------------------------------------------------------------
    public function getAllTables($html)
    {
        $html1 = $html;
        $ris = [];
        $tmp = getFirstTable($html1);

        $ris[] = $tmp;
        while ($html1 != \str_replace($tmp, '', $html1)) {
            $html1 = \str_replace($tmp, '', $html1);
            $tmp = getFirstTable($html1);
            $ris[] = $tmp;
        }

        return $ris;
    }

    //-----------------------------------------------------------
    public function tagliaBordi($str_start, $str_end, $output)
    {
        $pos = \mb_strpos($output, $str_start);
        if (false === $pos) {
            return '';
        }
        $output = \mb_substr($output, $pos + \mb_strlen($str_start));
        $pos = \mb_strpos($output, $str_end);
        if (false === $pos) {
            return '';
        }
        $output = \mb_substr($output, 0, $pos);

        return $output;
    }

    //-----------------------------------------------------------

    //----------------------------------------------------------
    public function getTableHref($html)
    {
        //echo htmlspecialchars($str);
        $res = [];
        \preg_match_all("/<table.*>(.*)<\/table>/isU", $html, $out);
        //echo '<pre>[OUTA]';print_r($out); echo '[/OUTA]</pre>';
        $attributes = ['id'];
        foreach ($out[0] as $k => $v) {
            $pat2 = '/<table.*([^\\s>"]*)/i';
            \preg_match_all($pat2, $v, $out1);
            //echo '<pre>[OUT]';print_r($out1); echo '[/OUT]</pre>';
            $obj = extractAttributesFromTag($attributes, $out1[0][0]);
            if (isset($obj->id)) {
                $res[$obj->id] = getHref($out[1][$k]);
            } else {
                //$res[]= getHref($out[1][$k]);
            }
        }

        return $res;
    }
}//end class html_op
