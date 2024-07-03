<?php

class Transactions_SolicitudretiromateriasController extends Zend_Controller_Action {


    private $Title = 'Transacciones \ Solicitud de Retiro de Materias o Semestre';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGruposSolicitudes');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbView_Calendarios');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Materiasaretirar');
        Zend_Loader::loadClass('Forms_Solicitudretiromateria');
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->ugs             = new Models_DbTable_UsuariosGruposSolicitudes();
        $this->estudiante      = new Models_DbTable_Usuarios();
        $this->recordacademico = new Models_DbTable_Recordsacademicos();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->calendarios     = new Models_DbView_Calendarios();
        $this->inscripciones   = new Models_DbTable_Inscripciones();
        $this->mar   = new Models_DbTable_Materiasaretirar();
        $this->filtros         = new Une_Filtros();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->CmcBytes_Profit = new CmcBytes_Profit();
        $this->CmcBytes_Redirect = new CmcBytes_Redirect();
        $this->logger = Zend_Registry::get('logger');
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->redirect_session = new Zend_Session_Namespace('redirect_session');
        //filtro
        //$this->filtros->setDisplay(true, false, false, false, false, false, false, false, false);
        //$this->filtros->setDisabled(false, false, true, true, true, true, true, true, true);
        //$this->filtros->setRecursive(true, false, false, false, false, false, false, false, false);
        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, false, true, false, false, false);
		$this->SwapBytes_Crud_Action->setEnable(true, false, true, false, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(false);
        $this->view->form = new Forms_Solicitudretiromateria();
        $this->SwapBytes_Form->set($this->view->form);
      //  $this->SwapBytes_Form->fillSelectBox('fk_tipo', $this->atributos->getTipes(39,'8266') , 'pk_atributo', 'valor');
        $this->view->form = $this->SwapBytes_Form->get();
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->filtros->getParams();
        $this->_params['redirect'] = $this->redirect_session->params;
        $this->FormTitle_Info = 'Realizar Solicitud';
    }



   function preDispatch() {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
                $this->_helper->redirector('index', 'login', 'default');
            }
            if (!$this->grupo->haveAccessToModule()) {
                $this->_helper->redirector('accesserror', 'profile', 'default');
            }
        }


        public function indexAction() {

            $this->view->title   = $this->Title;
            $this->view->filters = $this->filtros;
            $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
            $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
            $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
            $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
            $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
            $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
            if($this->checkRedirect()==true){
                $this->view->trigger = $this->CmcBytes_Redirect->triggerButton($this->_params['redirect']['action']);
                $this->redirect_session->unsetAll();
            }
            $this->view->SwapBytes_Ajax->setView($this->view);
        }

        private function checkRedirect(){

            if($this->_params['redirect']['set'] == true){
                return true;
            }else{
                return false;
            }
        }

        public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            //$Data   = $this->filtros->getParams();
            $json   = array();
            $other = array('cedula' => $this->authSpace->userId,
                            'grupo' => 855);
            $rows = $this->ugs->getSolicitudesRetiroMaterias($other);
            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');
                $columns = array(array('column'  => 'pk_usuariogruposolicitud',
                                       'primary' => true,
                                        'hide' => true),
                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'function' => 'rownum',
                                       'rows'    => array('style' => 'text-align:right')),
                                 array('name'    => 'Número',
                                       'width'   => '80px',
                                       'column'  => 'pk_usuariogruposolicitud',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Tipo',
                                       'width'   => '120px',
                                       'column'  => 'valor',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Periodo',
                                       'width'   => '80px',
                                       'column'  => 'fk_periodo',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Sede',
                                       'width'   => '80px',
                                       'column'  => 'nombre',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Escuela',
                                       'width'   => '80px',
                                       'column'  => 'escuela',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Semestre',
                                       'width'   => '80px',
                                       'column'  => 'semubic',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Fecha',
                                       'width'   => '70px',
                                       'column'  => 'fechasolicitud'),
                                 array('name'    => 'Estado',
                                       'width'   => '70px',
                                       'column'  => 'estado',
                                       'rows'    => array('style' => 'text-align:center'))
                                 );
                $other = Array(
                         Array( 'actionName' => '',
                                'action' => 'materias(##pk##)',
                                'label' => 'Materias'
                                ),
                          Array( 'actionName' => '',
                                'action' => 'imprimir(##pk##)',
                                'label' => 'Imprimir',
                                'column' => 'estado',
                                'validate' => 'true',
                                'intrue' => 'Completada',
                                'intruelabel' => ''
                                )
                );
                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VOO',$other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkClase');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen solicitudes de retiro de materias o semestre que mostrar.");
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

        private function getinfo($exec){

            $json = array();
            $data = array();
            $pk_usuario = $this->authSpace->userId;
            // Buscar los datos del registro seleccionado.
            $dataRow    = $this->estudiante->getRow($pk_usuario);
            if($exec == true){
                $today = date("d/m/Y");
                $Info = $this->recordacademico->getInformacionGeneral($pk_usuario);
            }else{
                $other = array('cedula' => $pk_usuario,
                                'grupo' => 855);
                $Info = $this->ugs->getSolicitudesRetiroMaterias($other);
            }
            if(isset($Info) && is_array($Info) && count($Info) > 0) {
                $properties = array('width' => '380',
                                    'align' => 'right');
                $styles = array(array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px'));

                $data[] = array('Nombre: ', $dataRow['nombre']);
                $data[] = array('Apellido: ', $dataRow['apellido']);
                $data[] = array('Periodo: ', $Info['upi']);
                $data[] = array('Sede: '  ,$Info['sede']);
                $data[] = array('Escuela: ', $Info['escuela']);
                $data[] = array('Semestre de Ubicación: ', $Info['su']);
                $data[] = array('Turno: ', $Info['valor']);
                $data[] = array('Fecha de Solicitud: ', $today);
                $html  = $this->SwapBytes_Html->table($properties, $data, $styles);

            }
            // Envia los datos al modal.
            $js .= $this->SwapBytes_Jquery->setHtml('helper', $html) .';';
            $js  .= $this->SwapBytes_Jquery->setHtml('error_helper', '') .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 300) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 400) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->setCenter('frmModal') . ';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->changeTitle('frmModal', $this->FormTitle_Info).';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->open('frmModal').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar').';';
            $js .="$('#fk_tipo-label').attr('style','margin-left: 50px; font-size:14px; font-weight:bold;');";
            return $js;
            //$this->getResponse()->setBody(Zend_Json::encode($json));
        }

        private function viewInfo($pk){

            $pk_usuario = $this->authSpace->userId;
            $dataRow    = $this->estudiante->getRow($pk_usuario);
            $other = array('cedula' => $pk_usuario);
            $Info = $this->ugs->getSolicitudInfo($pk);
            if(isset($Info) && is_array($Info) && count($Info) > 0) {
                $properties = array('width' => '380',
                                    'align' => 'right');
                $styles = array(array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px'));
                $data[] = array('Nombre: ', $dataRow['nombre']);
                $data[] = array('Apellido: ', $dataRow['apellido']);
                $data[] = array('Periodo: ', $Info[0]['fk_periodo']);
                $data[] = array('Sede: '  ,$Info[0]['nombre']);
                $data[] = array('Escuela: ', $Info[0]['escuela']);
                $data[] = array('Semestre de Ubicación: ', $Info[0]['semubic']);
                //$data[] = array('Turno: ', $Info['valor']);
                $data[] = array('Fecha de Solicitud: ', $Info[0]['fechasolicitud']);
                $html  = $this->SwapBytes_Html->table($properties, $data, $styles);
            }

            
            $js .= $this->SwapBytes_Jquery->setHtml('helper', $html) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 280) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 400) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->setCenter('frmModal') . ';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->changeTitle('frmModal', 'Ver Solicitud').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->open('frmModal').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar').';';
            $js .="$('#fk_tipo-label').attr('style','margin-left: 50px; font-size:14px; font-weight:bold;');";
            return  $js;
        }

        private function showSemestre(){

            $pk_usuario = $this->authSpace->userId;
            $Info = $this->recordacademico->getInformacionGeneral($pk_usuario);
            $record = $this->recordacademico->getList_record_especifico($Info['upi'], $Info['sed_cod'], $Info['esc_cod'], $pk_usuario);
            //$this->logger->log($record,ZEND_LOG::ALERT);
            $properties = array('width' => '480',
                                'align' => 'center'
                                );
            $styles = array(array('style' => 'text-align:left;font-size:14px;width:340px;'),
                            array('style' => 'text-align:center;font-size:14px;width:80px;'),
                            array('style' => 'text-align:left;font-size:14px;width:60px;')
                           );
            $data[] = array('Esta seguro que desea retirar las siguientes materias?:');
            foreach($record as $materia){
          
                $data[] = array($materia['materia'], $materia['cod_ord']. $materia['cod_sem'] .$materia['seccion'],$materia['unidadcredito'] . ' U.C');
            }

            $html = $this->SwapBytes_Html->table($properties, $data, $styles);
            //$js = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('fk_tipo-label','fk_tipo-element')).';';
            //$js = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('helper')).';';
            $js  .= $this->SwapBytes_Jquery->setHtml('helper', '') .';';
            $js  .= $this->SwapBytes_Jquery->setHtml('error_helper', $html) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 340) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 520) .';';
            return $js;
        }

        private function errorSolvente($msg = null){

            $html = "<div class=\'alert\'><div class=\'message\'>{$msg}</div></div>";
            $js .= $this->SwapBytes_Jquery->setHtml('error_helper', $html) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('fk_tipo-element','fk_tipo-label')) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->setCenter('frmModal') . ';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Aceptar').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Siguiente').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Cancelar').';';
            return $js;
        }

        public function viewAction(){
            $this->SwapBytes_Ajax->setHeader();
            $dataRow = $this->_params['modal'];
            $this->SwapBytes_Form->fillSelectBox('fk_tipo', $this->ugs->getSolTipo($dataRow['id']) , 'fk_tipo', 'valor');
             $json[] = $this->viewInfo($dataRow['id']);
             $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Siguiente').';';
             $this->SwapBytes_Crud_Form->setJson($json);
             $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Detalles de la Solicitud');
             $this->SwapBytes_Crud_Form->getView();
        }

        public function addoreditloadAction() {
            if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $this->logger->log($this->CmcBytes_Profit->getSolvente($this->authSpace->userId),ZEND_LOG::INFO);
            $ci = $this->authSpace->userId;
            $solvente = $this->CmcBytes_Profit->getSolvente($this->authSpace->userId);
            
            $becado = $this->CmcBytes_Profit->isBecado($ci);
            //$this->logger->log($solvente,ZEND_LOG::INFO);
            $ultimoperiodo = $this->periodos->getUltimo();
            $sede = $this->inscripciones->getSedeInscripcion($ci, $ultimoperiodo);
            if(isset($sede[0])){
                $escuela = $this->inscripciones->getUltimaEscuelapk($ci);
                $tiene_inscritas = $this->recordacademico->getList_record_especifico($ultimoperiodo, $sede[0]['fk_estructura'], $escuela, $ci);
            }else{
               $msg = "Usted no tiene inscripción en el periodo actual.";
               $json[] = $this->errorSolvente($msg);
               $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Siguiente').';';
               $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar').';';
            }
            $countdown = $this->calendarios->getRetiroCountdown($ultimoperiodo);
           // $solvente = false; //forzar valor a  solvente
            $dataRow['pk_usuariogrupo'] = $this->grupo->getPK($ci, 'AND fk_grupo = 855');
            $solvente = true;
           if ($becado == true){
                $msg = "Usted se encuentra en situacion de Becado por lo que no puede retirar materias.";
                $json[] = $this->errorSolvente($msg);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Siguiente').';';
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar').';';
			/*Solvencia Administrativa*/
            } else if ($solvente == false) {
                	$msg = "No se puede realizar la solicitud debido a que no se encuentra solvente administrativamente";
                	$json[] = $this->errorSolvente($msg);
                	$json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Siguiente').';';
                	$json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar').';';
			} else 
					 if ($countdown[0]['restante'] < 0) {
                		$msg = "No se puede realizar la solicitud, la fecha tope para la realizarla era el " . $countdown[0]['fin'];
                		$json[] = $this->errorSolvente($msg);
                		$json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Siguiente').';';
                		$json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar').';';
					 } else	
						 if (!isset($tiene_inscritas[0])) {
                				$msg = "Usted no tiene materias inscritas en este periodo";
                				$json[] = $this->errorSolvente($msg);
                				$json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Siguiente').';';
                				$json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar').';';
            			} else {
                            //$this->atributos->getTipes(39,'8248') en local
                			$this->SwapBytes_Form->fillSelectBox('fk_tipo', $this->atributos->getTipes(40,'8266') , 'pk_atributo', 'valor');
                			$json[] = $this->getinfo(true);
                			$json[] = "$('#frmModal').parent().find(\"button:contains('Guardar')\").children().html('Siguiente');";
                			$json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Siguiente').';';
            			}
                $this->SwapBytes_Crud_Form->setJson($json);
                $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Realizar Solicitud');
                $this->SwapBytes_Crud_Form->getAddOrEditLoad();
            }
        }

        public function addoreditconfirmAction() {
			if ($this->_request->isXmlHttpRequest()) {
				$this->SwapBytes_Ajax->setHeader();
				$message = 'No se ha podido retirar el Periodo, debe cancelar el monto correspondiente al pago del Periodo para poder retirarlo.';
				$ci = $this->authSpace->userId;
				$dataRow = $this->_params['modal'];
				$this->SwapBytes_Form->fillSelectBox('fk_tipo', $this->atributos->getTipes(40,'8266') , 'pk_atributo', 'valor');
				//$this->logger->log($dataRow,ZEND_LOG::ALERT);
				$pagocompleto = $this->CmcBytes_Profit->checkPagoSemCompleto($ci);;
				if ($pagocompleto == false && $dataRow['fk_tipo'] == 8247) {
					$this->SwapBytes_Crud_Form->getDialog('Error al intentar de retirar semestre', $message, swOkOnly);
				}
				$this->SwapBytes_Crud_Form->setJson($json);
				$this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
				$this->SwapBytes_Crud_Form->getAddOrEditConfirm();
				}
        }

        public function addoreditresponseAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $today = date("Y-m-d");
            //$this->logger->log('pase',ZEND_LOG::INFO);
            $escuela = $this->inscripciones->getUltimaEscuelapk($this->authSpace->userId);
            $ultimoperiodo = $this->periodos->getUltimo();
            $sede = $this->inscripciones->getSedeInscripcion($this->authSpace->userId, $ultimoperiodo);
            $pensum = $this->inscripciones->getPensumInscripcion($this->authSpace->userId, $ultimoperiodo);
            $dataRow = $this->_params['modal'];
            $dataRow['id'] = null;
            $dataRow['fk_usuariogrupo'] = $dataRow['pk_usuariogrupo'];
            $dataRow['pk_usuariogrupo'] = null;
            $dataRow['hiddenerror_helper'] = null;
            $dataRow['fk_periodo'] = $ultimoperiodo;
            $dataRow['fk_estructura'] = $sede[0]['fk_estructura'];//$this->_params['filters']['sede'];
            $dataRow['fechasolicitud'] = $today;
            //var_dump($dataRow);die;
            // Retiro Materiaa
            if($dataRow['fk_tipo'] == 8246){
                $ci = $this->authSpace->userId;
                $est = $this->estudiante->getRow($ci);
                $Info = $this->recordacademico->getInformacionGeneral($ci);
                $today = date("d/m/Y");
                $new_pk = $this->ugs->addRow($dataRow);
                $data = Array('module' => 'transactions',
                          'controller' => 'Materiasaretirar',
                          'params' => Array('set' => 'true',
                                            'action' => 'agregar',
                                            'permit' => true,
                                            'fk_ugs' => $new_pk));
                $this->SwapBytes_Crud_Form->getAddOrEditEnd($data);
            }
            // Retiro Semestre 
            else {
                $record = $this->recordacademico->getList_record_especifico($ultimoperiodo, $sede[0]['fk_estructura'], $escuela, $this->authSpace->userId);
                $retiro = $this->recordacademico->retirarSemestre($this->authSpace->userId, $ultimoperiodo, $escuela, $sede[0]['fk_estructura'], $pensum[0]['fk_pensum'], true);

                $new_pk = $this->ugs->addRow($dataRow);
                $materiaretirar['fk_usuariogruposolicitud'] = $new_pk;

                if(is_array($retiro) && $retiro != false){
                    foreach($retiro as $materia){
                        $materiaretirar['fk_recordacademico'] = $materia['pk_recordacademico'];
                        $this->mar->addRow($materiaretirar);
                    }
                    
                }
                $this->SwapBytes_Crud_Form->getAddOrEditEnd($data);
            }
            
        }
    }

        public function modformAction(){
            if ($this->_request->isXmlHttpRequest()) {
            	$this->SwapBytes_Ajax->setHeader();
                $elements = $this->_getParam('data');
                $ci = $this->authSpace->userId;
               // if($elements == 8268){ //local
                if ($elements == 8247) {
                    //$pagocompleto = $this->CmcBytes_Profit->checkPagoSemCompleto(19022630);
                    //$pagocompleto = $this->CmcBytes_Profit->checkPagoSemCompleto($ci);
                        //$this->SwapBytes_Crud_Form->getDialog('¿Desea retirar la materia?', $message, swOkOnly);
                        $json[] = $this->showSemestre();
                    //$this->logger->log($pagocompleto,ZEND_LOG::ALERT);
                    //$this->SwapBytes_Crud_Form->getDialog('¿Desea retirar la materia?', $message, swOkOnly);
                } else {
                    $json[] =$this->getinfo(true);
                }
                $this->getResponse()->setBody(Zend_Json::encode($json));
                 //$this->SwapBytes_Crud_Form->setJson($json);
            }
        }

        public function materiasAction() {
            if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $pk = $this->_getParam('pk');
            $solvente = $this->CmcBytes_Profit->getSolvente($this->authSpace->userId);
            //$solvente = true;
            if($this->mar->countRetiradas($pk) == 0 && $solvente == true){
                $permit = true;
            }else{
                $permit = false;
            }
            $data = Array('module' => 'transactions',
                          'controller' => 'Materiasaretirar',
                          'params' => Array('set' => 'true',
                                            'action' => 'listar',
                                            'permit' => $permit,
                                            'fk_ugs' => $pk));
            $json[] = $this->CmcBytes_Redirect->getRedirect($data);
            $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }

        public function existsAction(){
            if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $querystring = $this->_getParam('data');
            $params = $this->SwapBytes_Uri->queryToArray($querystring);
            $articulos = $this->CmcBytes_Profit->getArticulosPago(16880180,$params['pago']);
            $html = "<ul>";
            foreach($articulos as $art){
                foreach($art as $t){

					SWITCH (trim($t)){

						CASE 'CERTPENSUM':
							$documento = 'Certificación de Pensum';
							$html .= "<li>". $documento ."</li>";
							break;
						CASE 'CERTTITULO':
							$documento = 'Certificación de Título';
							$html .= "<li>". $documento ."</li>";
							break;
						CASE 'CERTPROGRAMAS':
							$documento = 'Certificación de Programas';
							$html .= "<li>". $documento ."</li>";
							break;

						default :
							$html = "No existen documentos asociados al numero de pago";
							$close = false;
							break;
					}
                }
            }
            if($cose != false){
            	$html .= "</ul>";
            }
            $json[] = $this->SwapBytes_Jquery->setHtml('documentos', $html);
            $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }

        public function imprimirAction(){
            //$this->SwapBytes_Ajax->setHeader();
                $pk = $this->_getParam('pk');
                $config = Zend_Registry::get('config');
				$dbname = $config->database->params->dbname;
				$dbuser = $config->database->params->username;
				$dbpass = $config->database->params->password;
				$dbhost = $config->database->params->host;
				$report = APPLICATION_PATH . '/modules/reports/templates/retiromaterias/retiromaterias.jasper';
				$imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
				$filename    = 'ConstanciadeRetiro';
				$filetype    = 'PDF';//strtolower($Params['rdbFormat']);
				$params      = "'Solicitud=string:{$pk}|Imagen=string:{$imagen}'";
				$cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                        // local -Djava.awt.headless=true
                Zend_Layout::getMvcInstance()->disableLayout();
                Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype }");
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
                $outstream = exec($cmd);
                echo base64_decode($outstream);
        }
	}

?>
