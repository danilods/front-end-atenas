<?php

namespace App\Controllers\Site;
use App\Controllers\BaseController;
use App\Models\RecomendacaoModel as recomendacao;
use App\Models\RelatorioModel as relatorio;
use App\Models\TaxonomiaFatorModel as fator;
use App\Models\TipoModel as tipo;
use App\Models\UfModel as uf;
use App\Models\AeronaveCategoriaModel as categoria;


class RelatorioControllerold extends BaseController {

	public function index() {

		$relacao_rf = relatorio::listar();
		$taxonomia_tipo = tipo::listar(null,'tipo');
		$estado = uf::custom('SELECT * FROM `geografia_uf` where pais_id=1  order by nome_codigo asc');
		
		$aeronaveCategoria = categoria::custom('SELECT aviacao FROM aeronave_categoria group by aviacao');
		//$resultado_fator = fator::listar();



        $dados = [
			'titulo' => 'RELATÓRIOS FINAIS',
			'relatorios' => $relacao_rf,
			'taxonomia_tipo' => $taxonomia_tipo,
			'estado' => $estado,
			'categoria' => $aeronaveCategoria
		];
		$template = $this->twig->loadTemplate('Site/relatorio.html');
		$template->display($dados);

	}


        
        
        public function json(){
        
        $query = 'select id, data_relatorio, classificacao, fabricante, modelo, tipo_ocorrencia, matricula from view_relatorios_finais_e_recomendacao limit 10' ;

        $rf = relatorio::custom($query);
        
        
         $data = array();
        
        foreach($rf as $r) { 
           
            
          
            $data[] = $r->to_array();
                       
                       
        }
      //  print_r($data);
        
		$url = "http://prevencao.potter.net.br";
             
        foreach ($data as $res){
       
            $resultado[] = array(
			"link" => $url."/detalhe/".$res['id']."/".$res['matricula'],
		
			"titulo" => $res['classificacao']." - ".$res['data_relatorio'],
			"subtitulo" => $res['matricula']." - ".$res['fabricante']." - ".$res['modelo']
			
			
			
	
		);
	
            
        }
        echo json_encode($resultado);
        
     
      
      
        
    }

}
