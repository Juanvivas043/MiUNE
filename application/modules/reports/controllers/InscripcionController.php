<?php

class Reports_InscripcionController extends Zend_Controller_Action {

    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');

        $this->Usuarios = new Models_DbTable_Usuarios();
        $this->seguridad = new Models_DbTable_UsuariosGrupos();
        $this->Periodos = new Models_DbTable_Periodos();
        $this->RecordAcademico = new Models_DbTable_Recordsacademicos();

        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    function preDispatch() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if (!$this->seguridad->haveAccessToModule()) {
           $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

    public function indexAction() {
        $this->view->title = "Reportes \ Inscripción";
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
    }

    public function generarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json = array();
            $Ci = $this->_getParam('ci');
            if (ctype_digit($Ci)) {
                $Escuela = $this->RecordAcademico->getEscuela($Ci);
                $Rows = $this->RecordAcademico->getAlumno($Ci);
                $IAA = $this->RecordAcademico->getIAA($Ci);
                $UCA = $this->RecordAcademico->getUCA($Ci, 12);
                $Usuario = $this->Usuarios->getRow($Ci);
                $Periodo = null;

                if (isset($Rows)) {
                    $HTML = '<table>';

                    // Encabezado del reporte.
                    $HTML .= '<tr><td>';
                    $HTML .= '<table>';
                    $HTML .= '<tr><td style="text-align:right"><b>Estudiante:</b></td><td>' . $Usuario['apellido'] . ',' . $Usuario['nombre'] . '</td></tr>';
                    $HTML .= '<tr><td style="text-align:right"><b>C.I.:</b></td><td>' . $Usuario['pk_usuario'] . '</td></tr>';
                    $HTML .= '<tr><td style="text-align:right"><b>Escuela:</b></td><td>' . $Escuela . '</td></tr>';
                    $HTML .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
                    $HTML .= '<tr><td style="text-align:right"><b>Indice acad&eacute;mico acumulado:</b></td><td>' . $IAA . '</td></tr>';
                    $HTML .= '<tr><td style="text-align:right"><b>Total de unidades de credito aprobadas:</b></td><td>' . $UCA . '</td></tr>';
                    $HTML .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
                    $HTML .= '</table>';
                    $HTML .= '</tr></td>';
                    $HTML .= '</table>';

                    $HTML = addslashes($HTML);
                }

                $json[] = $this->SwapBytes_Jquery->setHtml('tblInscripcion', $HTML);

                $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }
    }

}
