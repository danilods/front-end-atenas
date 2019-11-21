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
use \App\Models\RoteiroMissaoModel as roteiroMissao;
use \App\Models\UsuarioModel as Usuario;
use \App\Models\CidadeModel as Cidade;
use \App\Models\OrdemServicoAeroportoModel as passagem;
use App\Models\UfModel as uf;
use \App\Models\Site\ocorrenciaTripulanteModel as tripulante;
use \Acme\Classes\DB as potter;
use Acme\Classes\Redirect;

use PHPMailer;




class NotificarController extends BaseController {





  public function index() {

	$data = date("Y-m-d");
	$timestamp = date('d/m/Y', strtotime("+10 days",strtotime($data)));
       
		
		

        //$categoria = categoria::listar();
		$estado = uf::custom('SELECT * FROM `geografia_uf` where pais_id=1  order by nome_codigo asc');
		$om_pagadora = Cidade::custom('SELECT id, nome_unidade FROM `tb_unidade` order by nome_unidade');
        $dados = [

            'titulo' => 'Ordem de Serviço',
			'om_pagadora' => $om_pagadora,
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
                            
                            $passagem_aerea = $_POST['passagem_aerea'];

                            $missao_custo = (isset($_POST['missao_custo'])) ? 1 : 0;
                            
                            $om_pagadora = $_POST['om_pagadora'];
							
							$os_paga_por_outras_om = (isset($_POST['os_paga_outra_om'])) ? 1 : 0;
                          
                            $codigo_firpa = substr(uniqid(rand()), 0, 5);
                            $despesas_conta_propria = 'SIM';
                            
                            $conta_uniao = 'SIM';
                            
                            

                            $disponibilidade_financeira = 'NAO';

                            $natureza_missao = $_POST['natureza_missao'];

                            $observacoes = $_POST['observacoes'];

                            $justificativa_dez_dias = $_POST['justificativa'];

                            $userCount = 0;

                            $quantidade_usuario = $_POST['total_usuario'];
                            $participante = array_values((array_filter($_POST['usuario'])));
                            
                            $quantidade_roteiro = $_POST['total_roteiro'];

                            $cont=0;
                            $contTrecho=0;
                            
                            // Verificar se foi acrescentado algum usuario no formulário
                            
                            if($quantidade_usuario==0){
                                echo "<script>alert('Erro: é necessário adicionar ao menos um participante(s).')</script>";
                                echo "<script>window.history.go(-1)</script>";
                                exit();
                            }
                            
                            
                            $r = 1;     
                            $horasRoteiro = 0;
                            
                             
                            //cadastrar ordem de servico por quantidade de usuário          
                            for($i=0; $i<$quantidade_usuario; $i++) {

                                                                    $userCount++;
                                                                    
                                                                    // Traz todas informações refente ao usuário.
                                                                    $usuario_cpf = substr($participante[$i],-11);
                                                                    foreach(usuario::UsuarioCpf($usuario_cpf) as $usuario);
                                                                    foreach(usuario::ViewUsuarioCpf($usuario_cpf) as $unidade);
                                                                    

                                                                    //echo "<script>alert('".$missao_custo."')</script>";
                                                                    //Verifica quantidade de diárias que será paga. 
                                                                    //$informacoes_diaria = $this->calcularDiarias($data_ida_missao,$hora_inicio_missao,$data_volta_missao,$data_retorno_missao,$usuario->cpf, $cidade,$adicional_deslocamento);                                                                
                                                                    
                                                                    $codigoOrdemServico = $this->NumeroOrdemServico($om_pagadora);
                                                                    
                                                                    $objOs = new ordemServico(); 
                                                                    $objOs->status_os = $this->getStatusOs($usuario->setor_divisao_id, $om_pagadora, $unidade->unidade_id);
                                                                    $objOs->numero_ordem_servico = $codigoOrdemServico;
                                                                    $objOs->codigo_firpa = $codigo_firpa;
                                                                    
                                                                    $objOs->setor_id = $usuario->setor_divisao_id;
                                                                                                                                        
                                                                    
                                                                    $objOs->disponibilidade_financeira = $disponibilidade_financeira;
                                                                    $objOs->despesas_conta_propria = $despesas_conta_propria;
                                                                    $objOs->conta_uniao = $conta_uniao;                                                                 
                                                                    $objOs->descricao_missao = utf8_decode($missao);
                                                                    $objOs->observacoes = utf8_decode($observacoes);
                                                                    $objOs->justificativa_antecipacao = utf8_decode($justificativa_dez_dias);
                                                                    //$objOs->quantidade_diarias = $informacoes_diaria[1];
                                                                    //$objOs->total_diarias = 0.5;
                                                                    //$objOs->custo_estimado = $informacoes_diaria[0];
                                                                    $objOs->natureza_missao_id = $natureza_missao;
                                                                    $objOs->tb_usuario_id = $usuario->id;
																	$objOs->os_paga_outra_om = $os_paga_por_outras_om;
                                                                    $objOs->om_pagadora = $om_pagadora;
                                                                    $objOs->status_passagem = ($passagem_aerea=='SIM') ? 1 : 0;
                                                                    $objOs->missao_custo = $missao_custo;
                                                                    $objOs->tb_taxonomia_natureza_despesa_id = ($usuario->tb_posto_graduacao_id==21 || 
                                                                                                                $usuario->tb_posto_graduacao_id==22 || 
                                                                                                                $usuario->tb_posto_graduacao_id==23 || 
                                                                                                                $usuario->tb_posto_graduacao_id==24 || 
                                                                                                                $usuario->tb_posto_graduacao_id==25 || 
                                                                                                                $usuario->tb_posto_graduacao_id==26) ? 1 : 2;

                                                                    //caso exista mais de um participante na mesma missão o primeiro terá valor 1 os demais como 0
                                                                    if($userCount==1){
                                                                    	$objOs->grupo_os_ativo = 1;
                                                                    }else{
                                                                    	$objOs->grupo_os_ativo = 0;
                                                                    }
                                                                    
                                                                    //salvar ordem de serviço
                                                                     $objOs->save();
                                                                     
                                                                    $countControle = 0;
                                                                    
                                                                    for($p=0; $p<$quantidade_roteiro; $p++) {
                                                                        
                                                                        $countControle++;
                                                                        
                                                                        $data_ida_missao = $this->dataFormat($_POST['data_ida'][$p],'en');
                                                                        $hora_inicio_missao = $_POST['hora_inicio_missao'][$p];
                                                                        
                                                                        $data_volta_missao = $this->dataFormat($_POST['data_volta'][$p],'en');
                                                                        $hora_retorno_missao = $_POST['hora_retorno_missao'][$p];

                                                                        $cidade = $_POST['cidade_hidden'][$p];
                                                                        $adicional_deslocamento = (isset($_POST['deslocamento'][$p])) ? 1 : 0;
                                                                        $pernoite = (isset($_POST['pernoite'][$p])) ? 1 : 0;
                                                                        $tipo_transporte = $_POST['tipo_transporte'][$p];



                                                                        $data = date("Y-m-d"); //data atual
                                                                        $data_mais_dez_dias = date('Y-m-d', strtotime("+10 days",strtotime($data)));                        
                                                            
                                                                        //Verificar se a data é inferiar a 10 dias                          
                                                                        if($data_ida_missao < $data_mais_dez_dias && $justificativa_dez_dias=='' ){
                                                                            echo "<script>alert('Erro: O período mínimo para solicitação da passagem é ".date('d/m/Y', strtotime("+10 days",strtotime($data)))." preencha a justificativa da antecipação.')</script>";
                                                                            echo "<script>window.history.go(-1)</script>";

                                                                            //deletar OS com data inferior a 10 dias da missão
                                                                                ordemServico::deletar($objOs->id);

                                                                            exit();
                                                                        }






                                                                            $idaTrecho[] = strtotime($data_ida_missao);         
                                                                            $voltaTrecho[] = strtotime($data_volta_missao);
                                                                            foreach (cidade::ConsultarCidade($cidade) as $ci);                                                                      
                                                                            $percentualCidade[] = $ci->diaria;

                                                                            $horaInicial = explode( ':', $hora_inicio_missao );         
                                                                            $horaFinal = explode( ':', $hora_retorno_missao );
                                                                            $horaIni = mktime( $horaInicial[0], $horaInicial[1]);                                                                       
                                                                            $horaFim = mktime( $horaFinal [0], $horaFinal [1]);                                                                     
                                                                            $diferencaoHoras = $horaFim - $horaIni;                                                                     
                                                                            $horasRoteiro += $diferencaoHoras;

                                                                            

                                                                            $datasIguais = 0; 
                                                                            $meiaDiaria = 0;



                                                                            if($countControle == $quantidade_roteiro){
                                                                            
                                                                            
                                                                            $meiaDiaria = 0.5;
                                                                            
                                                                                                                                                                                                                                
                                                                            
                                                                            //verifica quantas datas de ida iguais dentro do array
                                                                            $datasIdasIguais = count($idaTrecho);
                                                                            
                                                                            //verifica quantas datas de volta iguais dentro do array
                                                                            $datasVoltasIguais = count($voltaTrecho);
                                                                           
                                                                            //se a quantidade de datas iguais ao num de roteiros, entao eles estao no mesmo dia
                                                                            //if($datasIdasIguais == $countControle and $datasVoltasIguais == $countControle){
                                                                            if(strtotime($data_ida_missao) == strtotime($data_volta_missao)){

                                                                                //roteiros no mesmo dia             
                                                                                $datasIguais =1;
                                                                                
                                                                                $maiorCidade = max($percentualCidade);
                                                                                
                                                                                $diariaRoteiroMissao = $this->calcularDiariasTrecho($data_ida_missao,$hora_inicio_missao,$data_volta_missao,$data_volta_missao,$usuario->cpf, $cidade,$adicional_deslocamento,$meiaDiaria, $datasIguais, $maiorCidade, $horasRoteiro,$missao_custo,$natureza_missao);
                                                                                
                                                                            }else {
                                                                                
                                                                                $maiorCidade = '';
                                                                                $diariaRoteiroMissao = $this->calcularDiariasTrecho($data_ida_missao,$hora_inicio_missao,$data_volta_missao,$data_volta_missao,$usuario->cpf, $cidade,$adicional_deslocamento,$meiaDiaria, 0, $maiorCidade, $horasRoteiro,$missao_custo,$natureza_missao);
                                                                            }
                                                                            
                                                                            
                                                                            
                                                                            
                                                                        }else {
                                                                            
                                                                            $maiorCidade = max($percentualCidade);

                                                                            $diariaRoteiroMissao = $this->calcularDiariasTrecho($data_ida_missao,$hora_inicio_missao,$data_volta_missao,$data_volta_missao,$usuario->cpf, $cidade,$adicional_deslocamento,$meiaDiaria, $datasIguais, $maiorCidade, $horasRoteiro,$missao_custo,$natureza_missao);

                                                                                                                     
                                                                        }

                                                                            
                                                                        //echo "<script>alert('".$datasIguais."')</script>";

                                                                        
                                                                                                                                                 
                                                                        $objRot = new roteiroMissao(); 
                                                                        
                                                                        $objRot->ordem_servico_id = $objOs->id;
                                                                        
                                                                        $objRot->data_inicio = $data_ida_missao;
                                                                        $objRot->hora_inicio = $hora_inicio_missao;
                                                                        
                                                                        $objRot->date_termino = $data_volta_missao;
                                                                        $objRot->hora_final = $hora_retorno_missao;

                                                                        //data script para danilo
                                                                        $objRot->data_inicio_txt = $data_ida_missao;
                                                                        $objRot->data_fim_txt = $data_volta_missao;
                                                                        
                                                                        $objRot->geografia_cidade_id = $cidade;
                                                                        $objRot->transporte_utilizado = $tipo_transporte;
                                                                        $objRot->pernoite = $pernoite;
                                                                        $objRot->adicional_deslocamento = $adicional_deslocamento;
                                                                        $objRot->valor_diaria = $diariaRoteiroMissao[0];
                                                                        $objRot->quantidade_diarias = $diariaRoteiroMissao[1];
                                                                    
                                                                    
                                                                        //Soma total de diárias e valores inseridos no roteiro
                                                                        $this->calcularTotalDiarias($objOs->id, $diariaRoteiroMissao[1],$diariaRoteiroMissao[1],$diariaRoteiroMissao[0],$diariaRoteiroMissao[3],$missao_custo);

                                                                        
                                                                        // salvar roteiros
                                                                        $objRot->save();
                                                                     
                                                                    }       
                                                                            if($passagem_aerea=='SIM'){
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
                                                                                                $objPassagem->tb_taxonomia_natureza_despesa_id = 3;                                                                             
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

    

    public function calcularDiariasTrecho($dataIda, $horaIda, $dataRetorno, $horaRetorno, $cpf, $cidade, $deslocamento, $meiaDiaria, $datasIguais, $maiorCidade, $horasRoteiro,$missao_custo,$natureza_missao){
        
        foreach (usuario::ViewUsuarioCpf($cpf) as $usu);
        foreach (cidade::ConsultarCidade($cidade) as $ci);
    
        //Calculo da quantidade de dias 
        $dia1=strtotime( $dataIda );
        $dia2=strtotime( $dataRetorno );
        $quantidade_dias = ( $dia2 - $dia1 ) / 86400;
        $horaLimite = 28800; // caso ultrapasse 8hs receberá meia diária;

        
        
        $adicional_deslocamento = ($deslocamento==1) ? 95.00 : 0;
        
        //$tipo_diaria = $this->TipoDiaria($ci->diaria);    //pega o campo referente ?cidade.

        //$usu_tipo_diaria_meia = $usu->$tipo_diaria * $meia_diaria;


        switch ($ci->diaria) {
            case 50:
                $usuarioDiaria = $usu->cinquenta;

                break;
             case 70:
                $usuarioDiaria = $usu->setenta;

                break;
             case 80:
                $usuarioDiaria = $usu->oitenta;

                break;
             case 90:
                $usuarioDiaria = $usu->noventa;

                break;    
            
            default:
                # code...
                break;
        }



        $somaDiarias = $quantidade_dias + $meiaDiaria;
        
        $valor_diaria_total = ($usuarioDiaria * $somaDiarias) + $adicional_deslocamento ; // multiplica o valor do dia pela quantidade de dias mais meia.   

        //verificar se será sem custo para uniao
        if($missao_custo==1){

            $valor_diaria_total = 0.00;
            $somaDiarias = 0;

        }
        // caso a natureza da missão seja de tripulante o valor será zerado pois quem faz o pagamento das diárias é o GABINETE.
        if($natureza_missao==5){
        	$valor_diaria_total = 0.00;
		}

        //Caso a missão ultrapasse 1 dia os calculos serão em outra linha 363.aprox.
        //Se a missão for realizada no mesmo dia, verifica se será pago meia diária.
        if($datasIguais>0){

        // Caso a missão seja inferior a 12hs
         if($horasRoteiro>=$horaLimite){

            return array($valor_diaria_total,"0.5",$adicional_deslocamento,1);
         }else{
            return array($adicional_deslocamento,0,$adicional_deslocamento,0);
         }
        
        
        
        }else{
        
            return array($valor_diaria_total,$somaDiarias,$adicional_deslocamento,1);
        
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


    
    /*public function calcularDiarias($dataIda, $horaIda, $dataRetorno, $horaRetorno, $cpf, $cidade, $deslocamento){
        
        foreach (usuario::ViewUsuarioCpf($cpf) as $usu);
        foreach (cidade::ConsultarCidade($cidade) as $ci);
    
        //Calculo da quantidade de dias 
        $dia1=strtotime( $dataIda );
        $dia2=strtotime( $dataRetorno );
        $quantidade_dias = ( $dia2 - $dia1 ) / 86400;
        
        
        $adicional_deslocamento = ($deslocamento=='SIM') ? 95.00 : 0;
        $tipo_diaria = $this->TipoDiaria($ci->diaria);  //pega o campo referente ?cidade.
        
        $usu_tipo_diaria_meia = $usu->$tipo_diaria * 0.5;
        
        $valor_diaria_total = ($usu->$tipo_diaria * $quantidade_dias) + $usu_tipo_diaria_meia + $adicional_deslocamento ; // multiplica o valor do dia pela quantidade de dias mais meia.   
        //Se a missão for realizada no mesmo dia, verifica se será pago meia diária.
        if($quantidade_dias==0){
        
        $horaInicial = explode( ':', $horaIda );
        $horaFinal = explode( ':', $horaRetorno );

        $horaIni = mktime( $horaInicial[0], $horaInicial[1]);
        $horaFim = mktime( $horaFinal [0], $horaFinal [1]);

        $horaDiferenca = $horaFim - $horaIni;
        
         if(date('H',$horaDiferenca )>='08'){
            return array($usu->meia_diaria,"0.5",$adicional_deslocamento,1);
         }else{
            return array(0,0.00,$adicional_deslocamento,0);
         }
        
        
        
        }else{
        
            return array($valor_diaria_total,$quantidade_dias,$adicional_deslocamento,1);
        
        }
            
    
    }
    */
    
    public function calcularTotalDiarias($id, $__quantidade_diarias,$__total_diarias,$__custo_estimado,$__meia_diaria,$missao_custo){
        
        
        
                
        $atualizar = ordemServico::find('all',$id);


        //verificar se será sem custo para uniao, caso afirmativo os dados que foram calculados serão substituídos por 0.00.
        if($missao_custo==1){

            $__quantidade_diarias = 0;
            $__custo_estimado = 0;
            $__total_diarias = 0;
            $__meia_diaria = 0;

        }
        
        if($__quantidade_diarias==0.5){

            $atualizar->quantidade_diarias = $__quantidade_diarias;
            $atualizar->total_diarias = $__total_diarias;
            $atualizar->meia_diaria = $__meia_diaria;
            
                if($__custo_estimado > $atualizar->custo_estimado){
                    $atualizar->custo_estimado = $__custo_estimado;
                }               
            
            $atualizar->save();
        }else{
        
            $atualizar->quantidade_diarias += $__quantidade_diarias;
            $atualizar->total_diarias += $__total_diarias;
            $atualizar->custo_estimado += $__custo_estimado;
            $atualizar->meia_diaria = $__meia_diaria;
            $atualizar->save();
            
        }
        
        
    }
    
    public function getStatusOs($idSetor, $omPagadora, $unidadeUser){
        
        $arraySetores = array(
            
			27 => 'CONFIRMADA',
			21 => 'CONFIRMADA',
			22 => 'CONFIRMADA',
			23 => 'CONFIRMADA',
			24 => 'CONFIRMADA',
			25 => 'CONFIRMADA',
			26 => 'CONFIRMADA',
			165 => 'CONFIRMADA',
			166 => 'CONFIRMADA',
            167 => 'CONFIRMADA',
            168 => 'CONFIRMADA',
            131 => 'CONFIRMADA',
            132 => 'CONFIRMADA',
            138 => 'CONFIRMADA',
            144 => 'CONFIRMADA',
            151 => 'CONFIRMADA',
            157 => 'CONFIRMADA',
            163 => 'CONFIRMADA',
            164 => 'AUTORIZADA',
            169 => 'AUTORIZADA'
        );
        
        if(array_search($idSetor,array_keys($arraySetores))){         
            return $key = $arraySetores[$idSetor];          
        }elseif($omPagadora != $unidadeUser){
			return $key = 'CONFIRMADA';
		}
		else {
            return $key = 'PENDENTE';
        }

    
    }
    



    public function NumeroOrdemServico($unidade){
    
                $ano_corrente =  date("Y");
				
				switch($unidade){
					
					case 1:
						$om = 'CENIPA';
						break;
					case 2: 
						$om = 'SERIPA-I';
						break;
					case 3: 
						$om = 'SERIPA-II';
						break;
					case 4: 
						$om = 'SERIPA-III';
						break;
					case 5: 
						$om = 'SERIPA-IV';
						break;
					case 6: 
						$om = 'SERIPA-V';
						break;
					case 7: 
						$om = 'SERIPA-VI';
						break;
					case 8: 
						$om = 'SERIPA-VII';
						break;
					
				}
                
                $numeracao = ordemServico::ConsultarOrdemServico($om,$ano_corrente);
                
                if(empty($numeracao)){
                    $resultNumeracao = array($om,1);
                        $ultimo_reg_part = explode("/", $resultNumeracao[1]);
                    
                }else{
                    $resultNumeracao = $numeracao;                  
                    foreach($resultNumeracao as $ordem_servico);
                        $ultimo_reg_part = explode("/", $ordem_servico->numero_ordem_servico);
                }
                
                
                
 
                
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
                    return $quantZero.'1/'.$om.'/'. date("Y").'';
                }else{
                    return $quantZero.''.$ultimo_reg_part[0].'/'.$om.'/'. date("Y").'';
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
