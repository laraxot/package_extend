@php
    $params=\Route::current()->parameters();
    $routename=\Route::currentRouteName();
    extract($params);
    $route_arr=[];
    if($routename!=null){
        $route_arr=explode('.',$routename);
        foreach($route_arr as $k=>$v){
            if(in_array($v,array_keys($params))){
                if(is_object($params[$v])){
                    $route_arr[$k]=$params[$v]->post_type;
                }else{
                    $route_arr[$k]=$params[$v];
                }
            }
        }
    }
    $routename=implode('.',$route_arr);
    if($routename==''){
        $routename='index';
    }
    $lang=$view.'.field';//'pub_theme::'.$routename;
    $view_path=dirname(\View::getFinder()->find('extend::includes.components.form.text')); //devo dargli una view esistente di partenza
    $blade_component='components.blade.input';
    if(in_admin()){
        $blade_component='backend::includes.'.$blade_component;
    }else{
        $blade_component='pub_theme::layouts.'.$blade_component;
    }
    if(!File::exists($view_path.'/_components.json')){
        $files = File::allFiles($view_path);
        $comps=[];
        foreach($files as $file){
            $filename=$file->getRelativePathname();
            //ddd($file->getPath());///home/vagrant/code/food/laravel/packages/XRA/Extend/src/views/includes/components/form"
            $ext='.blade.php';
            if(ends_with($filename,$ext)){
                $base=substr(($filename),0,-strlen($ext));
                $name=str_replace(DIRECTORY_SEPARATOR,'_',$base);
                $name='bs'.studly_case($name);

                //$comp_view=str_replace('/','.',$base);
                //echo '<br/>'.$base.'  --- '.$name.'  --  '.$comp_view;
                $comp_view=str_replace(DIRECTORY_SEPARATOR,'.',$base);
                //echo '<br/>'.$base.'  --- '.$name.'  --  '.$comp_view;
                $comp_view='extend::includes.components.form.'.$comp_view;
                //echo '<br/> Component ['.$name.']['.xdebug_time_index().']';
                $comp=new \StdClass();
                $comp->name=$name;
                $comp->view=$comp_view;
                $comp->base=$base;
                $comp->dir=$file->getPath();
                $comps[]=$comp;
                /*
                Form::component($name, $comp_view,
                    ['name', 'value' => null,'attributes' => [],'lang'=>$lang,
                    'comp_view'=>$comp_view,
                    'comp_dir'=>$file->getPath(),
                    'blade_component'=>$blade_component]);
                */
            }
        }
        //ddd($comps);
        File::put($view_path.'/_components.json',json_encode($comps));
    }else{
        $comps=File::get($view_path.'/_components.json');
        $comps=json_decode($comps);
    }
    foreach($comps as $comp){
        Form::component($comp->name, $comp->view,
                    ['name', 'value' => null,'attributes' => [],'lang'=>$lang,
                    'comp_view'=>$comp->view,
                    'comp_dir'=>$comp->dir,
                    'blade_component'=>$blade_component]);
    }




    //---------- ShortCuts -------
    /*
    Form::component('bsHtml5UploadImg', 'extend::includes.components.form.html5upload.upload',
        ['name', 'value' => null, 'attributes' => [],'lang'=>$lang,'comp_view'=>$comp_view,'blade_component'=>$blade_component]);
    */
Form::macro('bsOpen', function ($model, $from, $to='', $params = null, $formName="theForm") {
    if ($params == null) {
        $params=\Route::current()->parameters();
    }
    $req_params=\Request::all();

    //if(is_array($req_params)) $params=array_merge($req_params,$params);

    //dd($params);

    if ($to=='') {
        $to=$from;
        switch ($to) {
            case 'update': $from='edit'; break;
            case 'store': $from='create'; break;
        }
    }

    $routename=Request::route()->getName();
    $routename=str_replace('.'.$from, '.'.$to, $routename);

    $route=route($routename, $params);

    $parz=array_merge([$routename], array_values($params));
    switch ($to) {
        case 'store':
            $method='POST';
        break;
        case 'update':
            $method='PUT'; //PUT/PATCH
        break;
        case 'destroy':
            $method='DELETE';
        break;
        default:
            $method='POST';
        break;
    }
    if (isset($params['method'])) {
        $method=$params['method'];
    }



    return Form::model($model, [
    'route' => $parz,
    'name' => $formName,
    'id' => $formName
    //'action' => $route
    ])
    //.csrf_field()
    .method_field($method);
});

Form::macro('bsBtnCrud', function ($extra) {
    $btns='';
    $btns.=Form::bsBtnEdit($extra);
    $btns.=Form::bsBtnDelete($extra);
    return $btns;
});
Form::macro('bsBtnEdit', function ($extra, $from='index', $to='edit') {
    $user=\Auth::user();
    if($user==null) return '';
    extract($extra);
    if (!$user->can('edit', $row)){
        return '[not can edit '.get_class($row).']';
    }
    //return '['.$row->title.']['.isset($row->pivot).']';
    $route=$row->edit_url;
    //ddd($route);
    if($route==null){
        $params=\Route::current()->parameters();
        $params=array_merge($params, $extra);
        $routename=Request::route()->getName();
        $routename=str_replace('.index_edit', '.index',$routename );
        //echo '<h3>'.$routename.'</h3>';
        $route=route(str_replace('.'.$from, '.'.$to, $routename), $params);
    }
    $class='btn btn-small btn-info';
    if (isset($extra['class'])) {
        $class.=' '.$extra['class'];
    }
    return '<a class="'.$class.'" href="'.$route.'" data-toggle="tooltip" title="Modifica"><i class="fa fa-pencil fa-fw" aria-hidden="true"></i></a>';
});


Form::macro('bsBtnClone', function ($extra, $from='index', $to='edit' /*$to='replicate' */) {
    $params=\Route::current()->parameters();
    $params=array_merge($params, $extra);
    $params['replicate']=1;
    $route=route(str_replace('.'.$from, '.'.$to, Request::route()->getName()), $params);
    return '<a class="btn btn-small btn-warning" href="'.$route.'"  data-toggle="tooltip" title="Duplica"><i class="fa fa-clipboard fa-fw" aria-hidden="true"></i></a>';
});

Form::macro('bsBtnDelete', function ($extra) {
    $user=\Auth::user();
    if($user==null) return '';
    extract($extra);

    if (!$user->can('delete', $row)){
        return '[not can delete]['.class_basename($row).']';
    }
    //return '['.$row->destroy_url.']';
    //$theme=\App\Services\ThemeService::class;
    $routename=Request::route()->getName();
    $routename=str_replace('.index_edit', '.destroy',$routename );
    $routename=str_replace('.index', '.destroy',$routename );

    //-----------
    $id=$row->getKey();
    //$id=array_values($extra)[0];
    $params=\Route::current()->parameters();
    $params=array_merge($params, $extra);
    $params['routename']='';unset($params['routename']);
    $route=route($routename, $params);

    //Theme::add('/theme/bc/jquery/dist/jquery.min.js');
    Theme::add('theme/bc/sweetalert2/dist/sweetalert2.min.js');
    Theme::add('theme/bc/sweetalert2/dist/sweetalert2.min.css');
    Theme::add('extend::js/btnDeleteX2.js');

    /*-- sweet alert 1 --
    Theme::add('/theme/bc/sweetalert/dist/sweetalert.css');
    Theme::add('/theme/bc/sweetalert/dist/sweetalert.min.js');
    Theme::add('/theme/js/btnDeleteX.js');
    */
    $class='btn btn-small btn-danger';
    if (isset($extra['class'])) {
        $class.=' '.$extra['class'];
    }
    return '<a class="'.$class.'" href="#" data-token="'. csrf_token() .'" data-id="'.$id.'" data-href="'.$row->destroy_url.'?id='.$id.'" data-toggle="tooltip" title="Cancella">
        <i class="fa fa-trash-o fa-fw" aria-hidden="true"></i>
    </a>';
});

Form::macro('bsBtnDetach', function ($extra) {
    $routename=Request::route()->getName();
    $routename=str_replace('.index', '.destroy',$routename );
    extract($extra);
    //-----------
    $id=$row->getKey();
    $params=\Route::current()->parameters();
    $params=array_merge($params, $extra);
    $params['routename']='';unset($params['routename']);
    $route=route($routename, $params);
    Theme::add('theme/bc/sweetalert2/dist/sweetalert2.min.js');
    Theme::add('theme/bc/sweetalert2/dist/sweetalert2.min.css');
    Theme::add('extend::js/btnDeleteX2.js');
    $class='btn btn-small btn-danger';
    if (isset($extra['class'])) {
        $class.=' '.$extra['class'];
    }
    return '<a class="'.$class.'" href="#" data-token="'. csrf_token() .'" data-id="'.$id.'" data-href="'.$row->detach_url.'?id='.$id.'" data-toggle="tooltip" title="Detach">
        <i class="fa fa-unlink fa-fw" aria-hidden="true"></i>
    </a>';
});


Form::macro('bsBtnUnlink', function ($extra=[]) {
    $theme=\App\Services\ThemeService::class;
    extract($extra);
    //-----------
    //$id=array_values($extra)[0];
    $id=$row->id;
    $route=$row->detach_url;
    if($route==null){
        $params=\Route::current()->parameters();
        $params=array_merge($params, $extra);
        $route=route(str_replace('.index', '.detach', Request::route()->getName()), $params);
    }
    //Theme::addStyle('/theme/bc/sweetalert/dist/sweetalert.css');
    //Theme::addScript('/theme/bc/jquery/dist/jquery.min.js');
    //Theme::addScript('/theme/bc/sweetalert/dist/sweetalert.min.js');
    //Theme::addScript('/theme/pub/js/btnDetachX.js');
    Theme::add('theme/bc/sweetalert2/dist/sweetalert2.min.js');
    Theme::add('theme/bc/sweetalert2/dist/sweetalert2.min.css');
    Theme::add('extend::js/btnDeleteX2.js');

    $class='btn btn-small btn-danger';
    if (isset($extra['class'])) {
        $class.=' '.$extra['class'];
    }
    return '<a class="'.$class.'" href="#" data-token="'. csrf_token() .'" data-id="'.$id.'" data-href="'.$route.'" data-toggle="tooltip" title="Scollega">
            <i class="fa fa-unlink fa-fw" aria-hidden="true"></i>
            </a>';
});

Form::macro('bsBtnUpDown',function ($extra){
    extract($extra);
    //$params=\Route::current()->parameters();
    //$params=array_merge($params, $extra);
    //$routename=Request::route()->getName();
    return '<a href="'.$row->moveup_url.'" class="btn btn-xs btn-warning">
            <i class="fa fa-arrow-up"></i>
    </a>
    <a href="'.$row->movedown_url.'" class="btn btn-xs btn-warning">
        <i class="fa fa-arrow-down"></i>
    </a>';
});



Form::macro('bsBtnCreate', function ($extra=[]) {
    $user=\Auth::user();
    if($user==null) return '';
    //*
    $params=\Route::current()->parameters();
    $row=last($params);
    if (!$user->can('create', $row)){
        return '[not can create]';
    }
    //*/
     //---default var ---
     //$txt='Nuova ';
     $params=[];
     extract($extra);
     //ddd($row->create_url);
     if($row->create_url!=''){
        $route=$row->create_url;
    }else{
        $routename=Request::route()->getName();
        $routename=str_replace('.index', '.create', $routename);
        $routename=str_replace('.index_edit', '.create', $routename);
        $params=array_merge(\Route::current()->parameters(), $params);
        $route=route($routename, $params);
    }

     $class='btn btn-small btn-info btn-xs';
     if (isset($extra['class'])) {
         $class.=' '.$extra['class'];
     }
     return '<a class="'.$class.'" href="'.$route.'" data-toggle="tooltip" title="Create">
     <i class="fa fa-plus-square fa-fw" aria-hidden="true"></i>&nbsp;'.$txt.'</a>';
 });

@endphp
