<?php



class NUM_OP extends baseController
{
    public function num_intersect($a0, $b0, $a1, $b1)
    {
        if ($a1 >= $a0 && $a1 <= $b0 && $b0 <= $b1) {
            return [$a1, $b0];
        }
        if ($a0 >= $a1 && $a0 <= $b0 && $b0 <= $b1) {
            return [$a0, $b0];
        }
        if ($a1 >= $a0 && $a1 <= $b1 && $b1 <= $b0) {
            return [$a1, $b1];
        }
        if ($a0 >= $a1 && $a0 <= $b1 && $b1 <= $b0) {
            return [$a0, $b1];
        }

        return [0, 0];
    }

    public function sistemaCampo($x, $t)
    {
        switch ($t) {
       case 'double':
         $tmp = $x;
    $tmp = \str_replace('€', '', $tmp);
    //	$tmp=str_replace("%","",$tmp);
        if (false != \mb_strpos($tmp, '.') && false != \mb_strpos($tmp, ',')) {
            $tmp = \str_replace('.', '', $tmp);
            $tmp = \str_replace(',', '.', $tmp);
        } else {
            $tmp = \str_replace(',', '.', $tmp);
        }
      $tmp = \trim($tmp);

        return $tmp;
        break;
        default:return $x; break;
     }
    }

    public function combinazione($n, $k)
    {
        $ris = fattoriale($n) / (fattoriale($n - $k) * fattoriale($k));

        return $ris;
    }

    public function fattoriale($n)
    {
        if (0 == $n) {
            return 1;
        }

        return $n * fattoriale($n - 1);
    }

    //----------
}//end class
