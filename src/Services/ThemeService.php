<?php
namespace XRA\Extend\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

//use Illuminate\Contracts\View\Factory as ViewContract;
//https://github.com/eusonlito/laravel-Packer
//genealabs/laravel-model-caching
use Assetic\Asset\AssetCache;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Cache\FilesystemCache;
//http://cgit.drupalcode.org/sandbox-linclark-2011168/tree/core/vendor/kriswallsmith/assetic/src/Assetic/Filter/JSMinPlusFilter.php?h=1869600-refactor-rdf&id=ef326513c577095b7d6ab75f49d5f3ef60d88710
use Assetic\Filter\CssMinFilter;
//use Assetic\Filter\JsMinPlusFilter;
use Assetic\Filter\JsMinFilter;
use Assetic\Filter\ScssphpFilter;
//----- Models -----

//---- xot extend -----
use Illuminate\Support\Facades\View;
use Route;
use XRA\Extend\Library\XmlMenu_op;
use XRA\Extend\Traits\Getter;
//----- services --
use XRA\Extend\Services\ArtisanService;

//---------CSS----------

class ThemeService
{
    use Getter;
    public static $config_name = 'metatag';
    public static $vars = [];
    private static $_instance = null;

    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function getTpl($params)
    {
        \extract($params);
        $controller_name = '\App\Http\Controllers\\'.\ucwords($obj).'Controller';
        $out = app($controller_name)->getTpl($params);

        return view($out['view'], $out['params']);
    }

    public static function getRow($params)
    {
        \extract($params);
        $controller_name = '\App\Http\Controllers\\'.\ucwords($obj).'Controller';
        $out = app($controller_name)->getRow($params);

        return $out;
    }

    public static function get_url($params)
    {
        return url('/');
    }

    public static function get_tags($params)
    {
        return [];
    }

    public static function get_styles($params)
    {
        return [];
    }

    public static function get_scripts($params)
    {
        return [];
    }

    public static function get_langs($params)
    {
        $uri=(\Request::has('uri'))?\Request::input('uri'):$_SERVER['REQUEST_URI'];
        
        $cache_key=str_slug($uri.'_langs');
        $langs=Cache::get($cache_key);
        if(!is_array($langs)) $langs=[];
        return $langs;
    }


    public static function get_scripts_pos($params)
    {
        return [];
    }

    public static function get_styles_pos($params)
    {
        return [];
    }

    public static function get_view_params($params)
    {
        return [];
    }

    public static function get_language($params)
    {
        $lang = \App::getLocale();
        $locale = config('laravellocalization.supportedLocales.'.$lang);

        return $locale['regional'];
    }

    public static function add($file, $position = null)
    {
        //echo '<br/>['.$file.']';

        $path_parts = \pathinfo($file);
        $ext = \mb_strtolower($path_parts['extension']);
        switch ($ext) {
            case 'css':
                return self::addStyle($file, $position);
            case 'js':
                return self::addScript($file, $position);
            default:
                echo '<h3>'.$file.'['.$ext.']</h3>';
                die('['.__LINE__.']['.__FILE__.']');
            break;
        }
    }

    public static function addStyle($style, $position = null)
    {
        if (null == $position) {
            $styles = self::__getStatic('styles');
            $position = \count($styles) + 10;
        }
        $styles_pos = self::__merge('styles_pos', [$position]);
        $styles = self::__merge('styles', [$style]);
    }

    public static function addScript($script, $position = null)
    {
        if (null == $position) {
            $scripts = self::__getStatic('scripts');
            $position = \count($scripts) + 10;
        }
        $scripts_pos = self::__merge('scripts_pos', [$position]);
        $scripts = self::__merge('scripts', [$script]);
    }

    public static function addViewParam($key, $value = null)
    {
        $view_params = self::__getStatic('view_params');
        if (!\is_array($key)) {
            $view_params[$key] = $value;
        } else {
            $view_params = \array_merge($view_params, $key);
        }
        self::__setStatic('view_params', $view_params);

        return self::getInstance(); /// per il fluent, o chaining
    }

    public static function addFavicon($src, $attrs = [])
    {
        $newsrc = self::getFileUrl($src);

        $favicon = '<link rel="shortcut icon" href="'.$newsrc.'"';
        foreach ($attrs as $k => $v) {
            $favicon .= ' '.$k.'="'.$v.'"';
        }

        return $favicon.'/>';
    }

    public static function addContentTools($params = [])
    {
        self::add('theme/bc/ContentTools/build/content-tools.min.css');
        self::add('theme/bc/ContentTools/build/content-tools.min.js');
        self::add('blog::js/contenttools.js');
        self::add('theme/bc/ContentTools/sandbox/sandbox.css');
    }

    public static function addSelect2($params = [])
    {
        /* librerie in locale
        self::add('theme/bc/select2/dist/css/select2.min.css');
        self::add('theme/bc/select2-bootstrap4-theme/dist/select2-bootstrap4.css');
        //self::add('theme/bc/popper.js/dist/popper.min.js');
        self::add('theme/bc/select2/dist/js/select2.full.min.js');
        self::add('extend::js/select2ajax.js');
        */
        //* librerin in cdn
        self::add('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css');
        self::add('theme/bc/select2-bootstrap4-theme/dist/select2-bootstrap4.css');
        self::add('https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js');
        self::add('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js');
        self::add('extend::js/select2ajax.js');
    }

    public static function view_path($view)
    {
        $viewNamespace = '---';
        $pos = \mb_strpos($view, '::');

        if ($pos) {
            $hints = \mb_substr($view, 0, $pos);
            $filename = \mb_substr($view, $pos + 2);
            $viewHints = View::getFinder()->getHints();
            if (isset($viewHints[$hints][0])) {
                $viewNamespace = $viewHints[$hints][0];

                return $viewNamespace.\DIRECTORY_SEPARATOR.$filename;
            } else {
                $viewNamespace = '---';
            }
        }

        return $viewNamespace;
    }

    public static function img_src($src)
    {
        $srcz = self::viewNamespaceToUrl([$src]);
        $src = $srcz[0];

        return asset($src);
    }

    public static function logo_html()
    {
        $logo_src = self::metatag('logo_img');
        $newsrc = self::getFileUrl($logo_src);
        $logo_alt = self::metatag('logo_alt');
        $attrs = [];
        $attrs['alt'] = $logo_alt;
        $img = '<img src="'.$newsrc.'"';
        foreach ($attrs as $k => $v) {
            $img .= ' '.$k.'="'.$v.'"';
        }

        return $img.'/>';
    }

    public static function showImg($src, $attrs = [], $only_url = false)
    {
        $srcz = self::viewNamespaceToUrl([$src]);

        $src = $srcz[0];
        $newsrc = self::getFileUrl($src);
        if ($only_url) {
            return $newsrc;
        }
        $img = '<img src="'.$newsrc.'"';
        foreach ($attrs as $k => $v) {
            $img .= ' '.$k.'="'.$v.'"';
        }

        return $img.'/>';
    }

    public static function getNameSpace($path)
    {
        $pos = \mb_strpos($path, '::');
        if (false === $pos) {
            return false;
        }

        return \mb_substr($path, 0, $pos);
    }

    public static function getViewNameSpacePath($ns)
    {
        if (null == $ns) {
            return null;
        }
        //dirname(\View::getFinder()->find('extend::includes.components.form.text'));
        $viewHints = View::getFinder()->getHints();
        if (isset($viewHints[$ns])) {
            return $viewHints[$ns][0];
        }
    }

    public static function getViewNameSpaceUrl($ns, $path1)
    {
        $path = self::getViewNameSpacePath($ns);
        /* 4 debug
        if(basename($path1)=='font-awesome.min.css'){
            ddd('-['.$path.']['.public_path('').']');
        }
        //*/
        if (starts_with($path, public_path(''))) {
            $relative = \mb_substr($path, \mb_strlen(public_path('')));
            $relative = str_replace('\\','/',$relative);
            return asset($relative.'/'.$path1);
        }
        $filename = $path.'/'.$path1;
        $path_pub = 'assets_packs/'.$ns.'/'.$path1;
        $filename_pub = public_path($path_pub);

        if (!\File::exists(\dirname($filename_pub))) {
            try {
                \File::makeDirectory(\dirname($filename_pub), 0755, true, true);
            } catch (Exception $e) {
                dd('Caught exception: ', $e->getMessage(), '\n['.__LINE__.']['.__FILE__.']');
            }
        }
        if (\File::exists($filename)) {
            try {
                //echo '<hr>'.$filename.' >>>>  '.$filename_pub; //4 debug
                \File::copy($filename, $filename_pub);
            } catch (Exception $e) {
                dd('Caught exception: ', $e->getMessage(), '\n['.__LINE__.']['.__FILE__.']');
            }
        } else {
            //ddd('non esiste '.str_replace('/',DIRECTORY_SEPARATOR,$filename)); //4 debug
        }
        return asset($path_pub);
    }

    public static function path2Url($path,$ns){
        if (starts_with($path, public_path('/'))) {
            $relative = \mb_substr($path, \mb_strlen(public_path('/')));
            return asset($relative);
        }
        $filename = $path;
        $ns_dir=self::getViewNameSpacePath($ns);
        $path1=substr($path,strlen($ns_dir));
        $path_pub = 'assets_packs/'.$ns.'/'.$path1;
        $filename_pub = public_path($path_pub);

        if (!\File::exists(\dirname($filename_pub))) {
            try {
                \File::makeDirectory(\dirname($filename_pub), 0755, true, true);
            } catch (Exception $e) {
                dd('Caught exception: ', $e->getMessage(), '\n['.__LINE__.']['.__FILE__.']');
            }
        }
        if (\File::exists($filename)) {
            try {
                //echo '<hr>'.$filename.' >>>>  '.$filename_pub; //4 debug
                \File::copy($filename, $filename_pub);
            } catch (Exception $e) {
                dd('Caught exception: ', $e->getMessage(), '\n['.__LINE__.']['.__FILE__.']');
            }
        } else {
            //ddd('non esiste '.$filename); //4 debug
        }
        return asset($path_pub);
    }


    public static function asset($path)
    {

        if ('/' == $path[0]) {
            $path = \mb_substr($path, 1);
        }
        $str = 'theme/bc/';
        if (starts_with($path, $str)) {
            return asset('/bc/'.\mb_substr($path, \mb_strlen($str)));
        }
        $str = 'theme/pub/';
        if (starts_with($path, $str)) {
            $path = 'pub_theme::'.\mb_substr($path, \mb_strlen($str));
        }
        $str = 'theme/';
        if (starts_with($path, $str)) {
            $path = 'adm_theme::'.\mb_substr($path, \mb_strlen($str));
        }

        $str = 'https://';
        if (starts_with($path, $str)) {
            return $path; 
        }
        
        $str = 'http://';
        if (starts_with($path, $str)) {
            return $path; 
        }

        $ns = self::getNameSpace($path);
        if($ns===false){
            return asset($path); // sperimentale..
            if(\File::exists($path)){
                //ddd('da implementare ['..']['.$path.']['.asset($path).']');
            }else{
                //ddd($path);
            }
        }
        $other = str_after($path, $ns.'::');
        $pos = \mb_strpos($other, '/');
        $other0 = \mb_substr($other, 0, $pos);
        $other1 = \mb_substr($other, $pos);
        //ddd('iiii ['.$pos.']['.$path.']['.$ns.']['.$other0.']['.$other1.']');
        //iiii [41]
        //[extend::includes.components.form.html5_upload.img/js/html5imageupload.js]
        //[extend]
        //[includes.components.form.html5_upload.img]
        //[/js/html5imageupload.js]

        try{
            $other0=dirname(\View::getFinder()->find($ns.'::'.$other0));
            $ns_dir=self::getViewNameSpacePath($ns);
            $other0=substr($other0, strlen($ns_dir));

        }catch(\Exception  $e){
            $other0 = \str_replace('.', '/', $other0);
        }
        //echo '<br/>['.$ns.']['.$other0.$other1.']';

        $url = self::getViewNameSpaceUrl($ns, $other0.$other1);

        return $url;//.'/'.$other0.$other1;
    }

    public static function url($path)
    {
        if($path=='') return $path;
        if ('/' == $path[0]) {
            $path = \mb_substr($path, 1);
        }
        $str = 'theme/bc/';
        if (\mb_substr($path, 0, \mb_strlen($str)) == $str) {
            $filename = asset('/bc/'.\mb_substr($path, \mb_strlen($str)));

            return $filename;
        }
        $str = 'theme/pub/';
        $theme = config('xra.pub_theme');
        if (\mb_substr($path, 0, \mb_strlen($str)) == $str) {
            $filename = asset('/themes/'.$theme.'/'.\mb_substr($path, \mb_strlen($str)));

            return $filename;
        }
        $str = 'theme/';
        $theme = config('xra.adm_theme');
        if (\mb_substr($path, 0, \mb_strlen($str)) == $str) {
            $filename = asset('/themes/'.$theme.'/'.\mb_substr($path, \mb_strlen($str)));

            return $filename;
        }

        return ''.$path;
    }

    public static function getFileUrl($path)
    {
        if ('/' == $path[0]) {
            $path = \mb_substr($path, 1);
        }
        $str = 'theme/bc/';
        if (\mb_substr($path, 0, \mb_strlen($str)) == $str) {
            $filename = asset('/bc/'.\mb_substr($path, \mb_strlen($str)));

            return $filename;
        }
        $str = 'theme/pub/';
        $theme = config('xra.pub_theme');
        if (\mb_substr($path, 0, \mb_strlen($str)) == $str) {
            $filename = asset('/themes/'.$theme.'/'.\mb_substr($path, \mb_strlen($str)));

            return $filename;
        }
        $str = 'theme/';
        $theme = config('xra.adm_theme');
        if (\mb_substr($path, 0, \mb_strlen($str)) == $str) {
            $filename = asset('/themes/'.$theme.'/'.\mb_substr($path, \mb_strlen($str)));

            return $filename;
        }

        return ''.$path;
    }

    public static function viewNamespaceToUrl($files)
    {
        foreach ($files as $k => $filePath) {
            //TODO testare con ARTISAN vendor:publish
            $pos = \mb_strpos($filePath, '::');
            if ($pos) {
                $hints = \mb_substr($filePath, 0, $pos);
                $filename = \mb_substr($filePath, $pos + 2);
                $viewHints = View::getFinder()->getHints();
                if (isset($viewHints[$hints][0])) {
                    $viewNamespace = $viewHints[$hints][0];
                } else {
                    $viewNamespace = '---';
                }
                if ('pub_theme' == $hints) {
                    $tmp = \str_replace(public_path(''), '', $viewNamespace);
                    $tmp = \str_replace(\DIRECTORY_SEPARATOR, '/', $tmp);
                    $pos = \mb_strpos($filename, '/');
                    $filename0 = \mb_substr($filename, 0, $pos);
                    $filename0 = \str_replace('.', '/', $filename0);
                    $filename1 = \mb_substr($filename, $pos);
                    $filename = $filename0.''.$filename1;
                    //echo '<h3>'.$filename0.''.$filename1.'</h3>';
                    //dd($tmp.'/'.$filename);
                    $new_url = $tmp.'/'.$filename;
                } else {
                    $old_path = $viewNamespace.\DIRECTORY_SEPARATOR.$filename;
                    $old_path = \str_replace('/', \DIRECTORY_SEPARATOR, $old_path);
                    $new_path = public_path('assets_packs'.\DIRECTORY_SEPARATOR.$hints.\DIRECTORY_SEPARATOR.$filename);
                    $new_path = \str_replace('/', \DIRECTORY_SEPARATOR, $new_path);
                    if (!\File::exists(\dirname($new_path))) {
                        try {
                            \File::makeDirectory(\dirname($new_path), 0755, true, true);
                        } catch (Exception $e) {
                            dd('Caught exception: ', $e->getMessage(), '\n['.__LINE__.']['.__FILE__.']');
                        }
                    }
                    if (\File::exists($old_path)) {
                        try {
                            \File::copy($old_path, $new_path);
                        } catch (Exception $e) {
                            dd('Caught exception: ', $e->getMessage(), '\n['.__LINE__.']['.__FILE__.']');
                        }
                    }
                    $new_url = \str_replace(public_path(), '', $new_path);
                    $new_url = \str_replace('\\/', '/', $new_url);
                    $new_url = \str_replace(\DIRECTORY_SEPARATOR, '/', $new_url);
                }
                $files[$k] = $new_url;
            }
        }

        return $files;
    }

    public static function showScripts($compress_js = true, $page = '')
    {

        //TODO FIX url da funzione e non replace !!!
        //
        $scripts_pos = self::__getStatic('scripts_pos');
        $scripts = self::__getStatic('scripts');
        $scripts = \array_values(array_sort($scripts, function ($v, $k) use ($scripts_pos) {
            return $scripts_pos[$k];
        }));
        $scripts = \array_unique($scripts);

        //$scripts = self::viewNamespaceToUrl($scripts);
        $scripts=collect($scripts)->map(function($item){
            return self::asset($item);
        })->all();
        //ddd($scripts);


        /*
        $scripts=collect($scripts)->map(function($item){
            return self::asset($item);
        })->all();
        */
        //ddd($scripts);

        if ($compress_js) {
            $scripts_list = \implode(',', $scripts);
            $scripts_md5 = \md5($scripts_list);

            $name = '/js/script_'.$scripts_md5.'.js';
            $path = public_path($name);
            $force = 1;
            if (!\file_exists($path) || $force) {
                //$cache = new FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/tmp'); //cartella tmp non esiste piu'
                $cache = new FilesystemCache(base_path('../cache'));
                $collection = new AssetCollection();
                foreach ($scripts as $filePath) {
                    $filePath = self::getRealFile($filePath);
                    if ('http' == \mb_substr($filePath, 0, \mb_strlen('http'))) {
                        $filename = \md5($filePath).'.css';
                        if (!\Storage::disk('cache')->exists($filename)) {
                            \Storage::disk('cache')->put($filename, \fopen($filePath, 'r'));
                        }
                        $asset = new FileAsset(\Storage::disk('cache')->path($filename));
                    } else {
                        $asset = new FileAsset($filePath);
                    }
                    if (\class_exists('JsMinFilter')) {
                        $asset->ensureFilter(new JsMinFilter());
                    }

                    $cachedAsset = new AssetCache($asset, $cache);
                    $collection->add($cachedAsset);
                }//end foreach

                \File::put($path, $collection->dump());
            }//end if
            $scripts = [$name];
        } else {
            foreach ($scripts as $k => $filePath) {
                $scripts[$k] = self::getFileUrl($filePath);
            }//end foreach
        }//end if
        foreach ($scripts as $k => $v) {
            $scripts[$k] = self::getFileUrl($v);
        }//end foreach
        $scripts = \array_unique($scripts);

        return view('extend::services.script')->with('scripts', $scripts);
    }

    //end function

    public static function getRealFile($path)
    {
        if(starts_with($path,asset(''))){
            return public_path(substr($path, strlen(asset(''))));
        }
        if ('/' == $path[0]) {
            $path = \mb_substr($path, 1);
        }
        $str = 'theme/bc/';
        //if (\mb_substr($path, 0, \mb_strlen($str)) == $str) {
        if(starts_with($path,$str)){
            $filename = public_path('bc/'.\mb_substr($path, \mb_strlen($str)));
            //$filename=str_replace('\\/','/',$filename);
            //$filename=realpath($filename);
            return $filename;
        }
        $str = 'theme/pub/';
        $theme = config('xra.pub_theme');
        //if (\mb_substr($path, 0, \mb_strlen($str)) == $str) {
        if(starts_with($path,$str)){
            $filename = public_path('themes/'.$theme.'/'.\mb_substr($path, \mb_strlen($str)));
            //$filename=str_replace('\\/','/',$filename);
            //$filename=realpath($filename);
            return $filename;
        }
        $str = 'theme/';
        $theme = config('xra.adm_theme');
        //if (\mb_substr($path, 0, \mb_strlen($str)) == $str) {
        if(starts_with($path,$str)){
            $filename = public_path('themes/'.$theme.'/'.\mb_substr($path, \mb_strlen($str)));

            return $filename;
        }
        $str = 'https://';
        if(starts_with($path,$str)){
            $info=pathinfo($path);
            switch($info['extension']){
                case 'css': $filename=public_path('/css/'.$info['basename']); break;
                case 'js':  $filename=public_path('/js/'.$info['basename']); break;
                default:
                    echo '<h3>Unknown Extension</h3>';
                    echo '<h3>['.$path.']</h3>';
                    ddd($info);
                break;
            }
            ImportService::download(['url'=>$path,'filename'=>$filename]);
            return $filename;
        }


        return ''.$path;
    }

    public static function showStyles($compress_css = true)
    {
        $styles_pos = self::__getStatic('styles_pos');
        $styles = self::__getStatic('styles');
        $styles = \array_values(array_sort($styles, function ($v, $k) use ($styles_pos) {
            return $styles_pos[$k];
        }));
        $styles = \array_unique($styles);

        //$styles = self::viewNamespaceToUrl($styles);
        $styles=collect($styles)->map(function ($item){
            return self::asset($item);
        })->all();
        //ddd(asset('/'));
        $not_compress=[];

        if ($compress_css) {
            $styles_list = \implode(',', $styles);
            $styles_md5 = \md5($styles_list);
            $name = '/css/style_'.$styles_md5.'.css';
            $path = public_path($name);
            $force = 1;
            //dd( storage_path('cache')); //pb\laravel\storage\cache
            //dd(\Storage::disk('cache')->url('file.jpg'));//getPath());
            if (!\file_exists($path) || $force) {
                //$cache = new FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/tmp');
                //$cache = new FilesystemCache(dirname($path));
                require __DIR__.'/vendor/autoload.php';//carico la mia libreria che uso solo qui..
                $cache = new FilesystemCache(base_path('../cache'));
                $collection = new AssetCollection();

                foreach ($styles as $filePath) {
                    //echo '<br/>['.$filePath.']';
                    $filePath = self::getRealFile($filePath);
                    if (!\File::exists($filePath)) {
                        //echo '<h3> non esiste il file ['.$filePath.']</h3>';
                        //die('['.__LINE__.']['.__FILE__.']');

                        $not_compress[]=$filePath;
                    }else{
                        $asset = new FileAsset($filePath);
                        $info = \pathinfo($filePath);
                        if (isset($info['extension'])) {
                            switch ($info['extension']) {
                                case 'scss':
                                    $asset->ensureFilter(new ScssphpFilter());
                                break;
                            default:
                                $asset->ensureFilter(new CssMinFilter());
                            break;
                            }
                        }
                        $cachedAsset = new AssetCache($asset, $cache);
                        $collection->add($cachedAsset);
                    }
                }

                $path = public_path().$name;

                \File::put($path, $collection->dump());
            }
            $styles = [$name];
        } else {
            foreach ($styles as $k => $filePath) {
                $styles[$k] = self::getFileUrl($filePath);
            }
        }
        //ddd($not_compress);
        return view('extend::services.style')->with('styles', $styles);
    }

    public static function tag($html)
    {
        $count = 0;
        \preg_match_all('/##([a-z]*)_([a-z]*)_([0-9]{1,30})##/i', $html, $matches);

        $count += \count($matches);
        $tmp = [];
        foreach ($matches[0] as $km => $vm) {
            $obj = new \stdclass();
            $obj->obj = $obj->act = $matches[1][$km];
            $obj->act = $matches[2][$km];

            $obj->id = $matches[3][$km];
            $obj->str = $matches[0][$km];
            $tmp[] = $obj;
        }

        $params = \Route::current()->parameters();

        $params['html'] = $html;
        $params['azioniTags'] = $tmp;
        $html = self::do_mySpecialTags($params);

        \preg_match_all('/##([a-z]*)_([0-9]*)_([a-z]{1,30})##/i', $html, $matches);

        $count += \count($matches);
        $tmp = [];
        foreach ($matches[0] as $km => $vm) {
            $obj = new \stdclass();
            $obj->obj = $obj->act = $matches[1][$km];
            $obj->act = $matches[3][$km];
            $obj->id = $matches[2][$km];
            $obj->str = $matches[0][$km];
            $tmp[] = $obj;
        }

        $params = \Route::current()->parameters();

        $params['html'] = $html;
        $params['azioniTags'] = $tmp;
        $html = self::do_mySpecialTags($params);

        \preg_match_all('/##([a-z]*)_([0-9]{1,30})##/i', $html, $matches);

        $count += \count($matches);
        $tmp = [];
        foreach ($matches[0] as $km => $vm) {
            $obj = new \stdclass();
            $obj->obj = $obj->act = $matches[1][$km];
            if ('albumran' == $obj->obj) {
                $obj->obj = 'album';
            }
            $obj->act = 'get';
            if ('albumran' == $obj->obj) {
                $obj->obj = 'album';
            }
            $obj->id = $matches[2][$km];
            $obj->str = $matches[0][$km];
            $tmp[] = $obj;
        }

        $html = self::do_mySpecialTags(['html' => $html, 'azioniTags' => $tmp]);

        \preg_match_all('/##([a-z]*)_([a-z]*)##/i', $html, $matches);

        $count += \count($matches);
        $tmp = [];
        foreach ($matches[0] as $km => $vm) {
            $obj = new \stdclass();
            $obj->obj = $obj->act = $matches[1][$km];
            $obj->act = $matches[2][$km];

            $obj->str = $matches[0][$km];
            $tmp[] = $obj;
        }

        $html = self::do_mySpecialTags(['html' => $html, 'azioniTags' => $tmp]);

        \preg_match_all('/##([a-z]*)##/i', $html, $matches);

        $count += \count($matches);
        $tmp = [];
        foreach ($matches[0] as $km => $vm) {
            $obj = new \stdclass();
            $obj->obj = $obj->act = $matches[1][$km];
            $obj->act = $matches[1][$km];

            $obj->str = $matches[0][$km];
            $tmp[] = $obj;
        }

        $html = self::do_mySpecialTags(['html' => $html, 'azioniTags' => $tmp]);

        return $html;
    }

    public static function do_mySpecialTags($params)
    {
        \extract($params);
        \reset($azioniTags);

        $params = \Route::current()->parameters();

        foreach ($azioniTags as $kt => $vt) {
            $obj = $vt->obj;
            if ('form' == $obj) {
                $obj .= 'tpl';
            }
            $act = $vt->act.'HTML';

            $params['html'] = '';

            $params = \array_merge($params, \get_object_vars($vt));

            $controller_name = '\App\Http\Controllers\\'.\ucwords($obj).'Controller';

            $out = app($controller_name)->$act($params);
            if (!\is_string($out)) {
                $out = $out->__toString();
            }

            $html = \str_replace($vt->str, $out, $html);
        }

        return $html;
    }

    public static function getBladeInclude($obj, $act, $id)
    {
        $controller_name = '\App\Http\Controllers\\'.\ucwords($obj).'Controller';
        $act = $act.'BladeInclude';

        return app($controller_name)->$act($id);
    }

    public static function metatags()
    {
        $view = 'extend::metatags';

        return (string) view($view);
    }

    public static function metatag($index)
    {
        $ris = self::__getStatic($index);
        //echo '<br/>['.$index.']['.$ris.']';
        if ('' == $ris) {
            $ris = config('metatag.'.$index);
            self::__setStatic($index, $ris);
        }

        return $ris;
    }

    public static function setMetatags($row)
    {
        //ddd($row);
        $params = \Route::current()->parameters();
        foreach ($params as $v) {
            if (\is_object($v) && isset($v->title)) {
                self::__concatBeforeStatic('title', $v->title.' | ');
            }
        }
        if (!\is_object($row)) {
            return; // forse buttare in 404 ..
        }
        //-- solo i campi che interessano
        $fields = ['subtitle', 'meta_description', 'meta_keywords', 'url', 'type', 'updated_at', 'published_at'];
        foreach ($fields as $field) {
            self::__setStatic($field, $row->$field);
        }
        //self::__concatBeforeStatic('title', $row->title.' | ');
        //ddd(self::__getStatic('title'));
        //self::__concatBeforeStatic('subtitle', $row->subtitle.' | ');
        //self::__setStatic('url', $row->url);
        //self::__setStatic('category', $row->post_type);
        //self::__setStatic('published_at', $row->published_at);
        //self::__setStatic('updated_at', $row->updated_at);
        $supportedLocales = config('laravellocalization.supportedLocales');
        self::__setStatic('locale', $supportedLocales[$row->lang]['regional']);
        //self::__setStatic('description', $row->meta_description);
        //self::__setStatic('keywords', $row->meta_keywords);
        //self::__setStatic('type', $row->post_type);
        $image_width = 200;
        $image_height = 200;
        $image = $row->imageResizeSrc(['width' => $image_width, 'height' => $image_height]);
        self::__setStatic('image', asset($image));
        self::__setStatic('image_width', $image_width);
        self::__setStatic('image_height', $image_height);
    }

    public static function getArea()
    {
        $tmp = \explode('/', \Route::current()->getCompiled()->getStaticPrefix());
        $tmp = \array_slice($tmp, 2, 1);

        if (\count($tmp) < 1) {
            return false;
        }

        return $tmp[0];
    }

    public static function getXmlMenu()
    {
        $menu0 = XmlMenu_op::get();
        if ($menu0 == []) {
            return $menu0;
        }
        $menus = XmlMenu_op::setActiveAndGrouped(['data' => $menu0]);

        return $menus;
    }

    //end getXmlMenu

    public static function viewNamespaceToDir($view)
    {
        $pos = \mb_strpos($view, '::');
        $pack = \mb_substr($view, 0, $pos);
        //relative from pack
        $relative_path = \str_replace('.', '/', \mb_substr($view, $pos + 2));
        $viewHints = View::getFinder()->getHints();
        $pack_dir = $viewHints[$pack][0];
        $view_dir = $pack_dir.'/'.$relative_path;
        $view_dir = \str_replace('/', \DIRECTORY_SEPARATOR, $view_dir);

        return $view_dir;
    }

    public static function tabs($params)
    {
        //o glielo passo come parametro o me lo calcolo, ma da errore su tabs annidati
        if (isset($params['view'])) {
            $tabs_view = $params['view'].'.tabs';
            $tabs_dir = self::viewNamespaceToDir($tabs_view);
        } else {
            $routename = Route::currentRouteName();
            $action = Route::currentRouteAction();
            $action = \explode('\\', $action);
            $pack = \mb_strtolower($action[1]);
            $tabs_view = $pack.'::'.$routename.'.tabs';
            //manca "admin"
            $tabs_dir = self::viewNamespaceToDir($tabs_view);
        }
        //dd($tabs_dir);
        $files = \File::files($tabs_dir);
        $tabs = [];
        $blade_ext = '.blade.php';
        foreach ($files as $file) {
            /* sostituire pathinfo in queste funzioni per la laravel way
            $file->getSize();
        $file->getFilename();
        $file->getType();
            */
            $path_parts = \pathinfo($file);

            if (\mb_substr($path_parts['basename'], -\mb_strlen($blade_ext)) == $blade_ext) {
                $tmp = [];
                //$tmp['path']=(string)$file; //usefull for debug
                $name = \mb_substr($path_parts['basename'], 0, -\mb_strlen($blade_ext));
                $re = '/([^_]*)_([0-9]*)/';
                $ris = \preg_match($re, $name, $matches);
                //echo '<pre>';print_r($ris);echo '</pre>';die();
                if ($ris) {
                    $tmp['name'] = $matches[1];
                    $tmp['position'] = $matches[2] * 1;
                } else {
                    $tmp['name'] = $name;
                    $tmp['position'] = 10;
                }
                $tmp['view'] = $name;

                $tabs[] = $tmp;
            }
        }

        $tabs = collect($tabs)->sortBy('position')->all();
        $tabs = \array_values($tabs);
        //echo '<pre>';print_r($tabs);echo '</pre>';die();
        /*
        $tabs=[
                ['name'=>'menu',        'icon'=>'fa fa-cutlery orange-text'],
                ['name'=>'bookatable',  'icon'=>'fa fa-calendar-check-o orange-text'],
                ['name'=>'reviews',     'icon'=>'fa fa-comments orange-text'],
                ['name'=>'info',        'icon'=>'fa fa-info-circle orange-text'],
                ];
        */

        //return view('extend::services.tabs',$params)
        $view = 'extend::services.tabs';
        $view = 'pub_theme::includes.partials.tabs';
        $view = 'adm_theme::includes.partials.tabs';

        return view($view, $params)
        ->with('tabs', $tabs)
        ->with('tabs_view', $tabs_view)
        //->with('row',$params['row'])
        ;
    }

    /**
     * { item_description }.
     */
    public static function route($params = [])
    {
        $params = \array_merge(\Route::current()->parameters(), $params);
        $routename = Route::currentRouteName();

        return url(route($routename, $params, false));
    }

    /**
     * { item_description }.
     */
    public static function getView($params = [])
    {
        $params = array_merge(\Route::current()->parameters(),$params);
        \extract($params);
        $tmp = \Route::currentRouteAction();
        //echo '<h3>'.$tmp.'</h3>';

        $tmp = \str_replace('App\Http\Controllers\\', '', $tmp);
        $tmp = \str_replace('\\', '.', $tmp);
        $tmp = \str_replace('Controller@', '.', $tmp);
        //echo '<h3>['.__LINE__.']'.$tmp.'</h3>';
        //$tmp = \mb_strtolower($tmp);
        //$tmp = snake_case($tmp);

        if ('.' == $tmp[0]) {
            $tmp = \mb_substr($tmp, 1);
        }
        //echo '<h3>'.$tmp.'</h3>';
        $tmp = \explode('.', $tmp);
        $pack = $tmp[1];
        $path = \array_slice($tmp, 3);
        if (isset($path[1]) && $path[1] == $pack && 'Admin' == $path[0]) {
            unset($path[1]);
        } elseif (isset($path[0]) && $path[0] == $pack) {
            unset($path[0]);
        }
        $path=collect($path)->map(function($item){
            return Str::snake($item);
        })->all();


        $view = strtolower($pack).'::'.\implode('.', $path);
       
        /*
        if($pack=='frontend'){
            $pack='pub_theme';
        }
        */
        //$view = $pack.'::'.$view;
        $str1 = '.pdfszip';
        if (\mb_substr($view, -\mb_strlen($str1)) == $str1) {
            $view = \mb_substr($view, 0, -\mb_strlen($str1)).'.pdf.row';
        }

        $str2 = '.pdfrow';
        if (\mb_substr($view, -\mb_strlen($str2)) == $str2) {
            $view = \mb_substr($view, 0, -\mb_strlen($str2)).'.pdf.row';
        }
        /* //-- cambiato route
        if(isset($container1)){
            $view=str_replace('::container.','::container.container1.',$view);
            $view=str_replace('::admin.container.','::admin.container.container1.',$view);
        }
        if(isset($container2)){
            $view=str_replace('::container.container1.','::container.container1.container2.',$view);
            $view=str_replace('::admin.container.container1.','::admin.container.container1.container2.',$view);
        }
        */
        if (false === \mb_strpos($view, '::admin.')) {
            /*
            for($i=0;$i<5;$i++){
                $cont_name='container';
                if($i>0){
                    $cont_name.=''.$i;
                }
                if(isset($$cont_name)){
                    $cont=$$cont_name;
                    $view=str_replace('::'.$cont_name.'.','::'.$cont->post_type.'.',$view);
                }
            }
            //*/
            //*
            if (isset($container0)) {
                $view = \str_replace('::container0.', '::'.$container0->post_type.'.', $view);
            }
            if (isset($container1)) {
                $cont1_type = \is_object($container1) ? $container1->post_type : $container1;
                //$cont1_type=(string)$container1;
                $view = \str_replace('.container1.', '.'.$cont1_type.'.', $view);
            }
            if (isset($container2)) {
                $view = \str_replace('.container2.', '.'.$container2->post_type.'.', $view);
            }
            if (isset($container3)) {
                $view = \str_replace('.container3.', '.'.$container3->post_type.'.', $view);
            }
            //*/
            $view = \str_replace('blog::', 'pub_theme::', $view);
        }

        /* -- DA SISTEMARE ADMIN !! --*/
        /*
        if(isset($container)){
            if(is_object($container)){ $container_type=$container0->post_type;
            }else{ $container_type=$container; }
            if(isset($container1)){
                if(isset($container2)){
                    if(is_object($container2)){ $container2_type=$container2->post_type;
                    }else{ $container2_type=$container2; }
                    dd($view);
                    $view=str_replace('::container.','::'.$container_type.'.'.$container1.'.'.$container2.'.',$view);
                    $view=str_replace('::admin.container.container1.','::admin.container.container1.container2.',$view);

                    //$view=str_replace('::admin.container.','::admin.'.$container_type.'.'.$container1_type.'.',$view);
                }
                if(is_object($container1)){ $container1_type=$container1->post_type;
                }else{ $container1_type=$container1; }
                $view=str_replace('::container.','::'.$container_type.'.'.$container1_type.'.',$view);
                $view=str_replace('::admin.container.','::admin.container.container1.',$view);
            }

            $view=str_replace('::container.','::'.$container_type.'.',$view);
            //$view=str_replace('.container.','.'.$container_type.'.',$view);

            if(strpos($view,'::admin.')===false){
                $view=str_replace('blog::','pub_theme::',$view);
            }
        }
        */

        if (\Request::ajax()) {
            $view .= '_ajax';
        }
        return $view;



        if (\View::exists($view)) {
            return $view;
        } else {
            $routename = \Route::current()->getName();
            $routename_arr=explode('.',$routename);
            $act=last($routename_arr);
            
            $view1='pub_theme::layouts.default.'.$act;  //magari fare un check if in_admin
            if(!\View::exists($view1)){
                echo '<h3>'.\Route::currentRouteAction().'</h3>';
                //ddd(ThemeService::view_path($view));
                $msg = '';
                $msg .= \chr(13).\chr(10).'<h3>la view ['.$view.'] non esiste <br/>';
                $msg .= \chr(13).\chr(10).'pub_theme= '.config('xra.pub_theme').'</h3>';
                $msg .= \chr(13).\chr(10).'['.__LINE__.']['.__FILE__.']';
                /* -- for debug --
                $hints=\View::getFinder()->getHints();
                $pub_theme_index=$hints['pub_theme'][0].DIRECTORY_SEPARATOR.'index.blade.php';
                ddd($pub_theme_index.':'.file_exists($pub_theme_index));
                */
                die($msg);
            }
            return $view1;
        }
    }

    //end getView

    public static function view($view=null){
        \Debugbar::startMeasure('render','Time for rendering');
        $params = \Route::current()->parameters();
        $routename = \Route::current()->getName();
        $routename_arr=explode('.',$routename);
        $act=last($routename_arr);
        if(\in_admin()){
            $view_default='adm_theme::layouts.default.'.$act;
        }else{
            $view_default='pub_theme::layouts.default.'.$act; //pub_theme o extend ?
        }
        $view_extend='extend::layouts.default.'.$act;
        //---------------------------------------------------------------------------
        if (\Request::ajax()) {
            $view_default .= '_ajax';
            $view_extend  .='_ajax';
        }
        $use_default=false;
        if($view==null){
            $view = self::getView($params);
        }
        //ddd($view);
        /*
        if (!\View::exists($view)) {
            if(!\View::exists($view_default)){
                echo('<h3>view ['.$view.'] and ['.$view_default.'] not esists</h3>['.__LINE__.']['.__FILE__.']');
                echo '<h3> pub_theme ['.config('xra.pub_theme').']</h3>';
                ddd('add missing template');  
            }
            $use_default=true;
        }
        */
        if (\View::exists($view)) {
            $view_work=$view;
        }elseif(\View::exists($view_default)){
            $view_work=$view_default; 
        }elseif(\View::exists($view_extend)){
            $view_work=$view_extend;
        }else{
            echo('<h3>view ['.$view.'] and ['.$view_default.'] and ['.$view_extend.'] not esists</h3>['.__LINE__.']['.__FILE__.']');
            echo '<h3> pub_theme ['.config('xra.pub_theme').']</h3>';
            ddd('add missing template');  
        }

        $row = last($params);
        $n_params=count($params);
        if($n_params>1){
            $second_last = collect(\array_slice($params, -2))->first(); //penultimo 
        }else{
            $second_last = null;
        }
        self::setMetatags($row);
        $routename = \Route::current()->getName();
        $lang = \App::getLocale();
        /*
        if($use_default){
            $view_work=$view_default;
        }else{
            $view_work=$view;
        }
        */


        $theView = view($view_work)
            ->with('row', $row)  //dipende dal tipo di crud
            ->with('second_last',$second_last)
            ->with('lang', $lang)
            ->with('params', $params)
            ->with($params)
            //->with($roots)  // dipende dal tipo di crud
            ->with('view', $view)
            ->with('view_default',$view_default)
            ->with('view_extend',$view_extend)
            ->with('routename', $routename);
        $view_params = self::__getStatic('view_params');
        foreach ($view_params as $key => $value) {
            $theView->with($key, $value);
        }
        \Debugbar::stopMeasure('render');
        return $theView;
    }

    public static function action(Request $request,$row){
        //echo '<pre>';print_r($routes);echo '</pre>';
        $routename = \Route::current()->getName();
        $rotename_arr=explode('.',$routename);
        $rotename_arr=array_slice($rotename_arr,0,-1);
        $routename_base=implode('.',$rotename_arr);
        //ddd($rotename_arr);
        $params = \Route::current()->parameters();
        $data = $request->all();
        if(!isset($data['_action'])){
            $data['_action']='save_close';
        }
        switch ($data['_action']) {
            case 'save_continue':
                //$this->routes->getByName($name)
                $routes=app('router')->getRoutes();
                $routename = $routename_base.'.edit';
                $route_info=$routes->getByName($routename);
                $pattern='/\{([^}]+)\}/';
                preg_match_all($pattern,$route_info->uri,$matches);
                $parz=[];
                //ddd($row->guid); //deve esserci, controllare che nel modello non ci sia function getGuidAttribute, e che ci sia
                //  linkedTrait 
                foreach($matches[1] as $match){
                    if(isset($params[$match])){
                        $parz[$match]=$params[$match];
                    }else{
                        $parz[$match]=$row;
                    }
                }
                return redirect()->route($routename, $parz);
                break;
            case 'save_close': 
                $routename = $routename_base.'.index';
                if(\Route::has($routename.'_edit')){
                    return redirect()->route($routename.'_edit', $params);    
                }
                return redirect()->route($routename, $params);
                break;
            case 'come_back':
                return redirect()->back();
                break;
            case 'row':
                return $row;
                break;
            default:
                echo '<h3>['.__LINE__.']['.__FILE__.']</h3>';
                ddd($data['_action']);
                break;
        }//end switch
    }//end function
    
    public static function cache(/*ViewContract $vc,*/$view,$data=[], $mergeData=[]){
        //scopiazzato da spatie partialcache
        $cache_key=str_slug($view).'-'.md5(json_encode($data));
        $data['lang']=\App::getLocale();
        $seconds=60*60*24;
        try{
            $html= Cache::/*store('apc')->*/remember($cache_key, $seconds, function () use($view, $data, $mergeData){
                return (string)\View::make($view, $data, $mergeData)->render();
                //return (string)self::view($view);
            });
        }catch(\Exception $e){
            ArtisanService::exe('cache:clear');
            $html=(string)\View::make($view, $data, $mergeData)->render();
        }
        return $html;
    } 


    public static function imageSrc($params){
        extract($params);
        $path=self::asset($path);
        return $path; // ci mette troppo nel server 
        //ddd($path);
        $parz=['src'=>$path,'height'=>$height,'width'=>$width];
        $img=new ImageService($parz);
        $ris= $img->fit()->save()->src();
        $ris=str_replace('\\','/',$ris);
        $ris=str_replace('//','/',$ris);
        return asset($ris);
    }

}//end class
