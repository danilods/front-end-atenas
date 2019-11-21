<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

class appModel extends \ActiveRecord\Model{
    
    
    public static function listar($limite = null, $order = null){
        
        if($limite != null){
            
            return parent::find('all',['select' => '*', 'limit' => $limite, 'order'=> $order]);
        
            
        }
        return parent::find('all',['order'=>$order]);
        
    }


    public static function atualizar($id, Array $atributes){
        
        $atualizar = parent::find($id);
        $atualizar->update_atributes( $atributes );
    }
    
    public static function cadastrar(Array $attributes){
        
        return parent::create($attributes);
    }
    
    public static function deletar($id){
        
        $deletar = parent::find($id);
        return $deletar->delete();
    }
    
    public static function where($campo, $valor, $tipo = null, $order=null){
        
        $tiposListagem = ($tipo == null)? 'first' : 'all';
        
        return parent::find($tiposListagem, ['order' => $order,'conditions'=>[$campo.'=?',
            $valor] ]);
    }
    
    public static function custom($consulta){
        return parent::find_by_sql($consulta);
    }
}