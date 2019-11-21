<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers\Site;

use \App\Controllers\BaseController;
use Acme\Classes\Redirect as Redirect;
use \App\Models\OrdemServicoModel as ordemServico;


class ConsultarController extends BaseController{
    

    
    public function index(){
        
        
        $dados =  ['titulo' => 'Orderm de Serviço'];
        $template = $this->twig->loadTemplate('Site/consultar.html');
        $template->display($dados);
        

    }
    
    public function filtro(){
	
        
        if($_SERVER['REQUEST_METHOD'] == 'POST' ){
        
        
        
        //tratar os campos do formulario
        
        $codigo = filter_var($_POST['codigo'], FILTER_SANITIZE_STRING);
        $cpf = filter_var($_POST['cpf'], FILTER_SANITIZE_STRING);
        
        
			
			$query = "SELECT * FROM tb_ordem_servico os inner join tb_usuario o on os.tb_usuario_id=o.id  where os.numero_ordem_servico='".$codigo."' and o.cpf='".$cpf."' LIMIT 1";
		
			$resultado = ordemServico::custom($query);
			foreach($resultado as $info);
			
			if(!empty($info)){
			
		   
				$dados =  ['titulo' => 'Informações Orderm de Serviço',
						   'informacoes' => $info,
						   'status_os' => $this->Status_os($info->status_os)
						   ];
				$template = $this->twig->loadTemplate('Site/mostrar_informacoes.html');
				$template->display($dados);
			
			}else{
				echo "<script>alert('Erro: Ordem de Serviço e/ou CPF nao foram localizados! Tente novamente.')</script>";
				echo "<script>window.history.go(-1)</script>";
				exit();

				//Redirect::to('consultar/');
			
			}
                        
                 
            
           
           }
    
    }
   
        public function Status_os($status){
		
		    switch ($status){
					case 'PENDENTE':
						return array('numero'=>1, 'status'=>'PENDENTE');
					break;        
					case 'ANALIZADA':
						return array('numero'=>2, 'status'=>'ANALIZADA');
					break;
					case 'CONFIRMADA':
						return array('numero'=>3, 'status'=>'CONFIRMADA');
					break;
					case 'AUTORIZADA':
						return array('numero'=>4, 'status'=>'AUTORIZADA');
					break;
					case 'EM PROCESSAMENTO':
						return array('numero'=>5, 'status'=>'EM PROCESSAMENTO');
					break;        
					case 'PROCESSADA':
						return array('numero'=>6, 'status'=>'PROCESSADA');
					break;
					case 'CANCELADA':
						return array('numero'=>7, 'status'=>'CANCELADA');
					break;
		
			
			}
	   }
    
   
    
    

    
}
