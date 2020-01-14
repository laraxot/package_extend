<?php



class STRING_OP extends baseController
{
    // given string of six hex digits, return array of 3 color values
    public function str_to_rgb($str)
    {
        return [
      \hexdec(\mb_substr($str, 0, 2)),
      \hexdec(\mb_substr($str, 2, 2)),
      \hexdec(\mb_substr($str, 4, 2)),
    ];
    }

    public function salt()
    {
        //return substr(md5(uniqid(rand(), true)), 0, $this->config->item('salt_length'));
        return \mb_substr(\md5(\uniqid(\rand(), true)), 0, 10);
    }

    ///------------------------------------------------------------------------------------------------------
    public function serialize2ArrayEval($str)
    {
        if (\is_array($str)) {
            return $str; //elemento gia' tradotto
        }
        $defaults = \unserialize($str);
        if (\is_array($defaults)) {
            foreach ($defaults as $ko => $vo) {
                $defaults[$ko] = $v0; //$registry->template->fetch("eval:".$vo);
            }

            return $defaults;
        } else {
            echo '<h3>Serializzato male '.__LINE__.__FILE__.'</h3>';
        }
    }

    public function tagliaBordiA($params)
    { // in htmllib
        return tagliaBordi($params['needle0'], $params['needle1'], $params['str']);
        /*extract($params);
        $len=strlen($str);
        $pos0=strrpos($str,$needle0);
        if($pos0===false){
          return '';
        }
        $pos1=strpos($str,$needle1,$pos0);
        if($pos1===false){
          return '';
        }
        $str=substr($str,$pos0,$pos1-$len+strlen($needle0));
        return $str;
*/
    }

    //-------------------------------------------------------------------------------------------------------
    public function estraiBlocco($params)
    {
        // ARRAY_OP::print_x($params);
        \extract($params);
        $str .= 'pipppo';
        $len = \mb_strlen($str);
        $pos0 = \mb_strripos($str, $needle0);
        if (false === $pos0) {
            return [];
        }
        $pos1 = \mb_stripos($str, $needle1, $pos0);
        $str1 = \mb_substr($str, $pos0, $pos1 - $len + \mb_strlen($needle1));
        $str2a = \mb_substr($str, 0, $pos0);
        $str2b = \mb_substr($str, $pos1 - $len + \mb_strlen($needle1));
        //echo '<br/>str   : '.$str;
        //echo '<br/>pos 0 : '.$pos0;
        //echo '<br/>pos 1 : '.$pos1;
        //echo '<br/>len   : '.$len;
        //echo '<br/>str1  : '.$str1;
        //echo '<br/>str2a : '.$str2a;
        //echo '<br/>str2b : '.$str2b;
        $params['str'] = $str2a.$str2b;

        return \array_merge(self::estraiBlocco($params), [$str1]);
    }

    //-------------------------------------------------------------------------------------------------------
    public function myCrypt($str)
    {
        return \md5($str.'0');
    }

    public function hash_password($password = false)
    {
        $salt_length = 10; //$this->config->item('salt_length');
        if (false === $password) {
            //return false;
            return 'piipo';
        }
        $salt = self::salt();
        $password = $salt.\mb_substr(\sha1($salt.$password), 0, -$salt_length);

        return $password;
    }

    //-------------------------------------------------------------------------------------------------------
    public function fill($str, $space, $max, $align = 'left')
    {
        $ris = $str;
        $i = 0;
        while (\mb_strlen($ris) < $max) {
            switch ($align) {
                  case 'left':
                      $ris = $space.$ris;
                      break;
                  case 'right':
                      $ris = $ris.$space;
                      break;
                  case 'center':
                      $i++;
                      if (0 == $i % 2) {
                          $ris = $space.$ris;
                      } else {
                          $ris = $ris.$space;
                      }
                      break;
                  default:
                      break;
              }
            //end switch
        }
        //end while

        return $ris;
    }

    //end function
    //---------------------------------------------------------------------------
    public function fillZeri($str, $x)
    {
        $ris = \str_replace(',', '', $str);
        $ris = \str_replace('.', '', $str);
        while (\mb_strlen($ris) < $x) {
            $ris = '0'.$ris;
        }

        return $ris;
    }

    //----------------------------------------------------------------------------
    public function fillZeriValue100($str, $x)
    {
        $ris = \str_replace(',', '.', $str);
        $ris = \round($ris * 100, 0);
        if (\mb_substr($ris, 0, \mb_strpos($ris, '.'))) {
            $ris = \mb_substr($ris, 0, \mb_strpos($ris, '.'));
        }
        while (\mb_strlen($ris) < $x) {
            $ris = '0'.$ris;
        }
        //echo "<br>".$str." ----- ". $ris."<br>";
        return $ris;
    }

    /* // esempio funzionamento
    $str='<text:p text:style-name="P82"/>
    <text:p text:style-name="P82"/>
    <text:p text:style-name="P82"/>
    <text:p text:style-name="P217">
    {foreach from = $formazione[1] item = el }aaa
    </text:p>
    <text:p text:style-name="Standard"/>
    <text:p text:style-name="Standard"/>
    <text:p text:style-name="Standard"/>
    <text:p text:style-name="Standard"/>
    <text:p text:style-name="Standard"/>
    < text:p text:style-name = "P82" / > ';

    $str1 = '{foreach';
    $str2 = '}';
    $str3 = '</text';
    $str4 = '>';

    echo '<pre>'.htmlspecialchars($str).'</pre><hr/>';
    $str = self::scambiaPostoStr($str, $str1, $str2, $str3, $str4);
    echo '<pre>'.htmlspecialchars($str).'</pre>';
    //print_r($str);
    */
    public function scambiaPostoStr($str, $str1, $str2, $str3, $str4, $start = 0)
    {
        $off = 0;
        while ($off < \mb_strlen($str)) {
            list($str, $off) = self::scambiaPostoStrOff($str, $str1, $str2, $str3, $str4, $off);
        }

        return $str;
    }

    public function scambiaPostoStrOff($str, $str1, $str2, $str3, $str4, $start = 0)
    {
        //$start = 0;
        //$str1 = '{foreach';
        $pos1 = \mb_strpos($str, $str1, $start);
        //echo '<br/>POS1 : '.$pos1;
        if (false === $pos1) {
            return [$str, \mb_strlen($str) + 1];
        }
        //$str2 = '}';
        $pos2 = \mb_strpos($str, $str2, $pos1);
        //echo '<br/>POS2 : '.$pos2;
        $find1 = \mb_substr($str, $pos1, $pos2 - $pos1 + 1);
        //echo '<br/>'.htmlspecialchars($find1);
        //-------------------------------------------------------
        //$str3 = '</text';
        $pos3 = \mb_strpos($str, $str3, $pos2);
        //echo '<br/>POS3 : '.$pos3;
        //$str4 = '>';
        $pos4 = \mb_strpos($str, $str4, $pos3);
        //echo '<br/>POS4 : '.$pos4;
        $find2 = \mb_substr($str, $pos3, $pos4 - $pos3 + 1);
        //echo '<br/>'.htmlspecialchars($find2);
        //----------------------------------------------------------
        $text0 = \mb_substr($str, 0, $pos1);
        //echo '<hr/>'.htmlspecialchars($text0);
        $text1 = \mb_substr($str, $pos2 + 1, $pos3 - $pos2 - 1);
        //echo '<hr/>'.htmlspecialchars($text1);
        $text2 = \mb_substr($str, $pos4 + 1);
        //echo '<hr/>'.htmlspecialchars($text2);
        //echo '<hr/><pre>'.htmlspecialchars($text0.$text1.$find2.$find1.$text2).'</pre>';
        $ris = [];
        $ris[0] = $text0.$text1.$find2.$find1.$text2;
        $ris[1] = $pos4;

        return $ris;
    }

    //---------------------
    public function gen_slug($str)
    {
        // special accents
        $a = ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'Ð', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', '?', '?', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', '?', '?', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', '?', 'O', 'o', 'O', 'o', 'O', 'o', 'Œ', 'œ', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'Š', 'š', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Ÿ', 'Z', 'z', 'Z', 'z', 'Ž', 'ž', '?', 'ƒ', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', '?', '?', '?', '?', '?', '?'];
        $b = ['A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'];
        $str = \str_replace(' ', '-', $str);
        $str = \mb_strtolower(\preg_replace(['/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'], ['', '-', ''], \str_replace($a, $b, $str)));
        $str = \trim($str);

        return $str;
    }

    public function slugify($text)
    {
        $text = self::normalize($text);
        //$text=trim($text);
        //$text=str_replace(' ','-',$text);
        // replace non letter or digits by -
        $text = \preg_replace('~[^\\pL\d]+~u', '-', $text);
        //*
        // trim
        $text = \trim($text, '-');
        // transliterate
        $text = \iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // lowercase
        $text = \mb_strtolower($text);
        // remove unwanted characters
        $text = \preg_replace('~[^-\w]+~', '', $text);
        if (empty($text)) {
            return '';
        }
        $text = \str_replace('-', '_', $text);
        //  */
        return $text;
    }

    public function normalize($string)
    {
        $table = [
        'Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj', 'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
        'À' => "A'", 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
        'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
        'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
        'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
        'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b',
        'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r',
    ];

        return \strtr($string, $table);
    }

    public function normalize_special_characters($str)
    {
        // Quotes cleanup
    $str = ereg_replace(\chr(\ord('`')), "'", $str);        // `
    $str = ereg_replace(\chr(\ord('´')), "'", $str);        // ´
    $str = ereg_replace(\chr(\ord('„')), ',', $str);        // „
    $str = ereg_replace(\chr(\ord('`')), "'", $str);        // `
    $str = ereg_replace(\chr(\ord('´')), "'", $str);        // ´
    $str = ereg_replace(\chr(\ord('“')), '"', $str);        // “
    $str = ereg_replace(\chr(\ord('”')), '"', $str);        // ”
    $str = ereg_replace(\chr(\ord('´')), "'", $str);        // ´

    $unwanted_array = ['Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
                                'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
                                'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
                                'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
                                'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', ];
        $str = \strtr($str, $unwanted_array);

        // Bullets, dashes, and trademarks
    $str = ereg_replace(\chr(149), '&#8226;', $str);    // bullet •
    $str = ereg_replace(\chr(150), '&ndash;', $str);    // en dash
    $str = ereg_replace(\chr(151), '&mdash;', $str);    // em dash
    $str = ereg_replace(\chr(153), '&#8482;', $str);    // trademark
    $str = ereg_replace(\chr(169), '&copy;', $str);    // copyright mark
    $str = ereg_replace(\chr(174), '&reg;', $str);        // registration mark

    return $str;
    }

    public function estraiDatiDaCodFisc($codfis)
    {
        global $registry;

        $mdb2 = $registry->conf->getMDB2();

        /////////////////////////////
        $day = \mb_substr($codfis, 9, 2);
        if ($day < 40) {
            $sesso = 'M';
        } else {
            $day -= 40;
            $sesso = 'F';
        }
        $month = self::getMeseCodFisc(\mb_substr($codfis, 8, 1));
        $year = \mb_substr($codfis, 6, 2);
        $cod_catasto = \mb_substr($codfis, 11, 4);
        //  echo "<br> cod catasto = ".$codfis."[".$cod_catasto."]";
        $sql = "select * from cap.comuni where cod_catasto like '".$cod_catasto."'";
        $data = $mdb2->queryAll($sql);
        //echo $data[0]->descrizione;

        $ris = ['d' => $day, 'm' => $month, 'Y' => $year, 'dateIT' => $day.'/'.$month.'/'.$year, 'timestamp' => \mktime(0, 0, 0, $month, $day, $year), 'comune' => $data[0]->descrizione, 'prov' => $data[0]->provincia, 'sesso' => $sesso];

        return $ris;
        //echo $codfis." = > ". $day."/".$month."/".$year;
    }

    //------------------------------------------------------------------------
    public function getMeseCodFisc($str)
    {
        switch ($str) {
              case 'A':
                  return 1;
                  //   gennaio
                  break;
              case 'B':
                  return 2;
                  //   febbraio
                  break;
              case 'C':
                  return 3;
                  //   marzo
                  break;
              case 'D':
                  return 4;
                  //   aprile
                  break;
              case 'E':
                  return 5;
                  //   maggio
                  break;
              case 'H':
                  return 6;
                  //   giugno
                  break;
              case 'L':
                  return 7;
                  //   luglio
                  break;
              case 'M':
                  return 8;
                  //   agosto
                  break;
              case 'P':
                  return 9;
                  //   settembre
                  break;
              case 'R':
                  return 10;
                  //   ottobre
                  break;
              case 'S':
                  return 11;
                  //   novembre
                  break;
              case 'T':
                  return 12;
                  //   dicembre
                  break;
          }

        return -1;
    }

    // --------------------------------------------------------------------------

    //inserisce la inserme dentro la str dopo la stringa findme
    public function insertStrAfterLastStr($str, $insertme, $findme)
    {
        $pos = \mb_strrpos($str, $findme);
        if (false !== $pos) {
            echo '<h3>TROVATO  ['.$pos.']</h3>';
            $pos1 = $pos + \mb_strlen($findme);
            $str1 = \mb_substr($str, 0, $pos1).$insertme.\mb_substr($str, $pos1);

            return $str1;
        }
        echo '<h3>non trovato</h3>';

        return $str;
    }

    //----------------------------------------------------------
    //inserisce la stringa inserme dentro la str nella posizione pos
    public function insertStrInPos($str, $insertme, $pos)
    {
        return $str1 = \mb_substr($str, 0, $pos).$insertme.\mb_substr($str, $pos);
    }

    //--------//-----------//-----------------------
    //---- cancella la prima occorrenza di str
    //--------------//-----------------//----------

    public function popStr($content, $str)
    {
        $pos = \mb_strpos($content, $str);
        if (false !== $pos) {
            return \mb_substr($content, 0, $pos).\mb_substr($content, $pos + \mb_strlen($str));
        }

        return $content;
    }

    //------------
}//end class
