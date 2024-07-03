<?php

class Consultas_CantidadinscritosporsemestreController extends Zend_Controller_Action {
    
    public function init() {
      
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Une_Filtros');

        $this->Une_Filtros     = new Une_Filtros();
        $this->seguridad       = new Models_DbTable_UsuariosGrupos();
        $this->inscripciones   = new Models_DbTable_Inscripciones();
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
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();
        $this->SwapBytes_Html_Message = new SwapBytes_Html_Message();

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
        $this->view->title = "Consultas \ Cantidad de Inscritos Por Semestre";
        $this->view->filters = $this->Une_Filtros;
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
    }

    //Cargo Campos de los Filtros
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
            $pensum = $this->_params['filters']['pensum'];

            $json = array();
            $total = $this->inscripciones->getMatriculaSemestre($periodo,$sede);

            $property_table = array('class'  => 'tableData',
                                    'width'  => '1415px',
                                    'column' => 'disponible');

            $property_column_total = array(array('name' => 'Escuela',
                                               'column'   => 'escuela',
                                               'width'    => '120px'),
                                         array('name' => '1',
                                               'column'   => '1',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')),
                                        array('name' => '2',
                                               'column'   => '2',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')),
                                        array('name' => '3',
                                               'column'   => '3',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')),
                                        array('name' => '4',
                                               'column'   => '4',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')),
                                               array('name' => '5',
                                               'column'   => '5',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')),
                                               array('name' => '6',
                                               'column'   => '6',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')),
                                               array('name' => '7',
                                               'column'   => '7',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')),
                                               array('name' => '8',
                                               'column'   => '8',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')),
                                               array('name' => '9',
                                               'column'   => '9',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')),
                                               array('name' => '10',
                                               'column'   => '10',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')),
                                               array('name' => '11',
                                               'column'   => '11',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')),
                                               array('name' => '12',
                                               'column'   => '12',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name' => 'TOTAL',
                                               'column'   => 'TOTAL',
                                               'width'    => '75px',
                                               'rows'     => array('style' => 'text-align:center')));

              
            
            if (empty($total)){
                $HTML       =  $this->SwapBytes_Html_Message->alert("No existen Estudiantes inscritos en este periodo");
                $HTML_total =  $this->SwapBytes_Html_Message->alert("No existen Estudiantes inscritos en este periodo");
                 
            } else {
                // Generamos la lista.
                $HTML       = $this->SwapBytes_Crud_List->fill($property_table, $data, $property_column);
                $HTML_total = $this->SwapBytes_Crud_List->fill($property_table, $total, $property_column_total);
            }

            $json[] = $this->SwapBytes_Jquery->setHtml('tblCantidad', $HTML);
            $json[] = $this->SwapBytes_Jquery->setHtml('tblTotal', $HTML_total);

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    } 
}
?>
