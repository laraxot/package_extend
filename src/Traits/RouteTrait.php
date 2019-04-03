<?php



namespace XRA\Extend\Traits;

use Route;

trait RouteTrait
{
    public static function dynamic_route($array, $namespace = null)
    {
        \reset($array);
        foreach ($array as $k => $v) {
            if (!\in_array('prefix', \array_keys($v), true) /*&& $v['name']!=null*/) {
                $v['prefix'] = \mb_strtolower($v['name']);
            }
            if (!\in_array('as', \array_keys($v), true)) {
                if (null === $v['name']) {
                    if (\in_array('prefix', \array_keys($v), true)) {
                        $v['as'] = $v['prefix'];
                        $v['as'] = \str_replace('{', '', $v['as']);
                        $v['as'] = \str_replace('}', '.', $v['as']);
                    } else {
                        $v['as'] = null;
                    }
                } else {
                    $v['as'] = \mb_strtolower($v['name']).'.';
                }
                $v['as'] = \str_replace('{', '', $v['as']);
                $v['as'] = \str_replace('}', '', $v['as']);
                //$v['as']=$v['prefix'].'.';
            }
            if ('trasferte_dip' == $namespace || 'trasferte_adm' == $namespace) {
                $namespace = null;
            }

            if (!\in_array('namespace', \array_keys($v), true)) {
                $v['namespace'] = $namespace;
            }
            if (false !== \mb_strpos($v['as'], '{')) {
                $v['as'] = null; //dd('non ci sono');
            }
            //echo '['.($namespace).' = '.$v['namespace'] .']';
            if (false !== \mb_strpos($v['prefix'], '{')) {
                $v['namespace'] = null;
            }
            if ('' == $v['namespace']) {
                $v['namespace'] = null;
            }

            $group_opts = ['prefix' => $v['prefix'], 'namespace' => $v['namespace'], 'as' => $v['as']];
            $v['group_opts'] = $group_opts;
            Route::group($group_opts, function () use ($v) {
                self::createRouteSubs($v);
                self::createRouteActs($v);
            });
            self::createRouteResource($v);
        } //end foreach
    }

    //end function

    //--------------------------------------------------------------------------------
    public static function createRouteResource($params)
    {
        $v = $params;
        if (null == $v['name']) {
            return;
        }
        if (isset($params['param_name'])) {
            $param_name = $params['param_name'];
        } else {
            $param_name = 'id_'.$v['name'];
            $param_name = \str_replace('{', '', $param_name);
            $param_name = \str_replace('}', '', $param_name);
            //$param_name=null;
        }
        $param_name = \mb_strtolower($param_name);

        $opts = ['parameters' => [\mb_strtolower($v['name']) => $param_name], 'names' => self::prefixedResourceNames($v['as'])];
        if (isset($v['only'])) {
            $opts['only'] = $v['only'];
        }
        if (!isset($v['controller'])) {
            $v['controller'] = \ucfirst($v['name']).'Controller';
            $v['controller'] = \str_replace('{', '', $v['controller']);
            $v['controller'] = \str_replace('}', '', $v['controller']);
        }
        $controllername = $v['controller'];
        if (null != $v['namespace'] && \is_string($v['controller']) && '\\' != $v['controller'][0]) {
            $controllername = $v['namespace'].'\\'.$controllername;
        }
        /* //lo tengo in caso di errore devo vedere il debug..
        echo '<h3>resource</h3>';
        echo '<br/> name :'.$v['name'];
        echo '<br/> controller :'.$controllername;
        echo '<br/> opts <pre>';print_r($opts);echo '</pre>';
        //*/
        $opts['where'] = [$param_name => '[0-9]+']; //se c'e' "id_" di sicuro e' un numero
        Route::resource(\mb_strtolower($v['name']), $controllername, $opts); //->where(['id_'.$v['name'] => '[0-9]+']);
    }

    // ------------------------------------------------------------------------------
    public static function createRouteSubs($params)
    {
        $v = $params;
        if (!isset($v['subs'])) {
            return;
        }

        $sub_namespace = $v['name'];
        $sub_namespace = \str_replace('{', '', $sub_namespace);
        $sub_namespace = \str_replace('}', '', $sub_namespace);

        if (\in_array('namespace', \array_keys($v), true) && null != $v['namespace']) {
            $sub_namespace = $v['namespace'];
        }

        if (isset($group_opts['prefix'][0]) && '{' == $group_opts['prefix'][0]) {
            $sub_namespace = null;
        }

        self::dynamic_route($v['subs'], $sub_namespace);
    }

    // Route::get($uri, $callback);

    //---------------------------------------------------
    public static function createRouteActs($params)
    {
        $v = $params;
        if (!isset($v['acts'])) {
            return;
        }
        \reset($v['acts']);
        foreach ($v['acts'] as $k1 => $v1) {
            if (!isset($v['controller'])) {
                $v['controller'] = $v['name'];
                $v['controller'] = \str_replace('/', '_', $v['controller']);
                if ($v['controller'] != $v['name']) {
                    $v['controller'] = camel_case($v['controller']);
                }
                $v['controller'] = $v['controller'].'Controller';
            }
            if (!isset($v1['act'])) {
                $v1['act'] = $v1['name'];
                $v1['act'] = \preg_replace('/{.*}\//', '', $v1['act']);
                $v1['act'] = \str_replace('/', '_', $v1['act']);
                if ($v1['act'] != $v1['name']) {
                    $v1['act'] = camel_case($v1['act']);
                }
            }
            if (!isset($v1['as'])) {
                $v1['as'] = \mb_strtolower($v1['name']).'';
                $v1['as'] = \str_replace('/', '.', $v1['as']);
                $v1['as'] = \preg_replace('/{.*}./', '', $v1['as']);
            }
            $v1['controller'] = $v['controller']; //le acts hanno il controller del padre
            $method = ['get', 'post'];
            \extract($v1);
            $uri = \mb_strtolower($name);
            if (isset($v1['prefix'])) { //------------ !!!!! da verificare che non faccia danni
                $uri = $v1['prefix'].'/'.$uri;
            }
            $uses = $controller.'@'.$act;
            $callback = ['as' => $as, 'uses' => $uses];
            if (\is_array($method)) {
                Route::match($method, $uri, $callback);
            } else {//is string or error
                Route::$method($uri, $callback);
            }
        } //endforeach
    }

    // /--------------------------------------------------------

    public static function routes()
    {
        if ('' != \Request::path()) {
            $tmp = \explode('/', \Request::path());
            $tmp = \array_slice($tmp, 0, 2);
            $tmp = \implode('_', $tmp);
            //echo '<h3>tmp = '.$tmp.'</h3>';die();
            $filename = 'web_'.$tmp.'.php';

            $tmp = \debug_backtrace();
            dd($tmp[3]['class']);

            $filename_dir = __DIR__.\DIRECTORY_SEPARATOR.$filename;
            echo '<h3>tmp = '.$filename_dir.'</h3>';
            die();
            if (\file_exists($filename_dir)) {
                require $filename_dir;
            }
        }
    }

    //end routes
    //------------------------------------------------------------------
    public static function prefixedResourceNames($prefix)
    {
        if ('.' == \mb_substr($prefix, -1)) {
            $prefix = \mb_substr($prefix, 0, -1);
        }
        if ('' == $prefix || null == $prefix) {
            return ['index' => $prefix.'index', 'create' => $prefix.'create', 'store' => 'store', 'show' => $prefix.'show', 'edit' => $prefix.'edit', 'update' => $prefix.'update', 'destroy' => $prefix.'destroy'];
        }
        $prefix = \mb_strtolower($prefix);

        return ['index' => $prefix.'.index', 'create' => $prefix.'.create', 'store' => $prefix.'.store', 'show' => $prefix.'.show', 'edit' => $prefix.'.edit', 'update' => $prefix.'.update', 'destroy' => $prefix.'.destroy'];
    }

    //end prefixedResourceNames
}//end trait
