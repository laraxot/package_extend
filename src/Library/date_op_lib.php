<?php



class DATE_OP extends baseController
{
    public static function microtime_float()
    {
        list($usec, $sec) = \explode(' ', \microtime());

        return (float) $usec + (float) $sec;
    }

    public function dateArray2time($d)
    {
        if (!\is_array($d)) {
            ARRAY_OP::print_x($d);
            ddd('qui');
        }
        if (!isset($d['m'])) {
            ARRAY_OP::print_x($d);
            ddd('qui');
        }

        if (!isset($d['h'])) {
            $d['h'] = 0;
        }
        if (!isset($d['i'])) {
            $d['i'] = 0;
        }
        if (!isset($d['s'])) {
            $d['s'] = 0;
        }
        $time = \mktime($d['h'], $d['i'], $d['s'], $d['m'], $d['d'], $d['Y']);

        return $time;
    }

    public function getTimestrDiff($t0, $t1)
    {
        $h = \mb_substr($t0, 0, -2);
        $i = \mb_substr($t0, -2);
        $t0_time = \mktime($h, $i, 00, \date('m'), \date('d'), \date('Y'));
        $h = \mb_substr($t1, 0, -2);
        $i = \mb_substr($t1, -2);
        $t1_time = \mktime($h, $i, 00, \date('m'), \date('d'), \date('Y'));

        $t = $t0_time - $t1_time;
        $ris = ['min' => \round($t / 60, 0), 'hi' => min2HourMin($t / 60)];

        return $ris;
    }

    public function min2HourMin($min)
    {
        $h = \round($min / 60, 0);
        $i = \round($min - ($h * 60), 0);
        if ($i < 0) {
            $h = $h - 1;
            $i = \round($min - ($h * 60), 0);
        }

        $time = \mktime($h, $i, 0, \date('m'), \date('d'), \date('Y'));

        return \date('H:i', $time);
    }

    public function getGiorniSenzaHoliday($dal, $al)
    {
        require_once 'Date/Holidays.php';
        $dal_time = \strtotime($dal);
        $al_time = \strtotime($al);
        $anno = \date('Y', $dal_time);

        $holiday = &Date_Holidays::factory('Italy', $anno, 'it_IT');
        if (Date_Holidays::isError($holiday)) {
            die('Factory was unable to produce driver-object');
        }

        $patrono = \mktime(0, 0, 0, 4, 27, $anno); //patrono
        $giorni = (($al_time - $dal_time) / (60 * 60 * 24)) + 1;
        if ($patrono >= $al_time && $patrono <= $dal_time) {
            --$giorni;
        }
        $holiday_arr = $holiday->_holidays;
        foreach ($holiday_arr as $k => $v) {
            if ($k >= $al_time && $k <= $dal_time) {
                --$giorni;
            }
        }
        $cur_date = $dal;
        // tolgo domeniche
        //while ($cur_date <= $al) {
        //if(date('w'
        //}

        return $giorni;
    }

    // --------------------------------------------------------------------------

    public function dateDiff($dateFrom, $dateTo, $dateMax)
    {
        //echo '<pre>';print_r(convDate($dateFrom));
        if (0 == $dateTo) {
            $dateTo = $dateMax;
        }
        if ($dateTo * 1.0 > $dateMax * 1.0) {
            $dateTo = $dateMax;
        // echo 'correggo['.$dateMax."]";
        } else {
        }
        /*
        $date0 = convDate($dateFrom);
        $date0Time = mktime(0, 0, 0, $date0["m"], $date0["d"], $date0["Y"]);
        $date1 = convDate($dateTo);
        $date1Time = mktime(0, 0, 0, $date1["m"], $date1["d"], $date1["Y"]);
        */
        $date0Time = \strtotime($dateFrom);
        $date1Time = \strtotime($dateTo);

        //echo  '<br>['.$dateFrom.']['.$date0Time.']['.date('d/m/Y',$date0Time).']';
        // aggiungo un giorno per dire
        return \round((($date1Time - $date0Time) / (60 * 60 * 24)), 0) + 0;
    }

    //------------//-----------------//-------------------
//------------
}//end class
