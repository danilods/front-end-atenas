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
use \App\Models\Site\ocorrenciaGeralModel as ocorrencia;
use \App\Models\Site\Aeronave as aeronave;
use \App\Models\Site\Lesao as lesao;
use App\Models\UfModel as uf;
use \App\Models\Site\ocorrenciaTripulanteModel as tripulante;
use \Acme\Classes\DB as potter;
use Acme\Classes\Redirect;
use App\Models\Site\faseVooModel as fase;
use App\Models\Site\aeronaveCategoriaModel as categoria;

use PHPMailer;
//use \Acme\Classes\Smtp\SMTP;





class RcsvPilotoController extends BaseController {





    public function index() {
        $captcha = new RainCaptcha();
        $img ='';
        $img .= $captcha->getImage();

        $fase_voo = fase::listar();
        $categoria = categoria::listar();
		$estado = uf::custom('SELECT * FROM `geografia_uf` where pais_id=1  order by nome_codigo asc');
        $dados = [

            'titulo' => 'Notificação de Ocorrência',

            'logo' => 'CENIPA',

            'fase_voo' => $fase_voo,
            'categoria' => $categoria,
			'estado' => $estado,
            'imagem' => $img


        ];
        $template = $this->twig->loadTemplate('Site/rcsv_piloto.html');
        $template->display($dados);



    }



    public function cadastrar(){



        if(isset($_POST['submit'])){

            $captcha =  new RainCaptcha();
            //if(!$captcha->checkAnswer($_POST['captcha'])){
            //	die(utf8_decode('Erro: Identificamos que o Código de Segurança não Confere! Tente novamente.'));
            //}

            if(isset($_POST['captcha'])) {

                $isCaptchaCorrect = $captcha->checkAnswer($_REQUEST['captcha']);

                if($isCaptchaCorrect) {

                    //ocorrencia

                    $tipo_ficha = $_POST['tipoFicha'];

                    $data_ocorrencia = $_POST['data_ocorrencia'];

                    $hora_ocorrencia = $_POST['hora_ocorrencia'];
					
					$estado_id = $_POST['estado'];
					
					$estado = $_POST['estado_nome'];
					
					$cidade = $_POST['cidade_nome'];

 


                    $aerodromo_ocorrencia = $_POST['aerodromo_icao_ocorrencia'];

                    $observacoes_local_ocorrencia = $_POST['observacoes_local_ocorrencia'];

                    $matricula_simplificado = $_POST['matricula_simples'];

                    $fabricante_simplificado = $_POST['fabricante_oculto'];

                    $modelo_simplificado = $_POST['modelo_oculto'];

                    $danos_aeronave_simplificado = $_POST['danos_aeronave_simplificado'];

                    $lesao_pessoas = $_POST['lesao_pessoas'];

                    $historico_ocorrencia = $_POST['historico_ocorrencia'];

                    $nome_notificador = $_POST['nome_notificador'];

                    $email = $_POST['email_notificante'];

                    $telefone1 = $_POST['telefone'];

                    $telefone2 = $_POST['telefone2'];

                    $seripa = $this->identificarSeripa($estado_id);

                    $email_seripa = $this->retornarEmailSeripa($estado_id);


                    $arraySeripaInfo = $this->InformacaoSeripaUltimo($estado_id);

                    $emailSeripa = $arraySeripaInfo['email'];

                    $email2Seripa = $arraySeripaInfo['email2'];










                    if ( strcasecmp( $tipo_ficha, 'simplificado' ) == 0 ){

                        $attributes = array(

                            'tipo_ficha' => $tipo_ficha,


                            'data_ocorrencia' => implode("-",array_reverse(explode("/",$data_ocorrencia))),
                            'hora' => $hora_ocorrencia,
                            'cidade' => $estado.' - '.$cidade,
                            'estado' => '',
                            'aerodromo' => $aerodromo_ocorrencia,
                            'obs_local_ocorrencia' => strtoupper($observacoes_local_ocorrencia),
                            'matricula' => $matricula_simplificado,
                            'modelo' => $modelo_simplificado,
                            'fabricante' => $fabricante_simplificado,
                            'danos_aeronave' => $danos_aeronave_simplificado,
                            'lesao_pessoas' => $lesao_pessoas,
                            'historico' => strtoupper($historico_ocorrencia),
                            'nome_emissor' => strtoupper($nome_notificador),
                            'email' => $email,
                            'telefone' => $telefone1,
                            'celular' => $telefone2,
                            'seripa' => $seripa,

                        );

                        try{


                            $resultado = ocorrencia::cadastrar($attributes);

                            if($resultado!=false){
                                $status = array("status" => true);
                            }else{
                                $status = array("status" => false);
                            }
                            //echo "ocorrencia simples cadastrada: ".$data_ocorrencia;

                            //   Redirect::to('notificar');

                            // $saida = $this->enviarEmailUsuario($attributes);

                            //   if($saida){
                            //     echo "email enviado";
                            // }
                            //else{
                            //  echo "email nao enviado";
                            // }

                            $this->enviarEmailSeripa($attributes, $emailSeripa, $email2Seripa);
                            //$this->enviarEmailUsuario($attributes);
                            $template = $this->twig->loadTemplate('Site/feedback_cadastro.html');
                            $unir =  array_merge($attributes,$arraySeripaInfo,$status);
                            $template->display($unir);









                        }
                        catch(ActiveRecordException $e){
                            echo "ocorrencia simples não cadastrada".$e;

                        }



                    }
                    else{




                        try{
                            $attributes = array(

                                'tipo_ficha' => $tipo_ficha,
                                'data_ocorrencia' => implode("-",array_reverse(explode("/",$data_ocorrencia))),
                                'hora' => $hora_ocorrencia,
                                'cidade' => $estado.' - '.$cidade,
                                'estado' => '',
                                'aerodromo' => $aerodromo_ocorrencia,
                                'obs_local_ocorrencia' => strtoupper($observacoes_local_ocorrencia),
                                'matricula' => $matricula_simplificado,
                                'modelo' => $modelo_simplificado,
                                'fabricante' => $fabricante_simplificado,
                                'danos_aeronave' => $danos_aeronave_simplificado,
                                'lesao_pessoas' => $lesao_pessoas,
                                'historico' => strtoupper($historico_ocorrencia),
                                'nome_emissor' => strtoupper($nome_notificador),
                                'email' => $email,
                                'telefone' => $telefone1,
                                'celular' => $telefone2,
                                'seripa' => $seripa,

                            );


                            $resut = ocorrencia::cadastrar($attributes);
                            if($resut!=false){
                                $status = array("status" => true);
                            }else{
                                $status = array("status" => false);
                            }
                            $template = $this->twig->loadTemplate('Site/feedback_cadastro.html');
                            $unir =  array_merge($attributes,$arraySeripaInfo,$status);
                            $template->display($unir);




                            $ocorrencia_id = $resut->id;



                            $aeronaveCount = 0;

                            $quantidade_aeronave = $_POST['count_aeronave'];



                            $html_completo = '';
                            $htmlTripulante = 'Tripulantes: ';
                            $htmlLesao = 'Lesões';
                            $htmlAeronave = '';
                            $cont=0;
                            $contles =0;
                            $contrip =0;

                            for($i =0; $i<$quantidade_aeronave; $i++) {

                                $aeronaveCount++;

                                $objAeronave = new aeronave();
                                $objAeronave->matricula = $_POST['input_matricula'][$i];
                                $objAeronave->fabricante = $fabricante_simplificado;
                                $objAeronave->modelo = $modelo_simplificado;
                                $objAeronave->danos = $_POST['danos_aeronave'][$i];
                                $objAeronave->informacoes_danos  = strtoupper($_POST['obsevacoes_danos_aeronave'][$i]);
                                $objAeronave->aerodromo_decolagem = $_POST['input_aerodromo'][$i];
                                $objAeronave->aerodromo_pouso =  $_POST['aerodromo_pouso'][$i];
                                $objAeronave->aerodromo_alternativo = $_POST['aerodromo_pouso_alternativo'][$i];
                                $objAeronave->obs_decolagem  = strtoupper($_POST['observacoes_local_decolagem'][$i]);
                                $objAeronave->obs_pouso  = strtoupper($_POST['observacoes_local_pouso'][$i]);
                                $objAeronave->obs_alternativo  = strtoupper($_POST['observacoes_pouso_alternativo'][$i]);
                                $objAeronave->fase_voo  = $_POST['fase_operacao'][$i];
                                $objAeronave->operador = $_POST['operador'][$i];
                                $objAeronave->categoria_aeronave = $_POST['categoria_operacao'][$i];
                                $objAeronave->ocorrencia_id = $ocorrencia_id;

                                $objAeronave->save();

                                $objAeronave::find(array('conditions'=> array('ocorrencia_id', $ocorrencia_id)));


                                $arrAer = $objAeronave->to_array();

                                $cont++;
                                $htmlAeronave .= $this->montarHtmlAeronave($arrAer,$cont );
                                //$html_completo .= $htmlAeronave;

                                $id_aeronave = $objAeronave->id;




                                $quantidade_tripulante =  count($_POST['funcao']);


                                for($x = 0; $x<$quantidade_tripulante; $x++) {

                                    $objTripulante = new tripulante();
                                    $objTripulante->funcao_tripulante = $_POST['funcao'][$x];
                                    $objTripulante->cod_anac =  $_POST['cod_anac'][$x];
                                    $objTripulante->nome_tripulante  = $_POST['nome_completo'][$x];
                                    $objTripulante->temp_aeronave_id = $id_aeronave;
                                    $objTripulante->save();



                                    $objTripulante::find(array('conditions'=> array('temp_aeronave_id', $id_aeronave)));


                                    $arrTrip = $objTripulante->to_array();

                                    $contrip++;

                                    $htmlTripulante .= $this->montarHtmlTripulante($arrTrip,$contrip);
                                    //  $html_completo .= $htmlTripulante;

                                }


                                $quantidade_lesao = count($_POST['grau_lesao']);

                                for($y =0; $y<$quantidade_lesao; $y++) {

                                    $objLesao = new lesao();
                                    $objLesao->grau_lesao =  $_POST['grau_lesao'][$y];
                                    $objLesao->funcao_bordo = $_POST['funcao_bordo'][$y];
                                    $objLesao->quantidade =   $_POST['quantidade'][$y];
                                    $objLesao->temp_aeronave_id = $id_aeronave;
                                    $objLesao->save();


                                    $objLesao::find(array('conditions'=> array('temp_aeronave_id', $id_aeronave)));

                                    $arrLesao = $objLesao->to_array();


                                    $contles++;

                                    $htmlLesao .= $this->montarHtmlLesao($arrLesao, $contles);
                                    //  $html_completo .= $htmlLesao;



                                }







                            }

                            $html_completo .= $htmlAeronave.''.$htmlTripulante.''.$htmlLesao;

                            $this->enviarNotificacaoCompleta($attributes, $html_completo, $emailSeripa, $email2Seripa);
                            $this->enviarEmailUsuario($attributes);




                        }


                        catch(ActiveRecordException $ex){



                        }
                    }

                }
                else {
                    echo "<script>alert('".utf8_decode("Erro: O código de Segurança não Confere! Tente novamente.")."')</script>";
					echo "<script>window.history.go(-1)</script>";
					exit();
                }
            }

            else {
                echo 'Hacking attempt!';
            }



        }// fim if post verification


    }



    public function enviarEmailSeripa(Array $ocorrencia, $email_seripa,$email2_seripa){

        $data = date("d/m/Y", strtotime($ocorrencia['data_ocorrencia']));
        $hora = $ocorrencia['hora'];
        $cidade = $ocorrencia['estado'].' - '.$ocorrencia['cidade'];
        $aerodromo = $ocorrencia['aerodromo'];
        $obs_local = $ocorrencia['obs_local_ocorrencia'];
        $matricula = $ocorrencia['matricula'];
        $modelo = $ocorrencia['modelo'];
        $fabricante = $ocorrencia['fabricante'];
        $danos = $ocorrencia['danos_aeronave'];
        $lesao =$ocorrencia['lesao_pessoas'];
        $historico = $ocorrencia['historico'];
        $nome_emissor = $ocorrencia['nome_emissor'];
        $email = $ocorrencia['email'];
        $telefone = $ocorrencia['telefone'];
        $celular = $ocorrencia['celular'];


        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <!-- Facebook sharing information tags -->
        <meta property="og:title" content="confirma�?„o de notifica�?„o" />

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
                                                <img src="http://www.potter.net.br:8080/Public/Images/banner.png" style="max-width:600px;" />
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
                                                                <h4 class="h4">COMUNICAÇÃO DE OCORRÊNCIA AERONAÚTICA - SIMPLIFICADA '.date("d/m/Y h:i:s").'</h4>
                                                            <br><b>  Dados da Ocorrência:</b>
													

<table width="100%" class="tablecustom">
<tr>
<td>Data da ocorrência:</td>
<td>'.$data.'</td>
</tr>
<tr>
<td>Hora:</td>
<td>'.$hora.'</td>
</tr>
<tr>
<td>Cidade:</td>
<td>'.$cidade.'</td>
</tr>
<tr>
<td>Aerodromo: </td>
<td>'.$aerodromo.'</td>
</tr>
<tr>
<td>Observações sobre o local da ocorrência:</td>
<td>'.$obs_local.'</td>
</tr>
<tr>
<td>MatrÌcula da Aeronave: </td>
<td>'.$matricula.'</td>
</tr>

<tr>
<td>Fabricante: </td>
<td>'.$fabricante.'</td>
</tr>
<tr>
<td>Modelo Aeronave: </td>
<td>'.$modelo.'</td>
</tr>

<tr>
<td>Danos Aeronave: </td>
<td>'.$danos.'</td>
</tr>

<tr>
<td>Lesão à pessoas: </td>
<td>'.$lesao.'</td>
</tr>
<tr>
<td>Histórico: </td>
<td>'.$historico.'</td>
</tr>
<tr>
<td>Nome emissor: </td>
<td>'.$nome_emissor.'</td>
</tr>
<tr>
<td>Email: </td>
<td>'.$email.'</td>
</tr>
<tr>
<td>Telefone: </td>
<td>'.$telefone.'</td>
</tr>
<tr>
<td>Celular: </td>
<td>'.$celular.'</td>
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

        //	$smtp = new SMTP();

        //	if($smtp->sendMail("danilod2.sousa@gmail.com", "nao-responda@potter.net.br", "NOTIFICAÇÃO DE OCORRÊNCIA","".$html."","localhost","","" )==true){
        //		echo 'sucesso';
        //	}else{
        //		echo 'erro';
        //	}


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
        $mail->Subject = 'NOTIFICAÇÃO DE OCORRÊNCIA';

        $mail->isHTML(true);
        $mail->Body = $html;


        $mail->AddAddress($email_seripa);
                $mail->AddAddress($email2_seripa);

        $mail->AddBCC("imprensa.cenipa@gmail.com");
	$mail->AddBCC("soleilcarla@yahoo.com.br");
	
        $mail->AddBCC("danilod2.sousa@gmail.com");
		$mail->AddBCC("".$email.""); // email para usuario
        $mail->AddBCC("jhonfelix@hotmail.com");
        $mail->AddBCC("danilods@cenipa.aer.mil.br");
        $mail->AddBCC("felix@cenipa.aer.mil.br");
		$mail->AddBCC("notificacao@cenipa.aer.mil.br");
                $mail->AddBCC("cenipanotifica@gmail.com");



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

    }


    public function enviarNotificacaoCompleta(Array $ocorrencia, $htmlAeronave, $email_seripa, $email2_seripa){



        $data = date("d/m/Y", strtotime($ocorrencia['data_ocorrencia']));
        $hora = $ocorrencia['hora'];
        $cidade = $ocorrencia['estado'].' - '.$ocorrencia['cidade'];
        $aerodromo = $ocorrencia['aerodromo'];
        $obs_local = $ocorrencia['obs_local_ocorrencia'];
        $matricula = $ocorrencia['matricula'];
        $modelo = $ocorrencia['modelo'];
        $fabricante = $ocorrencia['fabricante'];
        $danos = $ocorrencia['danos_aeronave'];
        $lesao =$ocorrencia['lesao_pessoas'];
        $historico = $ocorrencia['historico'];
        $nome_emissor = $ocorrencia['nome_emissor'];
        $email = $ocorrencia['email'];
        $telefone = $ocorrencia['telefone'];
        $celular = $ocorrencia['celular'];





        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <!-- Facebook sharing information tags -->
        <meta property="og:title" content="confirma�?„o de notifica�?„o" />

        <title>CENIPA - NOTIFICA«vO DE OCORR NCIA</title>
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
				border-bottom:1px #ccc solid;
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
                                                <img src="http://www.potter.net.br:8080/Public/Images/banner.png" style="max-width:600px;" />
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
                                                                <h4 class="h4">COMUNICAÇÃO DE OCORRÊNCIA AERON�?UTICA - FICHA COMPLETA '.date("d/m/Y h:i:s").'</h4>
                                                            <br> <b>Dados da Ocorrência:</b>


<table width="100%" class="tablecustom">

<table width="100%" class="tablecustom">

<td>Data da ocorrência:</td>

<td>'.$data.'</td>
</tr>
<tr>
<td>Hora:</td>
<td>'.$hora.'</td>
</tr>
<tr>
<td>Cidade:</td>
<td>'.$cidade.'</td>
</tr>
<tr>
<td>Aerodromo: </td>
<td>'.$aerodromo.'</td>
</tr>
<tr>
<td>Observações sobre o local da ocorrência:</td>
<td>'.$obs_local.'</td>
</tr>

<tr>
<td>Histórico: </td>
<td>'.$historico.'</td>
</tr>
<tr>
<td>Nome emissor: </td>
<td>'.$nome_emissor.'</td>
</tr>
<tr>
<td>Email: </td>
<td>'.$email.'</td>
</tr>
<tr>
<td>Telefone: </td>
<td>'.$telefone.'</td>
</tr>

<td>Celular: </td>
<td>'.$celular.'</td>
</tr>


</table>

'.$htmlAeronave.'




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
                                                                <p>ATENÇÃO: Esta é uma mensagem automática, favor não responder!</p>
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

        $conteudo_email = $html;

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
        $mail->Subject = 'NOTIFICAÇÃO DE OCORRÊNCIA';

        $mail->isHTML(true);
        $mail->Body = $conteudo_email;


        $mail->AddAddress($email_seripa);
        $mail->AddAddress($email2_seripa);
        $mail->AddBCC("imprensa.cenipa@gmail.com");
	    $mail->AddBCC("soleilcarla@yahoo.com.br");
		$mail->AddBCC("".$email.""); // email para usuario

        $mail->AddBCC("danilods@cenipa.aer.mil.br");
        $mail->AddBCC("felix@cenipa.aer.mil.br");
        $mail->AddBCC("danilod2.sousa@gmail.com");
        $mail->AddBCC("jhonfelix@hotmail.com");
		$mail->AddBCC("notificacao@cenipa.aer.mil.br");
                $mail->AddBCC("cenipanotifica@gmail.com");


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

    }



    public function montarHtmlAeronave(Array $aeronave, $cont){

        $matricula = $aeronave['matricula'];
        $fabricante = $aeronave['fabricante'];
        $modelo = $aeronave['modelo'];
        $danos = $aeronave['danos'];
        $informacoes_danos = $aeronave['informacoes_danos'];
        $aerodromo_decolagem = $aeronave['aerodromo_decolagem'];
        $aerodromo_pouso =$aeronave['aerodromo_pouso'];
        $aerodromo_alternativo =$aeronave['aerodromo_alternativo'];
        $obs_decolagem = $aeronave['obs_decolagem'];
        $obs_pouso = $aeronave['obs_pouso'];
        $obs_alternativo = $aeronave['obs_alternativo'];
        $fave_voo = utf8_decode($aeronave['fase_voo']);
        $operador = utf8_decode($aeronave['operador']);
        $categoria = utf8_decode($aeronave['categoria_aeronave']);






        $msgAer = '<br>
<b>Dados da Aeronave:</b><br>

<table width="100%" class="tablecustom">
<tr>
<td>Matrícula:</td>
<td>'.$matricula.'</td>
</tr>
<tr>
<td>Fabricante:</td>
<td>'.$fabricante.'</td>
</tr>
<tr>
<td>Modelo:</td>
<td>'.$modelo.'</td>
</tr>
<tr>
<td>Danos:</td>
<td>'.$danos.'</td>
</tr>
<tr>
<td>Informações danos:: </td>
<td>'.$informacoes_danos.'</td>
</tr>
<tr>
<td>Aerodromo decolagem:</td>
<td>'.$aerodromo_decolagem.'</td>
</tr>
<tr>
<td>Observações aerodromo decolagem: </td>
<td>'.$obs_decolagem.'</td>
</tr>

<tr>
<td>Aerodromo pouso: </td>
<td>'.$aerodromo_pouso.'</td>
</tr>
<tr>
<td>Observações aerodromo pouso: </td>
<td>'.$obs_pouso.'</td>
</tr>

<tr>
<td>Aerodromo alternativo: </td>
<td>'.$aerodromo_alternativo.'</td>
</tr>

<tr>
<td>Observações aerodromo alternativo: </td>
<td>'.$obs_alternativo.'</td>
</tr>
<tr>
<td>Fase de voo: </td>
<td>'.utf8_encode($fave_voo).'</td>
</tr>
<tr>
<td>Operador: </td>
<td>'.$operador.'</td>
</tr>
<tr>
<td>Categoria aeronave: </td>
<td>'.utf8_encode($categoria).'</td>
</tr>
</table>';


        return $msgAer;

    }

    public function montarHtmlTripulante(Array $tripulante, $cont){


        $cont = 0;
        $funcao_tripulante = $tripulante['funcao_tripulante'];
        $cod_anac = $tripulante['cod_anac'];
        $nome_tripulante = $tripulante['nome_tripulante'];

        $cont++;

        $msgTrip = '<br>
  <br>

<table width="100%" class="tablecustom">
<tr>
<td>Função do tripulante:</td>
<td>'.$funcao_tripulante.'</td>
</tr>
<tr>
<td>Nome:</td>
<td>'.$nome_tripulante.'</td>
</tr>
<tr>
<td>Código anac:</td>
<td>'.$cod_anac.'</td>
</tr>
</table>
<br>';

        return $msgTrip;

    }

    public function montarHtmlLesao(Array $lesao, $cont){

        $cont = 0;

        $grau_lesao = $lesao['grau_lesao'];
        $funcao_bordo = $lesao['funcao_bordo'];
        $quantidade = $lesao['quantidade'];

        $cont++;

        $msgLesao = '<br>  <br>

<table width="100%" class="tablecustom">
<tr>
<td>Grau de lesão:</td>
<td>'.$grau_lesao.'</td>
</tr>
<tr>
<td>Função:</td>
<td>'.$funcao_bordo.'</td>
</tr>
<tr>
<td>Quantidade:</td>
<td>'.$quantidade.'</td>
</tr>
</table>';


        return $msgLesao;

    }


    public function enviarEmailUsuario(Array $ocorrencia){

        $nome_emissor = $ocorrencia['nome_emissor'];
        $email = $ocorrencia['email'];



        $mensagemHTML= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <!-- Facebook sharing information tags -->
        <meta property="og:title" content="confirma�?„o de notifica�?„o" />

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
                                                <img src="http://www.potter.net.br:8080/Public/Images/banner.png" style="max-width:600px;" />
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
                                                                <h4 class="h4">Comunicação de Ocorrência Aeronáutica recebida com sucesso - '.date("d/m/Y h:i:s").'.</h4>
                                                                <p>Prezado(a) '.$nome_emissor.',<br> A comunicação de ocorrência foi recebida com sucesso. </p>

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



        // Verifica se existe email e se não está vazia
        if(!empty($email)){

            $conteudo_email = $mensagemHTML;

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
            $mail->Subject = 'NOTIFICAÇÃO DE OCORRÊNCIA';

            $mail->isHTML(true);
            $mail->Body = $conteudo_email;


            $mail->AddAddress($email);

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
        }

    }

    public function retornarEmailSeripa($estado){

        $arraySeripa7 =  array(1,2,3,4);
        $arraySeripa1 =  array(5,6,8);
        $arraySeripa6 =  array(7,25,26,27);
        $arraySeripa2 =  array(9,10,11,12,13,14,15,16);
        $arraySeripa3 =  array(17,18,19);
        $arraySeripa5 =  array(21,22,23);
        $arraySeripa4 =  array(20,24);


        if(in_array($estado,$arraySeripa1)){

            return "notifica@seripa1.aer.mil.br";
        }
        elseif (in_array($estado,$arraySeripa2)){
            return "notifica@seripa2.aer.mil.br";

        }

        elseif (in_array($estado,$arraySeripa3)) {
            return "notifica.seripa3@gmail.com";

        }
        elseif (in_array($estado,$arraySeripa4)) {
            return "investiga@seripa4.aer.mil.br";

        }
        elseif (in_array($estado,$arraySeripa5)) {
            return "notifica@seripa5.aer.mil.br";

        }
        elseif (in_array($estado,$arraySeripa6)) {
            return "notifica@seripa6.aer.mil.br";

        }
        elseif (in_array($estado,$arraySeripa7)) {
            return "notifica@seripa7.aer.mil.br";

        }
        elseif($estado==0 || $estado==28){
            return "notifica@cenipa.aer.mil.br";
        }



    }

    public function identificarSeripa($estado){

        $arraySeripa7 =  array(1,2,3,4);
        $arraySeripa1 =  array(5,6,8);
        $arraySeripa6 =  array(7,25,26,27);
        $arraySeripa2 =  array(9,10,11,12,13,14,15,16);
        $arraySeripa3 =  array(17,18,19);
        $arraySeripa5 =  array(21,22,23);
        $arraySeripa4 =  array(20,24);


        if(in_array($estado,$arraySeripa1)){

            return "Primeiro Serviço Regional de Investigação e Prevenção de Acidentes Aeronáuticos - SERIPA1";
        }
        elseif (in_array($estado,$arraySeripa2)){
            return "Segundo Serviço Regional de Investigação e Prevenção de Acidentes Aeronáuticos - SERIPA2";

        }

        elseif (in_array($estado,$arraySeripa3)) {
            return "Terceiro Serviço Regional de Investigação e Prevenção de Acidentes Aeronáuticos - SERIPA3";
        }
        elseif (in_array($estado,$arraySeripa4)) {
            return "Quarto Serviço Regional de Investigação e Prevenção de Acidentes Aeronáuticos - SERIPA4";

        }
        elseif (in_array($estado,$arraySeripa5)) {
            return "Quinto Serviço Regional de Investigação e Prevenção de Acidentes Aeronáuticos - SERIPA5";

        }
        elseif (in_array($estado,$arraySeripa6)) {
            return "Sexto Serviço Regional de Investigação e Prevenção de Acidentes Aeronáuticos - SERIPA6";

        }
        elseif (in_array($estado,$arraySeripa7)) {
            return "Setimo Serviço Regional de Investigação e Prevenção de Acidentes Aeronáuticos - SERIPA7";
        }
        elseif($estado==0 || $estado==28){
            return "CENIPA";
        }



    }

    function InformacaoSeripaUltimo($estado){

        $arraySeripa7 =  array(1,2,3,4);
        $arraySeripa1 =  array(5,6,8);
        $arraySeripa6 =  array(7,25,26,27);
        $arraySeripa2 =  array(9,10,11,12,13,14,15,16);
        $arraySeripa3 =  array(17,18,19);
        $arraySeripa5 =  array(21,22,23);
        $arraySeripa4 =  array(20,24);


        if(in_array($estado,$arraySeripa1)){

            return array("nome" => "Primeiro Serviço Regional de Investigação e Prevenção de Acidentes Aeronáuticos",
                "sigla" => "SERIPA1",
                "email" => "notifica.seripa1@gmail.com",
                "email2" => "notifica@seripa1.aer.mil.br",

                
                "telefone" => "(91) 99162-0824") ;
        }
        elseif (in_array($estado,$arraySeripa2)){
            return array("nome" => "Segundo Serviço Regional de Investigação e Prevenção de Acidentes Aeronáuticos",
                "sigla" => "SERIPA2",
                "email" => "notifica@seripa2.aer.mil.br",
                "email2" => "investiga.seripa2@gmail.com",
                "telefone" => "(81) 9161-2232 ") ;

        }

        elseif (in_array($estado,$arraySeripa3)) {
            return array("nome" => "Terceiro Serviço Regional de Investigação e Prevenção de Acidentes Aeronáuticos",
                "sigla" => "SERIPA3",
                
                "email" => "notifica@seripa3.aer.mil.br",
                "email2" => "notifica.seripa3@gmail.com",
                
                "telefone" => "(21) 99646-8360/ 99603-3004") ;

        }
        elseif (in_array($estado,$arraySeripa4)) {
            return array("nome" => "Quarto Serviço Regional de Investigação e Prevenção de Acidentes Aeronáuticos",
                "sigla" => "SERIPA4",
                
                "email" => "investiga@seripa4.aer.mil.br",
                "email2" => "investiga.seripa4@gmail.com",
                
                "telefone" => "(11) 99459-3047/ 99427-5043 ") ;

        }
        elseif (in_array($estado,$arraySeripa5)) {
            return array("nome" => "Quinto Serviço Regional de Investigação e Prevenção de Acidentes Aeronáuticos",
                "sigla" => "SERIPA5",
                "email" => "notifica@seripa5.aer.mil.br",

                "email2" => "investiga.seripa5@gmail.com",
                "telefone" => "(51) 9268-3043/ 9283-5207") ;

        }
        elseif (in_array($estado,$arraySeripa6)) {
            return array("nome" => "Sexto Serviço Regional de Investigação e Prevenção de Acidentes Aeronáuticos",
                "sigla" => "SERIPA6",
                "email" => "notifica@seripa6.aer.mil.br",
                "email2" => "investiga.seripa6@gmail.com",
                "telefone" => "(61) 9649-5304/ 9649-5458");

        }
        elseif (in_array($estado,$arraySeripa7)) {
            return array("nome" => "Setimo Serviço Regional de Investigação e Prevenção de Acidentes Aeronáuticos",
                "sigla" => "SERIPA7",
                "email" => "notifica@seripa7.aer.mil.br",
                "email2" => "investiga.seripa7@gmail.com",
                "telefone" => "(92) 98423-0177/ 99323-4435") ;

        }
        elseif($estado==0 || $estado==28){
            return array("nome" => "Centro de Investigação e Prevenção de Acidentes Aeronáuticos",
                "sigla" => "CENIPA",
                "email" => "notifica@cenipa.aer.mil.br",
                "email2" => "cenipanotifica@gmail.com",
                "telefone" => "(61) 9994-9554") ;
        }



    }


}