<?php



use XRA\Extend\Traits\RouteTrait;

$namespace = $this->getNamespace();
$pack = class_basename($this->getNamespace());
$pack_low = \mb_strtolower($pack);

$item0 = [
    'name' => $pack_low, 'prefix' => $pack_low, 'as' => $pack_low.'.', 'namespace' => null, 'controller' => $pack.'Controller', 'only' => ['index'],
];

$areas_prgs = [
    $item0,
];

Route::group(
    [
        'prefix' => 'admin',
        'middleware' => ['web', 'auth'],
        'namespace' => $namespace.'\Controllers\Admin',
    ],
    function () use ($areas_prgs) {
        RouteTrait::dynamic_route($areas_prgs);
    }
);
