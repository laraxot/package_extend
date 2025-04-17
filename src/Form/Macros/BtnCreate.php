<?php
namespace XRA\Extend\Form\Macros;
use Illuminate\Support\Facades\Request;
//use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;
//----- services -----
use XRA\Extend\Services\ThemeService;


class BtnCreate{
	public function __invoke(){
		return function ($extra=[]) {
			$user=\Auth::user();
			if($user==null) return '';
			//*
			$params=\Route::current()->parameters();
			
			//*/
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
		 }; //end function
	}//end invoke
}//end class