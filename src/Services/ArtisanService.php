<?php
namespace XRA\Extend\Services;

use Artisan;

//----- TODO
//--  1) capire come far fare da chiamato non da consolle "scout:import"

class ArtisanService
{
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
