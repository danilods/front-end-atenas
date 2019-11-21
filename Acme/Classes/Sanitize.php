<?php

namespace Acme\Classes;

class Sanitize{

	public static function string($ valor){
		return filter_var(trim($valor), FILTER_SANITIZE_STRING);
	}


}