<?php



class IMG_OP extends baseController
{
    public function prova($params)
    {
        echo '<h3>PROVA</h3>';
    }

    //-------------------------------
    /**
     * Set the image variable by using image create.
     *
     * @param string $filename - The image filename
     */
    //private
    public function setImage($filename)
    {
        if (\is_file($_SERVER['DOCUMENT_ROOT'].$filename)) {
            $filename = $_SERVER['DOCUMENT_ROOT'].$filename;
        }

        $size = \getimagesize($filename);
        //ddd($size);
        //die();
        $this->ext = $size['mime'];

        switch ($this->ext) {
            // Image is a JPG
            case 'image/jpg':
            case 'image/jpeg':
                // create a jpeg extension
                $this->image = \imagecreatefromjpeg($filename);
                break;

            // Image is a GIF
            case 'image/gif':
                $this->image = @\imagecreatefromgif($filename);
                break;

            // Image is a PNG
            case 'image/png':
                $this->image = @\imagecreatefrompng($filename);
                break;

            // Mime type not found
            default:
                throw new Exception('File is not an image, please use another file type.', 1);
        }

        $this->origWidth = \imagesx($this->image);
        $this->origHeight = \imagesy($this->image);
    }

    /**
     * Resize the image to these set dimensions.
     *
     * @param int    $width        - Max width of the image
     * @param int    $height       - Max height of the image
     * @param string $resizeOption - Scale option for the image
     *
     * @return Save new image
     */
    public function resizeTo($width, $height, $resizeOption = 'default')
    {
        switch (\mb_strtolower($resizeOption)) {
            case 'exact':
                $this->resizeWidth = $width;
                $this->resizeHeight = $height;
            break;

            case 'maxwidth':
                $this->resizeWidth = $width;
                $this->resizeHeight = $this->resizeHeightByWidth($width);
            break;

            case 'maxheight':
                $this->resizeWidth = $this->resizeWidthByHeight($height);
                $this->resizeHeight = $height;
            break;
            case 'crop':

                $deltax = $width / $this->origWidth;
                $deltay = $height / $this->origHeight;
                //echo $deltax.';'.$deltay;
                if ($deltax > $deltay) {
                    $delta = $deltax;
                } else {
                    $delta = $deltay;
                }
                $w1 = $this->origWidth * $delta;
                $h1 = $this->origHeight * $delta;
                $this->newImage = \imagecreatetruecolor($w1, $h1);
                \imagecopyresampled($this->newImage, $this->image, 0, 0, 0, 0, $w1, $h1, $this->origWidth, $this->origHeight);
                //echo '<br/>'.$w1.'   ;  '.$h1;
                //echo '<br/>'.$width.'   ;  '.$height;
                //ddd('qui');
                $to_crop_array = ['x' => \rand(0, $w1 - $width), 'y' => \rand(0, $h1 - $height), 'width' => $width, 'height' => $height];
                $this->newImage = \imagecrop($this->newImage, $to_crop_array);
                //ddd('qui');

                return;

            break;
            default:
                if ($this->origWidth > $width || $this->origHeight > $height) {
                    if ($this->origWidth > $this->origHeight) {
                        $this->resizeHeight = $this->resizeHeightByWidth($width);
                        $this->resizeWidth = $width;
                    } elseif ($this->origWidth < $this->origHeight) {
                        $this->resizeWidth = $this->resizeWidthByHeight($height);
                        $this->resizeHeight = $height;
                    } else {
                        $this->resizeWidth = $width;
                        $this->resizeHeight = $height;
                    }
                } else {
                    $this->resizeWidth = $width;
                    $this->resizeHeight = $height;
                }
            break;
        }

        $this->newImage = \imagecreatetruecolor($this->resizeWidth, $this->resizeHeight);
        \imagecopyresampled($this->newImage, $this->image, 0, 0, 0, 0, $this->resizeWidth, $this->resizeHeight, $this->origWidth, $this->origHeight);
    }

    /**
     * Get the resized height from the width keeping the aspect ratio.
     *
     * @param int $width - Max image width
     *
     * @return Height keeping aspect ratio
     */
    private function resizeHeightByWidth($width)
    {
        return \floor(($this->origHeight / $this->origWidth) * $width);
    }

    /**
     * Get the resized width from the height keeping the aspect ratio.
     *
     * @param int $height - Max image height
     *
     * @return Width keeping aspect ratio
     */
    private function resizeWidthByHeight($height)
    {
        return \floor(($this->origWidth / $this->origHeight) * $height);
    }

    //private
    public function show()
    {
        \header('Content-Type: image/jpeg');
        \imagejpeg($this->newImage, null, \rand(35, 76));
    }

    /**
     * Save the image as the image type the original image was.
     *
     * @param String[type] $savePath     - The path to store the new image
     * @param string       $imageQuality - The qulaity level of image to create
     *
     * @return Saves the image
     */
    public function saveImage($savePath, $imageQuality = '100', $download = false)
    {
        switch ($this->ext) {
            case 'image/jpg':
            case 'image/jpeg':
                // Check PHP supports this file type
                if (\imagetypes() & IMG_JPG) {
                    \imagejpeg($this->newImage, $savePath, $imageQuality);
                }
                break;

            case 'image/gif':
                // Check PHP supports this file type
                if (\imagetypes() & IMG_GIF) {
                    \imagegif($this->newImage, $savePath);
                }
                break;

            case 'image/png':
                $invertScaleQuality = 9 - \round(($imageQuality / 100) * 9);

                // Check PHP supports this file type
                if (\imagetypes() & IMG_PNG) {
                    \imagepng($this->newImage, $savePath, $invertScaleQuality);
                }
                break;
        }

        if ($download) {
            \header('Content-Description: File Transfer');
            \header('Content-type: application/octet-stream');
            \header('Content-disposition: attachment; filename= '.$savePath.'');
            \readfile($savePath);
        }

        \imagedestroy($this->newImage);
    }

    //--------------------------------------------------

    //---------------------------------------------------
    public function sistemaImgNome($img)
    {
        $path_parts = \pathinfo($img);
        //echo '<pre>[server]'; print_r($_SERVER); echo '[/server]</pre>';
        $newname = STRING_OP::slugify($path_parts['filename']).'.'.\mb_strtolower($path_parts['extension']);

        return $newname;
    }

    ///---------------------------------------------------
    public function imgFromServerToLocal($params)
    {
        \extract($params);
        //$im = imagecreatefromjpeg($urlimg);
        $info = \pathinfo($urlimg);
        //ARRAY_OP::print_x($info);
        $params['host'] = 'http:'.$info['dirname'].'/';
        $params['href'] = $info['basename'];

        $ris = getpage($params);

        //ARRAY_OP::print_x($ris);
        FILE_OP::writeFile('pippo.jpg', $ris['html'], true);
    }

    //---------------------------------------------------
    public function imgServer2Local($img)
    {
        global $agenzie;
        $min_width = 600;
        //$path_parts = pathinfo($img);
        //echo '<pre>[server]'; print_r($_SERVER); echo '[/server]</pre>';
        //$newname = STRING_OP::slugify($path_parts['filename']).'.'.strtolower($path_parts['extension']);
        $newname = sistemaImgNome($img);
        $from = \str_replace(' ', '%20', $img);
        $to = __DIR__.'/imgz/'.$agenzie['nome'].'/'.$newname;
        $to = \str_replace('\\', '/', $to);
        $myurl = \str_replace($_SERVER['DOCUMENT_ROOT'], $_SERVER['SERVER_NAME'], __DIR__);

        if (!\is_file($to)) {
            if (url_exists($from)) {
                //copy($from,$to);
                list($img0->width, $img0->height) = \getimagesize($img);
                if ($img0->width < $min_width) {
                    $scale = $min_width / $img0->width;
                } else {
                    $scale = 1;
                }
                $img1->width = $img0->width * $scale;
                $img1->height = $img0->height * $scale;
                $image = \imagecreatefromjpeg($img);
                $newimage = \imagecreatetruecolor($img1->width, $img1->height);
                \imageantialias($newimage, true);
                \imagealphablending($newimage, true);
                //imagecopyresized($newimage, $image, 0, 0, 0, 0, $img1->width, $img1->height, $img0->width, $img0->height);
                \imagecopyresampled($newimage, $image, 0, 0, 0, 0, $img1->width, $img1->height, $img0->width, $img0->height);
                \imagejpeg($newimage, $to, 90);
            } else {
                return 'http://'.$myurl.'/imgz/non_disponibile.jpg';
            }
        } else {
            echo '<br/>Esiste gia '.$from.'  '.$to;
        }
        $url_new = \str_replace($_SERVER['DOCUMENT_ROOT'], $_SERVER['SERVER_NAME'].'/', $to);
        $url_new = \str_replace('//', '/', $url_new);
        $url_new = 'http://'.$url_new;

        return $url_new;
    }

    //---------------------------------------------------

    public function banner1($params)
    {
        $w = 400;
        $h = 30;
        $fsize = 25;
        $fangle = 0;
        $fx = 11;
        $fy = 21;
        // The text to draw
        $text = 'Testing...';
        \extract($params);
        // Set the content-type
        \header('Content-Type: image/jpeg');

        // Create the image
        $im = \imagecreatetruecolor($w, $h);

        // Create some colors
        $white = \imagecolorallocate($im, 255, 255, 255);
        $grey = \imagecolorallocate($im, 128, 0, 0);
        $black = \imagecolorallocate($im, 0, 0, 0);
        \imagefilledrectangle($im, 0, 0, $w, $h, $white);

        // Replace path by your own font path
        $font = 'c:/xampp/htdocs/fonts/FFF_Tusj.ttf';

        // Add some shadow to the text
        \imagettftext($im, $fsize, 0, $fx, $h * 0.9, $grey, $font, $text);

        // Add the text
        //imagettftext($im, $fsize, 0, 10, 20, $black, $font, $text);

        // Using imagepng() results in clearer text compared with imagejpeg()
        \imagejpeg($im);
        //imagepng($im);
        \imagedestroy($im);
    }

    public function banner($params)
    {
        // Create a 300x150 image
        // Path to our font file
        /*
$font_array=array('FFF_Tusj.ttf','1-26d83.ttf','Austie Bost Wibbly.ttf','Bastarda.ttf','bigdog.ttf','calligra.ttf','Chuckle  Normal.ttf','cour.ttf','DancingScript-Regular.ttf','FantaisieArtistique.ttf',
        'Funny_Face.ttf','Luna.ttf','Pacifico.ttf','Peas & Carrots.ttf','PLATSCH2.TTF','sanremo.ttf','SEASRN__.ttf','Sketch Match.ttf','True Lies.ttf');
        */
        //$font_array=array('True Lies.ttf');
        $fonts_dir = $_SERVER['DOCUMENT_ROOT'].'/fonts/';
        $font_array = FILE_OP::getArrayFileDir(['dir' => $fonts_dir, 'ext' => 'ttf']);
        //ddd($font_array);

        $fsize = 20;
        $marginx = 10;
        $marginy = 5;
        $text = 'Powered by PHP '.PHP_VERSION;
        \extract($params);
        //$font = 'c:/xampp/htdocs/fonts/FFF_Tusj.ttf';
        $font = $fonts_dir.$font_array[\rand(1, \count($font_array)) - 1];
        // First we create our bounding box for the first text
        $box = \imagettfbbox($fsize, 0, $font, $text);
        //echo '<h3>'.$font.'</h3>';ddd('qui');
        /* ARRAY_OP::print_x($box);
         [0] => -1
         [1] => 14
            [2] => 652
            [3] => 14
            [4] => 652
            [5] => -50
            [6] => -1
            [7] => -50
            */

        $fw = \abs($box[4] - $box[0]) + ($marginx * 2);
        $fh = \abs($box[5] - $box[1]) + ($marginy * 2);

        $deltaw = $params['w'] / $fw;
        /*
        echo '<br/>'.$deltaw;
        echo '<br/>'.$params['w'];
        echo '<br/>'.$fw;
        //*/

        if ($deltaw > 1.05 || $deltaw < 0.95) {
            $params['fsize'] = $fsize * $deltaw;

            return self::banner($params);
        }

        /*
        if(isset($params['w']) && $fw>$params['w']){
            $params['fsize']=$fsize-0.5;
            return banner($params);
        }

        if(isset($params['w']) && $fw<$params['w']*0.7){
            $params['fsize']=$fsize+0.5;
            return banner($params);
        }
        */

        $im = \imagecreatetruecolor($fw, $fh);
        $black = \imagecolorallocate($im, 0, 0, 0);
        $white = \imagecolorallocate($im, 64, 164, 64);
        $transparent = \imagecolorallocatealpha($im, 0, 0, 0, 127);
        //imageSTRING_OP::fill( $im, 0, 0, $transparent );
        \imagefill($im, 0, 0, $transparent);

        // Set the background to be white
        //imagefilledrectangle($im, 0, 0, $w, $h, $black);

        $x = 0 + $box[0] + $marginx;
        $y = $fh - $box[1] - $marginy;
        //$y =$h+(($box[5] + $box[1])/2);

        // Write it
        \imagettftext($im, $fsize, 0, $x, $y, $white, $font, $text);

        // Create the next bounding box for the second text
        //*
        // Output to browser
        \header('Content-Type: image/png');
        // Turn off alpha blending and set alpha flag
        \imagealphablending($im, true);
        \imagesavealpha($im, true);

        // Make the background transparent
        //imagecolortransparent($im, $black);

        \imagepng($im);
        \imagedestroy($im);
        //*/
    }

    //------------//-----------------//-------------------
//------------
}//end class

class GetImage
{
    public $source;
    public $save_to;
    public $set_extension;
    public $quality;

    public function download($method = 'curl')
    {// default method: cURL
        $info = @\getimagesize($this->source);
        $mime = $info['mime'];

        // What sort of image?
        $type = \mb_substr(\mb_strrchr($mime, '/'), 1);

        switch ($type) {
    case 'jpeg':
        $image_create_func = 'ImageCreateFromJPEG';
        $image_save_func = 'ImageJPEG';
        $new_image_ext = 'jpg';

        // Best Quality: 100
        $quality = isset($this->quality) ? $this->quality : 100;
    break;

    case 'png':
        $image_create_func = 'ImageCreateFromPNG';
        $image_save_func = 'ImagePNG';
        $new_image_ext = 'png';

        // Compression Level: from 0  (no compression) to 9
        $quality = isset($this->quality) ? $this->quality : 0;
    break;

    case 'bmp':
        $image_create_func = 'ImageCreateFromBMP';
        $image_save_func = 'ImageBMP';
        $new_image_ext = 'bmp';
    break;

    case 'gif':
        $image_create_func = 'ImageCreateFromGIF';
        $image_save_func = 'ImageGIF';
        $new_image_ext = 'gif';
    break;

    case 'vnd.wap.wbmp':
        $image_create_func = 'ImageCreateFromWBMP';
        $image_save_func = 'ImageWBMP';
        $new_image_ext = 'bmp';
    break;

    case 'xbm':
        $image_create_func = 'ImageCreateFromXBM';
        $image_save_func = 'ImageXBM';
        $new_image_ext = 'xbm';
    break;

    default:
        $image_create_func = 'ImageCreateFromJPEG';
        $image_save_func = 'ImageJPEG';
        $new_image_ext = 'jpg';
    }

        if (isset($this->set_extension)) {
            $ext = \mb_strrchr($this->source, '.');
            $strlen = \mb_strlen($ext);
            $new_name = \basename(\mb_substr($this->source, 0, -$strlen)).'.'.$new_image_ext;
        } else {
            $new_name = \basename($this->source);
        }

        $save_to = $this->save_to.$new_name;

        if ('curl' == $method) {
            $save_image = $this->LoadImageCURL($save_to);
        } elseif ('gd' == $method) {
            $img = $image_create_func($this->source);

            if (isset($quality)) {
                $save_image = $image_save_func($img, $save_to, $quality);
            } else {
                $save_image = $image_save_func($img, $save_to);
            }
        }

        return $save_image;
    }

    public function LoadImageCURL($save_to)
    {
        $ch = \curl_init($this->source);
        $fp = \fopen($save_to, 'w');

        // set URL and other appropriate options
        $options = [CURLOPT_FILE => $fp,
                 CURLOPT_HEADER => 0,
                 CURLOPT_FOLLOWLOCATION => 1,
                 CURLOPT_TIMEOUT => 60, ]; // 1 minute timeout (should be enough)

        \curl_setopt_array($ch, $options);

        \curl_exec($ch);
        \curl_close($ch);
        \fclose($fp);
    }

    /*
    function setImage($params){
        echo '<h3>aa</h3>';
    }

    */

//-------------------------------
}//end class
