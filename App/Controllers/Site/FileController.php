<?php

namespace App\Controllers\Site;
use Acme\Classes\Parameters as parameter;
use App\Controllers\BaseController;
use App\Models\RecomendacaoModel;
use App\Models\RelatorioModel as relatorio;

class FileController extends BaseController {

	
		public function index() {
		
		
				$valor = parameter::getParameter(2);
				
				$idioma = parameter::getParameter(3);

				$valor2 = parameter::getParameter(4);

				

				$resultado2 = relatorio::all(array('conditions' => array('id = ? AND matricula = ?', $valor, $valor2)));
			
				
				
				if (count($resultado2)>0){
					
					
					
        
        
						
						
						    foreach($resultado2 as $r);
					  
							$data[] = $r->to_array();
							
							
							if($idioma == "PT"){
						
						
							$path = $_SERVER['DOCUMENT_ROOT']."Public/media/media/".$data[0]['anexo_pt'];
							
							}
							
							elseif($idioma == "EN"){
								
								$path = $_SERVER['DOCUMENT_ROOT']."Public/media/media/".$data[0]['anexo_en'];
							}
							
							else{
								
								$path = $_SERVER['DOCUMENT_ROOT']."Public/media/media/".$data[0]['anexo_es'];
							}
						
							// var_dump($path);
							
	
							$ext = explode('/',$path); // separa a url em partes
							$file = $ext[(count($ext)-1)]; // captura tudo registro do array que é o nome do arquivo
										   
									  
						  if(is_file($path)){                       
							header("Content-type: application/pdf");
							header("Content-Disposition: attachment; filename=" . $file);
							readfile("{$path}");
						}else{
							echo "<script>alert('Erro: arquivo não encontrado em nosso sistema!')</script>";
							echo "<script>window.history.go(-1)</script>";
							exit();   
						}
											  

					
					  
							}
							
							
							
							
							else{
								echo "<script>alert('Erro: arquivo não encontrado em nosso sistema!')</script>";
								echo "<script>window.history.go(-1)</script>";
								exit();   
							}
						
						}
					
					
					
				
					
					
					
					
					
					
					
					
              
     
       

}