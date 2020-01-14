<?php
namespace XRA\Extend\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//use File;
//---- services --
use XRA\Extend\Services\ThemeService;
use XRA\Extend\Services\TranslatorService;

class TranslationController extends Controller{

	public function index(Request $request){
		return ThemeService::view();
	}


	public function store(Request $request){
        $data=$request->all();
        $trans=$data['trans'];
        foreach($trans as $k=>$v){
            TranslatorService::set($k, $v);
        }
         if (\Request::ajax()) {
                $response = [
                    'success' => true,
                    //'data'    => $result,
                    'message' => 'OK',
                ];
                $response = \array_merge($data, $response);

                return response()->json($response, 200);
        }
        return redirect()->back();
        
    }
}