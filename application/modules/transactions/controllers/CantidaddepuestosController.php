<?php

class Transactions_cantidaddepuestosController extends Zend_Controller_Action {


    private $Title = 'Transacciones \ Cantidad de Puestos';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Sorteo');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Vehiculos');
        Zend_Loader::loadClass('Models_DbTable_Usuariosvehiculos');
        Zend_Loader::loadClass('Models_DbTable_Puestosturnos');
        Zend_Loader::loadClass('Forms_Puestosturnos');

        $this->vehiculos = new Models_DbTable_Vehiculos();
        $this->sorteo    = new Models_DbTable_Sorteo();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->atributos = new Models_DbTable_Atributos();
        $this->usuvehiculos = new Models_DbTable_Usuariosvehiculos();
        $this->pt           = new Models_DbTable_Puestosturnos();
        //$this->vehiculos = new Models_DbTable_Vehiculos();

        $this->filtros         = new Une_Filtros();


        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->CmcBytes_Redirect        = new CmcBytes_Redirect();
        $this->logger = Zend_Registry::get('logger');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->vehiculo_session = new Zend_Session_Namespace('vehiculo_session');
        $this->redirect_session = new Zend_Session_Namespace('redirect_session');
        //filtro
        $this->filtros->setDisplay(true, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, true, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(true, false, false, false, false, false, false, false, false);

        $Regreso = "<button id='btnReturn' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnReturn' role='button' aria-disabled='false'>Regresar";

        $this->SwapBytes_Crud_Action->addCustum($Regreso);

        $this->SwapBytes_Crud_Search->setDisplay(false);
        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, false, true, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, false, true, false, false, false);


        $this->view->form = new Forms_Puestosturnos();
        $this->SwapBytes_Form->set($this->view->form);

        $this->SwapBytes_Form->fillSelectBox('tipo', $this->atributos->getSelect(69) , 'pk_atributo', 'valor');
        $this->SwapBytes_Form->fillSelectBox('turno', $this->atributos->getSelect(2,'valor','pk_atributo',"'N/A'") , 'pk_atributo', 'valor'); //cambiar por las motos
        //$this->SwapBytes_Form->fillSelectBox('marca', $this->atributos->getSelect(68) , 'pk_atributo', 'valor');
        //$this->SwapBytes_Form->fillSelectBox('modelo', $this->atributos->getChilds(11468) , 'pk_atributo', 'valor');

        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->filtros->getParams();
        $this->_params['exists'];
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
            $this->view->search_span           = 3;
            $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
            $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
            $this->view->SwapBytes_Ajax->setView($this->view);
        }


        public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $Data   = $this->filtros->getParams();
            $json   = array();
            $ci = $this->authSpace->userId;
            $periodo = $this->_params['filters']['periodo'];

            $rows = $this->pt->getPuestosTurnos($periodo);
            $this->logger->log($rows,ZEND_LOG::ALERT);

            if(isset($rows) && count($rows) > 0) {

                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_puestoturno',
                                       'primary' => true,
                                        'hide' => true),
                                 array('name' => array('control' => array('tag' => 'input',
                                                'type' => 'checkbox',
                                                'name' => 'chkSelectDeselect')),
                                        'width' => '30px',
                                        'column' => 'accion',
                                        'rows' => array('style' => 'text-align:center'),
                                        'control' => array('tag' => 'input',
                                            'type' => 'checkbox',
                                            'name' => 'chkRecordAcademico',
                                            'id' => 'chkRecordAcademico',
                                            'value' => '##pk_usuariovehiculo##')),
                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'function' => 'rownum',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Tipo',
                                       'width'   => '120px',
                                       'column'  => 'tiposorteo',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Turno',
                                       'width'   => '70px',
                                       'column'  => 'turno',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Cantidad',
                                       'width'   => '70px',
                                       'column'  => 'cantidad',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Inscritos',
                                       'width'   => '70px',
                                       'column'  => 'inscritos',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Seleccionados',
                                       'width'   => '70px',
                                       'column'  => 'seleccionados',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Restantes',
                                       'width'   => '70px',
                                       'column'  => 'restantes',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Pagos',
                                       'width'   => '70px',
                                       'column'  => 'pago',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Por Pagar',
                                       'width'   => '90px',
                                       'column'  => 'porpagar',
                                       'rows'    => array('style' => 'text-align:center')),

                                 );


//                $other = Array(
//                         Array( 'actionName' => 'subactividades',
//                                'action' => 'subactividades(##pk##)',
//                                'label' => 'Participantes',
//                                ),
//
//                         Array( 'actionName' => 'subactividades',
//                                'action' => 'subactividades(##pk##)',
//                                'label' => 'Publicar',
//                                )

//                );

                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'UD',$other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkClase');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen registros para mostrar.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }


            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }

        public function addoreditloadAction() {

        if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
            
            $dataRow       = $this->pt->getRow($this->_params['modal']['id']);
            $dataRow['id'] = $this->_params['modal']['id'];
            $dataRow['tipo'] = $dataRow['fk_tiposorteo'];
            $dataRow['turno'] = $dataRow['fk_turno'];
            $titulo = 'Editar cantidad de Puestos';

        }else{
            
                $dataRow = $this->_params['modal'];

                $titulo = 'Cantidad de Puestos';


                
            
        }

        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $titulo);
                $this->SwapBytes_Crud_Form->getAddOrEditLoad();

        }

        public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

			$dataRow       = $this->_params['modal'];
                        $periodo = $this->_params['filters']['periodo'];
                        $tipo = $dataRow['tipo'];
                        $turno = $dataRow['turno'];
                 if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] <= 0) {

                        $exists = $this->pt->existTurnoPeriodo($turno, $periodo, $tipo);
                 }

                        if(count($exists) >= 1){
                            $mensaje = 'ya existe un registro para el turno y tipo de sorteo en el periodo seleccionado.';
                            $this->SwapBytes_Crud_Form->getDialog('Error', $mensaje, swOkOnly);
                        }

			$this->SwapBytes_Crud_Form->setJson($json);

                            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
                            $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
		}
	}

        public function addoreditresponseAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

                        
			// Obtenemos los parametros que se esperan recibir.

            if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
                $this->logger->log($this->_params['modal'],ZEND_LOG::WARN);
                $dataRow                  = $this->_params['modal'];
                $dataRow['fk_tiposorteo'] = $dataRow['tipo'];
                $dataRow['fk_periodo'] = $this->_params['filters']['periodo'];
                $dataRow['fk_turno'] = $dataRow['turno'];
                $id = $dataRow['id'];
                $dataRow['id'] = null;
                $dataRow['tipo'] = null;
                $dataRow['turno'] = null;
                $this->pt->updateRow($id, $dataRow);
            }else{

			$dataRow                  = $this->_params['modal'];
                        $this->logger->log($dataRow,ZEND_LOG::ALERT);

                        $dataRow['id'] == null;
                        $dataRow['fk_tiposorteo'] = $dataRow['tipo'];
                        $dataRow['tipo'] = null;
                        $dataRow['fk_turno'] = $dataRow['turno'];
                        $dataRow['turno'] = null;
                        $dataRow['fk_periodo'] = $this->_params['filters']['periodo'];
                        $this->pt->addRow($dataRow);
            }
			$this->SwapBytes_Crud_Form->getAddOrEditEnd();

		}
        }

        public function deleteloadAction() {
             $this->SwapBytes_Ajax->setHeader();


            $dataRow       = $this->pt->getRow($this->_params['modal']['id']);
            $dataRow['id'] = $this->_params['modal']['id'];
            $dataRow['tipo'] = $dataRow['fk_tiposorteo'];
            $dataRow['turno'] = $dataRow['fk_turno'];
            $titulo = 'Editar cantidad de Puestos';


             $this->logger->log($dataRow,ZEND_LOG::ALERT);
             $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Vehiculo', $message);
             $this->SwapBytes_Crud_Form->setJson($json);
             $this->SwapBytes_Crud_Form->setWidthLeft('80px');
             $this->SwapBytes_Crud_Form->getDeleteLoad(true);

         }

         public function deletefinishAction() {
            $dataRow = $this->_params['modal'];
            $this->logger->log($dataRow,ZEND_LOG::ALERT);

            $this->pt->deleteRow($dataRow['id']);

            $this->SwapBytes_Crud_Form->setProperties($this->view->form);
            $this->SwapBytes_Crud_Form->getDeleteFinish();
          }


        private function validateElementsmsg($data){

              $msg = '';
              $json[] = $this->SwapBytes_Jquery_Ui_Form->cleanErrors();
              $inicio = new Zend_Date($data['fechainicio'],'dd-MM-YYYY');
              $tope = new Zend_Date($data['fechatope'],'dd-MM-YYYY');
              $fin = new Zend_Date($data['fechafin'],'dd-MM-YYYY');

              if ($tope < $fin){

                  $msg = 'la Fecha Tope debe ser mayor o igual a la de culminacion';
                  $id = 'fechatope';
                  $json[] = $this->SwapBytes_Jquery_Ui_Form->displayError($msg, $id);
              }

              if($fin < $inicio){
                  $msg = 'la Fecha de culminacion debe ser mayor o igual a la de inicio';
                  $id = 'fechafin';
                  $json[] = $this->SwapBytes_Jquery_Ui_Form->displayError($msg, $id);
              }

              $this->getResponse()->setBody(Zend_Json::encode($json));
              $this->SwapBytes_Crud_Form->setJson($json);


          }

        private function validateElements($data){

              $msg = '';
              //$jr = $this->SwapBytes_Jquery_Ui_Form->cleanErrors();
              //$this->getResponse()->setBody(Zend_Json::encode($json));
              $inicio = new Zend_Date($data['fechainicio'],'dd-MM-YYYY');
              $tope = new Zend_Date($data['fechatope'],'dd-MM-YYYY');
              $fin = new Zend_Date($data['fechafin'],'dd-MM-YYYY');

              if ($tope < $fin || $fin < $inicio){
              //$this->logger->log('dsdsd',ZEND_LOG::ALERT);
                  return false;
              }else{
                  return true;
              }

              //$this->SwapBytes_Crud_Form->setJson($json);


          }

        public function existsAction(){
            if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $querystring = $this->_getParam('data');
            $params = $this->SwapBytes_Uri->queryToArray($querystring);

            $params['placa'] = str_replace("-","",$params['placa']);

            $exists = $this->vehiculos->getRowByPlaca($params['placa']);
            $this->logger->log($exists,ZEND_LOG::ALERT);
            if(isset($exists[0]['pk_vehiculo'])){

                $this->vehiculo_session->exists = true;
                $this->SwapBytes_Form->fillSelectBox('tipo', $this->atributos->getByPk($exists[0]['fk_tipo']) , 'pk_atributo', 'valor');
                $this->SwapBytes_Form->fillSelectBox('marcamotos', $this->atributos->getByPk($exists[0]['fk_modelo']) , 'pk_atributo', 'valor'); //motos
                if(isset($exists[0]['marca_pk'])){

                    $this->SwapBytes_Form->fillSelectBox('marca', $this->atributos->getByPk($exists[0]['marca_pk']) , 'pk_atributo', 'valor');

                }
                $this->SwapBytes_Form->fillSelectBox('modelo', $this->atributos->getByPk($exists[0]['fk_modelo']) , 'pk_atributo', 'valor');
                $rowData['id'] = $exists[0]['pk_vehiculo'];
                $rowData['marca'] = $exists[0]['marca_pk'];
                $rowData['marcamotos'] = $exists[0]['fk_modelo'];
                $rowData['modelo'] = $exists[0]['fk_modelo'];
                $rowData['tipo'] = $exists[0]['fk_tipo'];
                $rowData['ano'] = $exists[0]['ano'];
                $rowData['placa'] = $exists[0]['placa'];
            }else{
                $this->vehiculo_session->exists = false;
            }



            if(isset($rowData)){
                $this->SwapBytes_Form->set($this->view->form);
                $this->view->form->populate($rowData);

                $this->view->form = $this->SwapBytes_Form->get();
                 $html  = $this->SwapBytes_Ajax->render($this->view->form);
                $html  = $this->SwapBytes_Ajax->render($this->view->form);

                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                //$json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 400) .';';
            }

            if(isset($exists[0]['pk_vehiculo']) && isset($exists[0]['marca_pk'])){
                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('marcamotos-label','marcamotos-element')).';';
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('marca-label','marca-element',
                                                                                'modelo-label','modelo-element')).';';
            }elseif(isset($exists[0]['pk_vehiculo'])){
                $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('marcamotos-label','marcamotos-element')).';';
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('marca-label','marca-element',
                                                                                'modelo-label','modelo-element')).';';
            }

            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar').';';

            //$this->SwapBytes_Crud_Form->setJson($json);
            $this->getResponse()->setBody(Zend_Json::encode($json));

            }

        }

        public function tipoAction(){
            $this->SwapBytes_Ajax->setHeader();
            $data = $this->_getParam('data');
            $params = $this->SwapBytes_Uri->queryToArray($data);

            if($params['tipo'] == 8235){

                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('marca-label','marca-element',
                                                                                'modelo-label','modelo-element')).';';

                $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('marcamotos-label','marcamotos-element')).';';
                $this->logger->log($json,ZEND_LOG::ALERT);
            }else{

                $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('marca-label','marca-element',
                                                                                'modelo-label','modelo-element')).';';
                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('marcamotos-label','marcamotos-element')).';';

            }


            $this->getResponse()->setBody(Zend_Json::encode($json));

        }

        public function modelosAction(){
            $data = $this->atributos->getChilds($this->_getParam('marca'));
            $this->SwapBytes_Ajax_Action->fillSelect($data);

        }

        public function periodoAction() {
		$this->filtros->getAction();
        }

        public function sedeAction() {
		$this->filtros->getAction(array('periodo'));
        }

        public function regresoAction(){
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();
                $this->redirect_session->unsetAll();

                $data = Array('module' => 'transactions',
                              'controller' => 'gestiondesorteos',
                              'params' => Array('set' => 'true',
                                                'action' => 'listar'
                                                ));



                $json[] = $this->CmcBytes_Redirect->getRedirect($data);
                $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }

}


?>


