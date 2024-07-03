<?php
/**
 * Clase que contiene una serie de metodos para el manejo basico de Ajax.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Crud_Ajax {
    public function __construct() {
        $this->controllerView = Zend_Layout::getMvcInstance()->getView();
        $this->Request        = Zend_Controller_Front::getInstance()->getRequest();

        $this->baseUrl    = $this->controllerView->baseUrl();
        $this->module     = $this->Request->getModuleName();
        $this->controller = $this->Request->getControllerName();
    }

    /*
    public function addListLoad($divUpdate) {	
        return '$.getJSON(urlAjax + "list/data/" + encodeURIComponent($("#' . $divUpdate . '").val()), function(data){executeCmdsFromJSON(data)});' . "\n";
    }
    */


    /*
    public function addListSearch($butonName, $function) {
        return "$('#{$butonName}').click(function(){" .
               "$('#{$butonName}').attr('disabled', true);" .
               "$('#{$butonName}').removeClass('ui-state-hover');" .
               $function .
               "$('#{$butonName}').attr('disabled', null;" .
               '}).hover(function(){$(this).addClass("ui-state-hover");},' .
               'function(){$(this).removeClass("ui-state-hover");}).mousedown(function(){' .
               '$(this).addClass("ui-state-active");' .
               '$(this).removeClass("ui-state-active");});';
    }
     */
}
?>
