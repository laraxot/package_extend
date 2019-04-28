<?php
namespace XRA\Extend\Traits;

//use Artisan;
use Illuminate\Http\Request;
//-------- services -----
use XRA\Blog\Models\Post;
//-------- models ------
use XRA\Blog\Models\PostRelated;
//------- services -------
use XRA\Extend\Services\ThemeService;
use XRA\Extend\Services\ScoutService;

trait CrudContainerItemTrait
{
    private $optionalParams = [];

    /*
    public function __construct(){
        ddd('preso');
    }
    */

    public function getModel()
    {

        $namespace=get_class($this);
        //$rc = new \ReflectionClass(\get_class($this));
        //$namespace = $rc->getNamespaceName();
        $str = 'Controllers';
        $pos = \mb_strpos($namespace, $str);
        $namespace = \mb_substr($namespace, 0, $pos);
        $namespace .= 'Models\\';
        //$class = class_basename(__CLASS__);
        $class = class_basename($this);
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


    public function index(Request $request){
        if ($request->act=='routelist') { return ArtisanTrait::exe('route:list');  }
        $params = \Route::current()->parameters();
        if (isset($params['lang'])) { \App::setLocale($params['lang']); }
        $row = last($params);
        if ($request->has('scoutimport')) { ScoutService::import(['model' => $row]);}

        if ($request->has('q') && ($request->ajax() || 1 == $request->ajax)) { //typeahead
            $start_time=microtime(true);
            $q = $request->input('q').'';
            /*
            $ris = $row->archive->filter(function ($item) use ($q) {
                return false !== \mb_stristr($item->title, $q);
            })->unique('title')->map(function ($item, $key) {
                return ['title' => $item->title];
            })->values();
            */
            $rows=Post::ofSearch($q)->where('post_type',$row->post_type)->select('title')->distinct()->limit(10)->get();
            return $rows->toJson();
            //return response($ris);
            //$ris=[['title'=>'titolo'],['title'=>'co']];
            $end_time=microtime(true);
            //return response()->json($ris).'['.($end_time-$start_time).']';
        }

        if ('json' == $request->dataType /*&& $request->ajax() */) {
            $rows = $row->archive();
            $type = $row->post_type;
            $data = $request->all();
            \extract($data);
            /*
            if($request->has('mapWestLng') && $request->has('mapEastLng') ){ // da spostare in archive direttamente ?
                $rows=$rows->ofLongitudeBetween($type,[$mapWestLng,$mapEastLng]);
            }
            if($request->has('mapSouthLat') && $request->has('mapNorthLat') ){
                $rows=$rows->ofLatitudeBetween($type,[$mapSouthLat,$mapNorthLat]);
            }
            */
            $ris = [];

            foreach ($rows->get() as $row) {
                $item = $this->extractMapParameters($row);
                $ris[] = $item;
            }

            return response()->json($ris);
        }

        \extract($params);
        $model = $this->getModel();
        //$rows = $model;
        list($containers,$items)=$this->params2ContainerItem($params);
        $n_containers=count($containers);
        $n_items=count($items);

        if($n_items==0){
            //$rows=$row->archive();
            $rows=$model->has('post')->with(['post']);//->paginate(20);//con post = 50, senza = 70
                
        }else{
            $types = str_plural($containers[$n_containers-1]->post_type);
            $types = camel_case($types);
            try{
                if(!is_object($items[$n_items-1])){

                    ddd($items);
                }
                $rows = $items[$n_items-1]->$types();
            }catch(\Exception $e){
                //$rows=[];
                ddd($e);
                return abort(404);
            }


            //*
            if($n_items>=2){ 
               //ddd($items[$n_items-2]); //guid = padova , type= location
                //$rows=$rows->scopeOfLocality('padova');
                //* -- se tutto collegato coi related 
                /* -- MEGLIO FARLO CUSTOM DENTRO IL MODELLO, non tutto deve passare per RELATED 
                $rows = $rows->whereHas('relatedrev',function($query) use ($items,$n_items) {
                    $query->where('blog_post_related.post_id',$items[$n_items-2]->post_id);
                });
                */
                //*/
            }
            //*/
        }
        

        if ($request->has('q')) {
            $query = $request->get('q');
            $rows = $rows->ofPostSearch($query);

        }

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
        $mrows=[];
        $allrows = $rows;
        $rows = $rows->/*groupBy('guid')->*/paginate(20);
        if($model->post_type=='restaurant'){
            $rows->load(['cuisineCats']); // con cuisineCats = 15 senza = 47
        }
        if($model->post_type=='cuisine'){
            $rows->load('recipes','ingredientCats');
        }
        $roots = Post::getRoots();

        return ThemeService::view()->with($roots)->with('rows',$rows)->with('allrows',$allrows); //mrows lo usero' per i morphed
        // $href_create=route('classificator.cat.create');
        //$titolo = 'classificazione pagine';
        //return view('adm_theme::tree.show')->with('href_create',$href_create)->with('titolo',$titolo);
        /*
        return ThemeService::addViewParam('row', $row)
            ->addViewParam('rows', $rows)
            ->addViewParam('allrows', $allrows)
            ->addViewParam($roots)
            ->view();
        */
    }

    public function edit(Request $request){
        if (1 == $request->replicate) { // da passare in act 
            return $this->replicate($request);
        }
        switch($request->act){
            case 'xstack': xdebug_print_function_stack( 'Your own message' ); break;
        }
        $lang = \App::getLocale();
        $params = \Route::current()->parameters();
        if (isset($params['lang'])) {
            \App::setLocale($params['lang']);
        } else {
            //$params['lang']=\App::getLocale();
        }
        \extract($params);
        $row = last($params); // si fa edit sempre sull'ultimo parametro prima appunto della parola edit
        $user = \Auth::user();
        if (is_object($row) && $user->cannot(__FUNCTION__, $row)) {
            abort(403);
        }
        $second_last = collect(\array_slice($params, -2))->first(); //penultimo
        if (!\is_object($row) && \is_object($second_last) && $second_last->post_type == $row) {
            $type = $item0->post_type.'_x_'.$second_last->post_type;
            $item0->related()->attach($second_last->post_id, ['type' => $type]);
        }
        $roots = Post::getRoots();
        return ThemeService::addViewParam('row', $row)
            ->addViewParam($roots)
            ->view();
    }

    //end edit

    public function update(Request $request){
        $params = \Route::current()->parameters();
        $lang = \App::getLocale();
        \extract($params);
        $data = $request->all();
        $row = last($params); // si fa edit sempre sull'ultimo parametro prima appunto della parola edit
        if (\Auth::user()->cannot(__FUNCTION__, $row)) {
            abort(403);
        }
        $data['image_resize_src'] = [];
        
        $ris=$row->update($data);
        $post=$row->post;
        $post->update($data);
        //$post=$row->post()->save($post); //?
        $pivot_var = $request->pivot;
        if(is_array($pivot_var) && isset($row->pivot) ){ 
            $row->pivot->update($pivot_var);
        }
        
        if (isset($data['linked']) && \is_array($data['linked'])) { //-- Linked Data
            $post->update($data['linked']);
        }
        
        if (isset($data['related'])) {
            ddd('check 1');
            foreach ($data['related'] as $rel_obj => $rel_id) {
                $type = $row->post_type.'_x_'.$rel_obj;
                PostRelated::where('post_id', $row->post_id)->where('type', $type)->delete();
                $res = $row->related()->attach($rel_id, ['type' => $type]);
            }
        }

        if (isset($data['relatedrev'])) {
            ddd('check 2');
            foreach ($data['relatedrev'] as $rel_obj => $rel_id) {
                $type = $rel_obj.'_x_'.$row->post_type;
                PostRelated::where('related_id', $row->post_id)->where('type', $type)->delete();
                $res = $row->relatedrev()->attach($rel_id, ['type' => $type]);
            }
        }

        if (\Request::ajax()) {
            $response = [
                'success' => true,
                //'data'    => $result,
                'message' => 'msg test',
            ];
            $response = \array_merge($data, $response);

            return response()->json($response, 200);
        }

        //$arr=array_slice($row->toArray(),0,4);
        $arr = [$row->post_id];
        \Session::flash('status', 'aggiornato! '.\implode(' ', $arr));
        //return back()->withInput();
        $routename = \Route::current()->getName();
        if (!isset($data['_action'])) {
            $data['_action'] = 'save_continue';
        }
        return ThemeService::action($request,$row);
    }

    public function getEmpty($row)
    {
        $class = '\\'.\get_class($row);
        $empty = new $class();
        $empty->lang = $row->lang;
        $empty->post_type = $row->post_type;

        return $empty;
    }

    public function create(Request $request){
        $params = \Route::current()->parameters();
        if (isset($params['lang'])) {
            \App::setLocale($params['lang']);
        } else {
            $lang = \App::getLocale();
        }

        \extract($params);
        $routename = \Route::current()->getName();
        $row = last($params);
        if (isset($this->notAuthorize) && true == $this->notAuthorize) {
        } else {
            $this->authorize('create', $row);
            //$this->authorize('create');
        }
        $empty = $this->getEmpty($row);
        if ('' != $request->step) {
            $view .= '.step'.$request->step;
        }
        $row = $empty;
        $roots = Post::getRoots();

        return ThemeService::addViewParam('row', $row)
            ->addViewParam($roots)
            ->view();
    }

    public function store(Request $request){
        $params = \Route::current()->parameters();
        $routename = \Route::current()->getName();
        
        if($request->_action=='exit'){
            $route_next = \str_replace('.store', '.index_edit', $routename);// da testare
            return redirect()->route($route_next, $params);
        }

        $params = \Route::current()->parameters();
        $n_params = \count($params);
        $row = last($params);
        $data = $request->all();

        $model=$this->getModel();
        $data['lang']=$model->lang;
        $data['type']=$model->post_type; //retrocompatibilità
        if(!isset($data['guid']) && isset($data['title']) ){
            $data['guid']=str_slug($data['title']);
        }
        $row=$model->create($data);
        //ddd($row); // bisogna controllare se post_id e' creato
        $post=$row->post()->create($data);
        //ddd($post);
        $second_last = collect(\array_slice($params, -2))->first(); //penultimo
        
        if (\is_object($second_last) && $n_params>=2) { // da verificare il > x
            $pivot_var = $request->pivot;
            if (!\is_array($pivot_var)) {
                $pivot_var = [];
            }
            $related_type=collect(config('xra.model'))->search(get_class($row));
            $pivot_var['related_type']=$related_type;
            //$pivot_var['type']= $second_last->post_type.'_x_'.$row->post_type; //retrocompatibilità iniziamo a toglierla
            //ddd($row);
            $pivot=$second_last->morphRelated($row->getModel())->attach($row->post_id,$pivot_var);
        }
        

        $arr = \array_slice($row->toArray(), 0, 5);
        $msg = 'aggiunto! ';
        if (isset($second_last)) {
            if (isset($second_last->post_id)) {
                $msg .= '['.$second_last->post_id.' '.$second_last->title.'] &raquo; ';
            } else {
                $msg .= '['.$second_last.'] &raquo; ';
            }
        }
        $msg = \implode(' ', $arr);

        \Session::flash('status', $msg);
        $routename = \Route::current()->getName();
        $step = request()->input('step', 1);

        if ($request->ajax()) {
            $params['step'] = $step + 1;
            $routename = \str_replace('.store', '.create', $routename);

            return response()->json([
                'action' => $data['_action'],
                'msg' => $msg,
                'step' => $step + 1,
                'nexturl' => route($routename, $params),
            ]);
        }

        if (!isset($data['_action'])) {
            $data['_action'] = 'save_continue';
        }
        return ThemeService::action($request,$row);
       
    }
    
    public function params2ContainerItem($params){
        $container=[];
        $item=[];
        foreach($params as $k=>$v){
            $pattern='/(container|item)([0-9]+)/';
            preg_match($pattern, $k,$matches);
            if(isset($matches[1]) && isset($matches[2]) ){
                $sk=$matches[1];
                $sv=$matches[2];
                $$sk[$sv]=$v;
            };
        }
        return [$container,$item]; 
    }

    public function indexEdit(Request $request){
        $params = \Route::current()->parameters();
        $row = last($params);
        if (!\Auth::check() || \Auth::user()->cannot(__FUNCTION__, $row)) {
            $routename = \Route::current()->getName();
            $route_next=str_replace('.index_edit','.index',$routename);
            return redirect()->route($route_next,$params);
        }
        list($container,$item)=$this->params2ContainerItem($params);
        $n_container=count($container);
        $n_item=count($item);

        $types=str_plural($container[$n_container-1]->post_type);
        $types=camel_case($types);
        $rows=$item[$n_item-1]->$types();
        $rows=$rows->paginate(20);
        $roots = Post::getRoots();
        return ThemeService::view()->with('rows',$rows)->with($roots);
    }

    public function indexOrder(Request $request){
        $params = \Route::current()->parameters();
        $row = last($params);
       
        /*
        if (!\Auth::check() || \Auth::user()->cannot(__FUNCTION__, $row)) {
            $routename = \Route::current()->getName();
            $route_next=str_replace('.index_edit','.index',$routename);
            return redirect()->route($route_next,$params);
        }
        */
        list($container,$item)=$this->params2ContainerItem($params);
        $n_container=count($container);
        $n_item=count($item);

        $types=str_plural($container[$n_container-1]->post_type);
        $types=camel_case($types);
        $rows=$item[$n_item-1]->$types();
        $rows=$rows->get(); //non ha senso mettere la paginazione
        if($request->getMethod()=='POST'){
            $order_arr=explode('|',$request->order_list);
            foreach($order_arr as $k=>$v){
                $up=$rows->where('post_id',$v)->first();
                $up->pivot->pos=$k+1;
                $up->pivot->save();
                //dd($k+1);
            }
            if (\Request::ajax()) {
                $response = [
                    'success' => true,
                    //'data'    => $result,
                    'message' => 'OK',
                ];
                $response = \array_merge($data, $response);

                return response()->json($response, 200);
            }
        }


        $order_list=$rows->pluck('post_id')->implode('|');
        $roots = Post::getRoots();
        return ThemeService::view()->with('rows',$rows)->with($roots)->with('order_list',$order_list);
    }



    public function show(Request $request){
        $params = \Route::current()->parameters();
        $row = last($params);
        $roots = Post::getRoots();
        if (\is_string($row)) {
            /*
            if($row=='edit'){
                return $this->editContainer($request);
            }
            */
            //return abort(404);
            $parz=$roots;
            $parz['msg']='not found';
            $parz['lang']=\App::getLocale();
            $parz['params']=$params;
            
            if (\View::exists('pub_theme::errors.404')) {
                return response()->view('pub_theme::errors.404', $parz, 404);
            } else {
                return response()->view('errors.404', $parz, 404);
            }
        }
        ThemeService::setMetatags($row);
        ThemeService::addViewParam('row', $row)->addViewParam($roots);

        return ThemeService::view();
    }

    //end show

    // TODO migliorare il destroy, in modo che cancellando una categoria o menu, cancelli ricorsivamente anche tutti i suoi figlio (categorie e non)
    public function destroy(Request $request)
    {
        $params = \Route::current()->parameters();
        \extract($params);
        $row = last($params);
        $row->delete();
        /*
        $class=class_basename($item);
        $model=new $class;
        $res = $model->destroy($request->v);
        */
        //$res = Post::destroy($request->id);
    }

    public function detach(Request $request)
    {
        $params = \Route::current()->parameters();
        $data = $request->all();
        $lang = \App::getLocale();
        \extract($params);
        $last = last($params);
        $last->pivot->delete();
    }

    public function attach(Request $request)
    {
        $params = \Route::current()->parameters();
        $data = $request->all();
        $lang = \App::getLocale();
        \extract($params);
        $last = last($params);
        $second_last = collect(\array_slice($params, -2))->first(); //penultimo
        if ('POST' == $request->getMethod()) {
            $related = Post::firstOrCreate(
                ['lang' => $lang, 'title' => $data['title'], 'type' => $last->post_type]
            );
            $msg = 'collegato '.\implode(' ', $data);
            \Session::flash('status', $msg);

            $related_type = $second_last->post_type.'_x_'.$last->post_type;
            $rel = $second_last->related()->attach($related->post_id, ['type' => $related_type]);

            $routename = \Route::current()->getName();
            $routename = \str_replace('.attach', '.index', $routename);

            return redirect()->route($routename, $params); //torna alla lista
            //return redirect()->back();//aggiungi un altro
        }

        return $this->create($request);
    }

    public function initPos()
    {
        $params = \Route::current()->parameters();
        //extract($params);
        $parent_item = collect(\array_slice($params, -3))->first();
        $me = collect(\array_slice($params, -1))->first();
        $brothers = $parent_item->related($me->post_type);
        $i = 0;
        foreach ($brothers->get() as $item) {
            echo '<br/>'.$item->title.'('.$item->pivot->pos.')';
            $item->pivot->pos = ($i + 1) * 10;
            $item->pivot->save();
            ++$i;
        }
    }

    public function moveUp(Request $request)
    {
        $params = \Route::current()->parameters();
        $parent_item = collect(\array_slice($params, -3))->first();
        $me = collect(\array_slice($params, -1))->first();
        $me_pos = $me->pivot->pos;
        if (null == $me_pos) {
            $this->initPos();

            return redirect()->back();
        }
        $prev = $parent_item->relatedNotOrdered($me->post_type)->where('blog_post_related.pos', '<', $me_pos)->orderBy('blog_post_related.pos', 'desc')->first();
        if (!isset($prev->pivot)) {
            return redirect()->back();
        }
        $prev_pos = $prev->pivot->pos;
        $me->pivot->pos = $prev_pos;
        $me->pivot->save();
        $prev->pivot->pos = $me_pos;
        $prev->pivot->save();

        return redirect()->back();
    }

    public function moveDown(Request $request)
    {
        $params = \Route::current()->parameters();
        $parent_item = collect(\array_slice($params, -3))->first();
        $me = collect(\array_slice($params, -1))->first();
        if (!isset($me->pivot)) {
            ddd($me);
        }
        $me_pos = $me->pivot->pos;
        if (null == $me_pos) {
            $this->initPos();

            return redirect()->back();
        }
        $next = $parent_item->relatedNotOrdered($me->post_type)->where('blog_post_related.pos', '>', $me_pos)->orderBy('blog_post_related.pos', 'asc')->first();
        if (!isset($next->pivot)) {
            return redirect()->back();
        }
        $next_pos = $next->pivot->pos;
        $me->pivot->pos = $next_pos;
        $me->pivot->save();
        $next->pivot->pos = $me_pos;
        $next->pivot->save();

        return redirect()->back();
    }

    public function jsonFeed()
    {
        $posts = Post::active()->limit(20)->get();
        $data = [
            'version' => 'https://jsonfeed.org/version/1',
            'title' => 'Laravel News Feed',
            'home_page_url' => 'https://laravel-news.com/',
            'feed_url' => 'https://laravel-news.com/feed/json',
            'icon' => 'https://laravel-news.com/apple-touch-icon.png',
            'favicon' => 'https://laravel-news.com/apple-touch-icon.png',
            'items' => [],
        ];
        foreach ($posts as $key => $post) {
            $data['items'][$key] = [
                'id' => $post->id,
                'title' => $post->title,
                'url' => 'https://laravel-news.com/'.$post->uri,
                'image' => $post->featured_image,
                'content_html' => $post->parsed_content,
                'date_created' => $post->publishes_at->tz('UTC')->toRfc3339String(),
                'date_modified' => $post->updated_at->tz('UTC')->toRfc3339String(),
                'author' => [
                    'name' => $post->user->name,
                ],
            ];
        }

        return $data;
    }

    //end jsonFeed

    /**
     * Adds dynamically parameters to a view.
     *
     * @param mixed $theVar
     *
     * @return $this
     */
    public function addViewParam($name, $value)
    {
        $this->optionalParams[$name] = $value;

        return $this;
    }

    /**
     * @param $row
     * @param $item
     *
     * @return mixed
     */
    public function extractMapParameters($row)
    {
        $linked = $row->linked;
        $item = $linked->getMapItem();
        $item['title'] = $row->title;
        $item['subtitle'] = $row->subtitle;
        $item['img_src'] = ($row->imageResizeSrc(['width' => 380, 'height' => 210]));
        $item['url'] = $row->url;
        $item['post_url'] = $row->url;

        return $item;
    }

    /**
     * Extracts parameters and add them to the view.
     *
     * @param $view
     *
     * @return mixed
     */
    private function addCustomParametersToView($view)
    {
        if (\count($this->optionalParams) > 0) {
            foreach ($this->optionalParams as $key => $value) {
                $view->with($key, $value);
            }
        }

        return $view;
    }


    public function tabs(){
        return ['test'];
    }
}//end trait
