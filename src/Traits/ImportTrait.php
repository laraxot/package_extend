<?php



namespace XRA\Extend\Traits;

use App\Http\Controllers\Controller;
use Cache;
use GuzzleHttp\Client as GuzzleClient;
//use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;

/*
https://shanedowling.com/simple-api-mocking-guzzle-charles
*/

trait ImportTrait
{
    protected $start_time;
    protected $client = null;
    protected $client_options = [];
    public $cacheType = 'cache'; //cache o file

    public function importInit()
    {
        \ini_set('max_execution_time', '3000');
        $route_current = \Route::current();
        if (\is_object($route_current)) {
            $params = $route_current->parameters();
            if (isset($params['lang'])) {
                \App::setLocale($params['lang']);
            }
        }
        $this->start_time = \microtime(true);
        $jar = new \GuzzleHttp\Cookie\CookieJar();
        $headers = [];
        if (isset(\Request::header()['user-agent'])) {
            $headers['User-Agent'] = \Request::header()['user-agent'][0];
        } else {
            $headers['User-Agent'] = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36';
        }
        $this->client = new GuzzleClient();
        $this->client_options = ['headers' => $headers, 'cookies' => $jar];
        $this->client_options['headers']['referer'] = 'http://www.google.com';
        /* // start charles proxy
        $this->client_options['proxy']='127.0.0.1:8888';
        $this->client_options['verify']='D:\xampp\charles-proxy-ssl-proxying-certificate.pem';
       //$this->client->setDefaultOption('verify', 'D:\xampp\charles-proxy-ssl-proxying-certificate.pem');
        // end charles proxy */
    }

    //end __construct

    public function getEffectiveUrl($method, $api_url, $attrs = [])
    {
        $attrs['allow_redirects'] = [
            'max' => 10,        // allow at most 10 redirects.
            'strict' => true,      // use "strict" RFC compliant redirects.
            'referer' => true,      // add a Referer header
            //'protocols'       => ['https'], // only allow https URLs
            //'on_redirect'     => $onRedirect,
            'track_redirects' => true,
        ];
        $res = $this->client->request($method, $api_url, \array_merge($this->client_options, $attrs));

        return $res->getHeaderLine('X-Guzzle-Redirect-History');
    }

    public function gRequest($method, $api_url, $attrs = [])
    {
        if (null == $this->client) {
            $this->importInit();
        }
        try {
            $res = $this->client->request($method, $api_url, \array_merge($this->client_options, $attrs));
        } catch (GuzzleException $e) {
            //*
            if (isset($proxy)) {
                echo '<h3>Proxy :'.$proxy.'</h3>';
            }
            echo '<h3>getRequest ['.$api_url.']</h3>';
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
        }
        $this->client_options['headers']['referer'] = $api_url;

        return $res;
    }

    public function cacheRequest($method, $api_url, $attrs = [])
    {
        $key = \json_encode(['method' => $method, 'api_url' => $api_url, 'attrs' => $attrs]);
        $key .= '_1';
        $value = Cache::rememberForever($key, function () use ($method,$api_url,$attrs) {
            $res = $this->gRequest($method, $api_url, \array_merge($this->client_options, $attrs));
            if (isset($res->is_error)) {
                return null;
            }
            $body = $res->getBody();

            return (string) $body;
        });
        $this->client_options['headers']['referer'] = $api_url;

        return $value;
    }

    public function cacheRequestFile($method, $api_url, $attrs = [])
    { //--- uguale ma al posto di usare il sistema cache usa i file
        $file_path = (str_slug($this->base_url, '_').'/'.str_slug($api_url, '_').'.json');
        if (\Storage::disk('cache')->exists($file_path)) {
            $content = \Storage::disk('cache')->get($file_path);
            $this->client_options['headers']['referer'] = $api_url;

            return $content;
        }
        $gres = $this->gRequest($method, $api_url, \array_merge($this->client_options, $attrs));
        if (isset($gres->is_error)) {
            /*
            dd($gres);
            $e=$gres;
            echo '<h3>getRequest</h3>';
            echo '<pre>';print_r($e->getRequest()); echo '</pre>';
            echo '<h3>getResponse</h3>';
            echo '<pre>';print_r($e->getResponse()); echo '</pre>';
            if($e->getResponse()!=null){
                echo '<h3>getResponse->getBody</h3>';
                echo '<pre>';print_r($e->getResponse()->getBody()); echo '</pre>';
            }
            die('['.__LINE__.']['.__FILE__.']');
            */
            $body = '';
        } else {
            $body = $gres->getBody();
        }

        $res = \Storage::disk('cache')->put($file_path, (string) $body);
        $this->client_options['headers']['referer'] = $api_url;
        //echo '<br/>da sito ['.$api_url.']';
        return (string) $body;
    }

    public function cacheUrl($url)
    {//-- shortcut
        if ('file' == $this->cacheType) {
            return $this->cacheUrlFile($url);
        }

        return $this->cacheRequest('GET', $url);
    }

    //end cacheUrl

    public function cacheUrlFile($url)
    {//-- shortcut
        return $this->cacheRequestFile('GET', $url);
    }

    //end cacheUrl
    ///------------ SIMPLE ENGINES  complex has controller model etc
    public function pixabay($params)
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
        $json = $this->cacheRequest('GET', $pixabay_url);
        $json = \json_decode($json);
        if (!isset($json->hits)) {
            return null;
        }
        $ris = collect($json->hits)->shuffle()->first();

        return $ris;
    }

    public function pexels($params)
    {
        $lang = \App::getLocale();
        \extract($params);
        //--- devono mandare via mail api key ..
        //dd($this->client);
        $url = 'https://api.pexels.com/v1/search?query='.$q.'&per_page=15&page=1';
    }

    public function qwantImg($params)
    {
        $lang = \App::getLocale();
        \extract($params);
        /*
        $parz = [
            'count' => $count,
            'offset' => $offset,
            'q' => $query,
            't' => 'all',
            'device' => 'desktop',
            'safesearch' => $safesearch ? 1 : 0,
            'locale' => $language,
        ];
        http_build_query($params)
        */

        $url = 'https://api.qwant.com/api/search/images?count=20&offset=1&q='.\urlencode($q);
        $json = $this->cacheRequest('GET', $url);
        $json = \json_decode($json);
        //echo '<h3>['.$q.']</h3>';
        if (!\is_object($json)) {
            return null;
        }
        if (!isset($json->data)) {
            dd($json);
        }
        $items = $json->data->result->items;
        $ris = [];
        foreach ($items as $item) {
            if (false === \mb_stripos($item->title, 'vendita')
                && false === \mb_stripos($item->title, 'casa')
                && false === \mb_stripos($item->title, 'concessionaria')
                && false === \mb_stripos($item->title, 'auto')
                && false === \mb_stripos($item->title, 'affitti')
                && false === \mb_stripos($item->title, 'How to reach')
                && false === \mb_stripos($item->title, 'IMMOBILGEST')
                && false === \mb_stripos($item->title, 'INTERNALS')
                && false === \mb_stripos($item->title, 'Affitto')
                && false === \mb_stripos($item->title, 'immobiliari')
                && false === \mb_stripos($item->title, 'Yamamay')
                && false === \mb_stripos($item->title, 'Arcaplanet')
                && false === \mb_stripos($item->title, 'Punto Snai')
            ) {
                $ris[] = $item;
            }
        }

        $img = (collect($ris)->unique()->shuffle()->first());
        $image_path = $img->media_fullsize;
        $str0 = 'https://s1.qwant.com'; //questo fa un redirect
        $str1 = 'http://s1.qwant.com';
        $str2 = 'https://s2.qwant.com'; //questo fa un redirect
        $str3 = 'http://s2.qwant.com';
        $str4 = '//s1.qwant.com'; //questo fa un redirect
        $str5 = '//s2.qwant.com';
        if (\mb_substr($image_path, 0, \mb_strlen($str0)) == $str0 || \mb_substr($image_path, 0, \mb_strlen($str1)) == $str1 ||
           \mb_substr($image_path, 0, \mb_strlen($str2)) == $str2 || \mb_substr($image_path, 0, \mb_strlen($str3)) == $str3 ||
           \mb_substr($image_path, 0, \mb_strlen($str4)) == $str4 || \mb_substr($image_path, 0, \mb_strlen($str5)) == $str5
        ) {
            $pos = \mb_strpos($image_path, '?');
            $image_path1 = \mb_substr($image_path, $pos + 1);
            \parse_str($image_path1, $output);
            //echo '<h3>'.$image_path1.'</h3>';
            //dd($output);
            $image_path = \urldecode($output['u']);
        }
        $img->media_fullsize = $image_path;

        return $img;
    }

    public function wikiquote($params)
    {
        //$q= necessary
        \extract($params);
        $wikiquote_url = 'https://it.wikiquote.org/wiki/'.$q;
        $html = $this->cacheRequest('GET', $wikiquote_url);
        //$html=@file_get_contents($wikiquote_url);

        $crawler = new Crawler((string) $html);
        $filter = 'ul li';
        $rows = $crawler
                ->filter($filter)
                ->each(function (Crawler $node) use ($q) {
                    $html = \strip_tags($node->html());
                    //return $html;
                    if (false !== \mb_stripos($html, $q)
                        && \mb_strlen($html) > 30
                        && false === \mb_stripos($html, 'commons')
                        && false === \mb_stripos($html, 'Wikibooks')
                        && false === \mb_stripos($html, 'Wikizionario')
                        && false === \mb_stripos($html, 'Wikipedia')
                        && false === \mb_stripos($html, 'How to reach')
                        && false === \mb_stripos($html, 'IMMOBILGEST')
                    ) {
                        return $html;
                    }
                });
        $txt = collect($rows)->unique()->shuffle()->first();

        return $txt;
    }

    //--- http://aforismi.meglio.it/ aforismi su altri alimenti..

    public function trans($params)
    {
        $i = \rand(0, 20);
        if ($i > 0 && $i < 10) {
            return $this->googleTrans($params);
        }

        return $this->mymemoryTrans($params);
    }

    public function apertiumTrans($params)
    {
        //https://github.com/24aitor/Laralang/blob/master/src/Builder/ApertiumTrans.php
        //$host = 'api.apertium.org';
        //$urldata = file_get_contents("http://$host/json/translate?q=$urlString&langpair=$this->from|$this->to");
        //$data = json_decode($urldata, true);
    }

    public function googleTrans($params)
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

    public function mymemoryTrans($params)
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
}
