<?php
namespace XRA\Extend\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Translation\Translator as BaseTranslator;
use Illuminate\Support\Facades\Cache;

//---- services ---
use XRA\Extend\Services\ThemeService;

class TranslatorService extends BaseTranslator{

 /**
     * @param string $key
     * @param array $replace
     * @param null $locale
     * @param bool $fallback
     *
     * @return array|null|string|void
     */
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        $translation = parent::get($key, $replace, $locale, $fallback);
        //echo '<br>['.$key.']['.$translation.']';
        $langs=ThemeService::__merge('langs', [$key=>$translation]);
        $cache_key=str_slug($_SERVER['REQUEST_URI'].'_langs');
        Cache::put($cache_key,$langs);
        //echo '<pre>';print_r($langs);echo '</pre>';
        /*
        if ($translation === $key) {
            Log::warning('Language item could not be found.', [
                'language' => $locale ?? config('app.locale'),
                'id' => $key,
                'url' => config('app.url')
            ]);
        }
        */

        
        return $translation;
    }


    public static function set($key,$value){
    	$lang=\App::getLocale();
    	if(trans($key) == $value ) return ; //non serve salvare
    	
    	$translator = app('translator');
    	$tmp=($translator->parseKey($key));
    	$namespace=$tmp[0];
    	$group=$tmp[1];
    	$item=$tmp[2];
    	$trans = trans();
		$path = collect($trans->getLoader()->namespaces())->flip()->search($namespace);
		$filename=$path.DIRECTORY_SEPARATOR.$lang.DIRECTORY_SEPARATOR.$group.'.php';

		$trad = $namespace.'::'.$group;
        $rows = (trans($trad));
        $item_keys=explode('.',$item);
        $item_keys=implode('"]["',$item_keys);
        $item_keys='["'.$item_keys.'"]';
        $str='$rows'.$item_keys.'="'.$value.'";';
        try{
            eval($str); //fa schifo ma funziona
        }catch(\Exception $e){

        }
        ArrayService::save(['data' => $rows, 'filename' => $filename]);

        \Session::flash('status', 'Modifica Eseguita! ['.$filename.']');

        


        /*

        ddd($rows)



        ddd($item_keys);

    	ddd($filename);
    	*/
    }

}