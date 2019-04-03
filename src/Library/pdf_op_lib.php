<?php



class PDF_OP extends baseController
{
    public function pdf_parse($text_from_file)
    {
        if (!\preg_match_all("/<<\s*\/V([^>]*)>>/x", $text_from_file, $out, PREG_SET_ORDER)) {
            return;
        }
        for ($i = 0; $i < \count($out); ++$i) {
            $pattern = "<<.*/V\s*(.*)\s*/T\s*(.*)\s*>>";
            $thing = $out[$i][2];
            if (eregi($pattern, $out[$i][0], $regs)) {
                $key = $regs[2];
                $val = $regs[1];
                $key = \preg_replace("/^\s*\(/", '', $key);
                $key = \preg_replace("/\)$/", '', $key);
                $key = \preg_replace("/\\\/", '', $key);
                $val = \preg_replace("/^\s*\(/", '', $val);
                $val = \preg_replace("/\)$/", '', $val);
                $matches[$key] = $val;
            }
        }

        return $matches;
    }

    /*
    *   createFDF
    *
    *   Takes values submitted via an HTML form and fills in the corresponding
    *   fields into an FDF file for use with a PDF file with form fields.
    *
    *   @param  $file   The pdf file that this form is meant for. Can be either
    *                   a url or a file path.
    *   @param  $info   The submitted values in key/value pairs. (eg. $_POST)
    *   @result Returns the FDF file contents for further processing.
    */
    public function createFDF($file, $info)
    {
        $data = "%FDF-1.2\n%âãÏÓ\n1 0 obj\n<< \n/FDF << /Fields [ ";
        foreach ($info as $field => $val) {
            if (\is_array($val)) {
                $data .= '<</T('.$field.')/V[';
                foreach ($val as $opt) {
                    $data .= '('.\trim($opt).')';
                }
                $data .= ']>>';
            } else {
                $data .= '<</T('.$field.')/V('.\trim($val).')>>';
            }
        }
        $data .= "] \n/F (".$file.') /ID [ <'.\md5(\time()).">\n] >>".
        " \n>> \nendobj\ntrailer\n".
        "<<\n/Root 1 0 R \n\n>>\n%%EOF\n";

        return $data;
    }

    /**
     * createXFDF.
     *
     * Tales values passed via associative array and generates XFDF file format
     * with that data for the pdf address sullpiled.
     *
     * @param string $file The pdf file - url or file path accepted
     * @param array  $info data to use in key/value pairs no more than 2 dimensions
     * @param string $enc  default UTF-8, match server output: default_charset in php.ini
     *
     * @return string The XFDF data for acrobat reader to use in the pdf form file
     */
    public function createXFDF($file, $info, $enc = 'UTF-8')
    {
        $data = '<?xml version="1.0" encoding="'.$enc.'"?>'."\n".
        '<xfdf xmlns="http://ns.adobe.com/xfdf/" xml:space="preserve">'."\n".
        '<fields>'."\n";
        foreach ($info as $field => $val) {
            $data .= '<field name="'.$field.'">'."\n";
            if (\is_array($val)) {
                foreach ($val as $opt) {
                    $data .= '<value>'.\htmlentities($opt).'</value>'."\n";
                }
            } else {
                $data .= '<value>'.\htmlentities($val).'</value>'."\n";
            }
            $data .= '</field>'."\n";
        }
        $data .= '</fields>'."\n".
        '<ids original="'.\md5($file).'" modified="'.\time().'" />'."\n".
        '<f href="'.$file.'" />'."\n".
        '</xfdf>'."\n";

        return $data;
    }

    // this function loads a data file created using pdftk dump_data_fields
    public function pdf_load_field_data($field_report_fn)
    {
        $ret_val = [];

        $fp = \fopen($field_report_fn, 'r');
        if ($fp) {
            $line = '';
            $rec = [];
            // $obj=new stdClass();
            while (false !== ($line = \fgets($fp, 2048))) {
                $line = \rtrim($line); // remove trailing whitespace
                if ('---' == $line) {
                    if (0 < \count($rec)) { // end of record
                        $ret_val[] = $rec;
                        $rec = [];
                    }
                    continue; // skip to next line
                }

                // split line into name and value
                $data_pos = \mb_strpos($line, ':');
                $name = \trim(\mb_substr($line, 0, $data_pos + 0)); // +1 include i :
                $value = \trim(\mb_substr($line, $data_pos + 2));

                if ('FieldStateOption' == $name) {
                    // pack state options into their own sub-array
                    if (!\array_key_exists('FieldStateOption', $rec)) {
                        $rec['FieldStateOption'] = [];
                    }
                    $rec['FieldStateOption'][] = $value;
                } else {
                    $rec[$name] = $value;
                }
            }
            if (0 < \count($rec)) { // pack final record
                $ret_val[] = $rec;
            }

            \fclose($fp);
        }

        return $ret_val;
    }

    ///-------------
    public function InvokePDFtk($pdf_tpl, $xfdf, $output)
    {
        $params = " $pdf_tpl fill_form $xfdf output $output flatten 2>&1";
        $pdftk_path = \exec('/usr/bin/which /usr/local/bin/pdftk');
        $have_pdftk = '/usr/local/bin/pdftk' == $pdftk_path;
        $pdftk_path = $have_pdftk ? $pdftk_path : 'pdftk ';
        \exec($pdftk_path.$params, $return_var);

        return  ['status' => $have_pdftk, 'command' => $pdftk_path.$params, 'output' => $return_var];
    }

    ///--------------------------------------
    public function pdf_field_data_to_obj($params)
    {
        \extract($params);
        $obj = new stdclass();
        \reset($arr);
        foreach ($arr as $k => $v) {
            $name = $v['FieldName'];
            $value = $v['FieldValue'];
            $obj->$name = $value;
        }

        return $obj;
    }

    //--------------
}//end class
