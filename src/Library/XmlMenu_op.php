<?php



namespace XRA\Extend\Library;

class XmlMenu_op
{
    public static function get()
    {
        $tmp = \explode('/', \Route::current()->getCompiled()->getStaticPrefix());
        $tmp = \array_slice($tmp, 2, 1);

        if (\count($tmp) < 1) {
            return false;
        }
        //$menu_dir=resource_path().'\menu\\'.implode('\\',$tmp);
        //$menu_dir = base_path('\packages\Enteweb\_ew8Menu');
        $tmp = $tmp[0];

        $action = \Request::route()->getAction();
        $psr4 = include base_path('vendor'.\DIRECTORY_SEPARATOR.'composer'.\DIRECTORY_SEPARATOR.'autoload_psr4.php');
        $namespace = $action['controller'];
        if ('\\' == $namespace[0]) {
            $namespace = \mb_substr($namespace, 1);
        }
        $namespace = \explode('\\', $namespace);
        $namespace = \array_slice($namespace, 0, 2);
        $namespace = \implode('\\', $namespace).'\\';
        $namespace_dir = $psr4[$namespace][0];

        //extra conn mi serve, per gestire pacchetti particolari.
        $menu_dir = $namespace_dir.'\\_menuxml\\Admin\\'.config('extra_conn');
        $menu_dir = \str_replace('\\\\', '\\', $menu_dir);

        $menu = self::dirXML2Obj($menu_dir);
        $parz = \Route::current()->parameters();
        $routename = \Route::currentRouteName();
        $url = route($routename, $parz);

        //die('['.__LINE__.']['.__FILE__.']');
        if (\Auth::check()) {
            $perm_type = \Auth::user()->perm->perm_type;
        } else {
            return []; //vedere se mettere perm_type=0, o uccidere tutto
        }
        //-------- mi toglie i menu cui non posso vedere per livello --
        $menu1 = [];
        \reset($menu);

        //while (list($k, $v) = each($menu)) {
        foreach ($menu as $k => $v) {
            if (!isset($v->visibility)) {
                $v->visibility = 0;
            }
            if ($perm_type >= $v->visibility) {
                $menu1[] = $v;
            }
        }
        $menu = $menu1;

        \reset($menu);
        //while (list($k, $v) = each($menu)) {
        foreach ($menu as $k => $v) {
            $menu[$k]->active = false;

            if (!isset($v->querystr_keys)) {
                $v->querystr_keys = '';
            }
            if (isset($v->submenu)) {
                $v2 = array_where($menu, function ($value, $key) use ($v) {
                    return $value->id_padre == $v->id;
                });
                $v2 = \array_values($v2)[0];
                list($sobj, $sact) = \explode('.', $v->submenu);

                $sobj = \str_replace('areemenupagine', 'AreeMenuPagine', $sobj);
                $sobj = '\App\Http\Controllers\Admin\\'.$sobj.'Controller';
                $controller = new $sobj();
                $submenu = $controller->$sact(\get_object_vars($v));
                \reset($submenu);

                foreach ($submenu as $k1 => $v1) {
                    if ('' == $v2->obj || '' == $v2->act) {
                        echo '<pre>';
                        \print_r($v2);
                        echo '</pre>';
                        die();
                    }
                    $submenu[$k1]->url = url('admin/ew8/area/'.$v1->id);
                    if ($submenu[$k1]->url == $url) {
                        $menu[$k]->active = true;
                        $submenu[$k1]->active = true;
                    } else {
                        $submenu[$k1]->active = false;
                    }
                }
                $menu[$k]->sub = $submenu;
            }

            if (!isset($v->act)) {
                $v->act = '';
            }
            if ('' != $v->act) {
                if ('' != $v->querystr_keys) {
                    $menu[$k]->url = '#1-'.$v->id.'-'.$v->querystr_keys;
                } else {
                    //if($v->obj=='aree')
                    $routename = $v->obj.'.'.$v->act;
                    if (\Route::has(\mb_strtolower($routename))) {
                        $routename = \mb_strtolower($routename);
                    }

                    if (\Route::has($routename)) {
                        //$menu[$k]->url = route($routename,[],false); //per fare url relativo
                        $route_pars = \Route::current($routename)->parameters();
                        if (!isset($route_pars['lang'])) {
                            $route_pars['lang'] = \App::getLocale();
                        }
                        //\Route::resourceParameters(['lang'=>\App::getLocale()]);
                        //var_dump(\Route::getRoutesByName());die();
                        //$routeCollection = \Route::getRoutes();

                        //foreach($routeCollection as $route){
                        //    $compiled = $route->getCompiled();
                        //    if(!is_null($compiled)){
                        //        echo '<pre>'; var_dump($compiled /*->getStaticPrefix() */ ); echo '</pre>';

                        //}
                        //echo $route->uri().'<br>';
                        //    echo $route->getName().'<br>';
                        //    echo $route->getActionName().'<br>';
                        //    echo '<pre>';print_r($route->parameterNames()); echo '</pre>';

                        //echo '<pre>';print_r($route->parameters());echo '</pre>';
                        //}
                        
                        //dd($routeCollection);
                        // dd(\Route::dispatchToRoute(\Request::create('blog.post.index')));
                        //dd(\Request::route('blog.post.index')->getName());
                        //
                        //dd(\Route::dispatchToRoute(\Request::create('blog.post.index')));
                        //if($par2!=null) dd($par2);
                        $menu[$k]->url = route($routename, $route_pars);
                    } else {
                        $menu[$k]->url = '#4-'.$routename;
                    }
                    //dd($url);
                    //{{ Request::is(substr($sub_el->url,1).'*') ? ' class="active"' :  '' }}
                    if (\mb_substr($url, 0, \mb_strlen($menu[$k]->url)) == $menu[$k]->url) {
                        //dd($menu[$k]);
                        $id_padre = $menu[$k]->id_padre;
                        if (isset($menu[$id_padre])) {
                            $menu[$id_padre]->active = true;
                        }
                        $menu[$k]->active = true;
                    }
                }
            } else {
                $menu[$k]->url = '#3-'.$v->id;
            }
            if (!isset($menu[$k]->visibility)) {
                $menu[$k]->visibility = 0;
            }
            if (!isset($menu[$k]->icons)) {
                $menu[$k]->icons = ' ';
            }
        }

        return $menu;
    }

    public static function dirXML2Obj($dir_menu)
    {
        $dir_fullmenu = $dir_menu.\DIRECTORY_SEPARATOR.'_menufull.xml';
        if (\file_exists($dir_fullmenu)) {
            $arr = Xml_op::xml2array_1($dir_fullmenu);
            if (!isset($arr['root']['row'])) {
                return [];
            }
            $arr = $arr['root']['row'];
            \reset($arr);
            //foreach($arr as $k => $v) {
            foreach ($arr as $k => $v) {
                $arr[$k] = Array_op::array2Obj($v);
            }
            $_SESSION[$dir_menu] = $arr;
        } else {
            $_SESSION[$dir_menu] = self::do_dirXML2Obj($dir_menu);
            if (!isset($_SESSION[$dir_menu]) || \count($_SESSION[$dir_menu]) < 3) {
                $_SESSION[$dir_menu] = self::do_dirXML2Obj($dir_menu);
            }
            $xmlstr = Array_op::array2xml($_SESSION[$dir_menu]);
            FILE_OP::writeFile($dir_fullmenu, '<root>'.\chr(13).$xmlstr.\chr(13).'</root>');
        }

        $_SESSION[$dir_menu] = Array_op::arrayKeys(['array' => $_SESSION[$dir_menu], 'key' => 'id']);

        return $_SESSION[$dir_menu];
    }

    public static function do_dirXML2Obj($dir_menu)
    {
        $dir_menu = \str_replace('\\', \DIRECTORY_SEPARATOR, $dir_menu);
        $dir_menu = \str_replace('/', \DIRECTORY_SEPARATOR, $dir_menu);

        $d = \dir($dir_menu);
        if (!\is_dir($dir_menu)) {
            return [];
        }

        $menu = [];
        $str = 'menu_';
        $maxId = 0;
        while (false !== ($entry = $d->read())) {
            $myfile = $d->path.'/'.$entry;
            $myfile = \str_replace('/', \DIRECTORY_SEPARATOR, $myfile);
            $path_parts = \pathinfo($myfile);

            if (isset($path_parts['extension']) && 'xml' == $path_parts['extension']) {
                if (\mb_substr($entry, 0, \mb_strlen($str)) == $str) {
                    $id = \mb_substr($entry, \mb_strlen($str) + 0, -4) * 1;
                    $tmp = Xml_op::xml2array($myfile);

                    if (isset($tmp['root']['row'])) {
                        $tmp1 = Array_op::array2Obj($tmp['root']['row']);
                        $tmp1->file_xmlmenu = $myfile;
                        if (isset($tmp1->id)) {
                            if ($tmp1->id > $maxId) {
                                $maxId = $tmp1->id;
                            }
                        }

                        if (isset($tmp1->id)) {
                            if ($tmp1->id != $id) {
                                echo '<br/>['.$entry.'][]';
                                echo '<br/>['.$tmp1->id.']['.$id.']';
                                echo '<pre>controlla ['.$myfile.']</pre>';
                                ddd('qui');
                            }
                            $menu[$tmp1->id] = $tmp1;
                        } else {
                            echo '<pre>['.__LINE__.']['.__FILE__.']';
                            echo '<pre>controlla ['.$myfile.']</pre>';
                            \rename($myfile, \str_replace('.xml', '.err', $myfile));
                        }
                    }
                }
            }
        }
        $d->close();
        //echo '<pre>'; print_r($menu); echo '</pre>';
        $params['dir_menu'] = $dir_menu;
        /* da fare assolutamente
        if ($maxId != XmlMenu_op::getLastXMLMenuID($params)) {
            $parz = array('dir_menu' => $dir_menu,'seq'=>$maxId);
            XmlMenu_op::updateLastXMLMenuID($parz);
        }
        */
        \usort($menu, ['self', 'cmpMenu']);
        /*
        if($dir_menu=='c:/xampp/htdocs/consolle/admin/controller/trasferte_adm/menu'){
            echo '<pre>[[[[[[[['.count($menu); echo ']]]]]]]]</pre>';
        }
        */
        //Array_op::print_x($menu);
        return $menu;
    }

    //---------------------------------------------------------

    //--------------------------------------------------------

    public static function setActiveAndGrouped($params)
    {
        $parz = \Route::current()->parameters();
        if (!isset($parz['lang'])) {
            $parz['lang'] = 'it';
        }
        $routename = \Route::currentRouteName();
        $url = route($routename, $parz);

        $menu0 = $params['data'];

        if (!\is_array($menu0)) {
            return [];
        }
        \reset($menu0);
        $ris = array_where($menu0, function ($value, $key) use ($url) {
            //dd($value->url.'    '.$url);
            //return $value->url == $url;
            return \mb_substr($url, 0, \mb_strlen($value->url)) == $value->url;
        });
        //dd($ris);
        $node = array_first($ris);
        $menu0 = collect($menu0)->keyBy('id')->all();
        if (isset($node->id)) {
            $id = $node->id;
            $id_padre = $node->id_padre;
            if (isset($menu0[$id_padre]) /*&& route($routename, $parz) != $url */) {
                $menu0[$id_padre]->active = 1;
            } else {
            }
        }

        return Array_op::array_raggruppa(['data' => $menu0, 'key' => ['id_padre']]);
    }

    //----------------------------------------------------

    public static function cmpMenu($a, $b)
    {
        //echo '[[A<pre>'; print_r($a); echo '</pre>]]';
        //echo '[[B<pre>'; print_r($b); echo '</pre>]]';
        // $a = preg_replace('@^(a|an|the) @', '', $a);
        // $b = preg_replace('@^(a|an|the) @', '', $b);
        //  return strcasecmp($a, $b);
        if (!isset($a->id_padre)) {
            //Array_op::print_x($a);
            $a->id_padre = 0;
        }
        if (!isset($a->ordine)) {
            //Array_op::print_x($a);
            $a->ordine = 10;
        }
        if (!isset($b->id_padre)) {
            //Array_op::print_x($a);
            $b->id_padre = 0;
        }
        if (!isset($b->ordine)) {
            //Array_op::print_x($a);
            $b->ordine = 10;
        }

        if ($a->id_padre == $b->id_padre && isset($a->ordine) && isset($b->ordine)) {
            return $a->ordine > $b->ordine;
        }

        return $a->id_padre > $b->id_padre;
    }

    //-----------------------------------------------------
}//end class
