<?php

class ErrorController extends Zend_Controller_Action {

    public function errorAction() {
        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- Controlador o Acción no funciona.
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                break;
            default:
                // Error generico de la aplicación.
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }

        if ($this->_request->isXmlHttpRequest()) {
            Zend_Layout::getMvcInstance()->disableLayout();

            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', 'text/plain');

            echo $errors->exception;
            echo $errors->request;
        } else {
            $this->view->exception = $errors->exception;
            $this->view->request   = $errors->request;
        }

        $logger = Zend_Registry::get('logger');
        $logger->log($errors->exception, Zend_Log::ERR);
//        $loggerFB = Zend_Registry::get('loggerFB');
//        $loggerFB->log($errors->exception, Zend_Log::ERR);
    }
}
