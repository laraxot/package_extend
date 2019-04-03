<?php


use XRA\Extend\Services\RouteService;

$namespace = $this->getNamespace();
$namespace .= '\Controllers';

$middleware = ['web', 'guest'];
$middleware = ['web'];

$prefix = 'imgz';

/*
$item0=[
    'name'=>'imgz',
    'prefix'=>'',
    'as'=>'imgz.',
    //'param_name'=>'item',
    //'namespace'=>null,
    'controller' =>  'ImgzController',
    'only'=>['index','show','create'],
    'acts'=>[
    	[
    	'name'=>'imgz/{wxh}/{txt}',
    	'prefix'=>'banner',
    	'method'=>'get',
    	'as'=>'banner',
    	'act'=>'banner',
    	],//end act_n
    ],//end acts
];
*/

$item0 = [
    'name' => 'Imgz',
    'param_name' => '',
    //'only'=>['index','show','create'],
    'acts' => [
        [
        'name' => 'canvas',
        ], //end act_n
    ], //end acts
];

$areas_prgs = [
    $item0,
];
//,'middleware' => ['web','auth']
$prefix = App::getLocale();
//$prefix='{locale}';
Route::group(
    [
        'prefix' => $prefix,
        'middleware' => $middleware,
        'namespace' => $namespace,
    ],
    function () use ($areas_prgs,$namespace) {
        RouteService::dynamic_route($areas_prgs, null, $namespace);
    }
);

$this->routes();
