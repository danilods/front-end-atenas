<?php


namespace Acme\Classes;

/*
 * @user João Antonio Felix Barbosa <jhonfelix@hotmail.com>
 * @Descrição SGSV - Sistema de Gerenciamento de Segurança de Voo
 * @Versao 2.0
 * @autor João Antonio
 * @data 30/10/2014 
 */

define("MAXSIZE_FILE", 150000000); //Tamanho máximo de arquivos;

class UploadFile {
      
    

    public static function Enviar($arquivo, $size, $caminho)
    {
        
        //Formatos que são permitidos
        $allowedExts = array('pdf','PDF','doc','DOC', 'docx','DOCX', 'odt','odf','zip','rar','7zip',"jpg", "JPG", "jpeg", "JPEG", "gif", "GIF", "png", "PNG", "bmp", "BMP");
        //verifica se foi postado um arquivo
        if(isset($arquivo)){
 
            //cria a variavel do arquivo
            $file = $arquivo;            
            $arquivo_temporario = $_FILES['arquivo']['tmp_name'];
 
            //pasta destino;
           
            if(self::ExistiDiretorio($_SERVER['DOCUMENT_ROOT']."/sonata/mysonata/web/uploads/media/".$caminho)==1){
                $destino = $_SERVER['DOCUMENT_ROOT']."/sonata/mysonata/web/uploads/media/".$caminho;
            }else{
 //               throw new Exception('Erro: não existe diretório para gravar esse arquivo!');
                echo "<script>alert('Erro: não existe diretório para gravar esse arquivo!')</script>";
                echo "<script>window.history.go(-1)</script>";
                exit();   
            }

            
            // Verifica se o arquivo esta de acordo com as extensão
            $extension = end(explode(".", $file));
            if (!(in_array($extension, $allowedExts))) {
 //                throw new Exception('Não é permitido esta extensão de arquivo! \n As extensões de arquivo permitidas: .$ext_permitido');
                echo '<script>alert("Não é permitido esta extensão de arquivo!\nAs extensões permitidas são: pdf, doc, docx, odt, odf, zip, rar e 7zip.")</script>';
                echo "<script>window.history.go(-1)</script>";
                exit();
            }


            if ($size > MAXSIZE_FILE) { /* verifica o tamanho da arquivo enviada */
//                throw new Exception("Arquivo muito grande ou ausente. O arquivo deve ser inferior a 50 megabytes.');
                echo '<script>alert("Arquivo com tamanho acima do permitido ou ausente.\nO arquivo deve ser inferior a ".  tamanhoArquivo(MAXSIZE_FILE).".")</script>';
                echo "<script>window.history.go(-1)</script>";
                exit();
            }
            
            //Pega a Extensão Original      
            $path_parts = pathinfo($file);
            $completo = md5(date("Y-m-d H:i:s"));
            //Agora vai juntar nome em md5 com a extensão
            $nome_final = $completo . "." . strtolower ($path_parts['extension']);


            if (!empty($nome_final)) {
                move_uploaded_file($arquivo_temporario, "$destino/$nome_final");
                return array('nome' => $nome_final, 'tamanho' => $size, 'status' =>'true');
            }else{
                return array('nome' => $nome_final, 'tamanho' => $size, 'status' =>'false');
            }
        }else{
                echo "<script>alert('Atenção: arquivo ausente. Tente novamente!')</script>";
 //               throw new Exception('Atenção: arquivo ausente. Tente novamente!');
                echo "<script>window.history.go(-1)</script>";
                exit(); 
        }
}

 
    
    private static function ExistiDiretorio($upload_directory) { 
		
		if(is_dir($upload_directory)) {
			return 1;
		}else{
                    mkdir("$upload_directory", 0777);
                        return 1;
                }
		
		
			 
	}
    
    
    
    
}

?>

