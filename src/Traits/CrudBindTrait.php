<?php
namespace XRA\Extend\Traits;

use Artisan;
use Illuminate\Http\Request;

use Carbon\Carbon;
//------services---------
use XRA\Extend\Services\ThemeService;
use XRA\Extend\Services\ScoutService;
//use Laravel\Scout\Searchable;
// il crud simple non ha il lock sui record, ne salva e continua, salva ed esci, ne esci e basta

trait CrudBindTrait
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

        return new $model();
    }

    public function getBindVar()
    {
        $class = class_basename(__CLASS__);
        $class = \str_replace('Controller', '', $class);
        $class = \mb_strtolower($class);

        return $class;
    }

    public function index(Request $request)
    {
        if ($request->act=='routelist') {
            return ArtisanTrait::exe('route:list');
        }
        $params = \Route::current()->parameters();
        if (isset($params['lang'])) {
            \App::setLocale($params['lang']);
        }
        \extract($params);
        $model = $this->getModel();
        $rows = $model;
        if (isset($container) && isset($item) && isset($container1)) {
            $type1s = str_plural($container1->post_type);
            $rows = $item->linked->$type1s();
        }

        //dd($rows);
        //$rows = $rows->filter(/*$request,*/ $params); //era search,ma scout search me lo fa rinominare

        if ($request->has('q')) {
            $query = $request->get('q');
            $rows = $rows->search($query);
            //dd($rows);
            //$rows=
            //$rows->searchable();
        }
        if (\method_exists($model, 'filter')) {
            $params['rows'] = $rows;
            $rows = $model->filter($params);
        }
        //dd($rows);
        //dd($rows);
        //$rows=$rows->where('anno',2017);

        if (\method_exists($model, 'getOrderBy')) {
            //die('['.__LINE__.']['.__FILE__.']');
            $orderBy_array = $model->getOrderBy();
            \reset($orderBy_array);
            foreach ($orderBy_array as $orderBy => $orderHow) {
                $rows = $rows->orderBy($orderBy, $orderHow);
            }
        }
        if ($request->has('scoutimport')) {
            ScoutService::import(['model' => $model]);
        }

        $allrows = $rows;
        $rows = $rows->paginate(20);
        // $href_create=route('classificator.cat.create');
        //$titolo = 'classificazione pagine';
        //return view('adm_theme::tree.show')->with('href_create',$href_create)->with('titolo',$titolo);
        $routename = \Route::current()->getName();
        $view = $this->getView($params);
        if (\View::exists($view)) {
            return view($view)
                    ->with('params', $params)
                    ->with($params)
                    ->with('rows', $rows)
                    ->with('allrows', $allrows)
                    ->with('routename', $routename)
                    ->with('view', $view)
                    //->with('id_dashboard', 1)
                    ;
        } else {
            return '<h3>La view ['.$view.'] non esiste</h3>';
        }
    }

    public function create(Request $request)
    {
        $params = \Route::current()->parameters();
        \extract($params);
        if (isset($lang)) {
            \App::setLocale($lang);
        } else {
            $lang = \App::getLocale();
        }
        /*
        $bind = $this->getBindVar();
        $row=$$bind; //si giusto con 2 $
        */
        $row = last($params); // si fa edit sempre sull'ultimo parametro prima appunto della parola edit
        if (false === $row) {
            $row = $this->getModel();
        }
        $routename = \Route::current()->getName();
        $view = $this->getView($params);

        return view($view)
                ->with('row', $row)
                ->with('params', $params)
                ->with($params)
                ->with('view', $view)
                ->with('routename', $routename)
                ;
    }

    //end edit

    public function store(Request $request)
    {
        $params = \Route::current()->parameters();
        \extract($params);
        $data = $request->all();
        /*
        $bind = $this->getBindVar();
        $row=$$bind; //si giusto con 2 $
        */
        $row = last($params); // si fa edit sempre sull'ultimo parametro prima appunto della parola edit
        //dd($data);
        if (false === $row) {
            $row = $this->getModel();
        }
        $row=$row->create($data);
        $arr = \array_slice($row->toArray(), 0, 4);
        \Session::flash('status', 'creato ! '.\implode(' ', $arr).' '.Carbon::now());
        return ThemeService::action($request,$row);
        /*
        switch ($data['_action']) {
            case 'save_continue':
                return back()->withInput();
            break;
            case 'save_close':
                $routename = \str_replace('.store', '.index', $routename);

                return redirect()->route($routename, $params);
            break;
            default:
            echo '<h3>['.__LINE__.']['.__FILE__.']</h3>';
            dd($data['_action']);
            break;
        }
        */
    }

    public function edit(Request $request)
    {
        if (1 == $request->replicate) {
            return $this->replicate($request);
        }
        $params = \Route::current()->parameters();
        \extract($params);
        if (isset($lang)) {
            \App::setLocale($lang);
        } else {
            $lang = \App::getLocale();
        }
        /*
        $bind = $this->getBindVar();
        $row=$$bind; //si giusto con 2 $
        */
        $row = last($params); // si fa edit sempre sull'ultimo parametro prima appunto della parola edit
        $routename = \Route::current()->getName();
        $view = $this->getView($params);

        return view($view)
                ->with('row', $row)
                ->with('params', $params)
                ->with($params)
                ->with('view', $view)
                ->with('routename', $routename)
                ;
    }

    //end edit

    public function update(Request $request)
    {
        $params = \Route::current()->parameters();
        \extract($params);
        $data = $request->all();
        /*
        $bind = $this->getBindVar();
        $row=$$bind; //si giusto con 2 $
        */
        $row = last($params); // si fa edit sempre sull'ultimo parametro prima appunto della parola edit
        //dd($data);
        $row->update($data);
        $arr = \array_slice($row->toArray(), 0, 4);

        \Session::flash('status', 'aggiornato! '.\implode(' ', $arr).' '.Carbon::now());
        return ThemeService::action($request,$row);
        //return back()->withInput();
    }

    public function show(Request $request)
    {
        $params = \Route::current()->parameters();
        $row = last($params);
        $routename = \Route::current()->getName();
        $view = $this->getView($params);

        return view($view)
                ->with('row', $row)
                ->with('params', $params)
                ->with($params)
                ->with('view', $view)
                ->with('routename', $routename)
                ;
    }

    public function destroy(Request $request)
    {
        $params = \Route::current()->parameters();
        $row = last($params);
        //extract($params);
        $row->delete();
        /*
        $class=class_basename($item);
        $model=new $class;
        $res = $model->destroy($request->v);
        */
    }

    public static function getView($params = [])
    {
        \extract($params);
        $tmp = \Route::currentRouteAction();
        //echo '<h3>'.$tmp.'</h3>';

        $tmp = \str_replace('App\Http\Controllers\\', '', $tmp);
        $tmp = \str_replace('\\', '.', $tmp);
        $tmp = \str_replace('Controller@', '.', $tmp);
        $tmp = \mb_strtolower($tmp);
        if ('.' == $tmp[0]) {
            $tmp = \mb_substr($tmp, 1);
        }
        //echo '<h3>'.$tmp.'</h3>';
        $tmp = \explode('.', $tmp);
        $pack = $tmp[1];
        $path = \array_slice($tmp, 3);
        //dd(config('xra.settings.base_url'));
        //dd($path);
        if (isset($path[1]) && $path[1] == $pack && 'admin' == $path[0]) {
            unset($path[1]);
        } elseif (isset($path[0]) && $path[0] == $pack) {
            unset($path[0]);
        }
        $view = \implode('.', $path);
        if ('frontend' == $pack) {
            $pack = 'pub_theme';
        }
        $view = $pack.'::'.$view;
        $str1 = '.pdfszip';
        if (\mb_substr($view, -\mb_strlen($str1)) == $str1) {
            $view = \mb_substr($view, 0, -\mb_strlen($str1)).'.pdf.row';
        }

        $str2 = '.pdfrow';
        if (\mb_substr($view, -\mb_strlen($str2)) == $str2) {
            $view = \mb_substr($view, 0, -\mb_strlen($str2)).'.pdf.row';
        }

        if (isset($container)) {
            if (\is_object($container)) {
                $container_type = $container0->post_type;
            } else {
                $container_type = $container;
            }
            if (isset($container1)) {
                if (\is_object($container1)) {
                    $container1_type = $container1->post_type;
                } else {
                    $container1_type = $container1;
                }
                $view = \str_replace('::container.', '::'.$container_type.'.'.$container1_type.'.', $view);
                $view = \str_replace('::admin.container.', '::admin.container.container1.', $view);
            }
            if (isset($container2)) {
                if (\is_object($container2)) {
                    $container2_type = $container2->post_type;
                } else {
                    $container2_type = $container2;
                }
                $view = \str_replace('::container.', '::'.$container_type.'.'.$container1.'.'.$container2.'.', $view);
                //$view=str_replace('::admin.container.','::admin.'.$container_type.'.'.$container1_type.'.',$view);
            }
            $view = \str_replace('::container.', '::'.$container_type.'.', $view);
            //$view=str_replace('.container.','.'.$container_type.'.',$view);

            if (false === \mb_strpos($view, '::admin.')) {
                $view = \str_replace('blog::', 'pub_theme::', $view);
            }
            /*elseif(isset($container)){
                dd(config('xra.model.'.$container0->post_type));
            }*/
        }

        if (\Request::ajax()) {
            $view .= '_ajax';
        }

        if (\View::exists($view)) {
            return $view;
        } else {
            echo '<h3>'.\Route::currentRouteAction().'</h3>';
            die('<h3>la view ['.$view.'] non esiste </h3>['.__LINE__.']['.__FILE__.']');
        }
    }

    //end getView
}//end trait
