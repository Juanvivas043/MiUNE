<?php
/**
 * Clase que contiene una serie de metodos que permiten la integracion con el
 * framework de javascript llamado jQuery.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Jquery_Ui_Form {
    private $_EndLineStatus = false;

	public function  __construct() {
		$this->request    = new Zend_Controller_Request_Http();

		if ($this->request->isXmlHttpRequest()) {
			$this->endLine(false);
		} else {
			$this->endLine(true);
		}

	}

    /**
     * Indica que toda sentencia de JavaScrip retornada por esta clase debe
     * terminar en punto y coma ";".
     *
     * @param bool $enable
     */
    public function endLine($enable) {
        $this->_EndLineStatus = (bool)$enable;
    }

    /**
     * Dependiendo de lo definido con el metodo "endLine", se encarga de retornar
     * el punto y coma ";" a cada sentencia que se forma en esta clase.
     *
     * @return string
     */
    private function _getEndLine() {
        return ($this->_EndLineStatus == true)? ';' : '';
    }

    /**
     * Cambia el titulo a un dialogo en especifico.
     * 
     * @param string $form
     * @param string $text
     * @return string
     */
    public function changeTitle($form, $text) {
        return "$('#{$form}').dialog({title: '{$text}'})";
    }

    /**
     * Permite mover el modal por la ventana.
     *
     * @param string $form
     * @param boolean $condition
     * @return string
     */
    public function setDraggable($form, $condition) {
        return "$('#{$form}').dialog({draggable: true})";
    }

	/**
	 * Define el margen de alineación desde la izquierda de todos los objetos HTML
	 * que se encuentran dentro del formulario modal.
	 *
	 * @param string $form
	 * @param integer $width
	 * @return string
	 */
	public function setAlignWidthLeft($form, $width) {
		 return "$('#{$form} .zend_form dt').css('width', '{$width}')";
	}

	/**
	 * Permite centar el formulario en la ventana.
	 *
	 * @param string $form
	 * @return string
	 */
	public function setCenter($form) {
		 return "$('#{$form}').dialog('option','position','center')";
	}

    /**
     * Cierra un dialogo.
     *
     * @param string $id
     * @return string
     */
    public function close($id) {
        return "$('#{$id}').dialog('close')";
    }

    /**
     * Abre un dialogo.
     *
     * @param string $id
     * @return string 
     */
    public function open($id) {
        return "$('#{$id}').dialog('open')";
    }

    /**
     * Redefine el ancho de un dialogo modal.
     *
     * @param string $id
     * @param int    $width
     * @return string
     */
    public function setWidth($id, $width) {
        return "$('#{$id}').dialog('option','width',{$width})";
    }

    /**
     * Redefine el alto de un dialogo modal.
     *
     * @param string $id
     * @param int    $width
     * @return string
     */
    public function setHeight($id, $height) {
        return "$('#{$id}').dialog('option','height',{$height})";
    }

    /**
     * Permite mostrar un boton en especifico del formulario.
     * NOTA: Estos se usan despues de haber llamado al metodo open($id)/close($id)
     *       para que pueda funcionar correctamente.
     *
     * @param int $index
     * @return string
     */
    public function buttonShow($id, $name, $id_button = null) {
		$string = "$('#{$id}').parent().find(\"button:contains('{$name}')\").show()";
        $string .= (isset($id_button)) ? ".attr('id', 'btn".$id_button."')" : "";
        return $string;
    }

    /**
     * Permite ocultar un boton en especifico del formulario.
     * NOTA: Estos se usan despues de haber llamado al metodo open($id)/close($id)
     *       para que pueda funcionar correctamente.
     *
     * @param int $index
     * @return string
     */
    public function buttonHide($id, $name, $id_button = null) { 
            $string = "$('#{$id}').parent().find(\"button:contains('{$name}')\").hide()";
            $string .= (isset($id_button)) ? ".attr('id', 'btn".$id_button."')" : "";
            return $string;
    }

    public function hideThisInputs($ids) {

        foreach($ids as $id){

            $js .= "$('#{$id}').children().val('');";
            $js .= "$('#{$id}').hide();";
        }

        return $js;
    }


    public function showThisInputs($ids) {

        foreach($ids as $id){

            $js .= "$('#{$id}').show();";
        }

        return $js;
    }

    public function hideAllInputs($id) {
		return "$('#{$id} :input').siblings().hide()";
    }

    public function ShowAllInputs($id, $name) {
		return "$('#{$id} :input').siblings().show()";
    }

    public function buttonVisibility($index, $condition) {
        return "$('.ui-dialog-buttonpane button:eq({$index})')." . (($condition == true)? "show" : "hide") . "()";
    }


    /**
     * Muestra mensajes de error en el formulario
     *
     * @param string $msg mensaje que se mostrara
     * @param string $id id del elemento al que se anexara el mensaje
     * @return string
     *
     */

    public function cleanErrors(){

        return "$('.errors').remove()";
    }

    public function displayError($msg,$id){

        return "$('#{$id}').after('<ul class=\'errors\'><li>{$msg}</li></ul>');";

    }

    /**
     * Define el decorado a los botones según el Theme cargados.
     *
     * @param string $name Nombre del div donde se asigna el decorado.
     * @return string
     */


    public function buttonDecorator($name) {
//        return "$('button, input:submit, a', '.{$name}').button();";
        return "$('button, input:submit').button()" . $this->_getEndLine();
    }

	/**
	 * Desabilita un boton de la pagina. No del formulario modal.
	 *
	 * @param string  $id
	 * @param boolean $Disable
	 * @return string
	 */
	public function buttonDisable($id, $Disable) {
		$Disable = ($Disable == true)? 'true' : 'false';
		
		return "$('#{$id}').button({ disabled: {$Disable} })" . $this->_getEndLine();
	}

   public function addJscript($script){
      // return $script . $this->_getEndLine();
      return "$.globalEval(\"{$script}\")" . $this->_getEndLine();
      // return "eval(\"{$script}\")" . $this->_getEndLine();
   }
}
?>
