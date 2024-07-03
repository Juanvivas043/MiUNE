<?php
/**
 * Clase que contiene una serie de metodos que permiten la integracion con el
 * framework de javascript llamado jQuery.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Jquery {
    private $_EndLineStatus = false;

	public function  __construct() {
		$this->request = new Zend_Controller_Request_Http();

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
     * Cambia el HTML de un objeto en especifico.
     *
     * @param string $Id
     * @param string $html
     * @return string
     */
    public function setHtml($Id, $html) {
        return "$('#{$Id}').html('{$html}')" . $this->_getEndLine();
    }

    /**
     * Cambia el HTML de un objeto en especifico.
     *
     * @param string $Id
     * @param string $html
     * @return string
     */
    public function setHtmlClass($Id, $html) {
        return "$('.{$Id}').html('{$html}')" . $this->_getEndLine();
    }

    /**
     * Agrega HTML al final de un objeto especifico
     *
     * @param string $Id
     * @param string $html
     * @return string
     */
    public function setAppend($Id, $html) {
        return "$('#{$Id}').append('{$html}')" . $this->_getEndLine();
    }

    public function fillSelectByArray($Id, $array,$pk,$nombre){
        if (is_array($array) && is_array($array[0])){
            foreach ($array as $key => $value) {
                $name = $value[$nombre];
                $val  = $value[$pk];
                $json .= "$('#{$Id}').append(new Option(\"{$name}\",\"{$val}\"))" . $this->_getEndLine();
            }
            return $json;
        }
    }
 
    /**
     * Cambia o define los atributos de un objeto en especifico.
     *
     * @param string $Id
     * @param string $attr
     * @param string $value
     * @return string
     */
    public function setAttr($Id, $attr, $value) {
        return "$('#{$Id}').attr('{$attr}', {$value})" . $this->_getEndLine();
    }

    public function removeAttrAll($type, $name, $attr) {
        $name = (!empty($name))? "[name=\'{$name}\']" : "";

        return "$('input:{$type}{$name}').removeAttr('{$attr}')" . $this->_getEndLine();
    }

    public function removeAttr($Id, $attr) {
        return "$('#{$Id}').removeAttr('{$attr}')" . $this->_getEndLine();
    }
    /**
     * Obtiene el valor de un objeto en especifico.
     *
     * @param string $Id
     * @return string
     */
    public function getVal($Id) {
        return "$('#{$Id}').val()" . $this->_getEndLine();
    }

    /**
     * Genera el Hash de tipo MD5 al contenido de un determinado objeto HTML.
     * Nota: La genaración del MD5 no es una funcionalidad propia de la libreria
     * de jQuery, es realizada por terceros, contemple agregar esta libreria para
     * su funcionamiento.
     *
     * URL: http://plugins.jquery.com/project/md5
     *
     * @param string $Id
     * @return string
     */
    public function getValInMD5($Id) {
        return "$.md5($('#{$Id}').val())" . $this->_getEndLine();
    }

    /**
     * Obtiene el valor de un objeto en especifico para ser codificado de tal forma
     * que pueda ser enviado por el URI.
     *
     * @param string $Id
     * @return string
     */
    public function getValEncodedUri($Id) {
        return "encodeURIComponent($('#{$Id}').val())" . $this->_getEndLine();
    }

    /**
     * Obtiene el indice de la opción seleccionada a un Objeto HTML de tipo SELECT.
     *
     * @param string $Id
     * @return string
     */
    public function getValSelectOption($Id) {
        return "$('#{$Id} option:selected').val()" . $this->_getEndLine();
    }

    /**
     * Define un nuevo valor de un objeto en especifico.
     *
     * @param string $Id
     * @param string $value
     * @return string
     */
    public function setVal($Id, $value = null) {
        return "$('#{$Id}').val('{$value}')" . $this->_getEndLine();
    }

    /**
     * Define un nuevo texto de un objeto en especifico.
     *
     * @param string $Id
     * @param string $txt
     * @return string
     */
    public function setText($Id, $value = null) {
        return "$('#{$Id}').text('{$value}')" . $this->_getEndLine();
    }

    /**
     * Define valores de una serie de objetos
     *
     * @param array(array) $array
     * @return string
     */
    public function setVals($array) {
        foreach ($array as $key => $value) {
            $id    = $value[0];
            $val   = $value[1];
            $HTML .= "$('#{$id}').val('{$val}');";
        }
        return $HTML;
    }

    /**
     * Define valor de un radio button
     *
     * @param string $name
     * @param string $value 
     * @return string
     */
    public function setRadio($name,$value) {
        if(!is_numeric($value)){
            if($value){ $value = 1; }
            else{ $value = 0; }
        }
        return "$(\"input[name=$name][value=$value]\").attr(\"checked\",\"checked\");";
    }

    /**
     * Crea la función que se encarga de mostrar un objeto HTML de tipo DIV, con
     * el fin de indicar que se esta ejecutando una llamada de tipo AJAX.
     *
     * @param string $Id
     * @return string
     */
    public function ajaxStart($Id) {
        return '$("#' . $Id . '").ajaxStart(function(){$(this).show();})' . $this->_getEndLine();
    }

    /**
     * Crea la función que se encarga de mostrar un objeto HTML de tipo DIV, con
     * el fin indicar que a finalizado la ejecución de una llamada de tipo AJAX.
     *
     * @param string $Id
     * @return string
     */
    public function ajaxStop($Id) {
        return '$("#' . $Id . '").ajaxStart(function(){$(this).hide();})' . $this->_getEndLine();
    }

	/**
	 * Asigna a un div con intención de notfificar al usuario cuando hay
	 * comunicación mediante AJAX.
	 *
	 * @param string $Id
	 * @return string
	 */
    public function getLoading($Id, $txt = null) {
    	$Id = "ellipsis";
        //Function setLoadingText 
        $js  = 'function setLoadingText(txt){$("#loadingTxt").text(txt);} setLoadingText("'.$txt.'");';
        $js .= '$("#' . $Id . '").ajaxStart(function(){$(this).show(); $("#full_background").addClass("blur"); $(".shadow").css("display","block"); });';
        $js .= '$("#' . $Id . '").ajaxStop(function(){$(this).hide(); $("#full_background").removeClass("blur"); $(".shadow").css("display","none"); });';
	$js .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  		})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  		ga('create', 'UA-60418921-2', 'auto');
  		ga('send', 'pageview');";

        return $js;
    }

    /**
     * Crea una petición GET por AJAX para recibir los datos mediante JSON. Se permite
     * el paso de parametros con valores ya definidos, o paso de parametros cuyos
     * valores se hayan en tiempo de ejecución de funciones mediante JavaScript.
     *
     * @param string $Action
     * @param array  $Params
     * @param array  $Functions
     * @return string
     */
    public function getJSON($Action, $Params = null, $Functions = null) {
        $Data     = '';
        $function = '';

        if(is_array($Params)) {
            foreach($Params as $Param => $Value) {
                $Data .= "{$Param}/{$Value}/";
            }
        }

        if(is_array($Functions)) {
            foreach($Functions as $Param => $Function) {
                $Function = trim($Function, ';');
                $Function = "\"+{$Function}+\"";
                $Data    .= "{$Param}/{$Function}/";
            }
        }

        $Data = rtrim($Data, '/');
        $Data = (isset($Data))? "{$Data}" : null;

        return "$.getJSON(urlAjax + \"{$Action}/{$Data}\", function(d){executeCmdsFromJSON(d)})" . $this->_getEndLine();
    }

    /**
     * Crea una petición por POST AJAX para recibir los datos mediante JSON. Se permite
     * el paso de parametros con valores ya definidos, o paso de parametros cuyos
     * valores se hayan en tiempo de ejecución de funciones mediante JavaScript.
     *
     * @param string $Action
     * @param array  $Params
     * @param array  $Functions
     * @return string
     */
    public function Post($Action, $Params = null, $json='json') {
        $Data = str_replace('"', '', json_encode($Params));
        return "$.post(urlAjax + \"{$Action}\",{$Data}, function(d){executeCmdsFromJSON(d)},'{$json}')" . $this->_getEndLine();
    }
    /**
     * Obtiene todos los datos de un formulario, los serializa para ser enviados
     * por el URI y los codifica los caracteres especiales.
	 *
	 * Nota: Recuerde tener la propiedad id y name definida en cada objeto del
	 * formulario.
     *
     * @param string $Id
     * @return string
     */
    public function serializeForm($Id = null, $Idnot = null) {
        if(empty($Id)) {
            if(!empty($Idnot)){
                foreach($Idnot as $Idn){
                    $not .= "#$Idn,";
                }
            return "escape($(':input').not('$not').serialize())" . $this->_getEndLine();
            }
            return "escape($(':input').serialize())" . $this->_getEndLine();
        } else {
            if(!empty($Idnot)){
                foreach($Idnot as $Idn){
                    $not .= "#$Idn,";
                }
            return "escape($('#{$Id}').find(':input').not('$not').serialize())" . $this->_getEndLine();
            }
            return "escape($('#{$Id}').find(':input').serialize())" . $this->_getEndLine();
        }
    }

    /**
     * Serializa un grupo de objetos HTML.
     *
     * @param string $Id
     * @return string
     */
    public function getSerialize($Id) {
        return "$('{$Id}').serialize()" . $this->_getEndLine();
    }

    /**
     * Agrega un evento al tetectar un cambio a un objeto HTML especifico de
     * cualquier tipo INPUT.
     *
     * @param string $Id
     * @param string $Functions
     * @return string
     */
    public function setChange($Id, $Functions) {
        return "$('#{$Id}').change(function(){" . $Functions . "})" . $this->_getEndLine();
    }

    /**
     * Permite detectar si un objeto HTML especifico de tipo INPUT, en especifico
     * CHECKBOX o RADIO ha sido seleccionado.
     *
     * @param string $Id
     * @param boolean $Not Niega el resultado aplicando un NOT.
     * @return string
     */
    public function isChecked($Id, $Not = false) {
        return (($Not == true)? '!' : '') . "$('#{$Id}').is(':checked')" . $this->_getEndLine();
    }

    /*
     * Permite ocultar un objeto HTML en especifico.
     *
     * @param string $Id
     * @return string
     */
    public function setHide($Id) {
        return "$('#{$Id}').hide()" . $this->_getEndLine();
    }

    /*
     * Permite ocultar objeto(s) HTML por clases.
     *
     * @param string $class
     * @return string
     */
    public function setHideClass($class) {
        return "$('.{$class}').hide()" . $this->_getEndLine();
    }

    /*
     * Permite ocultar objeto(s) HTML por etiqueta.
     *
     * @param string $tag
     * @return string
     */
    public function setHideTag($tag) {
        return "$('{$tag}').hide()" . $this->_getEndLine();
    }


    /**
     * Permite ocultar un objeto HTML en especifico.
     *
     * @param string $Id
     * @return string
     */
    public function setShow($Id) {
        return "$('#{$Id}').show()" . $this->_getEndLine();
    }

    /**
     * Agrega un evento click a un objeto en especifico.
     *
     * @param string $Id
     * @param string $Functions
     * @return string
     */
    public function setClick($Id, $Functions) {
        return "$('#{$Id}').click(function(){" . $Functions . "})" . $this->_getEndLine();
    }

    /**
     * Asigna el foco a un objeto HTML en especifico.
     *
     * @param string $Id
     * @return string
     */
    public function setFocus($Id) {
        return "$('#{$Id}').focus()" . $this->_getEndLine();
    }

    public function ifSetAttr($Condition, $Id, $attr, $value){
        return "if($Condition){ $('#{$Id}').attr('{$attr}', {$value}) }" . $this->_getEndLine();
    }

    public function ifSetVal($Condition, $Id, $value){
        return "if($Condition){ $('#{$Id}').val('{$value}') }" . $this->_getEndLine();
    }

    public function ifSetValSelectOption($Condition, $Id, $Index) {
        return "if($Condition){ $('#{$Id} option:eq({$Index})').attr('selected','selected') }" . $this->_getEndLine();
    }

    public function setValSelectIndex($Id, $Index) {
        return "$('#{$Id} option:eq({$Index})').attr('selected','selected')" . $this->_getEndLine();
    }

    public function setValSelectOption($Id, $Option) {
        return "$('#{$Id} option[value={$Option}]').attr('selected','selected')" . $this->_getEndLine();
    }

    public function fillSelect($Id, $Action, $Params = array()) {
		$jParams = '';
		if(count($Params) > 0) {
			foreach($Params as $ParamIndex => $ParamValue) {
				$jParams .= "/$ParamIndex/' + $('#{$ParamValue}').val() + '";
			}
		}

		$Param = (isset($Param))? "/{$Param}/" : '';
		$Value = (isset($Value))? "+ $('#{$Value}').val()" : '';

		return "$.getJSON(urlAjax + '{$Action}{$jParams}', function(j){fillSelect('select#{$Id}', j)})" . $this->_getEndLine();
    }

    public function fillSelectRecursive($Id, $Action, $Params = array(), $recursive) {
		$jParams = '';
		if(count($Params) > 0) {
			foreach($Params as $ParamIndex => $ParamValue) {
				$jParams .= "/$ParamIndex/' + $('#{$ParamValue}').val() + '";
			}
		}

		$Param = (isset($Param))? "/{$Param}/" : '';
		$Value = (isset($Value))? "+ $('#{$Value}').val()" : '';

		return "$.getJSON(urlAjax + '{$Action}{$jParams}', function(j){fillSelect('select#{$Id}', j);$recursive})" . $this->_getEndLine();
    }
    /**
     * Permite seleccionar y deseleccionar todos los elementos de una lista de objetos HTML de tipo Checkbox.
     *
     * @param string $Id
     * @param string $IdChecksList
     * @return string
     */
    public function checkOrUncheckAll($Id, $IdChecksList) {
        return "$('input[name={$Id}]').click(function(){var checked_status = this.checked;$('input[name={$IdChecksList}]').each(function(){ if($(this).is(':disabled') == false) { this.checked = checked_status;} });})" . $this->_getEndLine();
    }
}
?>
