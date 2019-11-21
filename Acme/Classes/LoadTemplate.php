<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Acme\Classes;

class LoadTemplate{
    protected $twig;
    private $loader;
    
    private function loader(){
        $this->loader = new \Twig_Loader_Filesystem(ROOT.'/App/Views');
        return $this->loader;
    }
    
    public function init(){
        $this->twig = new \Twig_Environment($this->loader(),[
            'debug' => true,
            'cache' => ROOT.'/Cache',
            'auto_reload' => true
        ]);
        return $this->twig;
    }
}
