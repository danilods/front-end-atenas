<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Acme\Classes;

class Url{
    public static function getUrl(){
        return $_SERVER['REQUEST_URI'];
    }
}