<?php



class JS_OP extends baseController
{
    public function minify($params)
    {
        return self::minify_MatthiasMullie($params);
    }

    public function minify_MatthiasMullie($params)
    {
        //$menu_id=$this->registry->template->__get('menu_id');
        //$scripts=$this->getScripts($params);
        $scripts = $params['scripts'];
        $minifier = new MatthiasMullie\Minify\JS();
        \reset($scripts);
        foreach ($scripts as $k => $v) {
            if ('/' == $v[0]) {
                $scripts[$k] = $_SERVER['DOCUMENT_ROOT'].$v;
            }
            $minifier->add($scripts[$k]);
        }

        //$minifiedPath = $_SERVER['DOCUMENT_ROOT'].'/tmp/css/MatthiasMullie.css';

        //$path=$_SERVER['DOCUMENT_ROOT'].'/tmp/js/script4_'.$menu_id.'.js';
        $path = $params['filename'];
        $minifier->minify($path);
        //FILE_OP::writefile($path,$buffer);
        $url = FILE_OP::path2url($path);

        return $url.'?rnd='.\rand(1, 100);
    }

    public function minify_minifier($params)
    {
        //$menu_id=$this->registry->template->__get('menu_id');
        //$scripts=$this->getScripts($params);
        $scripts = $params['scripts'];
        \reset($scripts);
        foreach ($scripts as $k => $v) {
            if ('/' == $v[0]) {
                $scripts[$k] = $_SERVER['DOCUMENT_ROOT'].$v;
            }
        }

        $vars = [
    'encode' => true,
    'timer' => true,
    'gzip' => true,
    'closure' => true,
    'remove_comments' => false,
    'echo' => false,
    ];
        $minified = new Minifier($vars);
        $path = $params['filename'];
        $path = $minified->merge($path, 'JS', $scripts);
        $url = FILE_OP::path2url($path);

        return $url.'?rnd='.\rand(1, 100);
    }

    public function redirectJs($uri = null)
    {
        if (null == $uri) {
            $uri = $_SERVER['REQUEST_URI'];
        }
        echo '<a href="'.$uri.'">Continua..</a>
	<meta http-equiv="refresh" content="0;url="'.$uri.'">
	<script>
		document.location.href="'.$uri.'";
		// use this to avoid redirects when a user clicks "back" in their browser
		window.location.replace("'.$uri.'");
		// use this to redirect, a back button call will trigger the redirection again
		window.location.href = "'.$uri.'";
		// given for completeness, essentially an alias to window.location.href
		window.location = "'.$uri.'";
	</script>
	';
        \header('Location: '.$uri, true, 301);
        exit;
    }

    public function backJs()
    {
        global $registry;
        //$par = $registry->menu->__get('menuParent');
        //JS_OP::redirectJs($par->url);
        $uri_back = $registry->menu->__get('uri_back');
        self::redirectJs($uri_back);
    }

    public function consoleLog($msg)
    {
        echo '<script>console.log("'.\urlencode($msg).'")</script>';
    }

    public function promptJs($label, $uri)
    {
        echo '<script>
	var name;
    do {
        name=prompt("'.$label.'");
    }
    while(name.length < 4);
    document.location.href="'.$uri.'"+name;
	</script>
	';
    }

    ///---------------------------------------------------
    public function swal($params)
    {
        \extract($params);
        $outHtml = '
	<script>
		swal("'.$title.'", "'.$text.'", "'.$type.'");
	</script>';
        echo $outHtml;
    }

    //----------------------------------------------------

    public function confirm($params)
    {
        if (isset($_GET['confirm'])) {
            return $_GET['confirm'];
        }
        $outHtml = '<script>
	if (confirm("'.$params['txt'].'")) {
		document.location.href="'.$_SERVER['REQUEST_URI'].'&confirm=1";
	}else {
		document.location.href="'.$_SERVER['REQUEST_URI'].'&confirm=0";
	};
	</script > Attendere ..';
        echo $outHtml;
    }

    public function parseJSVars($params)
    {
        \extract($params);
        //preg_match_all('/var (.*?)[\n]/', $html, $matches );
        \preg_match_all('|var (.*?)|U', $html, $matches);
        //ARRAY_OP::print_x(count($matches));
        //ARRAY_OP::print_x($matches[1]);
        //ddd('qui');

        $var = [];
        \reset($matches[1]);
        foreach ($matches[1] as $k => $v) {
            //echo '<br/>['.__LINE__.']['.__FILE__.']<pre>'.htmlspecialchars($v).'</pre>';ddd('qui');
            \preg_match_all('/(.*)[ ]+=(.*)/', $v, $matches1);
            //ARRAY_OP::print_x($matches1); ddd('qui');
            if (3 != \count($matches1)) {
                ARRAY_OP::print_x($v);
            }

            $key = \trim($matches1[1][0]);
            //ARRAY_OP::print_x($key);ddd('qui');
            $val = \trim($matches1[2][0]);
            //ARRAY_OP::print_x($matches1); ddd('qui');

            if ("'" == $val[0]) {
                $val = \mb_substr($val, 1);
            }
            if ("'" == $val[\mb_strlen($val) - 1]) {
                $val = \mb_substr($val, 0, -1);
            }
            if (';' == $val[\mb_strlen($val) - 1]) {
                $val = \mb_substr($val, 0, -1);
            }
            if ("'" == $val[\mb_strlen($val) - 1]) {
                $val = \mb_substr($val, 0, -1);
            }
            $var[$key] = $val;
        }

        return $var;
    }

    //------------//-----------------//-------------------

//------------
}//end class
