<?php

class Reports_InscritospiraController extends Zend_Controller_Action {

     public function init() {
       
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
         Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        
        $this->filtros = new Une_Filtros();
        $this->record = new Models_DbTable_Recordsacademicos();
        $this->grupo = new Models_DbTable_UsuariosGrupos();
        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri = new SwapBytes_Uri();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->_params['filters'] = $this->filtros->getParams();

        $this->filtros->setDisplay(true, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, true, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(true, true, true, false, false, false, false, false, false);
//		$this->filtros->setType('seccion', FILTER_TYPE_SECCION_PADRES);

        $this->SwapBytes_Crud_Action->setDisplay(true, true);
        $this->SwapBytes_Crud_Action->setEnable(true, true);
        $this->SwapBytes_Crud_Search->setDisplay(false);
        
        $this->logger = Zend_Registry::get('logger');
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
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
    }

    public function indexAction() {

        $this->view->title = "Reportes \ Inscritos Pira";
        $this->view->filters = $this->filtros;
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
    }
    
    public function periodoAction() {
        $this->filtros->getAction();
    }

    public function sedeAction() {
        $this->filtros->getAction(array('periodo'));
    }
    
    public function escuelaAction() {
        $this->filtros->getAction(array('periodo'));
    }
    
    public function descargarAction(){ 
            
    }
    
    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

             $json = array();
            
            $ra_data = $this->record->getEstudiantesPira($this->_params['filters']['periodo'], $this->_params['filters']['sede'], $this->_params['filters']['escuela']);
           

            // Definimos las propiedades de la tabla.
            $ra_property_table = array('class' => 'tableData',
                                       'width' => '1100px',
                                       'column' => 'disponible');

            $ra_property_column = array(array('column'   => 'cedula',
                                              'primary'  => true,
                                              'hide'     => true),
                                        array('name'     => '#',
                                              'width'    => '20px',
                                              'function' => 'rownum',
                                              'rows'     => array('style' => 'text-align:center')),
                                        array('name'     => 'C.I.',
                                              'column'   => 'cedula',
                                              'width'    => '70px',
                                              'rows'     => array('style' => 'text-align:center')),
                                        array('name'     => 'Estudiante',
                                              'column'   => 'estudiante',
                                              'width'    => '300px',
                                              'rows'     => array('style' => 'text-align:center')),
                                        array('name'     => 'Escuela',
                                              'column'   => 'escuela',
                                              'width'    => '200px',
                                              'rows'     => array('style' => 'text-align:center')),
                                        array('name'     => 'Iap',
                                              'column'   => 'iperiodo',
                                              'width'    => '250px',
                                              'rows'     => array('style' => 'text-align:center')),
                                        array('name'     => 'Iia',
                                              'column'   => 'iia',
                                              'width'    => '250px',
                                              'rows'     => array('style' => 'text-align:center'))
                                        );
            
            $other = array(array('actionName'  => 'imprimir',
                                 'action'      => 'myimprimir(##pk##)',
                                 'label'       => 'Imprimir'));
            
            $HTML = $this->SwapBytes_Crud_List->fill($ra_property_table, $ra_data, $ra_property_column, null, null);   

            }
            
            $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
    }
    
}
