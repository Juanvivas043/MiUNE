<?php

class Evaluations_EvaluacionestudiantesController extends Zend_Controller_Action {
    
    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Une_ChartJS');
        Zend_Loader::loadClass('Une_Sweetalert');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_EvaluacionProfesores');

        $this->Une_Filtros              = new Une_Filtros();
        $this->Une_ChartJS              = new Une_ChartJS();
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

        $this->view->title                  = "Evaluaciones / Evaluación del Profesor";
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

            $materias = $this->evaluacion->getProfesoresPorEstudiante($this->ci,$this->periodo);
            //$materias = $this->evaluacion->getProfesorPorSede($this->periodo);
            //var_dump($materias);die;
            /*
$ci= $this->authSpace->userId;            
            $periodoactual = $this->periodo->getUltimo();         
            $periodoAc = $periodoactual."-TAPLE";

            $rows = $this->evaluaciontutores->getEstudiantesTutorAcademico($ci, $periodoAc);
*/
           if(empty($materias)){

            $mensaje = "No posee Materias asignadas.";
            $HTML  = $this->SwapBytes_Html_Message->alert($mensaje);

           }else{

             $table = array('class'=> 'tableData',
                           'width'=> '900px');
            $columns = array(array( 'column'   => 'pk_recordacademico',
                                    'primary' => true,
                                    'hide'     =>true),
                             array( 'name'     => 'Profesor',
                                    'column'   => 'nombre',
                                    'width'    => '300px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Asignatura',
                                    'column'   => 'materia',
                                    'width'    => '350px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Estado',
                                    'column'   => 'estado',
                                    'width'    => '100px',
                                    'rows'     => array('style' => 'text-align:center')));
            
                $other = array(array('actionName'  => 'estado',
                                 'action'      => 'evaluacion(##pk##)',
                                 'label'       => 'Evaluación',
                                 'column'      => 'estado',
                                 'validate'    => 'true',
                                 'intrue'      => 'Por Evaluar',
                                 'intruelabel' => ''));
          $HTML   = $this->SwapBytes_Crud_List->fill($table, $materias, $columns, 'O',$other);

           }
         $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);

/*

            $title  = array('text'     => 'Resultados',
                            'fontSize' => 15,
                            'position' => 'top'
                            );
            $legend = array('position' => 'top',
                            'fontColor' => 'rbga(255, 99, 132, 0.2)',
                            'fontSize' => 15,
                            );
            $BeginAtZero = true;
         

            $data = array('labels' => array(1,2,3,4/*,5,6,7,8,9,10,11,12),
                          'data' => array(array('title' => 'aprobados',
                                              'data'  => [3.4, 2.2, 3.3, 1.4,/* 142, 141, 113, 92, 148, 104, 102, 99],
                                              'color' => 'rgba(0,120,122,1)',
                                              'width' => 3,
                                              'backgroundColor' => ['rgba(0,120,122,1)', 'rgba(0,120,122,1)','rgba(0,120,122,1)', 'rgba(0,120,12,1)'],
                                              'bordercolor' => 'rgba(0,120,122,1)',
                                              'hovercolor' => 'rgba(0,120,122,0.8)',
                                             ),*/
                                        /*array('title' => 'reprobados',
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

            //$json[] = $this->Une_ChartJS->setGraph($data, $title, $legend, $BeginAtZero, 'pie');*/

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }        
    }


    public function evaluacionAction(){
 
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();

          $this->record = $this->_getParam('pk');
          $_SESSION['record'] =  $this->_getParam('pk');
          $this->ugprofesore = $this->evaluacion->getUsuariogrupoProfesorByRecord($this->record)[0]['pk_usuariogrupo'];
          $infoie = $this->evaluacion->getInscripcionEncuestaEstudiante($this->record);
          $json[] = "$('#formulario').show();
                      $('#tableData').hide();
                      $('#Siguiente').show();";

          if (is_null($this->evaluacion->getPkAsignacionEncuesta($this->record)[0]['pk_asignacionencuesta'])) {
          
          
            $this->evaluacion->insertPreEvaluacion($infoie[0]['pk_inscripcionencuesta'],$infoie[0]['pk_recordacademico'], $infoie[0]['fk_usuariogrupo'], $infoie[0]['fk_encuesta'], $this->ugprofesore);
          }
          //var_dump($this->record);die;
          /*if (is_null($parte)) {
            $parte = 20254;
          }
          if ($parte < 20256 && $parte >= 20254) {
            $json[] = "$('#Siguiente').show();";
          }else{
            $json[] = "$('#Siguiente').hide();";
          }
          if ($parte > 20254 && $parte <= 20256) {
            $json[] = "$('#Anterior').show();";
          }else{
            $json[] = "$('#Anterior').hide();";
          }*/
          //$this->_getParam('pk')
          //var_dump($this->_getParam('pk'));die;
          /*
          $this->record = $this->_getParam('pk');
          
          $preguntas = $this->evaluacion->getPreguntasEstudiantes( $this->record, $parte);


            $table = array('class'=> 'tableData',
                           'width'=> '900px');
            $columns = array(array( 'column'   => 'pk_pregunta',
                                    'primary' => true,
                                    'hide'     =>true),
                             array( 'name'     => '#',
                                    'column'   => 'ordinal',
                                    'width'    => '10px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Preguntas',
                                    'column'   => 'pregunta',
                                    'width'    => '700px',
                                    'rows'     => array('style' => 'text-align:left')),
                             array('name'     => 'Siempre',
                                   'width'    => '50px',
                                   'rows'     => array('style'      => 'text-align:center'),
                                   'control'  => array('tag'        => 'input',
                                                       'type'       => 'radio',
                                                       'name'       => '##pk_pregunta##',
                                                       'class'      => '##pk_pregunta##',
                                                       'value'      => '4',
                                                       'required'   => 'true')),
                           array('name'     => 'Casi Siempre',
                                 'width'    => '50px',
                                 'rows'     => array('style'      => 'text-align:center'),
                                 'control'  => array('tag'        => 'input',
                                                     'type'       => 'radio',
                                                     'name'       => '##pk_pregunta##',
                                                     'class'      => '##pk_pregunta##',
                                                     'value'      => '3')),
                           array('name'     => 'Algunas Veces',
                                 'width'    => '50px',
                                 'rows'     => array('style'      => 'text-align:center'),
                                 'control'  => array('tag'        => 'input',
                                                     'type'       => 'radio',
                                                     'name'       => '##pk_pregunta##',
                                                     'class'      => '##pk_pregunta##',
                                                     'value'      => '2')),
                           array('name'     => 'Nunca',
                                 'width'    => '50px',
                                 'rows'     => array('style'      => 'text-align:center'),
                                 'control'  => array('tag'        => 'input',
                                                     'type'       => 'radio',
                                                     'name'       => '##pk_pregunta##',
                                                     'class'      => '##pk_pregunta##',
                                                     'value'      => '1')));

          $HTML   = $this->SwapBytes_Crud_List->fill($table, $preguntas, $columns);

           
          $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);*/
          $this->getResponse()->setBody(Zend_Json::encode($json));
      }    
                    
    }


    public function finalizarAction(){
 
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();

          $this->record = $_SESSION['record'];
          $infoie = $this->evaluacion->getInscripcionEncuestaEstudiante($this->record);
          $this->asignacionencuesta = $this->evaluacion->getPkAsignacionEncuesta($this->record)[0]['pk_asignacionencuesta'];
          $array_respuestas = $this->_getParam('results');
          
          $returnU = $this->evaluacion->updateAsignacionEncuesta($this->asignacionencuesta);
          $returnI = $this->evaluacion->insertRespuestas($this->asignacionencuesta, $infoie[0]['fk_encuesta'], $array_respuestas);
          if ($returnU && $returnI) {
            $json[] = $this->sweetalert->setBasicAlert("success","Éxito","Encuesta realizada con éxito!");
            $json[] .= "$('.confirm').click(function(){
                           location.reload();
                        })";
          }else{

            $this->evaluacion->errorEncuesta($this->asignacionencuesta);
            $json[] = $this->sweetalert->setBasicAlert("error","Error","Ocurrió un problema con su encuesta, vuelva a intentar.");

          }

          $this->getResponse()->setBody(Zend_Json::encode($json));  

         // die;
         /* $this->evaluacion->updateAsignacionEncuesta($this->asignacionencuesta);
          $this->evaluacion->insertRespuestas($this->asignacionencuesta, $infoie[0]['fk_encuesta'], $array_respuestas);*/


          /*$parte = $this->_getParam('parte');
          if (is_null($parte)) {
            $parte = 20254;
          }
          if ($parte < 20256 && $parte >= 20254) {
            $json[] = "$('#Siguiente').show();";
          }else{
            $json[] = "$('#Siguiente').hide();";
          }
          if ($parte > 20254 && $parte <= 20256) {
            $json[] = "$('#Anterior').show();";
          }else{
            $json[] = "$('#Anterior').hide();";
          }
          //$this->_getParam('pk')
          //var_dump($this->_getParam('pk'));die;
          if (!is_null($this->_getParam('pk'))) {
            $this->record = $this->_getParam('pk');
          }



          $HTML   = $this->SwapBytes_Crud_List->fill($table, $preguntas, $columns);

           
          $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
          $this->getResponse()->setBody(Zend_Json::encode($json));*/
      }    
                    
    }

}

?>