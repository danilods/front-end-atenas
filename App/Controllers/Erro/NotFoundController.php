<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers\Erro;
use App\Controllers\BaseController;

class NotFoundController extends BaseController {

	public function index() {
		$dados = [
			'titulo' => 'Erro 404',
		];
		$template = $this->twig->loadTemplate('Erro/extra_404_error.html');
		$template->display($dados);
	}

}