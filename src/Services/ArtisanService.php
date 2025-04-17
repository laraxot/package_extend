<?php

namespace XRA\Extend\Services;

use Artisan;

//----- TODO
//--  1) capire come far fare da chiamato non da consolle "scout:import"

class ArtisanService{

    public static function  act($act){ //da fare anche in noconsole, e magari mettere un policy
        switch($act){
            case 'migrate'      : return ArtisanService::exe('migrate');
            case 'routelist'    : return ArtisanService::exe('route:list');
            case 'optimize'     : return ArtisanService::exe('optimize');

            case 'clearcache'   : return ArtisanService::exe('cache:clear');
            case 'routecache'   : return ArtisanService::exe('route:cache');
            case 'routeclear'   : return ArtisanService::exe('route:clear');
            case 'viewclear'    : return ArtisanService::exe('view:clear');
            case 'configcache'  : return ArtisanService::exe('config:cache');
            default             : return '';
        }
        return '';
    }


    public static function exe($command, array $arguments = [])
    {
        try {
            $output = '';

            Artisan::call($command, $arguments);
            $output .= '[<pre>'.Artisan::output().'</pre>]';

            return $output;  // dato che mi carico solo le route minime menufull.delete non esiste.. impostare delle route comuni.
        } catch (Exception $e) {
            return '<br/>'.$command.' non effettuato';
        }
        //return view('Backend::admin.index')->with('output', $output);
    }
}
