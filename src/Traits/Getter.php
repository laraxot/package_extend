<?php


namespace XRA\Extend\Traits;

trait Getter
{
    public static function __merge($index, $value)
    {
        $tmp = self::__getStatic($index);
        /*
        $value = \array_filter($value, function ($var) {
            return null !== $var && '' != \trim($var);
        });
        */
        //if(!is_array($tmp)) $tmp=[];
        $tmp = \array_merge($tmp, $value);
        self::__setStatic($index, $tmp);

        return $tmp;
    }

    public static function __getStatic($index)
    {
        if (isset(self::$vars[$index])) {
            return self::$vars[$index];
        }

        $params = [];
        $func = 'get_'.$index;
        $ris = self::$func($params);
        //dd(get_called_class());//XRA\Extend\Services\ThemeService
        //dd(class_basename(get_called_class()));//ThemeService
        if ('' == $ris && isset(\get_called_class()::$config_name)) {
            $config_name = \get_called_class()::$config_name;
            $ris = config($config_name.'.'.$index);
        }
        self::__setStatic($index, $ris);

        return $ris;
    }

    //end __set

    public static function __setStatic($index, $value)
    {
        //echo '<br/>SET ['.get_class($this).']['.$index.']['.round(memory_get_usage()/(1024*1024),2).' MB]';
        self::$vars[$index] = $value;
    }

    //end __set

    public static function __concatBeforeStatic($index, $value)
    {
        $tmp = self::__getStatic($index);
        $tmp = $value.$tmp;
        self::__setStatic($index, $tmp);
    }

    //*
    public static function __callStatic($method, $args)
    {
        if (\preg_match('/^([gs]et)([A-Z])(.*)$/', $method, $match)) {
            $reflector = new \ReflectionClass(__CLASS__);
            $property = \mb_strtolower($match[2]).$match[3];
            if ($reflector->hasProperty($property)) {
                $property = $reflector->getProperty($property);
                switch ($match[1]) {
                    case 'get':
                        return $property->getValue();
                    case 'set':
                        return $property->setValue($args[0]);
                }
            } else {
                throw new \InvalidArgumentException("Property {$property} doesn't exist");
            }
        }
    }

    //*/
    public function __isset($index)
    {
        return isset($this->vars[$index]);
    }

    public function __concat($index, $value)
    { //default After
        $tmp = $this->__get($index);
        $tmp = $tmp.$value;
        $this->__set($index, $tmp);
    }

    /**
     * @set undefined vars
     *
     * @param string $index
     * @param mixed  $value
     */
    public function __set($index, $value)
    {
        //echo '<br/>SET ['.get_class($this).']['.$index.']['.round(memory_get_usage()/(1024*1024),2).' MB]';
        $this->vars[$index] = $value;
    }

    public function __concatBefore($index, $value)
    {
        $tmp = $this->__get($index);
        $tmp = $value.$tmp;
        $this->__set($index, $tmp);
    }

    /**
     * @get variables
     *
     * @param mixed $index
     *
     * @return mixed
     */
    public function __getOLD($index)
    {
        if (isset($this->vars[$index])) {
            return $this->vars[$index];
        }
        /*
        //$params=array();
        if(isset($this->registry)){
         $params=$this->registry->getParams('params');
        }else{
         $params=$this->getParams('params');
        }
        */
        $params = [];
        $func = 'get_'.$index;
        if (!\method_exists($this, $func)) {
            if (\class_exists($index)) {
                $obj = new $index($this);
                //echo '<h3>CLASSE ['.$index.']</h3>';
                $this->__set($index, $obj);

                return $obj;
            }
            Enteweb::print_x('<h3>'.\get_class($this).'->'.$func.'</h3>');
        }

        $ris = $this->$func($params);
        $this->__set($index, $ris);

        return $ris;
    }

    public function __getVars($params = [])
    {
        $vars = $this->vars;
        $vars['smarty'] = '';
        unset($vars['smarty']);

        return $vars;
    }
}
