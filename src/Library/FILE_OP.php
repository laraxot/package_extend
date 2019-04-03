<?php



namespace XRA\Extend\Library;

class FILE_OP
{
    public static function pathCopy2http($params)
    {
        \extract($params);
        $copy_dir = \str_replace(\DIRECTORY_SEPARATOR.'framework'.\DIRECTORY_SEPARATOR.'mvc'.\DIRECTORY_SEPARATOR.'includes', '', __DIR__);
        $filepath = \str_replace($copy_dir, $_SERVER['DOCUMENT_ROOT'], $copypath);
        $filepath = \str_replace('/', \DIRECTORY_SEPARATOR, $filepath);
        $filepath = \str_replace(\DIRECTORY_SEPARATOR.\DIRECTORY_SEPARATOR, \DIRECTORY_SEPARATOR, $filepath);

        return $filepath;
    }

    public static function getContentFile($url)
    {
        $contents = '';
        if (!($fp = @\fopen($url, 'r'))) {
            echo '<h3>Non riesco ad aprire ['.$url.']</h3>';

            return [];
        }
        while (!\feof($fp)) {
            $contents .= \fread($fp, 8192);
        }
        \fclose($fp);

        return $contents;
    }

    public static function mime_content_type($filename)
    {
        $mime_types = [
            'txt' => 'text/plain', 'htm' => 'text/html', 'html' => 'text/html', 'php' => 'text/html', 'css' => 'text/css', 'js' => 'application/javascript', 'json' => 'application/json', 'xml' => 'application/xml', 'swf' => 'application/x-shockwave-flash', 'flv' => 'video/x-flv',

            // images
            'png' => 'image/png', 'jpe' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'bmp' => 'image/bmp', 'ico' => 'image/vnd.microsoft.icon', 'tiff' => 'image/tiff', 'tif' => 'image/tiff', 'svg' => 'image/svg+xml', 'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip', 'rar' => 'application/x-rar-compressed', 'exe' => 'application/x-msdownload', 'msi' => 'application/x-msdownload', 'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg', 'qt' => 'video/quicktime', 'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf', 'psd' => 'image/vnd.adobe.photoshop', 'ai' => 'application/postscript', 'eps' => 'application/postscript', 'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword', 'rtf' => 'application/rtf', 'xls' => 'application/vnd.ms-excel', 'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text', 'ods' => 'application/vnd.oasis.opendocument.spreadsheet', ];
        $info = \pathinfo($filename);
        $ext = $info['extension'];
        //$ext = strtolower(array_pop(explode('.',$filename)));
        if (\array_key_exists($ext, $mime_types)) {
            return [$ext, $mime_types[$ext]];
        } elseif (\function_exists('finfo_open')) {
            $finfo = \finfo_open(FILEINFO_MIME);
            $mimetype = \finfo_file($finfo, $filename);
            \finfo_close($finfo);

            return [$ext, $mimetype];
        } else {
            return [$ext, 'application/octet-stream'];
        }
    }

    public function path2url($path)
    {
        $doc_root = $_SERVER['DOCUMENT_ROOT'];
        $copy_dir = \str_replace(\DIRECTORY_SEPARATOR.'framework'.\DIRECTORY_SEPARATOR.'mvc'.\DIRECTORY_SEPARATOR.'includes', '', __DIR__);
        //echo '<br/>document_root '.$doc_root;
        //echo '<br/>path          '.$path;
        //echo '<br>clud           '.$copy_dir;

        $doc_root = \str_replace('\\', '/', $doc_root);
        if ('/' != \mb_substr($doc_root, -1)) {
            $doc_root .= '/';
        }

        $path = \str_replace('\\', '/', $path);
        if ('/' != \mb_substr($path, -1)) {
            $path .= '/';
        }

        $copy_dir = \str_replace('\\', '/', $copy_dir);
        if ('/' != \mb_substr($copy_dir, -1)) {
            $copy_dir .= '/';
        }
        //$url = str_replace($_SERVER["DOCUMENT_ROOT"], 'http://'.$_SERVER["HTTP_HOST"].'/', $path);
        $url = $path;
        $url = \str_ireplace($doc_root, '/', $url);
        $url = \str_ireplace($copy_dir, '/', $url);

        $url = \mb_substr($url, 0, -1);
        //Array_op::print_x($doc_root.'    '.$path.'   '.$url);
        return $url;
    }

    //-------------------------------------------------

    /**
     * Byte Convert
     * Convert file size into byte, kb, mb, gb and tb.
     *
     * @param int $size
     *
     * @return string
     */
    public function byte_convert($size)
    {
        // size smaller then 1kb
        if ($size < 1024) {
            return $size.' Byte';
        }
        // size smaller then 1mb
        if ($size < 1048576) {
            return \sprintf('%4.2f KB', $size / 1024);
        }
        // size smaller then 1gb
        if ($size < 1073741824) {
            return \sprintf('%4.2f MB', $size / 1048576);
        }
        // size smaller then 1tb
        if ($size < 1099511627776) {
            return \sprintf('%4.2f GB', $size / 1073741824);
        } // size larger then 1tb
        else {
            return \sprintf('%4.2f TB', $size / 1073741824);
        }
    }

    //-----------------------------------------

    public function exeSQLFile($params)
    {
        $mysqli = new mysqli($params['hostspec'], $params['username'], $params['password'], $params['database']);

        /* check connection */
        if (\mysqli_connect_errno()) {
            \printf("Connect failed: %s\n", \mysqli_connect_error());
            exit();
        }
        $query = \file_get_contents($params['filename']);

        /* execute multi query */
        if ($mysqli->multi_query($query)) {
            do {
                /* store first result set */
                if ($result = $mysqli->store_result()) {
                    while ($row = $result->fetch_row()) {
                        \printf("%s\n", $row[0]);
                    }
                    $result->free();
                }
                /* print divider */
                if ($mysqli->more_results()) {
                    \printf("-----------------\n");
                }
            } while ($mysqli->next_result());
        }

        /* close connection */
        $mysqli->close();
    }

    public function zipFiles($params)
    {
        global $registry;
        include 'Archive/Zip.php';        // imports
        $filename = 'zip/creaPDFmail-.zip';
        $p_params = ['remove_all_path' => true];
        \extract($params);
        if (0 == \count($files_to_zip)) {
            $registry->template->showMsg('non ci son file da comprimere', 'error');

            return;
        }

        $download_dir = $registry->conf->settings['root']['conf']['xot']['download'];
        $datepub = \date('Ymd');
        //if(dirname($filename)=='' || dirname($filename)=='.' || true ){  // da sistemare
        $filename = $download_dir.'/zip/'.$filename;
        //}else {
        //echo '<br/>dirname =['.dirname($filename).']';
        //}
        $filename = \str_replace('//', '/', $filename);
        self::recursive_mkdir(\dirname($filename));

        $obj = new Archive_Zip($filename); // name of zip file
        //$p_params=array('remove_all_path'=>true);
        if ($obj->create($files_to_zip, $p_params)) {
            //echo 'Created successfully!';
            self::showDownloadLink($filename);
        } else {
            echo 'Error in file creation '.$filename;
            echo '<pre>';
            \print_r($files_to_zip);
            echo '</pre>';
        }
    }

    public static function recursive_mkdir($path, $mode = 0777)
    {
        $path = \str_replace('/', \DIRECTORY_SEPARATOR, $path);
        $dirs = \explode(\DIRECTORY_SEPARATOR, $path);
        $count = \count($dirs);
        $path = '';
        for ($i = 0; $i < $count; ++$i) {
            $path .= $dirs[$i].\DIRECTORY_SEPARATOR;
            //echo '<br/>path['.$path.']';
            if (!\is_dir($path) && !\mkdir($path, $mode)) {
                return false;
            }
        }

        return true;
    }

    //----------------------------------------------------------

    public static function showDownloadLink($filename, $caption = '')
    {
        global $registry;
        $html = '';
        $filename = self::fixpath($filename);
        $path_parts = \pathinfo($filename);
        $download_dir = '';
        if (\is_object($registry) && isset($registry->conf->settings)) {
            $download_dir = $registry->conf->settings['root']['conf']['xot']['download'];
        }
        $href = \str_replace($_SERVER['DOCUMENT_ROOT'], 'http://'.$_SERVER['HTTP_HOST'].'/', $filename);
        //$href = $_SERVER['REQUEST_URI'] . '&rt=file/dwn/' . str_replace($download_dir, '', $filename);;
        $href = $_SERVER['REQUEST_URI'].'?downloadfile='.\str_replace($download_dir, '', $filename);

        echo '<div style="border:1px solid darkgray;float:left;margin:3px;padding:3px;">';
        echo '<a href="'.$href.'" target="_blank;">';
        switch ($path_parts['extension']) {
            case 'xls':
                echo '<i class="fa fa-file-excel-o fa-2x"></i>';
                break;
            case 'pdf':
                echo '<i class="fa fa-file-pdf-o fa-2x"></i>';
                break;
            case 'doc':
                echo '<i class="fa fa-file-word-o fa-2x"></i>';
                break;
            case 'docx':
                echo '<i class="fa fa-file-word-o fa-2x"></i>';
                break;
            case 'html':
                echo '<i class="fa fa-file-text-o fa-2x"></i>';
                break;
            case 'ppt':
                echo '<i class="fa fa-file-powerpoint-o fa-2x"></i>';
                break;
            case 'zip':
                echo '<i class="fa fa-file-archive-o fa-2x"></i>';
                break;
            default:
                echo '<i class="fa fa-file-text-o fa-2x"></i>';
                break;
            //default: echo '<img src="'.$img.'" width="48px" style="float:left;">'; break;
        }

        echo '</a>';
        echo $caption;
        if ('' != \trim($caption)) {
            echo '<br/>';
        }
        echo '&nbsp;<a href="'.$href.'" target="_blank;">'.$path_parts['basename'].'</a>';
        //echo '<br/>['.$filename.']';
        echo '<br/>'.\round(\filesize($filename) / 1024, 2).' Kbytes';

        echo '</div>';
    }

    //-------------------------------------------------

    public static function fixpath($str)
    {
        return self::sistemaPath(['str' => $str]);
    }

    //----------------------------------------------------

    public static function sistemaPath($params)
    {
        \extract($params);
        $str = \str_replace('\\', '/', $str);
        $str = \str_replace('//', '/', $str);
        $str = \str_replace('//', '/', $str);
        $str = \str_replace('/', \DIRECTORY_SEPARATOR, $str);

        return $str;
    }

    //-------------------------------------------------------

    public function editFile($params)
    {
        \extract($params);
        if (\file_exists($filename)) {
            $content = \file_get_contents($filename);
        } else {
            $content = '';
        }
        $form = new HTML_QuickForm('editFile', 'post', $_SERVER['REQUEST_URI']);
        $form->setDefaults(['txt' => $content]);
        $form->addElement('header', null, $filename);

        //$form->addElement('textarea','txt','txt:');
        $form->addElement('jquery_tinymce', 'txt', 'txt:');
        //$form->addElement('tinymce','txt1','txt:');
        $form->addElement('submit', null, 'Modifica');
        $form->display();

        if ($form->validate()) {
            //echo '<pre>';print_r(); echo '</pre>';
            $vals = $form->exportValues();
            self::writeFile($filename, $vals['txt']);
            global $registry;
            $registry->template->showMsg('Modificato', 'success');
        }
        //echo $content;
    }

    //-------------------------------------------------------------------------------------

    public static function writeFile($filename, $somecontent, $show = false)
    {
        $bom = \chr(239).\chr(187).\chr(191);
        $somecontent = \str_replace($bom, '', $somecontent);
        $datepub = \date('Ymd');
        //echo '-----------'.dirname($filename);

        if ('' == \dirname($filename) || '.' == \dirname($filename)) {
            global $registry;
            $download_dir = $registry->conf->settings['root']['conf']['xot']['download'];
            $filename = $download_dir.'/'.$filename;
        } else {
            //echo '<br/>dirname =['.dirname($filename).']';
        }
        //echo '<br> FILENAME: '.$filename;
        //echo '<br> dirname: '.dirname($filename);
        //ddd('qui');
        $filename = \str_replace('\\', '/', $filename);
        $filename = \str_replace('//', '/', $filename);

        //echo '<h3>'.__LINE__.']'.$filename.'</h3>';
        $dir_tmp = \str_replace('\\', '/', $filename);
        $dir_tmp = \str_replace('//', '/', $filename);
        $dir_array = \explode('/', $dir_tmp);
        $dir_tmp1 = '';
        /*
        for ($i = 0; $i < count($dir_array) - 1; $i++) {
            $dir_tmp1 .= $dir_array[$i] . "/";
            if (!is_dir($dir_tmp1)) {
                mkdir($dir_tmp1);
            }
        }
        */
        $filename = \trim(\str_replace('/', \DIRECTORY_SEPARATOR, $filename));
        //sleep(10);
        if (!$handle = \fopen($filename, 'w')) {
            Array_op::print_x("<br>Cannot open file ($filename)");

            return;
        }
        // Write $somecontent to our opened file.
        if (false === \fwrite($handle, $somecontent)) {
            echo "<br>Cannot write to file ($filename)";
        }
        if ($show) {
            self::showDownloadLink($filename);
        }
        \fclose($handle);

        return $filename;
    }

    //------------------------------------------------------

    public function forceDownload($params)
    {
        //Array_op::print_x($params);ddd('qui');
        //Storing the previous encoding in case you have some other piece
        /*
      //of code sensitive to encoding and counting on the default value.
      $previous_encoding = mb_internal_encoding();
      //Set the encoding to UTF-8, so when reading files it ignores the BOM
      mb_internal_encoding('UTF-8');
      //Process the CSS files...
      //Finally, return to the previous encoding
      mb_internal_encoding($previous_encoding);
      */
        \extract($params);
        $path_parts = \pathinfo($file);
        $ext = \mb_strtolower($path_parts['extension']);
        \header('Pragma: no-cache');
        \header('Expires: 0');

        \ob_start();
        \readfile($file);
        $content = \ob_get_contents();
        \ob_end_clean();
        $content = \str_replace(\chr(239).\chr(187).\chr(191), '', $content);

        switch ($ext) {
            case 'odt':

                \header('Content-type: application/vnd.oasis.opendocument.text');
                \header('Content-Disposition: attachment; filename="'.\basename($file).'";');
                echo $content;
                break;
            case 'xls':
                //ob_start();
                /*
                header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
                header("Content-Disposition: inline; filename=\"" . basename($file) . ".xls\"");
                header("Pragma: no-cache");
                header("Expires: 0");
                readfile($file);
                */
                //$file = 'excelFile-'.date("Y-M-D")."-".time().'.xls';
                // $str = file_get_contents($file);
                \ob_start();
                /*
                echo '<table border="1"> ';
                echo '<tr><th>Product Name </th><th>Category</th><th>Active</th></tr>';
                for ($i=0; $i<=10; $i++) {
                 echo "<tr><td>Product$i</td><td align='center'>Category $i</td><td>Active</td></tr>";
                }
                echo '</table>';
                //*///
                //echo $str;
                //readfile($file);
                //echo 'oggi è una bella giornata òèéòàù';
                //$content = ob_get_contents();
                //ob_end_clean();
                //$content = str_replace(chr(239) . chr(187) . chr(191), '', $content);

                \header('Expires: 0');
                \header('Last-Modified: '.\gmdate('D, d M Y H:i:s').' GMT');
                \header('Cache-Control: no-store, no-cache, must-revalidate');
                \header('Cache-Control: post-check=0, pre-check=0', false);
                \header('Pragma: no-cache');
                \header('Content-Type:   application/vnd.ms-excel; charset=utf-8');
                \header('Content-type:   application/x-msexcel; charset=utf-8');
                \header('Content-disposition: attachment; filename='.\basename($file));
                \header('Expires: 0');
                \header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                \header('Cache-Control: private', false);
                echo $content;

                /*
                $str = $content;
                if (mb_detect_encoding($str ) == 'UTF-8') {
                  $str = mb_convert_encoding($str , "HTML-ENTITIES", "UTF-8");
               }
               header('Content-type: application/x-msdownload; charset=utf-8');
               header('Content-Disposition: attachment; filename=companies.xls');
               header('Pragma: no-cache');
               header('Expires: 0');
               echo $str ;
                */

                //$content=mb_convert_encoding($content, 'UTF-8');
                /*
                header("Expires: 0");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                header("Cache-Control: no-store, no-cache, must-revalidate");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
                header("Content-type: application/vnd.ms-excel;charset:UTF-8");
                header('Content-length: '.strlen($content));
                header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
                header("Content-type:   application/x-msexcel; charset=utf-8");
                header('Content-disposition: attachment; filename='.basename($file));
                //$content = str_replace(chr(239) . chr(187) . chr(191), '', $content);
                echo $content;
                //exit;
                           */

                /*
                $str = file_get_contents($file);
                header('Pragma: public');
    header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
    header("Pragma: no-cache");
    header("Expires: 0");
    header('Content-Transfer-Encoding: none');
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8'); // This should work for IE & Opera
    header("Content-type: application/x-msexcel; charset=UTF-8"); // This should work for the rest
    header('Content-Disposition: attachment; filename=comentarios-consulta.xls');
        //echo utf8_decode($str);
        echo $str;
        //*/
                /*
                header('Content-Description: File Transfer');
            //header('Content-Type: application/octet-stream');
            header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            */

                //echo $str;
                //echo mb_detect_encoding($str );
                /*
                header('Content-type: application/x-msdownload; charset=utf-16');
                header("Content-type: application/x-msexcel; charset=utf-8");
                header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
                header("Content-Disposition: inline; filename=\"" . basename($file) . "");
                *//*
            header("Content-Type:   application/vnd.ms-excel; charset=utf-8");

            header("Content-Disposition: attachment; filename=Test.xls");
            header("Expires: 0");
            //header('Content-length: '.strlen($str));
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false);
            //ob_end_flush();
            exit;
            */
                /*
                if (mb_detect_encoding($str ) == 'UTF-8') {
                    //$str = mb_convert_encoding($str , "HTML-ENTITIES", "UTF-8");
                }

                //header('Content-type: application/x-msdownload; charset=utf-16');
                header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
                header("Content-type: application/x-msexcel; charset=utf-8");
                header("Content-Disposition: attachment; filename=\"" . basename($file) . "\";");
                //header('Pragma: no-cache');
                header('Expires: 0');
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private", false);
                */
                //echo $str ;

                //echo  mb_convert_encoding($str , "HTML-ENTITIES", "UTF-8");

                /*
                header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
                //header("Content-Type: application/vnd.ms-excel; charset=iso-8859-1");
                header("Content-Disposition: inline; filename=\"" . basename($file) . "");
                readfile($file);
                */
                //return;
                break;
            case 'pdf':
                \header('Content-type: application/pdf');
                \header('Content-Disposition: attachment; filename="'.\basename($file).'";');
                \readfile($file);

                exit;
                break;
            case 'zip':
                //header("Content-Type: application/zip");
                \header('Content-Type: application/octet-stream');
                \header('Content-Disposition: attachment; filename="'.\basename($file).'";');
                //header("Content-Type: application/x-tar");
                \readfile($file);
                exit;
                break;
            default:
                //header('Content-Description: File Transfer');
                \header('Content-Type: application/force-download');
                \header('Content-Type: application/octet-stream');
                \header('Content-Type: application/download');
                \header('Content-Disposition: attachment; filename="'.\basename($file).'";');
                \header('Content-Transfer-Encoding: binary ');
                //readfile($file);
                //exit;
                echo $content;
                break;
        }

        /*
        if (strstr($HTTP_USER_AGENT,"MSIE")){
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . filesize($file));
        }
        */
    }

    //----------------------------------------------------------

    public function forceDownload_old($params)
    {
        \extract($params);
        $path_parts = \pathinfo($file);
        $ext = \mb_strtolower($path_parts['extension']);
        switch ($ext) {
            case 'asf':
                $type = 'video/x-ms-asf';
                break;
            case 'avi':
                $type = 'video/x-msvideo';
                break;
            case 'exe':
                $type = 'application/octet-stream';
                break;
            case 'mov':
                $type = 'video/quicktime';
                break;
            case 'mp3':
                $type = 'audio/mpeg';
                break;
            case 'mpg':
                $type = 'video/mpeg';
                break;
            case 'mpeg':
                $type = 'video/mpeg';
                break;
            case 'rar':
                $type = 'encoding/x-compress';
                break;
            case 'txt':
                $type = 'text/plain';
                break;
            case 'wav':
                $type = 'audio/wav';
                break;
            case 'wma':
                $type = 'audio/x-ms-wma';
                break;
            case 'wmv':
                $type = 'video/x-ms-wmv';
                break;
            case 'zip':
                $type = 'application/x-zip-compressed';
                break;
            case 'xls':
                $type = 'application/vnd.ms-excel;charset:UTF-8';
                break;
            default:
                $type = 'application/force-download';
                break;
        }

        \header('Pragma: public');
        \header('Expires: 0');
        \header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        \header('Cache-Control: public', false);
        \header('Content-Description: File Transfer');
        \header('Content-Type: '.$type);
        \header('Accept-Ranges: bytes');
        \header('Content-Disposition: attachment; filename="'.\basename($file).'";');
        \header('Content-Transfer-Encoding: binary');
        \header('Content-Length: '.\filesize($file));
        \readfile($file_real);
        exit;
    }

    //------------//-----------------//-------------------
    //------------//-----------------//-------------------
    //------------//-----------------//-------------------
    //------------//-----------------//-------------------
    //C:\xampp\htdocs\consollex\Admin\views\autorizzazione_sanitaria\ODT\IN
    //c:\xampp\consollex\Admin\views\autorizzazione_sanitaria\ODT\IN\
    //------------//-----------------//-------------------

    public function readZippedXML($archiveFile, $dataFile)
    {
        // Create new ZIP archive
        $zip = new ZipArchive();
        // Open received archive file
        if (true === $zip->open($archiveFile)) {
            // If done, search for the data file in the archive
            if (false !== ($index = $zip->locateName($dataFile))) {
                // If found, read it to the string
                $data = $zip->getFromIndex($index);
                // Close archive file
                $zip->close();
                // Load XML from a string
                // Skip errors and warnings
                $xml = DOMDocument::loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                // Return data without XML formatting tags
                //$zip->save('aaa.odt');
                //return ($xml->saveXML());
                $xmlstr = $xml->saveXML();

                return $xmlstr;
                //$xmlstr = str_replace('Objects', 'pippo', $xmlstr);
                //$zip->addFromString ( $dataFile , $xmlstr );
            }
            $zip->close();
        }
        // In case of failure return empty string
        return '';
    }

    //------------//-----------------//-------------------

    public function changeZippedXML($archiveFile, $xmlstr, $dataFile)
    {
        // Create new ZIP archive
        $zip = new ZipArchive();
        // Open received archive file
        if (true === $zip->open($archiveFile)) {
            // If done, search for the data file in the archive
            if (false !== ($index = $zip->locateName($dataFile))) {
                // If found, read it to the string
                $data = $zip->getFromIndex($index);
                // Close archive file
                //$zip->close();
                // Load XML from a string
                // Skip errors and warnings
                //    $xml = DOMDocument::loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                // Return data without XML formatting tags
                //$zip->save('aaa.odt');
                //return ($xml->saveXML());
                // $xmlstr = $xml->saveXML();
                //$xmlstr = str_replace('Objects', 'pippo', $xmlstr);
                $tidy_config = ['clean' => true, //'output-xhtml' => true,
                    'show-body-only' => true, 'wrap' => 0, //ml' => true,
                    'output-xml' => true, 'input-xml' => true,
                ];

                //*
                $tidy = \tidy_parse_string($xmlstr, $tidy_config, 'UTF8');
                $tidy->cleanRepair();
                $xml_out = $tidy;

                //*/
                $zip->addFromString($dataFile, $xmlstr);
                $zip->close();

                return $xmlstr;
            }
            $zip->close();
        }
        // In case of failure return empty string
        return '';
    }

    //--------------------------------------------------------------------------

    public function getArrayFileDir($params)
    {
        //ddd('qui');
        //dir directory dove cercare
        //ext estensione file
        $exclude_dir = ['.', '..'];
        if (isset($params['exclude_dir'])) {
            $exclude_dir = \array_merge($exclude_dir, $params['exclude_dir']);
            $exclude_dir = \array_unique($exclude_dir);
        }
        $params['exclude_dir'] = $exclude_dir;
        \extract($params);

        if ('/' != $dir[\mb_strlen($dir) - 1]) {
            $dir = $dir.'/';
        }
        $dir = \str_replace('/', \DIRECTORY_SEPARATOR, $dir);
        //$dir=FILE_OP::fixpath($dir);
        if (!isset($params['dir_start'])) {
            $params['dir_start'] = $dir;
        }
        self::recursive_mkdir($params['dir_start']);
        /*
        if(!is_dir($dir)){
            //ddd('qui');
            echo '<h3>non e\' una directory valida ['.$dir.']</h3>';
            return array();
        }
        */
        $filez = [];
        if ($handle = \opendir($dir)) {
            //echo "Directory handle: $handle\n";
            //echo "Entries:\n";

            /* This is the correct way to loop over the directory. */
            while (false !== ($entry = \readdir($handle))) {
                $filename = self::fixpath($dir.'/'.$entry);
                //$filename = str_replace('\\', '/', $filename);
                //$filename = str_replace('//', '/', $filename);
                //$filename = str_replace('/',DIRECTORY_SEPARATOR,$filename);
                //echo "<br/>".$filename."\n";
                $path_parts = \pathinfo($filename);
                //echo '<pre>'; print_r($path_parts); echo '</pre>';
                if (isset($path_parts['extension']) && $path_parts['extension'] == $ext) {
                    //$filez[] = $path_parts['basename'];
                    $filez[] = \str_replace($params['dir_start'], '', $filename);
                }

                if (\is_dir($filename) && isset($params['recursive']) && 1 == $params['recursive'] && !\in_array($entry, $exclude_dir, true)) {
                    //ddd('qui');
                    $params['dir'] = $filename;
                    if (!isset($params['level'])) {
                        $params['level'] = 0;
                    }
                    if (0 == $params['level']) {
                        $params['dir_start'] = $dir;
                    }
                    ++$params['level'];
                    //Array_op::print_x($params);
                    $filez = \array_merge($filez, self::getArrayFileDir($params));
                    //$filez[$dir]=FILE_OP::getArrayFileDir($params);
                }
            }
            \closedir($handle);
            //echo '<pre>'; print_r($filez); echo '</pre>';
        }

        return $filez;
    }

    //------------------------------------------------------------------------

    /**
     * Makes directory, returns TRUE if exists or made.
     *
     * @param string $pathname the directory path
     *
     * @return bool returns TRUE if exists or made or FALSE on failure
     */
    public function mkdir_recursive($pathname, $mode = 0777)
    {
        \is_dir(\dirname($pathname)) || mkdir_recursive(\dirname($pathname), $mode);

        return \is_dir($pathname) || @\mkdir($pathname, $mode);
    }

    //end function
}//end class file_op
