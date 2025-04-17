<?php
namespace XRA\Extend\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Cookie\SetCookie;
//use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Stream;
use Psr\Http\Message\RequestInterface;
//https://www.sitepoint.com/guzzle-php-http-client/
///*
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\DomCrawler\Crawler;

//*/

class ImportService
{
    public static $client = null;
    public static $client_options = [];
    public static $res = null;
    public static $cookieJar = null;

    public static function setClientOptions($data = [])
    {
        self::$client_options = \array_merge(self::$client_options, $data);
        //ddd(self::$client_options);
    }

    public static function initCookieJar()
    {
        $cookieFile = base_path('../jar.txt');
        self::$cookieJar = new FileCookieJar($cookieFile, true);
    }

    public static function importInit()
    {
        \ini_set('max_execution_time', 3000);
        $params = \Route::current()->parameters();
        //$cookieJar = new CookieJar();

        if (null == self::$cookieJar) {
            self::initCookieJar();
        }
        /*
        $headers_key=collect(\Request::header())->map(function($item,$key){	return title_case($key); })->all();
        $headers_value=collect(\Request::header())->map(function($item,$key){ return $item[0]; })->all();
        $headers=array_combine($headers_key,$headers_value);
        */
        //$headers=\Request::header();
        //$headers['Upgrade-Insecure-Requests'] = 1;
        //$headers['Referer']='http://www.google.com';
        //unset($headers['Cache-Control']);
        //unset($headers['Cookie']);
        //unset($headers['Host']);
        $headers = [];
        $fields = ['User-Agent', 'Accept', 'Accept-Language', 'Accept-Encoding', 'Connection', 'Cookie', 'Upgrade-Insecure-Requests', 'Cache-Control'];
        foreach ($fields as $field) {
            $headers[$field] = \Request::header($field);
        }
        //ddd($headers);
        //ddd(\Request::header('Accept'));
        /*
        $headers['Accept']= text/html,application/xhtml+xml,application/xml;q=0.9,* / *;q=0.8
        Accept-Language: en-US,en;q=0.5
        Accept-Encoding: gzip, deflate, br
        Connection: keep-alive
        Upgrade-Insecure-Requests: 1
        */
        //ddd($headers);
        self::enableRedirect();
        self::$client_options['headers'] = $headers;
        self::$client_options['headers']['Referer'] = 'http://www.google.com';
        self::$client_options['cookies'] = self::$cookieJar;
        self::$client = new GuzzleClient(self::$client_options);
    }

    //end __construct

    //https://hotexamples.com/examples/guzzlehttp.cookie/CookieJar/-/php-cookiejar-class-examples.html
    // google trend example
    //https://hotexamples.com/examples/guzzlehttp.cookie/CookieJar/setCookie/php-cookiejar-setcookie-method-examples.html

    public static function enableCharles()
    {
        $proxy = [
            //'http'  => 'tcp://127.0.0.1:8888', // Use this proxy with "http"
            //'https' => 'tcp://127.0.0.1:8888', // Use this proxy with "https",
            'http' => 'http://127.0.0.1:8888', // Use this proxy with "http"
            'https' => 'https://127.0.0.1:8888', // Use this proxy with "https",

            //'no' => ['.mit.edu', 'foo.com']    // Don't use a proxy with these
        ];
        self::setClientOptions([
            'proxy' => $proxy,
            'verify' => false,
        ]);
        //senza verify false errore = #message: "cURL error 60: SSL certificate problem: self signed certificate in certificate chain (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)"
    }

    public static function setCookie(array $cookies)
    {
        //$cookieJar->setCookie(SetCookie::fromString('SID="AuthKey 23ec5d03-86db-4d80-a378-6059139a7ead"; expires=Thu, 24 Nov 2016 13:52:20 GMT; path=/; domain=.sketchup.com'));
        if (null == self::$cookieJar) {
            self::initCookieJar();
        }

        $url_info = \parse_url(self::$client_options['base_uri']);
        $domain = $url_info['host'];
        foreach ($cookies as $name => $value) {
            $cookieData = [
                'Domain' => $domain,
                'Name' => $name,
                'Value' => $value,
                'Discard' => true,
            ];
            self::$cookieJar->setCookie(new SetCookie($cookieData));
        }
        self::$client_options['cookies'] = self::$cookieJar;
    }

    public static function enableRedirect()
    {
        $onRedirect = function (RequestInterface $request, ResponseInterface $response, UriInterface $uri) {
            echo '<hr/>Redirecting! '.$request->getUri().' to '.$uri."\n";
        };
        $redirect_params = [
                'max' => 10,        // allow at most 10 redirects.
                'strict' => true,      // use "strict" RFC compliant redirects.
                'referer' => true,      // add a Referer header
                //'protocols'       => ['https'], // only allow https URLs
                'on_redirect' => $onRedirect,
                'track_redirects' => true,
        ];
        self::setClientOptions(['allow_redirects' => $redirect_params]);
        //$client->followRedirects(true);
    }

    public static function disableRedirect()
    {
        self::setClientOptions(['allow_redirects' => false]);
    }

    public static function getConfig($x)
    {
        //$cookieJar = $client->getConfig('cookies');
        //$cookieJar->toArray();
        return self::$client->getConfig($x);
    }

    public function getEffectiveUrl($method, $url, $attrs = [])
    {
        $attrs['allow_redirects'] = [
            'max' => 10,        // allow at most 10 redirects.
            'strict' => true,      // use "strict" RFC compliant redirects.
            'referer' => true,      // add a Referer header
            //'protocols'       => ['https'], // only allow https URLs
            //'on_redirect'     => $onRedirect,
            'track_redirects' => true,
        ];
        $res = $this->client->request($method, $url, \array_merge($this->client_options, $attrs));

        return $res->getHeaderLine('X-Guzzle-Redirect-History');
    }

    public static function jqueryRequest($method, $url, $attrs = []){
        return view('extend::jquery_request');
    }

    public static function gRequest($method, $url, $attrs = [], $out = 'res')
    {
        if (null == self::$client) {
            self::importInit();
        }
        if (!isset(self::$client_options['base_uri'])) {
            $url_info = \parse_url($url);
            self::$client_options['base_uri'] = $url_info['scheme'].'://'.$url_info['host'];
            $url = $url_info['path'];
            if (isset($url_info['query'])) {
                $url .= '?'.$url_info['query'];
            }
        }

        $base_uri=self::$client_options['base_uri'];
        if(Str::startsWith($url,$base_uri)){
            $url=substr($url, strlen($base_uri));
        }

        try {
            $res = self::$client->request($method, $url, \array_merge(self::$client_options, $attrs));
        } catch (GuzzleException $e) {
            //*
            if (isset($proxy)) {
                echo '<h3>Proxy :'.$proxy.'</h3>';
            }
            echo '<h3>getRequest ['.$url.']</h3>';
            echo '<pre>';
            \print_r($e->getRequest());
            echo '</pre>';
            echo '<h3>getResponse</h3>';
            echo '<pre>';
            \print_r($e->getResponse());
            echo '</pre>';
            if (null != $e->getResponse()) {
                echo '<h3>getResponse->getBody</h3>';
                echo '<pre>';
                \print_r($e->getResponse()->getBody());
                echo '</pre>';
            }
            //dd($e);
            //*/
            $e->is_error = 1;

            return $e;
        } catch (GuzzleHttp\Exception\RequestException $e) {
            ddd('ooo');
        } catch (FatalThrowableError $e) {
            ddd('qui');
        } catch (GuzzleException\RequestException $e) {
            ddd('3');
        } catch (RequestException $e) {
            ddd('4');
        }

        //echo $res->getStatusCode(); // 200
        //echo $res->getHeaderLine('X-Guzzle-Redirect-History');// http://first-redirect, http://second-redirect, etc...
        //echo $res->getHeaderLine('X-Guzzle-Redirect-Status-History');// 301, 302, etc...
        self::$res = $res;

        self::$client_options['headers']['Referer'] = self::$client_options['base_uri'].$url;
        switch ($out) {
            case 'res': return $res;
            case 'html':
                $html = (string) $res->getBody();

                return $html;
            case 'crawler':
                $html = (string) $res->getBody();
                $crawler = new Crawler((string) $html, self::$client_options['base_uri']);

                return $crawler;
        }

        return $res;
    }

    public static function getStatusCode()
    {
        return self::$res->getStatusCode();
    }

    public static function getRedirectHistory()
    {
        return self::$res->getHeaderLine('X-Guzzle-Redirect-History'); // http://first-redirect, http://second-redirect, etc...
        //echo $res->getHeaderLine('X-Guzzle-Redirect-Status-History');// 301, 302, etc...
    }

    public static function submit($form, $vars, $out)
    {
        $vars = \array_merge($form->getValues(), $vars);

        return self::gRequest($form->getMethod(), $form->getUri(), ['form_params' => $vars], $out);
    }

    public static function getCacheKey($method, $url, $attrs = []){
        $key = \json_encode(['method' => $method, 'url' => $url, 'attrs' => $attrs]);
        $key .= '_1';
        return $key;
    }

    public static function cacheRequest($method, $url, $attrs = [])
    {
        $key=self::getCacheKey($method, $url, $attrs = []);
        $value = Cache::store('file')->rememberForever($key, function () use ($method,$url,$attrs) {
            $res = self::gRequest($method, $url, $attrs);
            if (isset($res->is_error)) {
                return null;
            }
            $body = $res->getBody();

            return (string) $body;
        });
        self::$client_options['headers']['referer'] = $url;

        return $value;
    }

    public static function cacheRequestFile($method, $url, $attrs = [])
    { //--- uguale ma al posto di usare il sistema cache usa i file
        if (!isset(self::$client_options['base_uri'])) {
            $url_info = \parse_url($url);
            if (isset($url_info['scheme']) && isset($url_info['host'])) {
                self::$client_options['base_uri'] = $url_info['scheme'].'://'.$url_info['host'];
            } else {
                self::$client_options['base_uri'] = '';
            }
            $url = $url_info['path'];
            if (isset($url_info['query'])) {
                $url .= '?'.$url_info['query'];
            }
        }

        $file_path = (str_slug(self::$client_options['base_uri'], '_').'/'.str_slug($url, '_').'.json');
        //$params=['method'=>$method,'url'=>$url,'attrs'=>$attrs];
        //$key=json_encode(array_values($params));
        //$key=str_slug
        if (\Storage::disk('cache')->exists($file_path)) {
            $content = \Storage::disk('cache')->get($file_path);
            self::$client_options['headers']['referer'] = $url;

            return $content;
        }
        $gres = self::gRequest($method, $url, $attrs);
        if (isset($gres->is_error)) {
            $body = '';
        } else {
            $body = $gres->getBody();
        }

        $res = \Storage::disk('cache')->put($file_path, (string) $body);
        self::$client_options['headers']['referer'] = $url;
        //echo '<br/>da sito ['.$url.']';
        return (string) $body;
    }

    public static function getAddressFields($params){
        \extract($params);
        $linked = new \stdClass();
        $location_url = config('bing.url_location_api').'?query='.\urlencode($address).'&maxResults=5&key='.config('bing.maps_key');
        $location_url = config('google.url_location_api').'?address='.\urlencode($address).'&key='.config('google.maps_key');
        $loc_json = self::cacheRequest('GET', $location_url);
        $loc_obj = \json_decode($loc_json);
        if (isset($loc_obj->results[0])) {
            $loc_obj = $loc_obj->results[0];
           //ddd($loc_obj->address_components);
            foreach ($loc_obj->address_components as $addr) {
                //echo '<br/>'.$addr->post_types[0].'  '.$addr->long_name.'  '.$addr->short_name;
                $sk = $addr->types[0];
                $linked->$sk = $addr->long_name;
                $sk .= '_short';
                $linked->$sk = $addr->short_name;
            }
            $linked->latitude = $loc_obj->geometry->location->lat;
            $linked->longitude = $loc_obj->geometry->location->lng;
        } else {
            dd($loc_obj);
        }

        return \get_object_vars($linked);
    }
    /*
    public function download($url, $name, $extensions){
       $path = __DIR__.'/download/' . $name . $extensions;
       $file_path = fopen($path,'w');
       $client = new \GuzzleHttp\Client();
       $response = $client->get($url, ['save_to' => $file_path]);
       return ['response_code'=>$response->getStatusCode(), 'name' => $name];
    }
    */
    /*
    $resource = fopen('/path/to/file', 'w');
    $stream = GuzzleHttp\Psr7\stream_for($resource);
    $client->request('GET', '/stream/20', ['save_to' => $stream]);
    */
    /*
    $resource = fopen('/path/to/file', 'w');
    $client->request('GET', '/stream/20', ['sink' => $resource]);
    */
    //new guzzle client setup
/*
$client = new GuzzleHttp\Client([ 'base_uri' => 'http://whatever' ]);
//create a php temp file (returns a resource)
$putStream = tmpfile();
//guzzle get() and sink into resource
$client->get('/stream/20', ['sink' => $putStream]);
rewind($putStream);
//store
Storage::disk('local')->put('somewhere/here.txt', $putStream);
//release tempfile
fclose($putStream);
*/

    //https://phpnews.io/feeditem/chunked-transfer-encoding-in-php-with-guzzle
    public static function download($params){
        //$url  
        //$filename
        extract($params);
        $resource_out=fopen($filename, 'w');
        self::gRequest('get',$url,['save_to'=>$resource_out]);
        fclose($resource_out);
        /*
        Call to undefined function GuzzleHttp\Stream\create
        $resource_in=fopen($url, 'r');
        $resource_out=fopen($filename, 'w');
        $original = Stream\create($resource_in); 
        $local = Stream\create($resource_out); 
        $local->write($original->getContents());
        fclose($resource_out);
        fclose($resource_in);
        */

        /*
        $fp = fopen($path, "wb");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //$headerArr = array("HTTP/1.1","Accept-Encoding: gzip;q=1.0,compress;q=0.5,identity;q=0");
        //curl_setopt($ch,CURLOPT_HTTPHEADER,$headerArr);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        //'choice1'=>'Scarica',
        //'choice1001'=>'ZIP'
        $result =curl_exec($ch);
            // 'param0'=>'ced03f',
            // 'choice1'=>"  Scarica  ",
        curl_close($ch);
        fclose($fp);
        */
    }
    /*
    public function upload_to_test($params){
        // Open a stream so that we stream the image download
        $stream = fopen($url, 'r');

        // Create a curl handle to upload to the file server
        $ch = curl_init($fileServer);
        // Send a PUT request
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        // Let curl know that we are sending an entity body
        curl_setopt($ch, CURLOPT_UPLOAD, true);
        // Let curl know that we are using a chunked transfer encoding
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Transfer-Encoding: chunked'));
        // Use a callback to provide curl with data to transmit from the stream
        curl_setopt($ch, CURLOPT_READFUNCTION, function($ch, $fd, $length) use ($stream) {
            return fread($stream, $length) ? '';
        });

        curl_exec($ch);
    }//end upload
    */



    //end function
    public static function pixabay($params)
    {
        $lang = \App::getLocale();
        $image_type = 'photo';
        //$q= necessary
        \extract($params);
        $pixabay_url = 'https://pixabay.com/api/?key=7945761-cdc8fef41b0600630fdabe778';
        $pixabay_url .= '&lang='.$lang;
        $pixabay_url .= '&image_type='.$image_type;
        $pixabay_url .= '&q='.$q;
        $pixabay_url = \str_replace(' ', '%20', $pixabay_url);
        $json = self::cacheRequest('GET', $pixabay_url);
        $json = \json_decode($json);
        if (!isset($json->hits)) {
            return null;
        }
        $ris = collect($json->hits)->shuffle()->first();

        return $ris;
    }

    public static function pexels($params)
    {
        $lang = \App::getLocale();
        \extract($params);
        //--- devono mandare via mail api key ..
        //dd($this->client);
        $url = 'https://api.pexels.com/v1/search?query='.$q.'&per_page=15&page=1';
    }

    //-------------------------------------------------------------------------
    public static function trans($params)
    {
        $i = \rand(0, 20);
        if ($i > 0 && $i < 10) {
            return self::googleTrans($params);
        }

        return self::mymemoryTrans($params);
    }

    public static function apertiumTrans($params)
    {
        //https://github.com/24aitor/Laralang/blob/master/src/Builder/ApertiumTrans.php
        //$host = 'api.apertium.org';
        //$urldata = file_get_contents("http://$host/json/translate?q=$urlString&langpair=$this->from|$this->to");
        //$data = json_decode($urldata, true);
    }

    public static function googleTrans($params)
    {
        $host = 'translate.googleapis.com';
        \extract($params);

        return $q;
        $q = \urlencode($q);
        $urldata = \file_get_contents("https://translate.googleapis.com/translate_a/single?client=gtx&sl=$from&tl=$to&dt=t&q=$q");
        $tr = $urldata;
        $tr = \mb_substr($tr, 3, -6);
        $tr = $tr.'["';
        $tr = \explode('],[', $tr);
        $trans = [];
        foreach ($tr as $tran) {
            $transl = \explode('","', $tran);
            \array_push($trans, \str_replace('\"', '"', \ucfirst(\mb_substr($transl[0], 1))));
        }

        return \trim(\implode(' ', $trans));
    }

    public static function mymemoryTrans($params)
    {
        $host = 'api.mymemory.translated.net';
        \extract($params);

        return $q;
        $q = \urlencode($q);
        $urldata = \file_get_contents('http://'.$host.'/get?q='.$q.'&langpair='.$from.'|'.$to.'');
        $data = \json_decode($urldata, true);
        if (200 != $data['responseStatus']) {
            if (true == $this->debug) {
                if (403 == $data['responseStatus']) {
                    $details = ($data['responseDetails']);
                } else {
                    $details = $data['responseDetails'];
                }
                $translation = "<font style='color:red;'>Error ".$data->responseStatus.': '.$details.'</font>';
            }

            return;
        }

        return $data['responseData']['translatedText'];
    }

    //end mymemoryTrans;

}//end class
