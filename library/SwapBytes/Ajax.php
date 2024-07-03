<?php
/**
 * Clase que contiene una serie de metodos para el manejo basico de Ajax.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Ajax {
    public function __construct() {
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->moduleName     = $this->Request->getModuleName();
        $this->controllerName = $this->Request->getControllerName();
    }

    /**
     * Permite definir la vista que se esta utilizando actualmente dentro del
     * controlador para compartirla con la libreria.
     *
     * @param Zend_View_Interface $view
     * @return <type>
     */
    public function setView(Zend_View_Interface $view) {
        $this->view = $view;

        return $this;
    }

    /**
     * Permite desabilitar el Layaut y el Render para no mostrar el HTML que
     * forma la pagina.
     */
    public function setHeader() {
        Zend_Layout::getMvcInstance()->disableLayout();

        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', 'text/plain');
        //Zend_Controller_Front::getInstance()->getResponse()->renderExceptions();
    }

    /**
     * Renderisa el HTML para ser enviado mediante AJAX por JSON, en caso de que
     * se envie el parametro especificado, este es renderizado unicamente,
     * permite eliminar los caracteres especiales utilizados como para definir
     * las nuevas lineas '\n', o agrega los slashes para los TAGS del HTML.
     *
     * @param string $html
     * @return string
     */
    public function render($html) {
        $html = addslashes($html);
//        $html = str_replace("\ ", "\\", $html);
        $html = str_replace("\n", "\\n", $html);
        $html = str_replace('\n','',$html);
        return $html;
    }

    /**
     * Genera la ruta base para ser utilizada en las ejecuciones de tipo AJAX para
     * las funcionalidades de un mismo modulo. Valor que es utilizado en la variable
     * "urlAjax" del JavaScript que implementan diversas clases para el uso de AJAX.
     *
     * @return string
     */
    public function getUrlAjax() {
        return Zend_Controller_Front::getInstance()->getBaseUrl() . '/' . $this->moduleName . '/' . $this->controllerName . '/';
    }

    public function getUrlAjaxJS() {
        return 'urlAjax ="' . Zend_Controller_Front::getInstance()->getBaseUrl() . '/' . $this->moduleName . '/' . $this->controllerName . '/";';
    }

    /**
     * Permite redireccionar la ventana actual a una nueva ruta.
     */
    public function redirect($action, $controller) {
        $url = $this->view->url(array('controller' => $action,
                                      'action'     => $controller), null, true);

        return "window.location = '$url'";
    }

    /**
     * Fuerza a finalizar una petición Ajax.
     */
    public function endResponse() {
        Zend_Controller_Front::getInstance()->getResponse()->sendResponse();
        exit();
    }
}
?>
