<?php

class IndexController extends Zend_Controller_Action {
    public function init() {
        /* Initialize action controller here */
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
    }

    public function indexAction() {
        // action body
    }
}
