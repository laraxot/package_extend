<?php
namespace XRA\Extend\Form\Macros;
use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;


class Open{
	public function __invoke(){
        return function ($model, $from, $to='', $params = null, $formName="theForm") {
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
        $routename=\Request::route()->getName();
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
};//end function 
    }//end invoke
}//end class