<?php

class Transactions_InscripcionesempresasController extends Zend_Controller_Action {

  private $_title = 'Transacciones \ Inscripcion de Empresas';

  	public function init() {
		/* Initialize action controller here */
		Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Inscripcionespasantias');
        Zend_Loader::loadClass('Models_DbTable_Instituciones');
        Zend_Loader::loadClass('Models_DbTable_Contactos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
		Zend_Loader::loadClass('Forms_Inscripcionempresa');

        $this->inscripcionespasantias   = new Models_DbTable_Inscripcionespasantias();
		$this->empresas                 = new Models_DbTable_Instituciones();
        $this->contactos                = new Models_DbTable_Contactos();
        $this->usuariosgrupos           = new Models_DbTable_UsuariosGrupos();
		$this->filtros                  = new Une_Filtros();
        
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
		$this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        
		//	 * Obtiene los parametros de los filtros y del modal.
		$this->_params['filters']   = $this->filtros->getParams();
		$this->_params['modal']     = $this->SwapBytes_Crud_Form->getParams();

		//	 * Configuramos los filtros.
		$this->authSpace = new Zend_Session_Namespace('Zend_Auth');

		$this->filtros->setDisplay(true, true, true, false, false, false, false, false, false);
		$this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
		$this->filtros->setRecursive(false, true, true, false, false, false, false, false, false);
		$this->SwapBytes_Crud_Action->setDisplay(true, true, true);
		$this->SwapBytes_Crud_Action->setEnable(true, true, true);
		$this->SwapBytes_Crud_Search->setDisplay(false);

		//	 * Mandamos a crear el formulario para ser utilizado mediante el AJAX.

		$this->view->form = new Forms_Inscripcionempresa();

	   	if (isset($this->_params['filters']['periodo']) && isset($this->_params['filters']['sede']) && isset($this->_params['filters']['escuela'])) {

	        $empresas              = $this->empresas->getempresas();
	        if ($this->_params['modal']['id'] == 0) { 
	        	$estudiantes            = $this->inscripcionespasantias->getEstudiantesInscritospasantiaslaborales($this->_params['filters']['periodo'],$this->_params['filters']['escuela'], $this->_params['filters']['sede']);
	        }
	        $estudiantes            = $this->inscripcionespasantias->getEstudiantesInscritosLaboral($this->_params['filters']['periodo'],$this->_params['filters']['escuela'], $this->_params['filters']['sede']);
			$tutoresacademicos      = $this->usuariosgrupos->getTutoresAcademicos($this->_params['filters']['periodo']);  
	        $this->SwapBytes_Form->set($this->view->form);
			$this->SwapBytes_Form->fillSelectBox('fk_recordacademico', $estudiantes  , 'pk_recordacademico', 'estudiante');
			$this->SwapBytes_Form->fillSelectBox('fk_institucion', $empresas  , 'pk_institucion', 'nombre');
			$this->SwapBytes_Form->fillSelectBox('fk_tutor_academico', $tutoresacademicos  , 'pk_usuariogrupo', 'profesor');
		  	$this->view->form = $this->SwapBytes_Form->get();
	        if ($this->_params['modal']['id'] == 0) {
				$this->_params['default']['empresa'] = $empresas[0]['pk_institucion'];
	        }
	    }
  	}

  	public function preDispatch() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
		  $this->_helper->redirector('index', 'login', 'default');
		}

		if (!$this->usuariosgrupos->haveAccessToModule()) {
		  $this->_helper->redirector('accesserror', 'profile', 'default');
		}
  	}

  	public function indexAction() {
		$this->view->title                      = $this->_title;
		$this->view->filters                    = $this->filtros;
		$this->view->SwapBytes_Jquery           = $this->SwapBytes_Jquery;
		$this->view->SwapBytes_Crud_Action      = $this->SwapBytes_Crud_Action;
		$this->view->SwapBytes_Crud_Form        = $this->SwapBytes_Crud_Form;
		$this->view->SwapBytes_Crud_Search      = $this->SwapBytes_Crud_Search;
		$this->view->SwapBytes_Ajax             = new SwapBytes_Ajax();
		$this->view->SwapBytes_Ajax->setView($this->view);
		$this->view->SwapBytes_Jquery_Ui_Form   = new SwapBytes_Jquery_Ui_Form();
  	}

	public function sedeAction() {
		$this->filtros->getAction();
	}

	public function escuelaAction() {
		$this->filtros->getAction();
	}

	public function tutorAction(){
	    $data = $this->contactos->gettutoresempresarial($this->_getParam('empresa'));
	    $this->SwapBytes_Ajax_Action->fillSelect($data);
	}
        
	public function listAction() {
		if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();
			$this->inscripcionespasantias->setDataempresas($this->_params['filters'], array('periodo', 'sede', 'escuela'));
			$rows = $this->inscripcionespasantias->getInscripcionesempresas();
			if (isset($rows) && count($rows) > 0) {
		    	$table = array('class' => 'tableData',
		                         'width' => '1200px');
				$columns = array(array('column' => 'pk_inscripcionpasantia',
						'primary' => true,
						'hide' => true),
					array('name' => 'Cedula',
						'width' => '80px',
						'column' => 'ci',
						'rows' => array('style' => 'text-align:center')),
					array('name' => 'Estudiante',
						'width' => '300px',
						'column' => 'estudiante',
						'rows' => array('style' => 'text-align:center')),
			                    array('name' => 'Empresa',
			                                'width' => '300px',
			                                'column' => 'empresa',
			                                'rows' => array('style' => 'text-align:center')),
			                    array('name' => 'Tutor Academico',
						'width' => '300px',
						'column' => 'tutoracademico',
						'rows' => array('style' => 'text-align:center')),
					array('name' => 'Tutor Empresarial',
						'width' => '300px',
						'column' => 'tutorempresa',
						'rows' => array('style' => 'text-align:center')));
				$HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VUD');
				$json[] = $this->SwapBytes_Jquery->setHtml('tblPasantias', $HTML);
				$json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkInscripcionproyecto');
		  	} else {
				$HTML   = $this->SwapBytes_Html_Message->alert("No existen Estudiantes Inscritos en Empresas en este periodo.");
				$json[] = $this->SwapBytes_Jquery->setHtml('tblPasantias', $HTML);
		  	}
		    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnAdd', false);
		  	$this->getResponse()->setBody(Zend_Json::encode($json));
		}
	}

	public function addoreditloadAction() {
		if ($this->_request->isXmlHttpRequest()) {
			$json    = array();
			$dataRow = array();
	    	if (is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
				// Editar
	  			$title = 'Editar Inscripcion de Empresa';
	  			$dataRow                              = $this->inscripcionespasantias->getRow($this->_params['modal']['id']);
				$dataRow['id']                        = $this->_params['modal']['id'];
				$dataRow['pk_inscripcionpasantia']    = $this->_params['modal']['id'];
	      		$estudiantes = $this->inscripcionespasantias->getEstudiantesInscritospasantiaslaborales($dataRow['fk_periodo'], $dataRow['escuela'], $this->_params['filters']['sede']);
				$this->SwapBytes_Form->fillSelectBox('fk_recordacademico', $estudiantes  , 'pk_recordacademico', 'estudiante');
				$this->SwapBytes_Form->enableElement('fk_recordacademico', false);
			} else {
			// Agregar
			  $title = 'Agregar Inscripcion de Empresa';
			}
		    $this->_fillSelectsRecursive($dataRow);
			$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
			$this->SwapBytes_Crud_Form->setJson($json);
			$this->SwapBytes_Crud_Form->setWidthLeft('130px');
			$this->SwapBytes_Crud_Form->getAddOrEditLoad(); 
		}
	}

	public function addoreditconfirmAction() {
		if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();
			$message = '';
			$this->_fillSelectsRecursive($this->_params['modal']['fk_institucion']);
			$this->SwapBytes_Crud_Form->setJson($json);
			$this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
			$this->SwapBytes_Crud_Form->setWidthLeft('130px');
			$this->SwapBytes_Crud_Form->getAddOrEditConfirm();
		}
	}

	public function addoreditresponseAction() {
		if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();
			$id = $this->_params['modal']['id'];
			$this->_params['modal']['id'] = null;
			if (is_numeric($id) && $id > 0) {
				$this->inscripcionespasantias->updateRow($id, $this->_params['modal']);
			} else {
				$this->inscripcionespasantias->addRow($this->_params['modal']);
			}
		  	$this->SwapBytes_Crud_Form->getAddOrEditEnd();
		}
	}

	public function viewAction() {
		$dataRow = $this->inscripcionespasantias->getRow($this->_params['modal']['id']);
		$estudiantes = $this->inscripcionespasantias->getEstudiantesInscritospasantiaslaborales($dataRow['fk_periodo'],$dataRow['escuela'], $this->_params['filters']['sede']);
		$tutoresempresariales   = $this->contactos->gettutoresempresarial($dataRow['fk_institucion']);
		$tutoresacademicos = $this->usuariosgrupos->getTutoresAcademicos($dataRow['fk_periodo'],$dataRow['escuela']);
		$this->SwapBytes_Form->fillSelectBox('fk_recordacademico', $estudiantes  , 'pk_recordacademico', 'estudiante');
		$this->SwapBytes_Form->fillSelectBox('fk_tutor_institucion', $tutoresempresariales  , 'pk_contacto', 'tutor');
		$this->SwapBytes_Form->fillSelectBox('fk_tutor_academico', $tutoresacademicos  , 'pk_usuariogrupo', 'profesor');
		$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Inscripcion de Empresa');
		$this->SwapBytes_Crud_Form->setWidthLeft('130px');
		$this->SwapBytes_Crud_Form->getView();
	}

	public function deleteloadAction() {
		$message        = '¿Esta seguro que desea eliminarle la inscripcion al estudiante?';
		$permit         = true;
		$dataRow        = $this->inscripcionespasantias->getRow($this->_params['modal']['id']);
		$dataRow['id']  = $this->_params['modal']['id'];
	    $estudiantes = $this->inscripcionespasantias->getEstudiantesInscritospasantiaslaborales($dataRow['fk_periodo'],$dataRow['escuela'], $this->_params['filters']['sede']);
	    $tutoresempresariales   = $this->contactos->gettutoresempresarial($dataRow['fk_institucion']);
		$tutoresacademicos = $this->usuariosgrupos->getTutoresAcademicos($dataRow['fk_periodo'],$dataRow['escuela']); 
	    $this->SwapBytes_Form->fillSelectBox('fk_recordacademico', $estudiantes  , 'pk_recordacademico', 'estudiante');
	    $this->SwapBytes_Form->fillSelectBox('fk_tutor_institucion', $tutoresempresariales  , 'pk_contacto', 'tutor');
	    $this->SwapBytes_Form->fillSelectBox('fk_tutor_academico', $tutoresacademicos  , 'pk_usuariogrupo', 'profesor');
		$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Inscripcion de Empresa', $message);
		$this->SwapBytes_Crud_Form->setWidthLeft('120px');
		$this->SwapBytes_Crud_Form->getDeleteLoad($permit);
	}

	public function deletefinishAction() {
		$this->inscripcionespasantias->deleteRows($this->_params['modal']['id']);
		$this->SwapBytes_Crud_Form->setProperties($this->view->form);
		$this->SwapBytes_Crud_Form->getDeleteFinish();
	}

  	private function _fillSelectsRecursive($RowData) {
    	if ($this->_params['modal']['id'] <> 0 && is_array($RowData) && count($RowData) > 0) {
      		$tutoresempresariales   = $this->contactos->gettutoresempresarial($RowData['fk_institucion']); 
     	} else if ($this->_params['modal']['id'] == 0) {
        	$empresa = (!empty($this->_params['modal']['fk_institucion'])) ? $this->_params['modal']['fk_institucion'] : $this->_params['default']['empresa'];
        	$tutoresempresariales = $this->contactos->gettutoresempresarial($empresa); 
     	}
      	$this->SwapBytes_Form->fillSelectBox('fk_tutor_institucion', $tutoresempresariales , 'pk_contacto', 'tutor');
    }    
}
