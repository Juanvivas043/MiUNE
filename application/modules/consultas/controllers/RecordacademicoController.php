<?php

class Consultas_RecordacademicoController extends Zend_Controller_Action {

    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');

        $this->seguridad = new Models_DbTable_UsuariosGrupos();
        $this->Usuarios = new Models_DbTable_Usuarios();
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
    /*function preDispatch() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if (!$this->seguridad->haveAccessToModule()) {
            $this->_helper->redirector('index', '', 'default');
        }
    }*/

    public function indexAction() {
        $this->view->title = "Consulas \ Record Académico";
        $this->view->module = $this->Request->getModuleName();
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

    private function equivalencias($Rows, $observ, $title) {
        $HTML .= '<tr><td>';
        $HTML .= '<table width="750">';
        $HTML .= "<tr><td colspan='7'><b>$title</b></td></tr>";
        $HTML .= '<tr><td width="80" style="text-align:center"><b>Código</b></td><td style="text-align:center"><b>Asignatura</b></td><td width="50" style="text-align:center"><b>U.C.</b></td><td width="180" style="text-align:center"><b>Observaciones</b></td></tr>';
        foreach ($Rows as $Row) {
            $CodigoOPSU = $Row['codigopropietario'];
            $Materia = $Row['materia'];
            $Uc = $Row['uc'];
            $Observacion = (isset($observ)) ? $observ : '&nbsp;';
            $Tuc += $Uc;

            $HTML .= '<tr>';
            $HTML .= '<td style="text-align:center">' . $CodigoOPSU . '</td>';
            $HTML .= '<td style="text-align:left">' . $Materia . '</td>';
            $HTML .= '<td style="text-align:center">' . $Uc . '</td>';
            $HTML .= '<td style="text-align:center">' . $Observacion . '</td>';
            $HTML .= '</tr>';
        }
        $HTML .= '<tr><td colspan="2"><b>Total Unidades de credito:</b></td><td style="text-align:center">' . $Tuc . '</td><td>&nbsp;</td></tr>';
        $HTML .= '</table>';
        $HTML .= '</td></tr>';
        return $HTML;
    }

    public function generarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $Ci = $this->_getParam('ci');
            if (empty($Ci))
                return;

            $sPensum  = $this->_getParam('selPensum');
            $PensumNombre  = $this->_getParam('nombre');

            $cantesc = $this->RecordAcademico->getCantidadEscuela($Ci);
            $Escuelas = $this->RecordAcademico->getEscuelasEstudiante($Ci);
            if($sEscuela == 'null')
				$sEscuela = $esc[0]['fk_atributo'];
            if ($cantesc > 1) {
                $sEscuela = $this->_getParam('selEscuela');
            } elseif ($cantesc == 1) {
                $sEscuela = $this->RecordAcademico->getIDEscuela($Ci);
            } else {
                $HTML = "<table><tr><td style='text-align:right'><b>Estudiante no posee Record Academico</b></td>";
                $HTML .= '</tr></table>';
                $HTML = addslashes($HTML);
                // $json[] = $this->SwapBytes_Jquery->setAttr('selEscuela', 'disabled', 'true');
                //$json[] = $this->SwapBytes_Jquery->setHtml('tblMsg', $HTML);
                $json[] = $this->SwapBytes_Jquery->setHtml('tblLista', "");
                $json[] = $this->SwapBytes_Jquery->setHtml('selEscuela    ', "");

                $this->getResponse()->setBody(Zend_Json::encode($json));


                return;
            }

            $opt = "";
            $opt_pensum = "";
            if ($cantesc >= 1) {
                foreach ($Escuelas as $esc) {
                    $opt .= "<option value='{$esc['fk_atributo']}'>{$esc['escuela']}</option>";
                    if ($sEscuela == $esc['fk_atributo']) {
                        $swt = 1;
                    }
                };
                $opt = addslashes($opt);

                if ($cantesc > 1 && $swt != 1) {
                    $json[] = $this->SwapBytes_Jquery->setAttr('selEscuela', 'disabled', 'false');
                    $json[] = $this->SwapBytes_Jquery->setHtml('selEscuela', $opt);
                    //$json[] = $this->SwapBytes_Jquery->setHtml('tblMsg', $MSG);
                    $json[] = $this->SwapBytes_Jquery->setHtml('tblLista', "");
                    $this->getResponse()->setBody(Zend_Json::encode($json));
                    $swt = 0;
                    $swt_pen = 0;
                    return;
                } else {
                    $swt = 0;
                    $swt_pen = 0;
                }
            }

			if($sPensum == 'null'){
				$Pensums = $this->RecordAcademico->getEstudiantePensums($Ci, $Escuela);
				$sPensum = $Pensums[0]['codigopropietario'];
			}
            $json = array();
            $Escuela = $this->RecordAcademico->getNombreEscuela($Ci, $sEscuela);
            //var_dump($Escuela);
            $Rows = $this->RecordAcademico->getCompletoEscuela2($Ci, $sEscuela, $sPensum);
            //$IAA = $this->RecordAcademico->getIAAEscuelaPensum($Ci, $sEscuela, $sPensum);
            $Periodo = end($Rows);
				if(empty($Periodo)){
					$Periodo = 124;
                $IAA = $this->RecordAcademico->getIAAEscuelaPensumArticulado($Ci, $sEscuela, $Periodo, $sPensum);
                }
                else {
                $IAA = $this->RecordAcademico->getIAAEscuelaPensumArticulado($Ci, $sEscuela, $Periodo['periodo'], $sPensum);
                }
            $UCA = $this->RecordAcademico->getUCACEscuelaPensum($Ci, $sEscuela, $sPensum);
            $EquiUCA = $this->RecordAcademico->getUCACEscuelaEquivPensum($Ci, $sEscuela, $sPensum);
            $Usuario = $this->Usuarios->getRow($Ci);
            $EquivalenciaDefinitivo = $this->RecordAcademico->getEquivalencias($Ci, 1266, $sPensum,$sEscuela);
            $EquivalenciaTransitorio = $this->RecordAcademico->getEquivalencias($Ci, 1265, $sPensum,$sEscuela);
            $Traslado = $this->RecordAcademico->getEquivalencias($Ci, 1264, $sPensum,$sEscuela);
            $Periodo = null;

            if (isset($Rows)) {
                $HTML = '<table>';

                // Encabezado del reporte.
                $HTML .= '<tr><td>';
                $HTML .= '<table>';
                $HTML .= '<tr><td style="text-align:right"><b>Estudiante:</b></td><td>' . $Usuario['apellido'] . ',' . $Usuario['nombre'] . '</td></tr>';
                $HTML .= '<tr><td style="text-align:right"><b>C.I.:</b></td><td>' . $Usuario['pk_usuario'] . '</td></tr>';
                $HTML .= '<tr><td style="text-align:right"><b>Escuela:</b></td><td>' . $Escuela . '</td></tr>';
                $HTML .= '<tr><td style="text-align:right"><b>Pensum:</b></td><td>' . $PensumNombre . '</td></tr>';
                $HTML .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
                $HTML .= '<tr><td style="text-align:right"><b>Indice acad&eacute;mico acumulado:</b></td><td>' . $IAA . '</td></tr>';
                $HTML .= '<tr><td style="text-align:right"><b>Total de unidades de credito aprobadas UNE:</b></td><td>' . $UCA . '</td></tr>';
                $HTML .= '<tr><td style="text-align:right"><b>Total de unidades de credito aprobadas Otras Univ. :</b></td><td>' . $EquiUCA . '</td></tr>';
                $HTML .= '<tr><td style="text-align:right"><b>Total de unidades de credito aprobadas:</b></td><td>' . ($EquiUCA + $UCA) . '</td></tr>';
                $HTML .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
                $HTML .= '</table>';
                $HTML .= '</td></tr>';
                // Equivalencias y Traslados
                if (!empty($EquivalenciaDefinitivo)) {
                    $observacion = 'Reconocida por Equivalencia';
                    $HTML .= $this->equivalencias($EquivalenciaDefinitivo, $observacion, 'Equivalencias');
                }
                if (!empty($EquivalenciaTransitorio)) {
                    $observacion = 'Equivalencia Transitoria';
                    $HTML .= $this->equivalencias($EquivalenciaTransitorio, $observacion, 'Equivalencias Transitoria');
                }
                if (!empty($Traslado)) {
                    $observacion = 'Reconocida Academicamente';
                    $HTML .= $this->equivalencias($Traslado, $observacion, 'Traslados');
                }
                //$HTML .= $this->equivalencias($Traslado, "Reconocida Academicamente", "Traslados");
                // Record Academico del Estudiante.
                foreach ($Rows as $Row) {
                    if ($Periodo != $Row['periodo']) {
                        $Periodo = $Row['periodo'];

                        $PeriodoDuracion = $this->Periodos->getDuracion($Row['periodo']);
                        $PeriodoDuracion = "{$Row['periodo']} ({$Row['inicio']} / {$Row['fin']})";

                        $HTML .= '<tr><td>';
                        $HTML .= '<table width="750">';
                        $HTML .= '<tr><td colspan="7"><b>Per&iacute;odo Lectivo:</b> ' . $PeriodoDuracion . '</td></tr>';
                        $HTML .= '<tr><td width="80" style="text-align:center"><b>Código</b></td><td style="text-align:center"><b>Asignatura</b></td><td width="50" style="text-align:center"><b>U.C.</b></td><td width="120" style="text-align:center"><b>Calificación</b></td><td width="100" style="text-align:center"><b>Observaciones</b></td></tr>';
                    }

                    if ($Periodo == $Row['periodo'] && empty($Row['promedio'])) {
                        
                        if ($Row['materia'] <> 'P.I.R.A.' && $Row['materia'] <> 'TALLER DE SERVICIO COMUNITARIO' ){
                        $CodigoOPSU = $Row['codigo2'];
                        $Materia = $Row['materia'];
                        $Uc = $Row['uc'];
                        $Nota = $Row['nota'] . ' ' . $Row['notatxt'];
                        $Observacion = (isset($Row['observacion'])) ? $Row['observacion'] : '&nbsp;';

                        $HTML .= '<tr>';
                        $HTML .= '<td style="text-align:center">' . $CodigoOPSU . '</td>';
                        $HTML .= '<td style="text-align:left">' . $Materia . '</td>';
                        $HTML .= '<td style="text-align:center">' . $Uc . '</td>';
                        $HTML .= '<td style="text-align:left">' . $Nota . '</td>';
                        $HTML .= '<td style="text-align:center">' . $Observacion . '</td>';
                        $HTML .= '</tr>';
                         } else {
                        $CodigoUNE   = '';
                        $Materia     = '';
                        $Uc          = '';
                        $Nota        = '';
                        $Observacion = '';
                        }
                    }

  if($Periodo == $Row['periodo'] && empty($Row['promedio']) && ($Row['materia'] == 'P.I.R.A.' || $Row['materia'] == 'TALLER DE SERVICIO COMUNITARIO')) {
                 
                $Materia     = $Row['materia'];
                $Observacion = '&nbsp;';   
            if($Row['nota'] >= 10){
                    $Observacion = 'Cursado Aprobado';
                } else if ((($Row['nota'] < 10) && ($Row['nota'] > 0))){
                    $Observacion = 'Cursado Reprobado';
                } else if (($Row['nota'] === 0)){
                    $Observacion = 'Inscrita y No Cursó';
                }else{

                    $Observacion = $Row['nota'];
                }

            $HTML .= '<tr>'; 
            $HTML .= '<td class="SingleText" style="text-align:center"></td>';
            $HTML .= '<td class="SingleText" style="text-align:left">'   . $Materia     . '</td>';
            $HTML .= '<td class="SingleText" style="text-align:center"></td>';
            $HTML .= '<td class="SingleText" style="text-align:center" >' . $Observacion . '</td>';
            $HTML .= '</tr>';  
     
        }
                    
                    
                    if ($Periodo == $Row['periodo'] && isset($Row['promedio'])) {
                        $HTML .= '<tr><td colspan="7"><b>Indice acad&eacute;mico del periodo:</b>' . $Row['promedio'] . '</td></tr>';
                        $HTML .= '<tr><td colspan="7"><b>Indice acad&eacute;mico acumulado al periodo:</b>' . $this->RecordAcademico->getIndiceAcumuladoPeriodoPensum($Ci, $sEscuela, $Row['periodo'], $sPensum) . '</td></tr>';
                        $HTML .= '<tr><td colspan="7"><b>Unidades de credito aprobadas:</b>' . $Row['ucaprobadas'] . '</td></tr>';
                        $HTML .= '<tr><td colspan="7"><b>Unidades de credito computadas:</b>' . $Row['uccomputadas'] . '</td></tr>';
                        $HTML .= '<tr><td colspan="7">&nbsp</td></tr>';
                        $HTML .= '</table>';
                        $HTML .= '</td></tr>';
                    }
                }

                $HTML .= '</table>';

                $HTML = addslashes($HTML);
            }
            if ($cantesc == 1) {
                $json[] = $this->SwapBytes_Jquery->setHtml('selEscuela', $opt);
                // $json[] = $this->SwapBytes_Jquery->setAttr('selEscuela', 'disabled', 'true');
            }
            //$json[] = $this->SwapBytes_Jquery->setHtml('tblMsg', $MSG);
            $json[] = $this->SwapBytes_Jquery->setHtml('tblLista', $HTML);

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

}
