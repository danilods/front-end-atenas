<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Acme\Classes;

class Redirect {
    
    public static function to( $location ){
        
        $redirect = ($location !=null )? $location : 'relatorio';
        
        return header("Location: " . "http://" . $_SERVER['HTTP_HOST'] ."/".$redirect);
    }
}