<?php



namespace XRA\Extend\library;

use Route;

class XOT
{
    public static function muori()
    {
        // echo '<pre>'; print_r(debug_backtrace()); echo '</pre>';

        $tmp = \debug_backtrace();
        $copy_dir = \str_replace(\DIRECTORY_SEPARATOR.'framework'.\DIRECTORY_SEPARATOR.'mvc'.\DIRECTORY_SEPARATOR.'includes', '', __DIR__);

        // echo '<pre>'.$copy_dir.'</pre>';
        // echo '<pre>'.__DIR__.'</pre>';
        // echo '<pre>';print_r($_SERVER);echo '</pre>';
        //  DOCUMENT_ROOT

        echo '<br style="clear:both"/>';
        echo '<div style="border:1px solid red;float:left">';
        echo '<br/><b>LINE: </b>'.$tmp[0]['line'];

        // echo '<br/><b>FILE: </b>'.$tmp[0]['file'];

        $filepath = \str_replace($copy_dir, $_SERVER['DOCUMENT_ROOT'], $tmp[0]['file']);
        $filepath = \str_replace('/', \DIRECTORY_SEPARATOR, $filepath);
        $filepath = \str_replace(\DIRECTORY_SEPARATOR.\DIRECTORY_SEPARATOR, \DIRECTORY_SEPARATOR, $filepath);
        echo '<br/><b>FILE: </b>'.$filepath;
        if (isset($tmp[1]['function '])) {
            echo '<br/><b>FUNZIONE: </b>'.$tmp[1]['function '];
        }

        // $registry->time_start;

        echo '<br/><b>TIME: </b>'.(DATE_OP::microtime_float());
        echo '</div>';
        die('');
    }

    //end muori

    public static function print_x($arr)
    {
        $copy_dir = \str_replace(\DIRECTORY_SEPARATOR.'framework'.\DIRECTORY_SEPARATOR.'mvc'.\DIRECTORY_SEPARATOR.'includes', '', __DIR__);
        $deb = \debug_backtrace();
        $line = $deb[0]['line'];
        $file = $deb[0]['file'];
        echo '<br style="clear:both;"/>';
        echo '<pre style="text-align:left;">FILE : ['.FILE_OP::pathCopy2http([
            'copypath' => $file,
        ]).']'.\chr(13).'LINE : ['.$line.']'.\chr(13).'';
        echo 'COUNT : '.\count($arr).' '.\chr(13);
        \print_r($arr);
        echo ']</pre>';
        echo '<table border="1">';
        $fieldz = [
            'file',
            'line',
            'class',
            'function ',
        ];
        echo '<tr>';
        \reset($fieldz);
        foreach ($fieldz as $kf => $vf) {
            echo '<th>'.$vf.'</th>';
        }

        echo '</tr>';
        \reset($deb);
        foreach ($deb as $k => $v) {
            echo '<tr>';
            \reset($fieldz);
            foreach ($fieldz as $kf => $vf) {
                // echo $vf;

                if ('file' == $vf && isset($v[$vf])) {
                    // print_r($_SERVER);

                    $v[$vf] = FILE_OP::pathCopy2http([
                        'copypath' => $v[$vf],
                    ]);
                    echo '<td align="left">';
                } else {
                    echo '<td>';
                }

                if (isset($v[$vf])) {
                    echo $v[$vf];
                }
                echo '</td>';
            }

            echo '</tr>';
        }

        echo '</table>';

        // echo '<pre>';print_r($back);echo '</pre>';
        // $back=debug_backtrace();
        // echo '<pre>';print_r($back);echo '</pre>';
    }

    //end print_x

    // ----------------------------------------------------------------------------------------

    public function showError($pearError)
    {
        $pearError->backtrace = '';
        unset($pearError->backtrace);

        $html = '<h3 style="color:red">Error !</h3>';
        $html .= '<table>';
        \reset($pearError);
        foreach ($pearError as $k => $v) {
            $html .= '<tr><td>'.$k.'</td><td><pre>'.$v.'</pre></td></tr>';
        }

        $html .= '</table>';

        // /echo $html;

        ddd($html);
    }

    // ------------------------------------------------------------------------------------------

    public function delta()
    {
        global $registry;
        $tmp = \debug_backtrace();
        $copy_dir = \str_replace(\DIRECTORY_SEPARATOR.'framework'.\DIRECTORY_SEPARATOR.'mvc'.\DIRECTORY_SEPARATOR.'includes', '', __DIR__);
        echo '<br/><b>LINE: </b>'.$tmp[0]['line'];

        // echo '<br/><b>FILE: </b>'.$tmp[0]['file'];

        $filepath = \str_replace($copy_dir, $_SERVER['DOCUMENT_ROOT'], $tmp[0]['file']);
        $filepath = \str_replace('/', \DIRECTORY_SEPARATOR, $filepath);
        $filepath = \str_replace(\DIRECTORY_SEPARATOR.\DIRECTORY_SEPARATOR, \DIRECTORY_SEPARATOR, $filepath);
        echo '<br/><b>FILE: </b>'.$filepath;
        if (isset($tmp[1]['function '])) {
            echo '<br/><b>FUNZIONE: </b>'.$tmp[1]['function '];
        }

        $dim = \memory_get_usage() - $registry->memory_get_usage;
        echo '<br/><b>DIM:</b>'.\round($dim / 1024, 2).' Kb';
        echo '<br/><b>memory usage</b>:'.\round(\memory_get_usage() / 1024, 2).' Kb';
        $registry->memory_get_usage = \memory_get_usage();
        $time = DATE_OP::microtime_float() - $registry->time_delta;

        // echo '<br/><b>delta:</b>'.$registry->time_delta;

        echo '<br/><b>delta  ms :</b>'.\round($time * 1000, 3);
        echo '<br/><b>time   ms :</b>'.\round((DATE_OP::microtime_float() - $registry->time_start) * 1000, 3);
        $registry->time_delta = DATE_OP::microtime_float();
        echo '<hr/>';
    }

    //end delta

    // ----------------------------------
    // private static $printResultCounter = 0;

    /**
     * Prints HTML Output to web page.
     *
     * @param string $title
     * @param string $objectName
     * @param string $objectId
     * @param mixed  $request
     * @param mixed  $response
     * @param string $errorMessage
     */
    public static function printOutput($title, $objectName, $objectId = null, $request = null, $response = null, $errorMessage = null)
    {
        if (\PHP_SAPI == 'cli') {
            ++self::$printResultCounter;
            \printf("\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n");
            \printf('(%d) %s', self::$printResultCounter, \mb_strtoupper($title));
            \printf("\n-------------------------------------------------------------\n\n");
            if ($objectId) {
                \printf("Object with ID: %s \n", $objectId);
            }

            \printf("-------------------------------------------------------------\n");
            \printf("\tREQUEST:\n");
            self::printConsoleObject($request);
            \printf("\n\n\tRESPONSE:\n");
            self::printConsoleObject($response, $errorMessage);
            \printf("\n-------------------------------------------------------------\n\n");
        } else {
            if (0 == self::$printResultCounter) {
                //   include "header.html";

                echo '
									<div class="row header"><div class="col-md-5 pull-left"><br /><!--<a href="../index.php"><h1 class="home">&#10094;&#10094; Back to Samples</h1></a>--><br /></div> <br />
									<div class="col-md-4 pull-right"><img src="https://www.paypalobjects.com/webstatic/developer/logo2_paypal_developer_2x.png" class="logo" width="300"/></div> </div>';
                echo '<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">';
            }

            ++self::$printResultCounter;
            echo '
				<div class="panel panel-default">
						<div class="panel-heading '.($errorMessage ? 'error' : '').'" role="tab" id="heading-'.self::$printResultCounter.'">
								<h4 class="panel-title">
										<a data-toggle="collapse" data-parent="#accordion" href="#step-'.self::$printResultCounter.'" aria-expanded="false" aria-controls="step-'.self::$printResultCounter.'">
						'.self::$printResultCounter.'. '.$title.($errorMessage ? ' (Failed)' : '').'</a>
								</h4>
						</div>
						<div id="step-'.self::$printResultCounter.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-'.self::$printResultCounter.'">
								<div class="panel-body">
						';
            if ($objectId) {
                echo '<div>'.($objectName ? $objectName : 'Object')." with ID: $objectId </div>";
            }

            echo '<div class="row hidden-xs hidden-sm hidden-md"><div class="col-md-6"><h4>Request Object</h4>';
            self::printObject($request);
            echo '</div><div class="col-md-6"><h4 class="'.($errorMessage ? 'error' : '').'">Response Object</h4>';
            self::printObject($response, $errorMessage);
            echo '</div></div>';
            echo '<div class="hidden-lg"><ul class="nav nav-tabs" role="tablist">
												<li role="presentation" ><a href="#step-'.self::$printResultCounter.'-request" role="tab" data-toggle="tab">Request</a></li>
												<li role="presentation" class="active"><a href="#step-'.self::$printResultCounter.'-response" role="tab" data-toggle="tab">Response</a></li>
										</ul>
										<div class="tab-content">
												<div role="tabpanel" class="tab-pane" id="step-'.self::$printResultCounter.'-request"><h4>Request Object</h4>';
            self::printObject($request);
            echo '</div><div role="tabpanel" class="tab-pane active" id="step-'.self::$printResultCounter.'-response"><h4>Response Object</h4>';
            self::printObject($response, $errorMessage);
            echo '</div></div></div></div>
						</div>
				</div>';
        }

        \flush();
    }

    /**
     * Prints success response HTML Output to web page.
     *
     * @param string $title
     * @param string $objectName
     * @param string $objectId
     * @param mixed  $request
     * @param mixed  $response
     */
    public static function printResult($title, $objectName, $objectId = null, $request = null, $response = null)
    {
        self::printOutput($title, $objectName, $objectId, $request, $response, false);
    }

    /**
     * Prints Error.
     *
     * @param            $title
     * @param            $objectName
     * @param null       $objectId
     * @param null       $request
     * @param \Exception $exception
     */
    public static function printError($title, $objectName, $objectId = null, $request = null, $exception = null)
    {
        $data = null;
        if ($exception instanceof PayPalExceptionPayPalConnectionException) {
            $data = $exception->getData();
        }

        self::printOutput($title, $objectName, $objectId, $request, $data, $exception->getMessage());
    }

    protected static function printConsoleObject($object, $error = null)
    {
        if ($error) {
            echo 'ERROR:'.$error;
        }

        if ($object) {
            if (\is_a($object, 'PayPal\Common\PayPalModel')) {
                /** @var $object \PayPal\Common\PayPalModel */
                echo $object->toJSON(128);
            } elseif (\is_string($object) && PayPalValidationJsonValidator::validate($object, true)) {
                echo \str_replace('\\/', '/', \json_encode(\json_decode($object), 128));
            } elseif (\is_string($object)) {
                echo $object;
            } else {
                \print_r($object);
            }
        } else {
            echo 'No Data';
        }
    }

    protected static function printObject($object, $error = null)
    {
        if ($error) {
            echo '<p class="error"><i class="fa fa-exclamation-triangle"></i> '.$error.'</p>';
        }

        if ($object) {
            if (\is_a($object, 'PayPal\Common\PayPalModel')) {
                /* @var $object \PayPal\Common\PayPalModel */
                echo '<pre class="prettyprint '.($error ? 'error' : '').'">'.$object->toJSON(128).'</pre>';
            } elseif (\is_string($object) && PayPalValidationJsonValidator::validate($object, true)) {
                echo '<pre class="prettyprint '.($error ? 'error' : '').'">'.\str_replace('\\/', '/', \json_encode(\json_decode($object), 128)).'</pre>';
            } elseif (\is_string($object)) {
                echo '<pre class="prettyprint '.($error ? 'error' : '').'">'.$object.'</pre>';
            } else {
                echo '<pre>';
                \print_r($object);
                echo '</pre>';
            }
        } else {
            echo '<span>No Data</span>';
        }
    }

    // --------------------------------------
    // --------------------------------------

    public static function getView($request = null)
    {
        $tmp = \debug_backtrace();

        //  echo '<h3>Request::path() : ['.\Request::path().']</h3>';

        /*
        echo '<pre>[';print_r(\Route::currentRouteName());echo ']</pre>';
        echo '<pre>[';print_r(\Request::path());echo ']</pre>';
        echo '<pre>[';print_r(\Route::current());echo ']</pre>';
        echo '<pre>[';print_r(\Route::currentRouteAction());echo ']</pre>';
        die('['.__LINE__.']['.__FILE__.']');
        */

        // $view= 'admin.test.index';
        // *

        $view = \str_replace('/', '.', (Route::current()->getCompiled()->getStaticPrefix()));
        if (\in_array($tmp[1]['function'], ['index', 'edit'], true)) {
            $view .= '.'.$tmp[1]['function'];
        }
        $view = \mb_substr($view, 1);
        $view = \str_replace('.ew8.', '.', $view); // questo replace sarebbe da togliere...

        // */

        return $view;
    }

    //end getview

    // /---------------------------------------
    public static function getView1($request = null)
    {
        $tmp = \Route::currentRouteAction();
        $tmp = \str_replace('App\Http\Controllers\\', '', $tmp);
        $tmp = \str_replace('\\', '.', $tmp);
        $tmp = \str_replace('Controller@', '.', $tmp);
        $tmp = \mb_strtolower($tmp);
        //echo '<pre>[';print_r($tmp);echo ']</pre>';
        return $tmp;
    }

    //end getview1
    //------------------------------------------------

    public static function dynamic_route($array, $namespace = null)
    {
        \reset($array);
        foreach ($array as $k => $v) {
            if (!isset($v['prefix'])) {
                echo '<h3>prefix mancante</h3>';
                echo '<pre>';
                \print_r($v);
                echo '</pre>';

                return;
            }
            $group_opts = ['prefix' => $v['prefix'], 'namespace' => $v['namespace'], 'as' => $v['as']];

            // echo '<pre>['.__LINE__.']';print_r($group_opts);echo '</pre>';
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
        /* -- da implementare
        $pos=strrpos($v['name'],'/');
        if($pos!==false){
            $name=substr($v['name'],$pos+1);
        }else{
            $name=$v['name'];
        }
        */
        if (isset($params['param_name'])) {
            $param_name = $params['param_name'];
        //echo '<pre>';var_dump($params);echo '</pre>';
        } else {
            $param_name = 'id_'.$v['name'];
        }
        $opts = ['parameters' => [$v['name'] => $param_name], 'names' => prefixedResourceNames($v['as'])];
        if (isset($v['only'])) {
            $opts['only'] = $v['only'];
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
    Route::resource($v['name'], $controllername, $opts); //->where(['id_'.$v['name'] => '[0-9]+']);
    }

    // ------------------------------------------------------------------------------
    public static function createRouteSubs($params)
    {
        $v = $params;
        if (!isset($v['subs'])) {
            return;
        }
        $sub_namespace = $v['name'];
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
            $method = $v1['method'];
            //if (isset($v1['as'])) $as2 = $v1['as'];
            //else $as2 = $v1['name'];
            $act = $v1['act'];
            $as = $v1['as'];
            $uri = $v1['name'];
            //$controllername = self::getControllerName(['name' => $v['name']]);
            //$controllername = $v1['controller'];
            $controllername = $v['controller']; //le acts hanno il controller del padre
            $uses = $controllername.'@'.$act;
            $callback = ['as' => $as, 'uses' => $uses];
            //echo '<pre>';print_r($callback);echo '</pre>';
            Route::$method($uri, $callback);
        } //endforeach
    }

    // ------------------------------------------------------

    public static function getControllerName($params)
    {
        $name = $params['name'];

        $name = \str_replace('-', ' ', $name);
        $name = \ucwords($name);
        $name = \str_replace(' ', '', $name);
        $name = $name.'Controller';
        $name = \str_replace('Trasferte_dip', 'Trasferte', $name);
        $name = \str_replace('Trasferte_adm', 'Trasferte', $name);
        $name = \str_replace('Fuorisede', 'FuoriSede', $name);

        return $name;
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
} //end class XOT

function prefixedResourceNames($prefix)
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
