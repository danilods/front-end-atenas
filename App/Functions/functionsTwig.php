<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Illuminate\Support\Str as st;

$str_limit = new \Twig_SimpleFunction('str_limit', function($value, $limit=50, $end = '...'){
    
    return st::limit($value, $limit, $end);
    
});

$site_url =  new \Twig_SimpleFunction('site_url', function(){
    return 'http://'.$_SERVER['SERVER_NAME'].':8080'; 
});