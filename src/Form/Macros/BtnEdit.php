<?php
namespace XRA\Extend\Form\Macros;
use Illuminate\Support\Facades\Request;
//use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;
//----- services -----
use XRA\Extend\Services\ThemeService;


class BtnEdit{
	public function __invoke(){
        return function ($extra, $from='index', $to='edit') {
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
        }; //end function
	}//end invoke
}//end class