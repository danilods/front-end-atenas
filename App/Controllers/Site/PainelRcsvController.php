<?php

/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

namespace App\Controllers\Site;

use ActiveRecord\ActiveRecordException;
use ActiveRecord\Model;
use \App\Controllers\BaseController;







class PainelRcsvController extends BaseController {





    public function index() {
		
		
        $dados = [

            'titulo' => 'RCSV - Relatório ao CENIPA para Segurança de Voo',

            'logo' => 'RCSV',
			


        ];
        $template = $this->twig->loadTemplate('Site/painelrcsv.html');
        $template->display($dados);



    }
	
	
	
                     

}