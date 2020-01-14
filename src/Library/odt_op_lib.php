<?php



class ODT_OP extends baseController
{
    public function fetchODT($params)
    {
        //$filename=$relazione_file->filename;
        //$parz['filename']=$filename;
        //$source = $this->path_odt_in.$filename;
        //$dest_out =$this->path_odt_out.$filename;
        \extract($params);

        $tpl_file = odt2tpl($params);

        $path_parts = \pathinfo($tpl_file);

        $config['date'] = '%d/%m/%Y';
        $config['time'] = '%H:%M:%S';
        $smarty->assign('config', $config);

        $smarty->setTemplateDir($path_parts['dirname']);
        //------------------------------------------------------------------

        tpl2odt($params);
    }

    //-----------------------------------------------------------------------

    public function odt2text($filename)
    {
        return readZippedXML($filename, 'content.xml');
    }

    //-----------------------------------------------------------------------
    public function tpl2odt($params)
    {
        \extract($params);
        try {
            //echo '<br>'.$path_parts['basename'];
        $path_parts_1 = \pathinfo($source); //???
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
            changeZippedXML($dest_out, $xml_out, 'content.xml');
            changeZippedXML($dest_out, $style, 'styles.xml');
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

    //------------//-----------------//-------------------
    public function odt2tpl($params)
    {
        \extract($params);
        $path_parts = \pathinfo($source);
        //echo '<pre>'; print_r($path_parts); echo '</pre>';
        $tpl_file = './templates/'.$path_parts['filename'].'_tpl.php';
        //return $tpl_file;
        $dest_mezzo = \str_replace('/IN/', '/MEZZO/', $source);
        //$dest_out = str_replace('/IN/', '/OUT/', $source);
        //$final = 'mezzo';
        $info_out = \pathinfo($dest_out);

        $final = 'out';
        switch ($final) {
        case 'out':
            FILE_OP::recursive_mkdir($info_out['dirname']);
            \copy($source, $dest_out);
            $content_tpl = odt2text($dest_out);
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
            FILE_OP::writeFile('./templates/'.$path_parts['filename'].'/content_tpl.php', $content_tpl);
            //-----------------
            $styles_tpl = readZippedXML($dest_out, 'styles.xml');
            $styles_tpl = sistema_chiamate_smarty($styles_tpl);
            FILE_OP::writeFile('./templates/'.$path_parts['filename'].'/styles_tpl.php', $styles_tpl);

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
//------------
}//end class
