<?php


//
// http://curl.haxx.se/libcurl/php/examples/
//
class CURL_OP extends baseController
{
    /**
     * Get Headers function.
     *
     * @param string #url
     *
     * @return array
     */
    public function curl_getHeaders($url)
    {
        $ch = \curl_init($url);
        \curl_setopt($ch, CURLOPT_NOBODY, true);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        \curl_setopt($ch, CURLOPT_HEADER, false);
        \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        \curl_setopt($ch, CURLOPT_MAXREDIRS, 3);

        \curl_exec($ch);
        $headers = \curl_getinfo($ch);
        \curl_close($ch);

        return $headers;
    }

    /**
     * Download.
     *
     * @param string $url, $path
     *
     * @return bool || void
     */
    public function curl_download($url, $path)
    {
        // open file to write
        $fp = \fopen($path, 'w+');
        // start curl
        $ch = \curl_init();
        \curl_setopt($ch, CURLOPT_URL, $url);
        // set return transfer to false
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        \curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // increase timeout to download big file
        \curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        // write data to local file
        \curl_setopt($ch, CURLOPT_FILE, $fp);
        // execute curl
        \curl_exec($ch);
        // close curl
        \curl_close($ch);
        // close local file
        \fclose($fp);

        if (\filesize($path) > 0) {
            return true;
        }
    }

    /*
    $url = 'http://path/to/remote/file.jpg';
    $path = 'uploads/file.jpg';

    $headers = getHeaders($url);

    if ($headers['http_code'] === 200 and $headers['download_content_length'] < 1024*1024) {
      if (download($url, $path)){
        echo 'Download complete!';
      }
    }
    */

    /**
     * Get Remote File Size.
     *
     * @param sting $url as remote file URL
     *
     * @return int as file size in byte
     */
    public function remote_file_size($url)
    {
        // Get all header information
        $data = \get_headers($url, true);
        // Look up validity
        if (isset($data['Content-Length'])) {
            // Return file size
            return (int) $data['Content-Length'];
        }
    }

    //--------------------------------------------------------------
    public function curl_xot($params = null)
    {
        //global $ch,$host,$href;
        if (isset($params['ch'])) {
            return $params['ch'];
        }
        \extract($params);
        $ch = \curl_init();
        //$url = 'http://www.allegramenteimmobiliare.it/default.aspx';
        $timeout = 30;
        \curl_setopt($ch, CURLOPT_HEADER, 0);
        \curl_setopt($ch, CURLOPT_VERBOSE, 1);
        \curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
        \curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
        //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        //curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
        \curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        \curl_setopt($ch, CURLOPT_POST, 0);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        \curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //curl_setopt($ch , CURLOPT_SSL_VERIFYPEER, 0 );
        //curl_setopt($ch , CURLOPT_SSL_VERIFYHOST, 0 );
        //curl_setopt( $ch, CURLOPT_ENCODING, "gzip, deflate" );

        //$out2 =getpage($href1);
        //die($out2);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (isset($params['REFERER'])) {
            \curl_setopt($ch, CURLOPT_REFERER, $params['REFERER']);
        }
        $params['REFERER'] = $host.$href;
        $params['ch'] = $ch;

        return $params;
    }

    //-----------------------------------------------------------
    public function getpage($params)
    {
        //global $ch,$host,$href;
        if (!isset($params['ch'])) {
            $params = curl_xot($params);
        }

        \extract($params);
        if (isset($posts)) {
            //url-ify the data for the POST
            //$posts_string='';
            //foreach($posts as $key=>$value) { $posts_string .= $key.'='.$value.'&'; }
            //rtrim($posts_string, '&');
            $posts_string = \http_build_query($posts);
            \curl_setopt($ch, CURLOPT_POST, \count($posts));
            \curl_setopt($ch, CURLOPT_POSTFIELDS, $posts_string);
        }

        $str = 'http:';
        if (\mb_substr($href, 0, \mb_strlen($str)) == $str) {
            $url = $href;
        } else {
            $url = $host.$href;
        }
        echo '<br style="clear:both"/>['.$url.']';

        //$ch = curl_init();
        $url = \str_replace('&amp;', '&', $url);
        //echo '<br/>Url[ <a href="'.$url.'" target="_blank"> '.$url.' </a> ]'.chr(13);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        \curl_setopt($ch, CURLOPT_URL, $url);
        if (isset($params['REFERER'])) {
            \curl_setopt($ch, CURLOPT_REFERER, $params['REFERER']);
        }
        $params['REFERER'] = $host.$href;
        $html = \curl_exec($ch);

        $curl_errno = \curl_errno($ch);
        $curl_error = \curl_error($ch);
        if ($curl_errno > 0) {
            $err_txt = '';
            $err_txt .= '<br/>host :'.$host;
            $err_txt .= '<br/>href :'.$href;
            $err_txt .= '<br/>errono :'.$curl_errno;
            $err_txt .= '<br/>error :'.$curl_error;
            ARRAY_OP::print_x($err_txt);
        }
        //echo $html;

        return ['html' => $html];
    }

    //--------------------------------------------------------------

    public function curl_cache_old($params)
    {
        $ris1 = [];
        if (!isset($params['host']) || '' == $params['host']) {
            $tmp = $params['href'];
            $pos = \mb_strpos($tmp, '/');
            $pos = \mb_strpos($tmp, '/', $pos + 1);
            $pos = \mb_strpos($tmp, '/', $pos + 1);
            $params['host'] = \mb_substr($tmp, 0, $pos);
            $params['href'] = \mb_substr($params['href'], $pos);
        }

        if ('/' == $params['host'][\mb_strlen($params['host']) - 1]) {
            $params['host'] = \mb_substr($params['host'], 0, -1);
        }
        if ('/' != $params['href'][0]) {
            $params['href'] = '/'.$params['href'];
        }
        //ARRAY_OP::print_x($params); ddd('qui');
        //$pagename=STRING_OP::slugify($params['host'].$params['href']).'.html';
        $pagename = STRING_OP::slugify($params['href']).'01.html';
        $tmp = STRING_OP::slugify(\str_replace('http://', '', $params['host']));

        $filename = 'c:/tmp/'.$tmp.'/'.$pagename;
        if (!\file_exists($filename)) {
            //$res=curl_xot($params);
            //$params=array_merge($params,$res);
            $res1 = getPage($params);
            FILE_OP::writeFile($filename, $res1['html'], true);
        } else {
            FILE_OP::showDownloadLink($filename);
            $res1['html'] = \file_get_contents($filename);
        }
        $params['html'] = $res1['html'];

        return $params;
    }

    public function curl_cache_old3($params)
    {
        if (!isset($params['host']) || '' == $params['host']) {
            $tmp = $params['href'];
            $pos = \mb_strpos($tmp, '/');
            $pos = \mb_strpos($tmp, '/', $pos + 1);
            $pos = \mb_strpos($tmp, '/', $pos + 1);
            $params['host'] = \mb_substr($tmp, 0, $pos);
            $params['href'] = \mb_substr($params['href'], $pos);
        }
        $params['url'] = $params['host'].$params['href'];
        //ARRAY_OP::print_x($params['url']);
        $headers = curl_getHeaders($params['url']);
        ARRAY_OP::print_x($headers);
        if (200 === $headers['http_code'] and $headers['download_content_length'] > 0) {
        }
    }

    //------------------------------------------------------------------------------------------
    //-----------------------------------------------------------------------------------------
    public function cache($params)
    {
        if (!isset($params['host']) || '' == $params['host']) {
            $tmp = $params['href'];
            $pos = \mb_strpos($tmp, '/');
            $pos = \mb_strpos($tmp, '/', $pos + 1);
            $pos = \mb_strpos($tmp, '/', $pos + 1);
            $params['host'] = \mb_substr($tmp, 0, $pos);
            $params['href'] = \mb_substr($params['href'], $pos);
            if ('' == $params['host']) {
                $params['host'] = $params['href'];
                $params['href'] = '';
            }
        }
        $params['host'] = \trim($params['host']);
        if (\mb_substr($params['href'], 0, \mb_strlen($params['host'])) == $params['host']) {
            $params['href'] = \mb_substr($params['href'], \mb_strlen($params['host']));
        }
        $params['url'] = $params['host'].$params['href'];
        /*
        echo '<br/>host: '.$params['host'];
        echo '<br/>href: '.$params['href'];
        echo '<br/>url: '.$params['url'];
        ddd('qui');
        */

        //ARRAY_OP::print_x($params);

        $pagename = STRING_OP::slugify($params['href']);
        if ('' == $pagename) {
            $pagename = 'index';
        }
        $pagename = \str_replace('-html', '', $pagename);
        $pagename .= '.html';
        $tmp = STRING_OP::slugify(\str_replace('http://', '', $params['host']));
        $params['path'] = FILE_OP::fixpath('c:/tmp/'.$tmp.'/'.$pagename);
        $caption = '';
        if (!\file_exists($params['path'])) {
            FILE_OP::recursive_mkdir(\dirname($params['path']));
            //ARRAY_OP::print_x(($params['path']));ddd('qui');

            $caption = 'from web  ['.$params['url'].']';
            //$res=curl_xot($params);
            //$params=array_merge($params,$res);
            $headers = self::getHeaders($params);
            //ARRAY_OP::print_x($headers);
        //ddd('qui');
        if (200 == $headers['http_code'] || 301 == $headers['http_code']) {  // 200 ok 301 redirected
            $this->download($params);
        //FILE_OP::showDownloadLink($params['path']);
        } else {
            //ddd('qui');
        }
        } else {
            $caption = 'from dir  ['.$params['url'].']';
        }
        echo '<br style="clear:both"/>'.$caption.'<br/>';
        FILE_OP::showDownloadLink($params['path']);
        $res1 = [];
        $res1['html'] = \file_get_contents($params['path']);
        //$params['html']=$res1['html'];
        return $res1;
    }

    //-----------------//----------------------------//---------------------------------------
    //-------------------------------------------------------------------

    /**
     * Get Headers function.
     *
     * @param string #url
     *
     * @return array
     */
    public function getHeaders($params)
    {
        \extract($params);
        $this->ch = self::__getStatic('ch');
        if ('' == $this->ch) {
            //echo '<br/>inizializzo..<br/>';
            $this->ch = $this->ch_init($params);
            //$this->ch=$this->setProxy($params);
        }
        //$this->ch = curl_init();
        \curl_setopt($this->ch, CURLOPT_URL, $url);
        \curl_setopt($this->ch, CURLOPT_NOBODY, true);
        \curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, false);
        \curl_setopt($this->ch, CURLOPT_HEADER, false);
        \curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        \curl_setopt($this->ch, CURLOPT_MAXREDIRS, 3);
        \curl_exec($this->ch);
        $headers = \curl_getinfo($this->ch);
        //curl_close( $this->ch );
        $this->__set('headers', $headers);

        return $headers;
    }

    /**
     * Download.
     *
     * @param string $url, $path
     *
     * @return bool || void
     */
    public function download($params)
    {
        //ARRAY_OP::print_x($params);
        //ddd('qui');
        \extract($params);
        // open file to write
        //ARRAY_OP::print_x($path); ddd('qui');
        FILE_OP::recursive_mkdir(\dirname($path));
        $fp = \fopen($path, 'w');  //w+
        // start curl
        //$this->ch = curl_init();
        //$this->ch=$this->__get('ch');
        //ARRAY_OP::print_x($this->ch);
        $this->ch = $this->ch_init($params);
        //$this->ch=$this->setProxy($params);

        \curl_setopt($this->ch, CURLOPT_URL, $url);
        // set return transfer to false
        \curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, false);
        \curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, true);
        \curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        // increase timeout to download big file
        \curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 10);
        // write data to local file
        \curl_setopt($this->ch, CURLOPT_FILE, $fp);
        // execute curl
        \curl_exec($this->ch);
        // close curl
        //curl_close( $this->ch );
        // close local file
        \fclose($fp);

        //$this->__set('ch',$this->ch);

        if (\filesize($path) > 0) {
            return true;
        }
    }

    //-----------------------------------------------------------------------------------------

    //----------------------//-----------------------------------------------------------------
    public function curl_cache($params)
    {
        $obj = $this->registry->curlx;
        $ris = $obj->cache($params);

        return \array_merge($params, $ris);
    }

    //$lurl=get_fcontent("http://ip2.cc/?api=cname&ip=84.228.229.81");
    //echo"cid:".$lurl[0]."<BR>";

    public function curl_get_fcontent($url, $javascript_loop = 0, $timeout = 5)
    {
        $url = \str_replace('&amp;', '&', \urldecode(\trim($url)));

        $cookie = \tempnam('/tmp', 'CURLCOOKIE');
        $ch = \curl_init();
        \curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1');
        \curl_setopt($ch, CURLOPT_URL, $url);
        \curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        \curl_setopt($ch, CURLOPT_ENCODING, '');
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    // required for https urls
        \curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        \curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        \curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        $content = \curl_exec($ch);
        $response = \curl_getinfo($ch);
        \curl_close($ch);

        if (301 == $response['http_code'] || 302 == $response['http_code']) {
            \ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1');

            if ($headers = \get_headers($response['url'])) {
                foreach ($headers as $value) {
                    if ('location:' == \mb_substr(\mb_strtolower($value), 0, 9)) {
                        return get_url(\trim(\mb_substr($value, 9, \mb_strlen($value))));
                    }
                }
            }
        }

        if ((\preg_match("/>[[:space:]]+window\.location\.replace\('(.*)'\)/i", $content, $value) || \preg_match("/>[[:space:]]+window\.location\=\"(.*)\"/i", $content, $value)) && $javascript_loop < 5) {
            return get_url($value[1], $javascript_loop + 1);
        } else {
            return [$content, $response];
        }
    }

    //------------//-----------------//-------------------
//------------
}//end class
