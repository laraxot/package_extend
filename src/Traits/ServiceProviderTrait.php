<?php



namespace XRA\Extend\Traits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
//use Symfony\Component\HttpFoundation\Request;

//use Symfony\Component\HttpFoundation\Cookie;

trait ServiceProviderTrait
{
    /** https://www.larashout.com/service-providers-in-laravel
    * Indicates if loading of the provider is deferred.
    *
    * @var bool
    */
    //protected $defer = true;  mi da errore 500
    /**
     * Bootstrap the application services.
     */
    public function boot(\Illuminate\Routing\Router $router)
    {
       
        //$this->registerPolicies();  //in fase di register legge la cartella policy modello pluto policy plutoPolicy
        $seg01 = (\Request::segment(1)); //il primo segmento potrebbe essere o lingua o admin
        if (\is_array(config('xra.languages'))) {
            if (\in_array($seg01, config('xra.languages'), true)) {
                $this->app->setLocale($seg01);
            }
        }

        $rc = new \ReflectionClass($this);
        //ddd(get_class($this)->getFileName());
        $dir = \dirname($rc->getFileName());

        $class = class_basename($this);
        $class = \str_replace('ServiceProvider', '', $class);
        $class = \mb_strtolower($class);

        $enable_packs = config('xra.enable_packs');
        if (\is_array($enable_packs)) {
            if (!\in_array($class, $enable_packs, true)) {
                return '';
            }
        }

        $this->loadViewsFrom($dir.'/views', $class);
        $this->loadRoutesFrom($dir.'/routes/web.php');
        $this->loadEventsFrom($dir.'/Events');
        $migrate_packs = config('xra.migrate_packs');

        //echo '<br/>'.$class.'';
        if (\is_array($migrate_packs)) {
            if (\in_array($class, $migrate_packs, true)) {
                $this->loadMigrationsFrom($dir.'/Migrations');
            }
        }
        $this->loadTranslationsFrom($dir.'/translations', $class);
        //mergeConfigFrom($path, $key)

        AliasLoader::getInstance()->alias($class, get_class($this));
        ///*
        //$this->files = new Filesystem;
        //if($this->files->exists($dir.'/config/'.$class.'.php')  ){
        //  dd('yeah');
        //&& !$this->files->exists(config_path($class.'.php')) ) {
        if (\file_exists($dir.'/config/'.$class.'.php')) {
            $this->publishes([
            $dir.'/config/'.$class.'.php' => config_path($class.'.php'),
        ]);
        }
        //};
        //*/

        $adm_theme = config('xra.adm_theme');
        $adm_theme_dir = public_path('themes'.\DIRECTORY_SEPARATOR.$adm_theme);
        $pub_theme = config('xra.pub_theme');
        /*
        if(\Request::has('pub_theme')){
            $pub_theme=\Request::input('pub_theme');
            \Cookie::queue('pub_theme', $pub_theme,3600);
            $cookie = cookie('pub_theme',$pub_theme);

        }elseif(\Cookie::get('pub_theme')!=null){
            $pub_theme=\Cookie::get('pub_theme');
        }
        */

        $pub_theme_dir = public_path('themes'.\DIRECTORY_SEPARATOR.$pub_theme);
        //die($pub_theme_dir.'['.__LINE__.']['.__FILE__.']');

        $this->app['view']->addNamespace('adm_theme', $adm_theme_dir);
        $this->app['view']->addNamespace('pub_theme', $pub_theme_dir);
        // \View::addNamespace('pub_theme', $pub_theme_dir);
        $this->loadTranslationsFrom($pub_theme_dir.'/translations', 'pub_theme');
        $this->loadTranslationsFrom($adm_theme_dir.'/translations', 'adm_theme');
        //ddd($pub_theme_dir);
        // Lang::addNamespace($namespace, $path);
    }

    //end boot
    /**
     * I cheated this comes from the AuthServiceProvider extended by the App\Providers\AuthServiceProvider.
     *
     * Register the application's policies.
     */
    /*
    public function registerPolicies(){
    	if(isset($this->policies)){
        	foreach ($this->policies as $key => $value) {
            	Gate::policy($key, $value);
        	}
    	}
    }
    */
    public function loadHelpersFrom($path){
        foreach (\glob($path.'/*.php') as $filename) {
            $filename = \str_replace('/', \DIRECTORY_SEPARATOR, $filename);
            require_once $filename;
        }
    }

    public function loadPoliciesFrom($path){
        $policies=[];
        if(!\File::isDirectory($path)){
            \File::makeDirectory($path, 0777, true, true);
        }
        $policies_file=$path.'/_policies.json';
        if(!\File::exists($policies_file)){
            foreach (\glob($path.'/*.php') as $filename) {
                $name = \str_replace('Policy.php', '', \basename($filename));
                //if($name!=basename($filename)){
                if (ends_with(\basename($filename), 'Policy.php')) {
                    //$model=$this->getNamespace().DIRECTORY_SEPARATOR.'Models'.DIRECTORY_SEPARATOR.$name;
                    //$policy=$this->getNamespace().DIRECTORY_SEPARATOR.'Policies'.DIRECTORY_SEPARATOR.$name.'Policy';
                    $model = $this->getNamespace().'\\Models\\'.$name;
                    $policy = $this->getNamespace().'\\Policies\\'.$name.'Policy';
                    //echo '<br>Policy ['.$model.']['.$policy.']'; //4 debug
                    //Gate::policy($model, $policy);
                    $tmp=new \StdClass();
                    $tmp->model=$model;
                    $tmp->policy=$policy;
                    $policies[]=$tmp;
                }
            }//end foreach
            try{
                \File::put($policies_file,json_encode($policies));
            }catch(\Exception $e){
                dd($e);
            }
        }else{
            $policies=\File::get($policies_file);
            $policies=json_decode($policies);
        }
        foreach($policies as $v){
            Gate::policy($v->model, $v->policy);
        }
       
    }


    public function loadEventsFrom($path){

        $events=[];
        if(!\File::isDirectory($path)){
            \File::makeDirectory($path, 0777, true, true);
        }
        $events_file=$path.'/_events.json';
        if(!\File::exists($events_file)){
            foreach (\glob($path.'/*.php') as $filename) {
                $info=pathinfo($filename); 
                //$tmp->namespace='\\'.$vendor.'\\'.$pack.'\\Events\\'.$info['filename'];
                $event_name=$info['filename'];
                $str='Event';
                if(ends_with($event_name,$str)){
                    $listener_name=substr($event_name, 0,-strlen($str)).'Listener';
                    $event      = $this->getNamespace().'\\Events\\'.$event_name;
                    $listener   = $this->getNamespace().'\\Listeners\\'.$listener_name;
                    if(class_exists($event) && class_exists($listener)){
                        //\Event::listen($event, $listener);
                        $tmp=new \StdClass();
                        $tmp->event=$event;
                        $tmp->listener=$listener;
                        $events[]=$tmp;
                    }
                }
            }
            try{
                \File::put($events_file,json_encode($events));
            }catch(\Exception $e){
                dd($e);
            }

        }else{
            
            $events=\File::get($events_file);
            $events=json_decode($events);
        }
        foreach($events as $v){
            \Event::listen($v->event, $v->listener);
        }
    }//end function

    /**
     * Register the application services.
     */
    public function register()
    {
        $rc = new \ReflectionClass(get_class($this));
        $dir = \dirname($rc->getFileName());

        

        $this->loadHelpersFrom($dir.'/Helpers');
        //-- le policy ora sono "indovinate"
        //https://laraveldaily.com/laravel-5-8-automatic-policy-resolution/
        //$this->loadPoliciesFrom($dir.'/Policies');
        

        foreach (\glob($dir.'/Commands/*.php') as $filename) {
            ddd($filename);
        }
    }

    //https://laracasts.com/discuss/channels/tips/example-on-how-to-use-multiple-locales-in-your-laravel-5-website
    public function map(Router $router, Request $request)
    {
        die('['.__LINE__.']['.__FILE__.']');
        $locale = $request->segment(1);
        $this->app->setLocale($locale);

        $router->group(['namespace' => $this->namespace, 'prefix' => $locale], function ($router) {
            //   require app_path('Http/routes.php');
        });
    }

    public function routes()
    {
        $locale = \Request::segment(1);

        if (\in_array($locale, ['it', 'en', 'de', 'fr'], true)) {
            \App::setLocale($locale);
            //app()->setLocale($locale);
            $start = 1;
        } else {
            $start = 0;
        }

        if ('' != \Request::path()) {
            $piece = 3;
            while ($piece > 0) {
                //dd(\Request::segments());
                //$tmp = explode('/', \Request::path());
                $tmp = \Request::segments();
                $tmp = \array_slice($tmp, $start, $piece);
                $tmp = \implode('_', $tmp);

                $pos = \mb_strpos($tmp, ':');

                if ($pos) {
                    $tmp = \mb_substr($tmp, 0, $pos);
                }

                $filename = 'web_'.$tmp.'.php';
                $rc = new \ReflectionClass(\get_class($this));
                $dir = \dirname($rc->getFileName());
                $tmp = [$dir, 'routes', $filename];
                $filename_dir = \implode(\DIRECTORY_SEPARATOR, $tmp);

                if (\file_exists($filename_dir)) {
                    require $filename_dir;
                }

                --$piece;
            }//end while
        }//endif
    }

    //end routes

    public function getNamespace()
    {
        $rc = new \ReflectionClass(\get_class($this));

        return $rc->getNamespaceName();
    }

    //end getNamespace

    public function getMenuXml()
    {
        $rc = new \ReflectionClass(\get_class($this));
        $dir = \dirname($rc->getFileName());
        $menu_dir = $dir.\DIRECTORY_SEPARATOR.'_menuxml'.\DIRECTORY_SEPARATOR.'admin';

        $menu = \XRA\Extend\Library\XmlMenu_op::dirXML2Obj($menu_dir);
        echo '<pre>';
        \print_r($menu);
        echo '</pre>';
        die('['.__LINE__.']['.__FILE__.']');
    }
}
