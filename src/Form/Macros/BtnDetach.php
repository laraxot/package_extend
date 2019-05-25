<?php
namespace XRA\Extend\Form\Macros;
use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;


class BtnDetach{
	public function __invoke(){
        return function ($extra) {
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
}; //end function
	}//end invoke
}//end class