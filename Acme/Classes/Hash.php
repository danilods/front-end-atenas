<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Acme\Classes;


class Hash {
    
    public static function criarSenha($senha, $salt){
        
        return crypt($senha, $salt);
        
    }
    
    public static function verificarSenha ($inputSenha, $senhaEncriptada){
        
        if(crypt($inputSenha,$senhaEncriptada) == $senhaEncriptada){
            
            return true;
       
        }
        return false;
        
    }
    public static function criarSalt(){
        
        return base64_encode(md5(uniqid(),true));
        
    }
}