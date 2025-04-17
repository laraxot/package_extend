<?php



namespace XRA\Extend\Services;

use Cache;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
//use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\FileCookieJar;

class ImportGoutteService
{
    public static $goutteClient = null;
    public static $guzzleClient = null;
    public static $client = null;
    public static $client_options = [];

    public static function importInit()
    {
        \ini_set('max_execution_time', 3000);
        $params = \Route::current()->parameters();
        $cookieFile = './jar.txt';
        $cookieJar = new FileCookieJar($cookieFile, true);
        //$cookieJar = new CookieJar();
        /*
        // file to store cookie data
$cookieFile = 'cookie_jar.txt';

$cookieJar = new FileCookieJar($cookieFile, TRUE);
        */
        \header('Access-Control-Allow-Origin', '*');
        \header('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE');
        \header('Access-Control-Allow-Headers', 'Content-Type');
        $headers = [];
        $headers['User-Agent'] = \Request::header()['user-agent'][0];
        $headers = \array_merge($headers, ['Access-Control-Allow-Origin', 'Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type', 'CORELATION_ID']);
        //self::$client_options['base_uri']='https://';
        self::$client_options['allow_redirects'] = true;
        self::$client_options['headers'] = $headers;
        self::$client_options['cookies'] = $cookieJar;
        self::$client_options['debug'] = \fopen(base_path('../curl_error.log'), 'w');
        self::$client_options['headers']['referer'] = 'http://www.google.com';
        self::$client_options['verify'] = false;
        self::$client_options['defaults']['verify'] = false;
        //ddd(self::$client_options);
        self::$goutteClient = new GoutteClient();
        self::$guzzleClient = new GuzzleClient(self::$client_options);
        self::$goutteClient->setClient(self::$guzzleClient);
        self::$client = &self::$goutteClient;
    }

    //end __construct

    public static function getID($method, $url, $attrs = [])
    {
        $url_parse = \parse_url($url);
        $dir = str_slug(\str_replace('.', '-', $url_parse['host']));
        $file = str_slug(\str_replace('.', '-', $url_parse['path']));
        $path = $dir.'/'.$file.'-'.str_slug(\json_encode($attrs));

        return $path.'_3';
    }

    public static function submit($form, $vars)
    {
        return self::$client->submit($form, $vars);
    }

    public static function click($link)
    {
        return self::$client->click($link);
    }

    public static function gRequest($method, $url, $attrs = [])
    {
        if (null == self::$client) {
            self::importInit();
        }
        try {
            $ris = self::$client->request($method, $url/*,array_merge(self::$client_options,$attrs)*/);
        } catch (Exception $e) {
            \var_dump($e);
        }
        self::$client_options['headers']['referer'] = $url; //in teoria lo fa da solo
        return $ris;
    }

    public static function cacheRequest($method, $url, $attrs = [])
    {
        $key = self::getID($method, $url, $attrs);
        $value = Cache::rememberForever($key, function () use ($method,$url,$attrs) {
            $crawler = self::gRequest($method, $url, $attrs);

            return \serialize($crawler);
        });
        self::$client_options['headers']['referer'] = $url;

        return \unserialize($value);
    }
}//end class
