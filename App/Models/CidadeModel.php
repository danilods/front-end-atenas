<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

class CidadeModel extends \App\Models\appModel {

	static $table_name = 'geografia_cidade';




	public static function ConsultarCidade($id){
		
		return parent::custom("Select * From geografia_cidade where id=$id");
	}


}

	
