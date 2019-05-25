<?php
namespace XRA\Extend\Form\Macros;
use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;


class BtnDelete{
	public function __invoke(){
        return function ($extra) {
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
}; //end function
	}//end invoke
}//end class