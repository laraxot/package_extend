<?php
namespace XRA\Extend\Traits;

use Carbon\Carbon;
use Zend;

use XRA\Extend\Services\ArrayService;

trait ArrayTrait
{
    public static function toXLS($params)
    {
        //return self::toXLS_Maatwebsite($params);
        return ArrayService::toXLS($params);
    }

    
    public static function toCSV($params)
    {
        //return self::toCSV_php($params);
        return ArrayService::toCSV($params);
    }

    public static function array_raggruppa($params)
    {
        if (!isset($params['key_array']) && isset($params['key'])) {
            $params['key_array'] = $params['key'];
        }
        \extract($params);
        if (!\is_array($data)) {
            return [];
        }
        //*-- made with collection
        $collection = collect($data);
        $grouped = $collection->groupBy(function ($item) use ($key_array) {
            $key_tmp = [];
            foreach ($key_array as $k) {
                $key_tmp[] = $item[$k];
            }

            return \implode('-', $key_tmp);
        });

        return $grouped->all();
        //*/

        \reset($data);
        $ris = [];
        //foreach($data as $k => $v) {
        foreach ($data as $k => $v) {
            if (!\is_array($key_array)) {
                ddd($key_array);
            }
            \reset($key_array);
            $skey = [];
            //while (list($k1, $v1) = each($key_array)) {
            foreach ($key_array as $k1 => $v1) {
                if (\is_object($v)) {
                    if (!isset($v->$v1)) {
                        //self::print_x($v1);
                        //self::print_x($v);
                        //ddd('qui');
                        $v->$v1 = '';
                    }
                    $v2 = $v->$v1;
                } else {
                    if (isset($v[$v1])) {
                        $v2 = $v[$v1];
                    } else {
                        $v2 = '';
                    }
                }
                $skey[] = $v2;
            }
            $skey = \implode('-', $skey);
            if (!isset($ris[$skey])) {
                $ris[$skey] = [];
            }
            $ris[$skey][] = $v;
        }

        return $ris;
    }

    public static function array_subtotale($params)
    {
        $fields = [];
        \extract($params);
        $ris = [];
        $fields = \array_merge($fields, $key);
        $fields = \array_merge($fields, $add); //in piu forse
        $fields = \array_unique($fields);
        if (!\is_array($data)) {
            return $ris;
        }

        //--------------
        //*
        $collection = collect($data);
        $grouped = $collection->groupBy(function ($item) use ($key,$add,$fields) {
            $key_tmp = [];
            foreach ($key as $k) {
                $key_tmp[] = $item[$k];
            }

            return \implode('-', $key_tmp);
        })->map(function ($item) use ($add,$fields) {
            foreach ($fields as $i) {
                $item->$i = $item->first()[$i];
            }
            foreach ($add as $i) {
                $item->$i = $item->sum($i);
            }

            return $item;
        });

        return $grouped->all();
        //*/
        //--------------
        \reset($data);

        foreach ($data as $k => $v) {
            if (\is_array($v)) {
                $v = self::toObject($v);
            }
            $skey = [];
            \reset($key);
            foreach ($key as $kk => $vk) {
                if (!isset($v->$vk)) {
                    echo '<pre>[v]';
                    \print_r($v);
                    echo '[/v]</pre>';
                    echo '<pre>[vk]';
                    \print_r($vk);
                    echo '[/vk]</pre>';
                    ddd('qui');
                }
                $skey[] = $v->$vk;
            }
            $skey = \implode('-', $skey);
            if (!isset($ris[$skey])) {
                $obj = new \stdclass();
                \reset($fields);
                foreach ($fields as $kf => $vf) {
                    if (!isset($v->$vf)) {
                        $v->$vf = 0;
                    }
                    $obj->$vf = $v->$vf;
                }
                $ris[$skey] = $obj;
            } else {
                \reset($add);
                foreach ($add as $ka => $va) {
                    if (!isset($v->$va)) {
                        $v->$va = 0;
                    }
                    if (!isset($ris[$skey]->$va)) {
                        $ris[$skey]->$va = 0;
                    }
                    $ris[$skey]->$va += $v->$va;
                    //echo '<pre>';print_r($ris[$skey]);echo '</pre>';
                    //echo '<pre>';print_r($v);echo '</pre>';
                    //echo '<pre>';print_r($va);echo '</pre>';
                    //ddd('qui');
                }
            }
        }

        return $ris;
    }

    public static function toObject($data)
    {
        //return is_array($data) ? (object) array_map(__function __, $data) : $data;
        return self::array2Obj($data);
    }

    public static function toArrayObject($data)
    {
        return \array_map(
            function ($v) {
                return self::toObject($v);
            },
            $data
        );
    }

    public static function array2Obj($array = [])
    {
        if (!\is_array($array)) {
            return $array;
        }
        \reset($array);
        $obj = new \stdClass();
        $i = 0;
        //foreach($array as $k => $v) {
        foreach ($array as $k => $v) {
            $obj->$k = self::array2Obj($v);
            ++$i;
        }
        if ($i > 0) {
            return $obj;
        }

        return null;
    }

    public static function save($params)
    {
        \extract($params);
        $writer = new Zend\Config\Writer\PhpArray();
        $content = $writer->toString($data);
        $content = \str_replace('\\\\', '\\', $content);
        $content = \str_replace('\\\'', '\\\\\'', $content);
        $content = \str_replace("'".storage_path(), 'storage_path()'.".'", $content);
        $content = \str_replace("\\\\'", "\\'", $content);
        \File::put($filename, $content);
    }
}
