<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Acme\Classes;

use Acme\Classes\Redirect;
use Acme\Classes\Url as Url;

class Parameters {

	public static function getParameter($numeroIndex) {

		$explodeUrl = explode('/', Url::getUrl());

		if (empty($explodeUrl[$numeroIndex])) {
			Redirect::to('notFound');
		}

		return $explodeUrl[$numeroIndex];

	}
}