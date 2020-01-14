<?php



class FORM_OP extends baseController
{
    //-------------------------------------------------------------------------------------------
    public function show($params)
    {
        $array = $params['array'];
        //$array=array_unique($array);
        \reset($array);
        $form = $this->__get('form');
        $def = ['array' => $array];

        $form->setDefaults($def);
        foreach ($array as $k => $v) {
            $form->addElement('text', 'array['.$k.']', $k);
            //echo '<br>ELEMENT['.$k.']';
        }
        $params['form'] = $form;
        if (!isset($params['process'])) {
            $tmp = \debug_backtrace();
            //ddd($tmp[1]);
        //ddd('qui');
        $params['process'] = [$tmp[1]['object'], 'do_'.$tmp[1]['function']]; //aspetto l'errore per correggere
        }

        self::submitandprocess($params);
        //$form->display();
    }

    //-----------//------------//---------------//---------------//-------------------------
    /*
    select stabi,repar,rdesc from REPA3L1
    where rdataf=0
    and ente=90
    and rdatai=(select max(rdatai) from REPA3L1)
    order by stabi,repar
    */

    public function form_stabi_repar($params)
    {
        global $registry;

        \extract($params);
        //$addempty=0;
        //$secempty=0;
        $form = new HTML_QuickForm('scegliDipendentiForm', 'POST', $_SERVER['REQUEST_URI']);
        //$stabi = 0;
        //$repar = 0;
        //$lista_gruppi = implode(',', $registry->user->myGroups());
        if (!isset($params['lista_gruppi'])) {
            $lista_gruppi = \implode(',', $registry->user->myGroups());
        } else {
            $lista_gruppi = $params['lista_gruppi'];
        }

        $form->setDefaults(['ente' => 90]);
        $form->addElement('header', null, 'REPARTO');
        $form->addElement('text', 'ente', 'ente');
        $parz = [
        'form' => $form, 'tablename' => 'repart', 'main_id' => 'stabi', 'main_name' => 'dest1', 'id_parent' => 'stabi', 'sec_id' => 'repar', 'sec_name' => 'dest1', 'main_sql' => 'and find_in_set(stabi,"'.$lista_gruppi.'") and stabi>=500', 'sec_sql' => '', 'main_orderby' => 'stabi desc', 'sec_orderby' => 'repar asc', 'field_name' => 'stabi_repar', 'field_label' => 'stabilimento/reparto',
        //,'addempty'=>0
        //,'secempty' => $secempty
        ];
        if (isset($params['addempty']) && 1 == $params['addempty']) {
            $parz['addempty'] = 1;
        }
        if (isset($params['secempty']) && 1 == $params['secempty']) {
            $parz['secempty'] = 1;
        }

        self::hierselectFromQuery($parz);

        if (isset($extra_el)) {
            \reset($extra_el);
            foreach ($extra_el as $k => $v) {
                $form->addElement($v);
            }
        }

        $form->addElement('submit', null, 'Filtro Stabi Repar');
        $form->display();
        if (!isset($process)) {
            $process = 'form_process';
        }
        if ($form->validate()) {
            $form->process($process);
        } else {
        }
    }

    //--------------------------------------------------------------------------------------
    public function form_stato_giorno_mese_anno_matr($params = [])
    {
        if (!isset($params['extra_el'])) {
            $params['extra_el'] = [];
        }
        $el = HTML_QuickForm::createElement('text', 'matr', 'matr');
        $params['extra_el'][] = $el;
        self::form_stato_giorno_mese_anno($params);
    }

    //--------------------------------------------------------------------------------------
    public function form_stato_giorno_mese_anno_ente_matr($params = [])
    {
        if (!isset($params['extra_el'])) {
            $params['extra_el'] = [];
        }
        $el = HTML_QuickForm::createElement('text', 'ente', 'ente');
        $params['extra_el'][] = $el;
        $el = HTML_QuickForm::createElement('text', 'matr', 'matr');
        $params['extra_el'][] = $el;

        self::form_stato_giorno_mese_anno($params);
    }

    //-------------------------------------------------------------------------------------
    public function form_stato_dal_al_matr($params = null)
    {
        if (!isset($params['extra_el'])) {
            $params['extra_el'] = [];
        }
        $el = HTML_QuickForm::createElement('text', 'ente', 'ente');
        $params['extra_el'][] = $el;
        $el = HTML_QuickForm::createElement('text', 'matr', 'matr');
        $params['extra_el'][] = $el;
        self::form_stato_dal_al($params);
    }

    //-------------------------------------------------------------------------------------
    public function form_stato_dal_al($params = null)
    {
        $form = new HTML_QuickForm('stato_giorno_mese_anno', 'POST', $_SERVER['REQUEST_URI']);
        $defaults = [];
        $stato_array = ['7' => '[7]chiuse', '8' => '[8]liquidate', '77' => '[77] inserito in assenze txt'];
        if (null != $params) {
            //echo '<pre>';print_r($params); echo '</pre>';
            \extract($params);
        }
        $defaults['dal'] = \date('Y').'-01-01';
        $defaults['al'] = \date('Y').'-06-01';
        $defaults = \array_merge(['anno' => \date('Y')], $defaults);
        $tipodata_array = ['1' => 'competenza', '2' => 'azione'];
        //ARRAY_OP::print_x($defaults);
        $form->setDefaults($defaults);
        $form->addElement('select', 'stato', 'stato', $stato_array);

        $form->addElement('jquery_date', 'dal', 'dal');
        $form->addElement('jquery_date', 'al', 'al');

        $form->addElement('advcheckbox', 'updateField', 'Aggiorna i campi (lento) ');
        $form->addElement('advcheckbox', 'showxls', 'crea xls ?');
        $form->addElement('select', 'tipo_data', 'tipo data', $tipodata_array);

        if (isset($extra_el)) {
            \reset($extra_el);
            foreach ($extra_el as $k => $v) {
                $form->addElement($v);
            }
        }

        $form->addElement('submit', null, 'vai !');
        $form->display();
        if (!isset($process)) {
            $process = 'form_process';
        }
        if ($form->validate()) {
            $form->process($process);
        } else {
        }
    }

    //-------------------------------------------------------------------------------------
    public function form_stato_giorno_mese_anno($params = null)
    {
        $form = new HTML_QuickForm('stato_giorno_mese_anno', 'POST', $_SERVER['REQUEST_URI']);
        $defaults = [];
        $stato_array = ['7' => '[7]chiuse', '8' => '[8]liquidate', '77' => '[77] inserito in assenze txt'];
        if (null != $params) {
            //echo '<pre>';print_r($params); echo '</pre>';
            \extract($params);
        }
        if (isset($params['lista_stati_array'])) {
            $stato_array = $params['lista_stati_array'];
        }

        $anno_array = [];
        for ($anno = 2009; $anno <= \date('Y'); ++$anno) {
            $anno_array[$anno] = $anno;
        }

        $tipodata_array = ['1' => 'competenza', '2' => 'azione'];

        $mese_array = [null => '-------', 1 => 'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];
        $defaults = \array_merge(['anno' => \date('Y')], $defaults);
        //echo '<pre>';print_r($defaults); echo '</pre>';
        $form->setDefaults($defaults);
        $form->addElement('select', 'stato', 'stato', $stato_array);
        $form->addElement('text', 'giorno', 'giorno');
        $form->addElement('select', 'mese', 'mese', $mese_array);
        $form->addElement('select', 'anno', 'anno', $anno_array);

        $form->addElement('advcheckbox', 'updateField', 'Aggiorna i campi (lento) ');
        $form->addElement('advcheckbox', 'showxls', 'crea xls ?');
        $form->addElement('select', 'tipo_data', 'tipo data', $tipodata_array);

        if (isset($extra_el)) {
            \reset($extra_el);
            while (list($k, $v) = \each($extra_el)) {
                $form->addElement($v);
            }
        }

        $form->addElement('submit', null, 'vai !');
        $form->display();
        if (!isset($process)) {
            $process = 'form_process';
        }
        if ($form->validate()) {
            $form->process($process);
        } else {
        }
    }

    //-------------------------------------------------------------------------------------
    public function form_stabi_repar_anno($params = null)
    {
        global $registry;
        $process = 'form_process';
        \extract($params);
        $form = new HTML_QuickForm('scegliDipendentiForm', 'POST', $_SERVER['REQUEST_URI']);
        //$stabi = 0;
        //$repar = 0;

        if (isset($params['allStabi'])) {
            $lista_gruppi = '%';
        } else {
            $lista_gruppi = \implode(',', $registry->user->myGroups());
        }
        $form->setDefaults(['ente' => 90, 'anno' => (\date('Y') - 1)]);
        if (isset($params['defaults'])) {
            $form->setDefaults($params['defaults']);
        }

        $form->addElement('header', null, 'REPARTO');
        $form->addElement('text', 'ente', 'ente');
        $parz = [
        'form' => $form, 'tablename' => 'repart', 'main_id' => 'stabi', 'main_name' => 'dest1', 'id_parent' => 'stabi', 'sec_id' => 'repar', 'sec_name' => 'dest1', 'main_sql' => 'and find_in_set(stabi,"'.$lista_gruppi.'") and stabi>=500', 'sec_sql' => '', 'main_orderby' => 'stabi desc', 'sec_orderby' => 'repar asc', 'field_name' => 'stabi_repar', 'field_label' => 'stabilimento/reparto'
        //,'addempty'=>1
        , 'secempty' => 1,
        ];
        if (isset($params['allStabi'])) {
            $parz['main_sql'] = 'and stabi>=500';
        }
        if (isset($params['addempty'])) {
            $parz['addempty'] = $params['addempty'];
        }

        self::hierselectFromQuery($parz);
        $form->addElement('text', 'anno', 'anno');
        $form->addElement('submit', null, 'Filtro Stabi Repar');
        $form->display();
        if ($form->validate()) {
            $form->process($process);
        } else {
        }
    }

    //-------------------------------------------------------------------------------------

    public function form_stabi_repar1($process)
    {
        global $registry;

        $form = new HTML_QuickForm('scegliDipendentiForm', 'POST', $_SERVER['REQUEST_URI']);
        $stabi = 0;
        $repar = 0;
        //if (isset($_SESSION['stabi'])) $stabi = $_SESSION['stabi'];
        //if (isset($_SESSION['repar'])) $repar = $_SESSION['repar'];
        if (isset($_REQUEST['stabi'])) {
            $stabi = $_REQUEST['stabi'];
        }
        if (isset($_REQUEST['repar'])) {
            $repar = $_REQUEST['repar'];
        }

        $form->setDefaults(['ente' => 90, 'stabi_repar' => [$stabi, $repar]]);
        $form->addElement('header', null, 'REPARTO');
        $form->addElement('text', 'ente', 'ente');
        //$form->addElement('text', 'repar', 'REPARTO');

        $sel = &$form->addElement('hierselect', 'stabi_repar', 'Stabilimento/Reparto:', null, '/');
        //$sql = 'select pkparent, par_desc from parent';
        /*
        $sql = 'select stabi as id, dest1 as nome from generale.repart where repar=0 and stabi>=500';
        $mainOptions = array();
        $tmp=$mdb2->queryAll($sql);
        while (list($k, $v) = each ($tmp)) {
            $mainOptions[$v->id]=$v->id.'] '.$v->nome;
        }
        */
        //print_r($registry->user->myGroups());
        $lista_gruppi = \implode(',', $registry->user->myGroups());

        $mainOptions = [];
        $secOptions = [];
        //echo '<br/>'.__LINE__.' - '.__FILE__;
        $main_do = DB_DataObject::factory('repart');
        //echo '<pre>'; print_r($main_do); echo '</pre>';
        //echo '<br/>'.__LINE__.' - '.__FILE__;
        $main_do->selectAdd('stabi as id, dest1 as nome');
        //$main_do->whereAdd('repar=0 and stabi>=400');
        $main_do->whereAdd('repar=0 and find_in_set(stabi,"'.$lista_gruppi.'") and stabi>=500');
        //echo '<br/>'.__LINE__.' - '.__FILE__;
        $tmp = $main_do->fetchAll();
        while (list($k, $v) = \each($tmp)) {
            $mainOptions[$v->id] = $v->id.'] '.$v->nome;
        }
        //echo '<br/>'.__LINE__.' - '.__FILE__;
        /*
        $sql = "select fk_parent, pkchild, chi_desc from child";
        $sql='select stabi as id_parent,repar as id,dest1 as nome from generale.repart where repar!=0 and stabi>=500';
        $tmp = $mdb2->queryAll($sql);
        $secOptions=array();
        while (list($k, $v) = each ($tmp)) {
            $secOptions[$v->id_parent][$v->id]=$v->id.'] '.$v->nome;
        }
        */
        $sec_do = DB_DataObject::factory('repart');
        $sec_do->selectAdd('stabi as id_parent,repar as id,dest1 as nome');
        $sec_do->whereAdd('repar!=0 and stabi>=400');
        $tmp = $sec_do->fetchAll();
        while (list($k, $v) = \each($tmp)) {
            $secOptions[$v->id_parent][$v->id] = $v->id.'] '.$v->nome;
        }

        // Using setMainOptions and setSecOptions is now deprecated
        // use setOptions.

        $sel->setOptions([$mainOptions, $secOptions]);

        $form->addElement('submit', null, 'Filtro Stabi Repar');
        $form->display();
        if ($form->validate()) {
            $form->process($process);
        } else {
        }
    }

    //-----------------------------------------------------------------------------------
    //-- passando la tabella m-n ti fa selezionare
    public function selectMultiTableMN($params)
    {
        \extract($params);
        //whereAdd = 'perm_user_id= '.$perm_user_id;
        //fieldname = 'areas'
        //fieldlabel = 'Aree'
        //echo '<h3>selectMultiTableMN</h3>';
        //tablemn = 'liveuser_area_admin_areas'
        //tablem = 'liveuser_areas'
        //tablem_label = 'area_define_name'
        //external_key =
        //-------------------------------------------
        $do = DB_DataObject::factory($tablem);
        $keys = $do->keys();
        $primary_key = $keys[0];
        //$do->joinAdd($do1);
        //$do->whereAdd('perm_user_id= '.$perm_user_id);
        $allAreas = $do->fetchAll();
        //echo '<h3>'.count($allAreas).'</h3><pre>'; print_r($allAreas); echo '</pre>';
        $allAreas = ARRAY_OP::arrayKeysX($allAreas, $primary_key, $tablem_label);
        //-------------------------------------------------------------------------

        $do = DB_DataObject::factory($tablemn);
        //$do1 = DB_DataObject::factory('liveuser_areas');
        //$do->joinAdd($do1);
        //$do->whereAdd('perm_user_id= '.$perm_user_id);
        $do->whereAdd($whereAdd);
        $areas = $do->fetchAll();
        if (!isset($external_key)) {
            $external_key = $primary_key;
        }
        $areas = ARRAY_OP::arrayKeysX($areas, $external_key);
        //echo '<pre>'; print_r($areas); echo '</pre>';
        //$do = DB_DataObject::factory('liveuser_area_admin_areas');

        //echo '<script type="text/javascript" src="/xot/js/qfamsHandler.js"></script>';
        echo '<script type="text/javascript" src="/PEAR/PEAR/HTML/QuickForm/qfamsHandler.js"></script>';
        $form->setDefaults([$fieldname => $areas]);

        /*$el=$form->addElement('advmultiselect', $fieldname, $fieldlabel, $allAreas,
                                        array('size' => 5,'style' => 'width:300px;')
                            );
                            */
        $el = &HTML_QuickForm::createElement(
        'advmultiselect',
        $fieldname,
        $fieldlabel,
        $allAreas,
                                    ['size' => 5, 'style' => 'width:300px;']
                        );

        $form->addElement($el);
        //echo htmlspecialchars($el1->getElementJs(true));
    }

    //-----------//-----------//-----------//-----------//-----------//-----------//-----------

    //---------------------------------------------------------------------------
    public function hierselectFromQuery($params)
    {
        \extract($params);
        //$tablename
        //$main_id
        //$sec_id
        //$main_name
        //$sec_name
        //$main_sql
        //$sec_sql
        //$field_name
        //$field_label

        $mainOptions = [];
        $secOptions = [];

        $main_do = DB_DataObject::factory($tablename);
        if (PEAR::isError($main_do)) {
            echo '
		<b> tablename </b>'.$tablename.'<br/>
		<b> MSG: </b>'.$main_do->getMessage().'<br />
		<b> Info :</b>'.$main_do->getDebugInfo();

            ddd('qui');
        }

        //$main_do->selectAdd('stabi as id, dest1 as nome');
        $main_do->selectAdd(''.$main_id.' as id, '.$main_name.' as nome');
        //$main_do->whereAdd('repar=0 and find_in_set(stabi,"'.$lista_gruppi.'")');
        $main_do->whereAdd(''.$sec_id.'=0 '.$main_sql);
        $main_do->orderBy($main_orderby);
        $tmp = $main_do->fetchAll();
        if (isset($addempty)) {
            $mainOptions[0] = '--seleziona--';
        }
        while (list($k, $v) = \each($tmp)) {
            $mainOptions[$v->id] = $v->id.'] '.$v->nome;
            if (isset($secempty)) {
                $secOptions[0][0] = '----';
                $secOptions[$v->id][0] = '----';
            }
        }
        //--------------------------------------------------------------------------
        $sec_do = DB_DataObject::factory('repart');
        $sec_do->selectAdd(''.$id_parent.' as id_parent,'.$sec_id.' as id,'.$sec_name.' as nome');
        $sec_do->whereAdd(''.$sec_id.'!=0 '.$sec_sql);
        $sec_do->orderBy($sec_orderby);
        $tmp = $sec_do->fetchAll();
        if (isset($addempty)) {
            $secOptions[][] = '--seleziona--';
        }
        while (list($k, $v) = \each($tmp)) {
            $secOptions[$v->id_parent][$v->id] = $v->id.'] '.$v->nome;
        }

        // Using setMainOptions and setSecOptions is now deprecated
        // use setOptions.
        $sel = &$form->addElement('hierselect', $field_name, $field_label, null, '/');
        $sel->setOptions([$mainOptions, $secOptions]);
        //print_r($sel);

    //return $sel;
    }

    public function form_process($params)
    {
        global $registry;
        //ARRAY_OP::print_x($params);
        //ARRAY_OP::print_x($registry->template->vars);
        /*
        $azioniFromMenu=$registry->template->__get('azioniFromMenu');
        if(!isset($azioniFromMenu[0])){

            $azioniFromMenu=$registry->template->__get('azioniFromMenu');
            //ARRAY_OP::print_x($azioniFromMenu);
            //ddd('qui');
        }
        */
        //$registry->menu->deleteSessionMenu($params);
        //$menuCurr=$registry->menu->getMenuCurr($params);

        $menuCurr = $registry->menu->__get('menuCurr');
        //ddd($menuCurr);ddd('qui');

        $menuFigli = $registry->menu->__get('menuFigli');
        $menuFigli = ARRAY_OP::array_sort($menuFigli, 'id', SORT_DESC);
        $menuFigli = \array_values($menuFigli);

        if (!isset($menuFigli[0])) {
            $parz = ['obj' => $registry->template->__get('obj'),
            'act' => 'do_'.$registry->template->__get('act'),
            'nome' => 'do '.$registry->template->__get('nome'),
            'id_padre' => $registry->template->__get('id'),
        ];
            //echo '<pre>';print_r($parz)	;echo '</pre>';ddd('qui');
            //ddd('qui');
            $id = $registry->menu->do_nuovo(['data' => $parz]);
        } else {
            $id = $menuFigli[0]->id;
        }
        //ddd($menuFigli);
        //ddd($id);
        //ddd('qui');

        //ARRAY_OP::print_x($id);ddd('qui');
        //echo '<h3>'.$id.'</h3>';
        $parz = ARRAY_OP::array_inline($params);
        $vals = \implode('/', $parz);
        $keys = \implode(',', \array_keys($parz));
        //echo '<pre>';print_r($vals);echo '</pre>';
        //echo '<pre>';print_r($keys);echo '</pre>';
        //ddd('qui');

        //$querystr_keys=ARRAY_OP::implode_recorsive(',',array_keys($params));
        $querystr_keys = $keys;
        //echo '<pre>'.$querystr_keys.'</pre>';ddd('qui');
        XMLMENU_OP::updateXMLMenuFields(['id_menu' => $id, 'data' => ['querystr_keys' => $querystr_keys]]);

        //$_SESSION['form_values']=$params;
        $uri = $_SERVER['REQUEST_URI'].'&rt=menu/gest/'.$id.'/'.$vals;
        JS_OP::redirectJs($uri);
    }

    //-----------------------------------------------------------------------------------
    public function form_process_old($params)
    {
        global $registry;
        if (!isset($registry->template->azioniFromMenu[0])) {
            //echo '<pre>';print_r($registry); echo '</pre>';ddd('qui');
            /*
            echo '<br>'.$registry->router->__get('controller');
            echo '<br>'.$registry->router->__get('action');
            echo '<br>'.$registry->template->nome;
            */
            $parz = ['obj' => $registry->router->controller,
            'act' => 'do_'.$registry->router->__get('action'),
            'nome' => 'do '.$registry->template->nome,
            'id_padre' => $registry->template->id, ];
            //echo '<pre>';print_r($parz)	;echo '</pre>';ddd('qui');
            $id = $registry->router->esegui('menu', 'nuovo', $parz);
        //ddd('qui');
        } else {
            $id = $registry->template->azioniFromMenu[0]->id;
        }
        //if($id==null) ddd('qui');
        //echo '<pre>';print_r($params); echo '</pre>';
        $parz = ARRAY_OP::array_inline($params);
        $vals = \implode('/', $parz);
        $keys = \implode(',', \array_keys($parz));
        //echo '<pre>';print_r($vals);echo '</pre>';
        //echo '<pre>';print_r($keys);echo '</pre>';
        //ddd('qui');

        //$querystr_keys=ARRAY_OP::implode_recorsive(',',array_keys($params));
        $querystr_keys = $keys;
        //echo '<pre>'.$querystr_keys.'</pre>';ddd('qui');
        XMLMENU_OP::updateXMLMenuFields(['id_menu' => $id, 'data' => ['querystr_keys' => $querystr_keys]]);

        //$_SESSION['form_values']=$params;
        $uri = $_SERVER['REQUEST_URI'].'&rt=menu/gest/'.$id.'/'.$vals;
        JS_OP::redirectJs($uri);
    }

    //------------------------------------------------------------------------------------
    public function form_sceltaVeloceMeseAnnoOLD($params = null)
    {
        if (null != $params) {
            \extract($params);
        }
        $html = '';
        //$html.= '<fieldset><legend>Scelta Veloce</legend>';
        for ($i = 0; $i < 5; ++$i) {
            $mytime = \mktime(0, 0, 0, \date('m') - $i + 1, \date('d'), \date('Y'));
            if (isset($menu_id)) {
                $html .= '<form action="?rt=menu/gest/'.$menu_id.'" method="post" style="float:left;">';
            } else {
                $html .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" style="float:left;">';
            }
            $html .= '<input type="hidden" name="mese" value="'.\date('m', $mytime).'" />
		<input type="hidden" name="anno" value="'.\date('Y', $mytime).'" />
		<input type="submit" value="'.\ucfirst(\strftime('%B %Y', $mytime)).'"/>
		</form > ';
        }
        //$html.= '</fieldset>';
        echo $html.'<br style="clear:both"/>';
    }

    public function form_sceltaVeloceMeseAnnoTEST1($params = null)
    {
        if (null != $params) {
            \extract($params);
        }
        for ($i = 0; $i < 3; ++$i) {
            $time = \mktime(0, 0, 0, \date('m') - $i, \date('d'), \date('Y'));
            $form = new HTML_QuickForm('scelta'.$i, 'post', $_SERVER['REQUEST_URI']);
            $form->setConstants([
            'anno' => \date('Y', $time) * 1,
            'mese' => \date('m', $time) * 1,
            ]);
            $form->addElement('text', 'mese', 'mese');
            $form->addElement('text', 'anno', 'anno');
            /*
            if (isset($extra_el)) {
                reset($extra_el);
                while (list($k, $v) = each($extra_el)) {
                    $form->addElement($v);
                }
            }
            */
            $txt = \ucfirst(\strftime('%B %Y', $time));
            $form->addElement('submit', null, $txt);
            echo '<div style="float:left">'.$form->toHtml().'</div>';
        }
        echo '<br style="clear:both"/>';
    }

    public function form_sceltaVeloceMeseAnno($params = null)
    {
        echo '<div class="btn-group">';
        for ($i = 0; $i < 5; ++$i) {
            $time = \mktime(0, 0, 0, \date('m') - $i, \date('d'), \date('Y'));
            $txt = \ucfirst(\strftime('%B %Y', $time));
            $mese = \date('m', $time) * 1;
            $anno = \date('Y', $time) * 1;
            echo "<button class=\"btn  btn-default\" onclick=\"$('#mese option[value=".$mese."]').prop('selected',true);$('#anno option[value=".$anno."]').prop('selected',true);$('form').submit();\"  data-toggle=\"button\">".$txt.'</button>';
        }
        echo '</div>';
        echo '<br style="clear:both"/>';
    }

    //---------------//--------------//---------------//---------------//--------------//---------------
    public function form_MeseAnno($params = null)
    {
        // 	setlocale(LC_ALL, 'ita');

        if (null != $params) {
            \extract($params);
        }
        if (!isset($process)) {
            $process = 'form_process';
        }

        $form = new HTML_QuickForm('scegliDipendentiForm', 'POST', $_SERVER['REQUEST_URI']);
        $form->setDefaults(['ente' => 90, 'anno' => (\date('Y') * 1), 'mese' => \date('m') - 1, ]);
        if (isset($defaults)) {
            $form->setDefaults($defaults);
        }

        $form->addElement('header', null, 'MESE/ANNO');

        if (isset($extra_el)) {
            \reset($extra_el);
            while (list($k, $v) = \each($extra_el)) {
                $form->addElement($v);
            }
        }

        $mese_array = [];
        for ($i = 1; $i <= 12; ++$i) {
            $mese_array[$i] = \ucfirst(\strftime('%B', \mktime(12, 0, 0, $i, \date('d'), \date('Y'))));
        }
        $mese_array[2] = 'Febbraio';
        //echo '<pre>';print_r($mese_array); echo '</pre>';

        $anno_array = [];
        for ($i = 2009; $i <= \date('Y') + 1; ++$i) {
            $anno_array[$i] = $i;
        }
        $form->addElement('select', 'mese', 'mese', $mese_array, ['id' => 'mese']);
        $form->addElement('select', 'anno', 'anno', $anno_array, ['id' => 'anno']);

        //$form->addElement('text', 'mese', 'mese');
        //$form->addElement('text', 'anno', 'anno');
        $form->addElement('submit', null, 'Vai !');
        $form->display();
        if ($form->validate()) {
            //echo '<h3>POST</h3><pre>'; print_r($_POST); echo '</pre>';
            //$params = $form->exportValues();
            $params = $form->getSubmitValues();
            //echo '['.__FILE__.']['.__LINE__.']<pre>'; print_r($params); echo '</pre>';
            if (isset($params['mese']) && isset($params['anno'])) {
                $mytime = \mktime(0, 0, 0, $params['mese'], 1, $params['anno']);
                echo '<h3>'.\ucfirst(\strftime('%B %Y', $mytime));
                echo '</h3>';
            }
            $form->process($process);
        } else {
        }
    }

    public function form_Anno($params)
    {
        //$form = new HTML_QuickForm('scegliDipendentiForm', 'POST', $_SERVER["REQUEST_URI"]);
        if (!isset($params['process'])) {
            $params['process'] = 'form_process';
        }
        if (!isset($params['defaults'])) {
            $params['defaults'] = [];
        }
        $anno_array = [];
        for ($i = 2009; $i <= \date('Y') + 1; ++$i) {
            $anno_array[$i] = $i;
        }
        $form = $this->__get('form');
        $def = ['anno' => \date('Y') - 1];
        $def = \array_merge($def, $params['defaults']);

        $form->setDefaults($def);

        $form->addElement('header', null, 'ANNO');
        $form->addElement('select', 'anno', 'anno', $anno_array, ['id' => 'anno']);
        $form->addElement('submit', null, 'Vai !');
        $form->display();
        if ($form->validate()) {
            $form->process($params['process']);
        }
    }

    public function form_MeseAnnoEnte($params)
    {
        $el = [];
        $el[] = HTML_QuickForm::createElement('text', 'ente', 'ente');
        $params['extra_el'] = $el;
        self::form_MeseAnno($params);
    }

    public function form_EnteAnnoMatr($params)
    {
        $el = [];
        $el[] = HTML_QuickForm::createElement('text', 'matr', 'matr');
        //$parz = array('extra_el' => $el,'process'=>'form_process');
        $params['extra_el'] = $el;

        return self::form_EnteAnno($params);
    }

    //---------------//--------------//---------------//---------------//--------------//---------------
    public function form_EnteAnno($params = null)
    {
        // 	setlocale(LC_ALL, 'ita');

        if (null != $params) {
            \extract($params);
        }
        if (!isset($process)) {
            $process = 'form_process';
        }

        $form = new HTML_QuickForm('scegliDipendentiForm', 'POST', $_SERVER['REQUEST_URI']);
        $form->setDefaults(['ente' => 90, 'anno' => (\date('Y') * 1), 'mese' => \date('m') - 1, ]);
        if (isset($defaults)) {
            $form->setDefaults($defaults);
        }
        $form->addElement('header', null, 'ENTE/ANNO');

        if (isset($extra_el)) {
            \reset($extra_el);
            while (list($k, $v) = \each($extra_el)) {
                $form->addElement($v);
            }
        }

        $anno_array = [];
        for ($i = 2009; $i <= \date('Y') + 1; ++$i) {
            $anno_array[$i] = $i;
        }
        $form->addElement('text', 'ente', 'ente');
        $form->addElement('select', 'anno', 'anno', $anno_array, ['id' => 'anno']);

        $form->addElement('submit', null, 'Vai !');
        $form->display();
        if ($form->validate()) {
            $form->process($process);
        }
    }

    //---------------//--------------//---------------//---------------//--------------//---------------
    public function form_EnteCognomeAnno($params = null)
    {
        // 	setlocale(LC_ALL, 'ita');

        if (null != $params) {
            \extract($params);
        }
        if (!isset($process)) {
            $process = 'form_process';
        }

        $form = new HTML_QuickForm('scegliDipendentiForm', 'POST', $_SERVER['REQUEST_URI']);
        $form->setDefaults(['ente' => 90, 'anno' => (\date('Y') * 1), 'mese' => \date('m') - 1, ]);
        if (isset($defaults)) {
            $form->setDefaults($defaults);
        }
        $form->addElement('header', null, ' ');

        if (isset($extra_el)) {
            \reset($extra_el);
            while (list($k, $v) = \each($extra_el)) {
                $form->addElement($v);
            }
        }

        $anno_array = [];
        for ($i = 2009; $i <= \date('Y') + 1; ++$i) {
            $anno_array[$i] = $i;
        }
        $form->addElement('text', 'ente', 'ente');
        $form->addElement('text', 'cognome', 'cognome');
        $form->addElement('text', 'nome', 'nome');
        $form->addElement('select', 'anno', 'anno', $anno_array, ['id' => 'anno']);

        $form->addElement('submit', null, 'Vai !');
        $form->display();
        if ($form->validate()) {
            $form->process($process);
        }
    }

    //--------------------------------------------------------------------------
    public function form_MeseAnnoStati($params = null)
    {
        if (null != $params) {
            \extract($params);
        }
        if (!isset($process)) {
            $process = 'form_process';
        }
        if (!isset($lista_stati_array)) {
            $lista_stati_array = [];
            //$lista_stati_array['1,2,3,4,5'] = 'da liquidare';
            $lista_stati_array['77'] = 'da liquidare';
            $lista_stati_array['7'] = 'in liquidazione';
            $lista_stati_array['8'] = 'liquidate';
        }
        $el = [];
        $el[] = HTML_QuickForm::createElement('select', 'lista_stati', 'lista_stati', $lista_stati_array);
        $parz = ['extra_el' => $el, 'process' => $process];
        self::form_sceltaVeloceMeseAnno($parz);
        self::form_MeseAnno($parz);
    }

    //-----------------------------------------------------------------------------
    public function form_DalAlStati($params = null)
    {
        // 	setlocale(LC_ALL, 'ita');
        if (null != $params) {
            \extract($params);
        }
        if (!isset($process)) {
            $process = 'form_process';
        }
        $form = new HTML_QuickForm('scegliDipendentiForm', 'POST', $_SERVER['REQUEST_URI']);
        $form->setDefaults([
        'dal' => ['d' => 31, 'm' => 5, 'Y' => (\date('Y') - 1)], 'al' => ['d' => 31, 'm' => 5, 'Y' => \date('Y')], 'lista_stati' => '8,81', ]);
        $form->addElement('jquery_date', 'dal', 'dal');
        $form->addElement('jquery_date', 'al', 'al');
        $form->addElement('text', 'lista_stati', 'lista_stati');
        $form->addElement('checkbox', 'meseintero', 'mese intero ?<br/> (prende in considerazione il mese del dal )');
        $form->addElement('checkbox', 'showxls', 'show xls?');
        //$form->addElement('text', 'matr', 'lista matricole da escludere (separate con ",")');
        $form->addElement('submit', null, 'Vai !');

        if ($form->validate()) {
            $form->process($process);
        } else {
        }
        $form->display();
    }

    public function formAddExtraEl($params)
    {
        \extract($params);
        if (isset($extra_el)) {
            \reset($extra_el);
            while (list($k, $v) = \each($extra_el)) {
                $form->addElement($v);
            }
        }

        return $form;
    }

    public function form_EnteMatrDalAl($params = null)
    {
        $process = 'form_process';
        if (null != $params) {
            \extract($params);
        }
        $el = [];
        $el[] = HTML_QuickForm::createElement('text', 'ente', 'ente');
        $el[] = HTML_QuickForm::createElement('text', 'matr', 'matr');
        $el[] = HTML_QuickForm::createElement('text', 'conome', 'cognome');
        $el[] = HTML_QuickForm::createElement('text', 'nome', 'nome');
        $el[] = HTML_QuickForm::createElement('html5_date', 'dal', 'dal');
        $el[] = HTML_QuickForm::createElement('html5_date', 'al', 'al');
        //$parz = array('extra_el' => $el,'process'=>'form_process');
        $params['extra_el'] = $el;
        $params['form'] = new HTML_QuickForm('scegliDipendentiForm', 'POST', $_SERVER['REQUEST_URI']);
        $form = self::formAddExtraEl($params);
        $form->addElement('submit', null, 'Vai !');

        if ($form->validate()) {
            $form->process($process);
        } else {
        }
        $form->display();
    }

    public function form_EnteMatrNome($params = null)
    {
        $process = 'form_process';
        if (null != $params) {
            \extract($params);
        }
        $el = [];
        $el[] = HTML_QuickForm::createElement('text', 'ente', 'ente');
        $el[] = HTML_QuickForm::createElement('text', 'matr', 'matr');
        $el[] = HTML_QuickForm::createElement('text', 'conome', 'cognome');
        $el[] = HTML_QuickForm::createElement('text', 'nome', 'nome');
        //$parz = array('extra_el' => $el,'process'=>'form_process');
        $params['extra_el'] = $el;
        $params['form'] = new HTML_QuickForm('scegliDipendentiForm', 'POST', $_SERVER['REQUEST_URI']);
        $form = self::formAddExtraEl($params);
        $form->addElement('submit', null, 'Vai !');

        if ($form->validate()) {
            $form->process($process);
        } else {
        }
        $form->display();
    }

    public function form_EnteMatr($params = null)
    {
        $process = 'form_process';
        if (null != $params) {
            \extract($params);
        }
        $el = [];
        $el[] = HTML_QuickForm::createElement('text', 'ente', 'ente');
        $el[] = HTML_QuickForm::createElement('text', 'matr', 'matr');
        //$parz = array('extra_el' => $el,'process'=>'form_process');
        $params['extra_el'] = $el;
        $params['form'] = new HTML_QuickForm('scegliDipendentiForm', 'POST', $_SERVER['REQUEST_URI']);
        $form = self::formAddExtraEl($params);
        $form->addElement('submit', null, 'Vai !');

        if ($form->validate()) {
            $form->process($process);
        } else {
        }
        $form->display();
    }

    public function form_EnteMatrMeseAnno($params = null)
    {
        if (null != $params) {
            \extract($params);
        }
        $el = [];
        $el[] = HTML_QuickForm::createElement('text', 'ente', 'ente');
        $el[] = HTML_QuickForm::createElement('text', 'matr', 'matr');
        $parz = ['extra_el' => $el, 'process' => 'form_process'];
        self::form_sceltaVeloceMeseAnno($parz);
        self::form_MeseAnno($parz);
    }

    public function addCaptcha($params)
    {
        \extract($params);
        //require_once 'HTML/QuickForm.php';
        require_once 'HTML/QuickForm/CAPTCHA/Equation.php';
        require_once 'HTML/QuickForm/CAPTCHA/Figlet.php';
        require_once 'HTML/QuickForm/CAPTCHA/Image.php';
        require_once 'HTML/QuickForm/CAPTCHA/Word.php';

        $options = ['sessionVar' => \basename(__FILE__, '.php'),
                 'options' => ['font_file' => [
                                       //  $_SERVER['DOCUMENT_ROOT'].'/fonts/figlet/basic.flf',
                                         $_SERVER['DOCUMENT_ROOT'].'/fonts/figlet/big.flf',
                                       //  $_SERVER['DOCUMENT_ROOT'].'/fonts/figlet/nancyj.flf',
                         ]],
                 'width' => 250,
                 'height' => 90,

                 'callback' => 'qfcaptcha_image.php?var='
                                   .\basename(__FILE__, '.php'),
                 'imageOptions' => [
                     'font_size' => 20,
                     'font_path' => $_SERVER['DOCUMENT_ROOT'].'/fonts/',
                     'font_file' => 'DejaVuSans.ttf', ],
    ];

        // Pick a random CAPTCHA driver
$drivers = [//'CAPTCHA_Word',
                // 'CAPTCHA_Image',
                 'CAPTCHA_Equation',
                // 'CAPTCHA_Figlet',
                ];
        $captcha_type = $drivers[\array_rand($drivers)];

        // Create the CAPTCHA element:
        ///*
        $captcha_question = &$form->addElement(
    $captcha_type,
    'captcha_question',
                                       'domanda:',
    $options
);
        if (PEAR::isError($captcha_question)) {
            echo $captcha_type.' :: '.$captcha_question->getMessage();
            exit;
        }
        //*/
        //*
        $captcha_answer = &$form->addElement('text', 'captcha', 'inserisci la risposta');

        $form->addRule('captcha', 'Enter your answer', 'required', null, 'server');

        $form->addRule(
    'captcha',
    'What you entered didn\'t match. Try again.',
               'CAPTCHA',
    $captcha_question
);
        //*/
        return $form;
    }

    //------------//-----------------//-------------------
    public function submitandprocess($params)
    {
        $form = $params['form'];
        $form->setDefaults($params);
        if (isset($params['process'])) {
            $process = $params['process'];
        } else {
            $tmp = \debug_backtrace();
            //ddd($tmp[1]);
        //ddd('qui');
        $process = [$tmp[1]['object'], 'do_'.$tmp[1]['function']]; //aspetto l'errore per correggere
        }

        $form->addElement('submit', null, 'Vai !');
        $form->display();
        if ($form->validate()) {
            return $form->process($process);
        }
    }

    //------------
}//end class
