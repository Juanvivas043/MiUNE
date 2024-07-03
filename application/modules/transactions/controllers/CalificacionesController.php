<?php
/*
 * colocar una mascara para q acepte solo numero.
 * llecar un control de la asignacion de la cargad e notas, no cargada, cargada no impresa, impresa.
 * clave de autorizacion.
 *
 */
class Transactions_CalificacionesController extends Zend_Controller_Action {
    private $Title  = 'Transacciones \ Carga de calificaciones';
    private $MessageTitle = 'Consignar calificaciones';
    private $MateriaEstadoCursada     = 862;
    private $MateriaEstadoPorImprimir = 1255;

    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Une_Filtros');
        $this->filtros         = new Une_Filtros();
        $this->asignaciones    = new Models_DbTable_Asignaciones();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->RecordAcademico = new Models_DbTable_Recordsacademicos();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->usuarios        = new Models_DbTable_Usuarios();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
    $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
    $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
    $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
    $this->filtros->setDisplay(true, true, true, true, true, true, false, true, false);
    $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
    $this->filtros->setRecursive(true, true, true, true, true, true, false, true, false);
    $this->filtros->setParam('usuario', $this->authSpace->userId);
    //$this->filtros->setParam('periodo', $this->periodos->getUltimo());
    // $this->filtros->setParam('periodo', 122);

    $this->SwapBytes_Crud_Action->setDisplay(true, true);
    $this->SwapBytes_Crud_Action->setEnable(true, true);
    $this->SwapBytes_Crud_Search->setDisplay(false);
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
        if(!$this->grupo->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
        }
}

    /**
     * Crea la estructura base de la pagina principal.
     */
    public function indexAction() {
        $this->view->title            = $this->Title;
        $this->view->filters          = $this->filtros;
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax   = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
    $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
    $this->view->SwapBytes_Crud_Form = $this->SwapBytes_Crud_Form;
    $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
    $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
    }
    public function periodoAction(){
      $this->filtros->getAction(array('usuario'));
    }
    public function sedeAction() {
        $this->filtros->getAction(array('usuario', 'periodo'));
    }

    public function escuelaAction() {
        $this->filtros->getAction(array('usuario', 'periodo', 'sede'));
    }

    public function pensumAction() {
        $this->filtros->getAction(array('usuario', 'periodo', 'sede', 'escuela'));
    }

    public function semestreAction() {
        $this->filtros->getAction(array('usuario', 'periodo', 'sede', 'escuela', 'pensum'));
    }

    public function materiaAction() {
        $this->filtros->getAction(array('usuario', 'periodo', 'sede', 'escuela', 'pensum', 'semestre'));
    }

    public function seccionAction() {
        $this->filtros->getAction(array('usuario', 'periodo', 'sede', 'escuela', 'pensum', 'semestre', 'seccion'));
    }

    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json   = array();
            $Estado = $this->_getParam('estado');
            $Data   = $this->filtros->getParams();
            //var_dump($Data);die;
            //$Data['periodo'] = $this->periodos->getUltimo();

//            $Params = $this->_getParam('data');
//            $Params = $this->SwapBytes_Uri->queryToArray($Params);
//
//            $Data['sede']     = $Params['selSede'];
//            $Data['escuela']  = $Params['selEscuela'];
//            $Data['semestre'] = $Params['selSemestre'];
//            $Data['materia']  = $Params['selMateria'];
//            $Data['seccion']  = $Params['selSeccion'];
//            $Data['estado']   = (isset($Estado))? $Estado : $this->MateriaEstadoInscrita;
            $Estado = (isset($Estado))? 'f' : $this->RecordAcademico->getStatusConsignado($Data);

//      var_dump($Data);
//      exit();
            $rows   = $this->RecordAcademico->getEstudiantes($Data);
            if($Estado == 'f') {
                if(isset($rows) && count($rows) > 0) {
                    // Definimos las propiedades de la tabla.
                    $table = array('class' => 'tableData',
                                   'width' => '760px');

                    $columns = array(array('column'  => 'pk_recordacademico',
                                           'primary' => true,
                                           'hide'    => true),
                                     array('name'     => '#',
                                           'width'    => '30px',
                                           'function' => 'rownum',
                                           'rows'    => array('style' => 'text-align:right')),
                                     array('name'    => 'C.I.',
                                           'width'   => '70px',
                                           'column'  => 'ci',
                                           'rows'    => array('style' => 'text-align:center')),
                                     array('name'    => 'Apellido',
                                           'width'   => '300px',
                                           'column'  => 'apellido'),
                                     array('name'    => 'Nombre',
                                           'width'   => '300px',
                                           'column'  => 'nombre'),
                                     array('name'    => 'Calif.',
                                           'column'  => 'calificacion',
                                           'width'   => '60px',
                                           'rows'    => array('style'     => 'text-align:center'),
                                           'control' => array('class'     => 'TextBoxNormal',
                                                              'tag'       => 'input',
                                                              'type'      => 'text',
                                                              'maxlength' => '2',
                                                              'size'      => '2',
                                                              'value'     => '##calificacion##',
                                                              'name'      => 'txt##pk_recordacademico##',
                                                              'id'        => 'txt##pk_recordacademico##')),
                                     array('name'    => 'N/C',
                                           'column'  => 'nc',
                                           'width'   => '30px',
                                           'column'  => 'estado',
                                           'rows'    => array('style' => 'text-align:center'),
                                           'control' => array('tag'   => 'input',
                                                              'type'  => 'checkbox',
                                                              'name'  => 'chk##pk_recordacademico##',
                                                              'id'    => 'chk##pk_recordacademico##')));
                    // Generamos la lista.
                    // 5960906
                    $pk_asignaciones = Array();
                    foreach ($rows as $row){
                       if(!in_array($row['pk_asignacion'],$pk_asignaciones)){
                          $pk_asignaciones[] = $row['pk_asignacion'];
                       }
                    }
                    if(count($pk_asignaciones)>1){
                       $pk_asignacion = implode(",", $pk_asignaciones);
                    }else{
                       $pk_asignacion = $pk_asignaciones[0];
                    }

                    $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
                    $HTML  .= $this->SwapBytes_Html->input(array('type' => 'hidden', 'name' => 'idAsignacion', 'value' => $pk_asignacion));
                    $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);

                    foreach($rows as $row) {
                        // Asignamos el evento a los objetos HTML de la lista.
                        $idTxt = 'txt' . $row['pk_recordacademico'];
                        $idChk = 'chk' . $row['pk_recordacademico'];

                        $condition = $this->SwapBytes_Jquery->isChecked($idChk);
                        $this->SwapBytes_Jquery->endLine(true);
                        $function  = $this->SwapBytes_Jquery->setVal($idTxt, '0');
                        $function .= $this->SwapBytes_Jquery->setAttr($idTxt, 'disabled', $condition);
                        $function .= $this->SwapBytes_Jquery->setAttr($idTxt, 'class', "'TextBoxNormal'");
                        $action    = $this->SwapBytes_Jquery->setVal($idTxt, '');
                        $action   .= ';';
                        $action   .= $this->SwapBytes_Jquery->setFocus($idTxt);
                        $function .= "if(!{$condition}){" . $action. "}";
                        $this->SwapBytes_Jquery->endLine(false);
                        $function  = $this->SwapBytes_Jquery->setChange($idChk, $function);

                        $json[] = $function;

                        // Asignamos los estados y valores por defecto a los objetos HTML
                        // de la lista, siempre y cuando estos se encuentren cursados.
                        if(empty($row['calificacion']) && $row['estado'] == $this->MateriaEstadoCursada) {
                            $json[] = $this->SwapBytes_Jquery->setVal($idTxt, '0');
                            $json[] = $this->SwapBytes_Jquery->setAttr($idTxt, 'disabled', 'true');
                            $json[] = $this->SwapBytes_Jquery->setAttr($idChk, 'checked', 'true');
                        }
                    }

                    $json[] = $this->SwapBytes_Jquery->setAttr('btnValidar', 'disabled', 'false');
                    $json[] = $this->SwapBytes_Jquery->setShow('btnValidar');
                    $json[] = '$("input").each(function () {$(this).bind("keydown", "return", function(){$(this).focusNextInputField();});})';
                    $json[] = '$("input:text:first").focus()';
                } else {
                    $HTML  = $this->SwapBytes_Html_Message->alert("No existen estudiantes inscritos.");

                    $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
                }
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("Las calificaciones de la asignatura seleccionada se encuentran YA consignadas a Control de Estudios, en caso de que se requiera realizar una modificación, solicite a dicha dirección para que le autorice.");
                $HTML .= "<br>";
                $HTML .= "Contraseña de Operaciones Especiales: <input id='txtOperacionEspecial' type='password'>";
                $HTML .= "&nbsp;";
                $HTML .= "<button type='button' id='btnOperacionEspecial'>Aceptar</button>";
        $HTML  = addslashes($HTML);

                $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
                $json[] = $this->SwapBytes_Jquery->setAttr('btnValidar', 'disabled', 'true');
                $json[] = $this->SwapBytes_Jquery->setHide('btnValidar');

                $Functions = $this->SwapBytes_Jquery->getValInMD5('txtOperacionEspecial');
                $Functions = $this->SwapBytes_Jquery->getJSON('clave', null, array('value' => $Functions));
                $json[] = $this->SwapBytes_Jquery->setClick('btnOperacionEspecial', $Functions);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function validarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $errorGlobal = false;
            $json        = array();
            $Data        = $this->_getParam('data');
            $Rows        = $this->SwapBytes_Uri->queryToArray($Data);

            // Validamos si las calificaciones son correctas como dato.
            foreach($Rows as $RowIndex => $RowValue) {
                // Solo permitimos validar aquellos campos de del Objeto tipo Text
                // que son enviados con la calificacion. Se ignoran el campos del
                // Objeto de tipo Checkbox.
                if(strpos($RowIndex, 'txt') !== false) {
                    $errorRow = false;

                    // Si se encuentra en el rango.
                    if(!($RowValue >= 1 && $RowValue <= 20)) {$errorRow = true; $errorGlobal = true;}

                    // Verifica si es numerico.
                    if(!is_numeric($RowValue) || is_float($RowValue)) {$errorRow = true; $errorGlobal = true;}

                    // Verifica si existen los simbolos (.) y (,) como parte de un
                    // decimal, con el fin de corregir el vacio de las funciones
                    // is_numericy y is_float.
                    if(strpos($RowValue, '.') > 0) {$errorRow = true; $errorGlobal = true;}
                    if(strpos($RowValue, ',') > 0) {$errorRow = true; $errorGlobal = true;}

                    // Coloreamos el objeto TextBox.
                    if($errorRow == true) {
                        $backgroundColor = "'TextBoxAlert'";
                    } else {
                        $backgroundColor = "'TextBoxNormal'";
                    }

                    $json[] = $this->SwapBytes_Jquery->setAttr($RowIndex, 'class', $backgroundColor);
                }
            }

            // Enviamos un mensaje al usuario dependiento del estado de la
            // validacion.
            if($errorGlobal == true) {
                $html  = $this->SwapBytes_Html->img($this->view->baseUrl() . '/images/icons/error-48.png', array('style' => 'float:left;margin-right: 8px;'));
                $html .= "No se puede enviar la información a Control de Estudio, existen valores incorrectos en los recuadros de color rojo, verifique las siguientes observaciones y vuelva a intentarlo.";
                $html .= $this->SwapBytes_Html->getList(array('Calificaciones entre el número 1 y el 20.',
                    'No se permiten números decimales, solo enteros.',
                    'No se permiten letras.',
                    'No se permiten caracteres especiales.'),
                    array('style' => 'padding-left:25px'));

                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmMessage', 'Guardar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmMessage', 'Cancelar');
            } else {
                $html  = $this->SwapBytes_Html->img($this->view->baseUrl() . '/images/icons/select-48.png', array('style' => 'float:left;margin-right: 8px;'));
                $html .= "Recuerde que antes de asentar las calificaciones en el Acta Definitiva de Calificaciones del Sistema de Control de Estudios, se haya cerciorado que fueron debidamente revisadas con todos los estudiantes y que no existen errores, ya que la Resolución del Consejo Universitario N° 01-92-02, vigente desde el 16 de Enero de 1992, establece que: ";
                $html .= "<b>\"Las calificaciones definitivas, una vez que han sido procesadas en el Computador, no podrán ser modificadas, a menos que el error haya sido originado en la transcripción desde la hoja de evaluación al acta definitiva de Calificaciones.\"</b>";
                $html .= "<br><br>";
                $html .= "¿Desea enviar la información a Control de Estudios?";
                $html .= $this->SwapBytes_Html->input(array('type'  => 'hidden',
                                                            'name'  => 'idAsignacion',
                                                            'value' => $Rows['idAsignacion']));

                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmMessage', 'Guardar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmMessage', 'Cancelar');
            }

            $json[] = $this->SwapBytes_Jquery->setHtml('frmMessage', $html);
            $json[] = $this->SwapBytes_Jquery->setAttr('frmMessage', 'style', "'text-align:left'");
            $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle('frmMessage', $this->MessageTitle);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->open('frmMessage');

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function claveAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json       = array();
            $Contraseña = $this->_getParam('value');

            if($this->usuarios->checkPasswordOperacionesEspeciales(1253, $Contraseña)) {
                $json[]    = $this->SwapBytes_Jquery->getJSON('list', null, array('filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros'),
                                                                                  'estado' => $this->MateriaEstadoCursada));
            } else {
                $json[] = $this->SwapBytes_Jquery->setVal('txtOperacionEspecial');
                $json[] = $this->SwapBytes_Jquery->setFocus('txtOperacionEspecial');
                $json[] = "alert('Clave invalida, vuelva a intentarlo.')";
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function guardarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json   = array();
            $Data   = $this->_getParam('data');
            $Rows   = $this->SwapBytes_Uri->queryToArray($Data);
            $estado = $this->MateriaEstadoCursada;

            foreach($Rows as $RowIndex => $RowValue) {
                $id = substr($RowIndex, 3, strlen($RowIndex) - 3);

                if(strpos($RowIndex, 'txt') !== false) {
                    $this->RecordAcademico->updateRow($id, null, null, null, $estado, $RowValue);
                    //$this->RecordAcademico->updateCalificacion($id, $estado, $RowValue);
                } else if(strpos($RowIndex, 'chk') !== false) {
                    //$this->RecordAcademico->updateCalificacion($id, $estado, 0);
                    $this->RecordAcademico->updateRow($id, null, null, null, $estado, $RowValue);
                }

                $json[] = $this->SwapBytes_Jquery->setAttr('txt' . $id, 'disabled', 'true');
                $json[] = $this->SwapBytes_Jquery->setAttr('chk' . $id, 'disabled', 'true');
            }

            // Definimos un nuevo estado a la asignacion,
            //
            $pos = strrpos($mystring, ",");
            if($pos === true){
               $pk_asignaciones = explode(",", $Rows['idAsignacion']);
               foreach($pk_asignaciones as $pk_asignacion){
                  $this->asignaciones->updateRow($pk_asignacion, array('fk_estado' => $this->MateriaEstadoPorImprimir));
               }
            }else{
               $this->asignaciones->updateRow($Rows['idAsignacion'], array('fk_estado' => $this->MateriaEstadoPorImprimir));
            }

            // Enviamos los datos por AJAX.
            $json[] = $this->SwapBytes_Jquery->setAttr('btnValidar', 'disabled', 'true');
            $json[] = $this->SwapBytes_Jquery->setHide('btnValidar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->close('frmMessage');

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
}
