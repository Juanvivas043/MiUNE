<?php

class Transactions_listanegraController extends Zend_Controller_Action {


    private $Title = 'Transacciones \ Lista Negra Sorteo';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Sorteo');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Listadosorteo');

        Zend_Loader::loadClass('Forms_Listanegra');

        $this->sorteo    = new Models_DbTable_Sorteo();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->atributos = new Models_DbTable_Atributos();
        $this->usuario           = new Models_DbTable_Usuarios();
        $this->periodos           = new Models_DbTable_Periodos();
        $this->ls                 = new Models_DbTable_Listadosorteo();

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
        $this->SwapBytes_Html           = new SwapBytes_Html();

        $this->logger = Zend_Registry::get('logger');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->redirect_session = new Zend_Session_Namespace('redirect_session');

        //filtro
        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(true, true, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);


        $Regreso = "<button id='btnReturn' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnReturn' role='button' aria-disabled='false'>Regresar";

        $this->SwapBytes_Crud_Action->addCustum($Regreso);

        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, false, true, true, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, false, true, true, false, false);


        $this->SwapBytes_Crud_Search->setDisplay(false);
        $this->view->form = new Forms_listanegra();
        $this->SwapBytes_Form->set($this->view->form);
        


        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->filtros->getParams();
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
            $this->view->SwapBytes_Ajax->setView($this->view);
        }

        public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $Data   = $this->filtros->getParams();
            $json   = array();

            $rows = $this->ls->getBanned();

            //$this->logger->log($rows,ZEND_LOG::ALERT);


            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_listadosorteo',
                                       'primary' => true,
                                        'hide' => true),
                                  array('name'    => array('control' => array('tag'        => 'input',
                                                                             'type'       => 'checkbox',
                                                                             'name'       => 'chkSelectDeselect')),
                                       'column'  => 'nc',
                                       'width'   => '20px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'control' => array('tag'   => 'input',
                                                          'type'  => 'checkbox',
                                                          'name'  => 'chkClase',
                                                          'value' => '##pk_listadosorteo##')),
                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'function' => 'rownum',
                                       'rows'    => array('style' => 'text-align:right')),
                                 array('name'    => 'Cédula',
                                       'width'   => '80px',
                                       'column'  => 'cedula',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Nombre',
                                       'width'   => '150px',
                                       'column'  => 'nombre',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Apellido',
                                       'width'   => '150px',
                                       'column'  => 'apellido'),
                                
                                 );


                

                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'D',$other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkClase');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen estuidantes en la lista negra.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }


            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }

        public function addoreditloadAction() {

		if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {

                    //$dataRow       = $this->sorteo->getRow($this->_params['modal']['id']);
                    $dataRow = $this->sorteo->getall($this->_params['modal']['id']);
                    $dataRow['id'] = $this->_params['modal']['id'];
                    $titulo = 'Editar registro';
                }else{

                    
                    $titulo = 'Agregar Registro';
                }
                        //$dataRow['fechainicio'] = $this->SwapBytes_Date->convertToForm($dataRow[0]['fechainicio']);
                        //$dataRow['fechafin'] = $this->SwapBytes_Date->convertToForm($dataRow[0]['fechafin']);
                        //$dataRow['fechatope'] = $this->SwapBytes_Date->convertToForm($dataRow[0]['fechatope']);

                $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 280) .';';
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar').';';

                $this->SwapBytes_Crud_Form->setJson($json);

        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $titulo);
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();


        }

        public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();



                        $dataRow       = $this->_params['modal'];
                        $dataRow['cedula'] = $this->redirect_session->cedula;
                        $info = $this->grupo->getUsuariogrupoEst($dataRow['cedula']);
                        $dataRow['ced'] = $info;
                        
                        
                        $this->logger->log($dataRow,ZEND_LOG::ALERT);
                        $this->validateElementsmsg($dataRow);
                        $valid = $this->validateElements($dataRow);
                        $this->logger->log($valid,ZEND_LOG::INFO);
                        $this->SwapBytes_Crud_Form->setJson($json);
                        
                        if($valid == true){
                            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow);
                            $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
                        }


                        


		}
	}

        public function addoreditresponseAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();


			// Obtenemos los parametros que se esperan recibir.
			$dataRow                  = $this->_params['modal'];
                        $ci = $this->redirect_session->cedula;
                        $this->redirect_session->unsetAll();
                        $info = $this->grupo->getUsuariogrupoEst($ci);
                        $this->logger->log($info,ZEND_LOG::WARN);



                        if(isset($info[0])){
                           $data['fk_usuariogrupo'] = $info[0]['pk_usuariogrupo'];
                           $data['fk_listado'] = 9689;
                           $this->ls->addRow($data);

                        }

			

			$this->SwapBytes_Crud_Form->getAddOrEditEnd();

		}
        }

        public function deleteloadAction() {
         $this->SwapBytes_Ajax->setHeader();
	$message = 'Seguro desea elmimnar el registro?:';
	$permit = true;
        $params = $this->_params['modal'];



        if(!isset($params['chkClase'])){
            $data = $this->ls->getBannedRow($params['id']);
            $dataRow['cedula'] = $data[0]['cedula'];
            $dataRow['id'] = $params['id'];
            $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(Array('cedula-element','cedula-label'));
            //$this->logger->log($dataRow,ZEND_LOG::ALERT);
        }else{

            if(is_array($params['chkClase'])) {
            
                $message = 'Seguro que desea eliminar ' . count($params['chkClase']) . ' elementos?';
                $msg = true;
                $dataRow['chkClase'] = implode(',', $this->_params['modal']['chkClase']);
                
                $json[] = $this->SwapBytes_Jquery_Ui_Form->open('frmModal');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideAllInputs('frmModal');
                $json[] = $this->SwapBytes_Crud_Form->setWidthLeft('80px');

            }else {
                $data = $this->ls->getBannedRow($this->_params['modal']['chkClase']);
                $dataRow['cedula'] = $data[0]['cedula'];
                $dataRow['id'] = $this->_params['modal']['chkClase'];
            }
        }

        

            
            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Registro', $message);
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->getDeleteLoad($permit,$msg);
        }

        public function deletefinishAction() {


            $params = $this->_params['modal'];
            $params['chkClase'] = explode(',',$params['chkClase']);
            //$this->logger->log($params,ZEND_LOG::ALERT);

            if(!is_numeric($params['id']) || $params['id'] == 0){
            
            if(is_array($params['chkClase'])){
            
                foreach($params['chkClase'] as $actividad) {
                    
                    $this->ls->deleteRow($actividad);


                    }

            }else{
                    
                   $this->ls->deleteRow($params['chkClase']);
            }

        }else {
            
            $this->ls->deleteRow($params['id']);
        }


            $this->ls->deleteRow($id);
            $this->SwapBytes_Crud_Form->setProperties($this->view->form);
            $this->SwapBytes_Crud_Form->getDeleteFinish();
        }

        private function validateElementsmsg($data){

              $msg = '';
              $json[] = $this->SwapBytes_Jquery_Ui_Form->cleanErrors();
              $json[] = "$('#data').html('');";
              //$this->logger->log($data,ZEND_LOG::WARN);
              if ($data['cedula'] == '' || $data['cedula'] == null){

                  $msg = 'Indique número de cédula';
                  $id = 'cedula';
                  $json[] = $this->SwapBytes_Jquery_Ui_Form->displayError($msg, $id);
              }elseif(!isset($data['ced'][0])){
                  $msg = 'Cédula invalida';
                  $id = 'cedula';
                  $json[] = $this->SwapBytes_Jquery_Ui_Form->displayError($msg, $id);

              }


              $this->getResponse()->setBody(Zend_Json::encode($json));
              $this->SwapBytes_Crud_Form->setJson($json);


          }

        private function validateElements($data){

              $msg = '';
              
              if ($data['cedula'] == '' || $data['cedula'] == null){
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
            //$this->logger->log($params['cedula'],ZEND_LOG::ALERT);
            $up = $this->periodos->getUltimo();
            $usu = $this->usuario->getInfoGeneral($params['cedula'],$up);

            $properties = array('width' => '255',
                                    'align' => 'center');

            $styles = array(array('style' => 'text-align:right;font-size:14px;font-weight:bold;'),array('style' => 'text-align:left;font-size:14px;'));

            if(!isset($usu[0])){
                $json[] = $this->SwapBytes_Jquery_Ui_Form->displayError('Cédula Invalida', 'cedula');
            }else{
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar').';';
                $this->redirect_session->cedula = $params['cedula'];
                $json[] = "$('#Lista').find(':input#cedula').attr('disabled','disabled');";
                $data[] = array('Nombre: ','  '. $usu[0]['nombre']);
                $data[] = array('Apellido: ','  '.$usu[0]['apellido']);
                $data[] = array('Escuela: ','  '.$usu[0]['escuela']);
                $data[] = array('Turno: ','  '.$usu[0]['valor']);

            }

            $html  = $this->SwapBytes_Html->table($properties, $data, $styles);
            
            $json[] = $this->SwapBytes_Jquery->setHtml('datos', $html);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 280) .';';
            

            $this->getResponse()->setBody(Zend_Json::encode($json));




            }

        }



        public function periodoAction() {
		$this->filtros->getAction();
        }

        public function sedeAction() {
		$this->filtros->getAction(array('periodo'));
        }

        public function participantesAction(){
            $this->SwapBytes_Ajax->setHeader();
            $pk = $this->_getParam('pk');
            //$this->logger->log($pk,ZEND_LOG::ALERT);

            $data = Array('module' => 'transactions',
                          'controller' => 'Participantes',
                          'params' => Array('set' => 'true',
                                            'action' => 'listar',
                                            'fk_ugs' => $pk));



            $json[] = $this->CmcBytes_Redirect->getRedirect($data);
            $this->getResponse()->setBody(Zend_Json::encode($json));

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

