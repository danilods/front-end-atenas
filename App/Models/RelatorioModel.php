<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

class RelatorioModel extends \App\Models\appModel {

	use \Acme\Traits\LoginTrait;

	static $table_name = 'view_relatorios_finais_e_recomendacao';



    public static function joinOcorrenciaRsv(){
        $join = 'LEFT JOIN view_contador_recomendacao rs ON( view_ocorrencia_rf.id = rs.ocorrencia_id ) order by view_ocorrencia_rf.data_relatorio desc';

        return parent::all(array('joins'=>$join));
    }



}