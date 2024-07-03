<?php

class Consultas_CantidadinscritosporescuelaController extends Zend_Controller_Action {
    
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
            $data = $this->inscripciones->getCantidadInscritos($periodo,$sede);
            $total = $this->inscripciones->getTotalCantidadInscritos($periodo,$sede);
            //$online = $this->inscripciones->getCantidadInscritosOnline($this->_params['filters']['periodo'],$this->_params['filters']['sede']);

            $property_table = array('class'  => 'tableData',
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
                                         array('name'     => 'Diferencia',
                                               'column'   => 'diferencia',
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
                                         array('name'     => 'Online Diferencia',
                                               'column'   => 'onlinediferencia',
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
                                         array('name'     => 'Nuevo Ingreso Diferencia',
                                               'column'   => 'nidiferencia',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center'))
                                         );
              $property_column_total = array(array('name' => 'Total Inscritos',
                                               'column'   => 'escuela',
                                               'width'    => '120px'),
                                         array('name' => 'Adminsitrativo',
                                               'column'   => 'administrativo',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name' => 'Academico',
                                               'column'   => 'academico',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name' => 'Diferencia',
                                               'column'   => 'diferencia',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')));
            // Generamos la lista.
            $HTML       = $this->SwapBytes_Crud_List->fill($property_table, $data, $property_column);
            $HTML_total = $this->SwapBytes_Crud_List->fill($property_table, $total, $property_column_total);
            $json[] = $this->SwapBytes_Jquery->setHtml('tblCantidad', $HTML);
            $json[] = $this->SwapBytes_Jquery->setHtml('tblTotal', $HTML_total);   
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
}
?>