<?php

class Reports_EquivalenciasController extends Zend_Controller_Action {
    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Une_Cde_Reportes_Equivalencias');

        $this->seguridad          = new Models_DbTable_UsuariosGrupos();
        $this->RecordAcademico    = new Models_DbTable_Recordsacademicos();
        $this->certificacionnotas = new Une_Cde_Reportes_Equivalencias();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->Ci = $this->_getParam('ci');
        $this->equivalencia = $this->_getParam('equivalencia');
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if(!$this->seguridad->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

     public function indexAction() {
        $this->view->title      = "Reportes \ Certificación \ Equivalencias";
        $this->view->module     = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
    }

    public function descargarAction() {
        if(ctype_digit($this->Ci)) {
            $Estudiante  = $this->RecordAcademico->getInfoEstudiante($this->Ci);
            $EquivalenciaDefinitivo = $this->RecordAcademico->getEquivalencias($this->Ci, 1266);
            $Traslado = $this->RecordAcademico->getEquivalencias($this->Ci, 1264);
            $Universidades = $this->RecordAcademico->getUniversidadesEquivalencias($this->Ci);
            $this->certificacionnotas->generar($this->Ci, $Estudiante, $EquivalenciaDefinitivo, $Traslado, $Universidades);
        }
    }
}
