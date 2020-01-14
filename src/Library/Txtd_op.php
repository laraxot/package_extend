<?php



namespace XRA\Extend\Library;

use Illuminate\Support\Facades\Schema;

class Txtd_op
{
    public static function toArray($file_txtd)
    {
        $handle = \fopen($file_txtd, 'r');
        $riga = 0;
        $ris = [];
        while (false !== ($data = \fgetcsv($handle, 2000, '|'))) {
            ++$riga;
            $row = \array_map('trim', $data);
            $row = \array_map('strtolower', $row);
            if (2 == $riga) {
                $head = $row;
            }
            if ($riga > 3 && isset($data[1])) {
                $ris[] = \array_combine($head, $row);
            }//end if
        }//end while
    \fclose($handle);

        return $ris;
    }

    //end function
    //--------------------------------------------------------------
    public static function getFieldsNames($params)
    {
        $tmp = self::toArray($params['filename']);
        $tmp = collect($tmp);
        $keyed = $tmp->keyBy(function ($item) {
            if ('desc' == $item['nome']) {
                $item['nome'] = 'desc1';
            }

            return \trim($item['nome']);
        });
        $keys = $keyed->keys();

        return $keys->all();
    }

    //--------------------------------------------------------------
    public static function createTable($params)
    {
        $tmp = self::toArray($params['filename']);
        if (\count($tmp) < 2) {
            return '<h3> file scaricato male riprovare </h3>';
        }
        Schema::connection($params['conn'])->dropIfExists($params['tbl']);

        //dd('--'.count($tmp).'--');
        Schema::connection($params['conn'])->create($params['tbl'], function ($table) use ($tmp) {
            $table->increments('id');
            foreach ($tmp as $k => $v) {
                if ('desc' == $v['nome']) {
                    $v['nome'] = 'desc1';
                }
                switch ($v['tipo']) {
                case 'carattere':
                    if ($v['lunghezza'] > 255) {
                        $table->text($v['nome']);
                    } else {
                        $table->char($v['nome'], $v['lunghezza']);
                    }
                break;
                case 'numero':
                    if ($v['dec'] > 0) {
                        $table->decimal($v['nome'], $v['lunghezza'], $v['dec']);
                    } else {
                        $table->integer($v['nome']);
                    }
                break;
                default:
                    die('['.$v['tipo'].']['.__LINE__.']['.__FILE__.']');
                break;
            }
            }
        });
        echo '<h3>Creato tabella '.$params['conn'].'.'.$params['tbl'].'</h3>';
    }

    //end createTable
}//end class
