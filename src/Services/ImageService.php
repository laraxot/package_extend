<?php
namespace XRA\Extend\Services;

use Illuminate\Support\Str;
use Cache;
use ImageOptimizer;
use Intervention\Image\Facades\Image;

//---- services ----

class ImageService
{
    private static $_instance = null;
    static $img=null,$width,$height,$src,$filename;


    public function __construct($params=[]){
        $this->init($params);
    }

    //---- setter
    public static function init($params){
        foreach($params as $k=>$v){
            $func='set'.Str::studly($k);
            self::$func($v);
        }
    }

    public static function setImg($val){
        self::$img = Image::make($val);
    }

    public static function setWxh($val){
        list($w, $h) = \explode('x', $val);
        self::setWidth($w);
        self::setHeight($h);
        
    }

    public static function setWidth($val){
        self::$width=$val;
    }

    public static function setHeight($val){
        self::$height=$val;
    }
    
     public static function setSrc($val){
        if(starts_with($val,url(''))){ //se e' una immagine locale
            $val=public_path(\substr($val,strlen(url(''))));
        }
        self::$src=$val;
        self::setImg($val);
    }
    //----------

    public static function getInstance($params=[]){
        if (null === self::$_instance) {
            self::$_instance = new self($params);
        }

        return self::$_instance;
    }

    public static function toHtml()
    {
    }

    public static function image_resized_cropped($params)
    {
        \extract($params);
        $pathinfo = \pathinfo($image_path);
        //$image_resized='assets_packs/img/'.$width.'x'.$height.'/'.basename($image_path);
        if (null == $image_path) {
            return 'assets_packs/img/'.$width.'x'.$height.'/nophoto.png';
        }
        $image_resized = 'assets_packs/img/'.$width.'x'.$height.'/'.$pathinfo['filename'].'.jpg';
        if (\File::exists(public_path($image_resized))) {
            return $image_resized;
        } //immagine la creo 1 volta sola
        if ('//' == \mb_substr($image_path, 0, 2)) {
            $image_path = 'http:'.$image_path;
        }

        if (!\File::exists($image_path)) {
            

            //return false;
        }

        $allowedMimeTypes = ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/svg+xml'];
        $allowedExtensions = ['jpg', 'png', 'gif'];
        if (!isset($pathinfo['extension'])) {
            return $image_path;
        }
        if (!\in_array($pathinfo['extension'], $allowedExtensions, true)) {
            return $image_path;
        }
        //if(!in_array($contentType,$allowedMimeTypes)) return $image_path;

        //return $image_path;

        $img = Image::make($image_path);

        $img->resize($width, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        if ($img->height() > $height) {
            /* //voglio croppare l'immagine per non lasciare bordi brutti
            $img->resize(null, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
            */
            $x0 = 0;
            $y0 = \rand(0, $img->height() - $height);
            $img->crop($width, $height, $x0, $y0);
        }
        $w0 = $img->width();
        $h0 = $img->height();
        $canvas = Image::canvas($width, $height, '#fdfdfd');
        $x = \round(($width - $w0) / 2, 0);
        $y = \round(($height - $h0) / 2, 0);
        $canvas->insert($img, null, $x, $y);

        \File::makeDirectory(\dirname(public_path($image_resized)), 0775, true, true);
        $canvas->save(public_path($image_resized), 75);
        ImageOptimizer::optimize(public_path($image_resized));
        //app(Spatie\ImageOptimizer\OptimizerChain::class)->optimize(public_path($image_resized));
        return $image_resized;
    }

    


    public static function crop($params=[]){
        $me=self::getInstance($params);
        
        $img    =self::$img;
        $width  =self::$width;
        $height =self::$height;
        

        if($width>$height){

            $img->resize($width, null, function ($constraint) {
                $constraint->aspectRatio();
            });

            if ($img->height() > $height) {
                /* //voglio croppare l'immagine per non lasciare bordi brutti
                    $img->resize(null, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });
                */
                $x0 = 0;
                //$y0 = \rand(0, $img->height() - $height);
                $y0 = round(($img->height() + $height)/2,0);
                $img->crop($width, $height, $x0, $y0);
            }
        }else{
            $img->resize(null,$height, function ($constraint) {
                $constraint->aspectRatio();
            });

            if ($img->width() > $width) {
                /* //voglio croppare l'immagine per non lasciare bordi brutti
                    $img->resize(null, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });
                */
                //$x0 = \rand(0, $img->width() - $width);
                $x0 = round(($img->width() + $width)/2,0);
                $y0 = 0;
                $img->crop($width, $height, $x0, $y0);
            }
        }
        $w0 = $img->width();
        $h0 = $img->height();
        $canvas = Image::canvas($width, $height, '#fdfdfd');
        $x = \round(($width - $w0) / 2, 0);
        $y = \round(($height - $h0) / 2, 0);
        $canvas->insert($img, null, $x, $y);
        self::$img=$canvas;
        return self::getInstance(); /// per il fluent, o chaining
    }

    public static function save($params=[]){
        //extract($params);
        self::$filename=public_path('/imgz/'.self::$width.'x'.self::$height.'/'.basename(self::$src));
        \File::makeDirectory(\dirname(self::$filename), 0775, true, true);
        $r=self::$img->save(self::$filename, 75);
        return self::getInstance();
    }

    public static function out($params=[]){
        return self::$img->encode('jpg', 60);
    }


    public static function image_resized_canvas($params)
    {
        \extract($params);
        $pathinfo = \pathinfo($image_path);
        //$image_resized='assets_packs/img/'.$width.'x'.$height.'/'.basename($image_path);
        if (null == $image_path) {
            //return 'assets_packs/img/'.$width.'x'.$height.'/nophoto.png';
            $image_path = public_path('images/nophoto.png');
        }
        $image_resized = 'assets_packs/img/'.$width.'x'.$height.'/'.$pathinfo['filename'].'.jpg';
        //if(\File::exists(public_path($image_resized))) return $image_resized; //immagine la creo 1 volta sola
        if ('//' == \mb_substr($image_path, 0, 2)) {
            $image_path = 'http:'.$image_path;
        }
        $allowedMimeTypes = ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/svg+xml'];
        $allowedExtensions = ['jpg', 'png', 'gif'];
        if (!isset($pathinfo['extension'])) {
            $pathinfo['extension'] = '';
        }
        $pos = \mb_strpos($pathinfo['extension'], '&');
        if (false !== $pos) {
            $pathinfo['extension'] = \mb_substr($pathinfo['extension'], 0, $pos);
        }

        if (!isset($pathinfo['extension'])) {
            return $image_path;
        }
        if (!\in_array($pathinfo['extension'], $allowedExtensions, true)) {
            return $image_path;
        }
        //if(!in_array($contentType,$allowedMimeTypes)) return $image_path;
        //* -- spostato in importtrait
        $str0 = 'https://s2.qwant.com'; //questo fa un redirect
        $str1 = 'http://s2.qwant.com';
        $str2 = 'https://s2.qwant.com'; //questo fa un redirect
        $str3 = 'http://s2.qwant.com';
        if (\mb_substr($image_path, 0, \mb_strlen($str0)) == $str0 || \mb_substr($image_path, 0, \mb_strlen($str1)) == $str1 ||
           \mb_substr($image_path, 0, \mb_strlen($str2)) == $str2 || \mb_substr($image_path, 0, \mb_strlen($str3)) == $str3) {
            $pos = \mb_strpos($image_path, '?');
            $image_path1 = \mb_substr($image_path, $pos + 1);
            \parse_str($image_path1, $output);
            //echo '<h3>'.$image_path1.'</h3>';
            $image_path = \urldecode($output['u']);
        }
        //*/
        $str = '/laravel-filemanager/';
        if (\mb_substr($image_path, 0, \mb_strlen($str)) == $str) {
            $image_path = public_path(\mb_substr($image_path, \mb_strlen($str)));
            //die($image_path);
        }
        if (starts_with($image_path, '/')) {
            if (!starts_with($image_path, public_path('/'))) {
                $image_path = public_path($image_path);
            }
        }
        //die($image_path);

        //return $image_path;
        try {
            $img = Image::make($image_path);
        } catch (\Intervention\Image\Exception\NotReadableException $e) {
            $filename = str_slug(\basename($image_path)).'.'.$pathinfo['extension'];
            /*
            if(!\Storage::disk('cache')->exists($filename)){
                \Storage::disk('cache')->put($filename, fopen($image_path, 'r'));
            }

            */
            //$asset = new FileAsset();

            //$jpg=imagecreatefromjpeg($image_path);

            //die('['.__LINE__.']['.__FILE__.']');
            $img_content = ImportService::cacheRequestFile('GET', $image_path);
            \Storage::disk('cache')->put($filename, $img_content);
            try {
                $img = Image::make(\Storage::disk('cache')->path($filename));
            } catch (\Intervention\Image\Exception\NotReadableException $e) {
                //echo '<h3>['.__LINE__.']['.__FILE__.']</h3>';
                //$this->image_src=''; //-- meglio non cancellare..
                //$this->image_resize_src=[];
                //$this->save();
                //ddd('non dovrei essere qui');
                return null;
            }
            //$img_content=base64_encode($img_content);
            //$im = imagecreatefromstring($img_content);

            //$img=Image::make($img_content);
            //*
            //echo '<h3>['.__LINE__.']['.__FILE__.']</h3>';
            //return ;
            //echo $content;

            //*/
        }

        $img->resize($width, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        if ($img->height() > $height) {
            $img->resize(null, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
        }

        $w0 = $img->width();
        $h0 = $img->height();
        $canvas = Image::canvas($width, $height, '#fdfdfd');
        $x = \round(($width - $w0) / 2, 0);
        $y = \round(($height - $h0) / 2, 0);
        $canvas->insert($img, null, $x, $y);

        \File::makeDirectory(\dirname(public_path($image_resized)), 0775, true, true);
        $canvas->save(public_path($image_resized), 75);
        //ImageOptimizer::optimize(public_path($image_resized));   //--- CAPIRE SE NE VALE LA PENA
        //app(Spatie\ImageOptimizer\OptimizerChain::class)->optimize(public_path($image_resized));
        return $image_resized;
    }
}
