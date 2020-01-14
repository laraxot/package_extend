<?php



namespace XRA\Extend\Services;

use GuzzleHttp\Client as GuzzleClient;
//----- Models -----

//---- xot extend -----
//use XRA\Extend\Traits\Getter;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
//use GuzzleHttp\Psr7\Request;
//use Guzzle\Http\Client as GuzzleOldClient;
//use Guzzle\Http\Exception\ClientErrorResponseException;

use Symfony\Component\DomCrawler\Crawler;

//-------------------

class ProxyService
{
    protected $client;
    protected $client_options;

    public function __construct()
    {
        //die('['.__LINE__.']['.__FILE__.']');
        $jar = new \GuzzleHttp\Cookie\CookieJar();
        //dd(\Request::header());
        /* -- not working but ..
        $head=[];
        foreach(\Request::header() as $k=>$v){
            $head[$k]=implode('',$v);
        }
        //dd($head);
        $head['referer']='http://www.google.com';
        $head['host']='google.com';
        */
        //dd(\Request::header()['user-agent'][0]);
        $headers = [];
        $headers['User-Agent'] = \Request::header()['user-agent'][0];
        $this->client = new GuzzleClient();
        $this->client_options = ['headers' => $headers, 'cookies' => $jar];
        $this->client_options['headers']['referer'] = 'http://www.google.com';
        //dd($this->client_options);
    }

    public function dogdevProxys(Request $request)
    {
        //$url='http://spys.one/free-proxy-list/IT/';
        $url = 'http://dogdev.net/Proxy/IT';

        $res = $this->client->request('GET', $url, $this->client_options);
        $body = $res->getBody();
        //return $body;
        $crawler = new Crawler((string) $body);
        $filter = '.pure-table tr';
        $catsHTML = $crawler
                    ->filter($filter)
                    ->each(function (Crawler $node) {
                        //return $node->html();
                        //return $this->getNodeListData($node);//$node->html();
                        return $node->filter('td')->each(function (Crawler $node1) {
                            return $node1->html();
                        });
                    });
        unset($crawler);
        //dd($catsHTML);
        $proxys = [];
        \reset($catsHTML);
        foreach ($catsHTML as $row) {
            $tmp = \strip_tags(\trim($row[0]));
            if (false !== \mb_strpos($tmp, ':')) {
                //echo '<br/>'.htmlspecialchars($tmp);
                $proxys[] = $tmp;
            }
        }

        return $proxys;
    }

    public function xroxyProxys(Request $request)
    {
        $url = 'http://www.xroxy.com/proxylist.php?port=&type=&ssl=&country=IT&latency=&reliability=#table';
        try {
            $res = $this->client->request('GET', $url, $this->client_options);
            $body = $res->getBody();
        } catch (GuzzleException $e) {
            dd($e);
        }
        //echo((string)$body);
        $crawler = new Crawler((string) $body);
        $filter = '.row0';
        $catsHTML = $crawler
                    ->filter($filter)
                    ->each(function (Crawler $node) {
                        //return $node->html();
                        //return $this->getNodeListData($node);//$node->html();
                        return $node->filter('td a')->each(function (Crawler $node1) {
                            return $node1->html();
                        });
                    });
        $filter = '.row1';
        $catsHTML1 = $crawler
                    ->filter($filter)
                    ->each(function (Crawler $node) {
                        //return $node->html();
                        //return $this->getNodeListData($node);//$node->html();
                        return $node->filter('td a')->each(function (Crawler $node1) {
                            return $node1->html();
                        });
                    });

        unset($crawler);
        $catsHTML = \array_merge($catsHTML, $catsHTML1);
        $proxys = [];
        foreach ($catsHTML as $row) {
            $proxy = \trim(\strip_tags($row[1])).':'.\trim(\strip_tags($row[2]));
            //echo '<br/>'.htmlspecialchars($proxy);
            $proxys[] = $proxy;
        }

        return $proxys;
    }

    public function firstProxyWorking(Request $request)
    {
        \ini_set('max_execution_time', 300);
        $proxys = $this->xroxyProxys($request);
        $url = 'http://www.foodfriendfinder.com/ip.php';
        $res = $this->client->request('GET', $url, $this->client_options);
        $body_without_proxy = $res->getBody();
        \shuffle($proxys);
        $i = 0;
        foreach ($proxys as $proxy) {
            $this->client_options['proxy'] = $proxy;
            try {
                $res = $this->client->request('GET', $url, $this->client_options);
                $body = $res->getBody();
            } catch (GuzzleException $e) {
                /* -- for debugging
                echo '<br/> NOT WORKING '.$proxy;
                $req = $e->getRequest();
                $resp =$e->getResponse();
                echo '<pre>'; print_r($req); echo '</pre>';
                echo '<pre>'; print_r($resp); echo '</pre>';
                */
                $body = '';
            }
            if (\mb_strlen($body) > 2 && $body != $body_without_proxy) {
                //echo '<pre>'.$body.'</pre><pre>'.$body_without_proxy.'</pre>'; //for debugging
                return $proxy;
            }
        }

        return 'no proxy working';
    }

    public function proxyrss(Request $request)
    {
        //$url = 'http://news.google.com/news?ned=us&topic=h&output=rss'; ! interessante per import
        $url = 'http://www.xroxy.com/proxyrss.xml';
        $feed = new \SimplePie();
        $feed->set_cache_location(public_path('tmp'));
        $feed->set_feed_url($url);
        $feed->init();
        foreach ($feed->get_items(0, 10) as $key => $item) {
            echo '<hr/>';
            echo '<br/> title :'.$item->get_title();
            echo '<br/> link :'.$item->get_link();
            echo '<br/> date :'.$item->get_date();

            echo '<br/> <pre> :'.$item->get_content().'</pre>';
            //dd($item);
        }
    }
}
