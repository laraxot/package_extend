<?php



class QUIZ_OP extends baseController
{
    //---------------//--------------//---------------//---------------//--------------//---------------
    public function form_gridradio($params)
    {
        global $registry;
        $me = $registry->user->__get('me');
        $perm_type = $me->perm_type;
        \extract($params);
        $mdb2 = $registry->conf->getMDB2();
        $sql = 'select * from codici where tipo='.$row_tipo.' and codice!=0';
        $rows = $mdb2->queryAll($sql);
        //echo '<pre>'; print_r($data); echo '</pre>';
        $sql = 'select * from codici where tipo='.$col_tipo.' and codice!=0';
        $cols = $mdb2->queryAll($sql);
        $sql = 'select * from codici where tipo='.$subtitles_tipo.' and codice!=0';
        $tmps = $mdb2->queryAll($sql);
        $subs = [];
        foreach ($tmps as $k => $v) {
            $subs[$v->codice] = $v;
        }

        $sql = 'select * from codici where tipo='.$row_tipo.' and codice=0';
        $tmp = $mdb2->queryAll($sql);
        if (!isset($tmp[0]->field2)) {
            echo '<pre>'.$sql.'</pre>';

            return;
        }
        $title = $tmp[0]->field2;

        \reset($rows);
        $row_head = [];
        foreach ($rows as $kr => $vr) {
            $row_head[$kr] = $vr->field2;
        }

        \reset($cols);
        $col_head = [];
        foreach ($cols as $kr => $vr) {
            $col_head[$kr] = $vr->field2;
        }
        \reset($rows);
        $html = '';
        if ($show_title) {
            $html = '<h4>'.$title.'</h4>';
        }
        $html .= '<div class="table-responsive">';
        $html .= '<table class="table table-hover table-condensed table-bordered">';
        $j = 0;
        foreach ($rows as $kr => $vr) {
            if (0 == $j++) {
                $html .= '<tr><th width="70%">';
                if (isset($subs[$vr->tipo])) {
                    $html .= $subs[$vr->tipo]->field2;
                } else {
                    $html .= '';
                }
                $html .= '</th><th>'.\implode('</th><th>', $col_head).'</th></tr>';
            }
            if (isset($risposte[$vr->tipo]) && isset($risposte[$vr->tipo][$vr->codice])) {
                $html .= '<tr>';
            } else {
                $html .= '<tr class="warning">';
            }
            \reset($cols);
            $i = 0;
            foreach ($cols as $kc => $vc) {
                if (0 == $i++) {
                    $html .= '<td  align="left">'.$row_head[$kr];
                    //$html.='['.$perm_type.']';
                    if ($perm_type >= 4) {
                        $html .= '&nbsp;&nbsp;<a href="'.$_SERVER['REQUEST_URI'].'&rt=questionari/edit/'.$vr->tipo.'/'.$vr->codice.'">edit</a>';
                    }
                    $html .= '  </td>';
                }
                //echo '<td>'.$vr->field2.' - '.$vc->field2.'</td>';
                if (isset($risposte[$vr->tipo]) && isset($risposte[$vr->tipo][$vr->codice]) && $risposte[$vr->tipo][$vr->codice] == $vc->codice) {
                    $html .= '<td align="center"><input type="radio" value="'.$vc->codice.'" checked="" name="grid['.$vr->tipo.']['.$vr->codice.']"/>
				</td>';
                } else {
                    $html .= '<td align="center" >
				<input type="radio" value="'.$vc->codice.'" name="grid['.$vr->tipo.']['.$vr->codice.']"/>
				&nbsp;
				</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    //---------------//--------------//---------------//---------------//--------------//---------------

    public function multiquiz($params)
    {
        $show_title = true;
        \extract($params);

        if (quizInviato(['tipo' => $quiz_tipo])) {
            $registry->template->showMsg('quiz gia\' inviato', 'success');

            return;
        }

        $mdb2 = $registry->conf->getMDB2();
        $me = $registry->user->__get('me');

        $handle = $me->handle;
        $handle = STRING_OP::myCrypt($handle);

        //8598c19e8c6bd46d81ddfafb98e7f653
        //echo $handle;
        /*
        //$quiz_tipo=6100;
        $quiz_tipo=7000;
        $valutazione_tipo=8000;
        $subtitles_tipo=9000;
        */
        $sql = 'select * from codici where tipo='.$quiz_tipo.' and codice!=0';
        $data = $mdb2->queryAll($sql);

        $sql = 'select * from codici where tipo='.$quiz_tipo.' and codice=0';
        $title = $mdb2->queryAll($sql);
        echo '<h3>'.$title[0]->field2.'</h3>';

        //echo '<pre>';print_r($risposte); echo '</pre>';

        $form = new HTML_QuickForm('scegliDipendentiForm', 'POST', $_SERVER['REQUEST_URI']);

        $do = DB_DataObject::factory('risposte');
        $fb = &DB_DataObject_FormBuilder::create($do);
        $form = &$fb->getForm($_SERVER['REQUEST_URI']);

        if ($form->validate()) {
            //ddd('qui');
            echo $form->process($form_process, false);
        }
        //-------------------------------------------------
        $sql = 'select * from risposte where handle="'.$handle.'"';
        $tmp = $mdb2->queryAll($sql);
        $risposte = [];
        foreach ($tmp as $k => $v) {
            $risposte[$v->tipo][$v->codice] = $v->risposta;
        }

        //*
        $html = '';
        $valutazione_tipo_arr = \explode(',', $valutazione_tipo);
        $nv = \count($valutazione_tipo_arr);
        $j = 0;
        foreach ($data as $k => $v) {
            $iv = ($j++) % $nv;
            $parz = [
        'show_title' => $show_title, 'row_tipo' => $v->codice, 'col_tipo' => $valutazione_tipo_arr[$iv], 'subtitles_tipo' => $subtitles_tipo, 'risposte' => $risposte,
        ];
            $html .= form_gridradio($parz);
        }
        $smarty->assign('html', $html);
        //*/
        require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
        // Create the renderer object
        $renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
        // build the HTML for the form
        $form->accept($renderer);
        //echo '<pre>'; print_r($renderer->toArray()); echo '</pre>';
        // assign array with form data
        $smarty->assign('FormData', $renderer->toArray());
    }

    //-------------------
    public function getMyAnswerQuiz($params)
    {
        \extract($params);

        $me = $registry->user->__get('me');
        $handle = $me->handle;
        $handle = STRING_OP::myCrypt($handle);
        $params['handle'] = $handle;

        return getAnswersQuiz($params);
    }

    public function getAnswersQuiz($params)
    {
        \extract($params);
        $mdb2 = $registry->conf->getMDB2();

        $sql0 = 'select * from codici where tipo="'.$tipo.'" and codice!=0';
        //echo '<br><pre>'.$sql0.'</pre>';
        $data = $mdb2->queryAll($sql0);
        $data1 = [];
        foreach ($data as $k => $v) {
            $sql = 'select * from risposte where tipo="'.$v->codice.'" ';
            if (isset($handle)) {
                $sql .= ' and handle="'.$handle.'"';
            }
            //echo '<br/>---<pre>'.$sql.'</pre>';
            $data1 = \array_merge($data1, $mdb2->queryAll($sql));
        }

        if (isset($valutazione_tipo)) {
            \reset($data1);
            $vv = \explode(',', $valutazione_tipo);
            $i = 0;
            $tipo_valutaz = [];
            \reset($data);
            foreach ($data as $k => $v) {
                $tipo_valutaz[$v->codice] = $vv[$i++ % \count($vv)];
            }

            //echo '<h3>TIPO VALUTAZ</h3><pre>';print_r($tipo_valutaz); echo '</pre>';

            foreach ($data1 as $k => $v) {
                $data1[$k]->valutazione_tipo = $tipo_valutaz[$v->tipo];
            }
        }

        return $data1;
    }

    //-------------------
    public function getQuestionQuiz($params)
    {
        \extract($params);
        $mdb2 = $registry->conf->getMDB2();
        $sql0 = 'select * from codici where tipo="'.$tipo.'" and codice!=0';
        $data = $mdb2->queryAll($sql0);
        $data1 = [];
        foreach ($data as $k => $v) {
            $sql = 'select * from codici where tipo="'.$v->codice.'" and codice!=0';
            $data1 = \array_merge($data1, $mdb2->queryAll($sql));
        }

        return $data1;
    }

    //------------------------
    public function tuttiQuizInviati($params)
    {
        \extract($params);
        \reset($listaTipoQuiz);
        $chiuso = 0;
        foreach ($listaTipoQuiz as $k => $v) {
            $params['tipo'] = $v;
            if (quizInviato($params)) {
                ++$chiuso;
            }
        }

        return $chiuso == \count($listaTipoQuiz);
    }

    //--------------------------------------

    public function quizInviato($params)
    {
        \extract($params);
        if (!isset($registry)) {
            global $registry;
        }
        $mdb2 = $registry->conf->getMDB2();

        $me = $registry->user->__get('me');
        $handle = $me->handle;
        $sql = 'select * from quiz_chiusi where handle="'.$handle.'" and tipoquiz="'.$tipo.'"';
        $data = $mdb2->queryAll($sql);

        return \count($data);
    }

    //-----------------------
}//end class
