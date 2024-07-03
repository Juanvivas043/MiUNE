<?php

/**
* @author: DDTI Septiembre 2016
* 
*
*/

class Evaluations_ResultadosevaluacionesController extends Zend_Controller_Action {


    /*Funcion donde se inicializan las librerias*/
    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbView_Sedes');
        Zend_Loader::loadClass('Models_DbView_Escuelas'); 
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_EvaluacionProfesores');


        $this->Une_Filtros              = new Une_Filtros();
        $this->sede                     = new Models_DbView_Sedes();
        $this->escuela                  = new Models_DbView_Escuelas();
        $this->periodos                 = new Models_DbTable_Periodos();
        $this->grupo                    = new Models_DbTable_UsuariosGrupos();
        $this->evaluacion               = new Models_DbTable_EvaluacionProfesores();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        /*Filtros*/
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));

        $this->Une_Filtros->setDisplay(true, true, true);
        $this->Une_Filtros->setRecursive(true, true, true);
         /*Botones de Acciones*/
        $this->SwapBytes_Crud_Action->setDisplay(true,true);
        $this->SwapBytes_Crud_Action->setEnable(true,true);
        $this->SwapBytes_Crud_Search->setDisplay(false);

    } 

    //Comentamos preDispatch para que no moleste al probar el codigo

    function preDispatch() {

         if (!Zend_Auth::getInstance()->hasIdentity()) {  
             $this->_helper->redirector('index', 'login', 'default');
         }
    
         if (!$this->grupo->haveAccessToModule()) {
             $this->_helper->redirector('accesserror', 'profile', 'default');
         }

    }


    public function indexAction() {
        $this->view->title = "Resultados Evaluaciones";
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
        if ($this->_request->isXmlHttpRequest()) {
            $json = array();
            $this->SwapBytes_Ajax->setHeader();
            $periodos = $this->periodos->getSelect();
            $this->SwapBytes_Ajax_Action->fillSelect($periodos);
        }

    }

    public function sedeAction() {
        $dataSedes = $this->sede->getSedes();
        array_unshift($dataSedes, array("pk_atributo"=>"0","sede"=>"Todas")); //se anade el campo de "todas" para seleccionar todas las sedes
        $this->SwapBytes_Ajax_Action->fillSelect($dataSedes);        
    }

    public function escuelaAction() {
        if ($this->Request->getParam('sede')==0) {
          $dataRows = $this->escuela->getEscuelas();
          array_unshift($dataRows, array("pk_atributo"=>"0","escuela"=>"Todas")); //se anade el campo de "todas" para seleccionar todas las escuelas
          $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
        }else{
          $dataRows = $this->escuelas->getSelect($this->Request->getParam('sede'));
          array_unshift($dataRows, array("pk_atributo"=>"0","escuela"=>"Todas")); //se anade el campo de "todas" para seleccionar todas las escuelas
          $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
        }
    }

    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $periodo    = $this->_params['filters']['selPeriodo'];
            $sede       = $this->_params['filters']['selSede'];
            $escuela    = $this->_params['filters']['selEscuela'];
            $resultados = $this->evaluacion->getResultadosAll($periodo,$sede, $escuela);

            if (!empty($resultados)) {

              $table = array('class'  => 'tableData',
                             'width'  => '976px',
                             'column' => 'disponible');
              $columns = array(array('column'  => 'pk_usuario',
                                     'primary' => true,
                                     'hide'    => true),
                               array('name'     => '#',
                                     'function' => 'rownum',
                                     'width'    => '20px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'C.I.',
                                     'column'   => 'pk_usuario',
                                     'width'    => '75x',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'Nombre',
                                     'column'   => 'nombre',
                                     'width'    => '300px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'EDRI',
                                     'column'   => 'peso1_1',
                                     'width'    => '40px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'EDV',
                                     'column'   => 'peso1_2',
                                     'width'    => '40px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'EDDP',
                                     'column'   => 'peso1_3',
                                     'width'    => '40px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'ADRI',
                                     'column'   => 'peso2_1',
                                     'width'    => '40px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'ADV',
                                     'column'   => 'peso2_2',
                                     'width'    => '40px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'ADDP',
                                     'column'   => 'peso2_3',
                                     'width'    => '40px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'ADC',
                                     'column'   => 'peso2_4',
                                     'width'    => '40px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'DDRI',
                                     'column'   => 'peso3_1',
                                     'width'    => '40px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'DDV',
                                     'column'   => 'peso3_2',
                                     'width'    => '40px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'DDDP',
                                     'column'   => 'peso3_3',
                                     'width'    => '40px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'DDC',
                                     'column'   => 'peso3_4',
                                     'width'    => '40px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'CDRI',
                                     'column'   => 'peso4_1',
                                     'width'    => '40px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'CDV',
                                     'column'   => 'peso4_2',
                                     'width'    => '40px',
                                     'rows'     => array('style' => 'text-align:center')),
                               array('name'     => 'Total',
                                     'column'   => 'total',
                                     'width'    => '40px',
                                     'rows'     => array('style' => 'text-align:center'))
               
                               );                        

              $HTML   = $this->SwapBytes_Crud_List->fill($table, $resultados, $columns);
              $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
              
            }else{

              $HTML =  $this->SwapBytes_Html_Message->alert("No existen Registros.");
              $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));
          }
    }
}
