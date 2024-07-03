<?php

/**
 * Clase que permite crea y controla los formularios de tipo modal para agregar,
 * editar, ver y eliminar los datos relacionados a un registro determinado. En
 * esta clase se crea todo el codigo HTML y JavaScript necesario. Esta orientado
 * su uso a jQuery.
 *
 * @category SwapBytes
 * @package  SwapBytes_Crud_Form
 * @version  0.3
 * @author   Nicola Strappazzon C., nicola51980@gmail.com, http://nicola51980.blogspot.com
 */
define('swOkOnly', 1);
define('swYesNo', 2);

class SwapBytes_Crud_Form extends SwapBytes_Form { 

    private $_data;
    private $_nameModal = 'frmModal';
    private $_nameModalButonNameOk = 'Aceptar';
    private $_nameModalButonNameSave = 'Guardar';
    private $_nameModalButonNameCancel = 'Cancelar';
    private $_nameModalButonNameDelete = 'Eliminar';
    private $_nameDialog = 'frmDialog';
    private $_title;
    private $_insertJS;
    private $_json = array();
//    private $_height;
//    private $_width;
    private $_widthLeft = '100px';

    public function __construct() {
        $this->_controller = Zend_Controller_Front::getInstance();
        $this->_request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->SwapBytes_Html_Message = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri = new SwapBytes_Uri();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->CmcBytes_Redirect = new CmcBytes_Redirect();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
    }

    /**
     * Define las propiedades basicas de un formulario.
     *
     * @param Zend_Form $form
     * @param array     $data
     * @param string    $title
     * @param string    $message
     */
    public function setProperties($form, $data = null, $title = null, $message = null) {
        $this->_form = $form;
        $this->_title = $title;
        $this->_data = $data;
        $this->_message = $message;
    }
    
    public function addJS($JS){
       $this->_insertJS = $JS;
    }

    /**
     * Asigna sentencias adicionales de jQuery para ser enviadas por JSON al formulario.
     * 
     * @param array $json
     */
    public function setJson($json) {
        if (is_array($json)) {
            $this->_json = $json;
        }else{
        }
    }

    /**
     * Asigna un formulario a un modal determinado de jQuery UI, unicamente con
     * la configuración de una Vista, no permite la modificación y agregación de
     * datos.
     */
    public function getView($hmtlpredef = null) {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            if (is_array($this->_data)) {
                $this->_form->populate($this->_data);
            }

            $this->set($this->_form);
            $this->enableElements(false);
            if(empty($hmtlpredef)){
            $html = $this->SwapBytes_Ajax->render($this->_form);
            // Preparamos el arreglo de JSON.
            $json = array();
            $json[] = $this->SwapBytes_Jquery->setHtml($this->_nameModal, $html);
            }else{               
                $hmtlpredef = addslashes($hmtlpredef);
                $hmtlpredef = str_replace("\n", "\\n", $hmtlpredef);
            $json[] = $this->SwapBytes_Jquery->setHtml($this->_nameModal, $hmtlpredef);
            }
//            $json[] = $this->SwapBytes_Jquery_Ui_Form->setHeight($this->_nameModal, $this->_height);
//            if($this->_width)    
//                $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth($this->_nameModal , $this->_width);
            
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter($this->_nameModal);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->open($this->_nameModal);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle($this->_nameModal, $this->_title);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameModal, 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameModal, 'Cancelar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameModal, 'Aceptar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameModal, 'Eliminar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setAlignWidthLeft($this->_nameModal, $this->_widthLeft);
            $json = array_merge($json, $this->_json);

            $this->_controller->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    /**
     * Crea un dialogo para tomar acciones alternativas con interacción con el usuario.
     *
     * @param string $title
     * @param string $html
     * @param int    $buttons
     */
    public function getDialog($title, $html, $buttons = swOkOnly) {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $html = "<div style=\"text-align:left;\">{$html}</div>";

            $json[] = $this->SwapBytes_Jquery->setHtml($this->_nameDialog, $html);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle($this->_nameDialog, $title);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter($this->_nameDialog);
            
           
            if ($buttons == swYesNo) {
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameDialog, 'Ok');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameDialog, 'Si');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameDialog, 'No');
            } else if ($buttons == swOkNo) {
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameDialog, 'Ok');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameDialog, 'Si');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameDialog, 'No');
            } else {
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameDialog, 'Ok');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameDialog, 'Si');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameDialog, 'No');
            }

            $json[] = $this->SwapBytes_Jquery_Ui_Form->open($this->_nameDialog);

            $this->_controller->getResponse()->setBody(Zend_Json::encode($json));
            $this->SwapBytes_Ajax->endResponse();
        }
    }
    
    public function getDialogParams($title, $html, $buttons = swOkOnly, $param) {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
            $html = "<div style=\"text-align:left;\">{$html}</div><div hidden id=\"aghide\">{$param}</div>";

            $json[] = $this->SwapBytes_Jquery->setHtml($this->_nameDialog, $html);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle($this->_nameDialog, $title);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter($this->_nameDialog);
            
           
            if ($buttons == swYesNo) {
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameDialog, 'Ok');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameDialog, 'Si');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameDialog, 'No');
            } else if ($buttons == swOkNo) {
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameDialog, 'Ok');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameDialog, 'Si');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameDialog, 'No');
            } else {
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameDialog, 'Ok');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameDialog, 'Si');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameDialog, 'No');
            }

            $json[] = $this->SwapBytes_Jquery_Ui_Form->open($this->_nameDialog);

            $this->_controller->getResponse()->setBody(Zend_Json::encode($json));
            $this->SwapBytes_Ajax->endResponse();
        }
    }

    
    /**
     * Define la dimención del formulario.
     *
     * @param int $height
     * @param int $width
     */
    public function setSize($height, $width) {
        $this->_height = $height;
        $this->_width = $width;
    }

    /**
     * @todo crear el metodo que se trae todos los parametros y organizarlos bien en un arreglo.
     * $param['modal']['id']
     * $param['database']['pk_tabla']
     * $param['filters']['selFiltro']
     */
    public function getParams() {
        $paramData = $this->Request->getParam('data');
        $params = $this->SwapBytes_Uri->queryToArray($paramData);
        $params['id'] = (!empty($params['id'])) ? $params['id'] : $this->Request->getParam('id', 0);

        return $params;
    }


    /**
     * Asigna un formulario a un modal determinado de jQuery UI, unicamente con
     * la configuración para agregar o modificar los datos del registro seleccionado.
     */
    public function getAddOrEditLoad() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            if (is_array($this->_data)) {
                $this->_form->populate($this->_data);
            }
            

            $html = $this->SwapBytes_Ajax->render($this->_form);

            $json[] = $this->SwapBytes_Jquery->setHtml($this->_nameModal, $html);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter($this->_nameModal);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->open($this->_nameModal);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle($this->_nameModal, $this->_title);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setDraggable($this->_nameModal, true);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameModal, 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameModal, 'Cancelar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameModal, 'Aceptar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameModal, 'Eliminar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setAlignWidthLeft($this->_nameModal, $this->_widthLeft);
            $json = array_merge($json, $this->_json);

            $this->_controller->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    /**
     * Retorna una respuesta dependiendo del resultado del formulario, si existe
     * un error en la validación es mostrado los mensajes, si la validación es un
     * exito cierra el formulario y refresca la lista. Se realiza un response de
     * AJAX usando JSON.
     *
     * @param boolean $permit Permite salvar un registro dependiendo del resultado
     *                        obtenido de una validación.
     */
    public function getAddOrEditConfirm() {

       
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json = array();

            $this->_form->populate($this->_data);
            
            if ($this->_form->isValid($this->_data)) {
                $json[] = $this->SwapBytes_Jquery->getJSON('addoreditresponse',
                                array('page' => $this->_request->getParam('page', 1)),
                                array('data' => $this->SwapBytes_Jquery->serializeForm($this->_nameModal, array(1 => 'contenido_html')),
                                    'filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros')));

            } else {
                
                $html = $this->SwapBytes_Ajax->render($this->_form);

                $json[] = $this->SwapBytes_Jquery->setHtml($this->_nameModal, $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter($this->_nameModal);
                //$json[] = $this->SwapBytes_Jquery_Ui_Form->setAlignWidthLeft($this->_nameModal, $this->_widthLeft);


                
            }

            $json = array_merge($json, $this->_json);

            $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript('startTinyMCE();');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript('startFileUploader();');
            $this->_controller->getResponse()->setBody(Zend_Json::encode($json));
            
        }
    }

    public function getAddOrEditEnd($data = null) {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json = array();
            $json[] = $this->SwapBytes_Jquery_Ui_Form->close($this->_nameModal);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->close($this->_nameDialog);
            $json[] = $this->SwapBytes_Jquery->getJSON('list',
                            array('page' => $this->_request->getParam('page', 1)),
                            array('buscar' => $this->SwapBytes_Jquery->getValEncodedUri('txtBuscar'),
                                'filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros')));

            if($data){
                $json[] = $this->CmcBytes_Redirect->getRedirect($data);
            }
            
            $json = array_merge($json, $this->_json);

            $this->_controller->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    /**
     * Define el espacio en que debe existir entre la ventana y el lado izquierdo
     * de los Labels del formulario, se ajusta dependiento del ancho que ocupe
     * todo el texto en general de los Labels.
     * 
     * @param string $width
     */
    public function setWidthLeft($width) {
        $this->_widthLeft = $width;
    }

    /**
     * Asigna un formulario a un modal determinado de jQuery UI, unicamente con
     * la configuración para eliminar los datos del registro seleccionado.
     *
     * @param boolean $permit Permite o no la acción de eliminar un registro
     * 						  dependiendo del resultado obtenido de una validación
     * 						  al momento de cargarse el modal.
     */
    public function getDeleteLoad($permit = true) {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $this->_form->populate($this->_data);
            $this->set($this->_form);
            $this->enableElements(false);

            if (!empty($this->_message)) {
                $html = $this->SwapBytes_Html_Message->alert($this->_message);
                $html .= '<br>';
            }

            $html .= $this->SwapBytes_Ajax->render($this->_form);

            // Preparamos el arreglo de JSON.
            $json = array();
            $json[] = $this->SwapBytes_Jquery->setHtml($this->_nameModal, $html);
//            $json[] = $this->SwapBytes_Jquery_Ui_Form->setHeight($this->_nameModal, $this->_height);
//            $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth($this->_nameModal , $this->_width);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter($this->_nameModal);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->open($this->_nameModal);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle($this->_nameModal, $this->_title);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameModal, 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameModal, 'Cancelar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameModal, 'Aceptar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setAlignWidthLeft($this->_nameModal, $this->_widthLeft);

            if ($permit == false) {
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide($this->_nameModal, 'Eliminar');
            } else {
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow($this->_nameModal, 'Eliminar');
            }

            $json = array_merge($json, $this->_json);

            $this->_controller->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function getDeleteFinish() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json[] = $this->SwapBytes_Jquery_Ui_Form->close($this->_nameModal);
            $json[] = $this->SwapBytes_Jquery->getJSON('list',
                            array('page' => $this->_request->getParam('page')),
                            array('buscar' => $this->SwapBytes_Jquery->getValEncodedUri('txtBuscar'),
                                'filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros')));

            $this->_controller->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function getRefresh() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json[] = $this->SwapBytes_Jquery->getJSON('list',
                            array('page' => $this->_request->getParam('page', 1)),
                            array('buscar' => $this->SwapBytes_Jquery->getValEncodedUri('txtBuscar'),
                                'filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros')));

            $this->_controller->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    /**
     * Verifica si un formulario aprueba la validación.
     *
     * @return boolean
     */
    public function isValid() {
        $this->_form->populate($this->_data);

        return $this->_form->isValid($this->_data);
    }

    /**
     * Obtiene el HTML basico para el formulario y el dialogo.
     *
     * @return string
     */
    public function getHtml() {
        $html = "<div id='{$this->_nameModal}' title=''></div>";
        $html .= "<div id='{$this->_nameDialog}' title=''></div>";

        return $html;
    }

    /**
     * Genera todo el codigo de jQuery necesario para poner en funcionamiento
     * el formulario y el dialogo según las propiedades definidas.
     */
    public function getJavaScript() {
        //Prueba
        $not = array(1 => 'contenido_html');
        
        $html = "$('#dialog:ui-dialog').dialog('destroy');";

        // Modal:
        $html .= "$('#{$this->_nameModal}').dialog({autoOpen: false,modal: true,resizable: false, buttons: {";
        $html .= "'{$this->_nameModalButonNameSave}': function() {";
        $html .= $this->SwapBytes_Jquery->getJSON('addoreditconfirm',
                        null,
                        array('page' => $this->SwapBytes_Jquery->getVal('page'),
                            'data' => $this->SwapBytes_Jquery->serializeForm($this->_nameModal, $not),
                            'filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros')));
        $html .= ";},'{$this->_nameModalButonNameCancel}': function() { $(this).dialog('close');}, '{$this->_nameModalButonNameOk}': function() { $(this).dialog('close'); },
                '{$this->_nameModalButonNameDelete}': function() {";
        $html .= $this->SwapBytes_Jquery->getJSON('deletefinish',
                        null,
                        array('page' => $this->SwapBytes_Jquery->getVal('page'),
                            //'id' => $this->SwapBytes_Jquery->getVal('id'),
                            'data' => $this->SwapBytes_Jquery->serializeForm($this->_nameModal, $not),
                            'filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros')));
        $html .= ";return false;}},
			open: function () { $(this).dialog('option','height','auto');$(this).dialog('option','width','auto');$(this).dialog('option','position','center');},close: function() {
                form_elements_clear($('#frmModal'));
			}});";

        // Dialogo:
        $html .= "$('#{$this->_nameDialog}').dialog({autoOpen: false,modal: true,open: function(){ $(this).dialog('option','height','auto');$(this).dialog('option','width','auto');$(this).dialog('option','position','center');},buttons: {'Ok': function() { $(this).dialog('close');},'Si': function() {";
        $html .= $this->SwapBytes_Jquery->getJSON('addoreditresponse',
                        null,
                        array('page' => $this->SwapBytes_Jquery->getVal('page'),
                            'data' => $this->SwapBytes_Jquery->serializeForm($this->_nameModal, $not),
                            'filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros')));
        $html .= ";},'No': function() { $(this).dialog('close');}}});";
        
        return $html;
    }

}

?>
