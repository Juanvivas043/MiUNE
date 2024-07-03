<?php

class Reports_ListadopasantesserviciocomunitarioController extends Zend_Controller_Action {
private $Title = 'Reportes \ Listado de Pasantes Servicio Comunitario';	

public function init(){

		Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Inscripcionespasantias');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        
        $this->seguridad          = new Models_DbTable_UsuariosGrupos();
    	$this->estudiante              = new Models_DbTable_Usuarios();
        $this->Inscripcionespasantias = new Models_DbTable_Inscripcionespasantias();
    	$this->filtros                  = new Une_Filtros();
        $this->CmcBytes_Filtros         = new CmcBytes_Filtros();
        $this->Swapbytes_array          = new SwapBytes_Array();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->CmcBytes_Redirect        = new CmcBytes_Redirect();
        //$this->CmcBytes_Profit       = new CmcBytes_Profit();
        $this->logger = Zend_Registry::get('logger');
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);
        $this->SwapBytes_Crud_Action->setEnable(true, true, false, false, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(false);//quitar barra
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        //filtros para la busqueda
  		$this->filtros->setDisplay(true, true);
    	$this->filtros->setDisabled(false, true);
    	$this->filtros->setRecursive(true, true);

        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
}

	function preDispatch() {
       if(!Zend_Auth::getInstance()->hasIdentity()) {
           $this->_helper->redirector('index', 'login', 'default');
       }

       if(!$this->seguridad->haveAccessToModule()) {
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
            $this->view->SwapBytes_Ajax = $this->SwapBytes_Ajax;
            $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
            $this->view->SwapBytes_Ajax->setView($this->view);
            $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
            
            

      }

    public function periodoAction() {
        $this->filtros->getAction();
    }

    public function sedeAction() {
        $this->filtros->getAction();
    }
   

 	public function usuarioAction(){

      	$this->SwapBytes_Ajax->setHeader(); 
        $ci= $this->authSpace->userId;
        $json = array();
        $tutor = $this->Inscripcionespasantias->getTutor($ci);
        //var_dump($tutor);die;

        $json[] ='$("#cedula").html("'.$tutor[0]['pk_usuario'].'")';
        $json[] ='$("#nombre").html("'.$tutor[0]['nombre'].'")';
        $json[] ='$("#apellido").html("'.$tutor[0]['apellido'].'")';
        $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->getResponse()->setBody(Zend_Json::encode($json));
}

	public function listAction(){
		if ($this->_request->isXmlHttpRequest()) {
	  			 $this->SwapBytes_Ajax->setHeader();

         		   $ci= $this->authSpace->userId;	
           		 $queryString = $this->_getParam('filters');

            	 $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);
            	 $periodo = $queryArray['selPeriodo'];
               $sede = $queryArray['selSede'];
                 
               $rows = $this->Inscripcionespasantias->getListadoPasantes($ci,$sede,$periodo);

                  if (isset($rows) && count($rows) > 0) {
     

                        $table = array('class'=> 'tableData',
                                       'width'=> '900px');
                        $columns = array(array( 'name'     => '#',
                                                'width'    => '50px',
                                                'function' => 'rownum',
                                                'rows'     => array('style' => 'text-align:center')),
                                         array( 'name'     => 'C.I.',
                                                'column'   => 'pk_usuario',
                                                'width'    => '250px',
                                                'rows'     => array('style' => 'text-align:center')),
                                         array( 'name'     => 'Estudiante',
                                                'column'   => 'estudiante',
                                                'width'    => '500px',
                                                'rows'     => array('style' => 'text-align:center')),
                                         array( 'name'     => 'Escuela',
                                                'column'   => 'escuela',
                                                'width'    => '500px',
                                                'rows'     => array('style' => 'text-align:center')),
                                         array( 'name'     => 'Institución',
                                                'column'   => 'institucion',
                                                'width'    => '500px',
                                                'rows'     => array('style' => 'text-align:center')));

                $HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
                
                
    } else {
    
    $HTML = $this->SwapBytes_Html_Message->alert("No posee pasantes para este Período");

    }

    
    $json[] = $this->SwapBytes_Jquery->setHtml('tblData', $HTML);
    $this->getResponse()->setBody(Zend_Json::encode($json));
    }
	}

	public function photoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $id    = $this->_getParam('id', 0);
        $image = $this->estudiante->getPhoto($id);

        $this->getResponse()
             ->setHeader('Content-type', 'image/jpeg')
             ->setBody($image);
    }
}
?>