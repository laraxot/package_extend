<?php


class XLS_OP extends baseController
{
    //http://www.devarticles.com/c/a/Web-Graphic-Design/Using-HTML-Quickform-for-Form-Processing/10/
    public function importXLSForm($params)
    {
        global $registry;
        $form = new HTML_QuickForm('indennitaForm', 'POST', $_SERVER['REQUEST_URI']);
        $file0 = &$form->addElement('file', 'filename', 'file xls:');
        $maxfilesize = 51 * 1024 * 1024;
        \ini_set('upload_max_filesize', '50M');
        \ini_set('memory_limit', '256M');
        echo '<pre>upload_max_filesize:'.\ini_get('upload_max_filesize');
        echo '<br/>post_max_size: '.\ini_get('post_max_size');
        echo '<br/>memory_limit: '.\ini_get('memory_limit');
        echo '</pre>';
        $form->setMaxFileSize($maxfilesize);
        // this is the problematic rule
        $form->addRule('filename', 'You need to select a file to upload', 'uploadedfile');
        $form->addRule('filename', 'The file is too big', 'maxfilesize', $maxfilesize);
        //$form->addRule('filename', 'The file is larger than 5MB', 'maxfilesize', '5120000', null, 'client');
        //$form->addRule('filename', 'Needs to be a jpg', 'mimetype', 'image/jpeg');
        //$form->addRule("filedata", "Please upload only images","mimetype", array("image/gif", "image/jpeg", "image/pjpeg"));
        $form->addRule('filename', 'You must select a XLS file to import.', 'filename', '|.*\.xls|');
        $form->addElement('submit', null, 'import XLS!');

        if ($form->validate()) {
            $vals = $form->exportValues();
            ARRAY_OP::print_x($vals);
            $file = &$form->getElement('filename');
            //echo '<pre>';print_r($file); echo '</pre>';
            $path_parts = \pathinfo($file->_value['name']);
            //echo '<pre>';print_r($path_parts); echo '</pre>';
            if (!isset($path_parts['extension']) || 'xls' != \mb_strtolower($path_parts['extension'])) {
                $registry->template->showMsg('selezionare un file .xls', 'error');

                return;
            }
            $parz = ['filename' => $file->_value['tmp_name']];
            $data = importXLS($parz);
            /*
            $parz1=array('obj'=>$registry->router->controller,
                'act'=>'do_'.$registry->router->__get('action'),
                'nome'=>'do '.$registry->template->nome,
                'id_padre'=>$registry->template->id,);
            //echo '<pre>';print_r($parz1); echo '</pre>';

            $registry->router->esegui($parz1['obj'],$parz1['act'],array('data'=>$data));
            */
            //$form->process(array($this,'do_stampaIndennita'));
            //$this->do_importXLS(array('data'=>$data));
            return $data;
        }
        $form->display();
    }

    public function XLSDataToObj($params)
    {
        //*
        \extract($params);
        $data1 = [];
        $nsheets = \count($data->sheets);
        for ($k = 0; $k < $nsheets; ++$k) {
            $keys = [];
            for ($j = 1; $j <= $data->sheets[$k]['numCols']; ++$j) {
                $tmp = $data->sheets[$k]['cells'][1][$j];
                $tmp = \str_replace('_', '-', $tmp);
                $tmp = STRING_OP::gen_slug($tmp);
                $tmp = \str_replace('-', '_', $tmp);
                $keys[] = $tmp;
            }
            for ($i = 2; $i <= $data->sheets[$k]['numRows']; ++$i) { // la 1 e' l'intestazione
                $obj = new stdclass();
                //for ($j = 1; $j <= $data->sheets[$k]['numCols']; $j++) {
                $values = \array_values($data->sheets[$k]['cells'][$i]);
                if (\count($keys) != \count($values)) {
                    //echo '<hr/>';
                    //echo '<pre>';print_r($keys);echo '</pre>';
                    //echo '<pre>';print_r($values);echo '</pre>';
                    $min = \min(\count($keys), \count($values));
                    $max = \max(\count($keys), \count($values));
                    if ($max - \count($keys) > 0) {
                        $keys = \array_merge($keys, array_STRING_OP::fill(\count($keys), $max - \count($keys), '---'));
                    }
                    if ($max - \count($values) > 0) {
                        $values = \array_merge($values, array_STRING_OP::fill(\count($values), $max - \count($values), '---'));
                    }
                    //echo '<pre>';print_r($keys);echo '</pre>';
                    //echo '<pre>';print_r($values);echo '</pre>';
                    //echo '<h3>'.$min.'</h3>';
                    $tmp = \array_combine($keys, $values);
                //echo '<pre>';print_r($tmp);echo '</pre>';
                //ddd('qui');
                } else {
                    $tmp = \array_combine($keys, $values);
                }
                $foglio_name = $data->boundsheets[$k]['name'];
                $data1[$foglio_name][$i] = ARRAY_OP::toObject($tmp);
                //}
            }
            //echo '<pre>';print_r($head);echo '</pre>';
        }
        //*/
        return $data1;
    }

    public function importXLS2($params)
    {
        require_once 'Excel/excel_reader2.php';
        \extract($params);
        $data = new Spreadsheet_Excel_Reader($params['filename']);

        return $data;
    }

    public function importXLS($params)
    {  //problema con le date vediamo un po'..
        require_once 'Excel/reader.php';
        // ExcelFile($filename, $encoding);
        \extract($params);
        $data = new Spreadsheet_Excel_Reader();
        // Set output Encoding.
        $data->setOutputEncoding('CP1251');

        $data->read($params['filename']);
        if (isset($show) && 1 == $show) {
            showXLS(['data' => $data]);
        }

        //echo '<pre>';print_r($data1);echo '</pre>';

        return $data;
    }

    public function showXLS($params)
    {
        \extract($params);
        $nsheets = \count($data->sheets);
        echo '<h3>fogli n.'.\count($data->sheets);
        echo '</h3>';
        //boundsheets
        //echo '<pre>';print_r($data); echo '</pre>';

        for ($k = 0; $k < $nsheets; ++$k) {
            echo '<h3>'.$data->boundsheets[$k]['name'].'</h3>';
            echo '<table border="1">';
            for ($i = 1; $i <= $data->sheets[$k]['numRows']; ++$i) {
                echo '<tr>';
                for ($j = 1; $j <= $data->sheets[$k]['numCols']; ++$j) {
                    if (isset($data->sheets[$k]['cells'][$i][$j])) {
                        echo '<td>'.$data->sheets[$k]['cells'][$i][$j].'</td>';
                    } else {
                        echo '<td></td>';
                    }
                }
                echo '</tr>';
            }
            echo '</table>';
        }
    }

    //------------
}//end class
