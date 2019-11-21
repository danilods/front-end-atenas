<?php

namespace App\Controllers\Site;
use App\Controllers\BaseController;
use App\Models\RecomendacaoModel as recomendacao;
use App\Models\OcorrenciaModel as ocorrencia;
use App\Models\TipoModel as tipo;
use App\Models\UfModel as uf;

class OcorrenciaController extends BaseController {

	public function index() {

		$relacao_ocorrencias = ocorrencia::where('investigada', 'SIM', 'all');
		$taxonomia_tipo = tipo::listar();
		$estado = uf::custom('SELECT * FROM `geografia_uf` where pais_id=1  order by nome_codigo asc');





        $dados = [
			'titulo' => 'ACOMPANHAMENTO DE OCORRÊNCIAS',
			'relatorios' => $relacao_ocorrencias,
			'taxonomia_tipo' => $taxonomia_tipo,
			'estado' => $estado
		];
		$template = $this->twig->loadTemplate('Site/ocorrencia.html');
		$template->display($dados);

	}



}
