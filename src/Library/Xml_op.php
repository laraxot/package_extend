<?php



namespace XRA\Extend\Library;

class Xml_op /*extends baseController */
{
    public static function xml2array($url)
    {
        global $registry;
        //echo $url;
        //
        //echo '<hr/>['.__FILE__.']['.__LINE__.']'.$url;
        $url = \str_replace('/', \DIRECTORY_SEPARATOR, $url);
        if (!\file_exists($url)) {
            $a = [];
            $a['root'] = [];
            $a['root']['row'] = [];

            return $a;
        }

        $xmlstr = FILE_OP::getContentFile($url);

        //$xmlstr = file_get_contents($url);

        $a = [];
        try {
            \libxml_use_internal_errors(true);
            $a['root'] = \json_decode(\json_encode((array) \simplexml_load_string($xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS)), 1);
            $err = \libxml_get_errors();
            if (\count($err) > 0) {
                /*
                if($registry->debug==1){
                  echo '<br/><br/><br/><br/><br/><br/><br/>';
                  echo '<br/> file: '.$url;
                  echo '<pre>';print_r($err);echo '</pre>';
                }
                */
                //$xmlstr=file_get_contents($url);
                $obj = self::xml_to_object($xmlstr);
                $arr = self::xmlobj2array($obj);
                //Array_op::print_x($arr);
                if (0 == \count($arr)) {
                    Array_op::print_x($arr);
                    Array_op::print_x($url);
                    \rename($url, \str_replace('.xml', '.err', $url));
                }

                return $arr;
            }
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
        /*
        if($a==array()){
          ddd('qui');
        }
        */
        return $a;
    }

    public static function xml_to_object($xml)
    {
        global $registry;
        $xml = self::cleanXMLTidy($xml);
        //echo '<pre>['.__LINE__.']'.htmlspecialchars($xml).'</pre>';
        $parser = \xml_parser_create();
        //xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
        \xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        \xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        \xml_parse_into_struct($parser, $xml, $tags);
        //Array_op::print_x($tags);ddd('qui');

        $err_code = \xml_get_error_code($parser);
        /*
        if($err_code!=0 && $registry->debug==1){
          $str='<br style="clear:both;"/>'.$err_code.']'.xml_error_string($err_code);
          //echo '<pre>[xml]'.htmlspecialchars(cleanXMLTidy($xml)).'[/xml]</pre>';
          $str.= '<br style="clear:both;"/><pre>[xml]'.chr(13).htmlspecialchars($xml).chr(13).'[/xml]</pre>';
          //echo '<pre>'.htmlspecialchars(cleanupXML($xml)).'</pre>';
          Array_op::print_x($str);

        }
        */
        \xml_parser_free($parser);

        $elements = [];  // the currently filling [child] XmlElement array
        $stack = [];
        foreach ($tags as $tag) {
            $index = \count($elements);
            if ('complete' == $tag['type'] || 'open' == $tag['type']) {
                $elements[$index] = new \stdClass();
                $elements[$index]->name = $tag['tag'];
                if (isset($tag['attributes'])) {
                    $elements[$index]->attributes = Array_op::array2Obj($tag['attributes']);
                }
                if (isset($tag['value'])) {
                    $elements[$index]->content = $tag['value'];
                }
                if ('open' == $tag['type']) {  // push
                    $elements[$index]->children = [];
                    $stack[\count($stack)] = &$elements;
                    $elements = &$elements[$index]->children;
                }
            }
            if ('close' == $tag['type']) {  // pop
                $elements = &$stack[\count($stack) - 1];
                unset($stack[\count($stack) - 1]);
            }
        }
        if (isset($elements[0])) {
            return $elements[0];
        }
        // ddd('qui');

        return;  // the single top-level element
    }

    //[C:\Users\sottanamarco\Copy\consolle_data\htdocs\framework\mvc\menu\menu_102.xml]

    public static function cleanXMLTidy($xml)
    {
        $xml = \str_replace('ITEMPROP', 'itemprop', $xml);
        $xml = \str_replace('ADDRESSREGION', 'addressRegion', $xml);
        $faicons = \preg_match_all('/<i class="fa [^>]+><\/i>/i', $xml, $matches);
        foreach ($matches[0] as $k => $v) {
            $xml = \str_replace($v, '', $xml);
        }
        $faicons = \preg_match_all('/<span class="fa [^>]+><\/span>/i', $xml, $matches);
        foreach ($matches[0] as $k => $v) {
            $xml = \str_replace($v, '', $xml);
        }

        $del = ['<i>&#x201c;</i>', '<i>&#x201d;</i>'];
        foreach ($del as $k => $v) {
            $xml = \str_replace($v, '', $xml);
        }

        $gimg = \preg_match_all('/<img[^>]+/i', $xml, $matches);
        //Array_op::print_x($matches);
        foreach ($matches[0] as $k => $v) {
            // echo '<br/>'.htmlspecialchars($v);
            $xml = \str_replace($v, $v.' />', $xml);
            $xml = \str_replace($v.' />>', $v.' />', $xml);
            $xml = \str_replace($v.' />/>', $v.' />', $xml);
        }
        if (!\class_exists('tidy')) {
            return $xml;
        }

        // Specify configuration
        $config = [//          'markup' =>'yes',
            'indent' => true, 'input-xml' => true, 'output-xml' => true, // 'show-body-only' => 'yes',
            'wrap' => 200, ];

        // Tidy
        $tidy = new \tidy();
        $tidy->parseString($xml, $config, 'utf8');
        $tidy->cleanRepair();

        return $tidy;
    }

    ///----------------//------------------------

    public static function xmlobj2array($obj)
    {
        $arr = [];
        if (!\is_array($obj)) {
            if (isset($obj->children)) {
                $arr[$obj->name] = self::xmlobj2array($obj->children);
            }
            if (isset($obj->content)) {
                $arr[$obj->name] = $obj->content;
            }
        } else {
            \reset($obj);
            foreach ($obj as $k => $v) {
                if (isset($v->children)) {
                    $arr[$v->name] = self::xmlobj2array($v->children);
                }
                if (isset($v->content)) {
                    $arr[$v->name] = $v->content;
                }
            }
        }

        return $arr;
    }

    public static function fileXmlToArray($nodeconf_dir)
    {
        //$nodeconf_dir=resource_path().'\\contextmenu\Agenda\tbl_cat_agenda\node_conf.xml';
        $nodeconf_dir = \str_replace('\\', \DIRECTORY_SEPARATOR, $nodeconf_dir);
        $nodeconf_dir = \str_replace('/', \DIRECTORY_SEPARATOR, $nodeconf_dir);
        //echo '<h3>['.$nodeconf_dir.']</h3>';
        //$xml_str=readfile($nodeconf_dir);
        //dd($nodeconf_dir);
        $xml = new \SimpleXmlElement($nodeconf_dir, 0, true);
        //$xml = \XmlParser::load($nodeconf_dir);
        //echo '<pre>';print_r($xml);echo '</pre>';
        //echo '<pre>';print_r($node_conf->icons['0']);echo '</pre>';
        //$tmp = \XmlParser::load($nodeconf_dir);
        $json = \json_encode($xml);
        $node = \json_decode($json, true);
        //echo '<pre>';print_r($node);echo '</pre>';
        return $node;
    }

    public static function xml2array_1($url, $get_attributes = 1, $priority = 'tag')
    {
        $contents = '';
        if (!\function_exists('xml_parser_create')) {
            return [];
        }
        $parser = \xml_parser_create('');
        if (!($fp = @\fopen($url, 'r'))) {
            return [];
        }
        while (!\feof($fp)) {
            $contents .= \fread($fp, 8192);
        }
        \fclose($fp);
        \xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
        \xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        \xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        \xml_parse_into_struct($parser, \trim($contents), $xml_values);
        \xml_parser_free($parser);
        if (!$xml_values) {
            return;
        } //Hmm...
        $xml_array = [];
        $parents = [];
        $opened_tags = [];
        $arr = [];
        $current = &$xml_array;
        $repeated_tag_index = [];
        foreach ($xml_values as $data) {
            unset($attributes, $value);
            \extract($data);
            $result = [];
            $attributes_data = [];
            if (isset($value)) {
                if ('tag' == $priority) {
                    $result = $value;
                } else {
                    $result['value'] = $value;
                }
            }
            if (isset($attributes) and $get_attributes) {
                foreach ($attributes as $attr => $val) {
                    if ('tag' == $priority) {
                        $attributes_data[$attr] = $val;
                    } else {
                        $result['attr'][$attr] = $val;
                    } //Set all the attributes in a array called 'attr'
                }
            }
            if ('open' == $type) {
                $parent[$level - 1] = &$current;
                if (!\is_array($current) or (!\in_array($tag, \array_keys($current), true))) {
                    $current[$tag] = $result;
                    if ($attributes_data) {
                        $current[$tag.'_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    $current = &$current[$tag];
                } else {
                    if (isset($current[$tag][0])) {
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                        ++$repeated_tag_index[$tag.'_'.$level];
                    } else {
                        $current[$tag] = [$current[$tag], $result];
                        $repeated_tag_index[$tag.'_'.$level] = 2;
                        if (isset($current[$tag.'_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag.'_'.$level] - 1;
                    $current = &$current[$tag][$last_item_index];
                }
            } elseif ('complete' == $type) {
                if (!isset($current[$tag])) {
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if ('tag' == $priority and $attributes_data) {
                        $current[$tag.'_attr'] = $attributes_data;
                    }
                } else {
                    if (isset($current[$tag][0]) and \is_array($current[$tag])) {
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                        if ('tag' == $priority and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level].'_attr'] = $attributes_data;
                        }
                        ++$repeated_tag_index[$tag.'_'.$level];
                    } else {
                        $current[$tag] = [$current[$tag], $result];
                        $repeated_tag_index[$tag.'_'.$level] = 1;
                        if ('tag' == $priority and $get_attributes) {
                            if (isset($current[$tag.'_attr'])) {
                                $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                                unset($current[$tag.'_attr']);
                            }
                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag.'_'.$level].'_attr'] = $attributes_data;
                            }
                        }
                        ++$repeated_tag_index[$tag.'_'.$level]; //0 and 1 index is already taken
                    }
                }
            } elseif ('close' == $type) {
                $current = &$parent[$level - 1];
            }
        }

        return $xml_array;
    }

    //---------------------------------------------------

    public function xml2obj($url)
    {
        $object = \json_decode(\json_encode(\simplexml_load_string($url)));

        return $object;
    }

    public function xmlobj2xmlstr($obj)
    {
        //Array_op::print_x($obj);
        $str = ia2xml($obj);

        return '<root>'.$str.'</root>';
    }

    public function array2xml($array)
    {
        return ia2xml($array);
    }

    public function ia2xml($array)
    {
        $xml = '';
        \reset($array);
        foreach ($array as $key => $value) {
            $key1 = $key;
            if (\is_numeric($key1)) {
                $key1 = 'row';
            }
            if (\is_array($value) || \is_object($value)) {
                $xml .= "<$key1>".ia2xml($value)."</$key1>";
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

    /**
     * convert xml string to php array - useful to get a serializable value.
     *
     * @param string $xmlstr
     *
     * @return array
     *
     * @author Adrien aka Gaarf
     */
    public function xmlstr_to_array($xmlstr)
    {
        $doc = new DOMDocument();
        $doc->loadXML($xmlstr);

        return domnode_to_array($doc->documentElement);
    }

    //-------------------------------------------------------------------

    public function domnode_to_array($node)
    {
        $output = [];
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = \trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; ++$i) {
                    $child = $node->childNodes->item($i);
                    $v = domnode_to_array($child);
                    if (isset($child->tagName)) {
                        $t = $child->tagName;
                        if (!isset($output[$t])) {
                            $output[$t] = [];
                        }
                        $output[$t][] = $v;
                    } elseif ($v) {
                        $output = (string) $v;
                    }
                }
                if (\is_array($output)) {
                    if ($node->attributes->length) {
                        $a = [];
                        foreach ($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string) $attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if (\is_array($v) && 1 == \count($v) && '@attributes' != $t) {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }

        return $output;
    }

    //function arrayToXml($array, $rootElement = null, $xml = null) {

    public function cmpMenu($a, $b)
    {
        //echo '[[A<pre>'; print_r($a); echo '</pre>]]';
        //echo '[[B<pre>'; print_r($b); echo '</pre>]]';
        // $a = preg_replace('@^(a|an|the) @', '', $a);
        // $b = preg_replace('@^(a|an|the) @', '', $b);
        //  return strcasecmp($a, $b);
        if (!isset($a->id_padre)) {
            //Array_op::print_x($a);
            $a->id_padre = 0;
        }
        if (!isset($a->ordine)) {
            //Array_op::print_x($a);
            $a->ordine = 10;
        }
        if (!isset($b->id_padre)) {
            //Array_op::print_x($a);
            $b->id_padre = 0;
        }
        if (!isset($b->ordine)) {
            //Array_op::print_x($a);
            $b->ordine = 10;
        }

        if ($a->id_padre == $b->id_padre && isset($a->ordine) && isset($b->ordine)) {
            return $a->ordine > $b->ordine;
        }

        return $a->id_padre > $b->id_padre;
    }

    public function createSequenceXML($seq_file)
    {
        $xmlstr = '<root><sequence>1023</sequence></root>';
        FILE_OP::writeFile($seq_file, $xmlstr);
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
                arrayToXml($params);
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

    public function xmlobjfind($params)
    {
        \extract($params);
        \reset($tags);
        $ris = [];
        foreach ($tags as $k => $v) {
            \reset($where);
            foreach ($where as $kw => $vw) {
                //echo '<br/>'.$kw.' = '.$vw .'?';
                if (isset($v->$kw) && $v->$kw == $vw) {
                    //Array_op::print_x($v);
                    $ris[] = $v;
                }
                if (isset($v->attributes->$kw) && $v->attributes->$kw == $vw) {
                    $ris[] = $v;
                }
            }
            if (isset($v->children) && \is_array($v->children)) {
                $params['tags'] = $v->children;
                $ris = \array_merge($ris, xmlobjfind($params));
            }
        }

        return $ris;
    }

    public function cleanupXML($xml)
    {
        $xmlOut = '';
        $inTag = false;
        $xmlLen = \mb_strlen($xml);
        for ($i = 0; $i < $xmlLen; ++$i) {
            $char = $xml[$i];
            // $nextChar = $xml[$i+1];
            switch ($char) {
                case '<':
                    if (!$inTag) {
                        // Seek forward for the next tag boundry
                        for ($j = $i + 1; $j < $xmlLen; ++$j) {
                            $nextChar = $xml[$j];
                            switch ($nextChar) {
                                case '<':  // Means a < in text
                                    $char = \htmlentities($char);
                                    break 2;
                                case '>':  // Means we are in a tag
                                    $inTag = true;
                                    break 2;
                            }
                        }
                    } else {
                        $char = \htmlentities($char);
                    }
                    break;
                case '>':
                    if (!$inTag) {  // No need to seek ahead here
                        $char = \htmlentities($char);
                    } else {
                        $inTag = false;
                    }
                    break;
                default:
                    if (!$inTag) {
                        $char = \htmlentities($char);
                    }
                    break;
            }
            $xmlOut .= $char;
        }

        return $xmlOut;
    }

    public function xmlobj_inline($params)
    {
        //if(!isset($params['ris'])) $params['ris']=array();
        \extract($params);
        $ris = [];
        \reset($obj);
        foreach ($obj as $k => $v) {
            $t = $v;
            if (isset($v->children)) {
                $sons = xmlobj_inline(['obj' => $v->children]);
                $ris = \array_merge($ris, $sons);
            }
            if (isset($t->children)) {
                unset($t->children);
            } // non togliendo mi permette di fare un doppio tipo di navigazione
            $ris[] = $t;
        }

        return $ris;
    }

    /**
     * @get text between tags
     *
     * @param string $tag    The tag name
     * @param string $html   The XML or XHTML string
     * @param int    $strict Whether to use strict mode
     *
     * @return array
     */
    public function getTextBetweenTags($tag, $html, $strict = 0)
    {
        /*** a new dom object ***/
        $dom = new domDocument();

        /*** load the html into the object ***/
        if (1 == $strict) {
            $dom->loadXML($html);
        } else {
            $dom->loadHTML($html);
        }

        /*** discard white space ***/
        $dom->preserveWhiteSpace = false;

        /*** the tag by its tag name ***/
        $content = $dom->getElementsByTagname($tag);

        /*** the array to return ***/
        $out = [];
        foreach ($content as $item) {
            /*** add node value to the out array ***/
            $out[] = $item->nodeValue;
        }
        /*** return the results ***/
        return $out;
    }

    //------------//-----------------//-------------------
    //------------
}//end class
