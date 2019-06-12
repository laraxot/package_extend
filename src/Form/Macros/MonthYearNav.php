<?php
namespace XRA\Extend\Form\Macros;
use Illuminate\Support\Facades\Request;
//use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;
//----- services -----
use XRA\Extend\Services\ThemeService;


use Carbon\Carbon;


class MonthYearNav{
	public function __invoke(){
        return function ($paramz) {
			$routename=\Route::currentRouteName();
			extract($paramz);
			$params=\Route::current()->parameters();
			if (isset($params['mese'])) {
				$mese=$params['mese'];
			} else {
				$mese=date('m');
			}
			if (isset($params['anno'])) {
				$anno=$params['anno'];
			} else {
				$anno=date('Y');
			}
			$time=mktime(0, 0, 0, $mese, 1, $anno);
			$time_prev=mktime(0, 0, 0, $mese-1, 1, $anno);
			$time_next=mktime(0, 0, 0, $mese+1, 1, $anno);
			$parz=$params;
			$parz['mese']=$mese;
			$parz['anno']=$anno;
			//echo '<pre>';print_r($parz);echo '</pre>';die();
			$route_mese_curr=route($routename, $parz);
			$parz['mese']=date('m', $time_prev);
			$parz['anno']=date('Y', $time_prev);
			$route_mese_prev=route($routename, $parz);
			$parz['mese']=date('m', $time_next);
			$parz['anno']=date('Y', $time_next);
			$route_mese_next=route($routename, $parz);

			$mese_curr=\Carbon\Carbon::createFromDate($anno, $mese, 1);

			$mese_prev=\Carbon\Carbon::createFromDate($anno, $mese-1, 1);

			$mese_next=\Carbon\Carbon::createFromDate($anno, $mese+1, 1);

			$html='<ul class="pager">
			<li class="previous"><a href="'.$route_mese_prev.'">&laquo;'.$mese_prev->formatLocalized('%B %Y').'</a></li>
			<li class="current"><a href="'.$route_mese_curr.'">'.$mese_curr->formatLocalized('%B %Y').' </a></li>
			<li class="next"><a href="'.$route_mese_next.'">'.$mese_next->formatLocalized('%B %Y') .' &raquo; </a></li>
			</ul>';
			return $html;
		};//end function 
    }//end invoke
}//end class 