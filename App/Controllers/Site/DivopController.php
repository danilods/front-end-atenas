<?php

/**
 * Created by PhpStorm.
 * User: danilodesouza
 * Date: 13/08/15
 * Time: 16:18
 */

namespace App\Controllers\Site;

use App\Controllers\BaseController;
use App\Models\PrevencaoGeralModel as divop;

class DivopController extends BaseController {

    public function index() {

        $query = 'select id, numero_atividade, titulo, detalhe, tipo, dia, anexo_prev from prevencao_geral where tipo="DIVOP" and publicado_site=1 ORDER BY dia DESC';

        $relacao_divop = divop::custom($query);




        $dados = [
            'titulo' => 'DIVULGAÇÃO OPERACIONAL',
            'divops' => $relacao_divop,
        ];
        $template = $this->twig->loadTemplate('Site/divop.html');
        $template->display($dados);
    }

    public function json() {
        
       
        
        $query = 'select id, numero_atividade, titulo, detalhe, tipo, dia, anexo_prev from prevencao_geral where tipo="DIVOP" and publicado_site=1 order by id desc limit 10';

        $path = "http://prevencao.potter.net.br/filedivop/";
        
        $divop = divop::custom($query);


        $data = array();

        foreach ($divop as $r) {



            $data[] = $r->to_array();
        }
        //  print_r($data);


        foreach ($data as $res) {
			
			$data = date('d/m/Y', strtotime($res['dia']));
			
            $resultado[] = array(
                "titulo" => utf8_decode($res['numero_atividade'])." - ".$data,
                "subtitulo" => utf8_encode($res['titulo'])." - ".utf8_encode($res['detalhe']),
                       
                "link" => $path."".utf8_encode($res['anexo_prev'])
            );
        }
        echo json_encode($resultado, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

}
