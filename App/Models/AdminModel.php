<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

class Admin extends \App\Models\AppModel {

	use \Acme\Traits\LoginTrait;

	static $table_name = 'tb_admin';

}