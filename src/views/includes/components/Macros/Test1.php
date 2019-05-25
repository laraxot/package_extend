<?php

class Test1{
	public function __invoke()
    {
        return function () {
        	return 'PRESO['.__LINE__.']['.__FILE__.']';
        }
    }
}