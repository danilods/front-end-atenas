<?php

namespace App\Controllers\Site;
use Acme\Classes\Parameters as parameter;
use App\Controllers\BaseController;
use App\Models\RecomendacaoModel;
use App\Models\RelatorioModel;

class DetalheController extends BaseController {

	public function index() {

		$ocorrencia = parameter::getParameter(2);

        $matricula = parameter::getParameter(3);

		$resultado = RecomendacaoModel::where('ocorrencia_id', $ocorrencia,'all');

        $resultado2 = RelatorioModel::all(array('conditions' => array('id = ? AND matricula = ?', $ocorrencia, $matricula)));
		
		$consultaFator = "SELECT tf.nome, of.nivel_contribuicao, of.observacoes from ocorrencia_fator of inner join 
		taxonomia_fatores tf on of.fator_id=tf.id where of.ocorrencia_id =".$ocorrencia."";
		
		$fatores_contribuintes = RelatorioModel::custom($consultaFator);

       // $resultado2 = RelatorioModel::where('matricula',$valor2,'all');

        $dados = [
            'titulo' => 'RECOMENDAÇÃO DE SEGURANÇA',
            
            'relatorios' => $resultado,
            'ocorrencia' => $resultado2,
			'fatores' => $fatores_contribuintes
        ];
        $template = $this->twig->loadTemplate('Site/detalhe_rsv.html');
        $template->display($dados);
	}

}