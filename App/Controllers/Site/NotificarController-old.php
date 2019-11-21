<?php

/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

namespace App\Controllers\Site;

use ActiveRecord\ActiveRecordException;
use ActiveRecord\Model;
use \App\Controllers\BaseController;
use \Acme\Classes\Sanitize;
use \Acme\Classes\RainCaptcha;
use \App\Models\OrdemServicoModel as ordemServico;
use \App\Models\UsuarioModel as Usuario;
use \App\Models\CidadeModel as Cidade;
use \App\Models\OrdemServicoAeroportoModel as passagem;
use App\Models\UfModel as uf;
use \App\Models\Site\ocorrenciaTripulanteModel as tripulante;
use \Acme\Classes\DB as potter;
use Acme\Classes\Redirect;

use PHPMailer;
//use \Acme\Classes\Smtp\SMTP;





class NotificarController extends BaseController {





    public function index() {

	$data = date("Y-m-d");
	$timestamp = date('d/m/Y', strtotime("+10 days",strtotime($data)));
       
		
		

        //$categoria = categoria::listar();
		$estado = uf::custom('SELECT * FROM `geografia_uf` where pais_id=1  order by nome_codigo asc');
        $dados = [

            'titulo' => 'Ordem de Serviço',

            'logo' => 'CENIPA',
	    'data_embarque' => $timestamp,

    
        ];
        $template = $this->twig->loadTemplate('Site/ordem_servico.html');
        $template->display($dados);



    }



    public function cadastrar(){

        if(isset($_POST['submit'])){
		

        					//capturar os dados para cadastro da tabela ordem de serviço

		                    $missao = $_POST['missao'];

		                    $data_ida_missao = $this->dataFormat($_POST['data_ida'],'en');
							$hora_inicio_missao = $_POST['hora_inicio_missao'];
							
							$data_volta_missao = $this->dataFormat($_POST['data_volta'],'en');
							$data_retorno_missao = $_POST['hora_retorno_missao'];

							$cidade = $_POST['cidade_hidden'];
							
							$despesas_conta_propria = $_POST['despesasporconta'];
							
							$conta_uniao = $_POST['conta_uniao'];
							
							$aeronave_utilizada = $_POST['tipo_aeronave'];

		                    $pernoite = $_POST['pernoite'];

		                    $adicional_deslocamento = $_POST['deslocamento'];

		                    $disponibilidade_financeira = $_POST['disponibilidade'];

		                    $natureza_missao = $_POST['natureza_missao'];

		                    $observacoes = $_POST['observacoes'];

		                    $justificativa_dez_dias = $_POST['justificativa'];


		                    //$usuario_id = 1;
							
							
							$data = date("Y-m-d"); //data atual
							$data_mais_dez_dias = date('Y-m-d', strtotime("+10 days",strtotime($data)));						
				
							//Verificar se a data é inferiar a 10 dias							
							if($data_ida_missao < $data_mais_dez_dias && $justificativa_dez_dias=='' ){
								echo "<script>alert('Erro: O período mínimo para solicitação da passagem é ".date('d/m/Y', strtotime("+10 days",strtotime($data)))." preencha o motivo da antecipação.')</script>";
								echo "<script>window.history.go(-1)</script>";
								exit();
							}


			                    $userCount = 0;
 
			                 	$quantidade_usuario = $_POST['total_usuario'];
								$participante = array_values((array_filter($_POST['usuario'])));

			                   	$cont=0;
			                    $contTrecho=0;
								
								// Verificar se foi acrescentado algum usuario no formulário
								
								if($quantidade_usuario==0){
									echo "<script>alert('Erro: é necessário adicionar ao menos um participante(s).')</script>";
									echo "<script>window.history.go(-1)</script>";
									exit();
								}
								
								
								
			                     
			                    //cadastrar ordem de servico por quantidade de usuário          
			                    for($i=0; $i<$quantidade_usuario; $i++) {

			                    	 								$userCount++;
																	
																	// Traz todas informações refente ao usuário.
																	$usuario_cpf = substr($participante[$i],-11);
																	foreach(usuario::UsuarioCpf($usuario_cpf) as $usuario);
																	foreach(usuario::ViewUsuarioCpf($usuario_cpf) as $unidade);
																	
																	//Verifica quantidade de diárias que será paga. 
																	$informacoes_diaria = $this->calcularDiarias($data_ida_missao,$hora_inicio_missao,$data_volta_missao,$data_retorno_missao,$usuario->cpf, $cidade,$adicional_deslocamento);
																	
																	$codigoOrdemServico = $this->NumeroOrdemServico($unidade->nome_unidade);
																	
																	$objOs = new ordemServico(); 
                                                                    $objOs->status_os = 'PENDENTE';
																	$objOs->numero_ordem_servico = $codigoOrdemServico;
																	$objOs->setor_id = $usuario->setor_divisao_id;
                                                                    $objOs->inicio_missao = $data_ida_missao;
																	$objOs->hora_inicio_missao = $hora_inicio_missao;
                                                                    $objOs->retorno_missao = $data_volta_missao;
																	$objOs->hora_retorno_missao = $data_retorno_missao;
                                                                    $objOs->cidade_id = $cidade;
																	$objOs->tipo_aeronave_utilizada = $aeronave_utilizada;
                                                                    $objOs->pernoite_missao = $pernoite;
                                                                    $objOs->acrescimento_deslocamento = $adicional_deslocamento;
                    			                             		$objOs->disponibilidade_financeira = $disponibilidade_financeira;
																	$objOs->despesas_conta_propria = $despesas_conta_propria;
																	$objOs->conta_uniao = $conta_uniao;																	
                                                                    $objOs->observacoes = utf8_decode($observacoes);
																	$objOs->justificativa_antecipacao = utf8_decode($justificativa_dez_dias);
                                                                    $objOs->quantidade_diarias = $informacoes_diaria[1];
                                                                    $objOs->total_diarias = $informacoes_diaria[1];
                                                                    $objOs->custo_estimado = $informacoes_diaria[0];
                                                                    $objOs->natureza_missao_id = $natureza_missao;
																	$objOs->tb_usuario_id = $usuario->id;
																	$objOs->tb_natureza_despesa_id = ($usuario->tb_posto_graduacao_id==21) ? 1 : 2;


																	//salvar ordem de serviço
																	 $objOs->save();
																			
																						//obter id da ordem de serviço cadastrada
																						$ordem_servido_id = $objOs->id;
																																						
																						$trechoCount = 0;																						 
																				
																						$trecho_data = $_POST['data'];
																						$trecho_hora = $_POST['hora'];
																						
																						$quantidadeTrecho = 0;
																						
																						$quantidade_trecho = count($_POST['id_trecho_origem']);
																						
																						$treco_origem = $_POST['id_trecho_origem'];
																						$treco_destino = $_POST['id_trecho_destino'];
																						$cpf_formulario = $_POST['cpf'];
																						
																						for($j =0; $j<$quantidade_trecho; $j++) {
																					   
																							if($cpf_formulario[$j]==$usuario->cpf){
																							
																								$objPassagem = new passagem();
																								$objPassagem->ordem_servico_id = $ordem_servido_id;
																								$objPassagem->data_viagem = $this->dataFormat($trecho_data[$j],'en');
																								$objPassagem->horario_viagem = $trecho_hora[$j];
																								$objPassagem->aerodromo_origem_id = $_POST['id_trecho_origem'][$j];
																								$objPassagem->aerodromo_destino_id = $_POST['id_trecho_destino'][$j];
																								$objPassagem->valor_passagem = 0.00;
																								$objPassagem->tb_natureza_despesa_id = 5;						                                                        
																								$objPassagem->save();
																								$quantidadeTrecho++;
																							}
																					  
																						}
																						// Verifica quantidade de deslocamento realizado e atualiza no bd.
																						if($adicional_deslocamento=='SIM'){
																								if($quantidadeTrecho==1){
																									$quantidadeTrecho=2;
																								}
																								ordemServico::UpdateAcresDeslocamento($ordem_servido_id,$quantidadeTrecho-1);
																						}else{
																								ordemServico::UpdateAcresDeslocamento($ordem_servido_id,0);
																						}

																	 
																   
															}
															
											$informe = [
													'titulo' => 'Ordem de Serviço',
													'logo' => 'CENIPA',
													'codigo' => $codigoOrdemServico,
													'status' => ($i>0) ? true : false
													 ];
											$template = $this->twig->loadTemplate('Site/feedback_cadastro.html');
											$template->display($informe);
											
																	
																

                                        }
                                }
								
								
	public function calcularDiarias($dataIda, $horaIda, $dataRetorno, $horaRetorno, $cpf, $cidade, $deslocamento){
		
		foreach (usuario::ViewUsuarioCpf($cpf) as $usu);
		foreach (cidade::ConsultarCidade($cidade) as $ci);
	
		//Calculo da quantidade de dias	
		$dia1=strtotime( $dataIda );
		$dia2=strtotime( $dataRetorno );
		$quantidade_dias = ( $dia2 - $dia1 ) / 86400;
		
		
		$adicional_deslocamento = ($deslocamento=='SIM') ? '95,00' : 0;
		$tipo_diaria = $this->TipoDiaria($ci->diaria);	//pega o campo referente à cidade.
		$valor_diaria_total = ($usu->$tipo_diaria * $quantidade_dias) + $usu->meia_diaria + $adicional_deslocamento; // multiplica o valor da dia pela quantidade de dias mais meia.
	
	
		
		//Se a missão for realizada no mesmo dia, verifica se será pago meia diária.
		if($quantidade_dias==0){
		
		$horaInicial = explode( ':', $horaIda );
		$horaFinal = explode( ':', $horaRetorno );

		$horaIni = mktime( $horaInicial[0], $horaInicial[1]);
		$horaFim = mktime( $horaFinal [0], $horaFinal [1]);

		$horaDiferenca = $horaFim - $horaIni;
		
		 if(date('H',$horaDiferenca )>='08'){
			return array($usu->meia_diaria,"0,5",$adicional_deslocamento);
		 }else{
			return array(0,"0,0",$adicional_deslocamento);
		 }
		
		
		
		}else{
		
			return array($valor_diaria_total,$quantidade_dias.",5",$adicional_deslocamento);
		
		}
			
	
	}
	

	function TipoDiaria($diaria){
		switch ($diaria){
			case 50:
				return 'cinquenta';
			break;        
			case 70:
				return 'setenta';
			break;
			case 80:
				return 'oitenta';
			break;
			case 90:
				return 'noventa';
			break;
		}
	}


	public function NumeroOrdemServico($unidade){
	
                $ano_corrente =  date("Y");
				
				foreach(ordemServico::ConsultarOrdemServico($unidade,$ano_corrente) as $ordem_servico);


                $ultimo_reg_part = explode("/", $ordem_servico->numero_ordem_servico);
 
                
               $ultimo_reg_part[0]++;                
                
                $limparZero = ltrim($ultimo_reg_part[0], "0");
                if(strlen($limparZero)==null || strlen($limparZero)==1){
                    $quantZero = '000';
                }elseif(strlen($limparZero)==2){
                    $quantZero = '00';
                }elseif(strlen($limparZero)==3){
                    $quantZero = '0';
                }else{
                    $quantZero = '';
                }               

                if(!isset($ultimo_reg_part[1])){
                    return $quantZero.'1/'.$unidade.'/'. date("Y").'';
                }else{
                    return $quantZero.''.$ultimo_reg_part[0].'/'.$unidade.'/'. date("Y").'';
                }
	
	}
	
	public function dataFormat($data, $opt) {
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
