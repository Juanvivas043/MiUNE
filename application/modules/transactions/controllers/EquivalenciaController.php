<?php
class Transactions_EquivalenciaController extends Zend_Controller_Action {
    private $Title = 'Transacciones \ Traslados & Equivalencias';

    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Asignaturas');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Reconocimientos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Pensums');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');

        $this->atributos       = new Models_DbTable_Atributos();
        $this->asignaturas     = new Models_DbTable_Asignaturas();
        $this->estructuras     = new Models_DbTable_Estructuras();
        $this->escuelas        = new Models_DbTable_EstructurasEscuelas();
        $this->inscripciones   = new Models_DbTable_Inscripciones();
        $this->Reconocimientos = new Models_DbTable_Reconocimientos();
        $this->RecordAcademico = new Models_DbTable_Recordsacademicos();
        $this->pensums         = new Models_DbTable_Pensums();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->usuario         = new Models_DbTable_Usuarios();
        $this->usuariogrupo    = new Models_DbTable_UsuariosGrupos();

        $this->SwapBytes_Html         = new SwapBytes_Html();
        $this->SwapBytes_Array        = new SwapBytes_Array();
        $this->SwapBytes_Ajax         = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action  = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_List    = new SwapBytes_Crud_List();
        $this->SwapBytes_Jquery       = new SwapBytes_Jquery();
        $this->SwapBytes_JavaScript   = new SwapBytes_JavaScript();
        $this->SwapBytes_Html_Message = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri          = new SwapBytes_Uri();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->Data['ci']            = $this->_getParam('ci');
        $this->Data['sede']          = $this->_getParam('sede');
        $this->Data['escuela']       = $this->_getParam('escuela');
        $this->Data['pensum']        = $this->_getParam('pensum');
        $this->Data['estado']        = $this->_getParam('estado');
        $this->Data['asignaturas']   = $this->_getParam('asignaturas');
        $this->Data['universidades'] = $this->_getParam('universidades');
        $this->Data['observaciones'] = $this->_getParam('observaciones');

        $this->NameChkUni   = 'chkUni';
        $this->NameChkMat   = 'chkMat';

        $FiltersSettings = array('periodo' => array('limit' => 10),
                                 'sede'    => array('table' => 'asignaciones'));
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if(!$this->usuariogrupo->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

    /**
     * Crea la estructura base de la pagina principal.
     */
    public function indexAction() {
        $this->view->title            = $this->Title;
        $this->view->module           = $this->Request->getModuleName();
        $this->view->controller       = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax   = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->universidades    = $this->getUniversidades();
    }

    private function getUniversidades() {
        $Universidades = $this->atributos->getSelect(25);
        $Universidades = $this->SwapBytes_Html->getCheckBoxList($this->NameChkUni, $Universidades, array('disabled' => null));

        return $Universidades;
    }

    public function sedeAction() {
        $dataRows = $this->estructuras->getSelect();
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    public function escuelaAction() {
        $dataRows = $this->escuelas->getSelect($this->Data['sede']);
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    public function pensumAction() {
        $dataRows = $this->pensums->getSelect($this->Data['escuela']);
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    public function estadoAction() {
        $dataRows = $this->atributos->getSelect(26);
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    private function generarValidate() {
        if(empty($this->estudiante)) {
            $json   = array();
            $HTML   = $this->SwapBytes_Html_Message->alert("Se requiere introducir una Cédula de Identidad valida o existente para poder listar las asignaturas.");
            $json[] = $this->SwapBytes_Jquery->setHide('divDatos');
            $json[] = $this->SwapBytes_Jquery->setHide('divLeyenda');
            $json[] = $this->SwapBytes_Jquery->setHide('divUniversidades');
            $json[] = $this->SwapBytes_Jquery->setHtml('tblRecordAcademico', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
            $this->SwapBytes_Ajax->endResponse();
        }
    }

    public function generarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $this->estudiante = $this->usuario->getRow($this->Data['ci']);
            $this->generarValidate();
            $this->estudiante = $this->estudiante['apellido'] . ', ' . $this->estudiante['nombre'];
            $escuelaNombre    = $this->inscripciones->getUltimaEscuela($this->Data['ci']);
            $json             = array();
            $HtmlObjectName   = 'pk_asignatura';
            $Observaciones    = '';
			$ra_data          = $this->asignaturas->getEquivalenciasExterna($this->Data['ci']
																			, $this->Data['pensum'],
																			$this->Data['escuela'],
																			$this->Data['estado']);
            // Definimos las propiedades de la tabla.
            $ra_property_table = array('class'  => 'tableData',
                                       'width'  => '750px',
                                       'column' => 'disponible',
                                       'rows'   => array('conditions' => array( 864 => array('equal'      => '864',
                                                                                             'properties' => array('style' => 'background-color:#CCCCFF;color:#666666;')),
                                                                                862 => array('equal'      => '862',
                                                                                             'properties' => array('style' => 'background-color:#99FF99;color:#666666;')),
                                                                               1264 => array('equal'      => '1264',
                                                                                             'properties' => array('style' => 'background-color:#99FF99;color:#666666;')),
                                                                               1265 => array('equal'      => '1265',
                                                                                             'properties' => array('style' => 'background-color:#99FF99;color:#666666;')),
                                                                               1266 => array('equal'      => '1266',
                                                                                             'properties' => array('style' => 'background-color:#99FF99;color:#666666;')),
                                           )));

            $ra_property_column = array( array('column'  => $HtmlObjectName,
                                               'primary' => true,
                                               'hide'    => true),
                                         array('name'    => '#',
                                               'width'   => '30px',
                                               'column'  => 'disponible',
                                               'rows'    => array('style'      => 'text-align:center'),
                                               'control' => array('tag'        => 'input',
                                                                  'type'       => 'checkbox',
                                                                  'value'      => '##' . $HtmlObjectName . '##',
                                                                  'name'       => $this->NameChkMat . '##' . $HtmlObjectName . '##',
                                                                  'id'         => $this->NameChkMat . '##' . $HtmlObjectName . '##',
                                                                  'conditions' => array( 864 => array('equal'      => '864',
                                                                                                      'properties' => array('disabled' => 'disabled')),
                                                                                         862 => array('equal'      => '862',
                                                                                                      'properties' => array('disabled' => 'disabled')),
                                                                                        1264 => array('equal'      => '1264',
                                                                                                      'properties' => array('disabled' => 'disabled')),
                                                                                        1265 => array('equal'      => '1265',
                                                                                                      'properties' => array('disabled' => 'disabled')),
                                                                                        1266 => array('equal'      => '1266',
                                                                                                      'properties' => array('disabled' => 'disabled')),
                                                                      ))),
                                         array('name'    => 'Código',
                                               'column'  => 'codigopropietario',
                                               'width'   => '80px',
                                               'rows'    => array('style' => 'text-align:center')),
                                         array('name'    => 'Sem.',
                                               'column'  => 'semestre',
                                               'width'   => '30px',
                                               'rows'    => array('style' => 'text-align:center')),
                                         array('name'    => 'Materia',
                                               'column'  => 'materia'),
                                         array('name'    => 'U.C.',
                                               'column'  => 'unidadcredito',
                                               'width'   => '30px',
                                               'rows'    => array('style' => 'text-align:center')),
                                         );
            // Eliminamos la condición que se le aplica al estado actual.
            $ra_property_table['rows']['conditions'][$this->Data['estado']] = null;
            $ra_property_column[1]['control']['conditions'][$this->Data['estado']] = null;
            // Preparamos el comportamiento dinamico del HTML.
            if(isset($ra_data)) {
                $Datos  = "<b>Apellidos y Nombres:</b> {$this->estudiante}<br><b>Ultima escuela inscrita:</b> {$escuelaNombre}";
                $HTML   = '<b>Materias:</b>';
                $HTML  .= $this->SwapBytes_Crud_List->fill($ra_property_table, $ra_data, $ra_property_column);
                $HTML  .= '<br>';
                $HTML  .= $this->SwapBytes_Html->input(array('type' => 'button', 'id' => 'btnGuardar', 'value' => 'Guardar'));
                // Se define la acción de Guardar al boton.
                $funSede          = $this->SwapBytes_Jquery->getVal('selSede');
                $funEscuela       = $this->SwapBytes_Jquery->getVal('selEscuela');
                $funPensum        = $this->SwapBytes_Jquery->getVal('selPensum');
                $funEstado        = $this->SwapBytes_Jquery->getVal('selEstado');
                $funEstudiante    = $this->SwapBytes_Jquery->getVal('txtCI');
                $funObservaciones = $this->SwapBytes_Jquery->getVal('txtObservaciones');
                $funAsignaturas   = $this->SwapBytes_Jquery->serializeForm('tblRecordAcademico');
                $funUniversidades = $this->SwapBytes_Jquery->serializeForm('lstUniversidades');
                $function         = $this->SwapBytes_Jquery->Post('guardar', array('sede'          => $funSede,
                                                                                            'escuela'       => $funEscuela,
                                                                                            'pensum'        => $funPensum,
                                                                                            'estado'        => $funEstado,
                                                                                            'ci'            => $funEstudiante,
                                                                                            'asignaturas'   => $funAsignaturas,
                                                                                            'universidades' => $funUniversidades,
                                                                                            'observaciones' => $funObservaciones));
//                var_dump($function);die;

                // Obtenemos las observaciones y seleccionamos las universidades.
                $PkInscripcion = $this->inscripciones->getPk($this->Data['ci'], $this->Data['escuela']);
                if(isset($PkInscripcion)) {
                    // Obtenemos las observaciones
                    $Observaciones = $this->inscripciones->getObservaciones($PkInscripcion);
                    }
                }
                // Definimos valores iniciales.
                $json[] = $this->SwapBytes_Jquery->removeAttrAll('checkbox', null, 'checked');
                $json[] = $this->SwapBytes_Jquery->removeAttrAll('checkbox', null, 'disabled');
                $json[] = $this->SwapBytes_Jquery->removeAttrAll('checkbox', null, 'checked');
                $json[] = $this->SwapBytes_Jquery->removeAttr('txtObservaciones', 'disabled');
                $json[] = $this->SwapBytes_Jquery->setHtml('lblDatos', $Datos);
                $json[] = $this->SwapBytes_Jquery->setVal('txtObservaciones', $Observaciones);
                $json[] = $this->SwapBytes_Jquery->setShow('divDatos');
                $json[] = $this->SwapBytes_Jquery->setShow('divLeyenda');
                $json[] = $this->SwapBytes_Jquery->setShow('divUniversidades');
                $json[] = $this->SwapBytes_Jquery->setHtml('tblRecordAcademico', $HTML);
                $json[] = $this->SwapBytes_Jquery->setClick('btnGuardar', $function);


				if (isset($PkInscripcion)) {
				// Definimos los valores por defecto de aquellos elementos de la
                // lista de Universidades que se encuentra seleccionados.
                    $Universidades = $this->Reconocimientos->getUniversidades($this->Data['ci']);
                    if(isset($Universidades)) {
                        foreach($Universidades as $row) {
                            $idChkUni = $this->NameChkUni . $row['pk_universidad'];
                            $json[] = $this->SwapBytes_Jquery->setAttr($idChkUni, 'checked' , 'true');
						}
				}
                // Definimos los valores por defecto de aquellos elementos de la
                // lista de Materias que se encuentra seleccionados.
                foreach($ra_data as $row) {
                    if(isset($row['seleccionada'])) {
                        $idChkMat = $this->NameChkMat . $row[$HtmlObjectName];
                        $json[] = $this->SwapBytes_Jquery->setAttr($idChkMat, 'checked' , '1');
                    }
                }
                $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }
    }

    public function guardarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            //if(empty($this->Data['universidades'])) return;
            //if(empty($this->Data['asignaturas']))   return;
            $Univ = $this->Data['universidades'];
            $Asig = $this->Data['asignaturas'];
            $Univ = $this->SwapBytes_Uri->queryToArray($Univ);
            $Asig = $this->SwapBytes_Uri->queryToArray($Asig);
            // Crear o actualiza una inscripcion.
            $pkInscripcion = $this->inscripciones->getPK($this->Data['ci'], $this->Data['escuela'], 0, $this->Data['pensum']);
            if(empty($pkInscripcion)) {
                $usuarioGrupo  = $this->usuariogrupo->getEstudiante($this->Data['ci']);
				$cxxxStatus    = $this->inscripciones->addRow(
															$usuarioGrupo,
															0,
															null,
															null,
															$this->Data['escuela'],
															$this->Data['sede'],
															872,
															addslashes($this->Data['observaciones']),
															$this->Data['pensum']);
                $pkInscripcion = $this->inscripciones->getPK($this->Data['ci'], $this->Data['escuela'], 0, $this->Data['pensum']);
            } else {

				$xxuxStatus = $this->inscripciones->updateRow(
															$pkInscripcion, $usuarioGrupo,
															0,
															null,
															null,
															$this->Data['escuela'],
															$this->Data['sede'],
															872, addslashes($this->Data['observaciones']),
															$this->Data['estado']);
            }
            $this->RecordAcademico->deleteAll($pkInscripcion, $this->Data['estado']);
            $this->Reconocimientos->deleteAll($this->Data['ci'], $this->Data['estado']);
            // Universidades en tbl_reconocimientos.
            if(isset($Univ) && is_array($Univ) && count($Univ) > 0) {
                foreach($Univ as $Row) {
                    $pkReconocimiento = $this->Reconocimientos->getPK($pkInscripcion, $Row);
                    if(empty($pkReconocimiento)) {
                        $this->Reconocimientos->addRow($pkInscripcion, $Row);
                    }
                }
            }
            // Crea el record academico.
            if(isset($Asig) && is_array($Asig) && count($Asig) > 0) {
                foreach($Asig as $Row) {
                    if(isset($Row)) {
                        $pkRecordAcademico = $this->RecordAcademico->getPK($pkInscripcion, $Row);
                        if(empty($pkRecordAcademico)) {
                            $this->RecordAcademico->addRow($pkInscripcion, $Row, null, $this->Data['estado']);
                        }
                    }
                }
            }

            // Finalizamos.
            $HTML = $this->SwapBytes_Html_Message->alert('Los reconocimientos por equivalencia y traslado se guardaron con exito!.');
            $json[] = $this->SwapBytes_Jquery->setHide('divDatos');
            $json[] = $this->SwapBytes_Jquery->setHide('divLeyenda');
            $json[] = $this->SwapBytes_Jquery->setHide('divUniversidades');
            $json[] = $this->SwapBytes_Jquery->setHtml('tblRecordAcademico', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
}
