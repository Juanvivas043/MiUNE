<?php

class Transactions_GestiondesorteosController extends Zend_Controller_Action {


  private $Title = 'Transacciones \ Gestion de Sorteos';

  public function init() {

    Zend_Loader::loadClass('Une_Filtros');
    Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
    Zend_Loader::loadClass('Models_DbTable_Sorteo');
    Zend_Loader::loadClass('Models_DbTable_Atributos');
    Zend_Loader::loadClass('Models_DbTable_Periodos');
    Zend_Loader::loadClass('Forms_Sorteo');

    $this->sorteo    = new Models_DbTable_Sorteo();
    $this->grupo     = new Models_DbTable_UsuariosGrupos();
    $this->atributos = new Models_DbTable_Atributos();
    $this->periodos  = new Models_DbTable_Periodos();
    $this->filtros   = new Une_Filtros();

    $this->Request = Zend_Controller_Front::getInstance()->getRequest();

    $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
    $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
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
    $this->redirect_session = new Zend_Session_Namespace('redirect_session');

    //filtro
    $this->filtros->setDisplay(true, false, false, false, false, false, false, false, false);
    $this->filtros->setDisabled(false, true, true, true, true, true, true, true, true);
    $this->filtros->setRecursive(true, false, false, false, false, false, false, false, false);

    $listas = "<button id='btnListas' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnReturn' role='button' aria-disabled='false'>Lista Negra";
    $puestos = "<button id='btnPuestos' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnPuestos' role='button' aria-disabled='false'>Puestos";

    $this->SwapBytes_Crud_Action->addCustum($puestos);
    $this->SwapBytes_Crud_Action->addCustum($listas);
    //botones
    $this->SwapBytes_Crud_Action->setDisplay(true, false, true, false, false, false);
    $this->SwapBytes_Crud_Action->setEnable(true, false, true, false, false, false);

    //$this->SwapBytes_Crud_Action->addCustum($puestos);
    //$this->SwapBytes_Crud_Action->addCustum($listas);

    $this->SwapBytes_Crud_Search->setDisplay(false);
    $this->view->form = new Forms_Sorteo();
    $this->SwapBytes_Form->set($this->view->form);
    $this->SwapBytes_Form->fillSelectBox('fk_tiposorteo', $this->atributos->getSelect(69) , 'pk_atributo', 'valor');

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
    $this->view->search_span  = 4;
    $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
    $this->view->SwapBytes_Ajax->setView($this->view);
  }

  public function listAction() {
    if ($this->_request->isXmlHttpRequest()) {

      $this->SwapBytes_Ajax->setHeader();
      $Data   = $this->filtros->getParams();
      $json   = array();
      $other = array('cedula' => $this->authSpace->userId,
                      'grupo' => 855);

      $rows = $this->sorteo->getSorteos($Data);

      //$this->logger->log($rows,ZEND_LOG::ALERT);
      
      if(isset($rows) && count($rows) > 0) {
          $table = array('class' => 'tableData');

          $columns = array(array('column'  => 'pk_sorteo',
                                 'primary' => true,
                                  'hide' => true),
                           array('name'     => '#',
                                 'width'    => '20px',
                                 'function' => 'rownum',
                                 'rows'    => array('style' => 'text-align:right')),
                           array('name'    => 'Descripción',
                                 'width'   => '80px',
                                 'column'  => 'descripcion',
                                 'rows'    => array('style' => 'text-align:center')),
                           array('name'    => 'Fecha de Inicio',
                                 'width'   => '70px',
                                 'column'  => 'fechainicio',
                                 'rows'    => array('style' => 'text-align:center')),
                           array('name'    => 'Fecha de Culminación',
                                 'width'   => '70px',
                                 'column'  => 'fechafin'),
                           array('name'    => 'Fecha del sorteo',
                                 'width'   => '70px',
                                 'column'  => 'fechasorteo',
                                 'rows'    => array('style' => 'text-align:center')),
                           array('name'    => 'Fecha tope de pago',
                                 'width'   => '70px',
                                 'column'  => 'fechatope',
                                 'rows'    => array('style' => 'text-align:center')),
                           array('name'    => 'Inscritos',
                                 'width'   => '70px',
                                 'column'  => 'inscritos',
                                 'rows'    => array('style' => 'text-align:center')),
                           array('name'    => 'Publicado',
                                 'width'   => '70px',
                                 'column'  => 'estado',
                                 'rows'    => array('style' => 'text-align:center')),
                           );

              $other = Array(
                       Array( 'actionName' => 'participantes',
                              'action' => 'participantes(##pk##)',
                              'label' => 'Participantes',
                              ),
                       
                       Array( 'actionName' => 'publicar',
                              'action' => 'publicar(##pk##)',
                              'label' => 'Publicar',
                              'column' => 'fechasorteo',
                              'validate' => 'true',
                              'intrue' => '',
                              'intruelabel' => '')                            
              );

              $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'UDO',$other);
              $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
              $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkClase');
          } 
          else {
              $HTML  = $this->SwapBytes_Html_Message->alert("No existen sorteos creados en el periodo.");

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
        $dataRow['fk_tiposorteo'] = $dataRow[0]['fk_tiposorteo'];
        $dataRow['administrativo']      = $this->SwapBytes_Form->setValueToBoolean($dataRow[0]['administrativo']);
        $titulo = 'Editar Sorteo';
    }
    else{
      $this->SwapBytes_Form->fillSelectBox('fk_tiposorteo', $this->atributos->getSelect(69) , 'pk_atributo', 'valor');
      $titulo = 'Agregar Sorteo';
    }

    $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fechainicio');
    $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fechafin');
    $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fechatope');
    $dataRow['fechainicio'] = $dataRow[0]['fecha_inicio'];
    $dataRow['fechafin']    = $dataRow[0]['fecha_fin'];
    $dataRow['fechatope']   = $dataRow[0]['fecha_tope'];
    $dataRow['descripcion'] = $dataRow[0]['descripcion'];
    $this->logger->log($dataRow,ZEND_LOG::ALERT);
      //$dataRow['fechainicio'] = $this->SwapBytes_Date->convertToForm($dataRow[0]['fechainicio']);
      //$dataRow['fechafin'] = $this->SwapBytes_Date->convertToForm($dataRow[0]['fechafin']);
      //$dataRow['fechatope'] = $this->SwapBytes_Date->convertToForm($dataRow[0]['fechatope']);

    $this->SwapBytes_Crud_Form->setJson($json);

    $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $titulo);
    $this->SwapBytes_Crud_Form->getAddOrEditLoad();
  }

  public function addoreditconfirmAction() {

    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();

      $dataRow       = $this->_params['modal'];

      $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fechainicio');
      $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fechafin');
      $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fechatope');
      $this->logger->log($dataRow,ZEND_LOG::ALERT);

      $this->SwapBytes_Crud_Form->setJson($json);

      $this->validateElementsmsg($this->_params['modal']);
      
      $this->SwapBytes_Crud_Form->setJson($json);

    
      if($this->validateElements($this->_params['modal']) == true){
          $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
          $this->SwapBytes_Crud_Form->getAddOrEditConfirm();  
      }
      else{
      }                 
	  }
  }

  public function addoreditresponseAction() {
    if ($this->_request->isXmlHttpRequest()) {

      $this->SwapBytes_Ajax->setHeader();
		// Obtenemos los parametros que se esperan recibir.

  		$dataRow                  = $this->_params['modal'];
      $dataRow['fk_periodo']    = $this->_params['filters']['periodo'];
  		$id                       = $dataRow['id'];
  		$dataRow['id']            = null;
  		$dataRow['filtro']        = null;
        //$inicio = new Zend_Date($dataRow['fechainicio'],'YYYY-MM-dd');
      $inicio = $this->SwapBytes_Date->convertToDataBase($dataRow['fechainicio']);
      //$tope = new Zend_Date($dataRow['fechatope'],'YYYY-MM-dd');
      $tope = $this->SwapBytes_Date->convertToDataBase($dataRow['fechatope']);
      //$fin = new Zend_Date($dataRow['fechafin'],'YYYY-MM-dd');
      $fin = $this->SwapBytes_Date->convertToDataBase($dataRow['fechafin']);

      $dataRow['fechainicio']   = $inicio;
      $dataRow['fechafin']      = $fin;
      $dataRow['fechatope']     = $tope;

      $this->SwapBytes_Crud_Form->setJson($json);

  		if(is_numeric($id) && $id > 0) {
  		  $this->sorteo->updateRow($id, $dataRow);
  		} 
      else{
  			$a = $this->sorteo->addRow($dataRow);
  		}

  		$this->SwapBytes_Crud_Form->getAddOrEditEnd();

  	}
  }

  public function deleteloadAction() {
    $permit = true;
    $dataRow = $this->sorteo->getall($this->_params['modal']['id']);
    $dataRow['id'] = $this->_params['modal']['id'];
    $dataRow['fk_tiposorteo'] = $dataRow[0]['fk_tiposorteo'];
    $titulo = 'Eliminar Sorteo';
    $dataRow['fechainicio'] = $dataRow[0]['fecha_inicio'];
    $dataRow['fechafin']    = $dataRow[0]['fecha_fin'];
    $dataRow['fechatope']   = $dataRow[0]['fecha_tope'];
    $dataRow['descripcion'] = $dataRow[0]['descripcion'];
    $dataRow['administrativo']      = $this->SwapBytes_Form->setValueToBoolean($dataRow[0]['administrativo']);

    $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Sorteo', $message);
    $this->SwapBytes_Crud_Form->setJson($json);
    $this->SwapBytes_Crud_Form->getDeleteLoad($permit,$msg);
  }

  public function deletefinishAction() {

    $id = $this->_params['modal']['id'];
    //recordar vaidar si tiene inscritos
    $this->sorteo->deleteRow($id);
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
    $inicio = new Zend_Date($data['fechainicio'],'YYYY-MM-dd');
    $tope = new Zend_Date($data['fechatope'],'dd-MM-YYYY');
    $fin = new Zend_Date($data['fechafin'],'dd-MM-YYYY');
    
    if ($tope < $fin || $fin < $inicio){
        return false;
    }
    else{
        return true;
    }
      //$this->SwapBytes_Crud_Form->setJson($json);
  }

  public function periodoAction() {

	  $this->filtros->getAction();

  }

  public function sedeAction() {

	  $this->filtros->getAction(array('periodo'));
      
  }

  public function listaAction(){

    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $this->redirect_session->unsetAll();

      $data = Array('module' => 'transactions',
                    'controller' => 'listanegra',
                    'params' => Array('set' => 'true',
                                      'action' => 'listar'
                                      ));

      $json[] = $this->CmcBytes_Redirect->getRedirect($data);
      $this->getResponse()->setBody(Zend_Json::encode($json));
    }
  }

  public function puestosAction(){

    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $this->redirect_session->unsetAll();

      $data = Array('module' => 'transactions',
                    'controller' => 'cantidaddepuestos',
                    'params' => Array('set' => 'true',
                                      'action' => 'listar'
                                      ));

      $json[] = $this->CmcBytes_Redirect->getRedirect($data);
      $this->getResponse()->setBody(Zend_Json::encode($json));
    }
  }

  public function participantesAction(){

    $this->SwapBytes_Ajax->setHeader();
    $pk = $this->_getParam('pk');
    $per = $this->sorteo->getRow($pk);
    $this->logger->log($pk,ZEND_LOG::ALERT);

    $data = Array('module' => 'transactions',
                  'controller' => 'participantes',
                  'params' => Array('set' => 'true',
                                    'action' => 'listar',
                                    'fk_ugs' => $pk,
                                    'per'    => $per['fk_periodo']));

    $json[] = $this->CmcBytes_Redirect->getRedirect($data);
    $this->getResponse()->setBody(Zend_Json::encode($json));
  }

  public function publicarAction(){

    $this->SwapBytes_Ajax->setHeader();
    $pk = $this->_getParam('pk');

    $today = date("Y-m-d");;

    $sorteo = $this->sorteo->getRow($pk);

    if($today < $sorteo['fechafin'] ){
      $message = 'La publicación de resultados debe realizarse una vez culminado el sorteo.';
      $this->SwapBytes_Crud_Form->getDialog('Error al publicar', $message, swOkOnly);
    }
    else{
      $data['fechasorteo'] = $today;
      $data['publicado'] = 't';
      $this->sorteo->publicar($pk, $data);//updateRow($pk, $data);
      $this->logger->log($pk,ZEND_LOG::ALERT);
      $json[]    = $this->SwapBytes_Jquery->getJSON('list', null, array('filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros')));
      $this->getResponse()->setBody(Zend_Json::encode($json));
    }
  }
}

?>

