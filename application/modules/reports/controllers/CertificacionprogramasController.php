<?php

class Reports_CertificacionProgramasController extends Zend_Controller_Action {
    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Une_Cde_Reportes_CertificacionProgramas');

        $this->seguridad          = new Models_DbTable_UsuariosGrupos();
        $this->RecordAcademico    = new Models_DbTable_Recordsacademicos();
        $this->certificacionprogramas = new Une_Cde_Reportes_CertificacionProgramas();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->Ci = $this->_getParam('ci');
        $this->Escuela = $this->_getParam('selEscuela');
        
        $this->SwapBytes_Ajax   = new SwapBytes_Ajax();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
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
        $this->view->title      = "Reportes \ Certificación \ Programas";
        $this->view->module     = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
     }
     
    
     public function verificarAction(){
         $this->_helper->viewRenderer->setNoRender(true);
         
         if ($this->_request->isXmlHttpRequest()) {
             $this->SwapBytes_Ajax->setHeader(); 
             $json    = array();
             $Ci   = $this->_getParam('ci');
             if(empty($Ci)) return;
             
             $cantesc = $this->RecordAcademico->getCantidadEscuela($Ci);
             $Escuelas = $this->RecordAcademico->getEscuelasEStudiante($Ci);
             
             if($cantesc >= 1){
                 if($cantesc>1){
                    $json[] = $this->SwapBytes_Jquery->setAttr('selEscuela','disabled','false');
                    $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerar','disabled','false');
                }else{
                    $json[] = $this->SwapBytes_Jquery->setAttr('selEscuela','disabled','true');
                    $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerar','disabled','false');
                }
                
                foreach($Escuelas as $esc){
                    $opt .= "<option value ={$esc['fk_atributo']}>{$esc['escuela']}</option>";
                }
                
                $HTML  = "<table><tr><td style='text-align:right'><b></b></td>";
                $HTML .= '</tr></table>';
                $HTML  = addslashes($HTML);
                $json[] = $this->SwapBytes_Jquery->setHtml('tblMsg',$HTML);
                $json[] = $this->SwapBytes_Jquery->setHtml('selEscuela', $opt);
             }else{
                $HTML  = "<table><tr><td style='text-align:right'><b>cedula no encontrada</b></td>";
                $HTML .= '</tr></table>';
                $HTML  = addslashes($HTML);
                $json[] = $this->SwapBytes_Jquery->setAttr('selEscuela','disabled','true');
                $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerar','disabled','true');
                $json[] = $this->SwapBytes_Jquery->setHtml('tblMsg',$HTML);
                $json[] = $this->SwapBytes_Jquery->setHtml('selEscuela    ', "");
             }
             
             $this->getResponse()->setBody(Zend_Json::encode($json));
             
             
         }
     }

    public function descargarAction() {
        if(ctype_digit($this->Ci) && ctype_digit($this->Escuela)) {
            $Estudiante = $this->RecordAcademico->getInfoEstudiante($this->Ci,$this->Escuela);
            $this->certificacionprogramas->generar($this->Ci, $Estudiante);
            
            
        }
    }
} 
