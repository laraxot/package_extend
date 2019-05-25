@php
   // use Illuminate\Support\Str;
   // use Illuminate\Support\Collection;



    //---------- ShortCuts -------
    /*
    Form::component('bsHtml5UploadImg', 'extend::includes.components.form.html5upload.upload',
        ['name', 'value' => null, 'attributes' => [],'lang'=>$lang,'comp_view'=>$comp_view,'blade_component'=>$blade_component]);
    */

//$tmp=\File::glob('extend::Form/Macros');

    //$tmp=ThemeService::packNamespacePath('extend');
//        $tmp=extend::getPath();




/*
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

    $act=$to.'_url';
    $route=$model->$act;

    if($route==''){
        $routename=Request::route()->getName();
        $routename=str_replace('.'.$from, '.'.$to, $routename);
        $route=route($routename, $params);
    }

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



    //$parz=array_merge([$routename], array_values($params));
    return Form::model($model, [
    //'route' => $parz,
    'url' => $route,
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
    return '<a class="'.$class.'" href="'.$route.'" data-toggle="tooltip" title="Modifica">
    <i class="fa fa-pencil fa-fw far fa-edit" aria-hidden="true"></i></a>';
});


Form::macro('bsBtnClone', function ($extra, $from='index', $to='edit' ) {
    $params=\Route::current()->parameters();
    $params=array_merge($params, $extra);
    $params['replicate']=1;
    $route=route(str_replace('.'.$from, '.'.$to, Request::route()->getName()), $params);
    return '<a class="btn btn-small btn-warning" href="'.$route.'"  data-toggle="tooltip" title="Duplica">
    <i class="fa fa-clipboard fa-fw far fa-clone" aria-hidden="true"></i></a>';
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

    $class='btn btn-small btn-danger';
    if (isset($extra['class'])) {
        $class.=' '.$extra['class'];
    }
    return '<a class="'.$class.'" href="#" data-token="'. csrf_token() .'" data-id="'.$id.'" data-href="'.$row->destroy_url.'?id='.$id.'" data-toggle="tooltip" title="Cancella">
        <i class="fa fas fa-trash-alt fa-trash-o fa-fw" aria-hidden="true"></i>
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
    
    $params=\Route::current()->parameters();
    
    
     //---default var ---
    $row=last($params);
    $txt='Nuova ';
    $params=[];
    extract($extra);

    if (is_object($row) && !$user->can('create', $row)){
        return '[not can create]['.get_class($row).']';
    }
     //ddd($row->create_url);
    if(is_object($row) && $row->create_url!=''){
        $route=$row->create_url;
    }else{
        $routename=Request::route()->getName();
        if($routename==null){
            $params['container0']=$row->post_type;
            $routename='container0.index';
        }
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

Form::macro('bsBtnCreateRelated', function ($parz) {
    $user=\Auth::user();
    if($user==null) return ''; // se non loggato manco il tasto
    //--- default vars
    $txt = 'Add';
    extract($parz);
    if (!$user->can('create', $related)){
        return '[not can create]';
    }
    $params=\Route::current()->parameters();
    $routename=$row->routename;
    
    if($routename==''){
        $routename=Request::route()->getName();
        if($routename==''){
            $routename='container0.index';
        }
    }
    $routename_arr=explode('.',$routename);
    $act=collect($routename_arr)->last();
    $route_no_act=array_slice($routename_arr,0,-1);
    $piece=count($route_no_act);
    $container_curr='container'.($piece-1);
    $item_curr='item'.($piece-1);
    $container_next='container'.($piece);
    $route_create=implode('.',$route_no_act).'.'.$container_next.'.create';
    $params[$container_curr]=$row->post_type;
    $params[$item_curr]=$row;
    $params[$container_next]=$related->post_type;
    $url=route($route_create,$params);
    $class='btn btn-secondary';
    return '<a class="'.$class.'" href="'.$url.'" data-toggle="tooltip" title="Create">
     <i class="fa fa-plus fa-fw" aria-hidden="true"></i>&nbsp;'.$txt.'</a>';
    
});
//*/

@endphp
