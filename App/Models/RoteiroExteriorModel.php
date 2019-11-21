<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

class RoteiroExteriorModel extends \App\Models\appModel {

	use \Acme\Traits\LoginTrait;

	static $table_name = 'roteiro_exterior';


	public static function UpdateAcresDeslocamento($id, $acrescimo){

		$atualizar = parent::find($id);
		$atualizar->quantidade_acrescimo = $acrescimo;
		$atualizar->save();
		
		
	}

	public static function ConsultarOrdemServico($unidade, $ano_corrente){
		return parent::custom("");
	}


}