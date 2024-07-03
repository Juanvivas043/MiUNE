<?php

class CmcBytes_Redirect {

     public function __construct() {

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->moduleName     = $this->Request->getModuleName();
        $this->controllerName = $this->Request->getControllerName();

        $this->redirect_session = new Zend_Session_Namespace('redirect_session');
        //$this->logger = Zend_Registry::get('logger');

    }

    public function getUrl() {
        //return Zend_Controller_Front::getInstance()->getBaseUrl() . '/' . $this->moduleName . '/' . $this->controllerName . '/';
        return Zend_Controller_Front::getInstance()->getBaseUrl();

    }

    public function getRedirect($data){
        $this->redirect_session->unsetAll();
        SWITCH ($data['params']['action']){
            CASE 'agregar':
                $data['params']['action'] = 'btnAdd';
                break;
            CASE 'listar':
                $data['params']['action'] = 'btnList';
                break;
        }

        $this->redirect_session->params = $data['params'];
        $js = "window.location.href='{$this->getUrl()}/{$data['module']}/{$data['controller']}';";


        return $js;
    }

    public function triggerButton($id){

        $js = "$('#{$id}').trigger('click');";
//$js = "$('#{$id}').attr('disabled','disabled')";
        return $js;
        

    }




}
?>
