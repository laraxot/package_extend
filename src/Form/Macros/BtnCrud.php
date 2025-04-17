<?php
namespace XRA\Extend\Form\Macros;
use Illuminate\Support\Facades\Request;
//use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;
//----- services -----
use XRA\Extend\Services\ThemeService;


class BtnCrud{
	public function __invoke(){
        return function ($extra) {
    		$btns='';
    		$btns.=Form::bsBtnEdit($extra);
    		$btns.=Form::bsBtnDelete($extra);
    		return $btns;
		}; //end function
	}//end invoke
}//end class