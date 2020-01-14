<?php



namespace XRA\Extend\Traits;

use Illuminate\Http\Request;
//----xot library---
use XRA\Extend\Library\XmlMenu_op;

trait TreeTrait
{
    public function index(Request $request)
    {
        if ($request->act=='routelist') {
            return ArtisanTrait::exe('route:list');
        }
        $model = $this->getModel();
        $lang = Enteweb::getLang();
        $rows = $model->where('id_tbl_lingua', $lang)->where('deleted_at', null)->get();
        $href_create = null;
        $params = \Route::current()->parameters();
        $titolo = $this->getTitle();
        if (null != $this->getHrefTitle()) {
            $href_create = route($this->getHrefTitle(), $params);
        }
        $view = $this->getView();
        $plugin = $this->getViewAlias();

        return view($plugin.$view)->with('href_create', $href_create)->with('titolo', $titolo)->with('rows', $rows);
    }

    public function getView()
    {
        $tmp = \Route::current()->getName();
        $tmp = \explode('.', $tmp);
        \array_shift($tmp);
        $view = \implode('.', $tmp);

        return 'admin.'.$view;
    }

    //    function getNodesAng($id_padre = 0)
    //    {
    //        $params = \Route::current()->parameters();
    //        $model = $this->getModel();
    //        $table = $model->getTable();
    //        $data = $model->search($params)->get()->toArray();
    //        $nodeconf_dir = base_path('/packages/Enteweb/_contextmenu/Aree/tbl_aree/node_conf.xml');
    //        $node = Xml_op::fileXmlToArray($nodeconf_dir);
    //
    //        $root = $data;
    //        $ris = [];
    //        foreach ($root as $k => $v) {
    //            $tmp = [];
    //            $tmp['id'] = $table . '.' . $v['id'];
    //            $tmp['text'] = $v['nome'] . ' ';
    //            if ($v['visible'] == 1)
    //                $tmp['icon'] = $node['iconPub'];
    //            else
    //                $tmp['icon'] = $node['iconNoPub'];
    //            $tmp['children'] = false;
    //            $tmp['state'] = [];
    //            $tmp['state']['opened'] = false;
    //            $tmp['state']['selected'] = false;
    //            $tmp['state']['tablename'] = $table;
    //            $tmp['children'] = false; //$this->getNodesAng($request,$v['id']);
    //            $ris[] = $tmp;
    //        }
    //        return $ris;
    //    }

    public function getNodes(Request $request, $id_padre = 0)
    {
        $params = \Route::current()->parameters();
        $model = $this->getModel();

        $tree_id = $model->getTable().'.0';
        if ($request->has('id') and '#' != $request->id) {
            $tree_id = $request->id;
        }
        list($tbl, $id_padre) = \explode('.', $tree_id);

        $ris = [];
        if (0 == $id_padre) {
            $firstchilds = $model->firstChilds($params);
            $childs = ['menu' => $firstchilds];
        } else {
            $padre = $model->find($id_padre);
            if (null == $padre) {
                return $ris;
            }
            $childs = $padre->getChilds();
        }

        \reset($childs);

        foreach ($childs as $tipo => $data) {
            foreach ($data->get() as $k => $v) {
                $prefix = $v->getTable();
                $nameLabel = $this->getNameLabel($prefix);
                $tmp = [];
                $tmp['id'] = $prefix.'.'.$v->id;
                $tmp['text'] = $v->$nameLabel.' '; //alcuni hanno nome, altri titolo
                $tmp['icon'] = $v->getTreeIcon();
                $tmp['children'] = $v->hasChilds();
                $tmp['state'] = [];
                $tmp['state']['opened'] = false;
                $tmp['state']['selected'] = false;
                $tmp['state']['tablename'] = $prefix;

                $ris[] = $tmp;
            }
        }

        return $ris;
    }

    public function getContextMenu(Request $request)
    {
        $params = \Route::current()->parameters();
        $tmp = \debug_backtrace();
        $name = \explode('\\', $tmp[0]['class']);
        $name_dir = \ucwords($name[\count($name) - 4]);
        //echo '<pre>'; print_r($name); echo '</pre>';die();

        \extract($request->all());
        //echo '<pre>';print_r($request->input('node'));echo '</pre>';
        if ($request->has('node')) {
            $node = $request->input('node');
            $tablename = $node['state']['tablename'];
        } else {
            $node = ['id' => 3];
            $tablename = 'tbl_page'; //4 testing !
        }
        $contextmenu_dir = base_path('packages\Enteweb\\'.$name_dir.'\src\_contextmenu\\'.$tablename);
        $contextmenu_dir = \str_replace('\\', \DIRECTORY_SEPARATOR, $contextmenu_dir);
        $contextmenu_dir = \str_replace('/', \DIRECTORY_SEPARATOR, $contextmenu_dir);
        $menu = [];
        $menu = XmlMenu_op::dirXML2Obj($contextmenu_dir);
        //echo '<pre>';print_r($menu); echo '<pre>';
        $str = 'items={'.\chr(13);
        foreach ($menu as $k => $v) {
            $url3 = '#';
            if ('' != $v->act) {
                $tmp = \explode('.', $node['id']);
                $sk = 'id_'.\str_replace('tbl_', '', $tmp[0]); // da predisporre traduzione ovvero solito array..
                $params[$sk] = $tmp[1];
                $url3 = @route($v->obj.'.'.$v->act, $params);
            }
            $str .= '"Objects_'.$k.'": {
                           "separator_before": false,
                           "separator_after": false,
                           "icon":"'.$v->icons.' icon-state-info",
                           "label": "'.$v->nome.'",
                           "action": function (obj) {
                              var url3="'.$url3.'";
                               console.log("Objects : "+url3);
                               loadIContent(url3,\'#icontent\');
                           }
                       },'.\chr(13);
        }
        $str .= '};';

        return $str;
    }
}
