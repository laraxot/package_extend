<?php
namespace XRA\Extend\Form\Macros;
use Illuminate\Support\Facades\Request;
//use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;
//----- services -----
use XRA\Extend\Services\ThemeService;


class BtnUnlink{
	public function __invoke(){
        return function ($extra=[]) {
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
}; //end function
	}//end invoke
}//end class