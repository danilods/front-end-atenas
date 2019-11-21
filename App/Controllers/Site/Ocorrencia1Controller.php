<?php

namespace App\Controllers\Site;
use App\Controllers\BaseController;
use App\Models\OcorrenciaModel as ocorrencia;


class Ocorrencia1Controller extends BaseController {

	public function index() {

		$relacao_ocorrencias = ocorrencia::listar();
		
		
		
		echo json_encode($relacao_ocorrencias);


	}



}
