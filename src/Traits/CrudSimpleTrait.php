<?php
namespace XRA\Extend\Traits;

use Artisan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spipu\Html2Pdf\Html2Pdf;
use TeamTNT\TNTSearch\Indexer\TNTGeoIndexer;
use TeamTNT\TNTSearch\TNTSearch;
use XRA\Extend\library\Resource;
// il crud simple non ha il lock sui record, ne salva e continua, salva ed esci, ne esci e basta
//------- services -------
use XRA\Extend\Services\ThemeService;
use XRA\Extend\Services\HtmlService;
use XRA\Extend\Services\ScoutService;

use XRA\Extend\library\SweetAlert;

trait CrudSimpleTrait
{
    private function getTitleFromModel($model)
    {
        return;
    }

    private function getRouteFromModel($model)
    {
        return;
    }

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

    public function getPrimaryKey()
    {
        $class = class_basename(__CLASS__);
        $class = \str_replace('Controller', '', $class);
        $class = \mb_strtolower($class);

        return 'id_'.$class;
    }

    

    public function index(Request $request)
    {
        if ($request->act=='routelist') {
            return ArtisanTrait::exe('route:list');
        }
        //if($request->scoutimport == 1){
        
        //}
        $params = \Route::current()->parameters();
        if (isset($params['lang'])) {
            \App::setLocale($params['lang']);
        }
        $model = $this->getModel();
        $rows = $model;
        //dd($rows);
        //$rows = $rows->filter(/*$request,*/ $params); //era search,ma scout search me lo fa rinominare

        if (\method_exists($model, 'scopeOfFilter')) {
            $rows = $rows->ofFilter($params);
        }
        if ('23990' == \Auth::user()->matr) {
            //$params['rows']=$rows;
            if (\method_exists($model, 'scopeOfSearch')) {
                $query = \trim($request->get('q'));
                if (\mb_strlen($query) > 1) {
                    //$rows=$rows->ofSearch($query);
                    //ddd($rows->toSql());
                }
            }
        }

        if ($request->has('q')) {
            $query = $request->get('q');
            if (\method_exists($rows, 'search')) {
                if (\mb_strlen(\trim($query)) > 2) {
                    $rows = $rows->search($query);
                }
                //ddd($rows->get());
            } else {
                echo '<p>Method Search not exists on ['.\get_class($rows).']</p>';
            }
            //dd($rows);
            //$rows=
            //$rows->searchable();
        }

        //*
        if (\method_exists($model, 'filter') && !\method_exists($model, 'scopeOfFilter')) {
            $params['rows'] = $rows;
            $rows = $model->filter($params);
        }
        //*/

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

        if ($request->has('scoutgeoimport')) {
            ScoutService::geoImport(['model' => $model]);
            //$this->scoutImport(['model'=>$row]);
        }

        $allrows = $rows;
        $rows = $rows->paginate(20);
        // $href_create=route('classificator.cat.create');
        //$titolo = 'classificazione pagine';
        //return view('adm_theme::tree.show')->with('href_create',$href_create)->with('titolo',$titolo);
        $routename = \Route::current()->getName();
        $view = $this->getView();

        if (\View::exists($view)) {
            return view($view)
                    ->with('params', $params)
                    ->with($params)   //non e' una ripetizione, quello sopra mi serve per le route, questo per avere subito le variabili
                    ->with('rows', $rows)
                    ->with('allrows', $allrows)
                    ->with('routename', $routename)
                    ->with('view', $view)
                    ->with('lang', \App::getLocale())
                    ;
        } else {
            return '<h3>La view ['.$view.'] non esiste</h3>';
        }
    }

    public function show(Request $request)
    {
        //die('['.__LINE__.']['.__FILE__.']');
        $params = \Route::current()->parameters();
        $model = $this->getModel();
        if (!isset($params[$this->getPrimaryKey()])) {
            $msg = '';
            $msg .= '<h3>Primary key non trovata in params</h3>';
            $msg .= '<h4>Primary key:['.$this->getPrimaryKey().']</h4>';
            echo '[params]<pre>';
            \print_r($params);
            echo '</pre>[/params]['.__LINE__.']['.__FILE__.']';

            return $msg;
        }
        $id = $params[$this->getPrimaryKey()];
        if (!\is_numeric($id)) {
            $row = $model->where(['guid' => $id])->first();
        } else {
            $row = $model->findOrFail($id);
        }

        $routename = \Route::current()->getName();
        $view = $this->getView();

        return view($view)
                ->with('params', $params)
                ->with('row', $row)
                ->with('view', $view)
                ->with('lang', \App::getLocale())
                ->with('routename', $routename);
    }

    /* forse caso fare un treecrud
    public function createinto($idcat){
        $this->create($idcat);
    }
    */

    public function create(Request $request)
    {
        $params = \Route::current()->parameters();
        $model = $this->getModel();

        $row = $model;
        \reset($params);
        foreach ($params as $k => $v) {
            if (\method_exists($this, 'translateParamToField')) {
                $tmp = $this->translateParamToField($k);
            } else {
                $tmp = $k;
            }
            if (false != $tmp) {
                $row->$tmp = $v;
            }
        }

        if (\method_exists($model, 'getDefaults')) {
            $def = $model->getDefaults();
            foreach ($def as $k => $v) {
                $row->$k = $v;
            }
            //    $data=array_merge($model->getDefaults(),$data);
        }

        $view = $this->getView();

        return view($view)
                    ->with('params', $params)
                    ->with('row', $row)
                    ->with('lang', \App::getLocale())
                    ->with('view', $view);
    }

    public function saveandexit($parz)
    {
        $params = [];
        \extract($parz);
        $params = \array_merge(\Route::current()->parameters(), $params);
        $routename = \Route::current()->getName();
        \Session::flash('status', $msg);
        $routename = \str_replace('.store', '.index', $routename);

        return redirect()->route($routename, $params);
    }

    public function saveandcontinue($parz)
    {
        $params = [];
        \extract($parz);
        $params = \array_merge(\Route::current()->parameters(), $params);
        $routename = \Route::current()->getName();
        \Session::flash('status', $msg);

        return redirect()->back();  // se .update

        $routename = \str_replace('.store', '.edit', $routename);
        $params[$this->getPrimaryKey()] = $row->id;

        return redirect()->route($routename, $params);
        //return redirect()->back();
    }

    public function store(Request $request)
    {
        $params = \Route::current()->parameters();
        $model = $this->getModel();
        if (\method_exists($model, 'validate')) {
            $res = $model->validate($data);
        } elseif (isset($model->rules) && \is_array($model->rules)) {
            $validatedData = $request->validate($model->rules);
        } else {
        }

        /*
        if(isset($params['lang']) && in_array('lang',$model->fields()->all())){
            $model=$model->where('lang',$params['lang']);
        }
        */
        $tbl = $model->getTable();
        //$id=$params[$this->getPrimaryKey()];
        $trad = \explode('\\', __CLASS__);
        $trad = \mb_strtolower($trad[1]);

        $lang = trans($trad.'::'.$tbl);
        if (!\is_array($lang)) {
            $lang = [];
        } //anche se non esiste
        $lang = \array_flip($lang);

        //echo '<pre>';print_r($lang);echo '</pre>';
        //die('['.__LINE__.']['.__FILE__.']');
        $row = $model;

        if ('' != $request->input('__submit_cancel')) {
            Resource::freeResource($row);

            return SweetAlert::alert('Annullato!', 'La risorsa non è stata creata.', 'red');
        }

        foreach ($request->all() as $k => $v) {
            if (\is_array($v)) {
                $v = \implode(',', $v);
                $request->merge([$k => $v]);
            }
        }

        $fields = \array_keys($row->getAttributes());
        $schema = $model->getConnection()->getSchemaBuilder();
        $columns = $schema->getColumnListing($model->getTable());

        //echo '<pre>';print_r($columns);echo '</pre>';
        $data = $request->all();
        $data_params = [];
        foreach ($params as $k => $v) {
            //$tmp=$this->translateParamToField($k);
            if (\method_exists($this, 'translateParamToField')) {
                $tmp = $this->translateParamToField($k);
            } else {
                $tmp = $k;
            }
            if (false != $tmp) {
                $data_params[$tmp] = $v;
            }
        }

        $data = \array_merge($data_params, $data);
        if (\method_exists($model, 'getDefaults')) {
            $data = \array_merge($model->getDefaults(), $data);
        }
        //$data= array_merge($data,$params); //dopo vediamo se forzare e togliore il campo hidden dalla view

        //echo '<pre>';print_r();echo '</pre>';
        //echo '<pre>';print_r($model->getTable());echo '</pre>';
        //echo '<pre>';print_r($columns);echo '</pre>';
        //echo '<pre>';print_r($data);echo '</pre>';

        //die();
        $myFields = \array_intersect($columns, \array_keys($data));
        //dd($myFields);
        //echo '<pre>';print_r($my);echo '</pre>';
        \reset($myFields);
        //while (list($k, $v) = each($myFields)) {
        foreach ($myFields as $k => $v) {
            $type = $schema->getColumnType($model->getTable(), $v);
            switch ($type) {
                case 'datetime':
                    if (\is_string($data[$v])) {
                        if (false !== \mb_strpos($data[$v], '/')) {
                            $data[$v] = Carbon::createFromFormat('d/m/Y H:i A', $data[$v]); /*'31/01/1976 02:45 PM'*/
                        } else {
                            $data[$v] = Carbon::parse($data[$v]); /*2018-02-21T08:55*/
                        }
                    }
                    break;
            }

            $row->$v = $data[$v];
        }

        //*
        \reset($params);
        //while (list($k, $v) = each($params)) {
        foreach ($params as $k => $v) {
            if (isset($lang[$k])) {
                $sk = $lang[$k];
                $row->$sk = $v;
                echo '<br/>'.$sk.' = '.$v;
            }
        }
        //*/

        //$row->pos = $data['pos'];
        $id = $row->save();
        if (\method_exists($model, 'getPrimaryKeyWithLang')) {
            $key_lang = $this->getPrimaryKeyWithLang();
            if (\in_array($key_lang, $columns, true)) {
                $row->update([$key_lang => $row->id]);
                $act = Resource::manageResource($request, $row);
            }
        }
        if ($request->_lastInsertId) {
            return $row->id;
        }
        if ('model' == $request->_out) {
            return $row;
        }
        //return $this->$act($request);
        $routename = \Route::current()->getName();
        $params[$this->getPrimaryKey()] = $row->id;

        \Session::flash('status', 'Risorsa salvata! '.$row->id);
        $routename = \str_replace('.store', '.index', $routename);
        //echo '<pre>'.$routename.'</pre>';
        //echo '<pre>';print_r($params); echo '</pre>'; die('['.__LINE__.']['.__FILE__.']');

        return redirect()->route($routename, $params);
    }

    public function edit(Request $request)
    {
        if (1 == $request->replicate) {
            return $this->replicate($request);
        }

        $params = \Route::current()->parameters();
        $params_key = \array_map('strtolower', \array_keys($params));
        $params_value = \array_values($params);
        $params1 = \array_combine($params_key, $params_value);
        $params = \array_merge($params, $params1);
        //dd($params);
        if (isset($params['lang'])) {
            \App::setLocale($params['lang']);
        }

        $model = $this->getModel();
        if (!isset($params[$this->getPrimaryKey()])) {
            $msg = '';
            $msg .= '<h3>Primary key non trovata in params</h3>';
            $msg .= '<h4>Primary key:['.$this->getPrimaryKey().']</h4>';
            echo '[params]<pre>';
            \print_r($params);
            echo '</pre>[/params]['.__LINE__.']['.__FILE__.']';

            return $msg;
        }
        $pk = $this->getPrimaryKey();  // 1 sola

        $pk_val = $params[$pk];

        /*
        $pks=$this->getPrimaryKey();
        if(!is_array($pks)){
            $pks=[$pks];
        }
        $row=$model;
        foreach($pks as $pk){
            if(isset($params[$pk])){
                $pk_val = $params[$pk];
            }elseif($pk=='lang'){
                $pk_val = \App::getLocale();
            }


            $row = $row->where($pk,$pk_val);
        }
        $row=$row->first();
        */

        //if(count($model->getKeyName())==1){
        if (isset($params['lang']) && \in_array('lang', $model->fields()->all(), true)) {
            $model = $model->where('lang', $params['lang']);
        }
        $row = $model->findOrFail($pk_val);

        /*
        }else{
            if(in_array('id',$model->fields()->all())){
                $row=$model->where(['id'=>$pk_val])->first();

            }else{
                dd($model->getKeyName() );
            }
        }
        */
        $routename = \Route::current()->getName();
        $view = $this->getView();

        return view($view)
                ->with('row', $row)
                ->with('params', $params)
                ->with('view', $view)
                ->with('routename', $routename)
                ->with('lang', \App::getLocale())
                ;
    }

    public function replicate(Request $request)
    {//duplica una riga..
        $params = \Route::current()->parameters();
        $model = $this->getModel();
        if (isset($params['lang']) && \in_array('lang', $model->fields()->all(), true)) {
            $model = $model->where('lang', $params['lang']);
        }
        $id = $params[$this->getPrimaryKey()];

        $row = $model->findOrFail($id);
        $clone_row = $row->replicate();
        $clone_row->save();
        //push ?????
        //$view = $this->getView();

        //return view($view)->with('params', $params)->with('row',$row);
        $routename = \Route::current()->getName();
        $params[$this->getPrimaryKey()] = $clone_row->id;

        \Session::flash('status', 'Risorsa Replicata! '.$clone_row->id);
        //$routename = str_replace('.replicate', '.index', $routename);
        //echo '<pre>'.$routename.'</pre>';
        //echo '<pre>';print_r($params); echo '</pre>'; die('['.__LINE__.']['.__FILE__.']');

        //return redirect()->route($routename, $params);
        return redirect()->back();
    }

    public function update(Request $request)
    {
        $routename = \Route::current()->getName();
        $params = \Route::current()->parameters();

        if ('exit' == $request->_action) {
            \Session::flash('status', 'Risorsa NON salvata!');

            return redirect()->route(\str_replace('.update', '.index', $routename), $params);
        }

        $model = $this->getModel();
        $table = $model->getTable();
        $data = $request->all();
        if (\method_exists($model, 'validate')) {
            $res = $model->validate($data);
        }
        $id = $params[$this->getPrimaryKey()];
        if (isset($params['lang']) && \in_array('lang', $model->fields()->all(), true)) {
            $model_pk = $model->getKeyName();
            $model->setKeyName('id');
            $row = $model->where('lang', $params['lang'])->where($model_pk, $id)->first();
        } else {
            $row = $model->findOrFail($id);
        }
        
        /*
        if ($request->input('__submit_cancel') != '') {
            Resource::freeResource($row);
            return SweetAlert::alert('Annullato!', 'La risorsa non è stata modificata.', 'red');
        }
        */
        //*
        $fields = \array_keys($row->getAttributes());
        $myFields = \array_intersect($fields, \array_keys($data));
        $schema = $model->getConnection()->getSchemaBuilder();
        \reset($myFields);
        //while (list($k, $v) = each($myFields)) {
        foreach ($myFields as $k => $v) {
            $type = $schema->getColumnType($table, $v);
            switch ($type) {
                case 'datetime':
                    if (false !== \mb_strpos($data[$v], 'T')) {
                        //dd($data[$v]);
                        $data[$v] = Carbon::parse($data[$v]);
                    } else {
                        $data[$v] = Carbon::createFromFormat('d/m/Y H:i A', $data[$v]); // '31/01/1976 02:45 PM'
                    }
                    break;
            }
            $row->$v = $data[$v];
        }
        //*/
        $row->save();
        //$row->update($request->all());
        
        if ('model' == $request->_out) {
            return $row;
        }

        
        \Session::flash('status', 'Risorsa salvata!');
        switch ($request->_action) {
            case 'save_continue': return redirect()->route(\str_replace('.update', '.edit', $routename), $params);
        }

        return redirect()->route(\str_replace('.update', '.index', $routename), $params);
    }

    public function delete(Request $request)
    {
        $params = \Route::current()->parameters();
        $model = $this->getModel();
        $id = $params[$this->getPrimaryKey()];
        $row = $model->findOrFail($id);
        $view = $this->getView();

        return Resource::checkBlock($row) ? SweetAlert::alert('Risorsa bloccata da '.$row->handle) : view($view)->with('row', $row)->with('params', $params);
    }

    // TODO migliorare il destroy, in modo che cancellando una categoria o menu, cancelli ricorsivamente anche tutti i suoi figlio (categorie e non)
    public function destroy(Request $request)
    {
        $params = \Route::current()->parameters();
        $model = $this->getModel();
        $id = $params[$this->getPrimaryKey()];
        $res = $model->destroy($id);
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
            }
            if (isset($container2)) {
                if (\is_object($container2)) {
                    $container2_type = $container2->post_type;
                } else {
                    $container2_type = $container2;
                }
                $view = \str_replace('::container.', '::'.$container_type.'.'.$container1.'.'.$container2.'.', $view);
            }
            $view = \str_replace('::container.', '::'.$container_type.'.', $view);
            $view = \str_replace('.container.', '.'.$container_type.'.', $view);
            $view = \str_replace('blog::', 'pub_theme::', $view);
        }

        if (\Request::ajax()) {
            $view .= '_ajax';
        }
        if ('pub_theme::container.home' == $view) {
            $view = 'pub_theme::index';
        }

        if (\View::exists($view)) {
            return $view;
        } else {
            echo '<h3>'.\Route::currentRouteAction().'</h3>';
            die('<h3>la view ['.$view.'] non esiste </h3>['.__LINE__.']['.__FILE__.']');
        }
    }

    //end getView

    //-------------------------------------------------------------------------------------------
    public function xls(Request $request)
    {
        $params = \Route::current()->parameters(); // "stabi" => "605"  "repar" => "11"  "anno" => "2017"
        \extract($params);
        $model = $this->getModel();
        //dd($model);
        $rows = $model->filter($params);
        $xls_fields = $model->xls_fields;
        $ris = [];
        foreach ($rows->get() as $row) {
            $tmp = [];
            \reset($xls_fields);
            foreach ($xls_fields as $field) {
                $tmp[$field] = (string) $row->$field;
            }
            $ris[] = $tmp;
        }
        //dd($ris);
        $filename = class_basename($model).'_'.\implode('_', $params);

        return ArrayTrait::toXLS(['data' => $ris, 'filename' => $filename]);
    }

    public function pdf(Request $request)
    {
        $params = \Route::current()->parameters(); // "stabi" => "605"  "repar" => "11"  "anno" => "2017"
        \extract($params);
        $model = $this->getModel();
        $rows = $model->filter($params);
        $view = self::getView();
        $content = view($view)
                ->with('rows', $rows)
                ->with('view', $view)
                ->with('params', $params);
        //return $content;
        try {
            $html2pdf = new Html2Pdf('L', 'A4', 'it');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($content->__toString());
            $html2pdf->Output();
        } catch (HTML2PDF_exception $e) {
            echo '<pre>';
            \print_r($e);
            echo '</pre>';
        }
    }

    //end pdf
    //-------
    //

    public function pdfszip(Request $request)
    {
        $params = \Route::current()->parameters(); // "stabi" => "605"  "repar" => "11"  "anno" => "2017"
        \extract($params);
        $model = $this->getModel();
        $rows = $model->filter($params);

        $parz = \array_merge(['name' => class_basename($model), 'cognome' => 'cognome', 'nome' => 'nome', 'id' => 0], $params);
        $zipFileName = str_slug(class_basename($model).'_'.\implode('_', $params).'_'.\Carbon\Carbon::now()).'.zip';
        $zipPath = 'C:/download/pdf/'.$zipFileName;
        $zipper = new \Chumper\Zipper\Zipper();
        $res = $zipper->make($zipPath);

        $view = self::getView();
        $pk = $this->getPrimaryKey();
        //dd($pk);
        foreach ($rows->get() as $row) {
            $parz['cognome'] = $row->cognome;
            $parz['nome'] = $row->nome;
            $parz['id'] = $row->id;
            $filename = str_slug(\implode('_', $parz)).'.pdf';
            $request->request->add([$pk => $row->id]);
            $content_pdf = $this->pdfrow($request, 'content_PDF');
            $res = \Storage::disk('download_pdf')->put($filename, $content_pdf);
            //$res=\Storage::disk('download_pdf')->put($filename, $row->content_PDF($view));
            $filename_path = \Storage::disk('download_pdf')->path($filename);
            //echo '<br/> '.$filename.'<br/>-- '.$filename_path;
            $res_zip = $zipper->add($filename_path);
        }

        $zipper->close();
        
        if (\File::exists($zipPath)) {
            return \Response::download($zipPath);
        } else {
            return '<hr/><h3>File non esiste ['.$zipPath.']</h3>';
        }
    }

    //end pdfszip
    //------------------------------------------------------------------------------------------
    public function pdfrow(Request $request, $out = 'show',$pdforientation = 'L')
    {
        
        $params = \Route::current()->parameters();
        \extract($request->all());
        \extract($params);
        //dd($params);
        $model = $this->getModel();
        $rows = $model->filter($params);
        //dd($rows->toSql());
        //$rows=$model;
        $pk = $this->getPrimaryKey();
        if (isset($params['id_pdf'])) {
            $request->$pk = $params['id_pdf']; //priorita' su path
        }
        //dd($request->$pk);
        $rows = $rows->where('id', $request->$pk);
        //dd($rows->toSql());
        $row = $rows->first();
        // dd($row);
        $view = self::getView();
        $content = view($view)
            ->with('row', $row)
            ->with('view', $view)
            ->with('params', $params);

        //return $content;
        $html=$content->__toString();
        $parz=[
            'pdforientation'=>$pdforientation,
            'html'=>$html,
            'out'=>$out,
            'filename'=>$row->id,
        ];
        return HtmlService::toPdf($parz);  //testing se qualcosa va male si toglie

        try {
            $html2pdf = new Html2Pdf($pdforientation, 'A4', 'it');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($content->__toString());
            if ('content_PDF' == $out) {
                return $html2pdf->Output($row->id.'.pdf', 'S');
            }
            $html2pdf->Output();
        } catch (HTML2PDF_exception $e) {
            echo '<pre>';
            \print_r($e);
            echo '</pre>';
        }
    }

    //-------------------------------------------------------------------------------------------
    //*
    public function filtersendmail(Request $request)
    {
        //return 'UNDER COSTRUCTION, ';
        $params = \Route::current()->parameters();
        \extract($params);
        $data = $request->all();
        //echo '<pre>';print_r($data);echo '</pre>';
        if ('POST' == $request->_method) {
            //die('['.__LINE__.']['.__FILE__.']');
            return $this->do_filtersendmail($request);
        }
        $model = $this->getModel();
        $rows = $model->filter($params);
        $rows = $rows->whereRaw($model->sendmail_where);
        $rows = $rows->get();
        //echo '<pre>';print_r($rows->toArray());echo '</pre>';
        $view = self::getView();

        return view($view)->with('rows', $rows)->with('params', $params);
    }

    //end filtersendmail
    //----------------------------------------------------------------------------------------
    public function do_filtersendmail(Request $request)
    {
        //echo '<h3>do_filtersendmail</h3>';
        $model = $this->getModel();
        $mail_class = $model->mail_class;
        $to = $request->to;
        foreach ($to as $v) {
            $row = $model->find($v);
            // $attach=$this->pdfrow()
            if ('' == $row->email) {
                return '<h3>Manca la mail a '.$row->id.' '.$row->cognome.' '.$row->nome.'</h3>';
            }
            $emails = [$row->email, 'sottanamarco@provincia.treviso.it', 'mpesce@provincia.treviso.it'];
            //$emails=['sottanamarco@provincia.treviso.it'];//4 debugging
            //
            //------ mail ---
            \Illuminate\Support\Facades\Mail::to($emails)->send(new $mail_class($row));
        }

        return '<h3>Inviate con successo </h3>';
    }

    //---------------------------------------------------------------------------------------
    //*/

    public static function action(Request $request)
    {
        $routename = \Route::current()->getName();
        $rotename_arr=explode('.',$routename);
        $rotename_arr=array_slice($rotename_arr,0,-1);
        $routename_base=implode('.',$rotename_arr);
        //ddd($rotename_arr);
        $params = \Route::current()->parameters();
        $data = $request->all();
        switch ($data['_action']) {
            case 'save_continue':
                $routename = $routename_base.'.edit';
                /*
                //ddd($request->route($routename)->paremeters());
                if (!isset($params['item0'])) {
                    $params['item0'] = $empty;
                }
                if (!isset($params['item1'])) {
                    $params['item1'] = $empty;
                }
                if (!isset($params['item2'])) {
                    $params['item2'] = $empty;
                }
                if (!isset($params['item3'])) {
                    $params['item3'] = $empty;
                }
                */
                return redirect()->route($routename, $params);
                break;
            case 'save_close':
                $routename = $routename_base.'.index';
                return redirect()->route($routename, $params);
                break;
            case 'come_back':
                return redirect()->back();
                break;
            case 'row':
                return $empty;
                break;
            default:
                echo '<h3>['.__LINE__.']['.__FILE__.']</h3>';
                ddd($data['_action']);
                break;
        }//end switch
    }//end function

}//end class
