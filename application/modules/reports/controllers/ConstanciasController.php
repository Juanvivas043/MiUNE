<?php

class Reports_ConstanciasController extends Zend_Controller_Action {
    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Une_Cde_Reportes_ConstanciaDeEstudios');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Une_Cde_Reportes_ConstanciaActuacionEstudiantil');
        Zend_Loader::loadClass('Une_Cde_Reportes_ConstanciaSoloFaltaTesis');
        Zend_Loader::loadClass('Une_Cde_Reportes_ConstanciaTramitacionDeTitulo');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        $this->usuario         = new Models_DbTable_Usuarios();
        $this->seguridad       = new Models_DbTable_UsuariosGrupos();
        $this->inscripciones        = new Models_DbTable_Inscripciones();
        $this->RecordAcademico      = new Models_DbTable_Recordsacademicos();
        $this->constanciadeestudios = new Une_Cde_Reportes_ConstanciaDeEstudios();
        $this->constanciatramitaciondetitulo = new Une_Cde_Reportes_ConstanciaTramitacionDeTitulo();
        $this->constanciaactuacionestudiantil = new Une_Cde_Reportes_ConstanciaActuacionEstudiantil();
        $this->constanciasolofaltatesis = new Une_Cde_Reportes_ConstanciaSoloFaltaTesis();
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
        $this->view->title      = "Reportes \ Constancias";
        $this->view->module     = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
    }

    public function descargarAction() {
        $constancia = $this->_getParam('selConstancia');
        if (!empty($constancia)){
			if (ctype_digit($this->Ci)) {
				$Estudiante = $this->RecordAcademico->getInfoEstudianteEscuela($this->Ci, $this->Escuela);
				//var_dump($Estudiante);die;
				$nuevoPensum = $this->RecordAcademico->IsPensumNuevo($this->Ci);
				if ($nuevoPensum) {
					$Estudiante[0]['semestre'] = str_replace("SEMESTRE","PERÍODO ACADÉMICO",$Estudiante[0]['semestre']);
				}
				switch ($constancia) {
				case 1:
					$this->constanciadeestudios->generar($this->Ci, $Estudiante,$nuevoPensum);
					break;
				case 2:
					$this->constanciaactuacionestudiantil->generar($this->Ci, $Estudiante);
					break;
				case 3:
					$this->constanciasolofaltatesis->generar($this->Ci, $Estudiante);
					break;
				case 4:
					$this->constanciatramitaciondetitulo->generar($this->Ci, $Estudiante);
					break;
				}
			}
        }
    }

    public function buscarAction() {
        $this->SwapBytes_Ajax->setHeader();
        $ci      = $this->_getParam('ci');
        $escuela = $this->_getParam('selEscuela');
        $pensum = $this->RecordAcademico->getPeriodoEscuelaPensum($ci);
        $EquiUCA = $this->RecordAcademico->getUCACEscuelaEquivPensum($ci, $escuela,$pensum[0]['pensum']);
        $status  = $this->RecordAcademico->getEstudianteStatus($ci, $escuela);
        $UCAtotalEsc = $this->RecordAcademico->UCAtotalEscuelaPensum($escuela, $pensum[0]['pensum']);
        //var_dump($UCAtotalEsc);die;
        $HTML    = "<table>";
        $HTML   .= "<tr><td>Estudiante: </td><td>{$status[0]['apellido']} {$status[0]['nombre']}</td></tr>";
        $HTML   .= "<tr><td>Ultimo Periodo Inscrito: </td><td>{$status[0]['periodo']}</td>";
        $HTML   .= "<tr><td>Unidades de Credito Aprobadas UNE: </td><td>{$status[0]['uca']}</td></tr>";
        $HTML   .= "<tr><td>Unidades de Credito Aprobadas Otras Univ.: </td><td>{$EquiUCA}</td></tr>";
        $totalUCA = $status[0]['uca'] + $EquiUCA;
        $HTML   .= "<tr><td>Unidades de Credito Aprobadas: </td><td>{$totalUCA}</td></tr>";
        $HTML   .= "<tr><td>Indice Academico Acumulado: </td><td>{$status[0]['iia']}</td></tr>";
        $HTML   .= "</tr></table>";
        $json[] = $this->SwapBytes_Jquery->setAttr('selConstancia','disabled','false');
        if ($status[0]['periodo']==$status[0]['uperiodo']) {
			$opciones .= "<option value='1'>Estudio</option>";
        }
        $opciones .= "<option value='2'>Actuacion Estudiantil</option>";
       if ($totalUCA == 169) {
                $opciones .= "<option value='3'>Solo Falta Tesis</option>";
         }
        switch ($pensum[0]['pensum']) {
            case 8://pensum 2012
                if($totalUCA == ($UCAtotalEsc - 5)){ // Creditos de Trabajo de grado II
                            $opciones .= "<option value='3'>Solo Falta Tesis</option>";
                        }
		if($totalUCA >= $UCAtotalEsc) {
                    $opciones .= "<option value='4'>Tramitacion de Titulo</option>";        
                }
            break;
	    case 7: //pensum 1997
		if($totalUCA == ($UCAtotalEsc - 5)){ // Creditos de Trabajo de grado II
                            $opciones .= "<option value='3'>Solo Falta Tesis</option>";
                        }    
		if($totalUCA >= 175){
		    $opciones .= "<option value='4'>Tramitacion de Titulo</option>";
		}
	    break;	
            case 6: //pensum 1992
                        if($totalUCA == ($UCAtotalEsc - 5)){ // Creditos de Trabajo de grado II
                            $opciones .= "<option value='3'>Solo Falta Tesis</option>";
                        }
                        if ($totalUCA >= $UCAtotalEsc) {
                            $opciones .= "<option value='4'>Tramitacion de Titulo</option>";        
                        }
                    break;
            default:
                if($totalUCA >= 175) {
                    $opciones .= "<option value='4'>Tramitacion de Titulo</option>";        
                }
        }
        $opciones  = addslashes($opciones);
        $json[] = $this->SwapBytes_Jquery->setHtml('tblInfoEst', $HTML);
        $json[] = $this->SwapBytes_Jquery->setHtml('selConstancia', $opciones);
        $this->getResponse()->setBody(Zend_Json::encode($json));
    }
    

        public function verificarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json    = array();
            $ci   = $this->_getParam('ci');
            if(!ctype_digit($ci)) return;
            $cantesc    = $this->RecordAcademico->getCantidadEscuela($ci);
            $Escuelas = $this->RecordAcademico->getUltimaEscuelaEstudiante($ci);
            if($cantesc > 1) {
              $sEscuela  = $this->_getParam('selEscuela');
            } elseif ($cantesc == 1) {
              $sEscuela  = $this->RecordAcademico->getIDEscuela($ci);
            } else {
              $HTML  = "<table><tr><td style='text-align:right'><b>Cedula no Encontrada</b></td>";
              $HTML .= '</tr></table>';
              $HTML  = addslashes($HTML);
              $json[] = $this->SwapBytes_Jquery->setAttr('selEscuela','disabled','true');
              $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerar','disabled','true');
              $json[] = $this->SwapBytes_Jquery->setAttr('selConstancia','disabled','true');
              $json[] = $this->SwapBytes_Jquery->setHtml('tblMsg', $HTML);
              $json[] = $this->SwapBytes_Jquery->setHtml('selEscuela    ', "");
              $json[] = $this->SwapBytes_Jquery->setHtml('tblInfoEst    ', "");
              $json[] = $this->SwapBytes_Jquery->setHtml('selConstancia    ', "");
              $this->getResponse()->setBody(Zend_Json::encode($json));
              return;
            }
			$pensum = $this->RecordAcademico->getPeriodoEscuelaPensum($ci);
            $ultimoperiodocursado = $this->RecordAcademico->getUltimoPeriodocursado($ci)[0]['fn_xrxx_reinscripcion_upc'];
            if ($ultimoperiodocursado == NULL) {
                $ultimoperiodocursado = $this->inscripciones->getUltimoPeriodoInscripcion($ci);
            }
            $fkPensum = $this->inscripciones->getPensumInscripcion($ci,$ultimoperiodocursado)[0]['fk_pensum'];  
			$EquiUCA = $this->RecordAcademico->getUCACEscuelaEquivPensum($ci, $sEscuela,$pensum[0]['pensum']);
            $ultimaSede = $this->inscripciones->getUltimaSede($ci);
            if($cantesc >= 1) {
                if ($cantesc>1) {
					$json[] = $this->SwapBytes_Jquery->setAttr('selEscuela','disabled','false');
					$json[] = $this->SwapBytes_Jquery->setAttr('btnGenerar','disabled','false');
					$json[] = $this->SwapBytes_Jquery->setAttr('selConstancia','disabled','false');
                } else {
					$json[] = $this->SwapBytes_Jquery->setAttr('selEscuela','disabled','true');
					$json[] = $this->SwapBytes_Jquery->setAttr('btnGenerar','disabled','false');
                }
                foreach ($Escuelas as $esc) {
                    $opt .= "<option value='{$esc['fk_atributo']}'>{$esc['escuela']}</option>";
                };
                $opt  = addslashes($opt);
                $status  = $this->RecordAcademico->getEstudianteStatus($ci, $Escuelas[0]['fk_atributo']);
                $UCAtotalEsc = $this->RecordAcademico->UCAtotalEscuelaPensum($Escuelas[0]['fk_atributo'], $pensum[0]['pensum']);
        		$totalUCA = $status[0]['uca'] + $EquiUCA;
                $HTML    = "<table>";
                $HTML   .= "<tr><td>Estudiante: </td><td>{$status[0]['apellido']} {$status[0]['nombre']}</td></tr>";
                $HTML   .= "<tr><td>Ultimo Periodo Inscrito: </td><td>{$status[0]['periodo']}</td>";
                $HTML   .= "<tr><td>Unidades de Credito Aprobadas UNE: </td><td>{$status[0]['uca']}</td></tr>";
                $HTML   .= "<tr><td>Unidades de Credito Aprobadas Otras Univ.: </td><td>{$EquiUCA}</td></tr>";
                $HTML   .= "<tr><td>Unidades de Credito Aprobadas: </td><td>{$totalUCA}</td></tr>";
                $HTML   .= "<tr><td>Indice Academico Acumulado: </td><td>{$status[0]['iia']}</td></tr>";
                $HTML   .= "</tr></table>";
                $json[] = $this->SwapBytes_Jquery->setAttr('selConstancia','disabled','false');
                if ($status[0]['periodo']==$status[0]['uperiodo']) {
					$opciones .= "<option value='1'>Estudios</option>";
                }
                $opciones .= "<option value='2'>Actuacion Estudiantil</option>";
              	//$opciones .= "<option value='3'>Solo Falta Tesis</option>";
		# $opciones .= "<option value='4'>Tramitacion de Titulo</option>";
		switch ($pensum[0]['pensum']) {

                    case 7://pensum 1997
                        $pendiente = $this->usuario->getMateriasPendientes($ci,$ultimoperiodocursado,$ultimaSede,$Escuelas[0]['fk_atributo'],$fkPensum);
                        //var_dump($pendiente);die;
                        if (count($pendiente) == 1 && $pendiente[0]['fk_materia'] == 834) {
                             $opciones .= "<option value='3'>Solo Falta Tesis</option>";
                        }
                        if (count($pendiente) == 0) {
                             $opciones .= "<option value='4'>Tramitacion de Titulo</option>";
                        }
                        /*
                        if ($totalUCA == 169) {
                        $opciones .= "<option value='3'>Solo Falta Tesis</option>";
                        }
                        if ($totalUCA >= 175) {
                            $opciones .= "<option value='4'>Tramitacion de Titulo</option>";        
                        }*/
                    break;
                    case 8://pensum 2012
                        $pendiente = $this->usuario->getMateriasPendientes($ci,$ultimoperiodocursado,$ultimaSede,$Escuelas[0]['fk_atributo'],$fkPensum);
                        //var_dump($pendiente);die;
                        if (count($pendiente) == 1 && $pendiente[0]['fk_materia'] == 9724) {
                             $opciones .= "<option value='3'>Solo Falta Tesis</option>";
                        }
                        if (count($pendiente) == 0) {
                             $opciones .= "<option value='4'>Tramitacion de Titulo</option>";
                        }
                        /*
                        if ($totalUCA == ($UCAtotalEsc - 5)) {//creditos de trabajo de grado II
                           
                        }
                        if ($totalUCA >= $UCAtotalEsc) {
                            $opciones .= "<option value='4'>Tramitacion de Titulo</option>";        
                        }*/
                    break;
                    default://pensum 1997
						/*hack para obtener constancia solo tesis debe modificarse para que 
						 * verifique por materia aprobada TODO*/
                        $pendiente = $this->usuario->getMateriasPendientes($ci,$ultimoperiodocursado,$ultimaSede,$Escuelas[0]['fk_atributo'],$fkPensum);
                        
                        if (count($pendiente) == 1 && $pendiente[0]['fk_materia'] == 834) {
                             $opciones .= "<option value='3'>Solo Falta Tesis</option>";
                        }
                        if (count($pendiente) == 0) {
                             $opciones .= "<option value='4'>Tramitacion de Titulo</option>";
                        }
                        /*

                        if ($totalUCA == 169 || ($totalUCA == $UCAtotalEsc - 5 && $pensum[0]['pensum'] == '6') ) {
							$opciones .= "<option value='3'>Solo Falta Tesis</option>";
                        }
                        if ($totalUCA >= 175) {
                            $opciones .= "<option value='4'>Tramitacion de Titulo</option>";        
                        }*/
                    break;
                }
                $opciones  = addslashes($opciones);
                $json[] = $this->SwapBytes_Jquery->setHtml('tblInfoEst', $HTML);
                $json[] = $this->SwapBytes_Jquery->setHtml('tblMsg', '');
                $json[] = $this->SwapBytes_Jquery->setHtml('selEscuela', $opt);
                $json[] = $this->SwapBytes_Jquery->setHtml('selConstancia', $opciones);
                $this->getResponse()->setBody(Zend_Json::encode($json));
			}
        }
    }
}
