<?php



class CSS_OP extends baseController
{
    public function minify($params)
    {
        return self::minify_MatthiasMullie($params);
    }

    //end minify

    public function minify_MatthiasMullie($params)
    {
        //$menu_id=$this->registry->template->__get('menu_id');
        //$stylesheets=$this->registry->template->__get('stylesheets');
        //$stylesheets=$this->getStyles($params);
        $stylesheets = $params['stylesheets'];
        $minifier = new MatthiasMullie\Minify\CSS();
        \reset($stylesheets);
        foreach ($stylesheets as $k => $v) {
            if ('/' == $v[0]) {
                $stylesheets[$k] = $_SERVER['DOCUMENT_ROOT'].$v;
            }
            $minifier->add($stylesheets[$k]);
        }

        //$minifiedPath = $_SERVER['DOCUMENT_ROOT'].'/tmp/css/MatthiasMullie.css';

        //$path=$_SERVER['DOCUMENT_ROOT'].'/tmp/css/style4_'.$menu_id.'.css';
        $path = $params['filename'];
        $minifier->minify($path);
        //FILE_OP::writefile($path,$buffer);
        $url = FILE_OP::path2url($path);

        return $url.'?rnd='.\rand(1, 100);
    }

    //--------
    public function minify_minified($params)
    {
        $menu_id = $this->registry->template->__get('menu_id');
        $stylesheets = $this->getStyles($params);
        \reset($stylesheets);
        foreach ($stylesheets as $k => $v) {
            if ('/' == $v[0]) {
                $stylesheets[$k] = $_SERVER['DOCUMENT_ROOT'].$v;
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
        $path = $minified->merge($_SERVER['DOCUMENT_ROOT'].'/tmp/css/style1_'.$menu_id.'.css', 'css/base', $stylesheets);
        $url = FILE_OP::path2url($path);

        return $url.'?rnd='.\rand(1, 100);
    }

    public function minify_file($params)
    {
        $menu_id = $this->registry->template->__get('menu_id');
        $stylesheets = $this->getStyles($params);
        \reset($stylesheets);
        foreach ($stylesheets as $k => $v) {
            if ('/' == $v[0]) {
                $stylesheets[$k] = $_SERVER['DOCUMENT_ROOT'].$v;
            }
        }

        $buffer = '';
        foreach ($stylesheets as $cssFile) {
            $buffer .= \chr(13).\file_get_contents($cssFile);
        }

        // Remove comments
        $buffer = \preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        // Remove space after colons
        $buffer = \str_replace(': ', ':', $buffer);
        // Remove whitespace
        $buffer = \str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $buffer);

        $path = $_SERVER['DOCUMENT_ROOT'].'/tmp/css/style3_'.$menu_id.'.css';
        FILE_OP::writefile($path, $buffer);
        $url = FILE_OP::path2url($path);

        return $url.'?rnd='.\rand(1, 100);

        /*
        // Enable GZip encoding.
        ob_start("ob_gzhandler");
        // Enable caching
        header('Cache-Control: public');
        // Expire in one day
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
        // Set the correct MIME type, because Apache won't set it for us
        header("Content-type: text/css");
        // Write everything out
        echo($buffer);
        */
    }

    //------------------
}//end class
