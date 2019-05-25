<?php
namespace XRA\Extend;
use Illuminate\Translation\Translator;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;


use XRA\Extend\BaseServiceProvider;

//use Illuminate\Support\ServiceProvider;
//--- traits
use XRA\Extend\Traits\ServiceProviderTrait;
//--- services
use XRA\Extend\Services\TranslatorService;


class ExtendServiceProvider extends BaseServiceProvider
{
    use ServiceProviderTrait{
		//boot as protected bootTrait;
		register as protected registerTrait;
	}

    public function register(){
    	$this->registerTranslator();
        $this->registerFormComponents();
        $this->registerFormMacros();
        \View::composer('*', function($view){
            \View::share('view_name', $view->getName());
        });


    	return $this->registerTrait();
    }

    public function registerFormComponents(){
        /*
            $params=\Route::current()->parameters();
    $routename=\Route::currentRouteName();
    extract($params);
    $route_arr=[];
    if($routename!=null){
        $route_arr=explode('.',$routename);
        foreach($route_arr as $k=>$v){
            if(in_array($v,array_keys($params))){
                if(is_object($params[$v])){
                    $route_arr[$k]=$params[$v]->post_type;
                }else{
                    $route_arr[$k]=$params[$v];
                }
            }
        }
    }
    $routename=implode('.',$route_arr);
    if($routename==''){
        $routename='index';
    }
    */
    $lang='field';
    //ddd(\Route::currentRouteName());
    //ddd(\Route::current()->parameters());
    //$lang=$view.'.field';//'pub_theme::'.$routename;
    //$view_path=dirname(\View::getFinder()->find('extend::includes.components.form.text')); //devo dargli una view esistente di partenza
    $view_path=__DIR__.'/views/includes/components/form';

    $blade_component='components.blade.input';
    if(in_admin()){
        $blade_component='backend::includes.'.$blade_component;
    }else{
        $blade_component='pub_theme::layouts.'.$blade_component;
    }
    if(!File::exists($view_path.'/_components.json')){
        $files = File::allFiles($view_path);
        $comps=[];
        foreach($files as $file){
            $filename=$file->getRelativePathname();
            //ddd($file->getPath());///home/vagrant/code/food/laravel/packages/XRA/Extend/src/views/includes/components/form"
            $ext='.blade.php';
            if(ends_with($filename,$ext)){
                $base=substr(($filename),0,-strlen($ext));
                $name=str_replace(DIRECTORY_SEPARATOR,'_',$base);
                $name='bs'.studly_case($name);

                //$comp_view=str_replace('/','.',$base);
                //echo '<br/>'.$base.'  --- '.$name.'  --  '.$comp_view;
                $comp_view=str_replace(DIRECTORY_SEPARATOR,'.',$base);
                //echo '<br/>'.$base.'  --- '.$name.'  --  '.$comp_view;
                $comp_view='extend::includes.components.form.'.$comp_view;
                //echo '<br/> Component ['.$name.']['.xdebug_time_index().']';
                $comp=new \StdClass();
                $comp->name=$name;
                $comp->view=$comp_view;
                $comp->base=$base;
                $comp->dir=$file->getPath();
                $comps[]=$comp;
                /*
                Form::component($name, $comp_view,
                    ['name', 'value' => null,'attributes' => [],'lang'=>$lang,
                    'comp_view'=>$comp_view,
                    'comp_dir'=>$file->getPath(),
                    'blade_component'=>$blade_component]);
                */
            }
        }
        //ddd($comps);
        File::put($view_path.'/_components.json',json_encode($comps));
    }else{
        $comps=File::get($view_path.'/_components.json');
        $comps=json_decode($comps);
    }
    foreach($comps as $comp){
        \Form::component($comp->name, $comp->view,
                    ['name', 'value' => null,'attributes' => [],'lang'=>$lang,
                    'comp_view'=>$comp->view,
                    'comp_dir'=>$comp->dir,
                    'blade_component'=>$blade_component]);
    }


    }



    public function registerFormMacros(){
        //$macros_dir=extend::getPath().'/Form/Macros';
        $macros_dir=__DIR__.'/Form/Macros';
        Collection::make(glob($macros_dir.'/*.php'))
            ->mapWithKeys(function ($path) {
                return [$path => pathinfo($path, PATHINFO_FILENAME)];
            })
            ->reject(function ($macro) {
                return Collection::hasMacro($macro);
            })
            ->each(function ($macro, $path) {
                $class = '\\XRA\\Extend\\Form\\Macros\\'.$macro;
                //app($class)();
                //echo '<hr/>'.$class.'<br/>';
                //\Form::macro(Str::camel($macro), app($class)());
                \Form::macro('bs'.Str::studly($macro), app($class)());
                //ddd($path);
            });
    }
    //--------------------------
    public function registerTranslator(){
    	 // Override the JSON Translator
        $this->app->extend('translator', function (Translator $translator) {
            $trans = new TranslatorService($translator->getLoader(), $translator->getLocale());
            $trans->setFallback($translator->getFallback());
            return $trans;
        });
    }
}
