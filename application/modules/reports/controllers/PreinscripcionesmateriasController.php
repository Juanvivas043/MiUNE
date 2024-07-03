<?php

class Reports_PreinscripcionesmateriasController extends Zend_Controller_Action {

	private $_Title   = 'Reporte \ Preinscripciones por Materias';


	public function init(){

        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Reinscripciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');

        $this->filtros          		= new Une_Filtros();
        $this->grupo                    = new Models_DbTable_UsuariosGrupos();
        $this->CmcBytes_Filtros 		= new CmcBytes_Filtros();
		$this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
		$this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
		$this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
		$this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
		$this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
		$this->SwapBytes_Jquery         = new SwapBytes_Jquery();
		$this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();

		$this->_params['filters'] = $this->filtros->getParams();

        $this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);
		$this->SwapBytes_Crud_Action->setEnable(true, true, false, false, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(false);  

   		$this->filtros->setDisplay(true, true, true);
		$this->filtros->setDisabled();
		$this->filtros->setRecursive(true, true, true);


        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->Preinscripciones 		= new Models_DbTable_Reinscripciones;
	}

  function preDispatch() {

         if (!Zend_Auth::getInstance()->hasIdentity()) {  
             $this->_helper->redirector('index', 'login', 'default');
         }
    
         if (!$this->grupo->haveAccessToModule()) {
             $this->_helper->redirector('accesserror', 'profile', 'default');
         }

    }

	public function indexAction(){
        $this->view->title                 = $this->_Title;
        $this->view->filters               = $this->filtros;
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
        $this->SwapBytes_Ajax_Action       = new SwapBytes_Ajax_Action();
    		$this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
    		$this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
    		$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->SwapBytes_Crud_Action->addJavaScript("setTimeout(function(){
                    $(\"#selPeriodo option[value='129']\").remove();
                    }, 1000);");
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
	}

	public function periodoAction(){
		$this->filtros->getAction();
	}
	 public function sedeAction() {
        $this->filtros->getAction();
    }

    public function escuelaAction() {
        $this->filtros->getAction(array('sede'));
    }

	public function listAction(){
		if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json = array();

            $periodo = $this->_params['filters']['periodo'];
            $sede    = $this->_params['filters']['sede'];
            $escuela = $this->_params['filters']['escuela'];


           	$rows = $this->Preinscripciones->listarPreinscripcionMaterias($periodo,$sede,$escuela);


            $table = array('class' => 'tableData',
               'width' => '700px' );

            $columns = array(array('name'    => 'pensum',
                                   'width'   => '60px',
                                   'column'  => 'pensum',
                                   'rows'    => array('style' => 'text-align:center')),
                             array('name'    => 'codigo',
                                   'width'   => '100px',
                                   'column'  => 'codigopropietario',
                                   'rows'    => array('style' => 'text-align:center')),
                             array('name'    => 'materia',
                                   'width'   => '400px',
                                   'column'  => 'materia',
                                   'rows'    => array('style' => 'text-align:center')),
                             array('name'    => 'Periodo',
                                   'width'   => '100px',
                                   'column'  => 'semestre',
                                   'rows'    => array('style' => 'text-align:center')),
                             array('name'    => 'Preinscritos',
                                   'width'   => '100px',
                                   'column'  => 'inscritos',
                                   'rows'    => array('style' => 'text-align:center')),
                             );
		
		$HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
		$json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
		$this->getResponse()->setBody(Zend_Json::encode($json));
        }
	}
}
