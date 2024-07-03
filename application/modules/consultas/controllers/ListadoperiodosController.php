<?php

class Consultas_ListadoperiodosController extends Zend_Controller_Action {
    public function init() {
        
        Zend_Loader::loadClass('Models_DbTable_Periodos');

        $this->periodos                 = new Models_DbTable_Periodos;

        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
      }

     //function preDispatch() {
        // if (!Zend_Auth::getInstance()->hasIdentity()) {
        //     $this->_helper->redirector('index', 'login', 'default');
       ///  }

       //  if (!$this->seguridad->haveAccessToModule()) {
       //      $this->_helper->redirector('index', 'login', 'default');
        // }
    // }

    public function indexAction() {
        $this->view->title                  = "Consultas / Listado de periodos";
        
        $rows = $this->periodos->listadoPeriodos();
        $table = array('class' => 'tableData');

        $columns = array(array('name'     => 'PERIODO',
                               'column'   => 'periodo',
                               'rows'     => array('style' => 'text-align:left')),
                         array('name'     => 'FECHA INICIO',
                               'column'   => 'fechainicio',
                               'rows'     => array('style' => 'text-align:center')),
                         array('name'     => 'INICIO CLASES',
                               'column'   => 'inicioclases',
                               'rows'     => array('style' => 'text-align:center')),
                         array('name'     => 'FECHA FIN',
                               'column'   => 'fechafin',
                               'rows'     => array('style' => 'text-align:center')));
        $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
        echo $HTML;
    }
  
}