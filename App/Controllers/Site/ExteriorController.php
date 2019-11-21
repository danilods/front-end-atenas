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
use \App\Models\RoteiroExteriorModel as roteiroMissao;
use \App\Models\UsuarioModel as Usuario;
use \App\Models\CidadeModel as Cidade;
use \App\Models\PaisModel as Pais;
use \App\Models\OrdemServicoAeroportoModel as passagem;
use App\Models\UfModel as uf;
use \App\Models\Site\ocorrenciaTripulanteModel as tripulante;
use \Acme\Classes\DB as potter;
use Acme\Classes\Redirect;

use PHPMailer;




class ExteriorController extends BaseController {





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
        $template = $this->twig->loadTemplate('Site/exterior.html');
        $template->display($dados);



    }



    public function cadastrar(){

        if(isset($_POST['submit'])){
        

                            //capturar os dados para cadastro da tabela ordem de serviço
                            
                            $missao_custo = (isset($_POST['missao_custo'])) ? 1 : 0;
                            
                            $om_pagadora = 1; //CENIPA
							
							$os_paga_por_outras_om = (isset($_POST['os_paga_outra_om'])) ? 1 : 0;
                          
                            $codigo_firpa = substr(uniqid(rand()), 0, 5);
                            $despesas_conta_propria = 'SIM';
                            
                            $conta_uniao = 'SIM';
                            
                            $valor_dolar = $_POST['valor_dolar'];

                            $disponibilidade_financeira = 'NAO';

                            $natureza_missao = $_POST['natureza_missao'];

                            $descricao_missao = $_POST['descricao_missao'];

                            $observacoes = $_POST['observacoes'];

                            $justificativa = $_POST['justificativa'];

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
                            
                            
                            $r = 1;     
                            $horasRoteiro = 0;


                            //roteiro
                             $data_ida_missao = $this->dataFormat($_POST['data_ida'],'en');
                             $hora_inicio_missao = $_POST['hora_inicio_missao'];
                            
                             $data_volta_missao = $this->dataFormat($_POST['data_volta'],'en');
                             $hora_retorno_missao = $_POST['hora_retorno_missao'];

                             $pais_missao = $_POST['pais_hidden'];
                             $detalhe_cidade = $_POST['cidade1'];
                             $endereco = $_POST['endereco'];
                             $estabelecimento = $_POST['estabelecimento'];

                            
                             
                            //cadastrar ordem de servico por quantidade de usuário          
                            for($i=0; $i<$quantidade_usuario; $i++) {

                                                                    $userCount++;
                                                                    
                                                                    // Traz todas informações refente ao usuário.
                                                                    $usuario_cpf = substr($participante[$i],-11);
                                                                    foreach(usuario::UsuarioCpf($usuario_cpf) as $usuario);
                                                                    foreach(usuario::ViewUsuarioCpf($usuario_cpf) as $unidade);
                                                             
                                                                    
                                                                    $codigoOrdemServico = $this->NumeroOrdemServico($om_pagadora);
                                                                    
                                                                    $objOs = new ordemServico(); 
                                                                    $objOs->status_os = $this->getStatusOs($usuario->setor_divisao_id, $om_pagadora, $unidade->unidade_id);
                                                                    $objOs->numero_ordem_servico = $codigoOrdemServico;
                                                                    $objOs->codigo_firpa = $codigo_firpa;
                                                                    
                                                                    $objOs->setor_id = $usuario->setor_divisao_id;
                                                                                                                                        
                                                                    
                                                                    $objOs->disponibilidade_financeira = $disponibilidade_financeira;
                                                                    $objOs->despesas_conta_propria = $despesas_conta_propria;
                                                                    $objOs->conta_uniao = $conta_uniao;                                                                 
                                                                    $objOs->descricao_missao = utf8_decode($descricao_missao);
                                                                    $objOs->observacoes = utf8_decode($observacoes);
                                                                    $objOs->justificativa_antecipacao = utf8_decode($justificativa);
                                                                    
                                                                    $objOs->acrescimento_deslocamento = (isset($_POST['deslocamento'])) ? 'SIM' : 'NAO';
                                                                    $objOs->pernoite_missao = (isset($_POST['pernoite'])) ? 'SIM' : 'NAO';

                                                                    $objOs->tipo_missao = $_POST['missao'];                                                                    
                                                                    $objOs->natureza_missao_id = $natureza_missao;
                                                                    $objOs->tb_usuario_id = $usuario->id;
																	$objOs->os_paga_outra_om = $os_paga_por_outras_om;
                                                                    $objOs->om_pagadora = $om_pagadora;
                                                                    $objOs->status_passagem = 1; //Com passagem incluso
                                                                    $objOs->missao_custo = $missao_custo;
                                                                    $objOs->tb_taxonomia_natureza_despesa_id = ($usuario->tb_posto_graduacao_id==21 || 
                                                                                                                $usuario->tb_posto_graduacao_id==22 || 
                                                                                                                $usuario->tb_posto_graduacao_id==23 || 
                                                                                                                $usuario->tb_posto_graduacao_id==24 || 
                                                                                                                $usuario->tb_posto_graduacao_id==25 || 
                                                                                                                $usuario->tb_posto_graduacao_id==26) ? 1 : 2;


                                                                    $calculo_diarias = $this->calcularDiariasExterior($data_ida_missao, $data_volta_missao, $usuario->cpf, $pais_missao);


                                                                    

                                                                    if($valor_dolar!=''){                                                                        	
                                                                        	$objOs->quantidade_diarias = $calculo_diarias[0];
		                                                                    $objOs->total_diarias = $calculo_diarias[0];
		                                                                    $objOs->custo_estimado = ($calculo_diarias[1]*$valor_dolar);
		                                                                    $objOs->custo_liquido = $calculo_diarias[1];
                                                                        }else{
                                                                        	$objOs->quantidade_diarias = $calculo_diarias[0];
		                                                                    $objOs->total_diarias = $calculo_diarias[0];
		                                                                    $objOs->custo_estimado = $calculo_diarias[1];
		                                                                    $objOs->custo_liquido = $calculo_diarias[1];
                                                                        }



                                                                    //salvar ordem de serviço
                                                                     $objOs->save();
                                                                     
                                                                   /*
                                                                    Bloco de inserção Roteiro Exterior
                                                                    */
                                                                        
                                                                                                                                               
                                                                     
                                                                        

                                                                    
                                                                        $objRot = new roteiroMissao(); 
                                                                        
                                                                        $objRot->ordem_servico_id = $objOs->id;
                                                                        
                                                                        $objRot->data_inicio = $data_ida_missao;
                                                                        $objRot->hora_inicio = $hora_inicio_missao;
                                                                        
                                                                        $objRot->date_termino = $data_volta_missao;
                                                                        $objRot->hora_final = $hora_retorno_missao;

                                                                        //data script para danilo
                                                                        $objRot->data_inicio_txt = $data_ida_missao;
                                                                        $objRot->data_fim_txt = $data_volta_missao;
                                                                        
                                                                        $objRot->pais_missao = $pais_missao;

                                                                        $objRot->detalhe_cidade = $detalhe_cidade;
                                                                        $objRot->endereco = $endereco;
                                                                        $objRot->estabelecimento = $estabelecimento;

                                                                        $objRot->dias_transito_ida = 1;
                                                                        $objRot->dias_transito_volta = 1;

                                                                        //Caso a api para consultar o valor do dolar funcione, será calculado pelo valor total da diária
                                                                        if($valor_dolar!=''){
                                                                        	$objRot->valor_diaria = ($calculo_diarias[1]*$valor_dolar);
                                                                        	$objRot->valor_diaria_dolar = $calculo_diarias[1];;
                                                                        	$objRot->cotacao_dolar = $valor_dolar;
                                                                        	$objRot->quantidade_dias = $calculo_diarias[0];
                                                                        }else{
                                                                            $objRot->quantidade_dias = $calculo_diarias[0];
                                                                        	$objRot->valor_diaria = $calculo_diarias[1];
                                                                        }
                                                                        

                                                                    
                                                                        
                                                                        // salvar roteiros
                                                                        $objRot->save();
                                                                     
                                                              
                                                                   


                                                                     
                                                                   
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


    public function calcularDiariasExterior($dataIda, $dataRetorno, $cpf, $pais){
        
        
        foreach (usuario::ViewUsuarioCpf($cpf) as $usuario);
        foreach (pais::ConsultarPais($pais) as $pais);

        $dia1=strtotime( $dataIda );
        $dia2=strtotime( $dataRetorno );
        $quantidade_dias = ( $dia2 - $dia1 ) / 86400;

        $diasTransitoIda =1;
        $diasTransitoVolta =1;
        

        switch ($pais->grupo) {
            case 'A':
                $usuarioDiaria = $usuario->alfa;

                break;
             case 'B':
                $usuarioDiaria = $usuario->bravo;

                break;
             case 'C':
                $usuarioDiaria = $usuario->charlie;

                break;
             case 'D':
                $usuarioDiaria = $usuario->delta;

                break;    
            
            default:
                # code...
                break;
        }

       
        $somaDiarias = $quantidade_dias+$diasTransitoIda+$diasTransitoVolta;
        
        $valor_diaria_total = ($usuarioDiaria * $somaDiarias); // multiplica o valor do dia pela quantidade de dias mais meia.  

     
         return array($somaDiarias,$valor_diaria_total);
        
        
            
    
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
						$om = 'PLAMTAX';
						break;
					case 2: 
						$om = 'EXTRA-PLAMTAX';
						break;
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
                    return $quantZero.'1/CENIPA/'.$om.'/'. date("Y").'';
                }else{
                    return $quantZero.''.$ultimo_reg_part[0].'/CENIPA/'.$om.'/'. date("Y").'';
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



/*

    Método para calculo auxilio alimentação e transporte
*/

    public function CalculoDescontoAuxilios($id_ordem_servico,$cpf,$hora_inicio_missao,$idaPeriodo,$voltaPeriodo){


        $Obj_ordem_servico = ordemServico::find('all',$id_ordem_servico);
         
         //inicio do calculo auxilio alimentação e transporte
        $horaLimiteDescontoAuxilio = mktime(17,59);
                        
        $desconto = $hora_inicio_missao < $horaLimiteDescontoAuxilio ? 0 : 1; //se o horário for anterior às 18 horas, vai descontar nas diárias da OS
                            
        //funcao para calcular quantidade de dias uteis do periodo todo     
        $quantidadeDiasUteisRoteiro = $this->getWorkingDays($idaPeriodo, $voltaPeriodo);
            
        $calculoAuxilio = $this->calcularAuxilio($cpf, $quantidadeDiasUteisRoteiro, $desconto);                     
        
        
        $Obj_ordem_servico->auxilio_alimentacao = $calculoAuxilio[0];
        $Obj_ordem_servico->auxilio_transporte = $calculoAuxilio[1];

        $calculo_desconto_total = $calculoAuxilio[0]+$calculoAuxilio[1];

        $Obj_ordem_servico->dias_uteis_alimentacao += $calculoAuxilio[2];
        
        $Obj_ordem_servico->dias_uteis_transporte += $calculoAuxilio[3];
        
        $total_custo_estimado_missao = ($Obj_ordem_servico->custo_estimado-$calculo_desconto_total);

        $Obj_ordem_servico->custo_estimado = $total_custo_estimado_missao;
        //valor total do desconto alimentação
        //$auxilioAlimentacao += $diariaAuxilioAlimentacao;
        //valor total do desconto transporte
        //$auxilioTransporte += $diariaAuxilioTransporte
        //return array($cpf);
        $Obj_ordem_servico->save();

        $this->calculoRoteiroMissao($id_ordem_servico,$total_custo_estimado_missao);
                                                               
    }

    public function calculoRoteiroMissao($id_ordem_servico,$total_custo_estimado_missao){

        foreach(roteiroMissao::find('all', array('conditions' => array('ordem_servico_id = ?', $id_ordem_servico))) as $dados);
        $atualizar = roteiroMissao::find('all',$dados->id);
        $atualizar->valor_diaria = $total_custo_estimado_missao;
        $atualizar->save();

    }


    //funcao para calcularAuxilio       
        public function calcularAuxilio($cpf, $diasUteis, $descontoAuxilio){
            

                $diarioAlimentacao = 0;
                
                $diarioTransporte = 0;
                
                foreach(usuario::UsuarioCpf($cpf) as $usu);
                
                $valorMensalAlimentacaoUsuario = $usu->auxilio_alimentacao;
                
                $valorMensalTransporteUsuario = $usu->auxilio_transporte;
                
                $diarioAlimentacao = $valorMensalAlimentacaoUsuario/22;
                
                $diarioTransporte = $valorMensalTransporteUsuario/22;
                
                $diasUteisAlimentacao = $diasUteis-$descontoAuxilio;
                
                $diasUteisTransporte = $diasUteis-$descontoAuxilio;
                
                $valorAlimentacaoRoteiro = $diarioAlimentacao * $diasUteis;
                
                $valorTransporteRoteiro = $diarioTransporte * $diasUteisTransporte;
                
                return array($valorAlimentacaoRoteiro, $valorTransporteRoteiro,$diasUteisAlimentacao, $diasUteis);
        }


//LISTA DE FERIADOS NO ANO
        /*Abaixo criamos um array para registrar todos os feriados existentes durante o ano.*/
        public function Feriados($ano){
           $dia = 86400;
           $datas = array();
           $datas['pascoa'] = easter_date($ano);
           $datas['sexta_santa'] = $datas['pascoa'] - (2 * $dia);
           $datas['carnaval'] = $datas['pascoa'] - (47 * $dia);
           $datas['corpus_cristi'] = $datas['pascoa'] + (60 * $dia);
           $feriados = array (
              '01/01',
              '02/02', // Navegantes
              date('d/m',$datas['carnaval']),
              date('d/m',$datas['sexta_santa']),
              date('d/m',$datas['pascoa']),
              '21/04',
              '01/05',
              date('d/m',$datas['corpus_cristi']),
              '20/09', // Revolução Farroupilha \m/
              '12/10',
              '02/11',
              '15/11',
              '25/12',
           );
          
        return $feriados;
        }  

                //verificar dias úteis
        function getWorkingDays($startDate, $endDate) {
            
            $ano = date("Y");
            
            $begin = strtotime($startDate);
            $end   = strtotime($endDate);
            if ($begin > $end) {
                echo "startdate is in the future! <br />";
                return 0;
            }
                    //array('01/01', '21/04', '01/05', '07/09', '12/10', '02/11', '15/11', '25/12');
                    else {
                        
                        
                        $holidays = $this->Feriados(date("Y"));
                        $weekends = 0;
                        $no_days = 0;
                        $holidayCount = 0;
                        while ($begin <= $end) {
                            $no_days++; // no of days in the given interval
                            if (in_array(date("d/m", $begin), $holidays)) {
                                $holidayCount++;
                            }
                            $what_day = date("N", $begin);
                            if ($what_day > 5) { // 6 and 7 are weekend days
                                $weekends++;
                            };
                            $begin += 86400; // +1 day
                        };
                $working_days = $no_days - $weekends - $holidayCount;

                return $working_days;
            }
        }   



}
