<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

class OperadorModel extends \App\Models\appModel {
    
        use \Acme\Traits\LoginTrait;

	static $table_name = 'aeronave_operador';
        
        public static function logar($nome_codigo,$cod_operador){
		$administradorEncontrado = parent::find('first', array('conditions' => array('nome_codigo=? and cpf_cnpj=?',$nome_codigo,$cod_operador)));
		return $administradorEncontrado;
	}


}