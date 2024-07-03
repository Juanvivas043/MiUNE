<?php

class Evaluations_AutoevaluacionprofesorController extends Zend_Controller_Action {
    
    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Une_ChartJS');
        Zend_Loader::loadClass('Une_Sweetalert');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_EvaluacionProfesores');

        $this->Une_Filtros              = new Une_Filtros();
        $this->sweetalert               = new Une_Sweetalert();
        $this->usuarios                 = new Models_DbTable_Usuarios();
        $this->periodos                 = new Models_DbTable_Periodos();
        $this->seguridad                = new Models_DbTable_UsuariosGrupos();
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
        $this->ug = $this->usuarios->getUsuarioGrupo($this->ci, 854)[0]['pk_usuariogrupo'];
        $this->asignacionencuesta = $this->evaluacion->getAsignacionEncuestaProf($this->ug);
        
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

        $this->view->title                  = "Evaluaciones / Autoevaluación del Profesor";
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

            $materias = $this->evaluacion->getMateriasProfesor($this->ci,$this->periodo);

            $cedula   = $materias[0]['pk_usuario'];
            $nombre   = $materias[0]['nombre'];
            $apellido = $materias[0]['apellido'];

            if(empty($materias)){

              $mensaje = "No posee Materias asignadas.";
              $HTML  = $this->SwapBytes_Html_Message->alert($mensaje);

            }else{

              $table = array('class'=> 'tableData',
                             'width'=> '650px');
              $columns = array(array( 'name'     => 'Asignatura',
                                      'column'   => 'materia',
                                      'width'    => '300px',
                                      'rows'     => array('style' => 'text-align:center')),
                               array( 'name'     => 'Escuela',
                                      'column'   => 'escuela',
                                      'width'    => '300px',
                                      'rows'     => array('style' => 'text-align:center')),
                               array( 'name'     => 'Sección',
                                      'column'   => 'valor',
                                      'width'    => '50px',
                                      'rows'     => array('style' => 'text-align:center')));

              if (!$this->asignacionencuesta[0]['finalizada']) {
                
                $HTML   = $this->SwapBytes_Crud_List->fill($table, $materias, $columns);
                $json[] = "$('#Autoevaluar').show();";

              }else{

                $mensaje = "Ya ha realizado la Autoevaluación.";
                $HTML  = $this->SwapBytes_Html_Message->alert($mensaje);
              }


            }
          
            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            $json[] = $this->SwapBytes_Jquery->setHtml('cedula', $cedula);
            $json[] = $this->SwapBytes_Jquery->setHtml('nombre', $nombre);
            $json[] = $this->SwapBytes_Jquery->setHtml('apellido', $apellido);
            $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$cedula}'");
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

    public function evaluacionAction(){
 
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();

          $infoie = $this->evaluacion->getAsignacionEncuestaProf($this->ug);
          $json[] = "$('#autoeval').show();
                      $('#formulario').show();
                      $('#tableData').hide();
                      $('#infoProf').hide();
                      $('#Autoevaluar').hide();
                      $('#Siguiente').show();";

         
          $this->getResponse()->setBody(Zend_Json::encode($json));
      }    
                    
    }


    public function finalizarAction(){
 
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();

          $array_respuestas = $this->_getParam('results');
          //var_dump($array_respuestas);die;
          
          $returnU = $this->evaluacion->updateAsignacionEncuesta($this->asignacionencuesta[0]['pk_asignacionencuesta']);
          $returnI = $this->evaluacion->insertRespuestas($this->asignacionencuesta[0]['pk_asignacionencuesta'] , $this->asignacionencuesta[0]['fk_encuesta'] , $array_respuestas);
          if ($returnU && $returnI) {
            $json[] = $this->sweetalert->setBasicAlert("success","Éxito","Encuesta realizada con éxito!");
            $json[] .= "$('.confirm').click(function(){
                           location.reload();
                        })";
          }else{

            $this->evaluacion->errorEncuesta($this->asignacionencuesta[0]['pk_asignacionencuesta']);
            $json[] = $this->sweetalert->setBasicAlert("error","Error","Ocurrió un problema con su encuesta, vuelva a intentar.");

          }

          $this->getResponse()->setBody(Zend_Json::encode($json));  

      }    
                    
    }

}


/*
            $title  = array('text'     => 'titulo yolo',
                            'fontSize' => 15,
                            'position' => 'top'
                            );
            $legend = array('position' => 'top',
                            'fontColor' => 'rbga(255, 99, 132, 0.2)',
                            'fontSize' => 15,
                            );
            $BeginAtZero = true;
         

            $data = array('labels' => array(1,2,3,4,5,6,7,8,9,10,11,12),
                          'data' => array(array('title' => 'aprobados',
                                              'data'  => [324, 135, 188, 169, 142, 141, 113, 92, 148, 104, 102, 99],
                                              'color' => 'rgba(0,120,122,1)',
                                              'width' => 3,
                                              'bordercolor' => 'rgba(0,120,122,1)',
                                              'hovercolor' => 'rgba(0,120,122,0.8)',
                                             ),
                                        array('title' => 'reprobados',
                                              'data'  => [113, 31, 49, 27, 15, 19, 26, 16, 6, 15, 7, 10],
                                              'color' => 'rgba(225,149,54,1)',
                                              'width' => 3,
                                              'bordercolor' => 'rgba(0,120,122,1)',
                                              'hovercolor' => 'rgba(225,149,54,0.8)',
                                             ),
                                        array('title' => 'retirados',
                                              'data'  => [31, 41, 41, 31, 32, 22, 7, 6, 8, 8, 3,5],
                                              'color' => 'rgba(119,44,70,1)',
                                              'width' => 3,
                                              'bordercolor' => 'rgba(0,120,122,1)',
                                              'hovercolor' => 'rgba(119,44,70,0.8)',
                                             ),
                                       )
                    );

            $json[] = $this->Une_ChartJS->setGraph($data, $title, $legend, $BeginAtZero, 'line');
*/

?>