<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Acme\Classes;

use App\Controllers\BaseController;
use App\Models\Site\MarcaModel;

class autocomplete extends BaseController{
   
    public function buscar(){
        
        $q = $_GET["q"];
        $sql =  "selec * from tb_marca where nome_marca like '%$q%'";
        
        $resultado = MarcaModel::find_by_sql($sql);
        
        echo json_encode($resultado);
        
    }
    
    
}
