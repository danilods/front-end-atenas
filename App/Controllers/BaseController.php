<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers;
use App\Core\CoreController;


class BaseController extends CoreController{
    private $controller;
    private $folders = ['Admin','Site'];
    
        public function setTwig($twig) {
            $this->twig=$twig;
        }
        
        public function pegaController(){
        $this->controller = ucfirst ($this->controller()['controller']).'Controller';
        
        
        foreach ( $this->folders as $folder) {
            if (class_exists("\\App\\Controllers\\".$folder."\\".$this->controller)){
                return "\\App\\Controllers\\".$folder."\\".$this->controller;
            }
        }
            return "\\App\\Controllers\\Erro\\NotFoundController";

        
            }
        public function pegarMetodo( $object ){
            if (empty($this->controller()['metodo'])){
                return $this->controller = 'index';
                
            }  else {
                if (method_exists($object, $this->controller()['metodo'])){
                    return $this->controller = $this->controller()['metodo'];
                    
                }  else {
                    return $this->controller = 'index';
                }
            }
        }
        


}