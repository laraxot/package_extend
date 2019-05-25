<?php
namespace XRA\Extend\Form\Macros;
//use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;


class BtnClone{
	public function __invoke(){
        return function ($extra, $from='index', $to='edit' ) {
    $params=\Route::current()->parameters();
    $params=array_merge($params, $extra);
    $params['replicate']=1;
    $route=route(str_replace('.'.$from, '.'.$to, Request::route()->getName()), $params);
    return '<a class="btn btn-small btn-warning" href="'.$route.'"  data-toggle="tooltip" title="Duplica">
    <i class="fa fa-clipboard fa-fw far fa-clone" aria-hidden="true"></i></a>';
}; //end function
	}//end invoke
}//end class