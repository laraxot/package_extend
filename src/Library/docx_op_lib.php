<?php



class DOCX_OP extends baseController
{
    public function fetchDOCX($params)
    {
        \extract($params);
        $tpl_file = docx2tpl($params);
        //ARRAY_OP::print_x($tpl_file);

        $path_parts = \pathinfo($tpl_file);

        $config['date'] = '%d/%m/%Y';
        $config['time'] = '%H:%M:%S';
        $smarty->assign('config', $config);
        $smarty->setTemplateDir($path_parts['dirname']);
        //------------------------------------------------------------------
        tpl2docx($params);
    }

    //------------//-----------------//-------------------

    public function docx2text($filename)
    {
        return readZippedXML($filename, 'word/document.xml');
    }

    //------------//-----------------//-------------------
    public function docx2tpl($params)
    {
        \extract($params);
        $info_in = \pathinfo($source);
        $tpl_file = './templates/'.$info_in['filename'].'_tpl.php';
        //return $tpl_file;
        $dest_mezzo = \str_replace('/IN/', '/MEZZO/', $source);
        $info_out = \pathinfo($dest_out);
        $final = 'out';
        switch ($final) {
        case 'out':
            FILE_OP::recursive_mkdir($info_out['dirname']);
            \copy($source, $dest_out);
            $content_tpl = readZippedXML($dest_out, 'word/document.xml');
            $content_tpl = \str_replace('>', '>'.\chr(13), $content_tpl);
            //$content_tpl = str_replace('{', '(', $content_tpl);
            //$content_tpl = str_replace('($', '{', $content_tpl);
            //$content_tpl = str_replace('}', ')', $content_tpl);
            $content_tpl = \str_replace('<'.'?xml version="1.0" encoding="UTF-8"?'.'>', '{literal}'.\chr(13).'<'.'?xml version="1.0" encoding="UTF-8"?'.'>', $content_tpl);
            $content_tpl = \str_replace('</office:font-face-decls>', '</office:font-face-decls>'.\chr(13).'{/literal}', $content_tpl);
            $content_tpl = \str_replace('relazione-&gt;', 'relazione->', $content_tpl);
            $content_tpl = \str_replace('_id_ditta-&gt;', '_id_ditta->', $content_tpl);
            $content_tpl = \str_replace('-&gt;', '->', $content_tpl);
            //$content_tpl = str_replace('','',$content_tpl);
            $content_tpl = sistema_chiamate_smarty($content_tpl);
            $content_tpl = smarty_block($content_tpl);

            FILE_OP::writeFile($tpl_file, $content_tpl);
            FILE_OP::writeFile('./templates/'.$info_in['filename'].'/content_tpl.php', $content_tpl);
            //-----------------
            $styles_tpl = readZippedXML($dest_out, 'word/styles.xml');
            $styles_tpl = sistema_chiamate_smarty($styles_tpl);
            FILE_OP::writeFile('./templates/'.$info_in['filename'].'/styles_tpl.php', $styles_tpl);

        break;
        case 'mezzo':
            \copy($source, $dest_mezzo);
            $content = \file_get_contents($tpl_file);
            $content = \str_replace('{literal}', '', $content);
            $content = \str_replace('{/literal}', '', $content);
            $content = \str_replace(\chr(13), '', $content);
            $content = \str_replace(\chr(10), '', $content);
            //echo $content;

            changeZippedXML($dest_mezzo, $content, 'content.xml');
        break;
    }

        return $tpl_file;
    }

    //------------//-----------------//-------------------

    //-----------------------------------------------------------------------
    public function tpl2docx($params)
    {
        \extract($params);
        try {
            //echo '<br>'.$path_parts['basename'];
        $path_parts_1 = \pathinfo($dest_out); //???
        //echo '<pre>';print_r($path_parts_1);echo '</pre>';

        //$content = $smarty->fetch($path_parts['basename']);
            $content = $smarty->fetch($path_parts_1['filename'].'/content_tpl.php');
            $style = $smarty->fetch($path_parts_1['filename'].'/styles_tpl.php');
            FILE_OP::writeFile('test.xml', $content, true);
            ///*
            $tidy_config = [
                     'clean' => true,
                     //'output-xhtml' => true,
                     'show-body-only' => true,
                     'wrap' => 0,
                     //ml' => true,
                    'output-xml' => true,
                    'input-xml' => true,
                     ];
            //*
            $tidy = \tidy_parse_string($content, $tidy_config, 'UTF8');
            $tidy->cleanRepair();
            $xml_out = $tidy;
            FILE_OP::writeFile('test_1.xml', $xml_out, true);
            //*/
            changeZippedXML($dest_out, $xml_out, 'word/document.xml');
            changeZippedXML($dest_out, $style, 'word/styles.xml');
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $msg = \str_replace('&amp;', '&
		', $msg);
            $msg = \str_replace('&lt;', '<', $msg);
            $msg = \str_replace('&gt;', '>', $msg);
            $msg = \str_replace('&quot;', '"', $msg);
            echo 'Caught exception: '.$msg."\n";
        }
        //echo $content;
        // echo $content_tpl;
        echo '<br style="clear:both" />'.$dest_out;
        FILE_OP::showDownloadLink($dest_out);
    }

    //------------
}//end class
