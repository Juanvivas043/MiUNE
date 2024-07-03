<?php
class Transactions_CuestionariopasantiasController extends Zend_Controller_Action{
  private $Title = 'Transacciones \ Cuestionario Pasantías Laborales'; 

  public function init(){
       
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Inscripcionespasantias');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        
        $this->seguridad          = new Models_DbTable_UsuariosGrupos();
        $this->estudiante              = new Models_DbTable_Usuarios();
        $this->periodo                 = new Models_DbTable_Periodos(); 
        $this->Inscripcionespasantias = new Models_DbTable_Inscripcionespasantias();
        $this->filtros                  = new Une_Filtros();
        $this->CmcBytes_Filtros         = new CmcBytes_Filtros();
        $this->Swapbytes_array          = new SwapBytes_Array();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->CmcBytes_Redirect        = new CmcBytes_Redirect();
        //$this->CmcBytes_Profit       = new CmcBytes_Profit();
        $this->logger = Zend_Registry::get('logger');
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);
        $this->SwapBytes_Crud_Action->setEnable(true, true, false, false, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(false);//quitar barra
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

  
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();

    }
      function preDispatch() {
       if(!Zend_Auth::getInstance()->hasIdentity()) {
           $this->_helper->redirector('index', 'login', 'default');
       }

       if(!$this->seguridad->haveAccessToModule()) {
           $this->_helper->redirector('accesserror', 'profile', 'default');
       }
    }

     public function indexAction() {
          
            $this->view->title   = $this->Title;
            $this->view->filters = $this->filtros;
            $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
            $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
            $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
            $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
            $this->view->SwapBytes_Ajax = $this->SwapBytes_Ajax;
            $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
            $this->view->SwapBytes_Ajax->setView($this->view);
            $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
            
            

      }
 public function validar($ci){
          $cont = 0;
          $per = "0123456789";
          for ($i = 0; $i<strlen($ci);$i++){
              for($j = 0; $j<strlen($per);$j++){
                  if($ci[$i]== $per[$j]){
                      $cont = $cont + 1;
                       
                  }
              }
              
                           
          }
          if($cont == strlen($ci) ){
             return true;  
                                   }
        
          return false;
      }

 public function estudianteAction(){
   if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $ci=$this->authSpace->userId;
            
            $json = array();
            $periodoactual = $this->periodo->getUltimo();
            //$periodo = $periodoactual."-CPLE";
            $periodoAc = $periodoactual."-TAPLE";
            $periodoEm = $periodoactual."-TEPLE";
            
            $escuelaEstudiante = $this->Inscripcionespasantias->getPasanteEscuela($ci);
            $escuela = $escuelaEstudiante[0]["escuela"];
            $pasante= $this->Inscripcionespasantias->getEstudianteInscritoPracticas($ci,$escuela,$periodo);
            
            $listo = $this->Inscripcionespasantias->getEstudiantePracticasListo($ci);
            $evaAc = $this->Inscripcionespasantias->getEvaluacionAcademica($ci);
            $evaEm = $this->Inscripcionespasantias->getEvaluacionEmpresarial($ci);
		   
            
            $id = $quiz[0]["id"];
            
            if(!empty($pasante[0]['nota'])){

              $json[] ='$("#realizado").show()';
              $json[]= '$("#quizframe").hide()';
              $json[] ='$("#calificacion").html("'.$pasante[0]['nota'].'/5'.'")';
              $json[] ='$("#academica").html("'.$evaAc[0]['nota'].'")';
              $json[] ='$("#empresarial").html("'.$evaEm[0]['nota'].'")';
              $json[] ='$("#cedula").html("'.$pasante[0]['cedula'].'")';
              $json[] ='$("#nombre").html("'.$pasante[0]['nombre'].'")';
              $json[] ='$("#apellido").html("'.$pasante[0]['apellido'].'")';
              $json[] ='$("#escuela").html("'.$pasante[0]['escuela'].'")';
            }
            //no tiene la materia inscrita ni la ha pasado
            else if(empty($pasante) && empty($listo)){
              
              $json[]='$("#tblusuario").hide()';
              $json[]='$("#sinpasantias").show()';
              $json[]='$("#quizframe").hide()';
              $this->SwapBytes_Crud_Form->setJson($json);
              $this->getResponse()->setBody(Zend_Json::encode($json));
            
            }

            else if (!empty($listo)) {
              
             $json[]='$("#listo").show()';
             $json[]='$("#quizframe").hide()';
             $json[] ='$("#cedula").html("'.$listo[0]['cedula'].'")';
             $json[] ='$("#nombre").html("'.$listo[0]['nombre'].'")';
             $json[] ='$("#apellido").html("'.$listo[0]['apellido'].'")';
             $json[] ='$("#escuela").html("'.$listo[0]['escuela'].'")';
             $this->SwapBytes_Crud_Form->setJson($json);
             $this->getResponse()->setBody(Zend_Json::encode($json));

            }

            else
            {
            $json[] = '$("#btnAuto").show()';
            $json[] ='$("#cedula").html("'.$pasante[0]['cedula'].'")';
            $json[] ='$("#nombre").html("'.$pasante[0]['nombre'].'")';
            $json[] ='$("#apellido").html("'.$pasante[0]['apellido'].'")';
            $json[] ='$("#escuela").html("'.$pasante[0]['escuela'].'")';
          }
            $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");
            //var_dump($this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'"));die;
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->getResponse()->setBody(Zend_Json::encode($json)); 
  }
 }

 public function cuestionarioAction(){
    if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $ci=$this->authSpace->userId;
            
            $json = array();
            $periodoactual = $this->periodo->getUltimo();
            $periodo = $periodoactual."-CPLE";
            $escuelaEstudiante = $this->Inscripcionespasantias->getPasanteEscuela($ci);
            $escuela = $escuelaEstudiante[0]["escuela"];
            $pasante= $this->Inscripcionespasantias->getEstudianteInscritoPracticas($ci);
            $es=(int)$escuela;
         
            

            switch ($es) {
              case 11:
                $json[]='$("#quizframe").attr("src", "http://omicron.une.edu.ve/moodle/mod/quiz/view.php?id=4770")';
                break;
              case 12:
                $json[]='$("#quizframe").attr("src", "http://omicron.une.edu.ve/moodle/mod/quiz/view.php?id=4771")';
                break;
              case 13:             
                $json[]='$("#quizframe").attr("src", "http://omicron.une.edu.ve/moodle/mod/quiz/view.php?id=4772")';         
                break;  
              case 14:
                $json[]='$("#quizframe").attr("src", "http://omicron.une.edu.ve/moodle/mod/quiz/view.php?id=4773")';
                break;   
              case 15:
                $json[]='$("#quizframe").attr("src", "http://omicron.une.edu.ve/moodle/mod/quiz/view.php?id=4774")';
                break;
              case 16:
                $json[]='$("#quizframe").attr("src", "http://omicron.une.edu.ve/moodle/mod/quiz/view.php?id=4775")';
                break;   
              }

            
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->getResponse()->setBody(Zend_Json::encode($json));

    }  
  }
  

/*  public function esperarAction(){
     if ($this->_request->isXmlHttpRequest()) {
     $this->SwapBytes_Ajax->setHeader(); 
     $json = array();
     sleep(60);
     $json[] = '$("#quizframe").contents().find(\'input[value="Enviar todo y terminar"]\').click(function(){window.location.reload()})';
     $json[] = '$("#quizframe").contents().find(\'input[value="Enviar"]\').hide()';
     $json[] = '$("#quizframe").contents().find(\'input[value="Enviar página"]\').hide()';
     $json[] = '$("#quizframe").contents().find(\'input[value="Guardar sin enviar"]\').hide()';
     $this->SwapBytes_Crud_Form->setJson($json);
            $this->getResponse()->setBody(Zend_Json::encode($json));
     }

  }*/

  public function photoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $id    = $this->_getParam('id', 0);
        $image = $this->estudiante->getPhoto($id);

        $this->getResponse()
             ->setHeader('Content-type', 'image/jpeg')
             ->setBody($image);
    }
}
