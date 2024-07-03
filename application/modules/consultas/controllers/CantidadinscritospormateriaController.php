<?php

class Consultas_CantidadinscritospormateriaController extends Zend_Controller_Action {
    
    public function init() {
      
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Une_Filtros');

        $this->Une_Filtros     = new Une_Filtros();
        $this->seguridad       = new Models_DbTable_UsuariosGrupos();
        $this->inscripciones       = new Models_DbTable_Inscripciones();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->sedes           = new Models_DbTable_Estructuras();
        $this->periodo         = new Models_DbTable_Periodos();
        $this->recordacademico = new Models_DbTable_Recordsacademicos();

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

        $this->Une_Filtros->setDisplay(true, true,true,true,true);
        $this->Une_Filtros->setRecursive(true, true,true,true,true);
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
        $this->view->title = "Consultas \ Cantidad de Inscritos Por Materia";
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
        $this->Une_Filtros->getAction();
    }
    public function escuelaAction() {
        $this->Une_Filtros->getAction();
    }
    public function pensumAction() {
        $this->Une_Filtros->getAction();
    }
    public function semestreAction() {
        $this->Une_Filtros->getAction();
    }

    public function generarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json = array();
            $checkIsChecked = (boolean) $_GET["check"];
            $data = $this->inscripciones->getCantidadInscritosPorMateria($this->_params['filters']['periodo'],$this->_params['filters']['sede'],$this->_params['filters']['escuela'],$this->_params['filters']['pensum'],$this->_params['filters']['semestre'],$checkIsChecked);
            if(isset($data) && count($data) > 0) {
              $property_table = array('class'  => 'tableData',
                                         'width'  => '870px',
                                         'column' => 'disponible');

              $property_column = array(array('name'     => 'Codigo',
                                                 'column'   => 'codigopropietario',
                                                 'width'    => '120px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Asignatura',
                                                 'column'   => 'asignatura',
                                                 'width'    => '75px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Seccion',
                                                 'column'   => 'seccion',
                                                 'width'    => '75px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Inscritos',
                                                 'column'   => 'inscritos',
                                                 'width'    => '75px',
                                                 'rows'     => array('style' => 'text-align:center','class' => 'inscritos')),
                                           array('name'     => 'Retirados',
                                                 'column'   => 'retirados',
                                                 'width'    => '75px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Cupos',
                                                 'column'   => 'cupos',
                                                 'width'    => '75px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Cupos Max.',
                                                 'column'   => 'cupos_max',
                                                 'width'    => '75px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Alerta',
                                                 'column'   => 'alert',
                                                 'width'    => '75px',
                                                 'rows'     => array('style' => 'text-align:center','class' => 'alert')),
                                           array('name'     => 'Fusion',
                                                 'column'   => 'fusion',
                                                 'width'    => '75px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Total Inscritos',
                                                 'column'   => 'total',
                                                 'width'    => '75px',
                                                 'rows'     => array('style' => 'text-align:center'))
                                           );
              // Generamos la lista.
              $HTML   = $this->SwapBytes_Crud_List->fill($property_table, $data, $property_column);
              $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);    
            }
            else {
              $HTML =  $this->SwapBytes_Html_Message->alert("No existen Estudiantes Inscritos en esta Materia");
              $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);   
            }
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
}

?>