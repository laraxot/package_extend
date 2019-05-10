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


$translation=[
    'name'=>'translation',
];

$areas_prgs = [
    $item0,
    $translation,
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


$item1=[
    'name'=>'Imgz',
    'param_name'=>['wxh','src'],
    'only'=>['show'],
];

$areas_prgs = [
    $item1
];

Route::group(
    [
        'prefix' => null,
        'middleware' => $middleware,
        'namespace' => $namespace,
    ],
    function () use ($areas_prgs,$namespace) {
        RouteService::dynamic_route($areas_prgs, null, $namespace);
    }
);

Route::get('/test', function () {
    
    try{
        Cache::store('apc')->put('avenger_1', 'hulk', 10);//Call to undefined function Illuminate\Cache\apc_store()
    }catch(\Exception $e){
        echo '<br/>NO APC';
    }
    try{
        Cache::store('memcached')->put('avenger_1', 'hulk', 10);//Class 'Memcached' not found
    }catch(\Exception $e){
        echo '<br/>NO memcached';
    }
    /*
    try{
        Cache::store('redis')->put('avenger_1', 'hulk', 10); //Class 'Predis\Client' not found
    }catch(\Exception $e){
        echo '<br/>NO REDIS';
    }
    */
});


$this->routes();
