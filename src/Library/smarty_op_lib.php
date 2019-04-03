<?php



class SMARTY_OP extends baseController
{
    public function smarty_block($content)
    {
        while (1) {
            $pattern = '/\[(block=([^\]]*))\]/';
            \preg_match($pattern, $content, $matches);
            if (!isset($matches[1])) {
                return $content;
            }
            //echo '['.__LINE__.']['.__FILE__.']<pre>'; print_r($matches); echo '</pre>';
            $tmp = \explode(' ', $matches[1]);
            \reset($tmp);
            $data = [];
            foreach ($tmp as $k => $v) {
                $tmp1 = \explode('=', $v);
                $data[$tmp1[0]] = \trim(\str_replace('"', '', $tmp1[1]));
            }
            //echo '<pre>'; print_r($data); echo '</pre>';
            $pos = \mb_strpos($content, $matches[0]);
            //echo '<br/>pos : '.$pos;
            $tag_open = '<'.$data['block'].'';
            $tag_close = '</'.$data['block'].'';
            $pos1 = \mb_strrpos(\mb_substr($content, 0, $pos), $tag_open);
            //echo '<br/>'.htmlspecialchars($tag_open).'  pos1 : '.$pos1;
            $pos2 = \mb_strpos($content, $tag_close, $pos);
            //echo '<br/>'.htmlspecialchars($tag_close).' pos2 : '.$pos2;
            $ins1 = '{foreach '.$data['from'].' as $'.$data['item'].'}';
            $ins2 = '{/foreach}';
            $content = STRING_OP::insertStrInPos($content, $ins1, $pos1);
            $pos3 = \mb_strpos($content, '>', $pos2 + \mb_strlen($ins1)) + 1;
            $content = STRING_OP::insertStrInPos($content, $ins2, $pos3);
            //echo '<pre>'.htmlspecialchars($content); echo '</pre>';
            $content = STRING_OP::popStr($content, $matches[0]);
        }
        //return $content;
    }

    //-------------------------------------------------------------------------------
    public function sistema_chiamate_smarty($str)
    {
        \preg_match_all('/({[^}]*})/', $str, $out);
        //echo '<pre>'; print_r($out[0]); echo '</pre>';
        foreach ($out[0] as $k => $v) {
            $ris = $v;
            $ris = \str_replace(\chr(13), '', $ris);
            $ris = \str_replace(\chr(10), '', $ris);
            //echo $ris;
            \preg_match_all('/(<[^>]*>)/', $ris, $out1);
            //echo '<pre>'; print_r($out1); echo '</pre>';
            $str_new = \strip_tags($ris).\implode('', $out1[1]);
            $str = \str_replace($v, $str_new, $str);
        }
        //*
        $str1 = '{foreach';
        $str2 = '}';
        $str3 = '</text';
        $str4 = '>';
        $str = STRING_OP::scambiaPostoStr($str, $str1, $str2, $str3, $str4);
        $str1 = '{/foreach';
        $str = STRING_OP::scambiaPostoStr($str, $str1, $str2, $str3, $str4);
        //*
        $str1 = '{if';
        $str2 = '}';
        $str3 = '</text';
        $str4 = '>';
        $str = STRING_OP::scambiaPostoStr($str, $str1, $str2, $str3, $str4);
        $str1 = '{/if';
        $str = STRING_OP::scambiaPostoStr($str, $str1, $str2, $str3, $str4);
        //*/
        //$str = sistema_foreach($str);
        $str = sistema_tag(['content' => $str, 'statement' => 'foreach']);

        return $str;
    }

    //--------------------------
    //- in $content,$statement
    public function sistema_tag($params)
    {
        \extract($params);
        $str1 = '{'.$statement;
        $pos = \mb_strpos($content, $str1);
        //echo '<br/>'.$pos;
        $str2 = '{/'.$statement;
        $pos1 = \mb_strpos(\mb_substr($content, $pos), $str2);
        //echo '<br/>'.$pos1;
        $pattern = '/({[^}]*})/';
        $pattern = '/((\{['.$statement.'][^}]*\})(.*?)\{\/'.$statement.'\})/s';
        \preg_match_all($pattern, $content, $out);
        //echo '<br/>risultati:'.count($out[1]).'<br/>';
        //echo '<pre>'; print_r($out); echo '</pre>';
        //*
        foreach ($out[3] as $k => $v) {
            //echo 'TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT<hr/>['.$k.']<pre>'.htmlspecialchars($v).'</pre>';
            $tidy_config = [
            'clean' => true,
            //'output-xhtml' => true,
            'show-body-only' => true,
            'wrap' => 0,
            'indent' => true,
             //ml' => true,
            'output-xml' => true,
            'input-xml' => true,
        ];
            //*
            $tidy = \tidy_parse_string($v, $tidy_config, 'UTF8');
            $tidy->cleanRepair();

            $xml_out = $tidy;

            //echo 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA<hr/><pre>'.htmlspecialchars($xml_out).'</pre>';
            $content = \str_replace($out[1][$k], $out[2][$k].$xml_out.'{/'.$statement.'}', $content);
        }
        $content = \str_replace('-&gt;', '->', $content);

        return $content;
    }

    //---------------------------------
    public function sistema_foreach($content)
    {
        $str1 = '{foreach';

        $pos = \mb_strpos($content, $str1);

        //echo '<br/>'.$pos;

        $str2 = '{/foreach';

        $pos1 = \mb_strpos(\mb_substr($content, $pos), $str2);
        //echo '<br/>'.$pos1;

        $pattern = '/({[^}]*})/';
        $pattern = '/((\{[foreach][^}]*\})(.*?)\{\/foreach\})/s';

        \preg_match_all($pattern, $content, $out);
        //echo '<br/>risultati:'.count($out[1]).'<br/>';

        //echo '<pre>'; print_r($out); echo '</pre>';
        //*
        foreach ($out[3] as $k => $v) {
            //echo 'TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT<hr/>['.$k.']<pre>'.htmlspecialchars($v).'</pre>';
            $tidy_config = [
                     'clean' => true,
                     //'output-xhtml' => true,
                     'show-body-only' => true,
                     'wrap' => 0,
                     'indent' => true,
                     //ml' => true,
                    'output-xml' => true,
                    'input-xml' => true,
                     ];

            //*
            $tidy = \tidy_parse_string($v, $tidy_config, 'UTF8');
            $tidy->cleanRepair();

            $xml_out = $tidy;

            //echo 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA<hr/><pre>'.htmlspecialchars($xml_out).'</pre>';
            $content = \str_replace($out[1][$k], $out[2][$k].$xml_out.'{/foreach}', $content);
        }
        $content = \str_replace('-&gt;', '->', $content);

        return $content;
    }

    //------------//-----------------//-------------------
//------------
}//end class
