<?php
class Transactions_ReconocimientoController extends Zend_Controller_Action {
    private $Title = 'Transacciones \ Reconocimiento';

    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Asignaturas');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
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

        $this->Data['ci']          = $this->_getParam('ci');
        $this->Data['sede']        = $this->_getParam('sede');
        $this->Data['escuela']     = $this->_getParam('escuela');
        $this->Data['pensum']      = $this->_getParam('pensum');
        $this->Data['asignaturas'] = $this->_getParam('asignaturas');
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

    private function generarValidate() {
        if(empty($this->estudiante)) {
            $json   = array();
            $HTML   = $this->SwapBytes_Html_Message->alert("Se requiere introducir una Cédula de Identidad valida o existente para poder listar las asignaturas.");
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

            $periodos       = $this->periodos->getSelect();
            $ra_data        = $this->asignaturas->getEquivalenciasInterna($this->Data['ci'], $this->Data['pensum'], $this->Data['escuela']);
			//var_dump($ra_data);die;
            // Definimos las propiedades de la tabla.
            $ra_property_table = array('class'  => 'tableData',
                                       'width'  => '1110px',
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

            $ra_property_column = array( array('column'  => 'pk_asignatura',
                                               'primary' => true,
                                               'hide'    => true),
                                         array('name'    => '#',
                                               'width'   => '30px',
                                               'column'  => 'disponible',
                                               'rows'    => array('style'      => 'text-align:center'),
                                               'control' => array('tag'        => 'input',
                                                                  'type'       => 'checkbox',
                                                                  'name'       => 'chk##' . $HtmlObjectName . '##',
                                                                  'id'         => 'chk##' . $HtmlObjectName . '##',
                                                                  'conditions' => array ( 864 => array('equal'      => '864',
                                                                                                       'properties' => array('disabled' => 'disabled')),
                                                                                         # 862 => array('equal'      => '862',
                                                                                         #              'properties' => array('disabled' => 'disabled')),
                                                                                         1264 => array('equal'      => '1264',
                                                                                                       'properties' => array('disabled' => 'disabled')),
                                                                                         1265 => array('equal'      => '1265',
                                                                                                       'properties' => array('disabled' => 'disabled')),
                                                                                         1266 => array('equal'      => '1266',
                                                                                                       'properties' => array('disabled' => 'disabled')),))),
                                         array('name'    => 'Código',
                                               'column'  => 'codigopropietario',
                                               'width'   => '80px',
                                               'rows'    => array('style' => 'text-align:center')),
                                         array('name'    => 'Sem.',
                                               'column'  => 'semestre',
                                               'width'   => '30px',
                                               'rows'    => array('style' => 'text-align:center')),
                                         array('name'    => 'Materia',
                                               'column'  => 'materia',
                                               'rows'    => array('style' => 'text-align:left')),
                                         array('name'    => 'U.C.',
                                               'column'  => 'unidadcredito',
                                               'width'   => '30px',
                                               'rows'    => array('style' => 'text-align:center')),
                                         array('name'    => 'período',
                                               'width'   => '200px',
                                               'column'  => 'fk_periodo',
                                               'rows'    => array('style'    => 'text-align:center'),
                                               'control' => array('tag'      => 'select',
                                                                  'style'    => 'width:200px',
                                                                  'disabled' => 'disabled',
                                                                  'options'  => $periodos,
                                                                  'name'     => 'selPer##' . $HtmlObjectName . '##',
                                                                  'id'       => 'selPer##' . $HtmlObjectName . '##')),
                                                 array('name'    => 'Calf.',
                                                       'column'  => 'calificacion',
                                                       'width'   => '50px',
                                                       'rows'    => array('style'     => 'text-align:center'),
                                                       'control' => array('class'     => 'TextBoxNormal',
                                                                          'tag'       => 'input',
                                                                          'type'      => 'text',
                                                                          'maxlength' => '2',
                                                                          'size'      => '2',
                                                                          'disabled'  => 'disabled',
                                                                          'value'     => '##calificacion##',
                                                                          'name'      => 'txt##' . $HtmlObjectName . '##',
                                                                          'id'        => 'txt##' . $HtmlObjectName . '##'))
                                         );

            $HTML   = $this->SwapBytes_Html_Message->alert("<b>Apellidos y Nombres:</b> {$this->estudiante}<br><b>Ultima escuela inscrita:</b> {$escuelaNombre}<br>");
            $HTML  .= '<br>';
            $HTML  .= $this->SwapBytes_Crud_List->fill($ra_property_table, $ra_data, $ra_property_column);
            $HTML  .= '<br>';
            $HTML  .= $this->SwapBytes_Html->input(array('type' => 'button', 'id' => 'btnGuardar', 'value' => 'Guardar'));

            $json[] = $this->SwapBytes_Jquery->setShow('divLeyenda');
            $json[] = $this->SwapBytes_Jquery->setHtml('tblRecordAcademico', $HTML);

            if(isset($ra_data)) {
                // Asignamos el evento a los objetos HTML de la lista.
                foreach($ra_data as $row) {
                    // Asignamos los nombres de los ID de cada control HTML que
                    // interviene en la tabla.
                    $idTxtCal = 'txt'    . $row[$HtmlObjectName]; // Calificacion
                    $idChkAsi = 'chk'    . $row[$HtmlObjectName]; // Asignatura
                    $idSelPer = 'selPer' . $row[$HtmlObjectName]; // Periodo

                    // Se define el comportamiento de los CHECK.
                    $condition  = $this->SwapBytes_Jquery->isChecked($idChkAsi, true);
                    $this->SwapBytes_Jquery->endLine(true);
                    $function   = $this->SwapBytes_Jquery->ifSetAttr($condition, $idTxtCal, 'disabled', $condition);
                    //$function  .= $this->SwapBytes_Jquery->ifSetVal($condition, $idTxtCal, '');
                    //$function  .= $this->SwapBytes_Jquery->ifSetValSelectOption($condition, $idSelPer, 0);
                    $function  .= $this->SwapBytes_Jquery->setAttr($idTxtCal, 'disabled', $condition);
                    $function  .= $this->SwapBytes_Jquery->setAttr($idSelPer, 'disabled', $condition);
                    $this->SwapBytes_Jquery->endLine(false);
                    $function   = $this->SwapBytes_Jquery->setChange($idChkAsi, $function);
                    $json[]     = $function;
                }

                // Se define la acción de Guardar al boton.
                $funSede        = $this->SwapBytes_Jquery->getVal('selSede');
                $funEscuela     = $this->SwapBytes_Jquery->getVal('selEscuela');
                $funPensum      = $this->SwapBytes_Jquery->getVal('selPensum');
                $funEstudiante  = $this->SwapBytes_Jquery->getVal('txtCI');
                $funAsignaturas = $this->SwapBytes_Jquery->serializeForm('tblRecordAcademico');
                $function       = $this->SwapBytes_Jquery->getJSON('guardar', null, array('sede'        => $funSede,
                                                                                          'escuela'     => $funEscuela,
                                                                                          'pensum'      => $funPensum,
                                                                                          'ci'          => $funEstudiante,
                                                                                          'asignaturas' => $funAsignaturas));
                $json[]         = $this->SwapBytes_Jquery->setClick('btnGuardar', $function);

                $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }
    }

    public function guardarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $Data = $this->Data['asignaturas'];
            $Data = $this->SwapBytes_Uri->queryToArray($Data);
            $Data = $this->SwapBytes_Array->split_key($Data, array('txt', 'selEst', 'selPer', 'chk'));

            // Verificamos primero la existencia de posibles errores en las
            // calificaciones, al existir estos los mostramos resaltando el
            // cuadro de texto de color rojo y no se guardan los datos.
            $errorGlobal = false;

            foreach($Data as $RowKey => $RowValue) {
                if(isset($RowValue['txt'])) {
                    // Solo permitimos validar aquellos campos de del Objeto tipo Text
                    // que son enviados con la calificacion. Se ignoran el campos del
                    // Objeto de tipo Checkbox.
                    $errorRow = false;

                    // Si se encuentra en el rango.
                    if(!($RowValue['txt'] >= 0 && $RowValue['txt'] <= 20)) {$errorRow = true; $errorGlobal = true;}

                    // Verifica si es numerico.
                    if(!is_numeric($RowValue['txt']) || is_float($RowValue['txt'])) {$errorRow = true; $errorGlobal = true;}

                    // Verifica si existen los simbolos (.) y (,) como parte de un
                    // decimal, con el fin de corregir el vacio de las funciones
                    // is_numericy y is_float.
                    if(strpos($RowValue['txt'], '.') > 0) {$errorRow = true; $errorGlobal = true;}
                    if(strpos($RowValue['txt'], ',') > 0) {$errorRow = true; $errorGlobal = true;}

                    // Coloreamos el objeto TextBox.
                    if($errorRow == true) {
                        $backgroundColor = "'TextBoxAlert'";
                    } else {
                        $backgroundColor = "'TextBoxNormal'";
                    }

                    $json[] = $this->SwapBytes_Jquery->setAttr('txt' . $RowKey, 'class', $backgroundColor);
                }
            }

            if($errorGlobal == true) {
                $this->getResponse()->setBody(Zend_Json::encode($json));
                $this->SwapBytes_Ajax->endResponse();
            }

            // Agregamos las asignaturas en el Record Academico.
            foreach($Data as $RowKey => $Row) {
                // Crea la inscripcion del estudiante en caso de que no exista.
                $pkInscripcion     = $this->inscripciones->getPK($this->Data['ci'], $this->Data['escuela'], $Row['selPer'], $this->Data['pensum']);
                $pkRecordAcademico = $this->RecordAcademico->getPK($pkInscripcion, $RowKey);

                // Creamos la inscripcion del estudiante en caso de que no exista
                // la materia en un periodo determinado, y se vuelven a buscar los
                // valores de las variables declaradas anteriormente dentro del
                // ciclo por la existencia de una ausencia.
                if(empty($pkInscripcion)) {
//                    print "I new PK\n";
                    $usuarioGrupo      = $this->usuariogrupo->getEstudiante($this->Data['ci']);
                    $cxxxStatus        = $this->inscripciones->addRow($usuarioGrupo, $Row['selPer'], null, null, $this->Data['escuela'], $this->Data['sede'], 872, null, $this->Data['pensum']);
                    $pkInscripcion     = $this->inscripciones->getPK($this->Data['ci'], $this->Data['escuela'], $Row['selPer'], $this->Data['pensum']);
                    $pkRecordAcademico = $this->RecordAcademico->getPK($pkInscripcion, $RowKey);
                }

                // Buscamos el PK por otro medio, esta vez se utiliza la C.I. del
                // estudiante y el codigo de la asignatura, y no depender de la
                // inscripcion, esto en caso de que se cambie el periodo.
                if(empty($pkRecordAcademico)) {
                    $pkRecordAcademico = $this->RecordAcademico->getPKbyUserID($this->Data['ci'], $RowKey);
                }
//
//                print "\n";
//                print 'FK A :' . $RowKey . "\n";
//                print 'PK RA:' . $pkRecordAcademico . "\n";
//                print 'PK I :' . $pkInscripcion . "\n";

                if(empty($pkRecordAcademico)) {
//                    print 'add' . "\n";

                    $this->RecordAcademico->addRow($pkInscripcion, $RowKey, null, 862, $Row['txt']);
                } else {
//                    print 'upd' . "\n";

                    $this->RecordAcademico->updateRow($pkRecordAcademico, $pkInscripcion, $RowKey, null, 862, $Row['txt']);
                }
            }
//            exit();

            // Finalizamos el proceso enviando la información al usuario.
            $json   = array();
            $HTML   = $this->SwapBytes_Html_Message->alert("Las equivalencias se guardaron con exito!.");
            $json[] = $this->SwapBytes_Jquery->setHtml('tblRecordAcademico', $HTML);

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
}
