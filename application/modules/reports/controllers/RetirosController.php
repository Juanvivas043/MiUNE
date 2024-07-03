<?php

class Reports_RetirosController extends Zend_Controller_Action {
    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');

        $this->Usuarios        = new Models_DbTable_Usuarios();
        $this->Periodos        = new Models_DbTable_Periodos();
        $this->RecordAcademico = new Models_DbTable_Recordsacademicos();

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
    }

    public function indexAction() {
        $this->view->title      = "Reportes \ Retiros";
        $this->view->module     = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
    }

    public function generarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json    = array();
            $Ci      = $this->_getParam('ci');
            $Escuela = $this->RecordAcademico->getEscuela($Ci);
            $Rows    = $this->RecordAcademico->getAlumno($Ci,863);
            $IAA     = $this->RecordAcademico->getIAA($Ci);
            $UCA     = $this->RecordAcademico->getUCA($Ci, 12);
            $Usuario = $this->Usuarios->getRow($Ci);
            $Periodo = null;

            if(isset($Rows)) {
                $HTML  = '<table>';

                // Encabezado del reporte.
                $HTML .= '<tr><td>';
                $HTML .= '<table>';
                $HTML .= '<tr><td style="text-align:right"><b>Estudiante:</b></td><td>' . $Usuario['apellido'] . ',' . $Usuario['nombre'] . '</td></tr>';
                $HTML .= '<tr><td style="text-align:right"><b>C.I.:</b></td><td>' . $Usuario['pk_usuario'] . '</td></tr>';
                $HTML .= '<tr><td style="text-align:right"><b>Escuela:</b></td><td>' . $Escuela . '</td></tr>';
                $HTML .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
                //$HTML .= '<tr><td style="text-align:right"><b>Indice acad&eacute;mico acumulado:</b></td><td>' . $IAA . '</td></tr>';
                //$HTML .= '<tr><td style="text-align:right"><b>Total de unidades de credito aprobadas:</b></td><td>' . $UCA . '</td></tr>';
                $HTML .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
                $HTML .= '</table>';
                $HTML .= '</td></tr>';
                
                // Record Academico del Estudiante.
                foreach($Rows as $Row) {
                    if($Periodo != $Row['periodo']) {
                        $Periodo = $Row['periodo'];

                        $PeriodoDuracion = $this->Periodos->getDuracion($Row['periodo']);
                        $PeriodoDuracion = "{$Row['periodo']} ({$PeriodoDuracion['fechainicio']} / {$PeriodoDuracion['fechafin']})";
                        
                        $HTML .= '<tr><td>';
                        $HTML .= '<table width="750">';
                        $HTML .= '<tr><td colspan="7"><b>Per&iacute;odo Acad&eacute;mico:</b> ' . $PeriodoDuracion . '</td></tr>';
                        $HTML .= '<tr><td width="80" style="text-align:center"><b>Código UNE</b></td><td width="80" style="text-align:center"><b>Código OPSU</b></td><td style="text-align:center"><b>Asignatura</b></td><td width="50" style="text-align:center"><b>U.C.</b></td><td width="100" style="text-align:center"><b>Observaciones</b></td></tr>';
                    }

                    if($Periodo == $Row['periodo'] && empty($Row['promedio'])) {
                        $CodigoUNE   = $Row['codigo1'];
                        $CodigoOPSU  = $Row['codigo2'];
                        $Materia     = $Row['materia'];
                        $Uc          = $Row['uc'];
                       // $Nota        = $Row['nota'] . ' ' . $Row['notatxt'];
                        $Observacion = 'Retirada';

                        $HTML .= '<tr>';
                        $HTML .= '<td style="text-align:center">' . $CodigoUNE   . '</td>';
                        $HTML .= '<td style="text-align:center">' . $CodigoOPSU  . '</td>';
                        $HTML .= '<td style="text-align:center">'   . $Materia     . '</td>';
                        $HTML .= '<td style="text-align:center">' . $Uc          . '</td>';
                        //$HTML .= '<td style="text-align:left">'   . $Nota        . '</td>';
                        $HTML .= '<td style="text-align:center">' . $Observacion . '</td>';
                        $HTML .= '</tr>';
                        $HTML .= '<tr>';
                        $HTML .= '<td>&nbsp</td>';
                        $HTML .= '</tr>';

                    }

                    if($Periodo == $Row['periodo'] && isset($Row['promedio'])) {
                        //$HTML .= '<tr><td colspan="7"><b>Indice acad&eacute;mico del periodo:</b>' . $Row['promedio']     . '</td></tr>';
                        //$HTML .= '<tr><td colspan="7"><b>Unidades de credito aprobadas:</b>'       . $Row['ucaprobadas']  . '</td></tr>';
                        //$HTML .= '<tr><td colspan="7"><b>Unidades de credito computadas:</b>'      . $Row['uccomputadas'] . '</td></tr>';
                        //$HTML .= '<tr><td colspan="7">&nbsp</td></tr>';
                        $HTML .= '</table>';
                        $HTML .= '</td></tr>';
                    }
                }
                
                $HTML .= '</table>';

                $HTML  = addslashes($HTML);
            }

            $json[] = $this->SwapBytes_Jquery->setHtml('tblRecordAcademico', $HTML);

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
}
