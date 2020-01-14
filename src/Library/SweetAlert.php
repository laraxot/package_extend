<?php



namespace XRA\Extend\Library;

class SweetAlert
{
    public static function alert($title, $message = null, $type)
    {
        echo '<script>$.confirm({
                        title: \''.$title.'\',
                        content: \''.$message.'\',
                        type: \''.$type.'\',
                        typeAnimated: true,
                        buttons: {
                            close: {text: \'Chiudi\',}
                        }
                    });</script>';
    }
}
