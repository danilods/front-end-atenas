<?php


namespace Acme\Classes\Smtp;

class MAIL  {

    public function mandaEmail($de, $para, $assunto, $mensagem, $name='',$resppara=null, $attachfile=null, $style="html")
    {

            include 'SMTP.php';
            $mail = new SMTP;
            $mail->Delivery('relay');
            $mail->Relay('localhost', '', '', 25, '', false);
            $mail->From($de, $name);
            $mail->AddTo($para);
            //if($attachfile) $mail->AttachFile($attachfile);
            if ($style=="html"){
                $mail->Html($mensagem, 'UTF-8');
            }else{
                $mail->Text($mensagem, 'UTF-8');
            }
            $send = $mail->Send($assunto);
            return $send;
        }
    
}
?>