<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Acme\Classes;

class AddSlashUrl{
    
    
public function addSlash(){
    if( $_SERVER['REQUEST_URI'] != '/' ){
        return $_SERVER["REQUEST_URI"].'/';
    }
}
}