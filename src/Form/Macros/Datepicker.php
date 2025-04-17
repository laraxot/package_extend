<?php
namespace XRA\Extend\Form\Macros;
use Illuminate\Support\Facades\Request;
//use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;
//----- services -----
use XRA\Extend\Services\ThemeService;


use Carbon\Carbon;


class Datepicker{
	public function __invoke(){
        return function ($label, $default = NULL, $params = []) {
			if (count($params) == 0 || !array_key_exists('class', $params))
				$params['class'] = "";
			if ($default === false) {
				$default = '';
			}
			/*
			elseif (Input::old($label) !== NULL) {
				$default = Input::old($label);
			}
			*/
			else {
				if ($default instanceOf Carbon || $default instanceOf DateTime) {
					$default = $default->format("d/m/Y");
				}
				elseif ($default == NULL || $default == 0) {
					$default = Carbon::today()->format("d/m/Y");
				}
				if ($default == NULL || $default == 0) {
					$default = Carbon::today()->format("d/m/Y");
				}
			}
			$params['class'] .= " date-picker";
			$input = '<div class="input-group"> ';
			$input .= '<input name="' . $label . '" id="' . $label . '" type="text" data-inputmask="\'alias\': \'date\'"  ';
			if ($default !== NULL && strlen((string)$default) > 0)
				$input .= 'value="' . $default . '" ';
			foreach ($params as $key => $value)
				$input .= $key . '="' . $value . '" ';
			$input .= "> ";
			$input .= '<span class="input-group-addon"><i class="mdi mdi-calendar-today"></i></span></div>';
			return $input;
		};//end function 
    }//end invoke
}//end class