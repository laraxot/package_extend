<?php


class TREE_OP extends baseController
{
    //------------------------------------------------------
    public function get_tree_multi($params)
    {
        //ddd($params);
        //ddd('qui');
        if (!isset($_POST['node'])) {
            $_POST['node'] = ['id' => '1']; // serve solo per fare i test
        }
        if (!isset($_SESSION['myMenu'])) {
            $myMenu = $this->registry->menu->__get('myMenu');
        } else {
            $myMenu = $_SESSION['myMenu'];
        }
        if (!isset($myMenu[0])) {
            $myMenu = $this->registry->menu->__get('myMenu');
        }
        if (!isset($_SESSION['menuAncestors'])) {
            $antenati = $this->registry->menu->__get('ancestors');
        } else {
            $antenati = $_SESSION['menuAncestors'];
        }
        if (!isset($antenati[0])) {
            $antenati = $this->registry->menu->__get('ancestors');
        }

        $tree_boss = $antenati[0];
        //$menuparent=ARRAY_OP::array_raggruppa(array('data'=>$myMenu,'key'=>array('id_padre')));
        //$menuFigli=$menuparent[$tree_boss->id];
        //ddd($antenati[0]);
        //ddd($menuFigli);
        if ('showTree' == $antenati[0]->funzione) {
            $tree_top = $antenati[0];
        } else {
            $tree_top = ARRAY_OP::array_searchX(['data' => $myMenu, 'whereAdd' => ['id_padre' => $antenati[0]->id, 'funzione' => 'showTree']]);
        }
        //ddd($tree_top);
        //ddd('qui');
        return $tree_top;
    }

    //-------------------------------------------------
    public function get_tree_top($params)
    {
        if (!isset($_POST['node'])) {
            $_POST['node'] = ['id' => '1']; // serve solo per fare i test
        }
        if (!isset($_SESSION['myMenu'])) {
            $myMenu = $this->registry->menu->__get('myMenu');
        } else {
            $myMenu = $_SESSION['myMenu'];
        }
        if (!isset($myMenu[0])) {
            $myMenu = $this->registry->menu->__get('myMenu');
        }
        if (!isset($_SESSION['menuAncestors'])) {
            $antenati = $this->registry->menu->__get('ancestors');
        } else {
            $antenati = $_SESSION['menuAncestors'];
        }
        if (!isset($antenati[0])) {
            $antenati = $this->registry->menu->__get('ancestors');
        }

        $tree_boss = $antenati[0];
        //$menuparent=ARRAY_OP::array_raggruppa(array('data'=>$myMenu,'key'=>array('id_padre')));
        //$menuFigli=$menuparent[$tree_boss->id];
        //ddd($antenati[0]);
        //ddd($menuFigli);
        if ('showTree' == $antenati[0]->funzione) {
            $tree_top = $antenati[0];
        } else {
            $tree_top = ARRAY_OP::array_searchX(['data' => $myMenu, 'whereAdd' => ['id_padre' => $antenati[0]->id, 'funzione' => 'showTree']]);
        }

        return $tree_top;
    }

    //----------------------------------------------------------------------------------
    public function get_tree_sub($params)
    {
        $tree_top = $this->__get('tree_top');

        $myMenu = $_SESSION['myMenu'];
        //ddd($myMenu);
        $edit = ARRAY_OP::array_searchX(['data' => $myMenu, 'whereAdd' => ['id_padre' => $tree_top->id, 'funzione' => 'editTablename']]);
        if (!isset($edit->id)) {
            $obj = new stdClass();

            return $obj;
        }
        //ddd($edit);
        $tree_sub = ARRAY_OP::array_searchX(['data' => $myMenu, 'whereAdd' => ['id_padre' => $edit->id, 'funzione' => 'showTree']]);
        //ddd($tree_sub);
        return $tree_sub;
    }

    //---------------------------------------------------
    public function getNodes($params)
    {
        $this->registry->template->__set('hideControlPanel', true);
        //echo 'ciao'; return ;
        if (isset($params[0])) {
            $params['_type'] = $params[0];
            unset($params[0]);
        } else {
            $params['_type'] = 'top';
        }
        if (isset($params[1])) {
            $params['_id_tree'] = $params[1];
            unset($params[1]);
        } else {
            $params['_id_tree'] = 0;
        }

        $tree = $this->__get('tree_'.$params['_type']);
        //ddd($params);
        if (null == $tree) {
            \header('Content-Type: application/json');
            $rslt = [];
            echo \json_encode($rslt);

            return;
        }

        if (!isset($tree->get_node_func)) {
            $tree->get_node_func = 'get_nodes';
        }

        $menuCurr = $_SESSION['menuCurr'];
        if (!isset($menuCurr->id)) {
            $menuCurr = $this->registry->menu->__get('menuCurr');
        }
        if (isset($menuCurr['sub_params'])) {
            $params = \array_merge($params, $menuCurr['sub_params']);
        }

        //ddd($tree);
        //echo '<h3>'.$tree->obj.'-> '.$tree->get_node_func.'</h3>'; ddd('qui');

        $rslt = $this->registry->router->esegui($tree->obj, $tree->get_node_func, $params);
        \header('Content-Type: application/json');
        //header('Content-Type: application/json; charset=utf-8');
        $json = \json_encode($rslt);
        if (false === $json) {
            // Avoid echo of empty string (which is invalid JSON), and
            // JSONify the error message instead:
            $json = \json_encode(['jsonError', \json_last_error_msg()]);
            if (false === $json) {
                // This should not happen, but we go all the way now:
                $json = '{"jsonError": "unknown"}';
            }
            // Set HTTP response status code to: 500 - Internal Server Error
            \http_response_code(500);
        }
        echo $json;
    }

    //--------------------------------------------------
    public function getNodes_old($params)
    {
        $this->registry->template->__set('hideControlPanel', true);
        //echo 'ciao'; return ;
        if (isset($params[0])) {
            $params['_type'] = $params[0];
            unset($params[0]);
        } else {
            $params['_type'] = 'top';
        }
        if (isset($params[1])) {
            $params['_id_tree'] = $params[1];
            unset($params[1]);
        } else {
            $params['_id_tree'] = 0;
        }

        //ddd($params);ddd('qui');
        //ddd('qui');
        $node = isset($_GET['id']) && '#' !== $_GET['id'] ? (int) $_GET['id'] : 0;
        $rslt = [];
        //ddd($_SESSION);
        //$menuCurr=$this->registry->menu->__get('menuCurr');
        $menuCurr = $_SESSION['menuCurr'];
        //ddd($menuCurr);
        //$par=$this->registry->menu->__get('menuParent');
        //echo 'ciao'; return ;
        //ddd($par);
        if (isset($menuCurr['sub_params'])) {
            $params = \array_merge($params, $menuCurr['sub_params']);
        }
        if (!isset($_SESSION['menuAncestors'])) {
            $antenati = $this->registry->menu->__get('ancestors');
        } else {
            $antenati = $_SESSION['menuAncestors'];
        }
        if (!isset($antenati[0])) {
            $antenati = $this->registry->menu->__get('ancestors');
            //ddd($antenati);
        }

        //ddd($antenati);

        //$breads=$this->registry->menu->__get('breadcrumb');
        //ddd($breads);

        $tree_boss = $antenati[0];
        //ddd($tree_boss);
        //ddd($menuCurr);
        //ddd($params);
        //ddd($tree_boss);
        if (!isset($tree_boss->get_node_func)) {
            echo '<h3>impostare get_node_func menu '.$tree_boss->id.'</h3>';
            $tree_boss->get_node_func = 'get_nodes';
        }
        $rslt = $this->registry->router->esegui($tree_boss->obj, $tree_boss->get_node_func, $params);
        //ddd($rslt);

        //$rslt[] = array('id' => 11, 'text' => 'pippo', 'children' => true);
        //$rslt[] = array('id' => 12, 'text' => 'pluto', 'children' => true);

        /*
        $temp = $fs->get_children($node);
        $rslt = array();
        foreach($temp as $v) {
        $rslt[] = array('id' => $v['id'], 'text' => $v['nm'], 'children' => ($v['rgt'] - $v['lft'] > 1));
        }
        */
        \header('Content-Type: application/json');
        //header('Content-Type: application/json; charset=utf-8');
        $json = \json_encode($rslt);
        if (false === $json) {
            // Avoid echo of empty string (which is invalid JSON), and
            // JSONify the error message instead:
            $json = \json_encode(['jsonError', \json_last_error_msg()]);
            if (false === $json) {
                // This should not happen, but we go all the way now:
                $json = '{"jsonError": "unknown"}';
            }
            // Set HTTP response status code to: 500 - Internal Server Error
            \http_response_code(500);
        }
        echo $json;
        //ddd($rslt);ddd('qui');
    }

    //------------------------------------------------
    public function show($params)
    {
        $bower_url = $this->registry->template->__get('bower_url');
        $template_url = $this->registry->template->__get('template_url');
        //--------------
        $scripts = [];
        $scripts[] = $bower_url.'/jstree/dist/jstree.js';
        $scripts[] = $template_url.'/js/xot_1jstree.js';
        $this->registry->template->addScripts($scripts);
        //---------------------------------------------------
        $styles = [];
        $styles[] = $bower_url.'/jstree-bootstrap-theme/dist/themes/proton/style.css';
        $styles[] = $bower_url.'/jstree/dist/themes/default/style.min.css';
        $this->registry->template->addStyles($styles);
        ///--------------------------------------------

        $out = $this->registry->template->show('tree_layout.tpl');
        echo $out;
    }

    //------------------------------------------------
    public function getContextMenu($params)
    {
        $this->registry->template->__set('hideControlPanel', true);
        //var url2 = loc.pathname + loc.search;
        //$menuFigli=$menuparent[$tree_boss->id];
        //ddd($tmp);
        $tree = $this->__get('tree_'.$params['type']);
        $myMenu = $_SESSION['myMenu'];
        $menuparent = ARRAY_OP::array_raggruppa(['data' => $myMenu, 'key' => ['id_padre']]);
        $menu_id = $tree->id;
        //ddd($_POST);
        //$menu_id=$_POST['node']['menu_id'];
        //$menu_id=1324;
        $menu_id = $_POST['node']['state']['menu_id'];
        $menuFigli = $menuparent[$menu_id];
        \reset($menuFigli);
        $str = 'items={'.\chr(13);
        foreach ($menuFigli as $k => $v) {
            $str .= '"PLUTO_'.$k.'": {
                "separator_before": false,
                "separator_after": false,
                "icon":"'.$v->icons.'",
                "label": "'.$v->nome.' '.$_POST['node']['id'].'",
                "action": function (obj) {
                	var url3=loc.pathname + loc.search+\'&rt=menu/gest/'.$v->id.'/\'+$node.id+\'&__ajaxCall=1\';
                    console.log("pluto : "+url3);
                    //loc.href=url3;
                    //$( "#icontent" ).load( url3 );
                    loadIContent(url3,\'#icontent\');

                }
            },'.\chr(13);
            // break;
        }
        $str .= '};';

        return $str;
    }

    //--------------
    public function get_content($params)
    {
        $node = isset($_GET['id']) && '#' !== $_GET['id'] ? $_GET['id'] : 0;
        $node = \explode(':', $node);
        if (\count($node) > 1) {
            $rslt = ['content' => 'Multiple selected'];
        } else {
            $temp = $fs->get_node((int) $node[0], ['with_path' => true]);
            $rslt = ['content' => 'Selected: /'.\implode('/', \array_map(function ($v) {
                return $v['nm'];
            }, $temp['path'])).'/'.$temp['nm']];
        }

        return $rslt;
    }

    //--------------
    public function create_node($params)
    {
        $node = isset($_GET['id']) && '#' !== $_GET['id'] ? (int) $_GET['id'] : 0;
        $temp = $fs->mk($node, isset($_GET['position']) ? (int) $_GET['position'] : 0, ['nm' => isset($_GET['text']) ? $_GET['text'] : 'New node']);
        $rslt = ['id' => $temp];

        return $rslt;
    }

    //--------------
    public function rename_node($params)
    {
        $node = isset($_GET['id']) && '#' !== $_GET['id'] ? (int) $_GET['id'] : 0;
        $rslt = $fs->rn($node, ['nm' => isset($_GET['text']) ? $_GET['text'] : 'Renamed node']);

        return $rslt;
    }

    //--------------
    public function delete_node($params)
    {
        $node = isset($_GET['id']) && '#' !== $_GET['id'] ? (int) $_GET['id'] : 0;
        $rslt = $fs->rm($node);

        return $rslt;
    }

    //--------------
    public function move_node($params)
    {
        $this->registry->template->__set('hideControlPanel', true);
        $node = isset($_GET['id']) && '#' !== $_GET['id'] ? (int) $_GET['id'] : 0;
        $parn = isset($_GET['parent']) && '#' !== $_GET['parent'] ? (int) $_GET['parent'] : 0;
        $position = isset($_GET['position']) ? (int) $_GET['position'] : 0;
        $type = $_GET['type'];
        $tree = $this->__get('tree_'.$type);
        //ddd($tree);
        $tbl = $tree->tablename;

        /*
        $node = isset($_GET['id']) && $_GET['id'] !== '#' ? (int)$_GET['id'] : 0;
        $parn = isset($_GET['parent']) && $_GET['parent'] !== '#' ? (int)$_GET['parent'] : 0;
        $rslt = $fs->mv($node, $parn, isset($_GET['position']) ? (int)$_GET['position'] : 0);
        return $rslt;
        */
    }

    //--------------
    public function copy_node($params)
    {
        $node = isset($_GET['id']) && '#' !== $_GET['id'] ? (int) $_GET['id'] : 0;
        $parn = isset($_GET['parent']) && '#' !== $_GET['parent'] ? (int) $_GET['parent'] : 0;
        $rslt = $fs->cp($node, $parn, isset($_GET['position']) ? (int) $_GET['position'] : 0);

        return $rslt;
    }

    //--------------
}//end class
