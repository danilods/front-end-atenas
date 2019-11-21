<?php
/**
 * Created by PhpStorm.
 * User: danilodesouza
 * Date: 13/08/15
 * Time: 16:19
 */

namespace App\Models;


class PrevencaoGeralModel extends \App\Models\appModel{

    static $table_name = 'prevencao_geral';


      public static function results_to_json($resultArray) {
            $arr = array();
            if(count($resultArray)>0){
            foreach($resultArray as $row){
            array_push($arr, $row->to_array());
            }
            }
            return json_encode($arr);
            }
    
    
} 