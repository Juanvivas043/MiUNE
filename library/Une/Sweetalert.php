<?php

/**
*
* Clase para la integracion de popup (alert) Sweet Alert
*
* @category Une
* @package Une_Sweetalert
* @version 0.1
* @author Alton Bell-Smythe abellsmythe@gmail.com
* 
*
*	ATTENTION
*	type = success, warning, info, success  (DO NOT USE input in regular USE function)
* 	Function could have Ajax
*
*	
*	If you want to make your custom alert yo can use the functions 
*	setBasicaAlert & setFunctionAlert integrating your own configuration
*	in the @param $xtra
*
*
*	Extras
*	Argument 				Default value 		Description
*
*	title 					null (required) 	title of modal
*	text 					null 				description of modal
*	type 					null 				type of the modal (warning,error,success,info,input)
*	allowEscapeKey 			true 				dismiss the modal pressing ESC
*	customClass 			null 				a custom CSS class for the modal
*	allowOutsideClick 		false 				dismiss the modal by clicking outside it
*	showCancelButton 		false 				show Cancel Button
*	showConfirmButton 		true 				show Confirm Button
*	confirmButtonText 		"OK" 				use to change text of Confirm Button
*	confirmButtonColor 		"#AEDEF4" 			use to change background color of Confirm Button
*	cancelButtonText 		"Cancel" 			use to change text of Cancel Button
*	closeOnConfirm 			true 				modal close when the user presses Confirm Button
*	closeOnCancel 			true 				modal Close when the user presses Cancel Button
*	imageUrl 				null 				custom icon for the modal
*	imageSize 				"80x80" 			specify image size of the icon
*	timer 					null 				auto close timer for the modal
*	html 					false 				will not escape title and text parameters
*	animation 				true 				animation for the modal (pop (default true), slide-from-top, slide-from-bottom)
*	inputType 				"text" 				change the type of the input
*	inputPlaceholder 		null 				specify a placeholder to help the user
*	inputValue 				null 				specify a default value to the input
*	showLoaderOnConfirm 	false 				disable the buttons and show that something is loading ( . . . )
*
*
*/

class Une_Sweetalert {

	/**
     * Funcion generar Alerta Sencilla
     *	
     * @param string $msg
     * @return string
     */
	public function setSimpleAlert($msg){
		$json = "swal(\"$msg\")";
		return $json;
	}

	/**
     * Funcion generar Alerta Basica
     *	
     * @param string $type
     * @param string $title
     * @param string $msg
     * @param string $xtra
     * @return string
     */
	public function setBasicAlert($type,$title,$msg,$xtra = NULL){
		if($xtra != NULL) { $xtra = ','.$xtra; }
		$json = 'swal({ title: "'.$title.'", text: "'.$msg.'", type: "'.$type.'"'.$xtra.'});';
		return $json;
	}

	/**
     * Funcion generar Alerta Sencilla con Foto Personalizada
     *	
     * @param string $title
     * @param string $msg
     * @param string $pic (route)
     * @return string
     */
	public function setPictureAlert($title,$msg,$pic){
		$json = "swal({ title: \"$title\", text: \"$msg\", imageUrl: \"$pic\" });";
		return $json;
	}

	/**
     * Funcion generar Alerta Sencilla con Timer
     *	
     * @param string $title
     * @param string $msg
     * @param numeric $time (miliseconds)
     * @return string
     */
	public function setTimerAlert($title,$msg,$time){
		$json = "swal({ title: \"$title\", text: \"$msg\", timer: $time, showConfirmButton: false });";
		return $json;
	}

	/**
     * Funcion generar Alerta Sencilla con Input
     *	
     * @param string $title
     * @param string $msg
     * @param string $placeholder
     * @param string $functionConfirm
     * @param string $functionCancel
     * @return string
     */
	public function setInputAlert($title,$msg,$placeholder,$functionConfirm,$functionCancel = NULL){
		$json = 'swal({ title: "'.$title.'", text: "'.$msg.'", type: "'.$input.'", showCancelButton: true, closeOnConfirm: false, inputPlaceholder: "'.$placeholder.'"
				},
				function(inputValue){
				  if (inputValue === false) return false;
				  if (inputValue === "") { '.$functionCancel.' return false }
				  '.$functionConfirm.' });';
		return $json;
	}


	/**
     * Funcion generar Alerta con Funcion
	 *
	 * Be careful with function param, you can use value isConfirm as Condition
     *
     * @param string $type
     * @param string $title
     * @param string $msg
     * @param string $function	
     * @param string $xtra	
     * @return string
     */
	public function setFunctionAlert($type,$title,$msg,$function, $xtra = NULL){
		$json = 'swal({ title: "'.$title.'", text: "'.$msg.'", type: "'.$type.'", '.$xtra.', closeOnConfirm: false }, 
						function(isConfirm){ '.$function.' });';
		return $json;
	}

	/**
     * Funcion generar Compleja Alerta con Funcion de Cancel y Confirm
	 *
	 * Be careful with function param
     *
     * @param string $type
     * @param string $title
     * @param string $msg
     * @param string $functionConfirm	
     * @param string $functionCancel
     * @param string $btnConfirm
     * @param string $btnCancel
     * @return string
     */
	public function setComplexAlert($type,$title,$msg,$functionConfirm,$functionCancel = NULL,$btnConfirm = "Ok",$btnCancel = "Cancel"){
		$json = 'swal({ title: "'.$title.'", text: "'.$msg.'", type: "'.$type.'", showCancelButton: true, confirmButtonColor: "#00787A", confirmButtonText: "'.$btnConfirm.'", cancelButtonText: "'.$btnCancel.'", closeOnConfirm: false, closeOnCancel: false},
				function(isConfirm){ if (isConfirm) { '.$functionConfirm.' } else { '.$functionCancel.' } });';
		return $json;
	}

}

?>