<?php
namespace XRA\Extend\Controllers;

use App\Http\Controllers\Controller;
use File;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use XRA\Extend\Services\ThemeService;

class ImgzController extends Controller
{
    public function ttf_rand()
    {
        $fonts_dir = ThemeService::view_path('extend::fonts');
        $files = File::files($fonts_dir); //->where('extension','ttf');
        $ttfz = [];
        foreach ($files as $file) {
            if ('ttf' == \mb_strtolower($file->getExtension())) {
                $ttfz[] = $file->getPathname();
            }
        }
        $ttf = $ttfz[\rand(0, \count($ttfz) - 1)];

        return $ttf;
    }

    public function banner(Request $request)
    {
        $ttf = $this->ttf_rand();
        //dd($ttf);
        $params = \Route::current()->parameters();
        \extract($params);
        list($w, $h) = \explode('x', $wxh);
        $img = Image::canvas($w, $h);
        $font_size = 224;
        // use callback to define details
        $img->text($txt, $w / 2, $h / 2, function ($font) use ($ttf,$font_size,$w,$h,$txt,$img) {
            $font->file($ttf);
            //$font_size = 20;
            $font->size($font_size);
            $box_size = $font->getBoxSize();
            $image_width = $w;
            $larger = $box_size['width'] > $image_width;
            while (($larger && $box_size['width'] > $image_width) || (!$larger && $box_size['width'] < $image_width)) {
                if ($larger) {
                    --$font_size;
                } else {
                    ++$font_size;
                }
                $font->size($font_size);
                $box_size = $font->getBoxSize();
            }

            $image_height = $h;
            while ($box_size['height'] > $image_height) {
                --$font_size;
                $font->size($font_size);
                $box_size = $font->getBoxSize();
            }

            $font->color('#000070');
            $font->align('center');
            $font->valign('middle');
            //$font->angle(45);
            $img->text($txt, $w / 2 + 1, $h / 2 + 2, function ($font) use ($ttf,$font_size) {
                $font->file($ttf);
                $font->size($font_size);
                $font->color('#ddd');
                $font->align('center');
                $font->valign('middle');
            });
        });

        /*
        $img->text($txt, $w/2+2, $h/2+2, function($font) use($ttf,$font_size){
            $font->file($ttf);
            $font->size($font_size);
            $font->color('#d60021');
            $font->align('center');
            $font->valign('top');
            //$font->angle(45);
        });
        */
        //$img->save(public_path('prova.jpg'));

        return response($img->stream('png', 20))->header('Content-type', 'image/png');
    }

    public function canvas(Request $request)
    {
        $data = $request->all();
        $path_parts = \pathinfo($data['name']);
        $error = false;

        $absolutedir = __DIR__;
        $dir = '/tmp/';
        $serverdir = $absolutedir.$dir;

        $tmp = \explode(',', $data['data']);
        $imgdata = \base64_decode($tmp[1], true);

        //$extension              = strtolower(end(explode('.',$data['name'])));
        $extension = $path_parts['extension'];
        $filename = \mb_substr($data['name'], 0, -(\mb_strlen($extension) + 1)).'.'.\mb_substr(\sha1(\time()), 0, 6).'.'.$extension;
        /*
        $handle                 = fopen($serverdir.$filename,'w');

        fwrite($handle, $imgdata);
        fclose($handle);
        */
        $path = 'photos/'.\Auth::user()->handle.'/'.$filename;
        \Storage::disk('public_html')->put($path, $imgdata);
        $url = \Storage::disk('public_html')->url($path);
        $url = \str_replace(url('/'), '', $url); //per risparmiare spazio
        $response = [
                'status' => 'success',
                'url' => $url.'?'.\time(), //added the time to force update when editting multiple times
                'filename' => $filename,
        ];

        if (!empty($data['original'])) {
            $tmp = \explode(',', $data['original']);
            $originaldata = \base64_decode($tmp[1], true);
            $original = \mb_substr($data['name'], 0, -(\mb_strlen($extension) + 1)).'.'.\mb_substr(\sha1(\time()), 0, 6).'.original.'.$extension;

            $handle = \fopen($serverdir.$original, 'w');
            \fwrite($handle, $originaldata);
            \fclose($handle);

            $response['original'] = $original;
        }

        return response()->json($response);
        //print json_encode($response);
    }
}//end class
