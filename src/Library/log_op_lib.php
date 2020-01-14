<?php



class LOG_OP extends baseController
{
    public function get_tbl($params)
    {
        return 'mylog';
    }

    public function microtime_float()
    {
        list($usec, $sec) = \explode(' ', \microtime());

        return (float) $usec + (float) $sec;
    }

    public function insert($params)
    {
        $tbl = self::get_tbl($params);
        $dbname = $this->registry->conf->__get('dbname');
        $me = $this->registry->user->__get('me');
        $params['handle'] = $me->handle;
        $params['datemod'] = \date('Y-m-d H:i:s');
        $parz = ['tablename' => $tbl, 'dbname' => $dbname, 'data' => $params];
        $regenerate = 0;
        $fields = DO_OP::getFields($parz);
        if (!\in_array('lockx', $fields, true)) { //lock e' riservata
            $parz['add'] = ['lockx' => 'INT(1) NULL DEFAULT NULL'];
            DO_OP::alterTable($parz);
            //$this->registry->conf->regenerate($params);
            $regenerate = 1;
        }

        DO_OP::insert($parz);
        ///------------------------------------------------------------------
        $parz = [];
        $parz['dbname'] = $dbname;
        $tablename = $params['tbl'];
        $parz['tablename'] = $tablename;
        $fields = DO_OP::getFields($parz);

        if (!\in_array('lockx', $fields, true)) { //lock e' riservata
            $parz['add'] = ['lockx' => 'INT(1) NULL DEFAULT NULL'];
            DO_OP::alterTable($parz);
            $regenerate = 1;
        }
        if (!\in_array('datemod', $fields, true)) { //lock e' riservata
            $parz['add'] = ['datemod' => 'DATETIME NULL DEFAULT NULL'];
            DO_OP::alterTable($parz);
            $regenerate = 1;
        }
        if (!\in_array('handle', $fields, true)) { //lock e' riservata
            $parz['add'] = ['handle' => 'VARCHAR(100) NULL DEFAULT NULL'];
            DO_OP::alterTable($parz);
            $regenerate = 1;
        }
        if ($regenerate) {
            $this->registry->conf->regenerate($params);
        }

        $data = ['datemod' => $params['datemod'], 'handle' => $me->handle, 'lockx' => $params['lockx']];
        DO_OP::update(['tablename' => $tablename, 'dbname' => $dbname, 'id' => $params['id_tbl'], 'data' => $data]);

        return true;
    }

    //$t[] = DATE_OP::microtime_float();
    //$nt = count($t);
    //echo '<br/>--['.__FILE__.']['.__LINE__.'] Time : '.($t[$nt-1] - $t[$nt - 2]);

    public function showTime()
    {
        $bt = \debug_backtrace();
        //echo '<pre>'; print_r($bt); echo '</pre>';
        if (!isset($_SESSION['lasttime'])) {
            echo '<br/> init time';
        } else {
            echo '<table border="1">';
            $bt[0]['function '] = $bt[1]['function '];
            foreach ($bt[0] as $k => $v) {
                echo '<tr><td>'.$k.'</td><td>';
                if (\is_array($v)) {
                    echo \implode(',', $v);
                } else {
                    echo $v;
                }
                echo '</td></tr>';
            }
            //echo '<br/> Time: '.round(DATE_OP::microtime_float() - $_SESSION['lasttime'], 3);
            $tt = \round(DATE_OP::microtime_float() - $_SESSION['lasttime'], 3);
            echo '<tr><td><b>Time:</b></td><td>'.$tt.'</td></tr>';
            echo '</table>';
        }
        $_SESSION['lasttime'] = DATE_OP::microtime_float();
        //ddd('qui');
    }

    //----------------------------------------------------------
    public function showLog($params)
    {
        $html = '';
        $fieldstorender = null;
        \extract($params);
        $do1 = DB_DataObject::factory('mylog');
        //$dbname=$this->__get('dbname');
        $dbname = $registry->conf->__get('dbname');
        //ddd($dbname);
        //ddd($do1);
        $do1->database($dbname);
        $do1->whereAdd($whereAdd);
        $do1->orderBy('datemod');
        $log = $do1->fetchAll();
        $html .= '<h3>Lista Modifiche</h3><table border="1">';
        $html .= '<tr><th><b>ID/Utente<br/>Data e ora modifica</b>
	<br/>Stato
	</th><th><b>Dati Modificati</b></th></tr>';
        foreach ($log as $k => $v) {
            $html .= '<tr><td>'.$v->id.'] '.$v->handle.'<br/>'.\date('d/m/Y H:i:s', \strtotime($v->datemod));
            $html .= '<br/>'.$v->id_approvaz;
            $html .= '   ';
            $html .= '</td>';
            $tmp = \unserialize($v->data);
            if (!\is_array($tmp)) {
                //ARRAY_OP::print_x($tmp);
                //ddd('qui');
                if (\is_bool($tmp)) {
                    //ARRAY_OP::print_x($v);ddd('qui');
                    $tmp = [];
                } else {
                    $tmp = \get_object_vars($tmp);
                }
            }
            //ARRAY_OP::print_x(array_keys($tmp));
            //ARRAY_OP::print_x($fieldstorender);
            if (null == $fieldstorender) {
                $fields = \array_keys($tmp);
            } else {
                $fields = \array_intersect($fieldstorender, \array_keys($tmp));
            }
            //ARRAY_OP::print_x($fields);
            $html .= '<td>';
            \reset($fields);
            foreach ($fields as $kf => $vf) {
                if (\is_object($tmp)) {
                    $html .= '<i>'.$vf.'</i> : '.$tmp->$vf.'<br/>';
                }
                if (\is_array($tmp)) {
                    $val = $tmp[$vf];
                    if (\is_array($tmp[$vf])) {
                        $val = @\implode('-', $tmp[$vf]);
                    }
                    $html .= '<i>'.$vf.'</i> : '.$val.'<br/>';
                }
            }
            $html .= '</td>';
        }
        $html .= '</table>';
        //echo $html;
        return $html;
    }

    //----------------------------------------------------------
    public function mylog($id_tbl, $tblname = '', $note = '', $stato = null)
    {
        global $registry;
        if (\is_array($id_tbl)) {
            \extract($id_tbl);
        }
        $params = [];
        $me = $registry->user->__get('me');
        $handle = $me->handle;
        $datemod = \date('Y-m-d H:i:s');

        $do1 = DB_DataObject::factory($tblname);
        //$dbname=$this->__get('dbname');
        $dbname = $registry->conf->__get('dbname');
        $do1->database($dbname);
        //ddd($dbname);
        //$dbname=$do1->database();
        //ddd($dbname);
        $do1->get($id_tbl);
        $do1->handle = $handle;
        $do1->datemod = $datemod;
        $data = \serialize($do1);
        $do1->update();

        //---------------------------------------------
        //$mdb2 = $registry->conf->getMDB2();
        $do = DB_DataObject::factory('mylog');
        $do->database($dbname);
        $do->id_tbl = $id_tbl;
        $do->tbl = $tblname;
        $do->note = $note;
        $do->id_approvaz = $stato;
        $do->data = $data;

        if (isset($obj)) {
            $do->obj = $obj;
        }
        if (isset($obj)) {
            $do->act = $act;
        }

        $do->handle = $handle;
        $do->datemod = $datemod;
        $do->insert();
    }

    //-----------------------------------------------------------------------------------------
    public function cambiaStato($params)
    {// uguale a changeStato ma come input ha solito array
        //ARRAY_OP::print_x($params);
        if (!isset($params['stato']) && isset($params['last_stato'])) {
            $params['stato'] = $params['last_stato'];
        }

        $ids = \explode(',', $params['id_tbl']);
        if (\count($ids) > 1) {
            \reset($ids);
            foreach ($ids as $k => $id_tbl) {
                $params['id_tbl'] = $id_tbl;
                //ARRAY_OP::print_x($id_tbl);
                self::cambiaStato($params);
            }
        } else {
            global $registry;
            $me = $registry->user->__get('me');
            $myhandle = $me->handle;
            $do1 = DB_DataObject::factory($params['tbl']);
            $do1->get($params['id_tbl']);
            //ARRAY_OP::print_x($do1);
            $data_ser = \serialize($do1);

            $do = DB_DataObject::factory('mylog');
            $do->id_tbl = $params['id_tbl'];
            $do->tbl = $params['tbl'];
            $do->note = $params['note'];
            $do->id_approvaz = $params['stato'];
            //---------
            $do->handle = $myhandle;
            $do->datemod = \date('Y-m-d H:i:s');
            $do->data = $data_ser;
            $do->insert();
            //ARRAY_OP::print_x($params);

            //echo '<br> ['.$do1->last_stato.'  = '.$params['stato'].']';
            if ($do1->last_stato == $params['stato']) {
                $msg = '<br/>&nbsp;'.$params['tbl'].'['.$params['id_tbl'].'] gia\' in stato '.$params['stato'];
                $registry->template->showMsg($msg, 'error');

                return;
            }

            $do1->note = $params['note'];
            $do1->last_stato = $params['stato'];
            $do1->handle = $myhandle;
            $datemod = \date('Y-m-d H:i:s');
            if (isset($params['datemod'])) {
                if (\is_array($params['datemod'])) {
                    $d = $params['datemod'];
                    $time = \mktime(0, 0, 0, $d['m'], $d['d'], $d['Y']);
                    $datemod = \date('Y-m-d H:i:s', $time);
                } else {
                    $datemod = \date('Y-m-d H:i:s', \strtotime($params['datemod']));
                }
            }

            //ARRAY_OP::print_x($datemod);
            $do1->datemod = $datemod;
            $do1->update();
            //ARRAY_OP::print_x($do1);
        //$registry->template->showMsg('fatto','success'); // nelle scelte multiple venivano fuori troppi messaggi
        }

        return;
    }

    //--------------------------------------------------------------------------------------
    public function changeStato($id_tbl, $tbl, $stato, $note = null, $datas = '')
    {
        if ('' == $stato || null == $stato) {
            return;
        }
        //ARRAY_OP::print_x($stato);ddd('qui');
        $tblname = $tbl;
        global $registry;
        //$dbname=$this->__get('dbname');
        $dbname = $registry->conf->__get('dbname');
        //ddd($dbname);ddd('qui');
        $me = $registry->user->__get('me');
        $do = DB_DataObject::factory('mylog');
        $do->database($dbname);
        $do->id_tbl = $id_tbl;
        $do->tbl = $tblname;
        $do->note = $note;
        $do->id_approvaz = $stato;
        //---------

        $do->handle = $me->handle;
        $do->datemod = \date('Y-m-d H:i:s');
        $do1 = DB_DataObject::factory($tbl);
        $do1->database($dbname);
        $do1->get($id_tbl);
        $do->data = \serialize($do1);
        $do->insert();

        if ($do1->last_stato == $stato) {
            $msg = '<br/>&nbsp;'.$tbl.'['.$id_tbl.'] gia\' in stato '.$stato;
            $registry->template->showMsg($msg, 'error');

            return;
        }
        $do1->note = $note;
        $do1->last_stato = $stato;
        $do1->handle = $me->handle;

        if ('' != $datas) {
            if (\is_array($datas)) {
                $time = \mktime(0, 0, 0, $datas['m'], $datas['d'], $datas['Y']);
                $do1->datemod = \date('Y-m-d H:i:s', $time);
            } else {
                $do1->datemod = \date('Y-m-d H:i:s', \strtotime($datas));
            }
        } else {
            $do1->datemod = \date('Y-m-d H:i:s');
        }

        $do1->update();
        //ARRAY_OP::print_x($do1);
        //$registry->template->showMsg('fatto','success'); // nelle scelte multiple venivano fuori troppi messaggi
        return;
    }

    public function get_ip_address()
    {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (true === \array_key_exists($key, $_SERVER)) {
                foreach (\explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = \trim($ip);
                    // attempt to validate IP
                    if (self::validate_ip($ip)) {
                        return $ip;
                    }
                }
            }
        }

        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
    }

    /**
     * Ensures an ip address is both a valid IP and does not fall within
     * a private network range.
     */
    public function validate_ip($ip)
    {
        if (false === \filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        return true;
    }

    //------------//-----------------//-------------------
//------------
}//end class
