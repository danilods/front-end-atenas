<?php

namespace App\Controllers\Site;

use App\Controllers\BaseController;
use App\Models\RecomendacaoModel as recomendacao;
use App\Models\OcorrenciaModel as operador;
use Acme\Classes\Parameters as parameter;
use App\Models\TipoModel as tipo;
use App\Models\UfModel as uf;
use Acme\Classes\Redirect as Redirect;

class Acompanhar_operadorController extends BaseController {

    public function index() {

        

        $cod_operador = parameter::getParameter(2);
        
         if (isset($_SESSION[ 'logado' ] ) && $_SESSION['operador']==$cod_operador){
            
      
            $relacao_ocorrencias = operador::where('operador', $cod_operador, 'all');

            if (count($relacao_ocorrencias > 0)) {
            
          
            $taxonomia_tipo = tipo::listar();



            $estado = uf::custom('SELECT * FROM `geografia_uf` where pais_id=1  order by nome_codigo asc');





            $dados = [
                'titulo' => 'ACOMPANHAMENTO DE OCORRÊNCIAS',
                'relatorios' => $relacao_ocorrencias,
                'taxonomia_tipo' => $taxonomia_tipo,
                'estado' => $estado
            ];
            $template = $this->twig->loadTemplate('Site/operador.html');
            $template->display($dados);
        } else {
            $_SESSION['erro'] = 'Dados não localizados';
            Redirect::to('operador');
        }
    }else{
          unset($_SESSION[ 'logado' ]);
          session_regenerate_id();
          Redirect::to('operador');
    }
    }

}
