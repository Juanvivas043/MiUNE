<?php

class Consultas_CantidadinscritosgeneroController extends Zend_Controller_Action {
    
    public function init() {
      
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Une_Filtros');

        $this->Une_Filtros     = new Une_Filtros();
        $this->seguridad       = new Models_DbTable_UsuariosGrupos();
        $this->inscripciones       = new Models_DbTable_Inscripciones();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->sedes           = new Models_DbTable_Estructuras();
        $this->periodo         = new Models_DbTable_Periodos();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        /*Filtros*/
        $this->_params['filters'] = $this->Une_Filtros->getParams();

        $this->Une_Filtros->setDisplay(true, true);
        $this->Une_Filtros->setRecursive(true, true);
        /*Botones de Acciones*/
        $this->SwapBytes_Crud_Action->setDisplay(false,false);
        $this->SwapBytes_Crud_Action->setEnable(false,false);
        $this->SwapBytes_Crud_Search->setDisplay(false);
    }

    function preDispatch() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
           $this->_helper->redirector('index', 'login', 'default');
        }
        if (!$this->seguridad->haveAccessToModule()) {
           $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

    //Acciones referidas al index
    public function indexAction() {
        $this->view->title = "Consultas \ Cantidad de Inscritos Por Escuela";
        $this->view->filters = $this->Une_Filtros;
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
    }

    public function periodoAction() {
       $this->Une_Filtros->getAction();
    }

    public function sedeAction() {
      $this->Une_Filtros->getAction(array('periodo'));
    }

    public function generarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $periodo = $this->_params['filters']['periodo'];
            $sede = $this->_params['filters']['sede'];

            $json = array();
            $masculino = $this->inscripciones->getCantidadInscritosGenero($periodo,$sede,true);
            $femenino  = $this->inscripciones->getCantidadInscritosGenero($periodo,$sede,false);
            $property_table_masculino = array('class'  => 'tableMasculino',
                                               'width'  => '1415px',
                                               'column' => 'disponible');
            $property_table_femenino  = array('class'  => 'tableFemenino',
                                               'width'  => '1415px',
                                               'column' => 'disponible');

            $property_column = array(array('name'     => 'Escuela',
                                           'column'   => 'escuela',
                                           'width'    => '120px'),
                                     array('name'     => 'Administrativo',
                                           'column'   => 'administrativo',
                                           'width'    => '75px',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'Academico',
                                           'column'   => 'academico',
                                           'width'    => '75px',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'Online Administrativo',
                                           'column'   => 'onlineadministrativo',
                                           'width'    => '75px',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'Online Academico',
                                           'column'   => 'onlineacademico',
                                           'width'    => '75px',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'Nuevo Ingreso Administrativo',
                                           'column'   => 'niadministrativo',
                                           'width'    => '75px',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'Nuevo Ingreso Academico',
                                           'column'   => 'niacademico',
                                           'width'    => '75px',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'Total Administrativo',
                                           'column'   => 'totaladministrativo',
                                           'width'    => '75px',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'Total Academico',
                                           'column'   => 'totalacademico',
                                           'width'    => '75px',
                                           'rows'     => array('style' => 'text-align:center'))
                                     );
            // Generamos la lista.
            $HTML_masculino = $this->SwapBytes_Crud_List->fill($property_table_masculino, $masculino, $property_column);
            $HTML_femenino  = $this->SwapBytes_Crud_List->fill($property_table_femenino, $femenino, $property_column);
            $json[] = $this->SwapBytes_Jquery->setHtml('tableMasculino', $HTML_masculino);
            $json[] = $this->SwapBytes_Jquery->setHtml('tableFemenino', $HTML_femenino);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
}
?>
