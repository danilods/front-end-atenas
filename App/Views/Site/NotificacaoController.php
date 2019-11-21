<?php

/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

namespace App\Controllers\Site;

use \App\Controllers\BaseController;
use \App\Models\Site\aerodromoModel;
use \App\Models\Site\aeronaveModel as aeronave;
use \Acme\Classes\Sanitize;
use \App\Models\Site\ocorrenciaGeralModel as ocorrencia;
use \App\Models\Site\ocorrenciaAeronaveModel as ocorrenciaAeronave;
use \App\Models\Site\ocorrenciaLesaolModel as lesao;
use \App\Models\Site\ocorrenciaNotificadorModel as notificador;
use \App\Models\Site\ocorrenciaTripulanteModel as tripulante;
use \App\Models\Site\aeronaveCategoriaModel as operador;
use \App\Models\Site\faseVooModel as fase;

use \Acme\Classes\DB as potter;





class NotificacaoController extends BaseController {

    public function index() {
		
		
        $dados = [
		
        'titulo' => 'Notificação de Ocorrência',
		'fase' => $fase,
        'registro' => $registro,
        'logo' => 'CENIPA',
		

        ];
        $template = $this->twig->loadTemplate('Site/notificacao.html');
        $template->display($dados);
    }

    public function buscarAeronave() {

        $term = (isset($_GET['term'])) ? htmlspecialchars($_GET['term']) : null;

//$sqlSelect = "SELECT * FROM aeronave_geral WHERE matricula LIKE '%$term%' ORDER BY matricula LIMIT 10";
        $rows = aeronaveModel::buscaAeronave($term);


        foreach ($rows as $reg) {

            $data = [
            "id" => $reg['id'],
            "name" => utf8_encode(addslashes($reg['matricula'])),
            "ano" => utf8_encode(addslashes($reg['ano_fabricacao'])),
            "equipamento" => utf8_encode(addslashes($reg['equipamento'])),
            "fabricante" => utf8_encode(addslashes($reg['fabricante_nome'])),
            "modelo" => utf8_encode(addslashes($reg['modelo'])),
            "tipo_motor" => utf8_encode(addslashes($reg['tipo_motor'])),
            "peso" => utf8_encode(addslashes($reg['peso_max_decolagem']))
            ];
        }

        echo json_encode($data);
    }


    public function buscarAerodromo() {

        $term = (isset($_GET['term'])) ? htmlspecialchars($_GET['term']) : null;

//$sqlSelect = "SELECT * FROM aeronave_geral WHERE matricula LIKE '%$term%' ORDER BY matricula LIMIT 10";
        $rows = aerodromoModel::buscaAerodromo($term);


        foreach ($rows as $reg) {

            $data = [
            "id" => $reg['id'],
            "icao" => utf8_encode(addslashes($reg['icao'])),
            "nome" => utf8_encode(addslashes($reg['nome']))

            ];
        }

        echo json_encode($data);
    }

    public function cadastrar(){



        if($_SERVER['REQUEST_METHOD'] == 'POST'):



            $id_cidade_ocorrencia = $_POST['id_cidade_ocorrencia'];
        $id_aerodromo_ocorrencia = $_POST['id_aerodromo_ocorrencia'];
        $observacoes_local_ocorrencia = $_POST['observacoes_local_ocorrencia'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $data_ocorrencia = $_POST['data_cadastro'];
       


        $hora_ocorrencia = $_POST['hora_ocorrencia'];
        $hora_ocorrencia_z = $_POST['hora_ocorrencia_utc'];
        $situacao_ocorrencia = "***";
        $ano = "***";
        $numero_processo_num = "***";
        $classificacao_ocorrencia = "***";
        $emissor = "***";
        $danos_terceiros = $_POST['danos_terceiros'];
        $historico_ocorrencia = $_POST['historico_ocorrencia'];
        $tipo_ocorrencia = 20;
        $nome_notificador = Sanitize::String($_POST['nome_notificador']);
        $email = Sanitize::email($_POST['email_notificante']);
        $telefone1 = Sanitize::String($_POST['telefone']);
        $telefone2 = Sanitize::String($_POST['telefone2']);

        $id_decolagem_aerodromo = Sanitize::int($_POST['id_decolagem_aerodromo']);
        $decolagem_obs = $_POST['observacoes_local_decolagem'];


        $id_pouso_aerodromo = $_POST['id_pouso_aerodromo'];

        $pouso_obs = $_POST['observacoes_local_pouso'];


        $id_aerodromo_alternativo = $_POST['aerodromo_pouso_alternativo'];

        $alternativo_obs = $_POST['observacoes_pouso_alternativo'];

        $fase_operacao = $_POST['fase_operacao'];
        $danos_aeronave = $_POST['danos_aeronave'];
        $informacoes_danos_aeronave = $_POST['obsevacoes_danos_aeronave'];

        


        $data_cadastro = $_POST['data_cadastro'];
        $categoria_id = $_POST['categoria_id'];
        $id_operador = $_POST['operador_id'];
        $operador_detalhe = $_POST['operador'];
        $operacao = "***";
        $fase_voo_id = $_POST['fase_operacao'];
        $custo_reparo = "***";
        $obs_danos_aeronave = $_POST['obsevacoes_danos_aeronave'];
        $id_aeronave =  $_POST['id_aeronave'];



        


        $arrayOcorrencia = [
           
           "cidade_id" => $id_cidade_ocorrencia,
           "local" => $observacoes_local_ocorrencia,
            "aerodromo_id" => $id_aerodromo_ocorrencia,
          "latitude" => $latitude,
           "longitude" => $longitude,
           "numero_processo" => "12345240",
           "tipo_id" => $tipo_ocorrencia,
           "dia" => $data_cadastro,
           "horario" => $hora_ocorrencia,
            "horario_utc" => $hora_ocorrencia_z,
           "danos_terceiros" => $danos_terceiros,
           "historico" => $historico_ocorrencia,
            "cadastrado_em" => $data_cadastro
        
            ];


        //  $resultadoOcorrencia =  ocorrencia::cadastrar($arrayOcorrencia);
        $db = new potter();
        $db->insert($id_cidade_ocorrencia,$observacoes_local_ocorrencia,
            $id_aerodromo_ocorrencia,$latitude,$longitude,"12345250",$tipo_ocorrencia,$data_cadastro,
            $hora_ocorrencia,$hora_ocorrencia_z,$danos_terceiros,$historico_ocorrencia,$data_cadastro);
        

        
          


      
        $arrayOcorrenciaNotificador = [

        "data_notificacao" => $data_cadastro,
        "ocorrencia_id" =>   $resultadoOcorrencia,
        "notificado_por" => $nome_notificador,
        "email_notificante" => $email,
        "telefone_notificante" => $telefone1,
        "telefone2_notificante" => $telefone2,
        "fax_notificante" => null,
        "observacoes" => null

        ];


       // try {

      //    $result_notificador = notificador::cadastrar($arrayOcorrenciaNotificador);

      //  } catch (Exception $e) {

     //   }


        $arrayOcorrenciaAeronave = [

        "ocorrencia_id" => $resultadoOcorrencia,
        "aeronave_id" => $id_aeronave,
        "origem_voo_id" => $id_decolagem_aerodromo,
        "origem_obs" => $decolagem_obs,
        "destino_voo_id" => $id_pouso_aerodromo,
        "destino_obs" => $pouso_obs,
        "alternativa_voo_id" => null,
        "alternativa_obs" => $alternativo_obs, 
        "categoria_id" => $categoria_id,
        "operador_id" => $id_operador,
        "operador_detalhe" => $operador_detalhe,
        "operacao" => $operacao,
        "fase_voo_id" => $fase_voo_id,
        "danos" => $danos_aeronave,
        "custo_reparo" => $custo_reparo,
        "observacoes" => $obs_danos_aeronave

        ];


      //  try {

      //      ocorrenciaAeronave::cadastrar($arrayOcorrenciaAeronave);


     //   } catch (Exception $e) {

      //  }



        $tripulante_ileso_qtd = Sanitize::int($_POST['tripulante_ileso']);
        $tripulante_leve_qtd = Sanitize::int($_POST['tripulante_leve']);
        $tripulante_grave_qtd = Sanitize::int($_POST['tripulante_grave']);
        $tripulante_fatal_qtd = Sanitize::int($_POST['tripulante_fatal']);
        $tripulante_desconhecido_qtd = Sanitize::int($_POST['tripulante_desconhecido']);

        $passageiro_ileso_qtd = Sanitize::int($_POST['passageiro_ileso']);
        $passageiro_leve_qtd = Sanitize::int($_POST['passageiro_leve']);
        $passageiro_grave_qtd = Sanitize::int($_POST['passageiro_grave']);
        $passageiro_fatal_qtd = Sanitize::int($_POST['passageiro_fatal']);
        $passageiro_desconhecido_qtd = Sanitize::int($_POST['passageiro_desconhecido']);

        $terceiro_ileso_qtd = Sanitize::int($_POST['terceiro_ileso']);
        $terceiro_leve_qtd = Sanitize::int($_POST['terceiro_leve']);
        $terceiro_grave_qtd = Sanitize::int($_POST['terceiro_grave']);
        $terceiro_fatal_qtd = Sanitize::int($_POST['terceiro_fatal']);
        $terceiro_desconhecido_qtd = Sanitize::int($_POST['terceiro_desconhecido']);





      

            $quantidade_funcao = count($_POST['funcao']);
            $quantidade_id_cod_anac = count($_POST['id_cod_anac']);



            for ($i = 0; $i< $quantidade_funcao; $i++){



          //      $tripulante = new tripulante();
         //       $tripulante->ocorrencia_id = $resultadoOcorrencia;
          //      $tripulante->aeronave_ocorrencia_id = $id_aeronave;
          //      $tripulante->tripulante_id = $_POST['id_cod_anac'][$i];
          //      $tripulante->funcao = $_POST['funcao'][$i];
          //      $tripulante->save();

           }



        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"TRIPULANTE","ILESO", $tripulante_ileso_qtd);
        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"TRIPULANTE","LEVE", $tripulante_leve_qtd);
        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"TRIPULANTE","GRAVE", $tripulante_grave_qtd);
        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"TRIPULANTE","FATAL", $tripulante_fatal_qtd);
        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"TRIPULANTE","DESCONHECIDO", $tripulante_desconhecido_qtd);


        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"PASSAGEIRO","ILESO", $passageiro_ileso_qtd);
        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"PASSAGEIRO","LEVE", $passageiro_leve_qtd);
        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"PASSAGEIRO","GRAVE", $passageiro_grave_qtd);
        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"PASSAGEIRO","FATAL", $passageiro_fatal_qtd);
        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"PASSAGEIRO","DESCONHECIDO", $passageiro_desconhecido_qtd);


        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"TERCEIRO","ILESO", $terceiro_ileso_qtd);
        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"TERCEIRO","LEVE", $terceiro_leve_qtd);
        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"TERCEIRO","GRAVE", $terceiro_grave_qtd);
        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"TERCEIRO","FATAL", $terceiro_fatal_qtd);
        inserirLesoes($id_ocorrencia_geral, $id_aeronave,"TERCEIRO","DESCONHECIDO", $terceiro_desconhecido_qtd);


           try {

        //    $lesao = new ocorrenciaLesaoModel();
        //    $lesao->aeronave_ocorrencia_id = $id_aeronave;
       //     $lesao->ocorrencia_id = $id_ocorrencia;
        //    $lesao->tipo_lesao = $tipo_lesao;
        //    $lesao->pessoa = $pessoa;
        //    $lesao->quantidade = $quantidade_lesao;
        //    $lesao->save();

        } catch (Exception $e) {

        }












       












        endif;





        $this->index();




    }


    public function cadastrarOcorrenciaGeral(Array $atributos){








    }


    public function cadastrarNotificador(Array $atributos){


        try {

            ocorrenciaNotificadorModel::cadastrar($atributos);

        } catch (Exception $e) {

        }




    }


    public function cadastrarAeronaveOcorrencia(Array $aeronaveocorrencia){

        try {

            ocorrenciaAeronaveModel::cadastrar($aeronaveocorrencia);


        } catch (Exception $e) {

        }



    }

}


    

