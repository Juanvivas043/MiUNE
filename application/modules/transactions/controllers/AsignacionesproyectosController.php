<?php


class Transactions_AsignacionesproyectosController extends Zend_Controller_Action {

  private $_title = 'Transacciones \ Asignación de Proyectos';

  public function init() {
	/* Initialize action controller here */
	Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Asignacionesproyectos');
        Zend_Loader::loadClass('Models_DbTable_Proyectos');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Inscripcionespasantias');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
	Zend_Loader::loadClass('Forms_Asignacionproyecto');
	Zend_Loader::loadClass('Models_DbView_Escuelas');

	$this->asignacionesproyectos = new Models_DbTable_Asignacionesproyectos();
        $this->proyectos = new Models_DbTable_Proyectos();
        $this->atributos = new Models_DbTable_Atributos();
        $this->inscripcionespasantias = new Models_DbTable_Inscripcionespasantias();
        $this->grupo = new Models_DbTable_UsuariosGrupos();
        $this->vEscuela = new Models_DbView_Escuelas();
	$this->filtros = new Une_Filtros();

	$this->SwapBytes_Ajax = new SwapBytes_Ajax();
	$this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
	$this->SwapBytes_Crud_Action = new SwapBytes_Crud_Action();
	$this->SwapBytes_Crud_List = new SwapBytes_Crud_List();
	$this->SwapBytes_Crud_Form = new SwapBytes_Crud_Form();
	$this->SwapBytes_Crud_Search = new SwapBytes_Crud_Search();
	$this->SwapBytes_Date = new SwapBytes_Date();
	$this->SwapBytes_Form = new SwapBytes_Form();
	$this->SwapBytes_Html_Message = new SwapBytes_Html_Message();
	$this->SwapBytes_Uri = new SwapBytes_Uri();
	$this->SwapBytes_Jquery = new SwapBytes_Jquery();
	$this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
   

	/*
	 * Obtiene los parametros de los filtros y del modal.
	 */
	$this->_params['filters'] = $this->filtros->getParams();
	$this->_params['modal'] = $this->SwapBytes_Crud_Form->getParams();

	/*
	 * Configuramos los filtros.
	 */
	$this->authSpace = new Zend_Session_Namespace('Zend_Auth');

	$this->filtros->setDisplay(true, true, true, false, false, false, false, false, false);
	$this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
	$this->filtros->setRecursive(false, true, true, false, false, false, false, false, false);
	$this->filtros->setType('seccion', FILTER_TYPE_SECCION_PADRES);

	$this->SwapBytes_Crud_Action->setDisplay(true, true, true);
	$this->SwapBytes_Crud_Action->setEnable(true, true, true);
	$this->SwapBytes_Crud_Search->setDisplay(false);
	/*
	 * Mandamos a crear el formulario para ser utilizado mediante el AJAX.
	 */
	$this->view->form = new Forms_Asignacionproyecto();

	$proyectos = $this->proyectos->getProyectos();  
    $tiposdehorario = $this->atributos->gettipohorario();
        
	  $this->SwapBytes_Form->set($this->view->form);
	  // poner el select box de proyectos y de tipohorario  
          $this->SwapBytes_Form->fillSelectBox('fk_proyecto', $proyectos  , 'pk_proyecto', 'nombre');
          $this->SwapBytes_Form->fillSelectBox('fk_tipohorario', $tiposdehorario  , 'pk_atributo', 'tipohorario');
	  $this->view->form = $this->SwapBytes_Form->get();

	  // Definimos valores por defecto provenientes de la DB cuando
	  // estamos agregando, estos valores se definen segun los datos del filtro:
	  if ($this->_params['modal']['id'] == 0) {
		$this->_params['default']['fk_proyecto']    = $proyectos[0]['pk_proyecto'];
		$this->_params['default']['fk_tipohorario'] = $tiposdehorario[0]['pk_atributo'];
	  }

  }

  public function preDispatch() {
	if (!Zend_Auth::getInstance()->hasIdentity()) {
	  $this->_helper->redirector('index', 'login', 'default');
	}

	if (!$this->grupo->haveAccessToModule()) {
	  $this->_helper->redirector('accesserror', 'profile', 'default');
	}
  }

  public function indexAction() {
	$this->view->title = $this->_title;
	$this->view->filters = $this->filtros;
	$this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
	$this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
	$this->view->SwapBytes_Crud_Form = $this->SwapBytes_Crud_Form;
	$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
	$this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
	$this->view->SwapBytes_Ajax->setView($this->view);
	$this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
  }

  public function sedeAction() {
	$this->filtros->getAction();
  }

  public function escuelaAction() {
	$this->filtros->getAction();
  }

  public function listAction() {
	if ($this->_request->isXmlHttpRequest()) {
	  $this->SwapBytes_Ajax->setHeader();

//			$json = $this->filtros->getJson();

	  //$this->asignacionesproyectos->setData($this->_params['filters'], array('periodo', 'sede', 'escuela'));
	  $rows = $this->asignacionesproyectos->getRows($this->_params['filters']['periodo'],$this->_params['filters']['escuela'],$this->_params['filters']['sede']);
	  if (isset($rows) && count($rows) > 0) {

	    $table = array('class' => 'tableData',
	                   'width' => '800px');

		$columns = array(array('column' => 'pk_asignacionproyecto',
				'primary' => true,
				'hide' => true),
			array('name' => 'proyectos',
				'width' => '300px',
				'column' => 'proyecto',
				'rows' => array('style' => 'text-align:center')),
			array('name' => 'Horario',
				'width' => '200px',
				'column' => 'horario',
				'rows' => array('style' => 'text-align:center')),
			array('name' => 'cupos',
				'width' => '50px',
				'column' => 'cupos',
				'rows' => array('style' => 'text-align:center')),
			array('name' => 'Inscritos',
				'width' => '50px',
				'column' => 'inscritos',
				'rows' => array('style' => 'text-align:center')));

		$HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VUD');
		$json[] = $this->SwapBytes_Jquery->setHtml('tblPasantias', $HTML);
		$json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkAsignacionproyecto');
	  } else {
		$HTML = $this->SwapBytes_Html_Message->alert("No existen Proyectos Asignados en este periodo.");
		$json[] = $this->SwapBytes_Jquery->setHtml('tblPasantias', $HTML);
	  }
	  $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnAdd', false);
	  $this->getResponse()->setBody(Zend_Json::encode($json));
	}
  }

  /**
   * Paso 1
   * aoel: AddOrEditLoad
   */
  public function addoreditloadAction() {
	  // $this->SwapBytes_Ajax->setHeader();
	$json = array();

    	if (is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
	  // Editar
	  $title = 'Editar Asignacion de Pasantia';
	  $dataRow = $this->asignacionesproyectos->getRow($this->_params['modal']['id']);
	  $dataRow['id'] = $this->_params['modal']['id'];
	} else {
	  // Agregar
	  $title = 'Agregar Asignacion de Pasantia';
	}

	$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
	$this->SwapBytes_Crud_Form->setJson($json);
	$this->SwapBytes_Crud_Form->setWidthLeft('80px');
	$this->SwapBytes_Crud_Form->getAddOrEditLoad(); 
  }

  /**
   * Paso 2
   * aoec: AddOrEditConfirm
   */
  public function addoreditconfirmAction() {
	if ($this->_request->isXmlHttpRequest()) {
	  $this->SwapBytes_Ajax->setHeader();

	  $message = '';

	  $this->asignacionesproyectos->setData($this->_params['filters'], array('periodo', 'sede', 'escuela'));
	  $rows = $this->asignacionesproyectos->getRowsPk();
	  $proyecto=$this->_params['modal']['fk_proyecto'];
	  $queryString = $this->_getParam('filters');
      $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);
      $estructura = $queryArray['selSede'];
      $escuela = $queryArray['selEscuela'];
      $escuelaname=$this->vEscuela->getEscuelaName($escuela);
	  // foreach ($rows as $key => $value) {

	  // 	if (in_array($proyecto,$value)) {
	  // 		$message = "<b>El proyecto ya existe en el periodo ".$this->_params['filters']['periodo']." y en la escuela ".$escuelaname."</b>";
			// $this->SwapBytes_Crud_Form->getDialog('No se puede agregar o modificar', $message);
	  // 	}
	  // }
	  $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
	  $this->SwapBytes_Crud_Form->setWidthLeft('80px');
	  $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
	}
  }

  /**
   * Paso 3
   * aoer: AddOrEditResponce
   */
  public function addoreditresponseAction() {
	if ($this->_request->isXmlHttpRequest()) {
	  $this->SwapBytes_Ajax->setHeader();

	  $id = $this->_params['modal']['id'];
	  $this->_params['modal']['fk_periodo'] = $this->_params['filters']['periodo'];
      $this->_params['modal']['fk_escuela'] = $this->_params['filters']['escuela'];
      $this->_params['modal']['fk_estructura'] = $this->_params['filters']['sede'];
	  $this->_params['modal']['filters'] = null;
	  $this->_params['modal']['id'] = null;

	  if (is_numeric($id) && $id > 0) {
		$this->asignacionesproyectos->updateRow($id, $this->_params['modal']);
	  } else {
		$this->asignacionesproyectos->addRow($this->_params['modal']);
	  }
	  $this->SwapBytes_Crud_Form->getAddOrEditEnd();
	}
  }

  public function viewAction() {

   
	$dataRow = $this->asignacionesproyectos->getRow($this->_params['modal']['id']);


	$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Asignacion Pasantias');
	$this->SwapBytes_Crud_Form->setWidthLeft('80px');
	$this->SwapBytes_Crud_Form->getView();
  }

  public function deleteloadAction() {
	$message = '';
	$permit = true;
	$dataRow = $this->asignacionesproyectos->getRow($this->_params['modal']['id']);
	$dataRow['id'] = $this->_params['modal']['id'];

	$inscritos = $this->inscripcionespasantias->getCountByAsignacionPasantias($dataRow['pk_asignacionproyecto']);

	if ($inscritos > 0) {
	  $message .= "<b>No se podra eliminar la asignación por que existen datos Estudiantes inscritos</b><br>";
	  $permit = false;
	}

	$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Asignación de Pasantias', $message);
	$this->SwapBytes_Crud_Form->setWidthLeft('80px');
	$this->SwapBytes_Crud_Form->getDeleteLoad($permit);
  }

  public function deletefinishAction() {
	$this->asignacionesproyectos->deleteRows($this->_params['modal']['id']);

	$this->SwapBytes_Crud_Form->setProperties($this->view->form);
	$this->SwapBytes_Crud_Form->getDeleteFinish();
  }


}
