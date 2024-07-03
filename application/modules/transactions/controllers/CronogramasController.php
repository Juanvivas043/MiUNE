<?php

class Transactions_CronogramasController extends Zend_Controller_Action {

    private $Title = 'Transacciones \ Carga de cronogramas';

    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Clases');
        Zend_Loader::loadClass('Models_DbView_Estrategias');
        Zend_Loader::loadClass('Models_DbView_Evaluaciones');
        Zend_Loader::loadClass('Forms_Cronograma');

        $this->Asignaciones    = new Models_DbTable_Asignaciones();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->Clases          = new Models_DbTable_Clases();
        $this->vw_estrategias  = new Models_DbView_Estrategias();
        $this->vw_evaluaciones = new Models_DbView_Evaluaciones();
        $this->filtros         = new Une_Filtros();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
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

        /*
         * Configuramos los filtros.
         */
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');

        $this->filtros->setDisplay(true, true, true, true, true, true, true, true, false);
        $this->filtros->setDisabled(true, true, true, true, true, true, true, true, false);
		$this->filtros->setRecursive(true, true, true, true, true, true, true, true, false);
		$this->filtros->setParam('usuario', $this->authSpace->userId);

        /*
         * Configuramos los botones.
         */
		$this->SwapBytes_Crud_Action->setDisplay(true, true, true, true, true, true);
		$this->SwapBytes_Crud_Action->setEnable(true, true, true, true, true, true);
                
                $this->SwapBytes_Crud_Action->addCustum("<button id=\"btnVerRecurso\" class=\"ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\" 
                                                        name=\"btnVerRecurso\" role=\"button\" aria-disabled=\"false\">Aula Virtual</button>");
                $this->SwapBytes_Crud_Action->addJavaScript("$('#btnVerRecurso').click(function(){
                                                                $.getJSON(urlAjax + \"buscar/filters/\"+escape($('#tblFiltros').find(':input').serialize())+\"\", function(d){executeCmdsFromJSON(d)});
                                                                newwindow=window.open('{$this->view->baseUrl()}/reports/recursos/generar','_newtab_' ,'scrollbars=1,toolbar=0,status=0,fullscreen=yes');
                                                                if (window.focus) {newwindow.focus()}
                                                                return false;
                                                            });");
                $this->SwapBytes_Crud_Search->setDisplay(false);

        /*
         * Mandamos a crear el formulario para ser utilizado mediante el AJAX.
         */
        $this->view->form = new Forms_Cronograma();

        $this->SwapBytes_Form->set($this->view->form);
        $this->SwapBytes_Form->fillSelectBox('fk_tipoestrategia', $this->vw_estrategias->get() , 'pk_atributo', 'valor');
        $this->SwapBytes_Form->fillSelectBox('fk_tipoevaluacion', $this->vw_evaluaciones->get(), 'pk_atributo', 'valor');

        $this->view->form = $this->SwapBytes_Form->get();

		/*
		 * Obtiene los parametros de los filtros y del modal.
		 */
		$this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
		$this->_params['filters'] = $this->filtros->getParams();
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    function preDispatch() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
             $this->_helper->redirector('index', 'login', 'default');
        }

        if (!$this->grupo->haveAccessToModule()) {
             $this->_helper->redirector('accesserror', 'profile', 'default');
        }
        
        $this->session = new Zend_Session_Namespace('Recursos');
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

    public function periodoAction() {
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

    public function turnoAction() {
        $this->filtros->getAction(array('usuario', 'periodo', 'sede', 'escuela', 'semestre', 'materia'));
    }

    public function seccionAction() {
        $this->filtros->getAction(array('usuario', 'periodo', 'sede', 'escuela', 'semestre', 'materia', 'seccion'));
    }

    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $Data   = $this->filtros->getParams();
            $json   = array();

            $Data['usuario']  = $this->authSpace->userId;

            $this->Clases->setData($Data);
            
            $rows = $this->Clases->getCronogramas();

            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_clase',
                                       'primary' => true,
                                       'hide'    => true),
                                 array('name'    => array('control' => array('tag'        => 'input',
                                                                             'type'       => 'checkbox',
                                                                             'name'       => 'chkSelectDeselect')),
                                       'column'  => 'nc',
                                       'width'   => '20px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'control' => array('tag'   => 'input',
                                                          'type'  => 'checkbox',
                                                          'name'  => 'chkClase',
                                                          'value' => '##pk_clase##')),
                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'column'   => 'numero',
                                       'rows'    => array('style' => 'text-align:right')),
                                 array('name'    => 'Fecha',
                                       'width'   => '70px',
                                       'column'  => 'fecha',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Descripción',
                                       'width'   => '300px',
                                       'column'  => 'descripcion'),
                                 array('name'    => 'Contenido',
                                       'width'   => '300px',
                                       'column'  => 'contenido'),
                                 array('name'    => 'T. Estra.',
                                       'width'   => '280px',
                                       'column'  => 'tipo_estrategia'),
                                 array('name'    => 'T. Evalu.',
                                       'width'   => '280px',
                                       'column'  => 'tipo_evaluacion'),
                                 array('name'    => 'Puntos',
                                       'width'   => '50px',
                                       'column'  => 'puntaje',
                                       'rows'    => array('style' => 'text-align:center')));
                
                $this->SwapBytes_Crud_List->addFooter('Puntos', 'Total', SQL_FUNCTION_SUM);

                $other = array(
                   array('actionName' => 'recursos',
                         'label'      => 'Recursos'));

                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VUO', $other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkClase');
            } else {
                $HTML   = $this->SwapBytes_Html_Message->alert("No existen cronogramas cargados.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function addoreditloadAction() {
		if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
			$dataRow       = $this->Clases->getRow($this->_params['modal']['id']);
			$dataRow['id'] = $this->_params['modal']['id'];
		}

		$dataRow['fecha'] = $this->SwapBytes_Date->convertToForm($dataRow['fecha']);
		$json[]           = $this->SwapBytes_Jquery_Ui->setDatepicker('fecha');
		
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Editar Cronograma');
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();
    }

	public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

			$json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fecha');

			$this->SwapBytes_Crud_Form->setJson($json);
			$this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
			$this->SwapBytes_Crud_Form->getAddOrEditConfirm();
		}
	}

    /**
     * Permite guardar el contenido de un determinado registro mediante una serie
     * de datos que fueron capturados por un formulario modal.
     */
    public function addoreditresponseAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
			
			// Obtenemos los parametros que se esperan recibir.
            $this->Asignaciones->setData($this->_params['filters'], array('periodo','sede','escuela','pensum','semestre', 'materia', 'turno','seccion'));
			
			$filtro                   = $this->Asignaciones->getPK();
			$dataRow                  = $this->_params['modal'];
			$id                       = $dataRow['id'];
			$dataRow['id']            = null;
			$dataRow['filtro']        = null;
			$dataRow['fecha']         = $this->SwapBytes_Date->convertToDataBase($dataRow['fecha']);
			$dataRow['fk_asignacion'] = $filtro;
                        

			if(is_numeric($id) && $id > 0) {
				$this->Clases->updateRow($id, $dataRow);
			} else if(is_numeric($filtro) && $filtro > 0) {
				$this->Clases->addRow($dataRow);
			}
			
			$this->SwapBytes_Crud_Form->getAddOrEditEnd();
		}
    }

    public function viewAction() {
        $dataRow          = $this->Clases->getRow($this->_params['modal']['id']);
        $dataRow['fecha'] = $this->SwapBytes_Date->convertToForm($dataRow['fecha']);

        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Cronograma');
        $this->SwapBytes_Crud_Form->getView();
    }
    
    public function recursosAction() {
        
        $this->session->id = $this->_getParam('id');
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');         
        $redirector->gotoUrl("/transactions/recursos/");


    }
    
    public function buscarAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $Data   = $this->filtros->getParams();

            var_dump($Data);
            
            $this->Asignaciones->setData($this->_params['filters'], array('periodo','sede','escuela','pensum','semestre','materia','turno','seccion','usuario'));

            $asignacion = $this->Asignaciones->getPK();
            
            $this->session->asig =  $asignacion;
            echo $this->session->asig;
        }
        
    }

    public function deleteloadAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
			
            $Params = $this->_params['modal'];

			if(isset($Params['chkClase'])) {
				if(is_array($Params['chkClase'])) {
					foreach($Params['chkClase'] as $clase) {
						$this->Clases->deleteRow($clase);
					}
				} else {
					$this->Clases->deleteRow($Params['chkClase']);
				}

				$this->SwapBytes_Crud_Form->getDeleteFinish();
			}
		}
    }

	public function copyAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

			$Params = $this->_params['modal'];

			$this->authSpace->copyItems = $Params['chkClase'];
		}
	}

	public function pasteAction() {
	    if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

			if(!isset($this->authSpace->copyItems)) { return; }

//            $Data['usuario']  = $this->authSpace->userId;

			$this->Asignaciones->setData($this->_params['filters'], array('periodo','sede','escuela','pensum','semestre','materia','turno','seccion','usuario'));

			$asignacion = $this->Asignaciones->getPK();
			$clases     = (is_array($this->authSpace->copyItems))? implode(',', $this->authSpace->copyItems) : $this->authSpace->copyItems;

			$this->Clases->copyRow($clases, $asignacion);

			$this->SwapBytes_Crud_Form->getRefresh();
		}
	}
}
