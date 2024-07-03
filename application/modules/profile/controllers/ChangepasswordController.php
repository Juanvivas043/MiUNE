<?php

class Profile_ChangepasswordController extends Zend_Controller_Action {
    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Forms_Password');

        $this->auth    = Zend_Auth::getInstance();
        $this->usuario = new Models_DbTable_Usuarios();
        
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    /*function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
    }*/

    public function getErrorsMessages() {
        return $this->errorsMessages;
    }

    public function indexAction() {
        $this->view->title = "Cambiar Contraseña";
        $this->view->form  = new Forms_Password();
       

        $Messages  = null;

        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($_POST)) {
                $id  = $this->auth->getIdentity();
                $Row = $this->usuario->getRow($id->pk_usuario);
                
                //Defino esta variable para que no de error
                $Old = null;
                $Pwd  = $this->_getParam('password');
                $New  = $this->_getParam('passwordNew');
                $Rep  = $this->_getParam('passwordRep');
                $OldH = md5($Old);
                $PwdH = md5($Pwd);
                $RepH = md5($Rep);
                $NewH = md5($New);

                if($Row['passwordhash'] <> $PwdH) {
                    $Messages['Errors']['PasswordOld'] = true;
                }

                if($Row['passwordhash'] == $RepH) {
                    $Messages['Errors']['PasswordSo'] = true;
                }

                if($NewH <> $RepH) {
                    $Messages['Errors']['PasswordRep'] = true;
                }

                if($Row['passwordhash'] == $PwdH && $NewH == $RepH && $Row['passwordhash'] <> $RepH) {
                    
                    $Rows = $this->usuario->changePassword($Row['pk_usuario'], $Rep);

                    if($Rows == 1) {
                        $Messages['Alert']['ChangePassword'] = true;
                    }
                }
            }
        }

        $this->view->message = $Messages;
        
    }
}
