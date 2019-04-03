<?php


/**
 * Created by PhpStorm.
 * User: Raffaele
 * Date: 26/04/2017
 * Time: 12:34.
 */

namespace XRA\Extend\library;

class TestResult
{
    public $message;
    public $status;

    public function __construct($message, $status = STATUS_OK)
    {
        $this->message = $message;
        $this->status = $status;
    }
}
