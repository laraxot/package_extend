<?php
class DO_OP extends baseController
{
    public function get_dbname($params)
    {
        $registry=$this->registry;
        $area=$registry->user->__get('areaCurr');
        //ddd($area);
        $dbname='';
        if (isset($area->db)) {
            $dbname=$area->db;
        }
        $setting=$this->registry->conf->settings;
        $dbname=str_replace('##DB_Liveuser##', $setting['root']['conf']['DB_Liveuser'], $dbname);
        $dbname=str_replace('##DB_frontend##', $setting['root']['conf']['DB_frontend'], $dbname);

        if ($dbname=='') {
            if (IN_ADMIN) {
                return $setting['root']['conf']['DB_Liveuser'];
            } else {
                return $setting['root']['conf']['DB_frontend'];
            }
        }
        return $dbname;
    }

    public function get_listDatabases($params)
    {
        $mdb2=$this->registry->conf->getMDB2();
        $listdb=$mdb2->listDatabases();
        return $listdb;
    }

    public function get_fieldlabels($params)
    {
        return array();
    }

    public function get_query($params)
    {
        return array();
    }

    public function get_rendererBootstrap($params)
    {
        require_once __MVC_DIR.'/includes/extPear/HTML_QuickForm_Renderer_Bootstrap.php';
        $rendererBootstrap = new HTML_QuickForm_Renderer_Bootstrap();
        return $rendererBootstrap;
    }

    //------------------------------------------------------------------------------------------------
    public function get_filter_sql($params)
    {
        $filter_sql='';
        return $filter_sql;
    }

    //-----------------------------------------------------------------------------------------------------
    // pluralize a name
    public static function _plural($name)
    {
        return "${name}s";
    }
    //-----------------------------------------------------------------------------------------------------

    //-----------------------------------------------------------------------------------------------------

    //----------------------------------------------------------------------------------------------------
    public function get_obj($params)
    {
        $menuCurr=$this->registry->menu->__get('menuCurr');
        //ddd($menuCurr);
        //return '';
        reset($menuCurr);
        foreach($menuCurr as $k => $v){
            $this->__set($k, $v);
        }
        if (isset($menuCurr['obj'])) {
            return $menuCurr['obj'];
        }
        return '';
    }

    public function get_fieldstorender($params)
    {
        return array();
    }

    public function get_defaults($params)
    {
        $tmp=array();
        return $tmp;
    }

    public function get_hiddenfields($params)
    {
        return array();
    }
    //---------------------------------------------------------------------------------
    public function get($params)
    {
        //$nome=$this->__get('nome');
        //echo '<h3>'.$this->__get('nome').'</h3>';
        //ddd($params);
        DO_OP::initOptions($params);

        if (!is_array($params)) {
            ddd($params);
        }
        extract($params);
        if (!isset($params['tablename'])) {
            $params['tablename']=$this->__get('tbl');
        }

        if (!isset($params['whereAdd'])) {
            $params['whereAdd']=self::getWhereAdd($params);
            //ddd($params['whereAdd']);
        }

        $params['tablename']=$params['tablename']; // da upgrade ..
        $ris=self::fetchAllX($params);
        return $ris;
    }
    //---------------------------------------------------------------------------------
    public function insert($params)
    {
        extract($params);
        $do=DB_DataObject::factory($tablename);
        if (isset($dbname) && $dbname!='') {
            $do->database($dbname);
        }
        //ddd($data);
        reset($data);
        foreach($data as $k=>$v){
            $do->$k=$v;
        }

        try {
            $res=$do->insert();
        } catch (PEAR_Exception $e) {
            ddd($e);
        }

        //ddd($do);
        return $res;
        //ddd($res);
    }

    //---------------------------------------------------------------------------------
    public function update($params)
    {
        extract($params);

        $do=DB_DataObject::factory($tablename);
        if (isset($dbname) && $dbname!='') {
            $do->database($dbname);
        }

        $do->get($id);
        //ddd($data);
        reset($data);
        foreach($data as $k=>$v){
            $do->$k=$v;
        }

        try {
            $res=$do->update();
        } catch (PEAR_Exception $e) {
            ddd($e);
        }

        //ddd($do);
        return $res;
        //ddd($res);
    }

    //-------------------------------------------------------------------------------
    public function show($params)
    {
        //DO_OP::initOptions($params);
        //ddd('a');
        $dg = new Structures_DataGrid($this->nrecords);

        //ddd('a');
        $do=$this->getDo($params);
        //ddd($do);
        if (!is_object($do)) {
            echo '<h3>creazione oggetto errata</h3>';
            return ;
        }

        $nris = $do->count();

        //echo
        //$do->limit(5);// test 4 speed
        //echo '<h3>'.$nris.' Risultati</h3>';
        if (isset($_GET['xls'])) {
            $do_clone = clone($do);
            $data = $do_clone-> fetchAll();
            $xls_parz=array('nomefile'=>'export-'.session_id()
                            ,'data'=>ARRAY_OP::array_render($data, $this->__get('fieldstorender'))
                            ,'fieldlabels'=>$this->fieldlabels);
            ARRAY_OP::toXLS($xls_parz);
        }

        //echo '<pre>'; print_r($do); echo '</pre>'; die('<br/>LINE:'.__LINE__.'<br/>FILE:'.__FILE__);
        $datagridOptions = array(
        'link_property' => 'fb_linkDisplayFields',
        'link_level' => 1,
        'link_keep_key' => false,
        );
        //$datagridOptions["generate_columns"] = true;
        //$datagridOptions["labels"] = array ("cognome" => "NN", 	);
        //ARRAY_OP::print_x($do);

        //ARRAY_OP::print_x($dg->sortSpec);
        //unset($dg->sortSpec['comune']);
        //ARRAY_OP::print_x(array_keys($do->table()));
        // corregge gli order by su field non esistenti
        $tables=array_keys($do->table());
        reset($dg->sortSpec);

        forach($dg->sortSpec as $k => $v){
            if (!in_array($k, $tables)) {
                unset($dg->sortSpec[$k]);
            }
        }

        //ddd($do);
        //ddd($datagridOptions);
        //ddd('qui');
        $error = $dg->bind($do, $datagridOptions, 'DataObject');
        //ddd('qui');
        if (PEAR::isError($error)) {
            echo '

		<br/> <b>LINE: </b>'.__LINE__.'<br/>
		<b> FILE: </b>'.__FILE__.'<br/>
		<b> MSG: </b>'.$error->getMessage() . '<br />
		<b> Info :</b>' . $error->getDebugInfo();
            ddd('qui');
        }


        /*
            $column =& $dg->getColumnByField('scadenza');
            $column->setFormatter(array($this,'mydatetime'));
        */
        	//echo '<pre>'; print_r($dg); echo '</pre>';
        //$this->primary_key = 'id';
        	//echo '[['.$do->count().']]';
        //echo $do->count();

        if ($nris == 0) {
            $tpl = '';

            //$tpl.=$this->bottonAzioniTable($params);
            $tpl.= '<h3>Nessuna Voce Presente</h3>';
            return $tpl;
        //$this->smarty->assign('content', $tpl);
        } else {
            //echo '<h3>'.$nris.' Risultati</h3>';
            $this->__set('nris', $nris);
            $this->__set('dg', $dg);

            //ARRAY_OP::print_x($dg->sortSpec);
            /*
            $params['dg']=$dg;
            $params['nris']=$nris;
            $params['primary_key']=$this->__get('primary_key');
            $params['fieldlabels']=$this->__get('primary_key');
            */
            //ddd($this->__get('fieldlabels'));
            return $this->registry->template->showDG($this->__getVars());
        }
    }
    //---------------------------------------------------------------------------------
    public function showEditable($params)
    {
        require_once 'HTML/QuickForm/DHTMLRulesTableless.php';
        require_once 'HTML/QuickForm/Renderer/Tableless.php';
        require_once 'Pager.php';
        require_once __MVC_DIR."/includes/extPear/HTML_QuickForm_Renderer_Xot.php";

        $puls = '<a class="btn btn-default" href="'.$_SERVER['REQUEST_URI'].'&xls=1"><i class="fa fa-file-excel-o"></i>crea XLS.</a>';

        //echo $puls;
        //$this->setVars($params);

        $pageID=$this->registry->template->__get('pageID');
        $nrecords=$this->registry->template->__get('nrecords');
        $orderBy=$this->registry->template->__get('orderBy');

        //echo '<h3>PAGE : '.$pageID.'<br/>nrecords : '.$nrecords.'</h3>';

        //$dg = new Structures_DataGrid($this->nrecords);
        /*
        if ($this->__get('tablename') == '') {
            $msg = ' assegna template->__set("tablename",.. ';
            $this->showMsg($msg, 'error');
            return;
        }
        */
        //ddd($this->__get('query'));
        //$do=$this->setDoVars($params);
        $params['id']='';
        unset($params['id']);
        $do=$this->getDo($params);
        //ddd($do);

        $tpl = '';

        //$tpl.= $this->bottonAzioniTable();

        //echo $tpl;
        $nris = $do->count();
        if ($nris == 0) {
            echo '<h3>NO RISULTATI</h3>';
            return;
        }

        if (isset($_GET['xls'])) {
            // '<pre>';print_r($this->fieldlabels); echo '</pre>';
            doARRAY_OP::toXLS(array('do'=>$do,'fieldlabels'=>$this->fieldlabels));
        }


        $params = array(
        'mode' => 'Sliding',
        //'nrecords' => $nrecords,
        'perPage'  => $nrecords,
        'delta' => 2,
        'totalItems' => $nris
    );
        //ddd($params);

        $pager = &Pager::factory($params);
        list($from, $to) = $pager->getOffsetByPageId($pageID);

        $caption = '<b>'.$do->count().' Righe from '.$from.' to '.$to.'</b> ';
        //echo $caption; return ;

        $do->limit($from - 1, $nrecords);
        $do->find();
        $keys = $do->keys();
        $this->primary_key = $keys[0];
        $this->registry->template->__set('primary_key', $keys[0]);

        $fb = array();
        $form = $this->__get('form');
        $i = 0;

        while ($do->fetch()) {
            $fb_tmp = & DB_DataObject_FormBuilder::create($do);
            $fb_tmp->elementNamePrefix = 'form_'.$i.'_';
            $fb_tmp->formHeaderText = $this->registry->template->actionColumnCallback(array('record' => $do->toArray()));  //'<a href="">Cancella</a>';
            $fb_tmp->formHeaderText .= '<input name="idList['.$do->id.']" type="checkbox" value="'.$do->id.'" id="qf_'.$do->id.'" />';
            //$fb_tmp->formHeaderText .= $do->id;
            //$fb_tmp->elementTypeMap['date'] = 'jquery_date';
            //$fb_tmp->elementTypeMap['datetime'] = 'jquery_datetime';
            $fb_tmp->elementTypeMap['date'] = 'html5_date';
            $fb_tmp->elementTypeMap['datetime'] = 'html5_datetime';
            //ARRAY_OP::print_x($this->__get('hiddenfields')); ddd('qui');
            if (count($this->__get('hiddenfields')) > 0) {
                $this->registry->conf->DataObjectCustomFields($do, $fb_tmp, $this->__get('hiddenfields'), 'hidden');
            }
            //$this->registry->conf->DataObjectCustomFields($do, $fb_tmp, array($keys[0]), 'text'); // nel caso di liste
            $fb_tmp->createSubmit = false;
            //
            //if ($i == $to - $from) {
            	//$fb_tmp->submitText = 'Salva '.$table;;
            	//$fb_tmp->createSubmit = true;
            	//$fb_tmp->useAccessors = true;
            	//$fb_tmp->linkNewValue = true;
            	//$fb_tmp->createSubmit = false;
            //}else {
            	//$fb_tmp->createSubmit = false;
            //}

            $fb_tmp->hidePrimaryKey = false;
            $fb_tmp->linkDisplayLevel = 2;
            $fb_tmp->fieldAttributes = array($keys[0] => array('readonly' => '1','style'=>'color:gray;width:5em'));
            //$fb_tmp->fieldAttributes = array($keys[0] => array('readonly' => '1'));
            if ($i>0) {
                $fb_tmp->useForm($form);
            }
            $form = & $fb_tmp->getForm($_SERVER["REQUEST_URI"]);
            //$form->elementNamePrefix = $fb_tmp->elementNamePrefix;
            $fb[$i] = & $fb_tmp;
            $i++;
        }

        //$form->addElement('submit', 'op', 'Go!');

        $nform = count($fb);


        if (is_object($form) && $form->validate()) {
            $vals = $form->getSubmitValues();
            //var_dump($vals); die(__LINE__.__FILE__);
            //$_GET['rt'] = $this->registry->router->controller.'/'.$vals['action'].'/'.implode('/',array_unique($vals['idList']));

            if (isset($vals['action'])) {
                if ($vals['action'] == '') {
                    echo ' nessuna azione selezionata '.$vals['action'];
                //echo '<pre>'; print_r($vals); echo '</pre>';
                } elseif (count($vals['idList']) == 0) {
                    echo ' nessuna riga selezionata ';
                } else {
                    $uri = $_SERVER['REQUEST_URI'].'&rt='.$vals['action'].'/'.implode('/', array_unique($vals['idList']));
                    $uri = str_replace('//', '/', $uri);
                    echo '<h3>'.$uri.'</h3>';
                    //ddd('qui');
                    JS_OP::redirectJs($uri);
                }
            }

            for ($i = 0; $i < $nform;$i++) {
                $form->process(array( & $fb[$i], 'processForm'), false);

                //echo '<pre>'; print_r($fb[$i]); echo '</pre>';
            }

            //echo '<pre>';print_r($form->exportValues()); echo '</pre>';
            //echo '<pre>';print_r($_POST); echo '</pre>';
            //DO_OP::updateFormGrid(array('tablename' => $this->__get('tablename') , 'dataf' => $_POST));
            $dataf=$form->exportValues();
            //ARRAY_OP::print_x($dataf);
            DO_OP::updateFormGridFB(array('tablename' => $this->__get('tablename') , 'dataf' => $dataf));
            $form->setDefaults($form->exportValues());

            $this->registry->template->showMsg('Salvato', 'success');
            //$form->freeze();
            if (isset($this->smarty->tpl_vars['obj'])) {
                $objname = $this->smarty->tpl_vars['obj']->value;
                $act = "do_".$this->smarty->tpl_vars['act']->value;
                if ($objname!='') {
                    $this->registry->router->esegui($objname, $act, $form->exportValues());
                }
            } else {
                //echo '<pre>'; print_r($this->registry->router); echo '</pre>';
                $objname = $this->registry->router->__get('controller');
                $act = "do_".$this->registry->router->__get('action');
                if ($objname!='') {
                    $this->registry->router->esegui($objname, $act, $form->exportValues());
                }
            }
        } else {
            //echo '<pre>'; print_r($form); echo '</pre>';
            //if(!empty($_POST)){
            if (is_object($form) && $form->isSubmitted()) {
                $this->showMsg('form non salvato, controllare !', 'error');
                //$form->setDefaults($form->exportValues());
            }
        }


        $renderer = new HTML_QuickForm_Renderer_Xot();
        $renderer->do = $do;
        $renderer->hiddenfields = $this->__get('hiddenfields');
        $renderer->pager = $pager;
        //if(!is_object($form)) $form=$this->__get('form');
        $form->accept($renderer);

        //echo $caption;
        //echo $renderer->toHtml();

        $outHtml = $renderer->toHtml();
        $outHtml=str_replace('a='.$_GET['a'].'&amp;rt='.$_GET['rt'].'&amp;pageID=', $_SERVER['QUERY_STRING'].'&amp;pageID=', $outHtml);
        $outHtml = str_replace('&pageID='.$pageID.'&', '&', $outHtml);
        $outHtml=str_replace('a='.$_GET['a'].'&rt='.$_GET['rt'].'&orderBy=', $_SERVER['QUERY_STRING'].'&orderBy=', $outHtml);
        if (isset($_GET['orderBy']) && isset($_GET['direction'])) {
            $outHtml = str_replace('&orderBy='.$_GET['orderBy'].'&direction='.$_GET['direction'].'&', '&', $outHtml);
        }
        $outHtml = str_replace('<td align="center" colspan="4">', '<td align="center" colspan="4">'.$this->registry->template->getAzioniTable(), $outHtml);
        $outHtml=$puls.$caption.$outHtml;
        //echo $outHtml;
        return $outHtml;
        //$outHtml = str_replace('<table', '<table class="table table-striped table-bordered table-condensed"', $outHtml);
        //-------

        //$this->smarty->assign('content', $outHtml);

        return $puls.$caption.$outHtml;
    }

    ///-------------

    //---------------------------------------------------------------------------------------------


    //------------------------------------------------------------------------------------------------------


    //-----------------------------------------------------------------------------------------------------

    //-----------------------------------------------------------------------------------------
    public function get_act($params)
    {
        return '';
    }

    //------------------------------------------------------------------------------------------------

    //-----------------------------------------------------------------------------------------------------

    public static function getTablename($str)
    {
        //"`"
        $_table_name = "" . DB_PREFIX . self::_plural(strtolower($str)) . "";
        return $_table_name;
    }
    //-----------------------------------------------------------------------------------------------------
    public function get_orderBy($params)
    {
        $orderBy = '';
        if (isset($_GET['orderBy']) && isset($_GET['direction'])) {
            $orderBy = $_GET['orderBy'].' '.$_GET['direction'];
        }
        return $orderBy;
    }
    //-----------------------------------------------------------------------------------------------------
    public function get_order_by($params)
    {
        $orderBy = '';
        if (isset($_GET['orderBy']) && isset($_GET['direction'])) {
            $orderBy = $_GET['orderBy'].' '.$_GET['direction'];
        }
        return $orderBy;
    }


    public function get_id($params)
    {
        return '';
    }

    //----------------------------------------------------------------------------------------------------
    public function get_nrecords($params)
    {
        //if (isset($_GET['nrecords'])) { $nrecords = $_GET['nrecords']; } else { $nrecords = 20; }
        //return $nrecords;
        return $this->registry->template->__get('nrecords');
    }
    //-----------------------------------------------------------------------------
    public function get_tablename($params)
    {
        if (isset($params['tablename']) && $params['tablename']!='') {
            return $params['tablename'];
        }
        $menuCurr=$this->registry->menu->__get('menuCurr');
        $tbl=$menuCurr['tablename'];
        return $tbl;
    }

    //--------------------------------------------------------------------------------
    public function alterTable($params)
    {
        extract($params);
        $mdb2=$this->registry->conf->getMDB2();
        //$mdb2->alterTable($tablename, array('add' => array('lock' => 'integer')), true);
        reset($add);
        foreach($add as $field => $type){
            $sql = 'ALTER TABLE '.$dbname.'.'.$tablename.'
		ADD COLUMN '.$field.' '.$type.';';
            $res=$mdb2->query($sql);
            echo '<pre>'.$sql.'</pre>';
        }
    }

    //---------------------------------------------------------------------------------
    public function getFields($params)
    {
        extract($params);
        $do=DB_DataObject::factory($tablename);
        if (isset($dbname) && $dbname!='') {
            $do->database($dbname);
        }
        $fields=array_keys($do->table());
        return $fields;
    }

    //---------------------------------------------------------------------------
    public function getID($params)
    {
        $id_tbl='id_'.$this->__get('tablename');
        if (!isset($params['id']) && isset($params[$id_tbl])) {
            $params['id']=$params[$id_tbl];
        }

        //ARRAY_OP::print_x($do);
        $pkey=$this->__get('primary_key');
        $id_tbl1=$pkey.'_'.$this->__get('tablename');
        if (!isset($params['id']) && isset($params[$id_tbl1])) {
            $params['id']=$params[$id_tbl1];
        }
        if (!isset($params['id']) && isset($params[$pkey])) {
            $params['id']=$params[$pkey];
        }
        if (!isset($params['id'])) {
            ARRAY_OP::print_x($params);
            ddd('qui');
        }
        return $params['id'];
    }

    //-------------------------------------------------------------------------------
    public function editTPL($params)
    {
        $params['guid']='';
        unset($params['guid']);
        //$whereAdd=$this->getWhereAdd($params);
        //ddd($whereAdd);
        //$data=DO_OP::fetchAllX(['tablename'=>$params['tablename'],'whereAdd'=>$whereAdd]);
        //ddd($data);

        //$id=$data[0]->id; /// da sistemare.. rifare buttare via..

        //ddd($id);
        $params['__new']=1;

        $do = DB_DataObject::factory($params['tablename']);
        //$do=$this->getDo($params);
        //ddd($params);
        $paramsw=$params;
        unset($paramsw['id_padre']);
        $whereAdd=$this->getWhereAdd($paramsw);
        $data=DO_OP::fetchAllX(['tablename' => $params['tablename'], 'whereAdd' => $whereAdd]);

        //$id=self::getID($paramsw);

        $id=$data[0]->id;
        $do->get($id);
        $me=$this->registry->user->__get('me');
        /*
        echo '<br/> handle lock ['.$do->handle.']';
        echo '<br/> lockx ['.$do->lockx.']';
        echo '<br/> my handle  ['.$me->handle.']';
        */
        //*
        if (isset($do->lockx)) {
            if ($do->lockx==1 && $do->handle != $me->handle.'') {
                JS_OP::swal(['title'=>'Risorsa bloccata','text'=>'Attualmente in uso da '.$do->handle,'type'=>'error']);
                return ;
            }
        }
        //***/
        $parz=[
                'id_tbl'=>$do->id
                ,'tbl' => $this->__get('tablename')
                ,'lockx'=>1
                ];

        LOG_OP::insert($parz);
        //ddd($do);
        //$do=$data[0];

        $fb = & DB_DataObject_FormBuilder::create($do);
        self::DataObjectSpecialType($do, $fb);

        $form = & $fb->getForm($_SERVER["REQUEST_URI"]);
        $form_id=$form->_attributes['id'];

        if (isset($_REQUEST['__ajaxCall']) && $_REQUEST['__ajaxCall']==1) { // da mettere solo POST !!!
            echo '<script>
		$(\'#'.$form_id.'\').submit(function(){
			var formData=$(this).serialize();
			var url3=$(this).attr(\'action\');
			//url3="'.$_SERVER['REQUEST_URI'].'";
			//alert(\'piedi di balsa222\');
    		loadIContent(url3,"#icontent",formData);
    		return false;
		});

		$(\'.submit\').click(function(){
            var form=$(\'#'.$form_id.'\');
            var url3=form.attr(\'action\');
            var name=$(this).attr(\'name\');
            var formData=form.serialize();
            formData=formData+\'&__\'+name+\'=1\';
            //console.log(formData);
            //alert(url3);
            loadIContent(url3,"#icontent",formData);

            return false;
        })
		</script>';
            //....
        //$outHtml=str_replace('id="__submit__"','id="ajaxSubmit"',$outHtml); // da mettere poi il caso che ci siano piu' di 1 ...
        }

        if ($form->isSubmitted()) {
            //echo '<h3>isSubmitted</h3>';
            if ($form->validate()) {
                	//echo '<h3>VALIDO</h3>';
            } else {
                	//echo '<h3>NON VALIDO</h3>';
        //ddd($form);
            }
        } else {
        }

        if ($form->validate()) {
            //echo '<h3>VALIDATE</h3>';
            if (isset($_POST['__submit_cancel']) && $_POST['__submit_cancel']==1) {
                $parz=[
                'id_tbl'=>$do->id
                ,'tbl' => $this->__get('tablename')
                ,'data' => serialize($form->exportValues())
                ,'lockx'=>0
                ];
                LOG_OP::insert($parz['data']);
                JS_OP::swal(['title'=>'Risorsa liberata','text'=>'Tutto quello che hai fatto NON � stato salvato','type'=>'success']);
                //echo '<h3>Liberata risorsa</h3>';
                return ;
            }
            echo $form->process(array( & $fb, 'processForm'), false);
            echo $this->registry->template->showMsg('Aggiornato', 'success');
            /*
            $parz = array('id_tbl' => $do->id
                , 'tblname' => $this->__get('tablename')
                ,'data' => serialize($form->exportValues())
                ,'obj' => ''
                ,'act'=>'');
            LOG_OP::mylog($parz);
            */
            if (isset($_POST['__submit_exit']) && $_POST['__submit_exit']==1) {
                $parz=[
                'id_tbl'=>$do->id
                ,'tbl' => $this->__get('tablename')
                ,'data' => serialize($form->exportValues())
                ,'lockx'=>0
                ];

                if (LOG_OP::insert($parz)) {
                    JS_OP::swal(['title'=>'Salvato correttamente','text'=>'Tutto quello che hai fatto è stato salvato e la risorsa liberata','type'=>'success']);
                } else {
                    JS_OP::swal(['title'=>'Oops...','text'=>'Qualcosa è andato storto','type'=>'error']);
                }

                //echo '<h3>Liberata risorsa</h3>';
                return ;
            }

            $parz=[
                'id_tbl'=>$do->id
                ,'tbl' => $this->__get('tablename')
                ,'data' => serialize($form->exportValues())
                ,'lockx'=>0
                ];

            LOG_OP::insert($parz);
            JS_OP::swal(['title'=>'Salvato correttamente','text'=>'Tutto quello che hai fatto � stato salvato e la risorsa � ancora bloccata','type'=>'success']);
        }

        $params['form']=$form;
        $this->registry->template->setFormData($params);
        $out='';

        $tmp = debug_backtrace();
        $class=$tmp[2]['class'];
        //ddd($class);
        $controller=str_replace('Controller', '', $class);
        //$action=$tmp[2]['function'];
        $tplpath=$this->registry->template->getTplPath($controller);

        //$out=$this->registry->template->show(FILE_OP::fixpath($tplpath.'/'.$params['tpl']));

        $out=$this->registry->template->show($controller.'/'.$params['tpl']);
        return $out;
    }

    //------------------------------------------------------------------------------------
    public function newTPL($params)
    {
        //ddd($params);
        $params['guid']='';
        unset($params['guid']);
        //$whereAdd=$this->getWhereAdd($params);
        //ddd($whereAdd);
        //$data=DO_OP::fetchAllX(['tablename'=>$params['tablename'],'whereAdd'=>$whereAdd]);
        //ddd($data);

        //$id=$data[0]->id; /// da sisteare.. rifare buttare via..
        //extract($paras);
        //ddd($tablename);ddd('qui');
        //ddd($id);
        $params['__new']=1;
        $do = DB_DataObject::factory($params['tablename']);
        //$do=$this->getDo($params);

        //ddd($params);
        $paramsw=$params;
        unset($paramsw['id_padre']);
        $whereAdd=$this->getWhereAdd($paramsw);
        $data=DO_OP::fetchAllX(['tablename' => $params['tablename'], 'whereAdd' => $whereAdd]);

        //$id=self::getID($paramsw);
        $id=$data[0]->id;
        //ddd($data);
        //$do->get($id);
        $me=$this->registry->user->__get('me');
        /*
        echo '<br/> handle lock ['.$do->handle.']';
        echo '<br/> lockx ['.$do->lockx.']';
        echo '<br/> my handle  ['.$me->handle.']';
        */
        //*

        if (isset($do->lockx)) {
            if ($do->lockx==1 && $do->handle != $me->handle.'') {
                JS_OP::swal(['title'=>'Risorsa bloccata','text'=>'Attualmente in uso da '.$do->handle,'type'=>'error']);
                return ;
            }
        }
        //***/
        $parz=[
                'id_tbl'=>$do->id
                ,'tbl' => $this->__get('tablename')
                ,'lockx'=>1
                ];

        LOG_OP::insert($parz);
        //ddd($do);
        //$do=$data[0];

        $fb = & DB_DataObject_FormBuilder::create($do);
        self::DataObjectSpecialType($do, $fb);

        $form = & $fb->getForm($_SERVER["REQUEST_URI"]);

        $links=$this->getLinksIni2Array(['do'=>$do]);
        //ddd($links);
        //if(isset($links[$params['tablename']])){
        $def=array();
        $links1=$links[$params['tablename']];
        reset($links);
        foreach($links as $k => $v){
            if ($v->tbl==$params['tablename']) {
                $v1=$v->linked;
                reset($v1);
                foreach($v1 as $k2 => $v2){
                    $k3=$v2->linked_table.'.'.$v2->linked_field;
                    if (isset($params[$k3])) {
                        $def[$v2->field]=$params[$k3];
                    }
                }
            }
        }
        //ddd($def);
        $def=array_merge($params, $def);
        $form->setDefaults($def);
        //}

        $form_id=$form->_attributes['id'];
        echo "<h3>".$form_id."</h3>";

        if (isset($_REQUEST['__ajaxCall']) && $_REQUEST['__ajaxCall']==1) { // da mettere solo POST !!!
            echo '<script>
		$(\'.submit\').click(function(){
			//tinymce.triggerSave();
            var form=$(\'#'.$form_id.'\');
            var url3=form.attr(\'action\');
            var name=$(this).attr(\'name\');
            var formData=form.serialize();
            formData=formData+\'&__\'+name+\'=1\';
            console.log(formData);
            //alert(url3);

            loadIContent(url3,"#icontent",formData);

            return false;
        })


		</script>';
            //....
        //$outHtml=str_replace('id="__submit__"','id="ajaxSubmit"',$outHtml); // da mettere poi il caso che ci siano piu' di 1 ...
        }

        if ($form->isSubmitted()) {
            //echo '<h3>isSubmitted</h3>';
            if ($form->validate()) {
                	//echo '<h3>VALIDO</h3>';
            } else {
                	//echo '<h3>NON VALIDO</h3>';
        //ddd($form);
            }
        } else {
        }

        if ($form->validate()) {
            $data = serialize($form->exportValues());

            //echo '<h3>VALIDATE</h3>';
            if (isset($_POST['__submit_cancel']) && $_POST['__submit_cancel']==1) {
                $parz=[
                'id_tbl'=>$do->id
                ,'tbl' => $this->__get('tablename')
                //,'data' => serialize($form->exportValues())
                ,'lockx'=>0
                ];
                LOG_OP::insert($parz);
                JS_OP::swal(['title'=>'Risorsa liberata','text'=>'Tutto quello che hai fatto NON è stato salvato','type'=>'success']);
                //echo '<h3>Liberata risorsa</h3>';
                return ;
            }
            echo $form->process(array( & $fb, 'processForm'), false);
            echo $this->registry->template->showMsg('Creato', 'success');
            /*
            $parz = array('id_tbl' => $do->id
                , 'tblname' => $this->__get('tablename')
                ,'data' => serialize($form->exportValues())
                ,'obj' => ''
                ,'act'=>'');
            LOG_OP::mylog($parz);
            */
            if (isset($_POST['__submit_exit']) && $_POST['__submit_exit']==1) {
                $parz=[
                'id_tbl'=>$do->id
                ,'tbl' => $this->__get('tablename')
                ,'data' => $data
                ,'lockx'=>0
            ];
                LOG_OP::insert($parz);
                JS_OP::swal(['title'=>'Salvato correttamente','text'=>'Tutto quello che hai fatto è stato salvato e la risorsa liberata','type'=>'success']);
                //echo '<h3>Liberata risorsa</h3>';

                return ;
            }
            $parz=[
                'id_tbl'=>$do->id
                ,'tbl' => $this->__get('tablename')
                ,'data' => $data
                ,'lockx'=>0
                ];
            LOG_OP::insert($parz);
            JS_OP::swal(['title'=>'Salvato correttamente','text'=>'Tutto quello che hai fatto è stato salvato e la risorsa è ancora bloccata','type'=>'success']);

            $objname = $this->__get('obj');
            $act = 'do_'.$this->__get('act');

            if ($objname != '' && $act != 'do_') {
                $this->registry->router->esegui($objname, $act, $do->toArray());
            }
        }

        $params['form']=$form;
        $this->registry->template->setFormData($params);
        $out='';

        $tmp = debug_backtrace();
        $class=$tmp[2]['class'];
        //ddd($class);
        $controller=str_replace('Controller', '', $class);
        //$action=$tmp[2]['function'];
        $tplpath=$this->registry->template->getTplPath($controller);

        //$out=$this->registry->template->show(FILE_OP::fixpath($tplpath.'/'.$params['tpl']));

        $out=$this->registry->template->show($controller.'/'.$params['tpl']);
        return $out;
    }

    //------------------------------------------------------------------------------------
    public function clona($params)
    {
        $do=$this->getDo($params);
        $id=self::getID($params);
        $do->get($id);
        $id_new=$do->insert();
        //$do_new=DB_DataObject::factory($tbl);
        //$do_new=clone($do);
        $do_new=$do;

        $do_new->get($id_new);
        $do_new->datemod=date('Y-m-d H:i:s', mktime());
        //$do_new->datemod='now()';
        $do_new->last_stato=1;
        $do_new->update();

        //echo '<h3>'.$id_new.'</h3>';ddd('qui');


        $objname = $this->__get('obj');
        $act = 'do_'.$this->__get('act');
        //$objname = $this->registry->router->__get('controller');
        //$act = "do_".$this->registry->router->__get('action');
        //echo '<h3>'.$objname.'-&gt;'.$act.'</h3>';


        if ($objname != "") {
            //echo '<h3>'.$objname.'-&gt;'.$act.'</h3>';

            $this->registry->router->esegui($objname, $act, $do->toArray());
        }

        //$uri_back = $this->registry->menu->__get('uri_back');
        //echo '<h3>'.$uri_back.'</h3>';
        //JS_OP::redirectJs($par->url);
        JS_OP::backJs($params);


        //ddd($id);
    /*
    $this->setVars($params);
    $tbl=$this->__get('tablename');
    extract($params);
    if(isset($params['id_'.$tbl])){
        $id=$params['id_'.$tbl];
    }

    //ARRAY_OP::print_x($params);ddd('qui');
    $do = DB_DataObject::factory($tbl);
    if(!isset($id)){
        ARRAY_OP::print_x($params);
        ddd('qui');
    }
    //$this->do = &$do;

    */
    }

    //---------------------------------------------------------------------------------
    public function edit($params)
    {

    //ddd($params);

        //echo $this->registry->conf->dsn;
        //ARRAY_OP::print_x($params) ;
        //$this->setVars($params);
        //echo '<h3>POST</h3><pre>';print_r($_POST);echo '</pre>';
        //echo '<script>alert("script loaded")</script>';

        $do=$this->getDo($params);
        //ddd($do);
        $do->fb_submitId='azz';
        //ddd('qui');
        //ddd('qui');
        //ddd($do);
        //ddd('qui');
        $id=self::getID($params);
        //echo '<h3>ID:'.$id.'</h3>';
        //ddd($id);

        if (is_array($id)) {
            //echo '<h3>id non trovato</h3>';
            //ddd($params);
            $params['tablename']=$this->__get('tablename');
            $this->__set('tbl', $params['tablename']);
            //echo '<h3>'.$tablename.'</h3>';
            //$whereAdd=DO_OP::getWhereAdd($params);
            //ddd($whereAdd);
            $data=$this->get($params);
            //ddd($data);
            $id=$data[0]->id;
        }

        $do->get($id);
        //ddd($do);

        /*
        $id_tbl='id_'.$this->__get('tablename');
        if(!isset($params['id']) && isset($params[$id_tbl])){
            $params['id']=$params[$id_tbl];
        }

        //ARRAY_OP::print_x($do);
        $pkey=$this->__get('primary_key');
        $id_tbl1=$pkey.'_'.$this->__get('tablename');
        if(!isset($params['id']) && isset($params[$id_tbl1])){
            $params['id']=$params[$id_tbl1];
        }
        if(!isset($params['id']) && isset($params[$pkey])){
            $params['id']=$params[$pkey];
        }

        if(!isset($params['id'])){
            ARRAY_OP::print_x($params);ddd('qui');
        }
        extract($params);
        $do->get($id);
        //ARRAY_OP::print_x($do);
        //ARRAY_OP::print_x($do);
        */
        //ddd($do);
        //echo '<h3>QUI</h3>';

        $fb = & DB_DataObject_FormBuilder::create($do);
        self::DataObjectSpecialType($do, $fb);

        if (count($this->__get('hiddenfields')) > 0) {
            self::DataObjectCustomFields($do, $fb, $this->__get('hiddenfields'), 'hidden');
        }

        //ddd($do);

        $fb->elementTypeMap['date'] = 'jquery_date';
        $fb->elementTypeMap['datetime'] = 'jquery_datetime';
        //$fb->elementTypeMap['date'] = 'html5_date';
        //$fb->elementTypeMap['datetime'] = 'html5_datetime';
        $fb->submitText = 'Aggiorna ';
        //echo $_SERVER["REQUEST_URI"];
        //echo '<pre>';print_r($fb);echo '</pre>';

        $form = & $fb->getForm($_SERVER["REQUEST_URI"]);
        $form_id=$form->_attributes['id'];

        if (isset($_REQUEST['__ajaxCall']) && $_REQUEST['__ajaxCall']==1) { // da mettere solo POST !!!
            echo '<script>
		$(\'#'.$form_id.'\').submit(function(){
			var formData=$(this).serialize();
			var url3=$(this).attr(\'action\');
			//url3="'.$_SERVER['REQUEST_URI'].'";
			//alert(\'piedi di balsa222\');
    		loadIContent(url3,"#icontent",formData);
    		return false;
		});

		</script>';
            //....
        //$outHtml=str_replace('id="__submit__"','id="ajaxSubmit"',$outHtml); // da mettere poi il caso che ci siano piu' di 1 ...
        }

        //$form->display(); return;

        //$form = & $fb->getForm();
        //ddd('qui');
        //ddd($do);
        //http://pear.reversefold.com/dokuwiki/pear:db_dataobject_formbuilder:formbuilder_configuration_options

        if ($form->validate()) {
            $form->process(array( & $fb, 'processForm'), true);
            if ($id != null) {
                $primary_key = $this->primary_key;
                if ($primary_key == "") {
                    $keys = $do->keys();
                    $primary_key = $keys[0];
                }

                $this->registry->template->showMsg('!!! Aggiornato con Successo '.$primary_key.' '.$do->$primary_key, 'success');
                //ddd('qui');
                LOG_OP::mylog($do->$primary_key, $this->__get('tablename'), 'editTablename');
            //ddd('qui');
            } else {
                $msg = 'Inserito con Successo id <b>'.$do->id.'</b><br/>Compila sotto per aggiungerne un altro NUOVO';
                $actionMenu = '<ol>';
                LOG_OP::mylog($do->id, $this->__get('tablename'), 'editTablename');
                /*
                $azioniFromMenuFratelli=$this->__get('azioniFromMenuFratelli');
                reset($azioniFromMenuFratelli);
                foreach ($azioniFromMenuFratelli as $k => $v){
                    $actionMenu .= '<li><a href="?rt=menu/gest/'.$v->id.'/'.$do->id.'" title="'.$v->nome.'">';
                    $actionMenu.= '<img src="'.$this->__get('template_url').'/resources/images/icons/'.$v->img.'"/>';
                    $actionMenu.= $v->nome.'</a> </li>';
                }
                */
                $actionMenu .= '</ol>';
                //$this->smarty->assign('txt', $actionMenu);
                $this->registry->template->showMsg($msg.$actionMenu, 'success');
            }
            //$form->addElement('submit', null, $fb->submitText); // ????
            //$outHtml = $form->toHtml();
            $outHtml = $this->getFormStyled(array('form'=>$form));
            //$outHtml = str_replace('type="submit"', 'type="submit" class="btn btn-primary" onclick="this.disabled=true; this.value=\'Attendere…\';"', $outHtml);
            //$this->smarty->assign('content', $outHtml);


            //$objname = $this->smarty->tpl_vars['obj']->value;
            //$act = "do_".$this->smarty->tpl_vars['act']->value;

            $objname = $this->__get('obj');
            $act = 'do_'.$this->__get('act');
            //$objname = $this->registry->router->__get('controller');
            //$act = "do_".$this->registry->router->__get('action');
            //echo '<h3>'.$objname.'-&gt;'.$act.'</h3>';


            if ($objname != "") {
                //echo '<h3>'.$objname.'-&gt;'.$act.'</h3
                $this->registry->router->esegui($objname, $act, $do->toArray());
            }


            //return $do;
            return $outHtml;
        	//$this->refresh();
    	//return $do;
        } else {
            $err=implode('<br/>', $form->_errors);
            if ($err!='') {
                $this->registry->template->showMsg($err, 'error');
            }
        }

        	//$form->addElement('submit', null, $fb->submitText);  // ????
        /* -- magari creaRE UN RENDERER PER BOOTSTRAP
        require_once 'HTML/QuickForm/Renderer/Tableless.php';
        $renderer =& new HTML_QuickForm_Renderer_Tableless();
        // usual code, e.g. new form fields, rules, ...
        $form->accept($renderer);


        $outHtml = $renderer->toHtml();
        $outHtml = str_replace('<li>','<div class="form-group">',$outHtml);
        $outHtml = str_replace('</li>','</div>',$outHtml);
        */
        /*
        $this->rendererBootstrap=$this->getRendererBootstrap();
        $form->accept($this->rendererBootstrap);
        $outHtml = $this->rendererBootstrap->toHtml();
        */

        $outHtml = $this->getFormStyled(array('form'=>$form));

        //$outHtml = $form->toHtml();
        	//$outHtml = str_replace('type="submit"', 'type="submit" class="button"', $outHtml);
        //$outHtml=str_replace('type="s")
        //$outHtml = str_replace('type="submit"', 'type="submit" class="btn btn-primary" onclick="this.disabled=true; this.value=\'Attendere…\';"', $outHtml);
        //"class"=>"btn btn-large btn-primary","onClick"=>"this.disabled=true; this.value='Attendere…';")



        //die($outHtml);
        //$this->smarty->assign('content', $outHtml);
        //echo $outHtml;
        //ddd('qui');


        //echo 'oo';
        //http://parafiablackburn.eu/scripts/rvslib/Pear/HTML/quickform-usage.html

        return $outHtml;
    }


    public function DataObjectSpecialType(& $obj, $fb)
    {
        //self::initOptions(array());
        $tablename = $obj->__table;
        if (!isset($obj->fb_fieldLabels)) {
            $obj->fb_fieldLabels=array();
        }
        $labels=$obj->fb_fieldLabels;

        $do = DB_DataObject::factory('_specialtypetable');
        if (PEAR::isError($do)) {
            return;
            echo '<br/><b>tablename: </b>[_specialtypetable]<br/>
		<br/> <b>LINE: </b>'.__LINE__.'<br/>
		<b> FILE: </b>'.__FILE__.'<br/>
		<b> MSG: </b><pre>'.$do->getMessage() . '</pre><br />
		<b> Info : </b> ' . $do->getDebugInfo().'';
            ddd('');
        }

        $dbname=$this->__get('dbname');

        //ddd($dbname);
        //$do->_database=$dbname;//'trasferte_dip';
        if ($dbname!='') {
            $do->database($dbname); // di _specialtypetable ce ne e' uno per database.. e noi controlliamo quello in essere
        }
        //ddd($do);
        //ddd($dbname);ddd('qui');
        //ddd('qui');
        $dsn=$this->registry->conf->__get('dsn');
        //echo $dsn;
        //ddd('qui');
        /*
        $dsn0 = $do->getDatabaseConnection();
        $dsn = $dsn0->dsn['phptype'].'://'.$dsn0->dsn['username'].':'.$dsn0->dsn['password'].'@'.$dsn0->dsn['hostspec'].'/'.$dsn0->database_name;
        */
        //echo $dsn;

        //$do->tablename = $tablename;
        //$do->find();
        //echo '<pre>'; print_r($do); echo '</pre>';
        $table_fields=$obj->table();

        //echo '<br/>['.__LINE__.']';

        $data = $do->fetchAll();
        //echo '<br/>['.__LINE__.']';


        //$data=self::fetchAllX(array('tablename'=>'_specialtypetable'));
        //ddd($data);ddd('qui');

        $ris = array();
        foreach ($data as $k => $v) {
            //echo '<br/>-['.__LINE__.']['.__FILE__.']'. $tablename.'  '.$v->fieldname.'   '.$v->fieldtype;
            $tag = $v->fieldname;
            $options = array();
            $attributes= array();
            if (strlen(trim($v->fieldoptions)) > 5) {
                $options = unserialize($v->fieldoptions);
            }
            if (strlen(trim($v->fieldattributes)) > 5) {
                $attributes = unserialize($v->fieldattributes);
            }
            if (!isset($labels[$tag])) {
                $labels[$tag] = '['.$tag.']';
            }
            	//echo '<br/>['.__LINE__.']';
            //*
            foreach($options as $ko => $vo){
                $pattern='/##([a-z_]*)##/';
                preg_match($pattern, $vo, $matches);
                if (isset($matches[1])) {
                    $tmp_m = $matches[1];
                    //echo '<h3>'.$tmp_m.'</h3>';
                    if (isset($obj->$tmp_m)) {
                        $options[$ko]=$obj->$tmp_m;
                    } else {
                        //ddd($tmp_m);
                    //ddd($obj);
                    }
                }
            }
            //*/
            	//echo '<br/>['.__LINE__.']';
            	//echo '<li>['.$v->fieldtype.']['.$fb->elementNamePrefix.$tag.']</li>'.chr(13);
            ///*
            /*
            echo '<br/>$v->fieldtype :'.$v->fieldtype;
            echo '<br/>$v->tag :'.$tag;
            echo '<br/>$labels :'.$labels[$tag];
            */
            //ddd($obj->table());
            $fieldName = $fb->getFieldName($tag);
            $fieldLabel = $fb->getFieldLabel($tag);

            if (in_array($tag, array_keys($table_fields))) {
                $el = HTML_QuickForm::createElement($v->fieldtype, $fieldName, $fieldLabel, $options, $attributes);
                //echo '<pre>'; print_r($attributes); echo '</pre>';
                if (isset($attributes['query'])) {
                    $el->loadQuery($dsn, $attributes['query']);
                }
                $obj->fb_preDefElements[$tag] = $el;
            }
            //*/
        }
        //echo '<br/>['.__LINE__.']';
    }
    ///-------------

    public function nuovo($params)
    {
        //echo '<br/>[newTablename]['.__LINE__.']['.__FILE__.']';
        //ARRAY_OP::print_x($this->registry->conf->__get('dbname'));ddd('qui');
        //$bread=$this->registry->menu->getBreadCrumb($params);
        //ARRAY_OP::print_x($bread);

        //ddd($params);ddd('qui');

        $params['__new']=1;

        //$this->setVars($params);
        $do=$this->getDo($params);
        //ARRAY_OP::print_x('a');

        $fb = & DB_DataObject_FormBuilder::create($do);

        $fb->submitText = 'Salva '.$this->__get('tablename');
        //*
        self::DataObjectSpecialType($do, $fb);
        $hiddenfields=array_unique($this->__get('hiddenfields'));
        //ddd($hiddenfields);
        $this->__set('hiddenfields', $hiddenfields);

        if (count($this->__get('hiddenfields')) > 0) {
            self::DataObjectCustomFields($do, $fb, $this->__get('hiddenfields'), 'hidden');
        }
        //*/
        //echo '<br/>['.__LINE__.']['.__FILE__.']';

        $fb->elementTypeMap['date'] = 'jquery_date';
        $fb->elementTypeMap['datetime'] = 'jquery_datetime';
        //$fb->elementTypeMap['date'] = 'html5_date';
        //$fb->elementTypeMap['datetime'] = 'html5_datetime';

        $form = & $fb->getForm($_SERVER["REQUEST_URI"].'&rnd='.rand(1, 100));

        $params['id']='';
        unset($params['id']);

        $form->setDefaults($params);

        $primary_key = $this->primary_key;
        if ($primary_key == "") {
            //echo '<pre>'; print_r($do->keys()); echo '</pre>';
            $keys = $do->keys();
            $primary_key = $keys[0];
        }
        //echo '<br/>['.__LINE__.']['.__FILE__.']';
        if ($form->validate()) {
            //ddd('qui');
            //echo '<pre>'; print_r($this->smarty->tpl_vars['obj']->value); echo '</pre>';
            //echo '<pre>'; print_r($this->smarty->tpl_vars['act']->value); echo '</pre>';
            //ddd('qui');
            //ddd('qui');
            //$this->registry->conf->mdb2->setDatabase('events_db');
            //echo '<br/>['.__LINE__.']['.__FILE__.']';
            echo $form->process(array( & $fb, 'processForm'), false);

            $msg = 'Inserito con Successo '.$do->database().'.'.$do->__table.' '.$primary_key.' <b>'.$do->$primary_key.'</b><br/>Compila sotto per aggiungerne un altro NUOVO';
            $actionMenu = '<ol>';
            //echo '<br/>['.__LINE__.']['.__FILE__.']';
            LOG_OP::mylog($do->$primary_key, $this->__get('tablename'), 'newTablename');
            //echo '<br/>['.__LINE__.']['.__FILE__.']';


            //$this->smarty->assign('txt', $actionMenu);
            //$this->registry->template->showMsg($msg.$actionMenu, 'success');
            $this->primary_key=$primary_key;
            $parz=array();
            $parz['azioni']=$this->registry->menu->__get('menuFratelli');
            $parz['record']=get_object_vars($do);
            $act=$this->registry->template->actionColumnCallbackMini($parz);

            //$this->smarty->assign('txt', $actionMenu);
            //$this->registry->template->showMsg($msg.$actionMenu, 'success');
            $this->registry->template->showMsg($msg.'<br/>'.$act, 'success');
            //$outHtml=$form->toHtml();
            $outHtml = $this->getFormStyled(array('form'=>$form));

            //$this->smarty->assign('content', $outHtml);

            	//$objname = $this->smarty->tpl_vars['obj']->value;
            	//$act = "do_".$this->smarty->tpl_vars['act']->value;
            $objname = $this->__get('obj');//smarty->tpl_vars['obj']->value;
            $act = "do_".$this->__get('act');//]->value;

            if ($objname != "") {
                $this->registry->router->esegui($objname, $act, $do->toArray());
            }
            //echo '<br/>['.__LINE__.']['.__FILE__.']';
            //return $do;
            return $outHtml;

        	//$this->refresh();
        	//return $do;
        } else {
            //echo '<pre>';print_r($form);echo '</pre>';
            $err=implode('<br/>', $form->_errors);
            if ($err!='') {
                $this->registry->template->showMsg($err, 'error');
            }
        }

        /*
        require_once __MVC_DIR.'/includes/extPear/HTML_QuickForm_Renderer_Bootstrap.php';
        $renderer =& new HTML_QuickForm_Renderer_Bootstrap();
        //$form->setAttributes('class="form-horizontal" role="form"');
        $form->accept($renderer);
        $outHtml= $renderer->toHtml();
        //*/
        $outHtml = $this->getFormStyled(array('form'=>$form));
        //$outHtml = $form->toHtml();

        //$outHtml = str_replace('type="submit"', 'type="submit" class="btn btn-primary"', $outHtml);
        //die($outHtml);
        //echo '<br/>['.__LINE__.']['.__FILE__.']';
        //$this->smarty->assign('content', $outHtml);
        //echo '<br/>['.__LINE__.']['.__FILE__.']';
        return $outHtml;
    }
    //--------------------------------------------------------------------------------------------



    //---------------------------------------------------------------------------------------------
    public function cancella($params)
    {
        $do=$this->getDoSimple($params);
        $id=self::getID($params);
        $tablename=$do->__table;

        if (is_array($id)) {
            $msg='Sicuro di voler eliminare per sempre ['.$tablename.']['.implode(',', $id).']';
        } else {
            $this->registry->template->__set('hideControlPanel', true);
            $msg='Sicuro di voler eliminare per sempre ['.$tablename.']['.$id.']';
        }
        echo '<h3>'.$msg.'</h3>';

        if (!is_array($id)) {
            $idz=array($id);
        } else {
            $idz=$id;
        }
        reset($idz);
        //*
        forach($idz as $k => $id_delete){
            $do=$this->getDoSimple($params);
            $do->get($id_delete);
            $do->delete();
        }
        if (is_array($id)) {
            JS_OP::backJs();
        }
        //*/
    }

    public function getDoSimple($params)
    {
        $tablename=$this->__get('tablename');
        $do = DB_DataObject::factory($tablename);
        if (PEAR::isError($do)) {
            ddd($do);
            return ;
        }
        $dbname=$this->__get('dbname');
        //ddd($dbname);ddd('qui');
    if ($dbname!='generale' && $dbname!='') { //da mettere anche user,,
        $do->database($dbname);
    }

        return $do;
    }


    //---------------------------------------------------------------------------------------------
    public function getDo($params)
    {

    //ARRAY_OP::print_x($params);
        if (!isset($params['__new'])) {
            $params['__new']=0;
        }


        reset($params);
        foreach($params as $k=>$v){
            $this->__set($k, $v);
        }

        $menuCurr=$this->registry->menu->__get('menuCurr');
        reset($menuCurr);
        foreach($menuCurr as $k => $v){
            if (!$this->__isset($k)) {
                $this->__set($k, $v);
            }
        }

        $tbl=$this->__get('tablename');
        if ($tbl=='') {
            $menuCurr=$this->registry->menu->__get('menuCurr');
            reset($menuCurr);
            foreach($menuCurr as $k => $v){
                $this->__set($k, $v);
            }
            $tbl=$this->__get('tablename');
            //ddd($tbl);
        //ddd('qui');
        }

        $do = DB_DataObject::factory($tbl);

        if (PEAR::isError($do)) {
            ddd($do);
            return ;
        }
        //$areaCurr=$this->registry->user->__get('areaCurr');
        //ddd($areaCurr);ddd('qui');
        $dbname=$this->registry->conf->__get('dbname');
        //ddd($dbname);ddd('qui');
    if ($dbname!='generale' && $dbname!='') { //da mettere anche user,,
        $do->database($dbname);
    }


        //ddd($do);
        if (!self::db_exists($do->_database)) {
            echo '<h3>NON ESISTE DB['.$do->_database.']</h3>';
            ddd('qui');
        }

        //ddd('qui');//ddd('qui');
        if (PEAR::isError($do)) {
            echo '<br/><b>tablename: </b>['.$this->__get('tablename').']<br/>
		<br/> <b>LINE: </b>'.__LINE__.'<br/>
		<b> FILE: </b>'.__FILE__.'<br/>
		<b> MSG: </b><pre>'.$do->getMessage() . '</pre><br />
		<b> Info : </b> ' . $do->getDebugInfo().'';
            ddd('');
        }
        //ddd($do);
        //ddd('qui');
        $keys = $do->keys();
        //ddd('qui');
        $this->primary_key = $keys[0];
        //$whereAdd=$this->getWhereAdd($params);
        //$whereAdd=DO_OP::getWhereAdd($params);
        //ddd($whereAdd);

        $this->__set('primary_key', $keys[0]);
        //echo '['.$this->__get('primary_key').']';
        //$do->getMyDateField("%d/%M/%Y %H:%i");

        $fieldstorender=$this->__get('fieldstorender');
        //ddd($fieldstorender);
        if (!is_array($fieldstorender)) {
            $fieldstorender=explode(',', $fieldstorender);
            $this->__set('fieldstorender', $fieldstorender);
        }
        //ddd($fieldstorender);
        //$fieldstorender[]='__crossLink_cross_indennita_rischi';
        //$this->__set('fieldstorender',$fieldstorender);
        	//ddd('qui');

        if (count($fieldstorender)>1) {
        } else {
            $this->__set('fieldstorender', array_keys($do->table())) ;
            $do->fb_fieldsToRender = $this->__get('fieldstorender');
        }
        $do->fb_fieldsToRender = $this->__get('fieldstorender');

        //ddd($do->fb_fieldsToRender);
        //return $do;
        //ddd('qui');

        //$this->do=$do;
        //$fieldlabels=$this->getFieldLabels($params);
        //ddd($fieldlabels);
        //$this->__set('fieldlabels',$fieldlabels);

        $defaults=$this->__get('defaults');

        reset($defaults);
        foreach($defaults as $kd => $vd){
            $do->$kd=$vd;
        }

        //ddd($this->__get('query'));
        if (isset($this->__get('query')[$this->__get('tablename')])) {
            $sql=$this->__get('query')[$this->__get('tablename')];
            //$sql=$this->fetch($sql);
            //echo '<h3>whereadd['.$sql.']</h3>';
            //ddd($sql);
            $do->whereAdd($sql);
        }
        $this->htmlfilterdo($do);
        if ($this->filter_sql != '') {
            $do->whereAdd($this->filter_sql);
        }
        //---------------------------------------------------------
        //$querystr=$this->registry->router->action_params_obj;
        $querystr=$this->registry->router->__get('act_pars');
        	//ddd('qui');

        if (!is_array($querystr)) {
            $querystr=array();
        }
        if (is_array($params)) {
            $querystr=array_merge($querystr, $params);
        }
        if ($params['__new']) {
            $querystr['id']='';
            unset($querystr['id']);
        }

        $filter_keys_from_url=array_intersect(array_keys($querystr), array_keys($do->table()));
        //ARRAY_OP::print_x($querystr);
        	//ARRAY_OP::print_x($do->table());
        //ARRAY_OP::print_x($filter_keys_from_url);
        //ddd('qui');
        ///echo '<pre>';print_r($a); echo '</pre>';
        //ARRAY_OP::print_x(array_keys($querystr));

        //ARRAY_OP::print_x($filter_keys_from_url);
        $hiddenfields=$this->__get('hiddenfields');
        foreach($filter_keys_from_url as $k => $v){ // da aggiungere che non viene renderizzato (fieldstorender)
            if ($querystr[$v]!="") {
                if (is_array($querystr[$v])) {
                    //ARRAY_OP::print_x($querystr);
                    $v1='';
                    if (isset($querystr[$v][0])) {
                        $v1=$querystr[$v][0];
                        echo '<h3>elaboro solo il primo risultato ['.$v.' = '.$v1.']</h3>';
                    }
                } else {
                    $v1=$querystr[$v];
                }
                $sql=$v.'="'.$v1.'"';
                //ARRAY_OP::print_x($sql);//ddd('qui');
                //echo '<h3>'.debug_backtrace()[1]['function'].'</h3>';
                if (debug_backtrace()[1]['function']!='edit') {
                    $do->whereAdd($sql);
                }
                if ($params['__new']) {
                    $hiddenfields[]=$v;
                }
            }
        }
        $this->__set('hiddenfields', $hiddenfields);
        //ARRAY_OP::print_x($filter_keys_from_url);
        //ARRAY_OP::print_x($hiddenfields);
        if (!$params['__new']) {
            ///ddd($do->fb_fieldsToRender );
            $this->__set('fieldstorender', array_diff($do->fb_fieldsToRender, $filter_keys_from_url));
            $do->fb_fieldsToRender=$this->__get('fieldstorender');
        //ddd($this->__get('fieldstorender') );
        } else {
            $fieldstorender=array_merge($do->fb_fieldsToRender, $hiddenfields);
            $fieldstorender=array_unique($fieldstorender);
            $this->__set('fieldstorender', $fieldstorender);
            $do->fb_fieldsToRender=$this->__get('fieldstorender');
        }
        //ddd($hiddenfields);
        //ddd($do->fb_fieldsToRender);
        if (isset($this->__get('order_by')[$this->__get('tablename')])) {
            $do->orderBy($this->__get('order_by')[$this->__get('tablename')]);
        }
        //echo '<pre>'; print_r($this->__get('order_by'); echo '</pre>';
        //$this->__set('do',$do);

        //ddd($do);
        $this->__set('do', $do);
        $fieldlabels=$this->getFieldLabels($params);
        $this->__set('fieldlabels', $fieldlabels);
        //ddd($fieldlabels);
        return $do;
    }

    //-----------------------------------------------------------------------------
    public function getFieldLabels($params)
    {
        //ddd('qui');
        $do=$this->__get('do');
        $tmp=array();
        $fieldstorender=$this->__get('fieldstorender');
        //ddd($this->__get('fieldstorender') );
        reset($fieldstorender);
        $hiddenfields=$this->__get('hiddenfields');
        //ddd($hiddenfields);
        //ddd($do);
        //ddd($do->fb_fieldLabels);

        foreach($fieldstorender as $k => $v){
            if (!isset($this->fieldlabels[$v])) {
                if (isset($do->fb_fieldLabels[$v])) {
                    $tmp[$v]=$do->fb_fieldLabels[$v];
                } else {
                    $tmp[$v]=str_replace('_', ' ', $v);
                }
            } else {
                $tmp[$v]=$this->fieldlabels[$v];
            }
        }
        //echo '<pre>';print_r($tmp); echo '</pre>';
        //echo '<pre>';print_r($this->fieldlabels); echo '</pre>';
        return $tmp;
    }

    //------------------------------------------------------------------------------------------------------
    public function htmlfilterdo($do)
    {
        /*
            print_r($do->table());
             'id'     =>  1  // == DB_DATAOBJECT_INT
        //     'name'   =>  2  // == DB_DATAOBJECT_STR
        //     'bday'   =>  6  // == DB_DATAOBJECT_STR + DB_DATAOBJECT_DATE
        //     'last'   =>  14 // == DB_DATAOBJECT_STR + DB_DATAOBJECT_DATE + DB_DATAOBJECT_TIME
        //     'active' =>  17 // == DB_DATAOBJECT_INT + DB_DATAOBJECT_BOOL
        //     'desc'   =>  34 // == DB_DATAOBJECT_STR + DB_DATAOBJECT_TXT
        //     'photo'  =>  64 // == DB_DATAOBJECT_STR + DB_DATAOBJECT_BLOB
        */
        $fields = array();
        $tmp = $do->table();
        reset($tmp);
        foreach($tmp as $k => $v){
            $fields[$k] = $k;
        }
        $this->htmlfilter($fields);
        //echo '<pre>'; print_r($_POST); echo '</pre>';
    }
    //------------------------------------------------------------------------------------------------------
    public function htmlfilter($fields)
    {
        $tmp1 = array('=', ' LIKE ','<', '>', '<=', '>=');
        $op = array();
        foreach($tmp1 as $k => $v){
            $op[$v] = $v;
        }

        $form = new HTML_QuickForm('showDG_filter', 'post', $_SERVER['REQUEST_URI']);
        $group = array();
        //$form->addElement('header', null, 'Filtra');
        $group[] =& HTML_QuickForm::createElement('select', 'field0', 'a', $fields);
        $group[] = & HTML_QuickForm::createElement('select', 'op', 'b', $op);
        $group[] =& HTML_QuickForm::createElement('text', 'field1', 'c');
        $group[] =& HTML_QuickForm::createElement('submit', 'vai', 'FILTRA !');
        $form->addGroup($group, '__filter', 'Filtro', '&nbsp;');
        //---------------
        /*
        require_once __MVC_DIR.'/includes/extPear/HTML_QuickForm_Renderer_Bootstrap.php';
        $renderer =& new HTML_QuickForm_Renderer_Bootstrap();
        //$form->setAttributes('class="form-horizontal" role="form"');
        $form->accept($renderer);
        $this->filter_html = $renderer->toHtml();
        */
        $this->filter_html = $this->getFormStyled(array('form'=>$form));
        $this->filter_html=str_replace('<form ', '<form class="form-inline" ', $this->filter_html);
        //$this->filter_html = $form->ToHtml();

        if (isset($_POST['__filter']) && isset($_POST['__filter']['field1']) && strlen($_POST['__filter']['field1']) > 0) {
            $filter = $_POST['__filter']['field0'].$_POST['__filter']['op'].'"'.$_POST['__filter']['field1'].'"';
            $this->filter_sql=$filter;
        }
    }


    //-----------------------------------------------------------------------------------------------------
    public function getFormStyled($params)
    {
        extract($params);
        $renderer=$this->__get('rendererBootstrap');
        $form->accept($renderer);
        $outHtml =$renderer->toHtml();
        return $outHtml;
    }





















    //-----------------------------------------------------------------------------------------




    public function translateFieldName($data, $trad)
    {
        $ris=array();

        $ris=array();
        reset($data);
        foreach($data as $k=>$v){
            $key=$k;
            if (is_object($v) || is_array($v)) {
                reset($v);
                foreach($v as $k1=>$v1){
                    $key=$k.'_'.$k1;
                    $key=STRING_OP::slugify($key);
                    $key=str_replace('-', '_', $key);
                    $ris[$key]=$v1;
                }
            } else {
                $key=STRING_OP::slugify($key);
                $key=str_replace('-', '_', $key);
                $ris[$key]=$v;
            }
        }
        //ARRAY_OP::print_x($ris);ddd('qui');
        $ris1=array();
        reset($ris);
        foreach($ris as $k=>$v){
            if (!isset($trad[$k]) && trim($v)!='') {
                $str1='col_xs_';
                if (substr($k, 0, strlen($str1))!=$str1) {
                    echo '<br/>['.$k.'] non gestito ['.$v.']';
                    ARRAY_OP::print_x($data);

                    ddd('qui');
                }
            } else {
                if (trim($v)!='') {
                    $k1=$trad[$k].'';
                    if ($k1!='0') {
                        //echo '<br/>['.$k1.']['.substr($k1,0,strlen($str1)).']['.$str1.']';
                        $ris1[$k1]=$v;
                    //echo '<br/><span style="color:green">'.$k.'   ['.$k1.']   '.$v.'</span>';
                    } else {
                        //echo '<br/><span style="color:red">'.$k.'   ['.$k1.']   '.$v.'</span>';
                    }
                }
            }
        }
        //ARRAY_OP::print_x($ris);
        //ARRAY_OP::print_x($ris1);ddd('qui');

        return $ris1;
    }


    public function getWhereAdd($params)
    {
        if (!isset($params['tablename'])) {
            ARRAY_OP::print_x('<h3>Specificate tabella</h3>');
            ddd('qui');
        }
        //if(defined('initOptions')){

        DO_OP::initOptions($params);

        	//define('initOptions',1);
        //}

        $tbl=$params['tablename'];
        $do = DB_DataObject::factory($tbl);
        if (PEAR::isError($do)) {
            ddd($do);
            return ;
        }
        if (isset($params['dbname'])) {
            $do->database($params['dbname']);
        }
        //ddd($do);

        $fields=array_keys($do->table());

        $whereAdd=array();
        if (isset($params['whereAdd'])) {
            $whereAdd[]=$params['whereAdd'];
        }

        if (isset($params['lista_stati']) && !isset($params['stato'])) {
            $params['stato']=$params['lista_stati'];
        }
        if (!isset($params['tipo_data'])) {
            $params['tipo_data']=1;
        } //se non viene passato si presume che sia per competenza
        $tipo_data=$params['tipo_data'];

        if (isset($params['stato']) && $params['stato']!='' && in_array('last_stato', $fields)) {
            if (isset($params['tbl_stato'])) {
                $sql='(find_in_set('.$params['tbl_stato'].'.last_stato,"'.$params['stato'].'"))';
            } else {
                $sql='(find_in_set(last_stato,"'.$params['stato'].'"))';
            }
            $whereAdd[]=$sql;
        }
        if (isset($params['giorno']) && $params['giorno']!='' &&
        isset($params['mese']) && $params['mese']!='' &&
        isset($params['anno']) && $params['anno']!='') {
        }


        if (isset($params['giorno']) && $params['giorno']!='') {
            if (isset($params['_field_giorno'][$tbl])) {
                $whereAdd[]=$params['_field_giorno'][$tbl][$tipo_data].' = "'.$params['giorno'].'"';
            } else {
                $whereAdd[]='day('.$params['_field_dal'][$tbl][$tipo_data].') = "'.$params['giorno'].'"';
            }
        }
        if (isset($params['mese']) && $params['mese']!='') {
            if (isset($params['_field_mese'][$tbl])) {
                $whereAdd[]=$params['_field_mese'][$tbl][$tipo_data].' = "'.$params['mese'].'"';
            } else {
                $whereAdd[]='month('.$params['_field_dal'][$tbl][$tipo_data].') = "'.$params['mese'].'"';
            }
        }

        if (isset($params['anno']) && $params['anno']!='') {
            if (isset($params['_field_anno'][$tbl])) {
                $whereAdd[]=$params['_field_anno'][$tbl][$tipo_data].' = "'.$params['anno'].'"';
            } else {
                $whereAdd[]='year('.$params['_field_dal'][$tbl][$tipo_data].') = "'.$params['anno'].'"';
            }
        }

        if (isset($params['between_anno']) && $params['between_anno']!='') {
            $whereAdd[]='
		(
			('.$params['between_anno'].' between year('.$params['_field_dal'][$tbl][$tipo_data].') and year('.$params['_field_al'][$tbl][$tipo_data].')
		) or
			('.$params['between_anno'].' >= year('.$params['_field_dal'][$tbl][$tipo_data].') and '.$params['_field_al'][$tbl][$tipo_data].'=0)
		)';
        }

        //------------------------------------------------------------------------------------------------
        if (isset($params['dal']) && isset($params['al'])) {
            if (is_array($params['dal'])) {
                $d=$params['dal'];
                $dal_time=mktime(0, 0, 0, $d['m'], $d['d'], $d['Y']);
            } else {
                $dal_time=strtotime($params['dal']);
            }
            if (is_array($params['al'])) {
                $d=$params['al'];
                $al_time=mktime(0, 0, 0, $d['m'], $d['d'], $d['Y']);
            } else {
                $al_time=strtotime($params['al']);
            }
            $whereAdd[]='('.$params['_field_dal'][$tbl][$tipo_data].' between \''.date('Y-m-d', $dal_time).'\' and \''.date('Y-m-d', $al_time).'\')';
        }
        //---------------------------------------------------------------------------------------------------------

        if (!isset($params['data_elab']) && isset($params['data'])) {
            $params['data_elab']=$params['data'];
        }
        if (!isset($params['data_elab']) && isset($params['between_date'])) {
            $params['data_elab']=$params['between_date'];
        }


        if (isset($params['data_elab']) && isset($params['data_elab']['d'])) {
            $data_time=DATE_OP::dateArray2time($params['data_elab']);
            $whereAdd[]='(
			( '.date('Ymd', $data_time).' between '.$params['_field_dal'][$tbl][$tipo_data].' and '.$params['_field_al'][$tbl][$tipo_data].' )
			or
			( '.date('Ymd', $data_time).' >= '.$params['_field_dal'][$tbl][$tipo_data].' and '.$params['_field_al'][$tbl][$tipo_data].'=0 )
		)';
        }


        //--------------------------------------------------------------------------------
        $special=array('lista_stati','stato','tipo_data','giorno','mese','anno','dal','al','data_elab','tablename','data_start','datemod');
        reset($params);
        foreach($params as $k=>$v){
            if (in_array($k, $fields) && $v!="") {
                if (!is_array($v) && !is_object($v) && !in_array($k, $special)) {
                    $whereAdd[]=$k.'="'.$v.'"';
                } else {
                    //ARRAY_OP::print_x($k);ARRAY_OP::print_x($v);
                }
            }
        }
        $whereAdd=array_unique($whereAdd);
        //---------------------------------------------------------------------------------
        if (isset($params['_field_ann'][$tbl])) {
            $whereAdd[]=' '.$params['_field_ann'][$tbl].'=""';
        } else {
            //echo 'no Ann ['.$tbl.']';
        //ARRAY_OP::print_x($params['_field_ann']);
        }

        $whereAdd=implode(chr(13).' AND '.chr(13), $whereAdd);

        if (isset($params['_field_trad'][$tbl])) {
            $trad=$params['_field_trad'][$tbl];
            reset($trad);
            foreach ($trad as $k => $v){
                $whereAdd=str_replace($k, $v, $whereAdd);
            }
        }



        return $whereAdd;
    }



    public function getWhereAdd_old($params)
    {
        $special=array('lista_stati','stato','tipo_data','giorno','mese','anno','dal','al','data_elab','tablename','data_start','datemod');
        $whereAdd=array();
        if (isset($params['lista_stati']) && !isset($params['stato'])) {
            $params['stato']=$params['lista_stati'];
        }
        if (!isset($params['tipo_data'])) {
            $params['tipo_data']=1;
        } //se non viene passato si presume che sia per competenza
        $tipo_data=$params['tipo_data'];

        //--------------//---------------------//---------------------------------------------
        if (isset($params['giorno']) && $params['giorno']!='') {
            $whereAdd[]='day(data_start)="'.$params['giorno'].'"';
        }
        if (isset($params['mese']) && $params['mese']!='') {
            $whereAdd[]='month(data_start)="'.$params['mese'].'"';
        }
        if (isset($params['anno']) && $params['anno']!='') {
            $whereAdd[]='year(data_start)="'.$params['anno'].'"';
        }




        //--------------//---------------------//-------------------------------------------
        if (isset($params['dal']) && isset($params['al'])) {
            if (is_array($params['dal'])) {
                $d=$params['dal'];
                $dal_time=mktime(0, 0, 0, $d['m'], $d['d'], $d['Y']);
            } else {
                $dal_time=strtotime($params['dal']);
            }
            if (is_array($params['al'])) {
                $d=$params['al'];
                $al_time=mktime(0, 0, 0, $d['m'], $d['d'], $d['Y']);
            } else {
                $al_time=strtotime($params['al']);
            }
            $whereAdd[]='(data_start between \''.date('Y-m-d', $dal_time).'\' and \''.date('Y-m-d', $al_time).'\')';
        }
        //------------//--------------------///----------------------------------------------
        if (!isset($params['data_elab']) && isset($params['data'])) {
            $params['data_elab']=$params['data'];
        }
        if (isset($params['data_elab']) && isset($params['data_elab']['d'])) {
            $data_time=dateArray2time($params['data_elab']);
            $whereAdd[]='(
			( '.date('Ymd', $data_time).' between data_start and data_end )
			or
			( '.date('Ymd', $data_time).' >= data_start and data_end=0 )
		)';
        }
        //------------------//---------------//-----------------------------------------------

        /*
        if(isset($params['matr']) && $params['matr']!=''){
            $whereAdd[]='matr="'.$params['matr'].'"';
        }
        if(isset($params['ente']) && $params['ente']!=''){
            $whereAdd[]='ente="'.$params['ente'].'"';
        }
        */
        if (isset($params['whereAdd']) && $params['whereAdd']!='') {
            $whereAdd[]=$params['whereAdd'];
        }


        if (!isset($params['tablename'])) {
            $whereAdd=implode(chr(13).' AND '.chr(13), $whereAdd);
            if ($params['tipo_data']==2) {
                $whereAdd=str_replace('data_start', 'datemod', $whereAdd);
            }
            return $whereAdd;
        }

        $do = DB_DataObject::factory($params['tablename']);
        $tablename=$params['tablename'];
        //extract($params);
        $fields=array_keys($do->table());
        //--------------------------------------------------
        if (isset($params['stato']) && $params['stato']!='' && in_array('last_stato', $fields)) {
            if (isset($params['tbl_stato'])) {
                $sql='(find_in_set('.$params['tbl_stato'].'.last_stato,"'.$params['stato'].'"))';
            } else {
                $sql='(find_in_set(last_stato,"'.$params['stato'].'"))';
            }
            $whereAdd[]=$sql;
        }
        //-------------------------------------------------------
        reset($params);
        foreach($params as $k=>$v){
            if (in_array($k, $fields) && $v!="") {
                if (!is_array($v) && !is_object($v)) {
                    $whereAdd[]=$k.'="'.$v.'"';
                } else {
                    //ARRAY_OP::print_x($k);ARRAY_OP::print_x($v);
                }
            }
        }
        $whereAdd=array_unique($whereAdd);
        $whereAdd=implode(chr(13).' AND '.chr(13), $whereAdd);
        if ($params['tipo_data']==2) {
            $whereAdd=str_replace('data_start', 'datemod', $whereAdd);
        }

        if (isset($params['_field_trad'][$tablename])) {
            $trad=$params['_field_trad'][$tablename];
            reset($trad);
            foreach ($trad as $k => $v){
                $whereAdd=str_replace($k, $v, $whereAdd);
            }
        }



        if (isset($params['_field_dal'][$tablename])) {
            $whereAdd=str_replace('data_start', $params['_field_dal'][$tablename], $whereAdd);
        } else {
            //ARRAY_OP::print_x($table)
        //ddd('qui');
        }
        if (isset($params['_field_al'][$tablename])) {
            $whereAdd=str_replace('data_end', $params['_field_al'][$tablename], $whereAdd);
        }

        if (isset($params['_field_mese'][$tablename])) {
            $whereAdd=str_replace('month(data_start)', $params['_field_mese'][$tablename][$tipo_data], $whereAdd);
            $whereAdd=str_replace('month(datemod)', $params['_field_mese'][$tablename][$tipo_data], $whereAdd);
        }

        if (isset($params['_field_anno'][$tablename])) {
            $whereAdd=str_replace('year(data_start)', $params['_field_anno'][$tablename][$tipo_data], $whereAdd);
            $whereAdd=str_replace('year(datemod)', $params['_field_anno'][$tablename][$tipo_data], $whereAdd);
        }

        if (isset($params['_field_giorno'][$tablename])) {
            $whereAdd=str_replace('day(data_start)', $params['_field_giorno'][$tablename][$tipo_data], $whereAdd);
            $whereAdd=str_replace('day(datemod)', $params['_field_giorno'][$tablename][$tipo_data], $whereAdd);
        }


        if (isset($_field_ann[$tablename])) {
            $whereAdd.=chr(13).' AND '.$_field_ann[$tablename].'=""';
        }




        return $whereAdd;
    }

    public function db_exists($dbname)
    {
        //$mdb2=$this->registry->conf->getMDB2();
        //$listdb=$mdb2->listDatabases();
        $listdb=$this->__get('listDatabases');
        //ddd($listdb);
        return in_array($dbname, $listdb);
    }



    public function fetchAllX($params)
    {
        //if(!defined('initOptions')){
        DO_OP::initOptions($params);
        	//define('initOptions',1);
        //echo '<h3>AAA-</h3>';
        //}


        $do = DB_DataObject::factory($params['tablename']);
        //ddd($do);
        //ddd('qui');
        if (PEAR::isError($do)) {
            //ddd($do);
            ddd($do);
            return ;
        }
        //ddd($do);
        if (isset($params['dbname'])) {
            $do->database($params['dbname']);
            //ddd($do);
        }
        //ddd($params['dbname']);
        //ddd($do);ddd('qui');

        if (PEAR::isError($do)) {
            ddd($do);
            return ;
        }

        /*
          if(!$this->db_exists($do->_database)){
              echo '<h3>NON ESISTE DB['.$do->_database.']</h3>';
              ddd('qui');
          }
        */
        	//ddd($do);


        $fields=array_keys($do->table());

        //if($params['tablename']=='ced03f'){	ARRAY_OP::print_x($do);ddd('qui');}

        if (isset($params['whereAdd'])) {
            $do->whereAdd($params['whereAdd']);
        }
        if (isset($params['limit'])) {
            if (!is_array($params['limit'])) {
                $params['limit']=explode(',', $params['limit']);
            }
            $n_limit=count($params['limit']);
            switch ($n_limit) {
            case 1:
                $do->limit($params['limit'][0]);
            break;
            case 2:
                //ARRAY_OP::print_x($params['limit']);
                $do->limit($params['limit'][0], $params['limit'][1]);
            break;
            default:
                ddd('qui');
            break;

        }
        }
        if (isset($params['orderBy'])) {
            $orderBy=trim($params['orderBy']);
            //ARRAY_OP::print_x($do->table()); ddd('qui');
            //ARRAY_OP::print_x($fields);
            if (in_array($orderBy, $fields)) {
                if (isset($params['direction'])) {
                    $orderBy.=' '.$params['direction'];
                }
                //ddd('qui');
                $do->orderBy($orderBy);
            } else {
                //ARRAY_OP::print_x($tablename);
            //ARRAY_OP::print_x($orderBy);
            //ARRAY_OP::print_x($fields);
            //ddd('qui');
            }
        }
        if (isset($params['selectAdd'])) {
            $old_selectAdd=$do->selectAdd();
            $do->selectAdd($params['selectAdd']);
            //ARRAY_OP::print_x($params['selectAdd']); ddd('qui');
        }
        try {
            //ARRAY_OP::print_x($do);
            $res=$do->find();
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            if (isset($params['selectAdd'])) {
                ARRAY_OP::print_x($params['selectAdd']);
            }
            ARRAY_OP::print_x($do);
            ///ARRAY_OP::print_x($params['whereAdd']);
        }

        //if($params['tablename']=='ced03f'){	ARRAY_OP::print_x($do);ddd('qui');}

        $ris=array();
        while ($do->fetch()) {
            $tmp = DO_OP::getLinksDoX($do);
            //*
            if (isset($params['selectAdd'])) {
                $tmp1=$params['selectAdd'];
                $tmp1=str_ireplace('distinct ', '', $tmp1);
                $tmp=OBJ_OP::obj_render($tmp, $tmp1);
            }
            //*/
            $ris[] = $tmp;
        }
        return $ris;
    }

    public function getListaKeys($params)
    {
        extract($params);
        $ris=array();
        reset($data);
        foreach($data as $k=>$v){
            reset($keys);
            $skey=array();
            foreach ($keys as $k1 => $v1){
                $skey[]=$v->$v1;
            }
            $skey=implode('-', $skey);
            $ris[]=$skey;
        }
        return $ris;
    }

    public function getTableListaKeys($params)
    {
        extract($params);
        //$sql='select * from generale.ANA00K20 where ente="'.$v->ente.'" and matr="'.$v->matr.'" and anaann=""';

        $sql=array();
        if (isset($whereAdd)) {
            $sql[]=$whereAdd;
        }
        if (is_array($lista_keys)) {
            $lista_keys=implode(',', $lista_keys);
        }
        $sql[]='find_in_set(concat('.implode(',\'-\',', $keys).'),"'.$lista_keys.'")';

        $sql=implode(chr(13).' AND ', $sql);
        //$sql='select * from '.$tablename.' where '.$sql;
        //$ris=$mdb2->queryAll($sql);
        $params['whereAdd']=$sql;
        return DO_OP::fetchAllX($params);//$ris;
    }


    public function do_toXLS($params)
    {
        extract($params);
        $do_clone = clone($do);
        $do_clone->find();
        //$data = $do_clone-> fetchAll();
        //echo '<pre>[[[';print_r($data); echo '</pre>';ddd('qui');


        $data=array();
        //reset($do_clone);
        while ($do_clone->fetch()) {
            $tmp= DO_OP::getLinksDoX($do_clone, array('linkDisplayFields'=>1));
            $data[]=$tmp;
        }
        //echo '<pre>';print_r($data); echo '</pre>';

        //*
        $data1=array();
        reset($data);
        foreach($data as $k=>$v){
            $data1[$k]=new stdclass();


            reset($do_clone->fb_fieldsToRender);
            foreach ($do_clone->fb_fieldsToRender as $k1 => $v1){
                //echo '<pre>';print_r(array_keys(get_object_vars($v)));echo '</pre>';
                if (is_object($v)) {
                    $v=get_object_vars($v);
                }
                $tmp3=array_keys($v);
                if (in_array($v1, $tmp3)) {
                    $data1[$k]->$v1=$data[$k]->$v1;
                }
                //*
                $v2='_'.$v1;
                if (in_array($v2, $tmp3)) {
                    $data1[$k]->$v2=$data[$k]->$v2;
                }
                //*/
            }
        }


        //echo '<pre>';print_r($data1); echo '</pre>';
        $data2=ARRAY_OP::array_inline_multi(array('data'=>$data1));
        //echo '<pre>';print_r($data2); echo '</pre>';
        $fieldlabels1=array();
        //echo '<pre>';print_r($data2['fields']); echo '</pre>';
        //echo '<pre>';print_r($fieldlabels); echo '</pre>';
        reset($data2['fields']);
        foreach ($data2['fields'] as $k => $v){
            if (isset($fieldlabels[$v])) {
                $fieldlabels1[$v]=$fieldlabels[$v];
            } else {
                $fieldlabels1[$v]=$v;
            }
        }
        //echo '<pre>';print_r($fieldlabels1); echo '</pre>';
        $xls_parz=array('nomefile'=>'export-'.session_id()
        ,'data'=>$data2['data'] //
        ,'fieldlabels'=>$fieldlabels1 // ????????
    );
        ARRAY_OP::toXLS($xls_parz);
    }

    public function getLinksIniArray($params)
    {
        if (!isset($params['dbname'])) {
            $do=$params['do'];
            $dbname=$do->_database;
        } else {
            $dbname=$params['dbname'];
        }
        $do_options = PEAR::getStaticProperty('DB_DataObject', 'options');
        $file_links = FILE_OP::fixpath($do_options['schema_location'].'/'.$dbname.'.links.ini');
        //ARRAY_OP::print_x($file_links);
        if (!file_exists($file_links)) {
            FILE_OP::writeFile($file_links, '', true);
        }
        $links_array = parse_ini_file($file_links, true);
        return $links_array;
        //ddd($links_array);ddd('qui');
        $tbl1 = $do->__table;
        $ris=array();
        reset($links_array);
        foreach ($links_array[$tbl1] as $k1 => $v1){
            $obj=new stdClass();
            list($obj->linkedtable, $obj->linkedfield)=explode(':', $v1);
            $k_arr=explode(',', $k1);
            $v_arr=explode(',', $obj->linkedfield);
            $c_arr=array_combine($k_arr, $v_arr);
            $obj->k=$c_arr;
            $ris[$k1]=$obj;
        }

        ddd($ris);

        return $links_array;
    }


    public function getLinksIni2Array($params)
    {
        $links=DO_OP::getLinksIniArray($params);
        //ddd($links);
        $links_1=array();
        reset($links);
        foreach ($links as $k => $v){
            $obj=new stdClass();
            $obj->tbl=$k;
            $obj->linked=array();
            reset($v);
            foreach($v as $k1=>$v1){
                $v2=explode(':', $v1);
                $obj1=new stdClass();
                $obj1->field=$k1;
                $obj1->linked_table=$v2[0];
                $obj1->linked_field=$v2[1];
                $obj->linked[$k1]=$obj1;
            }
            //$links_1[]=$obj;
            $links_1[$obj->tbl]=$obj;
        }
        return $links_1;
    }



    public function getLinksDoX($do, $params=null)
    {
        $tbl1 = $do->__table;
        $links_array=DO_OP::getLinksIniArray(array('do'=>$do));
        $ris_row = ARRAY_OP::toObject($do->toArray());
        if (isset($links_array[$tbl1])) {
            reset($links_array[$tbl1]);
            //echo '['.__LINE__.']['.__FILE__.']<pre>'; print_r($links_array[$tbl1]); echo '</pre>';
            //ARRAY_OP::print_x($links_array[$tbl1]);
            foreach ($links_array[$tbl1] as $kl => $vl){
        //echo '<br/> Kl:'.$kl;
                //echo '<br/> Vl:'.$vl;
                $k_arr=explode(',', $kl);
                list($linkedtable, $linkedfield)=explode(':', $vl);
                $v_arr=explode(',', $linkedfield);

                $c_arr=array_combine($k_arr, $v_arr);
                //ARRAY_OP::print_x($c_arr);ddd('qui');



                $kl_tmp=explode(',', $kl);
                $kl=$kl_tmp[0];
                $tmpl = explode(':', $vl);
                $tmpl_1_tmp=explode(',', $tmpl[1]);
                $tmpl[1]=$tmpl_1_tmp[0];
                //ddd($do);
                //ddd($kl);
                //echo '<h3>--['.$do->$kl.']--</h3>';

                if (isset($do->$kl) && $do->$kl!==0 && $do->$kl!="") {
                    //ddd('qui');
                    /*
                    echo '<br>k1      : '.$kl;
                    echo '<br>tmp1[0] :'.$tmpl[0];
                    echo '<br>tmp1[1] :'.$tmpl[1];
                    echo '<br>';
                    //*/
                    if (method_exists($do, 'prepareLinkedDataObject')  && true) {
                        //ddd('qui');
                        $ris_links=DB_DataObject::factory($tmpl[0]);
                        $do->prepareLinkedDataObject($ris_links, $kl);
                        //$ris_links = $ris_links->getLink($kl, $tmpl[0], $tmpl[1]);
                        $ris_links->whereAdd($tmpl[1].'="'.$do->$kl.'"');
                        /*
                        $ris_links1 = $do->getLink($kl, $tmpl[0], $tmpl[1]);
                        $do->prepareLinkedDataObject($ris_links1,$kl);
                        $ris_links1->find();
                        $ris_links1->fetch();
                        ARRAY_OP::print_x($ris_links1);
                        ARRAY_OP::print_x($ris_links);ddd('qui');
                        */
                        $ris_links->find();
                        $ris_links->fetch();
                    //ARRAY_OP::print_x($ris_links);

                //ARRAY_OP::print_x($ris_links1);
                //ddd('qui');
                    } else {
                        //ddd('qui');
                        //ARRAY_OP::print_x($kl.'   '.$tmpl[0].'   '.$tmpl[1]);
                        $ris_links = $do->getLink($kl, $tmpl[0], $tmpl[1]);
                        //ARRAY_OP::print_x($ris_links);
                //ddd('qui');


                //$ris_links = $do->getLink('asztip', $tmpl[0], 'tipo');
                //$ris_links = $do->getLink('aszcod', $tmpl[0], 'codice');
                //$ris_links = $do->getLink('asztip'), $tmpl[0], 'tipo');
                //$do->prepareLinkedDataObject($ris_links,$kl);
                //$res=  $ris_links->getDatabaseResult();

                //ARRAY_OP::print_x($ris_links);ddd('qui');
                //ddd('qui');
                    }

                    //$do->fetch();
                    //*
                    //ARRAY_OP::print_x($do);
                    //ARRAY_OP::print_x($do->links());
                    //ARRAY_OP::print_x($ris_links);
                    //ARRAY_OP::print_x($ris_links->links());
                    //$ris_links->whereAdd('tipo = 17');
                    //$ris_links->find();
                    //$a=$ris_links->fetch();
                    //ARRAY_OP::print_x($ris_links);


                    //*/

                    if (isset($kl_tmp[1])) {
                        //ddd('qui');
                        //$ris_links = $do->getLink($kl, $tmpl[0], $tmpl[1]);
                        //echo '<h3>'.$kl.'  '.$tmpl[0].'  '. $tmpl[1].'</h3>';
                        //
                        //echo '<pre>';print_r($ris_links);echo '</pre>';
                        if (is_numeric($kl_tmp[1])) {
                            $do_z=DB_DataObject::factory($ris_links->__table);
                            $do_z->whereAdd($tmpl[1].'="'.($ris_links->$tmpl[1]).'"');
                            $do_z->whereAdd($tmpl_1_tmp[1].'="'.($kl_tmp[1]).'"');
                            $do_z->find();
                            $do_z->fetchAll();
                            $ris_links=$do_z;
                        } else {
                            //ddd('qui');
                            if (!isset($ris_links->__table)) {
                                ARRAY_OP::print_x($ris_links);
                                ddd('qui');
                            }
                            $do_z=DB_DataObject::factory($ris_links->__table);
                            reset($c_arr);
                            $sql=array();
                            foreach ($c_arr as $kc => $vc){
                                $sql[]=$vc.'="'.$do->$kc.'"';
                                //echo '<br style="clear:both;"/><pre>['.$linkedtable.']['.$ris_links->__table.']'.chr(13).$sql.'</pre>';
                            }
                            $sql=implode(chr(13).' AND ', $sql);
                            //echo '<br style="clear:both;"/><pre>['.$linkedtable.']['.$ris_links->__table.']'.chr(13).$sql.'</pre>';
                            //$sql1=$tmpl[1].'="'.($ris_links->$tmpl[1]).'"';
                            //$sql2=$tmpl_1_tmp[1].'="'.($do->$kl_tmp[1]).'"';
                            //echo  '<br style="clear:both;"/><pre>sql1'.chr(13).$sql1.'</pre>';
                            //echo  '<br style="clear:both;"/><pre>sql2'.chr(13).$sql2.'</pre>';
                            $do_z->whereAdd($sql);
                            //$do_z->whereAdd($tmpl[1].'="'.($ris_links->$tmpl[1]).'"');
                            //$do_z->whereAdd($tmpl_1_tmp[1].'="'.($do->$kl_tmp[1]).'"');

                            $do_z->find();
                            $do_z->fetch();
                            //ARRAY_OP::print_x($do_z->toArray());
                            //$do_z->fetchAll();
                            $ris_links=$do_z;
                            //ARRAY_OP::print_x($ris_links);ddd('qui');
                        }
                        //echo '<pre>';print_r($do_z);echo '</pre>';
                //$do_z->whereAdd
                //$ris_links
                //$ris_links->whereAdd('tipo=16');
                //$ris_links->find();
                //$ris_links->fetch();
                //$ris_links = $do->getLink(16,'codici','tipo');
                    }
                    //ddd($ris_links);ddd('qui');
                    //echo '['.__LINE__.']['.__FILE__.']<pre>';print_r($ris_links); echo '</pre>';
                    if (!is_object($ris_links)) {

                //echo '<pre>['; print_r( $ris_links); echo ']</pre>';
                //ddd('qui');
                    } else {
                        //ddd('qui');
                        //echo '<pre>'; print_r($ris_links->toArray()); echo '</pre>';
                        //$subkey = '_'.$kl;
                        if (isset($kl_tmp[1]) && is_numeric($kl_tmp[1])) {
                            $subkey = '_'.$kl;
                        } else {
                            $subkey = '_'.implode('_', $kl_tmp);
                        }

                        //ARRAY_OP::print_x($subkey);
                        //ARRAY_OP::print_x($ris_links);

                        if (isset($params['linkDisplayFields']) && $params['linkDisplayFields']==1 && isset($ris_links->fb_linkDisplayFields)) {
                            reset($ris_links->fb_linkDisplayFields);
                            $tmp2=new stdClass();
                            foreach ($ris_links->fb_linkDisplayFields as $k2 => $v2){
                                $tmp2->$v2=$ris_links->$v2;
                            }
                            //ddd('qui');
                            //echo '<pre>';print_r($tmp2); echo '</pre>';
                            $ris_row->$subkey = $tmp2;
                        } else {
                            //ddd('qui');
                            $ris_row->$subkey = ARRAY_OP::toObject($ris_links->toArray());
                            //ARRAY_OP::print_x($ris_row);
                        }
                    }
                }
            }
        }
        //---------------------------------------------------------------------------------------
        if (isset($do->fb_crossLinks)) {
            //ddd('qui');
            reset($do->fb_crossLinks);
            foreach ($do->fb_crossLinks as $k1 => $v1){
                //echo '<br/>'.$v['table'];
                $tbl2 = $v1['table'];
                reset($links_array[$tbl2]);
                //echo '['.__LINE__.']['.__FILE__.']['.$tbl2.']<pre>'; print_r($links_array[$tbl2]); echo '</pre>';
                foreach ($links_array[$tbl2] as $kl => $vl){
                    $tmpl = explode(':', $vl);
                    //echo '['.__LINE__.']['.__FILE__.']<pre>'; print_r($tmpl); echo '</pre>';
                    if ($tmpl[0] == $tbl1) {
                        //echo '<h3>PRESO '.$tbl2.'.'.$kl.' = '.$tmpl[0].'.'.$tmpl[1].'</h3>';
                        $whereAdd = $kl.'='.$do->$tmpl[1];
                        //echo '<pre>'.$whereAdd.'</pre>';
                        $do1 = DB_DataObject::factory($tbl2);

                        $do1->whereAdd($whereAdd);
                        $do1->find();
                        $tmp2 = array();
                        while ($do1->fetch()) {
                            $tmp2[] = ARRAY_OP::toObject($do1->toArray());
                        }
                        $subkey = '_'.$tbl2;
                        $ris_row->$subkey = $tmp2;
                        //$ris_links = $do->getLink($kl, $tmpl[0], $tmpl[1]);
                    //echo '<pre>['; print_r( $ris_links); echo ']</pre>';
                    }

                    /*
                    if($do->$kl!=0 && $do->$kl!=""){
                        $ris_links = $do->getLink($kl, $tmpl[0], $tmpl[1]);
                        if (!is_object($ris_links)) {
                            //echo '<h3>'.$kl.'  '.$tmpl[0].'  '. $tmpl[1].'</h3>';
                            //echo '<pre>['; print_r( $ris_links); echo ']</pre>';
                        }else{
                            //echo '<pre>'; print_r($ris_links->toArray()); echo '</pre>';
                            $subkey = '_'.$kl;
                            $ris_row->$subkey = ARRAY_OP::toObject($ris_links->toArray());
                        }
                    }
                    */
                }
            }
        }
        //ARRAY_OP::print_x($ris_row);


        return $ris_row;
    }
    //--------------------------------------------------------------------------
    public function collegaDoPadreDoFiglio($do_padre, $do_figlio, $ris)
    {
        //$do0 = DB_DataObject::factory($tbl1);
        //echo '['.$tbl2.']';

        //$do1 = DB_DataObject::factory($tbl2);
        $do = clone($do_padre);
        $do1 = clone($do_figlio);

        $do-> joinAdd($do1);

        $do-> find();

        while ($do-> fetch()) {
            $do-> getLinks();
            echo '<pre>';
            print_r($do);
            echo '</pre>';
        }
        return $ris;
    }









    //--------------------------------------------------------------------------
    public function specialType($tablename, $fieldname, $values = array())
    {
        //echo '<pre>'; print_r($values); echo '</pre>';
        $do = DB_DataObject::factory('_specialtypetable');
        if (PEAR::isError($do)) {
            echo($do->getMessage() . '<br />' . $do->getDebugInfo());
            //ddd('qui');
            return;
        }

        $do->tablename = $tablename;
        $do->fieldname = $fieldname;
        $tag=$fieldname;
        $do->find();
        if ($do->count() == 0) {
            return false;
        }
        $do->fetch();

        $dsn0 = $do->getDatabaseConnection();
        $dsn = $dsn0->dsn['phptype'].'://'.$dsn0->dsn['username'].':'.$dsn0->dsn['password'].'@'.$dsn0->dsn['hostspec'].'/'.$dsn0->database_name;
        $options = array();
        $attributes= array();
        if (strlen(trim($do->fieldoptions)) > 5) {
            $options = unserialize($do->fieldoptions);
        }
        if (strlen(trim($do->fieldattributes)) > 5) {
            $attributes = unserialize($do->fieldattributes);
        }
        if (!isset($labels[$tag])) {
            $labels[$tag] = '['.$tag.']';
        }
        foreach ($options as $ko => $vo){
            $pattern='/##([a-z_]*)##/';
            preg_match($pattern, $vo, $matches);
            if (isset($matches[1])) {
                $tmp_m = $matches[1];
                //echo '<h3>'.$tmp_m.'</h3>';
                $options[$ko]=$values[$tmp_m];
            }
        }
        //*/
        	//echo '<li>['.$v->fieldtype.']['.$fb->elementNamePrefix.$tag.']</li>'.chr(13);
        $el = HTML_QuickForm::createElement($do->fieldtype, $fieldname, $fieldname, $options, $attributes);
        //echo '<pre>'; print_r($attributes); echo '</pre>';
        if (isset($attributes['query'])) {
            $el->loadQuery($dsn, $attributes['query']);
        }
        return $el;
    }



    //--------------------------------------------------------------------------
    //insert data into table called tablename
    //params deve avere tablename = nome tabella
    public function insertDataTablename($params)
    {
        extract($params);
        $do = & DB_DataObject::factory($tablename);
        $fields=array_keys($do->toArray());
        foreach ($data as $k => $v) {
            $k = strtolower($k);
            if (!in_array($k, $fields)) {
                echo '<br/>il campo <b>['.$k.']</b> non esiste ';
                //echo '<pre>';print_r($_SERVER); echo '</pre>';
                echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?rt=confx/addfield/'.$k.'"> Aggiungi il campo </a>';
                die('<br/>'.__LINE__.__FILE__);
            }
            $do->$k = $v;
        }
        $do->insert();
    }
    //-------------------------------
    public function menudo2xmldir($params)
    {
        //-- creo il menu ---
        extract($params);
        require_once 'XML/Query2XML.php';

        $query2xml = XML_Query2XML::factory($mdb2);
        $menu_do = DB_DataObject::factory('_menu');
        $menu_do->find();
        while ($menu_do->fetch()) {
            $id = $menu_do->id;
            $dom = $query2xml->getFlatXML(
            'SELECT * FROM _menu  where id='.$id,
            'root',
            'row'
        );
            $dom->formatOutput = true;
            FILE_OP::writeFile($dir_menu.'/menu_'.$id.'.xml', $dom->saveXML(), true);
        }
        //--------------------------------
    }
    //----------------------------------------------------------------------------------------------
    public function DataObjectCustomFields($obj, $fb, $fields, $fieldType, $options = array())
    {
        //if(!isset($fb->elementNamePrefix)) $fb->elementNamePrefix=''; // da sistemare
        foreach ($fields as $k => $tag){
            if (!isset($obj->fb_fieldLabels[$tag])) {
                $obj->fb_fieldLabels[$tag] = $tag;
            }
            //$el = HTML_QuickForm::createElement($fieldType,$fb->elementNamePrefix.$tag,$obj->fb_fieldLabels[$tag],$options);
            $el = HTML_QuickForm::createElement($fieldType, $fb->getFieldName($tag), $obj->fb_fieldLabels[$tag], $options);
            //fb->getFieldLabel($tag);
            //$el = HTML_QuickForm::createElement($fieldType,$fb->elementNamePrefix.$tag,$tag,array('size'=>2,'maxlenght'=>2));
            $obj->fb_preDefElements[$tag] = $el;
            //$v->fb_preDefElements['id_trasferte'] = HTML_QuickForm::createElement('hidden', $prefix.'id_trasferte', 'id_trasferte');
        }
    }
    //------------------------------------------------------------------------



    //-----------------------------------------------------------------------------
    public function updateFormGridFB($params)
    {
        extract($params);
        $form_data = array();
        reset($dataf);
        foreach ($dataf as $k => $v){
            $subject = $k;
            $pattern = '/form_([0-9]*)_(.*)/i';
            preg_match($pattern, $subject, $matches);
            //echo '<pre>'; print_r($matches); echo '</pre>';
            if (count($matches) < 2) {
                return;
            }
            $form_data[$matches[1]][$matches[2]] = $v;
        }
        //ddd('qui');
        //echo '<pre>'; print_r($form_data); echo '</pre>';
        $do = DB_DataObject::factory($tablename);
        $keys = $do->keys();
        //ARRAY_OP::print_x($form_data);
        //ddd('qui');
        reset($form_data);
        foreach ($form_data as $k => $v){
            $key = $v[$keys[0]];
            $do = DB_DataObject::factory($tablename);
            $do->get($key);
            //ARRAY_OP::print_x($v);
            //ddd('qui');
            $fields=array_keys($v);
            $fields_txt=array();
            reset($v);
            foreach ($v as $kv => $vv){
                if (is_string($vv)) {
                    $fields_txt[]=$kv;
                }
            }
            //-------------------------------------------------
            $do->fb_fieldsToRender=$fields;
            $fb = & DB_DataObject_FormBuilder::create($do);
            DO_OP::DataObjectCustomFields($do, $fb, $fields_txt, 'text'); // per evitare select che se ci son molti dati..
            //$fb->elementNamePrefix = 'form_'.$k.'_';
            $form = & $fb->getForm($_SERVER["REQUEST_URI"]);
            //ARRAY_OP::print_x($v);
            $form->setDefaults($v);
            //$form->display();
            $form->process(array( & $fb, 'processForm'), false);
            //ddd('qui');
        }
        //ddd('qui');
    }




    //-------------------------------------------------
    // per forzare l'aggiornamento da showtablenameeditable
    // funziona con tabelle con chiave singola
    //------------------------------------------------
    public function updateFormGrid($params)
    {
        ///*
        //echo '<h3>updateFormGrid</h3>';
        //ARRAY_OP::print_x($params);
        //ddd('qui');
        //*/
        //tablename
        //data
        extract($params);
        $form_data = array();
        reset($dataf);
        foreach ($dataf as $k => $v){
            $subject = $k;
            $pattern = '/form_([0-9]*)_(.*)/i';
            preg_match($pattern, $subject, $matches);
            //echo '<pre>'; print_r($matches); echo '</pre>';
            if (count($matches) < 2) {
                return;
            }
            $form_data[$matches[1]][$matches[2]] = $v;
        }
        //echo '<pre>'; print_r($form_data); echo '</pre>';
        $do = DB_DataObject::factory($tablename);
        $keys = $do->keys();
        //echo '<pre>'; print_r($keys); echo '</pre>';

        reset($form_data);
        foreach ($form_data as $k => $v){
            $key = $v[$keys[0]];
            //echo '<hr/>['.__FILE__.']['.__LINE__.']<br/>'.$key;
            $do = DB_DataObject::factory($tablename);
            $do->get($key);
            //echo '<pre>'; print_r($v); echo '</pre>';
            //$v = array_diff($v, array($keys[0] => $key));
            unset($v[$key]);
            reset($v);
            foreach ($v as $k1 => $v1){
                if (is_array($v1)) {
                    if (isset($v1['d']) && isset($v1['m']) && isset($v1['Y'])) {
                        if (isset($v1['H']) && isset($v1['i'])) {
                            $do->$k1 = $v1['Y'].'-'.$v1['m'].'-'.$v1['d'].' '.$v1['H'].':'.$v1['i'].':00';
                        } else {
                            $do->$k1 = $v1['Y'].'-'.$v1['m'].'-'.$v1['d'];
                        }
                    } else {
                        if (isset($v1['H']) && isset($v1['i'])) {
                            //echo '['.__FILE__.']['.__LINE__.']';echo '<pre>'; print_r($v1); echo '</pre>';
                            $do->$k1 = $v1['H'].':'.$v1['i'].':00';
                        //echo '<pre>'; print_r($k1);print_r($do->$k1);echo '</pre>';
                        } else {
                            //echo '['.__FILE__.']['.__LINE__.']<pre>'; print_r($v1); echo '</pre>';
                            ARRAY_OP::print_x($v1);
                        }
                    }
                } else {
                    $do->$k1 = $v1;
                }
                //echo '<br/>['.__FILE__.']['.__LINE__.'] '.$k1.' = '.$do->$k1;
            }
            $res=$do->update();
            //
        //echo '<pre>'; print_r($res); echo '</pre>';
        }
    }



    /////////////////////
    /* // funzione messa in array_lib
    function createTableFromArray($params) {
        global $mdb2;
        extract($params);
        //echo '<h3>DATA</h3><pre>';print_r($data);echo '</pre>';
        $tmp  = array_values($data);
        if (count($tmp) == 0) {
            echo '<h4>Array vuoto</h4>';
            return ;
        }
        //echo '<h3>TMP</h3><pre>';print_r($tmp);echo '</pre>';
        if(is_object($tmp[0])){
            $head = array_keys(get_object_vars($tmp[0]));
        }else {
            $head = array_keys($tmp[0]);
        }
        //echo '<h3>HEAD</h3><pre>';print_r($head);'</pre>';
        $sql='CREATE TABLE IF NOT EXISTS '.$tablename.' ('.implode(' VARCHAR (250),',$head).' VARCHAR (250));';
        //echo '<hr/><pre>['.$sql.']</pre><hr/>';
        $res = $mdb2->query($sql);
        if(isset($truncate) && $truncate==1){
            $sql = 'Truncate '.$tablename;
            $res = $mdb2->query($sql);
        }

        reset($data);
        foreach($data as $k => $v) {
            $fields = array();
            //echo '<hr/><pre>'.print_r($v).'</pre>';
            reset($v);
            foreach ($v as $k1 => $v1){
                $fields[] = $mdb2->quote($v1, 'text');
                //echo '<br/>['.$v1.']';
            }
            $sql = "INSERT INTO ".$tablename." (".implode(',', $head).")
                    VALUES (".implode(',',$fields)."); ";
            //echo '<hr/><pre>'.$sql.'</pre>';
            $res=$mdb2->query($sql);
        }

        echo '<h3>+Done</h3>';
    }
    */

    //------------//-----------------//-------------------
    public static function initOptions($params=array())
    {
        //echo __SITE_DIR.'/conf.php';

        //ddd('qui');
        global $registry;
        $area=$registry->menu->__get('areaCurr');
        //ddd($area);ddd('qui');

        $dbname='';
        if (isset($area->db)) {
            $dbname=$area->db;
        }
        //ddd($dbname);


        include(FILE_CONF);
        //ddd($conf['conf']['DB_DataObject']);


        // this  the code used to load and store DataObjects Configuration.
        $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
        //ddd($options);
        $options=$conf['conf']['DB_DataObject'];
        //ddd($options);


    // the simple examples use parse_ini_file, which is fast and efficient.
    // however you could as easily use wddx, xml or your own configuration array.
    //$config = parse_ini_file('example.ini',TRUE);
    /*
    $options= array(
    'database'          => 'mysql://'.DB_USER.':'.DB_PASS.'@'.DB_HOST.'/'.DB_NAME.'',
    'schema_location'   => __SITE_DIR.'/DataObjects',
    'class_location'    => __SITE_DIR.'/DataObjects',
    'require_prefix'    => 'DataObjects/',
    'class_prefix'      => 'DataObjects_',
    'db_driver'         => 'MDB2', //Use this if you wish to use MDB2 as the driver
    'quote_identifiers' => true
    );
    */
    //self::regenerate($params);

    // because PEAR::getstaticProperty was called with an & (get by reference)
    // this will actually set the variable inside that method (a quasi static variable)
    //$options = $config['DB_DataObject'];
    }

    //------------------------
    public static function regenerate($params=array())
    {
        require_once 'DB/DataObject/Generator.php';
        ini_set("memory_limit", "256M");
        ini_set("max_execution_time", "3150");
        //$_SERVER['argv'][1] = $this->conf->file_config; //"dbconfig.php";
        //require_once("DB/DataObject/createTables.php");
        echo '<pre>';
        DB_DataObject::debugLevel(5);
        $generator = new DB_DataObject_Generator;
        $generator->start();
        echo '</pre><hr/>';
        ddd('qui');
    }
    //-------------------------------------------------------------
    public function editListLinkedTable($params)
    {
        require_once 'HTML/QuickForm/advmultiselect.php';
        $params['dbname']=$this->registry->conf->__get('dbname');
        $linked=$this->getLinksIni2Array($params);
        $tmp=$linked[$params['tablename']];
        $table_field=$params['fieldlinked'];
        $tmp=$tmp->linked[$table_field];
        //ddd($tmp);
        $parz=$params;
        $parz['guid']='';
        unset($parz['guid']);

        $whereAdd=$this->getWhereAdd($parz);
        //ddd($whereAdd);
        $parz=['tablename'=>$params['tablename'],'whereAdd'=>$whereAdd];
        //ddd($parz);
        $data=DO_OP::fetchallX($parz);
        //ddd($data);
        //echo '<h3>'.$table_field.'</h3>';
        if (!isset($params[$table_field])) {
            $params[$table_field]=explode(',', $data[0]->$table_field);
        }
        /*
        $user_data = $this->registry->user->getUsers($params);
        $groups=$user_data[0]['groups'];
        $allGroups = $this->registry->user->__get('allGroups');
        */
        $all=DO_OP::fetchallX(['tablename'=>$tmp->linked_table]);
        $all=ARRAY_OP::arrayKeysX($all, 'id', 'nome');
        //ddd($all);

        //ddd($allGroups);
        //echo '<script type="text/javascript" src="/xot/js/qfamsHandler.js"></script>';
        $name=STRING_OP::slugify(__CLASS__.'_'.__FUNCTION__);
        $form = new HTML_QuickForm($name.'Form', 'post', $_SERVER['REQUEST_URI']);

        $form->setDefaults($params);

        //$form->setDefaults([$table_field=>['1'=>'1','2'=>'2']]);
        //$def=$form->getDefaults();
        //ddd($params);

        //$form->setDefaults($user_data[0]);
        //$form->addElement('text', 'auth_user_id', 'auth_user_id');
        //$form->addElement('text', 'perm_user_id', 'perm_user_id');
        $fieldstorender=explode(',', $params['fieldstorender']);
        $fieldstorender[]='tablename';
        reset($fieldstorender);
        foreach ($fieldstorender as $kfr => $vfr){
            //$form->addElement('text', $vfr, $vfr);
            $form->addElement('hidden', $vfr, $vfr);
        }
        //$form->addElement('text', 'tablename', 'tablename');
        //$form->addElement('text', 'id_tbl_lingua', 'id_tbl_lingua');
        //$idfield='id_'.$params['tablename'];
        //$form->addElement('text', $idfield, $idfield);

        $res=$form->addElement(
        'advmultiselect',
        $table_field,
        $table_field,
        $all,
                                    array('size' => 5,'style' => 'width:300px;')
                        );
        //$res->setValues([1,2]);
        if (!isset($_POST[$table_field])) {
            $res->_values=$params[$table_field];
        }
        //ddd($res);
        //ddd($this->registry->user->__get('myGroups'));

        $form->addElement('submit', null, 'Modifica ', 'class="btn btn-primary"');

        if ($form->validate()) {
            //$form->process(array($this,'do_editUserGroups'));
            /*
            echo '<h3>VALIDO!</h3>';
            if($form->isSubmitted()){
                echo '<h3>isSubmitted!</h3>';
            }else{
                echo '<h3>NOT isSubmitted!</h3>';
            }
            */
            $form->process(array($this,'do_editListLinkedTable'));
        } else {
        }

        $form->display();

        $scripts=array();
        $vendor_path=$this->registry->template->__get('vendor_path');
        $scripts[]=$vendor_path.'./pear-pear.php.net/HTML_QuickForm_advmultiselect/data/HTML_QuickForm_advmultiselect/HTML/QuickForm/qfamsHandler-min.js';
        $this->registry->template->addScripts($scripts);

        // $form = & $fb->getForm($_SERVER["REQUEST_URI"]);
        $form_id=$form->_attributes['id'];

        if (isset($_REQUEST['__ajaxCall']) && $_REQUEST['__ajaxCall']==1) { // da mettere solo POST !!!
            echo '<script>
		$(\'#'.$form_id.'\').submit(function(){
			var formData=$(this).serialize();
			var url3=$(this).attr(\'action\');
    		loadIContent(url3,"#icontent",formData);
    		return false;
		});

		$(\'.submit\').click(function(){
            var form=$(\'#'.$form_id.'\');
            var url3=form.attr(\'action\');
            var name=$(this).attr(\'name\');
            var formData=form.serialize();
            formData=formData+\'&__\'+name+\'=1\';
            //console.log(formData);
            //alert(url3);

            loadIContent(url3,"#icontent",formData);

            return false;
        })
		</script>';
            //....
            //$outHtml=str_replace('id="__submit__"','id="ajaxSubmit"',$outHtml); // da mettere poi il caso che ci siano piu' di 1 ...
            echo $this->registry->template->getScriptsHTML($params);
        }
    }

    public function do_editListLinkedTable($params)
    {
        if (count($params)<2) {
            return;
        }
        //ddd($params);
        //*
        $whereAdd=$this->getWhereAdd($params);
        //ddd($whereAdd);
        $parz=['tablename'=>$params['tablename'],'whereAdd'=>$whereAdd];
        //ddd($parz);
        $data=DO_OP::fetchallX($parz);
        //ddd($data);
        //*/
        $id=$data[0]->id;
        $data=['checkbox_tbl_service'=>implode(',', $params['checkbox_tbl_service'])];
        $parz=['tablename'=>$params['tablename'],'id'=>$id,'data'=>$data];
        DO_OP::update($parz);
    }

    //------------
}//end class
