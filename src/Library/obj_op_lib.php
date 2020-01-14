<?php



class OBJ_OP extends baseController
{
    public function obj_render($obj, $fielstorender)
    {
        if (!\is_array($fielstorender)) {
            $fielstorender = \explode(',', $fielstorender);
        }

        \reset($fielstorender);
        $v = $obj;
        $ris = new stdclass();
        foreach ($fielstorender as $k1 => $v1) {
            if (\is_object($v)) {
                $v = \get_object_vars($v);
            }
            if (isset($v[$v1])) {
                $ris->$v1 = $v[$v1];
            } else {
                $ris->$v1 = '';
            }
            $v2 = '_'.$v1;
            if (isset($v[$v2])) {
                $ris->$v2 = $v[$v2];
            }
        }
        \reset($obj);
        foreach ($obj as $k1 => $v1) {
            $tmp = \explode('_', $k1);
            if (isset($tmp[1]) && \in_array($tmp[1], $fielstorender, true)) {
                $ris->$k1 = $v1;
            }
        }

        /*
        reset($obj);
        foreach ($obj as $k => $v){
            if(substr($k,0,1)=='_'){
                $ris->$k=$v;
            }
        }
        */

        return $ris;
    }

    public function nodeSonsToRoot($params)
    {
        //$node
        //$obj_arr
        \extract($params);
        $data = [];
        \reset($obj_arr);
        foreach ($obj_arr as $k => $v) {
            //echo '<br/><b>'.($k+1).'</b>&nbsp;]&nbsp;'.$v->_id_codice->field2;
            $v1 = clone $v;
            unset($v1->$node);
            $t1 = \get_object_vars($v1);
            if (isset($v->$node) && \is_object($v->$node)) {
                $t2 = \get_object_vars($v->$node);
            //echo ' <span style="color:green">'.$v->_id_anagrafica->cognome.' '.$v->_id_anagrafica->nome.'</span>';
            } else {
                //echo '<h3>INCOMPLETO</h3><pre>';print_r($v);echo '</pre>';
                //echo ' <span style="color:red"> NON IMPOSTATO</span>';
                $t2 = [];
            }
            $t3 = \array_merge($t1, $t2);
            $data[$k] = ARRAY_OP::array2Obj($t3);
        }

        return $data;
    }

    //array array_column ( array $array , mixed $column_key [, mixed $index_key = null ] )
    public function obj_column($array, $column_key, $index_key = null)
    {
        $ris = [];
        \reset($array);
        foreach ($array as $k => $v) {
            $ris[] = $v->$column_key;
        }

        return $ris;
    }

    public function obj_merge($obj1, $obj2)
    {
        \reset($obj2);
        foreach ($obj2 as $k => $v) {
            $obj1->$k = $v;
        }

        return $obj1;
    }

    //http://solvedstack.com/questions/forcing-access-to-__php_incomplete_class-object-properties

    public function fixObject(&$object)
    {
        if (!\is_object($object) && 'object' == \gettype($object)) {
            return $object = \unserialize(\serialize($object));
        }

        return $object;
    }

    //--------------------------------------------------------------------------------------------------
    public function unique($array)
    {
        ddd($array);
    }

    //------------//-----------------//-------------------
//------------
}//end class
