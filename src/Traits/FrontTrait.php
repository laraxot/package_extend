<?php
namespace XRA\Extend\Traits;

use Cache;
use Illuminate\Http\Request;
use Route;
//--------- services--------
use XRA\Extend\Services\ThemeService as Theme;
//------traits-------
use XRA\Extend\Traits\CrudSimpleTrait as CrudTrait;
//------models----------
use XRA\Blog\Models\Post;

\error_reporting(E_ALL);
\ini_set('display_errors', 1);

trait FrontTrait
{
    public function getModel()
    {
        $rc = new \ReflectionClass(\get_class($this));
        $namespace = $rc->getNamespaceName();
        $str = 'Controllers';
        $pos = \mb_strpos($namespace, $str);
        $namespace = \mb_substr($namespace, 0, $pos);
        $namespace .= 'Models\\';
        $class = class_basename(__CLASS__);
        $class = \str_replace('Controller', '', $class);
        $model = $namespace.$class;
        //dd($model);
        return new $model();
    }

    public function containerFunc(Request $request)
    {
        $lang = \App::getLocale();
        $params = \Route::current()->parameters();
        $containers = \array_keys(config('xra.model'));
        \extract($params);
        if (\is_object($container)) {
            $type = $container0->post_type;
        } else {
            $type = $container;
        }
        /*
        if(!in_array($container,$containers)) {
            $container=Post::where('lang',$lang)->where('guid',$container)->first()->post_type;
        }
        */
        if (isset($item)) {
            $guid = $item;
            $func = 'show';
        } else {
            $guid = $container;
            $func = 'index';
        }
        $row = Post::with(['related'])
            ->where('lang', $lang)
            ->where('guid', $guid)
            ->where('type', $container)
            ->first();
        $row1 = null;
        if (isset($container1) && isset($item1)) {
            $row1 = Post::with([])->where('lang', $lang)->where('guid', $item1)->where('type', $container1)->first();
        }
        if (null == $row) {
            //dd('da creare ?['.$guid.']['.$container.']');
            if ($guid == $container && \in_array($container, $containers, true)) {
                $row = Post::with(['related', 'parentPost'])->firstOrCreate(['lang' => $lang, 'guid' => $guid, 'type' => $container], ['title' => $guid]);
                $row->title = $guid;
                $row->save();
            } else {
                $row2 = Post::with(['related'])
                    ->where('lang', 'it')      ///da prendere da config
                    ->where('guid', $guid)
                    ->where('type', $container)
                    ->first();
                if (null == $row2) {
                    //dd('pagina 404 ['.$container.']['.__LINE__.']['.__FILE__.']');
                    $msg = '['.$container.']['.__LINE__.']['.__FILE__.']';

                    return view('pub_theme::errors.404', ['msg' => $msg]);
                } else {
                    //*
                    $row = $row2->replicate();
                    //$tmp=$this->trans(['q'=>$this->$field,'from'=>$this->lang,'to'=>$lang]);
                    $row->url = null;
                    $row->url_lang = null;
                    $row->lang = $lang;
                    $row->save();
                    //*/
                    $this->syncLang();
                }
            }
        }

        Theme::__concatBeforeStatic('title', $row->title.' | ');
        Theme::__concatBeforeStatic('subtitle', $row->subtitle.' | ');
        Theme::__setStatic('url', $row->url);
        Theme::__setStatic('category', $row->post_type);
        Theme::__setStatic('published_at', $row->published_at);
        Theme::__setStatic('updated_at', $row->updated_at);
        $supportedLocales = config('laravellocalization.supportedLocales');
        Theme::__setStatic('locale', $supportedLocales[$lang]['regional']);
        Theme::__setStatic('description', $row->meta_description);
        Theme::__setStatic('keywords', $row->meta_keywords);
        Theme::__setStatic('type', $row->post_type);
        $image_width = 200;
        $image_height = 200;
        $image = $row->imageResizeSrc(['width' => $image_width, 'height' => $image_height]);
        Theme::__setStatic('image', asset($image));
        Theme::__setStatic('image_width', $image_width);
        Theme::__setStatic('image_height', $image_height);

        $params['container'] = $container;
        $view = CrudTrait::getView($params);
        //$view='pub_theme::blank';

        return view($view)
                ->with('row', $row)
                ->with('row1', $row1)
                ->with('view', $view)
                ->with('params', $params)
                ->with('request', $request)
                ;
    }

    /*
    public function syncLang(){ //--- da FARE ---
        $rows=Post::where('lang','it');
        $langs=array_keys(config('laravellocalization.supportedLocales'));
        $langs=array_combine($langs,$langs);
        unset($langs['it']);
        foreach($rows->get() ad $row){
            foreach($langs as $ilang){
                $rowlang=Post::where('lang',$ilang)->where('post_id',$row->post_id)->where('type',$row->post_type)->first();
                if($rowlang==null){
                    //------- DA FARE --------------
                }
            }
        }
    }
    */
    public function containerFuncB(Request $request)
    {
        $lang = \App::getLocale();
        $params = \Route::current()->parameters();
        $row = last($params); // meglio ultimo oggetto
        \extract($params);
        $view = CrudTrait::getView($params);
        $roots = Post::where('lang', $lang)->whereRaw('guid = type ')->get()->keyBy('type')->all();

        return view($view)->with($params)
                ->with('view', $view)
                ->with('params', $params)
                ->with('request', $request)
                ->with('row', $row)
                ->with('lang', $lang)
                ->with($params)
                ->with($roots)
                ;
    }

    public function containerFuncCache(Request $request)
    {
        $params = \Route::current()->parameters();
        $key = ['params' => $params, 'data' => $request->all()];
        $key = \json_encode($key);
        $key = \md5($key);
        $value = Cache::rememberForever($key, function () use ($request) {
            $html = (string) $this->containerFunc($request);

            return $html;
        });

        return $value;
    }

    public function index(Request $request)
    {
        //die('['.__LINE__.']['.__FILE__.']');
        return $this->containerFuncB($request);
    }

    /*
    public function index1(Request $request){
        die('['.__LINE__.']['.__FILE__.']');
        return $this->containerFunc($request);
    }
    */

    public function show(Request $request)
    {
        //die('['.__LINE__.']['.__FILE__.']');
        return $this->containerFuncB($request);
    }

    public function create(Request $request)
    {
        //die('['.__LINE__.']['.__FILE__.']');
        return $this->containerFuncB($request);
    }

    public function attach(Request $request)
    {
        return $this->containerFuncB($request);
    }

    public function store_old(Request $request)
    {
        $data = $request->all();
        $data = \json_decode($data);
        $data['subtitle'] = \Request::format(); //html
        $data['lang'] = \App::getLocale();
        $data['type'] = 'restaurant';
        $row = Post::create($data); //usa i fillable
        //*
        $data['post_id'] = $row->post_id;
        $linked = Restaurant::create($data); //usa i fillable

        //*/
        /*
        dd(new(Restaurant)->getFillable());
        dd($row->linked()->getFillable());
        $linked=$row->linked()->update($data); //linked crea la riga se non esiste, percio' save non create update non controlla i fillable percio' da errore
        dd($linked);
        */
        \Session::flash('status', 'Ristorante Aggiunto ['.$row->post_id.'] Ricordati questo numero per eventuali comunicazioni');
        $response = [
            'success' => true,
            'data' => 'ok',
            'message' => 'create ['.$row->post_id.']',
            'url' => $row->url,
        ];

        if (true == $request->is_amp) {
            \Debugbar::disable();
            $domain_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http')."://$_SERVER[HTTP_HOST]";
            \header('Content-type: application/json');
            \header('Access-Control-Allow-Credentials: true');
            //header("Access-Control-Allow-Origin: ". str_replace('.', '-','https://example.com') .".cdn.ampproject.org");
            \header('AMP-Access-Control-Allow-Source-Origin: '.$domain_url);
            \header('Access-Control-Expose-Headers: AMP-Access-Control-Allow-Source-Origin');
            \header('AMP-Redirect-To: '.$row->url);
            \header('Access-Control-Expose-Headers: AMP-Redirect-To, AMP-Access-Control-Allow-Source-Origin');

            return response()->json($response, 200);
        }

        if (\Request::ajax()) {
            return response()->json($response, 200);
        }

        //-- se le altre 2 son false arrivo qui
        //return redirect()->back();
        //$routename = \Route::current()->getName();
        //$params = \Route::current()->parameters();
        //$routename = str_replace('.store', '.index', $routename);
        return redirect($row->url); //->route($routename, $params);
    }

    public function update(Request $request)
    {
        $params = \Route::current()->parameters();
        \extract($params);
        $data = $request->all();
        $row = last($params); // si fa edit sempre sull'ultimo parametro prima appunto della parola edit
        $data['image_resize_src'] = [];
        $row->update($data);
        $arr = \array_slice($row->toArray(), 0, 4);
        $msg = 'aggiornato! '.\implode(' ', $arr);
        \Session::flash('status', $msg);

        //$validator = Validator::make($data, $rules);
        //$errors = $validator->errors();
        //$msg='';
        //foreach ($errors->all() as $message) {
        //    $msg.='<br/>'.$message;
        //}
        //if ($validator->fails()) {
        if ($request->ajax()) {
            return response()->json(['msg' => $msg], 200);
        }

        //return back()->withInput();
        $routename = \Route::current()->getName();
        switch ($data['_action']) {
            case 'save_continue':
                return back()->withInput();
            break;
            case 'save_close':
                $routename = \str_replace('.update', '.index', $routename);

                return redirect()->route($routename, $params);
            break;
            default:
            echo '<h3>['.__LINE__.']['.__FILE__.']</h3>';
            dd($data['_action']);
            break;
        }
    }
}//end trait
