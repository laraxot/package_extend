<?php



namespace XRA\Extend\Services;

use Route;

class RouteService
{
    protected static $namespace_start = '';
    protected static $curr = null;

    public static function getGroupOpts($v, $namespace)
    {
        $group_opts = [
            'prefix' => self::getPrefix($v, $namespace),
            'namespace' => self::getNamespace($v, $namespace),
            'as' => self::getAs($v, $namespace),
        ];

        return $group_opts;
    }

    public static function getPrefix($v, $namespace)
    {
        if (\in_array('prefix', \array_keys($v), true)) {
            return $v['prefix'];
        }
        $prefix = \mb_strtolower($v['name']);
        ///*
        $param_name = self::getParamName($v, $namespace);
        if ('' != $param_name) {
            if (\is_array($param_name)) {
                return $prefix.'/{'.\implode('}/{', $param_name).'}';
            }

            return $prefix.'/{'.$param_name.'}';
        }
        //*/
        /*
        $params_name=self::getParamsName($v,$namespace);
        if($params_name!=[]){
            return $prefix.'/{'.implode('}/{',$params_name).'}';
        }
        */
        return $prefix;
    }

    public static function getAs($v, $namespace)
    {
        if (\in_array('as', \array_keys($v), true)) {
            return $v['as'];
        }
        $as = \mb_strtolower($v['name']).'';
        $as = \str_replace('/', '.', $as);
        $as = \preg_replace('/{.*}./', '', $as);

        $as = \str_replace('{', '', $as);
        $as = \str_replace('}', '', $as);

        return $as.'.';
    }

    public static function getNamespace($v, $namespace)
    {
        if (\in_array('namespace', \array_keys($v), true)) {
            return $v['namespace'];
        }
        //if($namespace!=null){
        $namespace = $v['name'];
        //}
        $namespace = \str_replace('{', '', $namespace);
        $namespace = \str_replace('}', '', $namespace);
        if ('' == $namespace) {
            return null;
        }

        return studly_case($namespace);
    }

    public static function getAct($v, $namespace)
    {
        if (\in_array('act', \array_keys($v), true)) {
            return $v['act'];
        }
        $v['act'] = $v['name'];
        $v['act'] = \preg_replace('/{.*}\//', '', $v['act']);
        $v['act'] = \str_replace('/', '_', $v['act']);
        $v['act'] = camel_case($v['act']);
        $v['act'] = \str_replace('{', '', $v['act']);
        $v['act'] = \str_replace('}', '', $v['act']);
        //camel_case foo_bar  => fooBar
        //studly_case foo_bar => FooBar
        return camel_case($v['act']);
    }

    public static function getParamName($v, $namespace)
    {
        if (\in_array('param_name', \array_keys($v), true)) {
            return $v['param_name'];
        }
        $param_name = 'id_'.$v['name'];
        $param_name = \str_replace('{', '', $param_name);
        $param_name = \str_replace('}', '', $param_name);
        //$param_name=null;
        $param_name = \mb_strtolower($param_name);

        return $param_name;
    }

    public static function getParamsName($v, $namespace)
    {
        $param_name = self::getParamName($v, $namespace);
        if (!\is_array($param_name)) {
            $params_name = [$param_name];
        } else {
            $params_name = $param_name;
        }

        return $params_name;
    }

    public static function getResourceOpts($v, $namespace)
    {
        $param_name = self::getParamName($v, $namespace);
        $params_name = self::getParamsName($v, $namespace);
        $opts = [
            'parameters' => [\mb_strtolower($v['name']) => \implode('}/{', $params_name)],
            'names' => self::prefixedResourceNames(self::getAs($v, $namespace)),
        ];
        if (isset($v['only'])) {
            $opts['only'] = $v['only'];
        }
        if ('' == $param_name && !isset($opts['only'])) {
            $opts['only'] = ['index'];
        }
        $where = [];
        foreach ($params_name as $pn) {
            $where[$pn] = '[0-9]+';
        }
        $opts['where'] = $where; //se c'e' "id_" di sicuro e' un numero
        return $opts;
    }

    public static function getController($v, $namespace)
    {
        if (\in_array('controller', \array_keys($v), true)) {
            return $v['controller'];
        }
        $v['controller'] = $v['name'];
        $v['controller'] = \str_replace('/', '_', $v['controller']);
        $v['controller'] = \str_replace('{', '', $v['controller']);
        $v['controller'] = \str_replace('}', '', $v['controller']);
        $v['controller'] = studly_case($v['controller']);
        //camel_case foo_bar  => fooBar
        //studly_case foo_bar => FooBar
        $v['controller'] = $v['controller'].'Controller';

        return $v['controller'];
    }

    public static function getUri($v, $namespace)
    {
        $uri = \mb_strtolower($v['name']);
        /*
        $v['prefix']=self::getPrefix($v,$namespace);
        if(isset($v['prefix'])){ //------------ !!!!! da verificare che non faccia danni
            $uri=$v['prefix'].'/'.$uri;
        }
        */
        return $uri;
    }

    public static function getMethod($v, $namespace)
    {
        if (\in_array('method', \array_keys($v), true)) {
            return $v['method'];
        }

        return ['get', 'post'];
    }

    public static function getUses($v, $namespace)
    {
        $controller = self::getController($v, $namespace);
        $act = self::getAct($v, $namespace);
        $uses = $controller.'@'.$act;

        return $uses;
    }

    public static function getCallback($v, $namespace, $curr)
    {
        $as = str_slug($v['name']); //!!!!!! test da controllare
        $uses = self::getUses($v, $namespace);
        if (null != $curr) {
            $uses = '\\'.self::$namespace_start.'\\'.$curr.'\\'.$uses;
        } else {
            $uses = '\\'.self::$namespace_start.'\\'.$uses;
        }
        $callback = ['as' => $as, 'uses' => $uses];

        return $callback;
    }

    public static function dynamic_route($array, $namespace = null, $namespace_start = null, $curr = null)
    {
        if (null != $namespace_start) {
            self::$namespace_start = $namespace_start;
        }/*
        if($curr!=null){
            static::$curr=$curr;
        }*/
        \reset($array);
        foreach ($array as $k => $v) {
            $group_opts = self::getGroupOpts($v, $namespace);
            $v['group_opts'] = $group_opts;
            Route::group($group_opts, function () use ($v,$namespace,$curr) {
                self::createRouteActs($v, $namespace, $curr);
                self::createRouteSubs($v, $namespace, $curr);
            });
            self::createRouteResource($v, $namespace);
        } //end foreach
    }

    //end function

    //--------------------------------------------------------------------------------
    public static function createRouteResource($v, $namespace)
    {
        if (null == $v['name']) {
            return;
        }
        $opts = self::getResourceOpts($v, $namespace);
        $controller = self::getController($v, $namespace);
        Route::resource(\mb_strtolower($v['name']), $controller, $opts); //->where(['id_'.$v['name'] => '[0-9]+']);
    }

    // ------------------------------------------------------------------------------
    public static function createRouteSubs($v, $namespace, $curr)
    {
        if (!isset($v['subs'])) {
            return;
        }
        $sub_namespace = self::getNamespace($v, $namespace);
        /*
        if(self::$curr==null){
            self::$curr=$sub_namespace;
        }else{
            if(self::$curr!=$sub_namespace){
                self::$curr=self::$curr.'\\'.$sub_namespace;
            }
        }
        */
        if (null == $curr) {
            $curr = $sub_namespace;
        } else {
            $piece = \explode('\\', $curr);
            if (last($piece) != $sub_namespace && $curr != $sub_namespace) {
                $curr .= '\\'.$sub_namespace;
            }
        }

        self::dynamic_route($v['subs'], $sub_namespace, null, $curr);
    }

    //---------------------------------------------------
    public static function createRouteActs($v, $namespace, $curr)
    {
        if (!isset($v['acts'])) {
            return;
        }
        \reset($v['acts']);

        $controller = self::getController($v, $namespace);
        foreach ($v['acts'] as $k1 => $v1) {
            $v1['controller'] = $controller; //le acts hanno il controller del padre
            $method = self::getMethod($v1, $namespace);
            $uri = self::getUri($v1, $namespace);
            $callback = self::getCallback($v1, $namespace, $curr);
            if (\is_array($method)) {
                Route::match($method, $uri, $callback);
            } else {
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
}
