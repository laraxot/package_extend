<?php



namespace XRA\Extend\Library;

class URL_OP
{
    // take an "absolute" URL (starting with '/') and make it
    // absolute from doc root. all other urls are passed through.
    // $attrs is optional array of key/value attributes to be added
    // to the URL as a query. if $encode is true, the $url is
    // urlencoded, except for :, /, and # chars, which are assumed
    // to be part of the scheme, path, or fragment. if $full is
    // true, the full scheme, sever, and port portions are added.
    public function url($url = false, $attrs = false, $encode = true, $full = true)
    {
        //return $_SERVER['REQUEST_URI'];
        if (false === $url) {
            //$url = USE_PHP_SELF ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
            $url = $_SERVER['REQUEST_URI'];
        } elseif ('/' == \mb_substr($url, 0, 1)) {
            $url = __SITE_PATH.'/'.\mb_substr($url, 1);  //DOC_ROOT
            if ($full) {
                $scheme = 'http';
                if ('on' == @$_SERVER['HTTPS']) {
                    $scheme .= 's';
                }
                $url = "$scheme://".$_SERVER['HTTP_HOST']."$url";
            }
        }

        //
        //echo '<h3>'.$url.'</h3>';
        $x = \parse_url($url); //path,query
        //ddd($x);
        //ddd('qui');
        /*
         [scheme] => http
        [host] => localhost
        [path] => /gpix/mygpix/
        [query] => rt=pixels/get_pixels
        */
        while ('/' == \mb_substr($x['path'], -1)) {
            $x['path'] = \mb_substr($x['path'], 0, -1);
        }
        if (!isset($x['query'])) {
            $x['query'] = '';
        }
        $x['query'] = \str_replace('&rt=', '&rt[]=', $x['query']);
        //$x['query']=str_replace('?rt=','?rt[]=',$x['query']);
        \parse_str($x['query'], $var_get);
        if (\is_array($attrs)) {
            $var_get = \array_merge($var_get, $attrs);
        }
        $x['query_arr'] = $var_get;

        return \urldecode($x['path'].'?'.Array_op::array_to_query($x['query_arr']));

        /*
        if ($encode) {
          $url = rawurlencode($url);
          $url = str_replace('%2F', '/', $url);
          $url = str_replace('%23', '#', $url);
          $url = str_replace('%3A', ':', $url);
          $url = str_replace('%7E', '~', $url);
        }*/
        /*
        $query = '';
        if (!empty($attrs))
          $query = '?' . $this->array_to_query($attrs);
        return $url . $query;
        */
    }

    public function parse_urlx_rt($req_uri = '')
    {
        if ('' == $req_uri) {
            $req_uri = $_SERVER['REQUEST_URI'];
        }
        $x = self::parse_urlx($req_uri);
        $params = [];
        if (isset($x['query_arr']['a'])) {
            $params['__area_id'] = $x['query_arr']['a'];
        }

        if (!isset($x['query_arr']['rt'])) {
            $x['query_arr']['rt'] = [];
        }
        $x['rt'] = $x['query_arr']['rt'];
        if (!\is_array($x['rt'])) {
            $x['rt'] = [$x['rt']];
        }
        $x['query_arr']['rt'] = '';
        unset($x['query_arr']['rt']);
        $n_rt = \count($x['rt']);
        $str = 'menu/gest/';

        for ($i = $n_rt - 1; $i >= 0; --$i) {
            $v = $x['rt'][$i];
            if (\mb_substr($v, 0, \mb_strlen($str)) == $str) {
                $tmp = \explode('/', $v);
                $params['__menu_id'] = $tmp[2];
                $params = \array_merge($params, \array_slice($tmp, 3));
                $i = -1; //esci;
            }
        }
        if ($n_rt > 0) {
            $last_rt = $x['rt'][$n_rt - 1];
            //echo '<h3>'.$last_rt.'</h3>';
            if (\mb_substr($last_rt, 0, \mb_strlen($str)) != $str) {
                $tmp = \explode('/', $last_rt);
                $x['obj'] = $tmp[0];
                $x['act'] = $tmp[1];
                $params = \array_merge($params, \array_slice($tmp, 2));
            }
        }
        $x['params'] = $params;

        return $x;
    }

    public static function parse_urlx($req_uri = null)
    {
        if (null === $req_uri) {
            $req_uri = $_SERVER['REQUEST_URI'];
        }

        $x = \parse_url($req_uri); //path,query
        if (!isset($x['path'])) {
            $x['path'] = '';
        }
        while ('/' == \mb_substr($x['path'], -1)) {
            $x['path'] = \mb_substr($x['path'], 0, -1);
        }
        if (!isset($x['query'])) {
            $x['query'] = '';
        }
        $x['query'] = \str_replace('&rt=', '&rt[]=', $x['query']);
        //$x['query']=str_replace('?rt=','?rt[]=',$x['query']);
        \parse_str($x['query'], $var_get);
        $x['query_arr'] = $var_get;

        return $x;
    }

    public function deparse_urlx_rt($x)
    {
        $x['query_arr']['rt'] = $x['rt'];
        //ddd($x);
        $query_get = \urldecode(\http_build_query($x['query_arr']));
        $query_get = \preg_replace('/rt\[[0-9+]\]=/', 'rt=', $query_get);
        $y = \urldecode($x['path'].'?'.$query_get);

        return $y;
    }

    public function fixurl($params)
    {
        $ris = [];
        $str = '//';
        if (\mb_substr($params['href'], 0, \mb_strlen($str)) == $str) {
            $ris['url'] = 'http:'.$params['href'];

            return $ris;
        }
        $str = '/';
        if (\mb_substr($params['href'], 0, \mb_strlen($str)) == $str) {
            $ris['url'] = $params['host'].$params['href'];

            return $ris;
        }
        $str = 'http://';
        if (\mb_substr($params['href'], 0, \mb_strlen($str)) == $str) {
            $ris['url'] = $params['href'];

            return $ris;
        }
        $str = 'https://';
        if (\mb_substr($params['href'], 0, \mb_strlen($str)) == $str) {
            $ris['url'] = $params['href'];

            return $ris;
        }
        Array_op::print_x($params);

        ddd('qui');
    }

    /*


    if (!function_exists('http_build_url')){
        define('HTTP_URL_REPLACE', 1);              // Replace every part of the first URL when there's one of the second URL
        define('HTTP_URL_JOIN_PATH', 2);            // Join relative paths
        define('HTTP_URL_JOIN_QUERY', 4);           // Join query strings
        define('HTTP_URL_STRIP_USER', 8);           // Strip any user authentication information
        define('HTTP_URL_STRIP_PASS', 16);          // Strip any password authentication information
        define('HTTP_URL_STRIP_AUTH', 32);          // Strip any authentication information
        define('HTTP_URL_STRIP_PORT', 64);          // Strip explicit port numbers
        define('HTTP_URL_STRIP_PATH', 128);         // Strip complete path
        define('HTTP_URL_STRIP_QUERY', 256);        // Strip query string
        define('HTTP_URL_STRIP_FRAGMENT', 512);     // Strip any fragments (#identifier)
        define('HTTP_URL_STRIP_ALL', 1024);         // Strip anything but scheme and host

        // Build an URL
        // The parts of the second URL will be merged into the first according to the flags argument.
        //
        // @param   mixed           (Part(s) of) an URL in form of a string or associative array like parse_url() returns
        // @param   mixed           Same as the first argument
        // @param   int             A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
        // @param   array           If set, it will be filled with the parts of the composed url like parse_url() would return
        function http_build_url($url, $parts=array(), $flags=HTTP_URL_REPLACE, &$new_url=false){
            $keys = array('user','pass','port','path','query','fragment');

            // HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
            if ($flags & HTTP_URL_STRIP_ALL)
            {
                $flags |= HTTP_URL_STRIP_USER;
                $flags |= HTTP_URL_STRIP_PASS;
                $flags |= HTTP_URL_STRIP_PORT;
                $flags |= HTTP_URL_STRIP_PATH;
                $flags |= HTTP_URL_STRIP_QUERY;
                $flags |= HTTP_URL_STRIP_FRAGMENT;
            }
            // HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
            else if ($flags & HTTP_URL_STRIP_AUTH)
            {
                $flags |= HTTP_URL_STRIP_USER;
                $flags |= HTTP_URL_STRIP_PASS;
            }

            // Parse the original URL
            $parse_url = parse_url($url);

            // Scheme and Host are always replaced
            if (isset($parts['scheme']))
                $parse_url['scheme'] = $parts['scheme'];
            if (isset($parts['host']))
                $parse_url['host'] = $parts['host'];

            // (If applicable) Replace the original URL with it's new parts
            if ($flags & HTTP_URL_REPLACE)
            {
                foreach ($keys as $key)
                {
                    if (isset($parts[$key]))
                        $parse_url[$key] = $parts[$key];
                }
            }
            else
            {
                // Join the original URL path with the new path
                if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH))
                {
                    if (isset($parse_url['path']))
                        $parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
                    else
                        $parse_url['path'] = $parts['path'];
                }

                // Join the original query string with the new query string
                if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY))
                {
                    if (isset($parse_url['query']))
                        $parse_url['query'] .= '&' . $parts['query'];
                    else
                        $parse_url['query'] = $parts['query'];
                }
            }

            // Strips all the applicable sections of the URL
            // Note: Scheme and Host are never stripped
            foreach ($keys as $key)
            {
                if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key)))
                    unset($parse_url[$key]);
            }


            $new_url = $parse_url;

            return
                 ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
                .((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') .'@' : '')
                .((isset($parse_url['host'])) ? $parse_url['host'] : '')
                .((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
                .((isset($parse_url['path'])) ? $parse_url['path'] : '')
                .((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
                .((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '')
            ;
        }
    }
    */
    //------------//-----------------//-------------------

    /**
     * ### getBaseUrl function
     * // utility function that returns base url for
     * // determining return/cancel urls.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if (\PHP_SAPI == 'cli') {
            $trace = \debug_backtrace();
            $relativePath = \mb_substr(\dirname($trace[0]['file']), \mb_strlen(\dirname(__DIR__)));
            echo "Warning: This sample may require a server to handle return URL. Cannot execute in command line. Defaulting URL to http://localhost$relativePath \n";

            return 'http://localhost'.$relativePath;
        }
        $protocol = 'http';
        if (443 == $_SERVER['SERVER_PORT'] || (!empty($_SERVER['HTTPS']) && 'on' == \mb_strtolower($_SERVER['HTTPS']))) {
            $protocol .= 's';
        }
        $host = $_SERVER['HTTP_HOST'];
        $request = $_SERVER['PHP_SELF'];

        return \dirname($protocol.'://'.$host.$request);
    }

    //------------
}//end class
