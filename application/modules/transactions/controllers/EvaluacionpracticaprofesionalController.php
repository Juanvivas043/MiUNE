<?php
	
	class Transactions_EvaluacionpracticaprofesionalController extends Zend_Controller_Action{
	private $Title = 'Transacciones \ Evaluación Práctica Profesional';	

	public function init(){
       
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Evaluaciontutores');
        Zend_Loader::loadClass('Models_DbTable_Inscripcionespasantias');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        
        $this->seguridad          = new Models_DbTable_UsuariosGrupos();
    		$this->estudiante              = new Models_DbTable_Usuarios();
        $this->Inscripcionespasantias = new Models_DbTable_Inscripcionespasantias();
    		$this->evaluaciontutores = new Models_DbTable_Evaluaciontutores();
        $this->periodo                 = new Models_DbTable_Periodos();
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

      public function usuarioAction(){

      	$this->SwapBytes_Ajax->setHeader(); 
        $ci= $this->authSpace->userId;
        $json = array();
        
        $tutoracademico = $this->evaluaciontutores->getTutor($ci);
        $grupotutor = $this->evaluaciontutores->getGrupoTutor($ci);
        $cont = sizeof($grupotutor);

        foreach ($grupotutor as $key => $value) {

          if (in_array("Tutores Empresariales",$value) && $cont>1){
            //recorro el arreglo para saber si existe el grupo tutor empresarial
            //entre todos los grupos de los arreglos
          $json[] ='$("#tableEmp").show()';
          $json[] ='$("#tableData").show()';  
          $json[] ='$("#tutorA").show()';
          $json[] ='$("#tutorE").show()';
          $json[] ='$("#cedula").html("'.$tutoracademico[0]['cedula'].'")';
          $json[] ='$("#nombre").html("'.$tutoracademico[0]['nombre'].'")';
          $json[] ='$("#apellido").html("'.$tutoracademico[0]['apellido'].'")';
          $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");

        }
          else if ($cont==1){
          $json[] ='$("#tableEmp").show()';
          $json[] ='$("#fotoDiv").hide()';   
          $json[] ='$("#tutorE").show()';  
          $json[] ='$("#cedula").html("'.$tutoracademico[0]['cedula'].'")';
          $json[] ='$("#nombre").html("'.$tutoracademico[0]['nombre'].'")';
          $json[] ='$("#apellido").html("'.$tutoracademico[0]['apellido'].'")';
          $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");

        }

          else{

          $json[] ='$("#tableData").show()'; 
          $json[] ='$("#tutorA").show()';  
          $json[] ='$("#cedula").html("'.$tutoracademico[0]['cedula'].'")';
          $json[] ='$("#nombre").html("'.$tutoracademico[0]['nombre'].'")';
          $json[] ='$("#apellido").html("'.$tutoracademico[0]['apellido'].'")';
          $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");


        }
        }

        $this->SwapBytes_Crud_Form->setJson($json);
        $this->getResponse()->setBody(Zend_Json::encode($json));


      }

      public function tablaacademicoAction(){

      	 if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
           
            $ci= $this->authSpace->userId;            
            $periodoactual = $this->periodo->getUltimo();         
            $periodoAc = $periodoactual."-TAPLE";

            $rows = $this->evaluaciontutores->getEstudiantesTutorAcademico($ci, $periodoAc);

           if(empty($rows)){

            $mensaje = "No posee pasantes";
            $HTML  = $this->SwapBytes_Html_Message->alert($mensaje);

           }else{

           	 $table = array('class'=> 'tableData',
                           'width'=> '900px');
            $columns = array(array( 'column'   => 'pk_usuario',
                                    'primary' => true,
                                    'hide'     =>true),
                             array( 'name'     => '#',
                                    'width'    => '50px',
                                    'function' => 'rownum',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'C.I.',
                                    'column'   => 'pk_usuario',
                                    'width'    => '250px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Estudiante',
                                    'column'   => 'estudiante',
                                    'width'    => '500px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Escuela',
                                    'column'   => 'escuela',
                                    'width'    => '500px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Institución',
                                    'column'   => 'institucion',
                                    'width'    => '500px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Nota',
                                    'column'   => 'nota',
                                    'width'    => '350px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Estado',
                                    'column'   => 'estado',
                                    'width'    => '350px',
                                    'rows'     => array('style' => 'text-align:center')));
            
                $other = array(array('actionName'  => 'estado',
                                 'action'      => 'evaluaciona(##pk##)',
                                 'label'       => 'Evaluacion',
                                 'column'      => 'estado',
                                 'validate'    => 'true',
                                 'intrue'      => 'Por Evaluar',
                                 'intruelabel' => ' '));
          $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'O',$other);

           }
         $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
         $this->getResponse()->setBody(Zend_Json::encode($json));

      }
	}


      public function tablaempresarialAction(){

         if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
           
            $ci= $this->authSpace->userId;
            $periodoactual = $this->periodo->getUltimo();         
            $periodoEm = $periodoactual."-TEPLE";
            $rows = $this->evaluaciontutores->getEstudiantesTutorEmpresarial($ci);
            if(empty($rows)){

            $mensaje = "No posee pasantes";
            $HTML  = $this->SwapBytes_Html_Message->alert($mensaje);

           }else{

             $table = array('class'=> 'tableData',
                           'width'=> '900px');

                         $columns = array(array( 'column'   => 'pk_usuario',
                                    'primary' => true,
                                    'hide'     =>true),
                             array( 'name'     => '#',
                                    'width'    => '50px',
                                    'function' => 'rownum',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'C.I.',
                                    'column'   => 'pk_usuario',
                                    'width'    => '250px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Estudiante',
                                    'column'   => 'estudiante',
                                    'width'    => '500px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Escuela',
                                    'column'   => 'escuela',
                                    'width'    => '500px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Institución',
                                    'column'   => 'institucion',
                                    'width'    => '500px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Nota',
                                    'column'   => 'nota',
                                    'width'    => '350px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Estado',
                                    'column'   => 'estado',
                                    'width'    => '350px',
                                    'rows'     => array('style' => 'text-align:center')));
                            
                    $other = array( array('actionName'  => 'estado',
                                 'action'      => 'evaluacione(##pk##)',
                                 'label'       => 'Evaluacion',
                                 'column'      => 'estado',
                                 'validate'    => 'true',
                                 'intrue'      => 'Por Evaluar',
                                 'intruelabel' => ' '));
          $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'O',$other);

           }
         $json[] = $this->SwapBytes_Jquery->setHtml('tableEmp', $HTML);
         $this->getResponse()->setBody(Zend_Json::encode($json));

      }
  }

  public function evaluacionaAction(){
 
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();
          
          $json = array();
          $ci= $this->authSpace->userId;
          $tutoracademico = $this->evaluaciontutores->getTutor($ci);
          $pk = $this->_getParam('pk');//cedula del estudiante
          $escuelaEstudiante = $this->Inscripcionespasantias->getPasanteEscuela($pk);  
         
          $periodoactual = $this->periodo->getUltimo();
         
          $periodoAc = $periodoactual."-TAPLE";
          $escuela = $escuelaEstudiante[0]["escuela"];
          $es=(int)$escuela;

          $estudiante= $this->evaluaciontutores->getEstudiante($pk);
          //var_dump($estudiante[0]['cedula']);die;
          $json[] = '$("#divEstudiante").show()';
          $json[] ='$("#estudiante").html("'.$estudiante[0]['estudiante'].'")';
          $json[] ='$("#cedulaEs").html("'.$estudiante[0]['cedula'].'")';

          $quiz = $this->Inscripcionespasantias->getIdQuizAcademico();

          $id = $quiz[0]["id"];
          $json[]='$("#frame").attr("src", "http://omicron.une.edu.ve/moodle/mod/quiz/view.php?id='.$id.'")';
            
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->getResponse()->setBody(Zend_Json::encode($json));
          
            
           
    }


  }
  public function evaluacioneAction(){
 
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();

          $json = array();
          $ci= $this->authSpace->userId;
          $tutoracademico = $this->evaluaciontutores->getTutor($ci);
          $pk = $this->_getParam('pk');//cedula del estudiante
          $escuelaEstudiante = $this->Inscripcionespasantias->getPasanteEscuela($pk);  
         
          $periodoactual = $this->periodo->getUltimo();
         
          $periodoEm = "129-TEPLE";
          $escuela = $escuelaEstudiante[0]["escuela"];
          $es=(int)$escuela;

          $estudiante= $this->evaluaciontutores->getEstudiante($pk);
         
          $quiz = $this->Inscripcionespasantias->getIdQuizEmpresarial($escuela);
          $id = $quiz[0]["id"];
          //var_dump($id);die;
          //$json[]='$("#frame").load(function(){ $("#frame").contents().find(\'input[value="Sí"]\').click();})'; 
            
            switch ($es) {
              case 11:
                $json[]='$("#frame").attr("src", "http://omicron.une.edu.ve/moodle/mod/quiz/view.php?id=5918")';
                break;
              case 12:
                $json[]='$("#frame").attr("src", "http://omicron.une.edu.ve/moodle/mod/quiz/view.php?id=5919")';
                break;
              case 13:             
                $json[]='$("#frame").attr("src", "http://omicron.une.edu.ve/moodle/mod/quiz/view.php?id=5920")';         
                break;  
              case 14:
                $json[]='$("#frame").attr("src", "http://omicron.une.edu.ve/moodle/mod/quiz/view.php?id=5921")';
                break;   
              case 15:
                $json[]='$("#frame").attr("src", "http://omicron.une.edu.ve/moodle/mod/quiz/view.php?id=5922")';
                break;
               case 16:
                $json[]='$("#frame").attr("src", "http://omicron.une.edu.ve/moodle/mod/quiz/view.php?id=5923")';
                break;   
              }

          $json[] = '$("#divEstudiante").show()';
          $json[] ='$("#estudiante").html("'.$estudiante[0]['estudiante'].'")';
          $json[] ='$("#cedulaEs").html("'.$estudiante[0]['cedula'].'")';
          $this->SwapBytes_Crud_Form->setJson($json);
          $this->getResponse()->setBody(Zend_Json::encode($json));     

        }
      }

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
?>
