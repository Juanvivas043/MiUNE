<?php
/**
 * Clase que contiene una serie de metodos para el manejo basico de Ajax para los
 * objetos del formulario HTML.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Ajax_Action {
    /**
     * Constructor de la clase.
     */
    public function __construct() {
        // Clases del Framework.
        $this->controller = Zend_Controller_Front::getInstance();
        $this->request    = new Zend_Controller_Request_Http();

        // Clases propias.
        $this->SwapBytes_Ajax      = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html = new SwapBytes_Ajax_Html();
    }
    
    /**
     * Acción predefinida para poder llenar mediante un arreglo a un objeto HTML
     * de tipo select.
     */
    public function fillSelect($dataRows, $label = null) {
        if ($this->request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $body = $this->SwapBytes_Ajax_Html->fillSelect($dataRows, $label);
            $this->controller->getResponse()->setBody($body);
        }
    }
}
?>
