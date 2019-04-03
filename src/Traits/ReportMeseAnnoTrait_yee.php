<?php
namespace App\Traits;

// /laravel/app/Updater.php
use Illuminate\Http\Request;
//use XRA\Extend\Library\Resource;
//use XRA\Extend\Library\SweetAlert;
use Xot\Sigma\Models\Anag;
//---------- models -----------
use Xot\Trasferte\Models;
use XRA\Extend\Traits\RouteTrait;
//------services---------
use XRA\Extend\Services\ThemeService;

trait ReportMeseAnnoTrait_yee
{
    //----------------------------------
    public function index(Request $request)
    {
        $data = $request->all();
        $params = \Route::current()->parameters();
        $model = $this->getModel();

        //$params=array_merge($request->all(),);
        if (!isset($params['mese'])) {
            $params['mese'] = \date('m');
        }
        if (!isset($params['anno'])) {
            $params['anno'] = \date('Y');
        }
        $params['tipo'] = 1;
        $rows = $model->search($params);
        //echo '<pre>';print_r($rows->get()->toArray());echo '</pre>';die('['.__LINE__.']['.__FILE__.']');

        $data = $rows->orderBy('nome', 'asc')->get()->toArray();

        $subtot = ArrayTrait::array_subtotale(['data' => $data, 'key' => ['ente', 'matr'], 'add' => ['distanza', 'km_effettivi', 'km_corretti', 'rimb_spese_viaggi_km', 'rimb_spese_viaggi_aut_e_varie'], 'fields' => ['nome', 'ente', 'matr']]);
        // echo '<pre>';print_r($subtot);echo '</pre>';die();

        $data = ArrayTrait::array_raggruppa(['data' => $data, 'key' => ['ente', 'matr']]);
        //echo '<pre>';print_r($data);echo '</pre>';

        $nomi = $data;
        \reset($data);
        foreach ($data as $k => $v) {
            $data[$k] = ArrayTrait::array_raggruppa(['data' => $v, 'key' => ['last_stato']]);
        }
        //echo '<pre>';print_r($data);echo '</pre>';
        $stati = Approvaz::all();

        if (isset($params['matr'])) {
            //$view='admin.trasferte.fuorisede.searchEnteMatrMeseAnno';
            $view = RouteTrait::getView();
            $view = \str_replace('.index', '.searchEnteMatrMeseAnno', $view);
            //echo '<pre>';print_r($rows->toArray());echo '</pre>';
            $anag = Anag::where('ente', $params['ente'])
        ->where('matr', $params['matr'])
        ->first();

            return view($view)
             ->with('rows', $rows->get())->with('stati', $stati)
             ->with('subtot', $subtot)->with('params', $params)
             ->with('anag', $anag);
        }
        //else{
        //    $view='admin.trasferte.fuorisede.search';
        //}

        $view = RouteTrait::getView();

        return view($view)
             ->with('rows', $data)->with('stati', $stati)
             ->with('subtot', $subtot)->with('params', $params);
    }

    //-------------------------------------
}
