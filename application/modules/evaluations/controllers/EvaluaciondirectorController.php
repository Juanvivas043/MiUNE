<?php

class Evaluations_EvaluaciondirectorController extends Zend_Controller_Action {
    
    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
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
        $this->grupo = $this->evaluacion->getGrupoDirector($this->ci);

        
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

        $this->view->title                  = "Evaluaciones / Evaluación Docente Dirección";
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

            $profesores = $this->evaluacion->getProfesoresPorEscuela($this->periodo,$this->grupo[0]['pk_atributo'],$this->grupo[0]['pk_usuariogrupo'] );

           if(empty($profesores)){

            $mensaje = "No hay profesores asignados.";
            $HTML  = $this->SwapBytes_Html_Message->alert($mensaje);

           }else{

             $table = array('class'=> 'tableData',
                           'width'=> '900px');
            $columns = array(array( 'column'   => 'pk_usuariogrupo',
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
          $HTML   = $this->SwapBytes_Crud_List->fill($table, $profesores, $columns, 'O',$other);

           }
          $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);

          $this->getResponse()->setBody(Zend_Json::encode($json));
        }        
    }

    public function evaluacionAction(){
 
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();

          $this->ugprofesor = $this->_getParam('pk');
          $_SESSION['ugprofesor'] = $this->ugprofesor;

          $json[] = "$('#formulario').show();
                      $('#tableData').hide();
                      $('#Siguiente').show();";

          if (empty($this->evaluacion->getAsignacionEncuestaDir($this->grupo[0]['pk_usuariogrupo'],$this->ugprofesor))) {
          
            $this->evaluacion->insertPreEvaluacionDir($this->grupo[0]['pk_usuariogrupo'], 35,$this->ugprofesor);
          }

          $this->getResponse()->setBody(Zend_Json::encode($json));
      }    
                    
    }


    public function finalizarAction(){
 
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();

          $this->asignacionencuesta = $this->evaluacion->getAsignacionEncuestaDir($this->grupo[0]['pk_usuariogrupo'],$_SESSION['ugprofesor']);

          $array_respuestas = $this->_getParam('results');
          
          $returnU = $this->evaluacion->updateAsignacionEncuesta($this->asignacionencuesta[0]['pk_asignacionencuesta']);
          $returnI = $this->evaluacion->insertRespuestas($this->asignacionencuesta[0]['pk_asignacionencuesta'], $this->asignacionencuesta[0]['fk_encuesta'], $array_respuestas);
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

      }    
                    
    }
}

?>