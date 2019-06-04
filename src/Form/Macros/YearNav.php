<?php
namespace XRA\Extend\Form\Macros;
//use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;


use Carbon\Carbon;


class YearNav{
	public function __invoke(){
        return function ($paramz) {
			$routename=\Route::currentRouteName();
			extract($paramz);
			$params=\Route::current()->parameters();
			if (isset($params['anno'])) {
				$anno=$params['anno'];
			} else {
				$anno=date('Y');
			}
			$time=mktime(0, 0, 0, 1, 1, $anno);
			$time_prev=mktime(0, 0, 0, 1, 1, $anno-1);
			$time_next=mktime(0, 0, 0, 1, 1, $anno+1);
			$parz=$params;
			$parz['anno']=$anno;
			
			$route_curr=route($routename, $parz);
			$parz['anno']=date('Y', $time_prev);
			$route_prev=route($routename, $parz);
			$parz['anno']=date('Y', $time_next);
			$route_next=route($routename, $parz);
			
			$html='<nav aria-label="year_nav">
			<ul class="pager pagination justify-content-center">
			<li class="previous page-item"><a class="page-link" href="'.$route_prev.'">&laquo;'.($anno-1).'</a></li>
			<li class="current page-item active"><a class="page-link" href="'.$route_curr.'">'.($anno).' </a></li>
			<li class="next page-item"><a class="page-link" href="'.$route_next.'">'.($anno+1) .' &raquo; </a></li>
			</ul>
			</nav>';

			return $html;
		};//end function 
    }//end invoke
}//end class 