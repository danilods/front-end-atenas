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




class TesteosController extends BaseController {





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
        $template = $this->twig->loadTemplate('Site/testeos.html');
        $template->display($dados);



    }



    public function cadastrar(){

        if(isset($_POST['submit'])){
        

                            //capturar os dados para cadastro da tabela ordem de serviço

                            $missao = $_POST['missao'];
                            
                            $passagem_aerea = $_POST['passagem_aerea'];

                            $missao_custo = (isset($_POST['missao_custo'])) ? 1 : 0;
                            
                            $om_pagadora = $_POST['om_pagadora'];
                            
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
                                                                    
                                                                    $codigoOrdemServico = $this->NumeroOrdemServico($unidade->nome_unidade);
                                                                    
                                                                    $objOs = new ordemServico(); 
                                                                    $objOs->status_os = $this->getStatusOs($usuario->setor_divisao_id);
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
                                                                    $objOs->om_pagadora = $om_pagadora;
                                                                    $objOs->status_passagem = ($passagem_aerea=='SIM') ? 1 : 0;
                                                                    $objOs->missao_custo = $missao_custo;
                                                                    $objOs->tb_taxonomia_natureza_despesa_id = ($usuario->tb_posto_graduacao_id==21 || 
                                                                                                                $usuario->tb_posto_graduacao_id==22 || 
                                                                                                                $usuario->tb_posto_graduacao_id==23 || 
                                                                                                                $usuario->tb_posto_graduacao_id==24 || 
                                                                                                                $usuario->tb_posto_graduacao_id==25 || 
                                                                                                                $usuario->tb_posto_graduacao_id==26) ? 1 : 2;


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

                                                                            $idaTrechoDesconto[] = $data_ida_missao;         
                                                                            $voltaTrechoDesconto[] = $data_volta_missao;

                                                                            foreach (cidade::ConsultarCidade($cidade) as $ci);                                                                      
                                                                            $percentualCidade[] = $ci->diaria;

                                                                            $horaInicial = explode( ':', $hora_inicio_missao );

                                                                            $horaFinal = explode( ':', $hora_retorno_missao );
                                                                            $horaIni = mktime( $horaInicial[0], $horaInicial[1]);
                                                                            $horaInicial_mktime[] = $horaIni;                                                                       
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


                                                                     /*
                                                                         Metodo para calcular desconto no auxilio transporte e alimentação
                                                                        */

                                                                        $this->CalculoDescontoAuxilios($objOs->id, $usuario->cpf,$horaInicial_mktime[0],$idaTrechoDesconto[0],$voltaTrechoDesconto[($quantidade_roteiro-1)]);




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
    
    public function getStatusOs($idSetor){
        
        $arraySetores = array(
            27 => 'CONFIRMADA',
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
        }else {
            return $key = 'PENDENTE';
        }

    
    }
    



    public function NumeroOrdemServico($unidade){
    
                $ano_corrente =  date("Y");
                
                $numeracao = ordemServico::ConsultarOrdemServico($unidade,$ano_corrente);
                
                if(empty($numeracao)){
                    $resultNumeracao = array($unidade,1);
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
