<?php

class Reports_CertificacionCalificacionController extends Zend_Controller_Action {
    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Une_Cde_Reportes_CertificacionNotas');

        $this->seguridad          = new Models_DbTable_UsuariosGrupos();
        $this->RecordAcademico    = new Models_DbTable_Recordsacademicos();
        $this->certificacionnotas = new Une_Cde_Reportes_CertificacionNotas();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->Ci = $this->_getParam('ci');
        $this->Servicio =$this->_getParam('servicio');
        $this->Escuela = $this->_getParam('selEscuela');
        $this->Pensum = $this->_getParam('selPensum');
        
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
        $this->view->title      = "Reportes \ Certificación \ Calificaciones";
        $this->view->module     = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
    }

    public function pensumAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
              $Ci = $this->_getParam('ci');
              $Escuela = $this->_getParam('selEscuela');
              $Pensums = $this->RecordAcademico->getEstudiantePensums($Ci, $Escuela);
             
              $opt = "";
              foreach ($Pensums as $pen) {
                 $opt .= "<option value='{$pen['codigopropietario']}'>{$pen['nombre']}</option>";
              };

              $opt = addslashes($opt);

              $json[] = $this->SwapBytes_Jquery->setAttr('selPensum', 'disabled', 'false');
              $json[] = $this->SwapBytes_Jquery->setHtml('selPensum', $opt);
              $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerate', 'disabled', 'true');

              $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
    

    public function descargarAction() {
        $cantesc    = $this->RecordAcademico->getCantidadEscuela($Ci);
        $Periodos    = $this->RecordAcademico->getPeriodosCursados($this->Ci);
        //$Estudiante  = $this->RecordAcademico->getInfoEstudiante($this->Ci);
        $Estudiante  = $this->RecordAcademico->getInfoEstudianteEscuela($this->Ci, $this->Escuela);
        //$Asignaturas = $this->RecordAcademico->getCompleto($this->Ci);
        $Asignaturas = $this->RecordAcademico->getCompletoEscuela2($this->Ci, $this->Escuela, $this->Pensum);
        //$iIndiceAcum = $this->RecordAcademico->getIndiceAcumulado($this->Ci);
        // $iIndiceAcum = $this->RecordAcademico->getIAAEscuela($this->Ci, $this->Escuela);
        $iIndiceAcum = $this->RecordAcademico->getIAAEscuelaPensum($this->Ci, $this->Escuela, $this->Pensum);
   	//$iTUCA       = $this->RecordAcademico->getTotalUCAprobadas($this->Ci, $Estudiante[0]['pk_escuela']);
        // $iTUCA       = $this->RecordAcademico->getTotalUCAprobadas($this->Ci, $this->Escuela);
        $iTUCA       = $this->RecordAcademico->getUCACEscuelaPensum($this->Ci, $this->Escuela, $this->Pensum);
        $Estudiante  = $this->RecordAcademico->getInfoEstudiante($this->Ci, $this->Escuela);
        $EquivalenciaDefinitivo = $this->RecordAcademico->getEquivalencias($this->Ci, 1266, $this->Pensum);
        $Traslado = $this->RecordAcademico->getEquivalencias($this->Ci, 1264, $this->Pensum,$this->Escuela);
        $Universidades = $this->RecordAcademico->getUniversidadesEquivalencias($this->Ci);
        if ($this->Pensum == '8'){ 
          $nuevoPensum = true;
        }else{
          $nuevoPensum = false;
        }
        $this->certificacionnotas->generar($this->Ci, $Asignaturas, $Estudiante, $iIndiceAcum, $iTUCA, $EquivalenciaDefinitivo, $Traslado, $Universidades, $this->Servicio);
    }

    public function verificarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json    = array();
            $Ci   = $this->_getParam('ci');

            if(!ctype_digit($Ci)) return;

            $cantesc    = $this->RecordAcademico->getCantidadEscuela($Ci);
            $Escuelas = $this->RecordAcademico->getEscuelasEstudiante($Ci);
            if($cantesc > 1){
              $sEscuela  = $this->_getParam('selEscuela');
            }elseif($cantesc == 1){
              $sEscuela  = $this->RecordAcademico->getIDEscuela($Ci);
            }else{
              $HTML  = "<table><tr><td style='text-align:right'><b>Estudiante no posee Record Academico</b></td>";
              $HTML .= '</tr></table>';
              $HTML  = addslashes($HTML);
              $json[] = $this->SwapBytes_Jquery->setAttr('selEscuela','disabled','true');
              $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerar','disabled','true');
              $json[] = $this->SwapBytes_Jquery->setHtml('tblMsg', $HTML);
              $json[] = $this->SwapBytes_Jquery->setHtml('selEscuela    ', "");

              $this->getResponse()->setBody(Zend_Json::encode($json));
              return;
            }
            if($cantesc >= 1){
                if($cantesc>1){
                
                $json[] = $this->SwapBytes_Jquery->setAttr('selEscuela','disabled','false');
                $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerar','disabled','false');
                }else{
                $json[] = $this->SwapBytes_Jquery->setAttr('selEscuela','disabled','true');
                $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerar','disabled','false');
                }
                foreach ($Escuelas as $esc){
                    $opt .= "<option value='{$esc['fk_atributo']}'>{$esc['escuela']}</option>";
                };
                $opt  = addslashes($opt);
                $json[] = $this->SwapBytes_Jquery->setHtml('tblMsg', '');
                $json[] = $this->SwapBytes_Jquery->setHtml('selEscuela', $opt);


                $Pensums = $this->RecordAcademico->getEstudiantePensums($Ci, $Escuelas[0]['fk_atributo']);
                $opt = "";
                foreach ($Pensums as $pen) {
                    $opt .= "<option value='{$pen['codigopropietario']}'>{$pen['nombre']}</option>";
                };

                $opt = addslashes($opt);

                $json[] = $this->SwapBytes_Jquery->setAttr('selPensum', 'disabled', 'false');
                $json[] = $this->SwapBytes_Jquery->setHtml('selPensum', $opt);
                
                $this->getResponse()->setBody(Zend_Json::encode($json));

        }
        }
    }
    
   
}
