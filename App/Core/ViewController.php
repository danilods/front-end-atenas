<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Core;

class ViewController {
    
    public function renderizarLayout($caminho, $dados = false,$erro=false){
        require "Views/BaseView/".$caminho."php";
    }
    
    public function renderizarTemplate($caminho,$dados=false){
        
        require "Views/BaseView/".$caminho."php";
    }
}