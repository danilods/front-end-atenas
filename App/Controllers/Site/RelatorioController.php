<?php

namespace App\Controllers\Site;

@session_start();

use App\Controllers\BaseController;
use Acme\Classes\Parameters as parameter;
use Acme\Classes\Redirect as redirect;
use App\Models\RecomendacaoModel as recomendacao;
use App\Models\RelatorioModel as relatorio;
use App\Models\TaxonomiaFatorModel as fator;
use App\Models\TipoModel as tipo;
use App\Models\UfModel as uf;

use Acme\Classes\paginator;



class RelatorioController extends BaseController {

	public function index() {
		
		
		if(parameter::getParameter(3)==null){
			redirect::to('relatorio/page/1');
			
		}else{
			$pag = parameter::getParameter(3);
			}
		
		if(!isset($_SESSION['registoTotalRf'])){
			$relacao_rf_count = relatorio::find('all', array('select' => 'COUNT(*) AS total'));
			$_SESSION['registoTotalRf'] = $relacao_rf_count[0]->total;	
		}
		  
		
		
		$inicio = 0;
		$itemsPerPage = 30;

		if ($pag!='')
		{
		$inicio = ($pag - 1) * $itemsPerPage;
		}
		if(!isset($pag)){ $pag = 1;
		}
		
		
		
		$relacao_rf = relatorio::find('all', array('limit' => $itemsPerPage, 'offset' => $inicio));
		$fatorContribuinte = relatorio::custom('select id, nome from taxonomia_fatores order by nome asc');
		
		$taxonomia_tipo = tipo::listar();
		$estado = uf::custom('SELECT * FROM `geografia_uf` where pais_id=1  order by nome_codigo asc');

		$resultado_fator = fator::listar();
		
		
		$totalItems = $_SESSION['registoTotalRf'];
		
		$currentPage = $pag;
		$urlPattern = '/relatorio/page/(:num)';
		
		$paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
			


        $dados = [
			'titulo' => 'RELATÓRIOS FINAIS',
			'relatorios' => $relacao_rf,
			'taxonomia_tipo' => $taxonomia_tipo,
			'fator' => $resultado_fator,
			'estado' => $estado,
			'paginator' => $paginator,
			'total' => $totalItems,
			'paginaatual' => $pag,
			'fatores' => $fatorContribuinte
		];
		$template = $this->twig->loadTemplate('Site/relatorio.html');
		$template->display($dados);

	}
	
	public function filtro(){
	

			$pag = parameter::getParameter(3);
			$filtro = parameter::getParameter(4);
			
			
			$matricula = addslashes($_GET['matricula']);
			$numero = addslashes($_GET['numero']);
			$data_inicial = addslashes($this->databr($_GET['data_inicial'],'en'));
			$data_final = addslashes($this->databr($_GET['data_final'],'en'));
			$equipamento = addslashes(utf8_decode($_GET['equipamento']));
			$fabricante = addslashes(utf8_decode($_GET['fabricante']));
			$modelo = addslashes(utf8_decode($_GET['modelo']));
			$fator = addslashes(utf8_decode($_GET['fator']));
			$classificacao = addslashes($_GET['classificacao']);
			$tipo = addslashes(utf8_decode($_GET['tipo']));
			$estado = addslashes($_GET['estado']);
			$cidade = addslashes(utf8_decode($_GET['cidade']));
			
			if($data_inicial != "--" && $data_final !="--"){
				
				$data = "and rf.data_ocorrencia_mysql BETWEEN '$data_inicial' and '$data_final'";
			
			}else{ $data = '';}
			
			
			if($classificacao!=''){
			$classificacao_tipo = " and rf.classificacao='".$classificacao."'";	
		}else{
			$classificacao_tipo = " and rf.classificacao like '%".$classificacao."%'";
		}
			
			

	unset($_SESSION['registoTotalFiltro']); 
	
	
	if(!isset($_SESSION['registoTotalFiltro'])){
		
			if($fator!=null){
				
					$query = "SELECT COUNT(*) as total FROM view_fatores_contribuintes fc inner join view_relatorios_finais_e_recomendacao rf on fc.ocorrencia_id=rf.id where fc.id=".$fator." and rf.numero_relatorio like '%".$numero."%' and rf.matricula like '%".$matricula."%' $classificacao_tipo and rf.fabricante like '%".$fabricante."%' and rf.modelo like '%".$modelo."%' and rf.equipamento like '%".$equipamento."%' and rf.tipo_ocorrencia like '%".$tipo."%' and rf.estado like '%".$estado."%' and rf.cidade like '%".$cidade."%' $data ";
				}else{
					$query = "SELECT COUNT(*) as total FROM view_relatorios_finais_e_recomendacao rf where matricula like '%".$matricula."%' and rf.numero_relatorio like '%".$numero."%' $classificacao_tipo and rf.fabricante like '%".$fabricante."%' and rf.modelo like '%".$modelo."%' and rf.equipamento like '%".$equipamento."%' and rf.tipo_ocorrencia like '%".$tipo."%' and rf.estado like '%".$estado."%' and rf.cidade like '%".$cidade."%' $data ";
				}
				
			$relacao_rf_count = relatorio::custom($query);
			$_SESSION['registoTotalFiltro'] = $relacao_rf_count[0]->total;	
		}
		  
		
		
		$inicio = 0;
		$itemsPerPage = 30;
		
		
		if ($pag!='')
		{
		$inicio = ($pag - 1) * $itemsPerPage;
		}
		if(!isset($pag)){ $pag = 1;
		}
		
		
		
		
		
		if($fator!=''){
			
			$query1 = "SELECT rf.id, rf.matricula, rf.numero_relatorio, rf.dia_ocorrencia, rf.fabricante, rf.modelo, rf.equipamento, rf.classificacao, rf.tipo_ocorrencia, rf.cidade, rf.estado, rf.anexo_pt, rf.anexo_en, rf.anexo_es, fc.ocorrencia_id FROM `view_fatores_contribuintes` fc
inner join view_relatorios_finais_e_recomendacao rf on fc.ocorrencia_id=rf.id where fc.id=".$fator." and rf.numero_relatorio like '%".$numero."%' and rf.matricula like '%".$matricula."%' $classificacao_tipo  and rf.fabricante like '%".$fabricante."%' and rf.modelo like '%".$modelo."%' and rf.equipamento like '%".$equipamento."%' and rf.tipo_ocorrencia like '%".$tipo."%' and rf.estado like '%".$estado."%' and rf.cidade like '%".$cidade."%' $data ORDER BY rf.data_ocorrencia_mysql desc LIMIT $inicio,$itemsPerPage";
		
		}else{
		
			$query1 = "SELECT id, matricula, numero_relatorio, dia_ocorrencia, fabricante, modelo, equipamento, classificacao, tipo_ocorrencia, cidade, estado, anexo_pt, anexo_en, anexo_es, ocorrencia_id  FROM view_relatorios_finais_e_recomendacao rf where rf.numero_relatorio like '%".$numero."%' and rf.matricula like '%".$matricula."%'  $classificacao_tipo and rf.fabricante like '%".$fabricante."%' and rf.modelo like '%".$modelo."%' and rf.equipamento like '%".$equipamento."%' and rf.tipo_ocorrencia like '%".$tipo."%' and rf.estado like '%".$estado."%' and rf.cidade like '%".$cidade."%' $data ORDER BY rf.data_ocorrencia_mysql desc LIMIT $inicio,$itemsPerPage";
		}
		
		
		$relacao_rf = relatorio::custom($query1);
		$fatorContribuinte = relatorio::custom('select id, nome from taxonomia_fatores order by nome asc');
		
		$taxonomia_tipo = tipo::listar();
		$estado = uf::custom('SELECT * FROM `geografia_uf` where pais_id=1  order by nome_codigo asc');

		$resultado_fator = fator::listar();
		
		
		$totalItems = $_SESSION['registoTotalFiltro'];
		
		$currentPage = $pag;
		
		if($filtro==null){
			$urlPattern = '/relatorio/filtro/(:num)';
		}else{
			$urlPattern = '/relatorio/filtro/(:num)/'.$filtro;
		}
		
		$paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
			


        $dados = [
			'titulo' => 'RELATÓRIOS FINAIS',
			'relatorios' => $relacao_rf,
			'taxonomia_tipo' => $taxonomia_tipo,
			'fator' => $resultado_fator,
			'estado' => $estado,
			'paginator' => $paginator,
			'total' => $totalItems,
			'paginaatual' => $pag,
			'fatores' => $fatorContribuinte
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
	
	
	public function databr($data, $opt) {
    // transforma a data para o padrão mysql
    if ($opt == 'en') {
        $dia = substr($data, 0, 2);
        $mes = substr($data, 3, 2);
        $ano = substr($data, 6, 4);
        $databr = $ano . "-" . $mes . "-" . $dia;
    } elseif ($opt == 'pt') {

        $dia = substr($data, 8, 2);
        $mes = substr($data, 5, 2);
        $ano = substr($data, 0, 4);
        $databr = $dia . "/" . $mes . "/" . $ano;
    } elseif ($opt == 'pt_time') {

        $dia = substr($data, 8, 2);
        $mes = substr($data, 5, 2);
        $ano = substr($data, 0, 4);
        $horas = substr($data, 11, 2);
        $minutos = substr($data, 14, 2);
        $segundos = substr($data, 17, 2);
        $databr = $dia . "/" . $mes . "/" . $ano . "  " . $horas . ":" . $minutos . ":" . $segundos;
    }
    return $databr;
}
	
}