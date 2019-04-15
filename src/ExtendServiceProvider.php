<?php
namespace XRA\Extend;
use Illuminate\Translation\Translator;

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
    	return $this->registerTrait();
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
