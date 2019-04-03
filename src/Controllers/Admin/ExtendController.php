<?php



namespace XRA\Extend\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use XRA\Extend\Services\ThemeService;
//--- services
use XRA\Extend\Traits\ArtisanTrait;

class ExtendController extends Controller
{
    public function index(Request $request)
    {
        if ($request->act=='routelist') {
            return ArtisanTrait::exe('route:list');
        }

        return ThemeService::view();
    }

    //end function
 //
}//end class
