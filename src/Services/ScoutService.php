<?php
namespace XRA\Extend\Services;

use TeamTNT\TNTSearch\Indexer\TNTGeoIndexer;
use TeamTNT\TNTSearch\TNTSearch;
use TeamTNT\TNTSearch\TNTGeoSearch;

//use Laravel\Scout\Searchable;
//use TeamTNT\TNTSearch\Indexer\TNTGeoIndexer;
//use TeamTNT\TNTSearch\TNTGeoSearch;
//"message": "SQLSTATE[HY000]: General error: 5 database is locked", "exception": "PDOException", "file": "/var/www/html/lara/fpb/laravel/vendor/teamtnt/tntsearch/src/Indexer/TNTIndexer.php", "line": 604,
//use TeamTNT\TNTSearch\TNTSearch;
//Model::disableSearchSyncing();
//$model->save()

use Illuminate\Http\Request;
//---------CSS----------

class ScoutService{

	public static function import($params)
    {
        \extract($params);
        $tnt = new TNTSearch();
        $driver = config('database.default');
        $config = config('scout.tntsearch') + config("database.connections.$driver");
        $tnt->loadConfig($config);
        //app('db')->connection()->getPdo()
        $pdo = $model->getConnection()->getPdo();
        $tnt->setDatabaseHandle($pdo);
        $indexer = $tnt->createIndex($model->searchableAs().'.index');
        $indexer->setPrimaryKey($model->getKeyName());
        $fields = \implode(', ', \array_keys($model->toSearchableArray()));
        $query = "{$model->getKeyName()}, $fields";
        if ('' == $fields) {
            $query = '*';
        }
        $indexer->query("SELECT $query FROM {$model->getTable()};");
        $indexer->run();
        //return ArtisanTrait::exe('scout:import '.get_class($model));
           // $comando='scout:import '.get_class($model);
           // Artisan::call($comando);
           // return '[<pre>' . Artisan::output() . '</pre>]';
    }

    public static function geoImport($params)
    {
        \extract($params);
        $index = $model->searchableAs().'.geo.index';
        $driver = config('database.default');
        $config = config('scout.tntsearch') + config("database.connections.$driver");
        $indexer = new TNTGeoIndexer();
        $indexer->loadConfig($config);

        $indexer->createIndex($index);
        $indexer->setPrimaryKey($model->getKeyName());
        $fields = \implode(', ', \array_keys($model->toSearchableArray()));
        $query = "{$model->getKeyName()}, $fields";
        if ('' == $fields) {
            $query = '*';
        }
        $indexer->query("SELECT $query FROM {$model->getTable()};");
        $indexer->run();
    }

    public static function findNearest($params){
        return self::findNearestTnt($params);
    }

    public static function findNearestTnt($params){
        extract($params);
        //$this->scoutGeoImport(['model'=>$model]); //da fare refresh una volta ogni tanto..
        $index = $model->searchableAs().'.geo.index';
        //ddd($index);//blog_post_restaurants.geo.index
        //C:\xampp\htdocs\lara\fpb\laravel\storage/blog_post_restaurants.geo.index
        if (!\File::exists(storage_path($index))) {
            ScoutService::geoImport(['model' => $model]);
        }
        $driver = config('database.default');
        $config = config('scout.tntsearch') + config("database.connections.$driver");
        $candyShopIndex = new TNTGeoSearch();
        $candyShopIndex->loadConfig($config);
        $candyShopIndex->selectIndex($index);
        $candyShops = $candyShopIndex->findNearest($currentLocation, $distance, $limit);
        return $candyShops;
    } 


}//end class