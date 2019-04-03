<?php
namespace XRA\Extend\Traits;

use Illuminate\Http\Request;
use XRA\Extend\Library\Array_op;
//--- services
use XRA\Extend\Services\ThemeService;

//------models ----------
//-- no models e' un trait

trait ReportMeseAnnoTrait
{
    public function index(Request $request)
    {
        $model = $this->getModel();
        $params = \Route::current()->parameters();
        if (isset($params['matr']) && '' != $params['matr']) {
            return $this->dettaglio($request);
        }
        if (!isset($params['mese'])) {
            $params['mese'] = \date('m');
        }
        if (!isset($params['anno'])) {
            $params['anno'] = \date('Y');
        }
        $params['tipo'] = 1;
        $rows = $model->filter($params)->get();
        $rows = $rows->toArray();
        //echo '['.__LINE__.']['.__FILE__.']<pre>';print_r($rows);echo '</pre>';
        $rows1 = Array_op::array_raggruppa(['data' => $rows, 'key' => ['ente', 'matr']]);
        \reset($rows1);
        foreach ($rows1 as $k => $v) {
            $rows1[$k] = Array_op::array_raggruppa(['data' => $v, 'key' => ['last_stato']]);
        }

        //echo '['.__LINE__.']['.__FILE__.']<pre>';print_r($rows1);echo '</pre>';
        $subtot = Array_op::array_subtotale([
        'data' => $rows, 'key' => ['ente', 'matr'], 'add' => $model->getAddFields(), 'fields' => ['nome'],
    ]);

        //echo '['.__LINE__.']['.__FILE__.']<pre>';print_r($subtot);echo '</pre>'; die();

        $view = ThemeService::getView();

        return view($view)
            ->with('rows', $rows1)
            ->with('params', $params)
            ->with('stati', $model->stati())
            ->with('subtot', $subtot)
            ;
    }

    //end index
    //--------------------------------------------------
    public function dettaglio(Request $request)
    {
        $model = $this->getModel();
        $params = \Route::current()->parameters();
        \extract($params);
        $params['tipo'] = 1;
        $rows = $model->filter($params);
        //echo '<br/>count: '.$rows->get()->count();
        //echo '<br/>sql: '.$rows->toSql();
        //dd($model);
        $view = ThemeService::getView();
        $view = \str_replace('.search.index', '.searchEnteMatrMeseAnno', $view);

        return view($view)
            ->with('rows', $rows->get())
            ->with('params', $params)
            ->with('stati', $model->stati())
            ->with('anag', $rows->first()->Anag()->first())
            ;
    }

    //--------------------------------------------------
}//end trait ReportMeseAnnoTrait
