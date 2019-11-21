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
//use \Acme\Classes\RainCaptcha;
use \App\Models\Site\RcsvOcorrenciaModel as ocorrencia;
use \App\Models\Site\RcsvOcorrenciaTipoModel as detalhe_ocorrencia;
use \App\Models\Site\RcsvNotificadorModel as notificador_ocorrencia;
use \App\Models\Site\RcsvSituacaoObservadaModel as situacao;
use \App\Models\Site\RcsvConsequenciaOcorrenciaModel as consequencia;
use \App\Models\Site\Aeronave as aeronave;
use \App\Models\Site\RcsvLocalSituacao as local;

use \App\Models\RcsvControleModel as Controle;

use App\Models\UfModel as uf;
use \Acme\Classes\DB as potter;
use Acme\Classes\Redirect;
use Acme\Classes\UploadFile as UploadFile;
use App\Models\Site\faseVooModel as fase;
use App\Models\Site\aeronaveCategoriaModel as categoria;

use PHPMailer;
//use \Acme\Classes\Smtp\SMTP;





class RcsvController extends BaseController {





    public function index() {
        /*$captcha = new RainCaptcha();
        $img ='';
        $img .= $captcha->getImage();*/

        $fase_voo = fase::listar();
        $categoria = categoria::listar();
		$consequencia = consequencia::listar();
		$situacao = situacao::listar();
		$local_situacao =  local::listar();
		
		$estado = uf::custom('SELECT * FROM `geografia_uf` where pais_id=1  order by nome_codigo asc');
		$tipo_missao = uf::custom('SELECT * FROM `rcsv_taxonomia_missao` order by tipo_missao asc');
		//$classificacao = uf::custom('SELECT id, classificacao_nome, area_ocorrencia FROM `rcsv_taxonomia_classificacao` order by area_ocorrencia, classificacao_nome asc');
        $dados = [

            'titulo' => 'RCSV - Relatório ao CENIPA para Segurança de Voo',

            'logo' => 'RCSV',
			'tipo_missao' => $tipo_missao,
            'categoria_aeronave' => $categoria,
			'consequencia_ocorrencia' => $consequencia,
			'situacao_observada' => $situacao,
			
			//'classificacao' => $classificacao,
			'estado' => $estado,
            //'imagem' => $img,
			'local' => $local_situacao


        ];
        $template = $this->twig->loadTemplate('Site/rcsv.html');
        $template->display($dados);



    }



    public function cadastrar(){



        if(isset($_POST['submit'])){

                  
                         
                    //rcsv_ocorrencia
					
					
					
					$situacao_rcsv = "ENTRADA";
					
					$tipo_reporte = $_POST['aviacao'];
					
					$classificacao = 150;
					
					
					
					$data_ocorrencia = $_POST['data'];
					
					$historico = $_POST['historico_ocorrencia'];
					
					$historico = mb_strtoupper($historico, 'UTF-8');
					
					$historico = utf8_decode($historico);
					
					
					$cidade = $_POST['cidade'];
					
				
					
					$aerodromo_ocorrencia = $_POST['aerodromo_id'];
					
					$local_situacao = $_POST['local_situacao'];
					
					$hora_local = $_POST['hora_local'];				

                    $tipo_notificador = $_POST['tipo'];
					
					
                    
					  
				
					//rcsv_ocorrencia_tipo
					
					
					$matricula = isset($_POST['matricula_anv_id']) ? $_POST['matricula_anv_id'] : 39146 ;   				
					$matricula_extra = isset($_POST['matricula_simples']) ? $_POST['matricula_simples'] : '';
										
                    $categoria = isset($_POST['categoria']) ? $_POST['categoria'] : 63;	    		

                    $missao = isset($_POST['missao']) ? $_POST['missao'] : 7;

                    $regra_voo = isset($_POST['regra']) ? $_POST['regra'] : '***';

                    $fase_voo = isset($_POST['fase_operacao']) ? $_POST['fase_operacao'] : 1;
					
					$situacao_observada = 6;
					
					$consequencia_ocorrencia = isset($_POST['consequencia_ocorrencia']) ? $_POST['consequencia_ocorrencia'] : 5;
					
					
					
					
										
					//rcsv_notificador
					
					$nome = $_POST['nome'];
					
					$nome = mb_strtoupper($nome, 'UTF-8');
					
					$nome = utf8_decode($nome);
					
					$cpf = $_POST['cpf'];
					
					$email = $_POST['email'];
					
					$num_habilitacao = $_POST['habilitacao'];
					
					$telefone = $_POST['telefone'];
					
					$empresa = isset($_POST['empresa_text']) ? $_POST['empresa_text'] : 'OUTRO';
					
					$empresa = mb_strtoupper($empresa, 'UTF-8');
					
					$empresa = utf8_decode($empresa);
										
					$empresa_id = $_POST['orgao_id'];
					
					
					
					//controle

					
					
					//$nome_anexo ;
					//$obs_encaminhamento = $_POST['descricao_arquivo']; 
					
					     // array com os dados da tabela ocorrencia_rcsv
						
						
						
					
						
						$numero_processo = ''; 
						
						
                        $attributes = array(
						
							'numero_rcsv' => $numero_processo,

                            'situacao_rcsv' => $situacao_rcsv,

							'tipo_reporte' => $tipo_reporte,
							
							'classificacao_id' => $classificacao,
							
							
                            'data_ocorrencia' => implode("-",array_reverse(explode("/",$data_ocorrencia))),
							
							
                            'rcsv_historico' => $historico,
							
                            'cidade_id' => $cidade,
                            							
                            'aerodromo_id' => $aerodromo_ocorrencia,
							

							
                            'hora_local' => $hora_local,
							
							'tipo_notificador_id' => $tipo_notificador,
							
							'local_situacao_id' => $local_situacao,

                        );


					   try{

							//cadastro na tabela ocorrencia_rcsv
                            $resultado = ocorrencia::cadastrar($attributes);
							
							
                            if($resultado!=false){
                                $status = array("status" => true);
                            }else{
                                $status = array("status" => false);
                            }
                            
							//retornar o id da tabela ocorrencia_rcsv
                            $rcsv_id = $resultado->id;

					
								$objOcorrenciatipo = new detalhe_ocorrencia();
								
										
										$objOcorrenciatipo->regra_de_voo = $regra_voo;
										
										$objOcorrenciatipo->ocorrencia_rcsv_id = $rcsv_id;

										$objOcorrenciatipo->aeronave_id = $matricula;
										
										$objOcorrenciatipo->aeronave_categoria_id = $categoria;
										
										$objOcorrenciatipo->matricula_extra = $matricula_extra;
										
										$objOcorrenciatipo->fasevoo_id = $fase_voo;
										
										$objOcorrenciatipo->missao_id = $missao;
										
										
										$objOcorrenciatipo->situacao_observada_id = $situacao_observada;
										
										$objOcorrenciatipo->consequencia_ocorrencia_id = $consequencia_ocorrencia;
										
										$objOcorrenciatipo->periodo_dia_id = 5;
										
										$objOcorrenciatipo->condicao_voo_id = 3;
										
										$objOcorrenciatipo->condicao_meteorologia_id = 13;
										
										$objOcorrenciatipo->condicao_trabalho_id = 7;
										
										
										$objOcorrenciatipo->save();
										

									
									

										
									
										$objNotificador = new notificador_ocorrencia();

										$objNotificador->nome = $nome;
										
										$objNotificador->cpf = $cpf;

										$objNotificador->telefone = $telefone;
										
										$objNotificador->email = $email;
										
																			
										
										$objNotificador->num_habilitacao = $num_habilitacao;
										
										$objNotificador->orgao_empresa = $empresa;
										
										
										$objNotificador->ocorrencia_id = $rcsv_id;
										
										$objNotificador->orgao_id = $empresa_id;
							
										$objNotificador->save();

											
										//controle
										
										
										//upload File
                                        /*

										if($_FILES['arquivo']['name'] != null){
											$dados = UploadFile::Enviar($_FILES['arquivo']['name'], $_FILES['arquivo']['size'], "rcsv" );
        									if ($dados['status'] == 'true') {
												$objControle = new Controle();
												$objControle->dia_encaminhamento = date('Y-m-d');
												$objControle->obs_encaminhamento = $obs_encaminhamento;
												$objControle->ocorrencia_id = $rcsv_id;
												$objControle->image_name = $dados['nome'];
												$objControle->save();
										
											}
										}
                                        */
										
										//$this->enviarEmail($attributes,$email);
										echo "<script>alert('Seu Reporte foi registrado e enviado para seu e-mail com sucesso!')</script>";
										echo "<script>window.location.href = 'http://www.cenipa.aer.mil.br/'</script>";

										exit();

									
									
									


                        }
                        catch(ActiveRecordException $e){
                            echo "ocorrencia simples não cadastrada".$e;
							echo "<script>alert('Erro: não foi possível registrar seu Relato. Tente novamente mais tarde!')</script>";
							echo "<script>window.history.go(-1)</script>";
							exit();

                        }



					
                  



						
		}
	}



    public function enviarEmail(array $attributes, $email){

		$data = date("d/m/Y", strtotime($attributes['data_ocorrencia']));
        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <!-- Facebook sharing information tags -->
        <meta property="og:title" content="Confirmação de reporte" />

        <title>CENIPA - NOTIFICAÇÃO DE OCORRÊNCIA</title>
        <style type="text/css">
            /* Client-specific Styles */
            #outlook a{padding:0;} /* Force Outlook to provide a "view in browser" button. */
            body{width:100% !important;} .ReadMsgBody{width:100%;} .ExternalClass{width:100%;} /* Force Hotmail to display emails at full width */
            body{-webkit-text-size-adjust:none;} /* Prevent Webkit platforms from changing default text sizes. */

            /* Reset Styles */
            body{margin:0; padding:0;}
            img{border:0; height:auto; line-height:100%; outline:none; text-decoration:none;}
            table td{border-collapse:collapse;}
            #backgroundTable{height:100% !important; margin:0; padding:0; width:100% !important;}


            body, #backgroundTable{
                /*@editable*/ background-color:#FAFAFA;
            }

            /**
            * @tab Page
            * @section email border
            * @tip Set the border for your email.
            */
            #templateContainer{
                /*@editable*/ border: 1px solid #DDDDDD;
            }

            /**
            * @tab Page
            * @section heading 1
            * @tip Set the styling for all first-level headings in your emails. These should be the largest of your headings.
            * @style heading 1
            */
            h1, .h1{
                /*@editable*/ color:#202020;
                display:block;
                /*@editable*/ font-family:Arial;
                /*@editable*/ font-size:34px;
                /*@editable*/ font-weight:bold;
                /*@editable*/ line-height:100%;
                margin-top:0;
                margin-right:0;
                margin-bottom:10px;
                margin-left:0;
                /*@editable*/ text-align:left;
            }

            /**
            * @tab Page
            * @section heading 2
            * @tip Set the styling for all second-level headings in your emails.
            * @style heading 2
            */
            h2, .h2{
                /*@editable*/ color:#202020;
                display:block;
                /*@editable*/ font-family:Arial;
                /*@editable*/ font-size:30px;
                /*@editable*/ font-weight:bold;
                /*@editable*/ line-height:100%;
                margin-top:0;
                margin-right:0;
                margin-bottom:10px;
                margin-left:0;
                /*@editable*/ text-align:left;
            }

            /**
            * @tab Page
            * @section heading 3
            * @tip Set the styling for all third-level headings in your emails.
            * @style heading 3
            */
            h3, .h3{
                /*@editable*/ color:#202020;
                display:block;
                /*@editable*/ font-family:Arial;
                /*@editable*/ font-size:26px;
                /*@editable*/ font-weight:bold;
                /*@editable*/ line-height:100%;
                margin-top:0;
                margin-right:0;
                margin-bottom:10px;
                margin-left:0;
                /*@editable*/ text-align:left;
            }

            /**
            * @tab Page
            * @section heading 4
            * @tip Set the styling for all fourth-level headings in your emails. These should be the smallest of your headings.
            * @style heading 4
            */
            h4, .h4{
                /*@editable*/ color:#202020;
                display:block;
                /*@editable*/ font-family:Arial;
                /*@editable*/ font-size:22px;
                /*@editable*/ font-weight:bold;
                /*@editable*/ line-height:100%;
                margin-top:0;
                margin-right:0;
                margin-bottom:10px;
                margin-left:0;
                /*@editable*/ text-align:left;
            }


            #templateHeader{
                /*@editable*/ background-color:#FFFFFF;
                /*@editable*/ border-bottom:0;
            }


            .headerContent{
                /*@editable*/ color:#202020;
                /*@editable*/ font-family:Arial;
                /*@editable*/ font-size:34px;
                /*@editable*/ font-weight:bold;
                /*@editable*/ line-height:100%;
                /*@editable*/ padding:0;
                /*@editable*/ text-align:center;
                /*@editable*/ vertical-align:middle;
            }


            .headerContent a:link, .headerContent a:visited, /* Yahoo! Mail Override */ .headerContent a .yshortcuts /* Yahoo! Mail Override */{
                /*@editable*/ color:#336699;
                /*@editable*/ font-weight:normal;
                /*@editable*/ text-decoration:underline;
            }

            #headerImage{
                height:auto;
                max-width:600px !important;
            }



            #templateContainer, .bodyContent{
                /*@editable*/ background-color:#FFFFFF;
            }

            .bodyContent div{
                /*@editable*/ color:#505050;
                /*@editable*/ font-family:Arial;
                /*@editable*/ font-size:14px;
                /*@editable*/ line-height:150%;
                /*@editable*/ text-align:left;
            }

            .bodyContent div a:link, .bodyContent div a:visited, /* Yahoo! Mail Override */ .bodyContent div a .yshortcuts /* Yahoo! Mail Override */{
                /*@editable*/ color:#336699;
                /*@editable*/ font-weight:normal;
                /*@editable*/ text-decoration:underline;
            }


            .templateButton{
                -moz-border-radius:3px;
                -webkit-border-radius:3px;
                /*@editable*/ background-color:#336699;
                /*@editable*/ border:0;
                border-collapse:separate !important;
                border-radius:3px;
            }


            .templateButton, .templateButton a:link, .templateButton a:visited, /* Yahoo! Mail Override */ .templateButton a .yshortcuts /* Yahoo! Mail Override */{
                /*@editable*/ color:#FFFFFF;
                /*@editable*/ font-family:Arial;
                /*@editable*/ font-size:15px;
                /*@editable*/ font-weight:bold;
                /*@editable*/ letter-spacing:-.5px;
                /*@editable*/ line-height:100%;
                text-align:center;
                text-decoration:none;
            }

            .bodyContent img{
                display:inline;
                height:auto;
            }



            #templateFooter{
                /*@editable*/ background-color:#FFFFFF;
                /*@editable*/ border-top:0;
            }



            .footerContent div{
                /*@editable*/ color:#707070;
                /*@editable*/ font-family:Arial;
                /*@editable*/ font-size:12px;
                /*@editable*/ line-height:125%;
                /*@editable*/ text-align:center;
            }


            .footerContent div a:link, .footerContent div a:visited, /* Yahoo! Mail Override */ .footerContent div a .yshortcuts /* Yahoo! Mail Override */{
                /*@editable*/ color:#336699;
                /*@editable*/ font-weight:normal;
                /*@editable*/ text-decoration:underline;
            }

            .footerContent img{
                display:inline;
            }


            #utility{
                /*@editable*/ background-color:#FFFFFF;
                /*@editable*/ border:0;
            }

			.std_content01 a:link{
				color: #fff;
			}
            #utility div{
                /*@editable*/ text-align:center;
            }

            #monkeyRewards img{
                max-width:190px;
            }
			a{ color: #fff }
			a:link {color: #fff}
			a:visited {color: #fff}
			a:hover {color: #fff}
			
			.tablecustom td{
				border-bottom: 1px #ccc solid;
			}
        </style>
    </head>
    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
        <center>
            <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="backgroundTable">
                <tr>
                    <td align="center" valign="top" style="padding-top:20px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="600" id="templateContainer">
                            <tr>
                                <td align="center" valign="top">
                                    <!-- // Begin Template Header \\ -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader">
                                        <tr>
                                            <td class="headerContent">

                                                <!-- // Begin Module: Standard Header Image \\ -->
                                                <img src="http://www.potter.net.br:8080/Public/Images/logo_rcsv.png" style="max-width:600px;" />
                                                <!-- // End Module: Standard Header Image \\ -->

                                            </td>
                                        </tr>
                                    </table>
                                    <!-- // End Template Header \\ -->
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top">
                                    <!-- // Begin Template Body \\ -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="600" id="templateBody">
                                        <tr>
                                            <td valign="top">

                                                <!-- // Begin Module: Standard Content \\ -->
                                                <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td valign="top" class="bodyContent">
                                                          <div class="std_content00">
                                                                <h4 class="h4">COMUNICADE DE RELATO RCSV '.date("d/m/Y h:i:s").'</h4>
                                                            <br><b>  Dados do relato:</b>
									

<table width="100%" class="tablecustom">
<tr>
<td>Aviação:</td>
<td>'.$attributes['tipo_reporte'].'</td>
</tr>
<tr>
<td>Data do Relato:</td>
<td>'.$data.'</td>
</tr>
<tr>
<td>Hora: </td>
<td>'.$attributes['hora_local'].'</td>
</tr>
<tr>
<tr>
<td>Histórico:</td>
<td>'.$attributes['rcsv_historico'].'</td>
</tr>
</table>



                                                              </strong>.</p>
                                                            </div>
                                                        </td>

                                                    </tr>
                                                    
                                                </table>
                                                <!-- // End Module: Standard Content \\ -->

                                            </td>
                                        </tr>
                                    </table>
                                    <!-- // End Template Body \\ -->
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top">
                                    <!-- // Begin Template Footer \\ -->
                                    <table border="0" cellpadding="10" cellspacing="0" width="600" id="templateFooter">
                                        <tr>
                                            <td valign="top" class="footerContent">

                                                <!-- // Begin Module: Transactional Footer \\ -->
                                                <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                                    <tr><div class="std_utility">
                                                        <td align="center" valign="top">Subdivisão de Tecnologia da Informação - SDTI. CENIPA.</td>
                                                        </div>
                                                    </tr>
                                                    <tr>
                                                        <td valign="middle" id="utility">
                                                            <div class="std_utility">
                                                               <p></p>
                                                                <p>Atenção: está é uma mensagem automática, favor não responder!</p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- // End Module: Transactional Footer \\ -->

                                            </td>
                                        </tr>
                                    </table>
                                    <!-- // End Template Footer \\ -->
                                </td>
                            </tr>
                        </table>
                        <br />
                    </td>
                </tr>
            </table>
        </center>
    </body>
</html>



';

/*
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPDebug = 0;
        $mail->CharSet = "UTF-8";
        // $mail->SMTPAuth = false;
        //$mail->SMTPSecure = '';
        $mail->Host = 'localhost';
        $mail->Port = 25;
        $mail->Username = '';
        $mail->Password = '';
        $mail->SetFrom('nao-responda@potter.net.br');
        $mail->Subject = 'COMUNICADO DE RELATO RCSV';

        $mail->isHTML(true);
        $mail->Body = $html;


        $mail->AddAddress("".$email."");


        if(!$mail->Send()){
            $error = 'Mail error'.$mail->ErrorInfo;
            return array("statusemail" => $error
            ) ;
        }
        else{
            $error = 'Mensagem Enviada';
            return array("statusemail" => $error
            ) ;
        }
		*/

    }





                         

}