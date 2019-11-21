<?php

namespace Acme\Classes\captcha;
 
/* 
** João antonio Felix
*/

class Captcha {

	private $sessionId;
	

	public function __construct($sessionId = null) {
		if($sessionId === null)
			$this->sessionId = md5($_SERVER['SERVER_NAME'] . ':' . $_SERVER['REMOTE_ADDR']);
		else
			$this->sessionId = $sessionId;
	}
	
	public function getImageCaptcha() {
		$imagemCaptcha = imagecreatefrompng("fundocaptch.png");
		$fonteCaptcha = imageloadfont("anonymous.gdf");
		$corCaptcha = imagecolorallocate($imagemCaptcha,255,0,0);
		imagestring($imagemCaptcha,$fonteCaptcha,15,5,$this->sessionId,$corCaptcha);
		header("Content-type: image/png");
		imagepng($imagemCaptcha);
		imagedestroy($imagemCaptcha);
	}
	

	public function checkCaptcha($sessao) {
		if(empty($sessao)){
			return false;
		}
		if($this->sessionId!=$sessao){
			return  false;
		}else{
			return  true;
		}
	}
}