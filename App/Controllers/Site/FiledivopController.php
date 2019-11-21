<?php

namespace App\Controllers\Site;
use Acme\Classes\Parameters as parameter;
use App\Controllers\BaseController;
use App\Models\RecomendacaoModel;
use App\Models\RelatorioModel as relatorio;

class FiledivopController extends BaseController {

	
		public function index() {
		
						
				$valor1 = parameter::getParameter(3);

				//$valor2 = parameter::getParameter(4);

				

						
						
							$path = $_SERVER['DOCUMENT_ROOT']."Public/media/media/prevencao/".$valor1;
							
							
	
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
							
							
							
					
					
					
				
					
					
					
					
					
					
					
					
              
     
       

}