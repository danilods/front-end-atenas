<?php

namespace App\Controllers\Site;
use App\Controllers\BaseController;
use App\Models\RecomendacaoModel;
use App\Models\TipoModel as tipo;

class RecomendacaoController extends BaseController {

	public function index() {

		
		$rsv = RecomendacaoModel::listar();
		$taxonomia_tipo = tipo::listar(null,'tipo');
		$dados = [
			'titulo' => 'RECOMENDAÇÃO DE SEGURANÇA DE VOO',
			'relatorios' => $rsv,
			'taxonomia_tipo' => $taxonomia_tipo
		];
		$template = $this->twig->loadTemplate('Site/recomendacao.html');
		$template->display($dados);

	}
        
          public function json(){
        
        $query = 'SELECT ocorrencia_id, matricula, tipo, dia_assinatura, classificacao FROM view_divulgacao_recomendacao group by matricula LIMIT 10 ' ;

        $rs = RecomendacaoModel::custom($query);
        
        
         $data = array();
        
        foreach($rs as $r) { 
           
            
          
            $data[] = $r->to_array();
                       
                       
        }
      //  print_r($data);
        
       $url = "http://prevencao.potter.net.br";
             
        foreach ($data as $res){
       
            $resultado[] = array(
			"link" => $url."/detalhe/".$res['ocorrencia_id']."/".$res['matricula'],
			"titulo" => $res['classificacao']." - ".$res['dia_assinatura'],
			"subtitulo" => $res['matricula']." - ".utf8_encode($res['tipo'])
			
			 
	
		);
	
            
        }
        echo json_encode($resultado);
        
     
      
      
        
    }

}