<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Acme\traits;

use Acme\Classes\Redirect as Redirect;

trait LoginTrait{
    
    private $campos;
    private $campo;
    private $sqlCampo;
    
    public function setCampos($campos){
        $this->campos = $campos;
        
    }
    
    public function logar($email, $senha){
        
    foreach ($this->campos as $campo) :
        $this->campo.= $campo. '=? and ';
    endforeach;
    
        $this->sqlCampo = rtrim( $this->campo, 'and ');

        $dadosUserLogado = parent::find ( 'first', ['conditions' => [$this->sqlCampo, $email, $senha] ] );
        
        return $dadosUserLogado;
    }
    
    public static function deslogar($sessao){
        
        if (isset($_SESSION[ $sessao ] ) ){
            unset($_SESSION[ $sessao ]);
            session_regenerate_id();
        }
       
    }
    
    public static function estaLogado($sessao, $redirect){
        
        if(!isset($_SESSION[ $sessao ] ) ){
            Redirect::to ($redirect);
        }
        
        
    }
    
    
    
    
}