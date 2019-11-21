<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers\Site;

use App\Controllers\BaseController as BaseController;

use Acme\Classes\Redirect as Redirect;
use App\Models\AeronaveOperadorModel;
use App\Models\OcorrenciaModel as ocorrencia;


class LoginController extends BaseController{
    
   
    
    public function index(){
        
       
        
        $dados =  ['titulo' => 'Acompanhar'];
        $template = $this->twig->loadTemplate('Site/login.html');
        $template->display($dados);
        
        
        

        
    }
    
   
    
    }
   
        
   
    
   
    
    

    

