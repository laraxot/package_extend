<?php
namespace XRA\Extend\Library;

use XRA\Extend\Services\ArrayService;

class Array_op /* extends baseController */
{
    public static function array_tree($array, $padre = 0)
    {
        //echo "<h4>padre [".$padre."]</h4>";
        if (!isset($array[$padre])) {
            return null;
        }
        $tmp = $array[$padre];
        $ris = [];
        if (\is_array($tmp)) {
            foreach ($tmp as $x => $row) {
                //echo "[".$x."]";
                $row['sub'] = self::array_tree($array, $x);
                $ris[$x] = $row;
                //print_r($row);
            }
        } else {
            //echo "[".$padre."] non ha figli";
        }

        return $ris;
    }

    public static function array2xml($array)
    {
        return self::ia2xml($array);
    }

    public static function ia2xml($array)
    {
        $xml = '';
        \reset($array);
        foreach ($array as $key => $value) {
            $key1 = $key;
            if (\is_numeric($key1)) {
                $key1 = 'row';
            }
            if (\is_array($value) || \is_object($value)) {
                $xml .= "<$key1>".self::ia2xml($value)."</$key1>";
            } else {
                if (\htmlspecialchars($value) == $value) {
                    $xml .= "<$key1>".$value."</$key1>".\chr(13);
                } else {
                    $xml .= "<$key1><![CDATA[".$value."]]></$key1>".\chr(13);
                }
            }
        }

        return $xml.'';
    }

    public static function arrayKeys($params)
    {
        \extract($params);
        if (\is_bool($array)) {
            ddd($array);
        }
        \reset($array);
        $ris = [];
        //foreach($array as $k => $v) {
        foreach ($array as $k => $v) {
            if (\is_object($v)) {
                $skey = $v->$key;
            } else {
                if (!isset($v[$key])) {
                    echo '<pre>';
                    \print_r($array);
                    echo '</pre>';
                    echo '<pre>';
                    \print_r($v);
                    echo '</pre>';
                    ddd('qui');
                }
                $skey = $v[$key];
                //ddd($v);
            }
            $ris[$skey] = $v;
        }

        return $ris;
    }

    // Recursive Function

    public function remove_empty($array)
    {
        return \array_filter($array, '_remove_empty_internal');
    }

    // convert an assoc array to a string of element attributes,
    // with each attribute value HTML-escaped

    public function _remove_empty_internal($value)
    {
        return !empty($value) || 0 === $value;
    }

    // convert an assoc array to a query string, with each attribute
    // value URL-encoded. The result is NOT html-escaped.

    public function toJsonAjax($params)
    {
        //utf8_decode(urldecode($str));

        $this->registry->template->__set('hideControlPanel', true);
        \header('Content-Type: application/json; charset=utf-8');
        //header('Content-Type: application/json; charset=utf-8');
        //$params['data']=array_map('utf8_encode',$params['data']);
        $params['data'] = self:: utf8_converter($params['data']);
        $json = \json_encode($params['data']);
        if (false === $json) {
            // Avoid echo of empty string (which is invalid JSON), and
            // JSONify the error message instead:
            $json = \json_encode(['jsonError', \json_last_error_msg()]);
            if (false === $json) {
                // This should not happen, but we go all the way now:
                $json = '{"jsonError": "unknown"}';
            }
            // Set HTTP response status code to: 500 - Internal Server Error
            \http_response_code(500);
        }
        echo $json;
    }

    //--------------------------------------------------------------------------------------

    public function utf8_converter($array)
    {
        \array_walk_recursive($array, function (&$item, $key) {
            if (!\mb_detect_encoding($item, 'utf-8', true)) {
                $item = \utf8_encode($item);
            }
        });

        return $array;
    }

    //---------------------------------------------------------------------------------------------

    public function array_to_attrs($attrs)
    {
        $temp = [];
        foreach ($attrs as $name => $value) {
            $temp[] = $name.'="'.\htmlspecialchars($value).'"';
        }

        return \implode(' ', $temp);
    }

    //---------------------------------------------------

    public function array_to_query($attrs = [])
    {
        if (\is_array($attrs) && \count($attrs)) {
            $temp = [];
            foreach ($attrs as $name => $value) {
                $temp[] = \rawurlencode($name).'='.\rawurlencode($value);
            }

            return \implode('&', $temp);
        } else {
            return '';
        }
    }

    public function arrayToXml($params)
    {
        $rootElement = null;
        $xml = null;
        $rowElement = 'item';
        \extract($params);
        $_xml = $xml;
        if (null === $_xml) {
            $_xml = new SimpleXMLElement(null !== $rootElement ? $rootElement : '<root/>');
        }

        foreach ($array as $k => $v) {
            if (\is_array($v) || \is_object($v)) { //nested array
                //arrayToXml($v, $k, $_xml->addChild('item'.$k));
                $params['array'] = $v;
                $params['rootElement'] = $k;
                $params['xml'] = $_xml->addChild($rowElement);
                self::arrayToXml($params);
            } else {
                $_xml->addChild($k, $v);
            }
        }
        $my_xml = $_xml->asXML();

        //require_once 'XML/Beautifier.php';
        //$fmt = new XML_Beautifier();
        //echo '<pre>'.htmlspecialchars($my_xml); echo '</pre>';
        //$my_xml = $fmt->formatString($my_xml);

        return $my_xml;
    }

    public function array_action($params)
    {
        $ris = [];
        \extract($params);
        \reset($array);
        foreach ($array as $k => $v) {
            if ('slugify' == $action) {
                //ddd('a');
                //ddd('qui');
                $ris[$k] = STRING_OP::slugify($v);
            } else {
                //$action=eval($action);
                $ris[$k] = $action($v);
            }
        }

        return $ris;
    }

    public function array_unsetEmpty($arr)
    {
        $ris = [];
        \reset($arr);
        foreach ($arr as $k => $v) {
            if (null == $v || '' == $v) {
            } else {
                $ris[$k] = $v;
            }
        }

        return $ris;
    }

    public function biarray_getkeys($params)
    {
        \extract($params);
        $ris = [];
        if (!\is_array($data)) {
            return false;
        }
        \reset($data);
        foreach ($data as $k => $v) {
            if (\is_object($v)) {
                $v = \get_object_vars($v);
            }
            \reset($key);
            foreach ($key as $kk => $vk) {
                if (!isset($ris[$vk])) {
                    $ris[$vk] = [];
                }
                if (isset($v[$vk])) {
                    if (\is_array($v[$vk])) {
                        $ris[$vk] = \array_merge($ris[$vk], $v[$vk]);
                    } else {
                        $ris[$vk][] = $v[$vk];
                    }
                }
            }

            //$ris=array_merge($ris,$v);
        }

        return $ris;
    }

    public function array_find($params)
    {
        \extract($params);
        $ris = [];
        $nwhere = \count($whereAdd);
        if (!\is_array($data)) {
            return false;
        }
        \reset($data);
        foreach ($data as $k => $v) {
            $v1 = $v;
            if (\is_object($v1)) {
                $v1 = \get_object_vars($v1);
            }
            \reset($whereAdd);
            $f = 0;
            foreach ($whereAdd as $kw => $vw) {
                if (isset($v1[$kw]) && $v1[$kw] == $vw) {
                    ++$f;
                }
            }
            if ($f == $nwhere) {
                $ris[] = $v;
            }
        }

        return $ris;
    }

    public static function toCSV($params)
    {
        global $registry;
        $caption = '';
        $delimiter = ';';
        \extract($params);
        if (0 == \count($data)) {
            return null;
        }
        //ob_start();
        //$download_dir = $registry->conf->settings['root']['conf']['xot']['download'];
        $download_dir = 'c:\download';
        $datepub = \date('Ymd');
        $backupdir = $download_dir.'/csv/';
        $backupdir = \str_replace('\\', '/', $backupdir);
        $backupdir = \str_replace('//', '/', $backupdir);
        $filename = $backupdir.$filename.'-'.$datepub.'.csv';

        $df = \fopen($filename, 'w');
        \reset($data);
        //fputcsv($df, array_keys());
        foreach ($data as $row) {
            if (\is_object($row)) {
                $row = \get_object_vars($row);
            }
            \fputcsv($df, $row, $delimiter);
        }
        \fclose($df);
        //return ob_get_clean();
        FILE_OP::recursive_mkdir(\dirname($filename));
        if (\file_exists($filename)) {
            $caption .= '<br/>N Righe = <b>'.\count($data).'</b>';
            FILE_OP::showDownloadLink($filename, $caption);
            echo '<br style="clear:both"/>';
        } else {
            echo '<br>File NON Creato '.$filename.'] '; //< br > [" . $cmd . "] < br > [" . $retval . "]";
        }
    }

    /**
     * Recursively implodes an array with optional key inclusion.
     *
     * Example of $include_keys output: key, value, key, value, key, value
     *
     * @param array  $array        multi-dimensional array to recursively implode
     * @param string $glue         value that glues elements together
     * @param bool   $include_keys include keys before their values
     * @param bool   $trim_all     trim ALL whitespace from string
     *
     * @return string imploded array
     */
    public function recursive_implode(array $array, $glue = ',', $include_keys = false, $trim_all = true)
    {
        $glued_string = '';
        // Recursively iterates array and adds key/value to glued string
        \array_walk_recursive($array, function ($value, $key) use ($glue, $include_keys, &$glued_string) {
            $include_keys and $glued_string .= $key.$glue;
            $glued_string .= $value.$glue;
        });
        // Removes last $glue from string
        \mb_strlen($glue) > 0 and $glued_string = \mb_substr($glued_string, 0, -\mb_strlen($glue));
        // Trim ALL whitespace
        $trim_all and $glued_string = \preg_replace("/(\s)/ixsm", '', $glued_string);

        return (string) $glued_string;
    }

    //-----------------------------------------------------
    public function r_implode($glue, $data)
    {
        //self::print_x($data);
        $ris = [];
        \reset($data);
        foreach ($data as $k => $v) {
            if (\is_array($v) || \is_object($v)) {
                $ris[] = self::r_implode(\chr(13).$glue, $v);
            } else {
                $ris[] = $v;
            }
        }

        return \implode($glue, $ris);
    }

    //--------------------------------------------------

    public function array_product_float($array, $float)
    {
        \reset($array);
        $ris = [];
        foreach ($array as $k => $v) {
            $ris[$k] = $v * $float;
        }

        return $ris;
    }

    public function array_parentsons($params)
    {
        if (!isset($params['keys']) && isset($params['key'])) {
            $params['keys'] = $params['key'];
        }
        \extract($params);
        if (self::isAssoc($keys)) {
            $keys_parent = \array_keys($keys);
            $keys_son = \array_values($keys);
        } else {
            $keys_parent = $keys;
            $keys_son = $keys;
        }
        $ris = [];
        $son = self::array_raggruppa(['data' => $son, 'key_array' => $keys_son]);
        foreach ($parent as $k => $v) {
            if (\is_array($v)) {
                $v = self::toObject($v);
            }
            $ris[$k] = $v;
            $skey = self::getSKey(['data' => $v, 'keys' => $keys_parent]);
            if (isset($son[$skey])) {
                $ris[$k]->$sonlabel = $son[$skey];
            } else {
                $ris[$k]->$sonlabel = null;
            }
        }

        return $ris;
    }

    //---------------//--------------------------///-----------------------

    public function isAssoc($array)
    {
        return $array !== \array_values($array);
    }

    //-----------------------------------------------------------------

    public static function array_raggruppa($params)
    {
        if (!isset($params['key_array']) && isset($params['key'])) {
            $params['key_array'] = $params['key'];
        }
        \extract($params);

        if (!\is_array($data)) {
            return [];
        }
        \reset($data);
        $ris = [];
        //foreach($data as $k => $v) {
        foreach ($data as $k => $v) {
            if (!\is_array($key_array)) {
                ddd($key_array);
            }
            \reset($key_array);
            $skey = [];
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

    public function getSKey($params)
    {
        \extract($params);
        if (\is_array($data)) {
            $data = self::toObject($data);
        }
        $skey = [];
        \reset($keys);
        foreach ($keys as $k => $v) {
            if (!isset($data->$v)) {
                self::print_x($v);
                self::print_x($data);
                ddd('qui');
            }
            $skey[] = $data->$v;
        }
        $skey = \implode('-', $skey);

        return $skey;
    }

    public static function print_x($arr)
    {
        $copy_dir = \str_replace(\DIRECTORY_SEPARATOR.'framework'.\DIRECTORY_SEPARATOR.'mvc'.\DIRECTORY_SEPARATOR.'includes', '', __DIR__);

        $deb = \debug_backtrace();
        $line = $deb[0]['line'];
        $file = $deb[0]['file'];
        echo '<br style="clear:both;"/>';
        echo '<pre style="text-align:left;">FILE : ['.FILE_OP::pathCopy2http(['copypath' => $file]).']'.\chr(13).'LINE : ['.$line.']'.\chr(13).'';
        echo 'COUNT : '.\count($arr).' '.\chr(13);
        \print_r($arr);
        echo ']</pre>';

        echo '<table border="1">';
        $fieldz = ['file', 'line', 'class', 'function '];
        echo '<tr>';
        \reset($fieldz);
        foreach ($fieldz as $kf => $vf) {
            echo '<th>'.$vf.'</th>';
        }
        echo '</tr>';
        \reset($deb);
        foreach ($deb as $k => $v) {
            echo '<tr>';
            \reset($fieldz);
            foreach ($fieldz as $kf => $vf) {
                //echo $vf;
                if ('file' == $vf && isset($v[$vf])) {
                    //print_r($_SERVER);
                    $v[$vf] = FILE_OP::pathCopy2http(['copypath' => $v[$vf]]);
                    echo '<td align="left">';
                } else {
                    echo '<td>';
                }

                if (isset($v[$vf])) {
                    echo $v[$vf];
                }
                echo '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';

        //$back=debug_backtrace();
        //echo '<pre>';print_r($back);echo '</pre>';
    }

    //---------------------------------------------

    public function array_union($params)
    {
        \extract($params);
        //---------------
        foreach ($parent as $k => $v) {
            $skey = self::getSKey(['data' => $v, 'keys' => $keys]);
            echo '<br/>'.$skey;
        }
    }

    //---------------------------------------------

    public function array_cerca($params)
    {
        //echo '<pre>';print_r($params); echo '</pre>';
        \extract($params);
        \reset($data);
        foreach ($data as $k => $v) {
            \reset($cosa);
            $trovato = 1;
            foreach ($cosa as $k1 => $v1) {
                if ($v->$k1 != $v1) {
                    $trovato = 0;
                }
            }
            if (1 == $trovato) {
                return $v;
            }
        }
    }

    //------------------------------------------

    public function sortArrayByNome($a, $b)
    {
        if ($a->nome == $b->nome) {
            return 0;
        }

        return ($a->nome < $b->nome) ? -1 : 1;
    }

    /////////////////////

    public function array_sort($array, $on, $order = SORT_ASC)
    {
        $new_array = [];
        $sortable_array = [];

        if (\count($array) > 0) {
            foreach ($array as $k => $v) {
                if (\is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    \asort($sortable_array);
                    break;
                case SORT_DESC:
                    \arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }

    //----------------------------------------------------------------

    public function array_searchX($params)
    {
        \extract($params);
        \reset($data);
        foreach ($data as $k => $v) {
            \reset($whereAdd);
            $find = 0;
            foreach ($whereAdd as $k1 => $v1) {
                if (isset($v->$k1) && $v->$k1 == $v1) {
                    ++$find;
                }
            }
            if ($find == \count($whereAdd)) {
                return $v;
            }
        }

        return false;
    }

    //----------------------------------------------------------------

    public function array2table($data)
    {
        $html = '';
        \reset($data);
        $i = 0;
        $head = '';
        $heads = [];
        foreach ($data as $k => $v) {
            $html .= '<tr>';
            foreach ($v as $k1 => $v1) {
                if (0 == $i) {
                    $head .= '<td>'.$k1.'</td>';
                    $heads[] = $k1;
                }
                //$html.='<td>'.$v1.'</td>';
                if (\is_array($v1)) {
                    $html .= '<td>-A-</td>';
                }
                if (\is_object($v1)) {
                    $html .= '<td>-O-</td>';
                } else {
                    $html .= '<td>'.$v1.'</td>';
                }
            }
            $html .= '</tr>';
            ++$i;
        }

        $html .= '</table>';

        $html = '<table border="1" class="array2table">'.$head.$html;

        return $html;
    }

    //----------------------------------------------------------------------

    public function createTableFromArray($params)
    {
        global $registry;

        \extract($params);
        if (!isset($mdb2)) {
            $mdb2 = $registry->conf->getMDB2();
        }

        //echo '<h3>DATA</h3><pre>';print_r($data);echo '</pre>';
        $tmp = \array_values($data);
        if (0 == \count($tmp)) {
            echo '<h4>Array vuoto</h4>';

            return;
        }
        //echo '<h3>TMP</h3><pre>';print_r($tmp);echo '</pre>';
        if (\is_object($tmp[0])) {
            $head = \array_keys(\get_object_vars($tmp[0]));
        } else {
            $head = \array_keys($tmp[0]);
        }
        if (isset($drop) && 1 == $drop) {
            $sql = 'drop table if exists '.$tablename;
            $res = $mdb2->query($sql);
        }

        //echo '<h3>HEAD</h3><pre>';print_r($head);'</pre>';
        $sql = 'CREATE TABLE IF NOT EXISTS '.$tablename.' ('.\implode(' VARCHAR (250),', $head).' VARCHAR (250));';
        //echo '<hr/><pre>['.$sql.']</pre><hr/>';
        $res = $mdb2->query($sql);
        if (isset($truncate) && 1 == $truncate) {
            $sql = 'Truncate '.$tablename;
            $res = $mdb2->query($sql);
        }

        \reset($data);
        foreach ($data as $k => $v) {
            $fields = [];
            //echo '<hr/><pre>'.print_r($v).'</pre>';
            \reset($v);
            foreach ($v as $k1 => $v1) {
                $fields[] = $mdb2->quote($v1, 'text');
                //echo '<br/>['.$v1.']';
            }
            $sql = 'INSERT INTO '.$tablename.' ('.\implode(',', $head).')
				VALUES ('.\implode(',', $fields).'); ';
            //echo '<hr/><pre>'.$sql.'</pre>';
            $res = $mdb2->query($sql);
        }

        echo '<h3>+Done</h3>';
    }

    //---------------------------------------------------------------------

    public function array_find_index($array, $index)
    {
        $pos = \mb_strpos($index, '[');
        if ($pos) {
            $subkey = \mb_substr($index, 0, $pos);
            $subkey = \str_replace(']', '', $subkey);
            if (isset($array[$subkey])) {
                return self::array_find_index($array[$subkey], \mb_substr($index, $pos + 1));
            } else {
                return false;
            }
        } else {
            $index = \str_replace(']', '', $index);
            if (isset($array[$index])) {
                return $array[$index];
            } else {
                return false;
            }
        }
    }

    //------------//------------//------------//------------//------------//------------

    public function array_media($params)
    {
        //self::print_x($params);
        $fields = [];
        \extract($params);
        $fields = \array_merge($fields, $key);
        $fields = \array_merge($fields, $add);
        $fields = \array_unique($fields);
        $min_arr = self::array_min($params);
        $max_arr = self::array_max($params);

        $subtotale = self::array_subtotale($params);
        $conta = self::array_conta($params);
        $media = [];
        \reset($subtotale);
        foreach ($subtotale as $k => $v) {
            //self::print_x($v);ddd('qui');
            $c = $conta[$k];
            $tmp = new stdclass();
            \reset($fields);
            foreach ($fields as $kf => $vf) {
                $tmp->$vf = $v->$vf;
            }
            \reset($add);
            foreach ($add as $ka => $va) {
                $tmp->$va = $v->$va / $c->q;
                $sk = '_min_'.$va;
                $tmp->$sk = $min_arr[$k]->$va;
                //$tmp->q_min=$min_arr[$k]->q;
                $sk = '_max_'.$va;
                $tmp->$sk = $max_arr[$k]->$va;
                //$tmp->q_max=$max_arr[$k]->q;
            }
            $tmp->q = $c->q;
            //self::print_x($params['key']);ddd('qui');
            $media[$k] = $tmp;
        }

        return $media;
    }

    //------------//------------//------------//------------//------------//------------

    public function array_min($params)
    {
        \extract($params);
        $data1 = self::array_raggruppa($params);
        $ris = [];
        foreach ($data1 as $k => $v) {
            $tmp = new stdClass();
            \reset($add);
            foreach ($add as $ka => $va) {
                $t = self::arrayKeysX($v, $va);
                $tmp->$va = \min($t);
            }
            $tmp->q = \count($v);
            $ris[$k] = $tmp;
        }

        return $ris;
    }

    //------------//------------//------------//------------//------------//------------

    //media gia' fatta per ridurre va fatto dopo self::array_media

    public static function arrayKeysX($array, $key_field, $value_field = null)
    {
        $ris = [];
        if (\is_array($array)) {
            foreach ($array as $k => $v) {
                if (\is_object($v)) {
                    $v = \get_object_vars($v);
                }
                if (null != $value_field) {
                    if ('*' == $value_field) {
                        $ris[$v[$key_field]] = $v;
                    } else {
                        if (!\is_array($value_field)) {
                            $ris[$v[$key_field]] = $v[$value_field];
                        } else {
                            $arr_tmp = [];
                            \reset($value_field);
                            foreach ($value_field as $kv => $vv) {
                                $arr_tmp[] = $v[$vv];
                            }
                            $ris[$v[$key_field]] = \implode(' ', $arr_tmp);
                        }
                    }
                } else {
                    $ris[] = $v[$key_field];
                }
            }
        } else {
            echo '<br>['.$array."]  non e' un array ";
        }
        //self::print_x($ris);
        if (!\is_array($ris)) {
            self::print_x($ris);
            ddd('qui');
        }

        return \array_unique($ris);
    }

    //------------//------------//------------//------------//------------//------------

    public function array_max($params)
    {
        \extract($params);
        $data1 = self::array_raggruppa($params);
        $ris = [];
        foreach ($data1 as $k => $v) {
            $tmp = new stdClass();
            \reset($add);
            foreach ($add as $ka => $va) {
                $t = self::arrayKeysX($v, $va);
                $tmp->$va = \max($t);
            }
            $tmp->q = \count($v);
            $ris[$k] = $tmp;
        }

        return $ris;
    }

    //------------//------------//------------//------------//------------//------------

    public static function array_subtotale($params)
    {
        $fields = [];
        \extract($params);
        $ris = [];
        $fields = \array_merge($fields, $key);
        $fields = \array_merge($fields, $add);
        $fields = \array_unique($fields);
        if (!\is_array($data)) {
            return $ris;
        }
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

    public function array_conta($params)
    {
        if (!isset($params['key_array']) && isset($params['key'])) {
            $params['key_array'] = $params['key'];
        }
        $fields = [];
        \extract($params);
        $ris = [];
        $fields = \array_merge($fields, $key);
        //$fields=array_merge($fields,$add);
        $fields = \array_unique($fields);
        \reset($data);
        foreach ($data as $k => $v) {
            \reset($key_array);
            $skey = [];
            foreach ($key_array as $k1 => $v1) {
                $skey[] = $v->$v1;
            }
            $skey = \implode('-', $skey);
            if (!isset($ris[$skey])) {
                $tmp = new stdclass();
                /*
                reset($key_array);
                foreach($key_array as $k1 => $v1){
                    $tmp->$v1=$v->$v1;
                }
                */
                \reset($fields);
                foreach ($fields as $kf => $vf) {
                    if (!isset($v->$vf)) {
                        $v->$vf = 0;
                    }
                    $tmp->$vf = $v->$vf;
                }

                $tmp->q = 0;
                $ris[$skey] = $tmp;
            }
            ++$ris[$skey]->q;
        }

        return $ris;
    }

    public function array_mediaPesata($params)
    {
        //$parz1=array('data'=>$data3,'key'=>array('categoria_eco'));
        $fields = [];
        \extract($params);
        $fields = \array_merge($fields, $key);
        $fields = \array_merge($fields, $add);
        $fields = \array_unique($fields);
        $min_arr = self::array_min($params);
        $max_arr = self::array_min($params);
        //self::print_x($data);
        //-----------------------------------------------
        $data4 = self::array_raggruppa($params);
        //self::print_x($data4);
        $data5 = [];
        \reset($data4);
        foreach ($data4 as $k => $v) {
            $tmp = new stdClass();
            $q = 0;
            $s = [];
            $i = 0;
            \reset($v);
            foreach ($v as $k1 => $v1) {
                if (0 == $i) {
                    \reset($fields);
                    foreach ($fields as $kf => $vf) {
                        $tmp->$vf = $v1->$vf;
                    }
                    \reset($add);
                    foreach ($add as $ka => $va) {
                        $s[$va] = 0;
                        $sk = '_min_'.$va;
                        $tmp->$sk = $v1->$sk;
                        $sk = '_max_'.$va;
                        $tmp->$sk = $v1->$sk;
                    }
                }
                ++$i;
                \reset($add);
                foreach ($add as $ka => $va) {
                    //$s+=$v1->$f*$v1->q;
                    $s[$va] += ($v1->$va * $v1->q);
                    //echo '<br>'.$k.'   '.$va.'  '.$v1->$va.'  '.$v1->q.'   '.$s[$va];
                    $sk = '_min_'.$va;
                    $tmp->$sk = \min($tmp->$sk, $v1->$sk);
                    $sk = '_max_'.$va;
                    $tmp->$sk = \max($tmp->$sk, $v1->$sk);
                }
                $q += $v1->q;
                //$sk='_min_'.$f;
                //$min=min($min,$v1->$sk);
                //$sk='_max_'.$f;
                //$max=max($max,$v1->$sk);
            }
            //$tmp->categoria_eco=$k;
            \reset($add);
            foreach ($add as $ka => $va) {
                //$tmp->$f=round($s/$q,2);
                $tmp->$va = $s[$va] / $q;
                //echo '<br/> Tot '.$s[$va].'  '.$q;
            }

            $tmp->q = $q;
            //$tmp->min=round($min,2);
            //$tmp->max=round($max,2);
            $data5[$k] = $tmp;
        }

        return $data5;
    }

    //----------------------------------------------------

    public function array_render($array, $fielstorender)
    {
        $ris = [];
        \reset($array);
        foreach ($array as $k => $v) {
            $ris[$k] = self::array_render_row($v, $fielstorender);
            /*
            reset($fielstorender);
            foreach ($fielstorender as $k1 => $v1){
                if (is_object($v)) $v = get_object_vars($v);
                if(isset($v[$v1])){
                    $ris[$k][$v1] = $v[$v1];
                }else{
                    $ris[$k][$v1] = '';
                }
            }
            */
        }

        return $ris;
    }

    //------------------------------------------------

    public function array_render_row($row, $fielstorender)
    {
        \reset($fielstorender);
        $v = $row;
        $ris = [];
        foreach ($fielstorender as $k1 => $v1) {
            if (\is_object($v)) {
                $v = \get_object_vars($v);
            }
            if (isset($v[$v1])) {
                $ris[$v1] = $v[$v1];
            } else {
                $ris[$v1] = '';
            }
            /*
            if(isset($v['_'.$v1])){
                $tmp2=$v['_'.$v1];
                //echo '<pre>';print_r($tmp2);echo '</pre>';
                reset($tmp2);
                foreach($tmp2 as $k2 => $v2){
                    $ris['_'.$v1.'_'.$k2] = $v['_'.$v1]->$k2;
                }
            }
            */
        }

        return $ris;
    }

    //---------------------------------------------------------------------------

    public function filterarray($params)
    {
        //echo '<pre>'; print_r($params); echo '</pre>';
        \extract($params);
        $myArrayUtil = new ArrayUtil();
        $myArrayUtil->set($params);
        $myArrayUtil->filter();

        return $myArrayUtil->_return;
    }

    //*/
    public static function toXLS($params){
        return ArrayService::toXLS($params);
    }

    public static function toXLS_phpexcel($params)
    {
        \Debugbar::disable();
        $objPHPExcel = new \PHPExcel();
        \extract($params);
        $data = \array_values($data);
        if (0 == \count($data)) {
            echo '<h3>nulla dentro DATA</h3>';

            return 'nulla dentro DATA';
        }
        \reset($data);
        $ews = $objPHPExcel->getSheet(0);
        $ews->setTitle('Data');
        $collection = collect($data);
        $firstrow = $collection->first();

        $ews->fromArray(\array_keys($firstrow), ' ', 'A1');
        $ews->fromArray($data, ' ', 'A2');

        $writer = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $writer->setIncludeCharts(true);
        //$writer->save('php://output');
        $pathToFile = 'c:\\download\\xls\\'.$filename.'.xlsx';
        $writer->save($pathToFile);
        //return 'aaaa';
        $caption = '';
        $caption .= '<br/>N Righe = <b>'.\count($data).'</b>';
        FILE_OP::showDownloadLink($pathToFile, $caption);

        return response()->download($pathToFile);
    }

    // From array to object.

    public static function toXLS_pear($params)
    {
        //echo '<pre>';print_r($params); echo '</pre>';

        $caption = '';
        \extract($params);
        if (!isset($nomefile) && isset($filename)) {
            $nomefile = $filename;
        }
        \ini_set('max_execution_time', 3600);
        require_once 'Spreadsheet/Excel/Writer.php';
        //global $registry;
        //$download_dir = $registry->conf->settings['root']['conf']['xot']['download'];
        $download_dir = 'c:\download';
        $datepub = \date('Ymd');
        $backupdir = $download_dir.'/excel/';
        $backupdir = \str_replace('\\', '/', $backupdir);
        $backupdir = \str_replace('//', '/', $backupdir);
        $filename = $backupdir.$nomefile.'-'.$datepub.'.xls';
        $workbook = new \Spreadsheet_Excel_Writer($filename);

        $worksheet = $workbook->addWorksheet('My first worksheet');
        $format_bold = $workbook->addFormat();
        $format_bold->setBold();
        $format_title = $workbook->addFormat();
        $format_title->setBold();
        $format_title->setColor('yellow');
        $format_title->setPattern(1);
        $format_title->setFgColor('blue');
        // let's merge
        $format_title->setAlign('merge');

        $format_2 = $workbook->addFormat(['Size' => 11, 'Align' => 'center', 'vAlign' => 'vjustify', //'vAlign' => 'vcenter',
            'Color' => 'black', 'Bold' => 1, 'Pattern' => 1, 'FgColor' => 'orange', ]);

        $data = \array_values($data);
        \reset($data);
        // echo '<pre>[';print_r($data); echo ']</pre>';
        $i = 0;
        $head = [];
        foreach ($data as $k => $v) {
            // echo '<br/>['.$k.']<pre>';print_r($v);'</pre>';
            $my = [];
            \reset($v);
            foreach ($v as $k1 => $v1) {
                $v1 = \str_replace(',', '.', $v1);

                $my[$k1] = $v1;
            }
            if (0 == $i) {
                //$head = array_keys($my);
                if (!isset($fieldlabels)) {
                    //self::print_x($v);
                    if (\is_object($v)) {
                        $fieldlabels = \array_keys(\get_object_vars($v));
                    } else {
                        $fieldlabels = \array_keys($v);
                    }
                    //$fieldlabels=array_keys($v);
                    //ddd('qui');
                }
                $head = \array_values($fieldlabels);
                //echo '<pre>';print_r($head); echo '</pre>';
                foreach ($head as $k3 => $v3) {
                    $head[$k3] = \str_replace('_', ' ', $v3);
                }
                $worksheet->writeRow($i, 0, $head, $format_2);
                //$i++;
            }
            $worksheet->writeRow($k + 1, 0, $my);
            //$i++;
        }
        //echo '<h3>HEAD</h3><pre>';print_r($head);echo '</pre>';

        $workbook->close();
        // echo '<div style="border:1px solid blue;margin:10px;padding:10px;width:550px;">';
        //die(dirname($filename));
        // mkdir(dirname($filename), 0, true);
        FILE_OP::recursive_mkdir(\dirname($filename));
        if (\file_exists($filename)) {
            $caption .= '<br/>N Righe = <b>'.\count($data).'</b>';
            FILE_OP::showDownloadLink($filename, $caption);
            echo '<br style="clear:both"/>';
        /*
        echo '<br><a href="' . str_replace($_SERVER["DOCUMENT_ROOT"], 'http://' . $_SERVER["HTTP_HOST"], $filename) . '">' . str_replace($backupdir . "/", "", $filename) . '</a>';
        //echo '&nbsp;&nbsp;<a href="?action=cancellafile&filename=">cancella</a>'
        echo ': ' . filesize($filename) / 1024 . ' Kbytes';
        */
        } else {
            echo '<br>File NON Creato '.$filename.'] '; //< br > [" . $cmd . "] < br > [" . $retval . "]";
        }
        //  echo '</div>';
    }

    public static function exportArray2XLS($nomefile, $data, $caption = '')
    {
        \ini_set('max_execution_time', 3600);
        require_once 'Spreadsheet/Excel/Writer.php';
        global $registry;
        $download_dir = $registry->conf->settings['root']['conf']['xot']['download'];
        $datepub = \date('Ymd');
        $backupdir = $download_dir.'/excel/';
        $backupdir = \str_replace('\\', '/', $backupdir);
        $backupdir = \str_replace('//', '/', $backupdir);
        $nomefile = STRING_OP::slugify($nomefile);
        $nomefile = \str_replace('-', '_', $nomefile);
        $nomefile = \str_replace(',', '_', $nomefile);

        $filename = $backupdir.$nomefile.'-'.$datepub.'.xls';
        $workbook = new Spreadsheet_Excel_Writer($filename);
        //$workbook = new Spreadsheet_Excel_Writer();
        //$registry->template-> hideControlPanel = true;
        // Send HTTP headers to tell the browser what's coming
        //$workbook->send("test.xls");

        $worksheet = &$workbook->addWorksheet('My first worksheet');
        $format_bold = &$workbook->addFormat();
        $format_bold->setBold();
        $format_title = &$workbook->addFormat();
        $format_title->setBold();
        $format_title->setColor('yellow');
        $format_title->setPattern(1);
        $format_title->setFgColor('blue');
        // let's merge
        $format_title->setAlign('merge');

        $format_2 = &$workbook->addFormat(['Size' => 11, 'Align' => 'center', 'vAlign' => 'vjustify', //'vAlign' => 'vcenter',
            'Color' => 'black', 'Bold' => 1, 'Pattern' => 1, 'FgColor' => 'orange', ]);

        $data = \array_values($data);
        \reset($data);
        // echo '<pre>[';print_r($data); echo ']</pre>';
        $i = 0;
        $head = [];
        foreach ($data as $k => $v) {
            // echo '<br/>['.$k.']<pre>';print_r($v);'</pre>';
            $my = [];
            \reset($v);
            foreach ($v as $k1 => $v1) {
                if (\is_object($v1)) {
                    $v1 = \get_object_vars($v1);
                    if (self::isAssoc($v1)) {
                        ddd('qui');
                    } else {
                        $v1 = \implode(',', $v1);
                    }
                }
                $v1 = \str_replace(',', '.', $v1);
                $my[$k1] = $v1;
            }
            if (0 == $i) {
                $head = \array_keys($my);
                foreach ($head as $k3 => $v3) {
                    $head[$k3] = \str_replace('_', ' ', $v3);
                }
                $worksheet->writeRow($i, 0, $head, $format_2);
                //$i++;
            }
            $worksheet->writeRow($k + 1, 0, $my);
            //$i++;
        }
        //echo '<h3>HEAD</h3><pre>';print_r($head);echo '</pre>';

        $workbook->close();
        // echo '<div style="border:1px solid blue;margin:10px;padding:10px;width:550px;">';
        //die(dirname($filename));
        // mkdir(dirname($filename), 0, true);
        FILE_OP::recursive_mkdir(\dirname($filename));
        if (\file_exists($filename)) {
            $caption .= '<br/>N Righe = <b>'.\count($data).'</b>';
            FILE_OP::showDownloadLink($filename, $caption);
            echo '<br style="clear:both"/>';
        /*
        echo '<br><a href="' . str_replace($_SERVER["DOCUMENT_ROOT"], 'http://' . $_SERVER["HTTP_HOST"], $filename) . '">' . str_replace($backupdir . "/", "", $filename) . '</a>';
        //echo '&nbsp;&nbsp;<a href="?action=cancellafile&filename=">cancella</a>'
        echo ': ' . filesize($filename) / 1024 . ' Kbytes';
        */
        } else {
            echo '<br>File NON Creato '.$filename.'] '; //< br > [" . $cmd . "] < br > [" . $retval . "]";
        }
        //  echo '</div>';
    }

    public function array_to_object($array = [])
    {
        if (!empty($array)) {
            $data = false;
            foreach ($array as $akey => $aval) {
                $data->{$akey} = $aval;
            }

            return $data;
        }

        return false;
    }

    public function showArray($myarr)
    {
        echo '<table border="1">';
        \reset($myarr);
        echo '<tr>';
        foreach ($myarr as $k => $v) {
            echo '<th><b>'.$k.'</b></th>';
        }
        echo '</tr>';
        if (\is_array($myarr) || \is_object($myarr)) {
            \reset($myarr);
            foreach ($myarr as $k => $v) {
                echo '<tr>';
                if (\is_array($v) || \is_object($v)) {
                    foreach ($v as $k0 => $v0) {
                        echo '<td>';
                        if (\is_array($v0) || \is_object($v0)) {
                            echo '<td>Array/OBJ</td>';
                        //self::showArray($v0);
                        } else {
                            echo $v0;
                        }
                        echo '</td>';
                    }
                } else {
                    echo '<td>'.$v.'</td>';
                }
                echo '</tr>';
            }
        } else {
        }
        echo '</table>';
    }

    public function exportArray3D2XLS($nomefile, $data3d)
    {
        require_once 'Spreadsheet/Excel/Writer.php';
        $ii = 0;
        $datepub = \date('Ymd');
        $caption = '';
        $totrighe = 0;
        global $registry;
        $download_dir = $registry->conf->settings['root']['conf']['xot']['download'];
        $backupdir = 'excel/';
        $backupdir = $download_dir.'/excel/';
        $backupdir = \str_replace('\\', '/', $backupdir);
        $backupdir = \str_replace('//', '/', $backupdir);

        $filename = $backupdir.$nomefile.'-'.$datepub.'.xls';
        $workbook = new Spreadsheet_Excel_Writer($filename);

        $format_bold = &$workbook->addFormat();
        $format_bold->setBold();
        $format_title = &$workbook->addFormat();
        $format_title->setBold();
        $format_title->setColor('yellow');
        $format_title->setPattern(1);
        $format_title->setFgColor('blue');
        // let's merge
        $format_title->setAlign('merge');

        $format_2 = &$workbook->addFormat(['Size' => 11, 'Align' => 'center', 'vAlign' => 'vjustify', //'vAlign' => 'vcenter',
            'Color' => 'black', 'Bold' => 1, 'Pattern' => 1, 'FgColor' => 'orange', ]);

        foreach ($data3d as $k => $data) {
            $ka = \trim(\mb_substr(STRING_OP::slugify($k), 0, 29)).$ii++;
            //echo '<hr> aggiungo foglio ['.$ka.']';
            $caption .= ''.$ka.' righe ='.\count($data).'<br/>';
            $totrighe += \count($data);
            $worksheet = &$workbook->addWorksheet($ka);
            if (PEAR::isError($worksheet)) {
                die($worksheet->getMessage());
            }
            $i = 0;
            $head = [];
            \reset($data);
            foreach ($data as $k => $v) {
                $my = [];
                \reset($v);
                foreach ($v as $k1 => $v1) {
                    if (0 == $i) {
                        $head[$k1] = \str_replace('_', ' ', $k1);
                    }
                    $my[$k1] = \str_replace(',', '.', $v1);
                }
                if (0 == $i) {
                    $worksheet->writeRow($i, 0, $head, $format_2);
                    ++$i;
                }
                $worksheet->writeRow($i, 0, $my);
                ++$i;
            }
        }
        $workbook->close();
        $caption .= '<br/> TOT RIGHE =<b>'.$totrighe.'</b>';
        FILE_OP::showDownloadLink($filename, $caption);
    }

    //--------------------------------------------

    public function array_inline_multi($params)
    {
        \extract($params);
        \reset($data);
        $ris = [];
        $fields = [];
        foreach ($data as $k => $v) {
            $tmp = self::array_inline($v);
            $ris[] = $tmp;
            if (\count(\array_keys($tmp)) > \count($fields)) {
                $fields = \array_unique(\array_merge(\array_keys($tmp), $fields));
            } else {
                $fields = \array_unique(\array_merge($fields, \array_keys($tmp)));
            }
        }
        $fields = \array_unique($fields);
        //echo '<pre>';print_r($fields); echo '</pre>';
        $ris1 = [];
        \reset($ris);
        foreach ($ris as $k => $v) {
            \reset($fields);
            foreach ($fields as $k1 => $v1) {
                if (!isset($v[$v1])) {
                    $v2 = '---';
                } else {
                    $v2 = $v[$v1];
                }
                $ris1[$k][$v1] = $v2;
            }
        }
        //----------------------------

        //-----------------------------
        $parz = [];
        $parz['data'] = $ris1;
        $parz['fields'] = $fields;

        return $parz;
    }

    public function array_inline($arr, $prefix = '')
    {
        $ris = [];
        \reset($arr);
        foreach ($arr as $k => $v) {
            if (\is_array($v) || \is_object($v)) {
                $ris = \array_merge($ris, self::array_inline($v, $k.'_'.$prefix));
            } else {
                $v = \str_replace(\chr(13), ',', $v);
                $v = \str_replace(\chr(10), '', $v);
                $ris[$prefix.$k] = $v;
            }
        }

        return $ris;
    }

    //---------------------

    public function implode_recorsive($delimiter, $array)
    {
        $ret = '';
        foreach ($array as $item) {
            if (\is_array($item)) {
                $ret .= multi_implode($delimiter, $array).$delimiter;
            } else {
                $ret .= $item.$delimiter;
            }
        }
        $ret = \mb_substr($ret, 0, 0 - \mb_strlen($delimiter));

        return $ret;
    }

    public function disponiFields($params)
    {
        \extract($params);
        $ris = [];
        \reset($data);
        while (list(, $tmp) = \each($data)) {
            $tmp1 = new stdclass();
            \reset($fields);
            foreach ($fields as $k => $v) {
                if (isset($tmp->$v)) {
                    $tmp1->$v = $tmp->$v;
                } else {
                    $tmp1->$v = '';
                }
            }
            $ris[] = $tmp1;
        }

        return $ris;
    }
}//-end class

class ArrayUtil
{
    //-------------------------------------
    public function set($params)
    {
        //echo '<pre>'; print_r($params); echo '</pre>';
        \reset($params);
        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
    }

    //------------------------------------
    public function filter()
    {
        $this->_return = \array_filter($this->data, [$this, 'do_filter']);
    }

    //------------------------------------
    public function do_filter($var)
    {
        //echo '<hr/><pre>'; print_r($var); echo '</pre>';
        //[field0] => id
        //[op] => =
        //[field1] => 101
        //$str = .'=='.$this->field1;
        if (\is_object($var)) {
            $var = \get_object_vars($var);
        }
        $v = $var[$this->field0];
        $this->op = \trim($this->op);
        switch ($this->op) {
            case '=':
                return $v == $this->field1;
                break;
            case '>':
                return $v > $this->field1;
                break;
            case '<':
                return $v < $this->field1;
                break;
            case '>=':
                return $v >= $this->field1;
                break;
            case '<=':
                return $v <= $this->field1;
                break;
            case 'LIKE':
                if (false === \mb_strpos(\mb_strtolower($v), \mb_strtolower($this->field1))) {
                    return 0;
                } else {
                    return 1;
                }
                break;
            default:
                echo 'operatore non riconosciuto['.$this->op.']';
                break;
        }
        //echo '<br/>'.$str;
        //return eval($str);

        return 0;
    }

    //-----------------------------------
}

//---------------------------------
