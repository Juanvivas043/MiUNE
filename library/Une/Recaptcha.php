<?php

/**
*
* Clase que permite autentificar el captcha de un formulario
*
* @category Une
* @package Une_Recaptcha
* @version 0.1
* @author Alton Bell-Smythe abellsmythe@gmail.com
* @mail une.ddti.info@gmail.com
*
* Site Key: 6Le2pR8TAAAAAOgkMMHpwruVRwVDJ4ghU3LqWjaM
*
*/

// GOOGLE
require_once "recaptchalib.php";

class Une_Recaptcha {

	// secret password
	private $secret   = "6Le2pR8TAAAAANajBOZ2mlJZkJsfrFhvDZXT-AA1";

	function __construct(){
		$this->reCaptcha = new ReCaptcha($this->secret);
	}

	/**
     * Retorna si el captcha es correcto o no
     *
     * @return boolean
     */
	public function checkCaptcha($response){

		$verify = $this->reCaptcha->verifyResponse($_SERVER["REMOTE_ADDR"],$response);

		return $verify;
	}	
}

?>