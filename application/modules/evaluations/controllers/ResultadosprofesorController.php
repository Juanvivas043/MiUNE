<?php

class Evaluations_ResultadosprofesorController extends Zend_Controller_Action {


    /*Funcion donde se inicializan las librerias*/
    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Une_ChartJS');
        Zend_Loader::loadClass('Models_DbView_Sedes');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_EvaluacionProfesores');


        $this->Une_Filtros              = new Une_Filtros();
        $this->Une_ChartJS              = new Une_ChartJS();
        $this->sede                     = new Models_DbView_Sedes();
        $this->periodos                 = new Models_DbTable_Periodos();
        $this->usuarios                 = new Models_DbTable_Usuarios();
        $this->grupo                    = new Models_DbTable_UsuariosGrupos();
        $this->evaluacion               = new Models_DbTable_EvaluacionProfesores();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->cedulas = new Zend_Session_Namespace('Zend_Auth');

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Jquery->endLine(true);   

        $this->ci = $this->usuarios->getUsuario($this->cedulas->userId)['pk_usuario'];
        $this->periodo = $this->periodos->getUltimo();

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

        $this->view->title                  = "Evaluaciones / Resultados del Profesor";
        $this->view->filters                = $this->Une_Filtros;
        $this->view->module                 = $this->Request->getModuleName();
        $this->view->controller             = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery       = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax         = $this->SwapBytes_Ajax;
        $this->view->SwapBytes_Crud_Action  = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search  = $this->SwapBytes_Crud_Search;
        
        $this->view->SwapBytes_Ajax->setView($this->view);
    }


    public function tablaAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $resultados = $this->evaluacion->getResultadosProfesor($this->periodo,$this->ci);

            $nombre   = $resultados[0]['nombre'];

            if(empty($resultados)){

              $mensaje = "No posee Materias asignadas.";
              $HTML  = $this->SwapBytes_Html_Message->alert($mensaje);

            }else{

              $table = array('class'=> 'tableData',
                             'width'=> '50px');
              $columns = array(array( 'name'     => 'Relacines Interpersonales',
                                      'column'   => 'ri',
                                      'width'    => '100px',
                                      'rows'     => array('style' => 'text-align:center')),
                               array( 'name'     => 'Valores',
                                      'column'   => 'v',
                                      'width'    => '100px',
                                      'rows'     => array('style' => 'text-align:center')),
                               array( 'name'     => 'Desempeño Pedagógico',
                                      'column'   => 'dp',
                                      'width'    => '100px',
                                      'rows'     => array('style' => 'text-align:center')),
                               array( 'name'     => 'Conocimiento',
                                      'column'   => 'c',
                                      'width'    => '100px',
                                      'rows'     => array('style' => 'text-align:center')));

              $total = array(array( 'name'     => 'Total',
                                      'column'   => 'total',
                                      'width'    => '50px',
                                      'rows'     => array('style' => 'text-align:center')));

              $title  = array('text'     => 'Resultados',
                            'fontSize' => 15,
                            'position' => 'top'
                            );
            $legend = array('position' => 'top',
                            'fontColor' => 'rbga(255, 99, 132, 0.2)',
                            'fontSize' => 15,
                            );
            $BeginAtZero = true;
         

            $data = array('labels' => array('Relaciones Interpersonales','Valores','Desempeño Pedagógico','Conocimiento'),
                          'data' => array(array('title' => 'Distribución',
                                              'data'  => array($resultados[0]['ri'], $resultados[0]['v'], $resultados[0]['dp'], $resultados[0]['c']),
                                              'color' => 'rgba(0,120,122,0.6)',
                                              'width' => 3,
                                              'backgroundColor' => array('rgba(0,120,122,0.6)'),
                                              'bordercolor' => 'rgba(0,120,122,0.6)',
                                              'hovercolor' => 'rgba(0,120,122,1)',
                                             )
                                       )
                    );

            $json[] = $this->Une_ChartJS->setGraph($data, $title, $legend, $BeginAtZero, 'radar');


            }

            $HTML   = $this->SwapBytes_Crud_List->fill($table, $resultados, $columns);
            $HTML2   = $this->SwapBytes_Crud_List->fill($table, $resultados, $total);

            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            $json[] = $this->SwapBytes_Jquery->setHtml('tableTotal', $HTML2);
            $json[] = $this->SwapBytes_Jquery->setHtml('cedula', $this->ci);
            $json[] = $this->SwapBytes_Jquery->setHtml('nombre', $nombre);
            $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$this->ci}'");
            //var_dump($json);die;

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }        
    }

    public function photoAction() {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender();

            $id    = $this->_getParam('id', 0);
            $image = $this->usuarios->getPhoto($id);

            $this->getResponse()
                 ->setHeader('Content-type', 'image/jpeg')
                 ->setBody($image);
    }
}
