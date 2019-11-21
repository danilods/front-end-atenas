<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//pegar a url

date_default_timezone_set('America/Sao_Paulo');

$url = \Acme\Classes\Url::getUrl();

//carregar template

$template = new \Acme\Classes\LoadTemplate();
$twig = $template->init();

//carregar funçoes do twig

$twig->addFunction($str_limit);
$twig->addFunction($site_url);


//chamar o basecontroller para pegar os controller e os metodos

$baseController = new App\Controllers\BaseController();
$baseController->setUrl($url);

//pega os controllers

$controller = $baseController->pegaController();
$classController = new $controller();

$classController->setTwig($twig);

//pegar metodo

$metodo = $baseController->pegarMetodo($classController);
$classController->$metodo();