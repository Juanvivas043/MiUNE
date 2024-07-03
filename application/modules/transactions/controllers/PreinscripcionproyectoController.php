<?php

class Transactions_PreinscripcionproyectoController extends Zend_Controller_Action{
    private $Title   = 'Transacciones \ Feria de Proyectos, Pre Inscripción';
    
     public function init(){
       
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Asignacionesproyectos');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Inscripcionespasantias');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Forms_Servicioonline');
        Zend_Loader::loadClass('Forms_Preinscripcionproyecto');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
               
        $this->seguridad                = new Models_DbTable_UsuariosGrupos();
        $this->preinproyecto            = new Models_DbTable_Asignacionesproyectos();
        $this->estudiante               = new Models_DbTable_Usuarios();
        $this->periodo                  = new Models_DbTable_Periodos();
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
        

        //Formulario
        $this->view->form = new Forms_Servicioonline();
        $this->SwapBytes_Form->set($this->view->form);
        $this->view->form = $this->SwapBytes_Form->get();
        
        $this->view->formpreinpro = new Forms_Preinscripcionproyecto;
        $this->SwapBytes_Form->set($this->view->formpreinpro);
        $this->view->formpreinpro = $this->SwapBytes_Form->get();
        
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
      public function estudianteAction (){
        $this->SwapBytes_Ajax->setHeader(); 
        $ci= $this->authSpace->userId;
        $json = array();
        $this->validar($ci);
              
        
        $estudiante = $this->preinproyecto->getInfoEstudiante($ci); //info del estudiante que va a preinscribirse
        $estudianteInscrito = $this->preinproyecto->getInfoInscrito($ci);
        $estudianteListo = $this->preinproyecto->getInfoEstudianteListo($ci);
        

        $periodoactual = $this->periodo->getUltimo();
        $periodosiguiente = $this->periodo->getMasNuevo();
        $fechaPuntual = $this->preinproyecto->getFechaPuntuales($periodosiguiente);
        $fechaEstudiantePuntual= $fechaPuntual[0]['fechainicio'];
        $fechafinP= date("d-m-Y", strtotime($fechaPuntual[0]['fechafin']));
        $fechainicioPE = date("d-m-Y", strtotime($fechaEstudiantePuntual));


        $fechaRezagado = $this->preinproyecto->getFechaRezagados($periodosiguiente);
        $fechaEstudianteRezagado= $fechaRezagado[0]['fechainicio'];
        $fechainicioR = date("d-m-Y", strtotime($fechaEstudianteRezagado));
        $fechafinR= date("d-m-Y", strtotime($fechaRezagado[0]['fechafin']));
       
        if(empty($estudiante)){
            /*
             * No tiene inscrito en el periodo la materia SERVICIO COMUNITARIO I o aun no la ha aprobado
             */
             $json[] = '$("#info").hide()';
             $json[] = '$("#tblestudiante").hide()';
             $json[] = '$("#tblinscrito").hide()';
             $json[] = '$("#sinservicio").show()';
             //$json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");
             $this->SwapBytes_Crud_Form->setJson($json);
             $this->getResponse()->setBody(Zend_Json::encode($json));
              
        }
        //quitar una vez culminado este período
        
        else  if($estudianteInscrito[0]['cedula']==$estudiante[0]['cedula'] && empty($estudianteListo)){
            /*
             * Ya tiene un proyecto inscrito
             */

            $json[] ='$("#info").hide()';
            $json[] ='$("#c_estudiantetxt").html("'.$estudiante[0]['cedula'].'")';
            $json[] ='$("#n_estudiantetxt").html("'.$estudiante[0]['nombre'].'")';
            $json[] ='$("#a_estudiantetxt").html("'.$estudiante[0]['apellido'].'")';
            $json[] ='$("#e_estudiantetxt").html("'.$estudiante[0]['escuela'].'")';
            $json[] ='$("#e_proyectotxt").html("'.$estudianteInscrito[0]['proyecto'].'")';
            $json[] ='$("#imprimir").show()';
            $json[]= '$("#cartaex").show()';
            $json[]= '$("#cartacom").show()';
            $json[] ='$("#inscrito").show()';
            $json[] ='$("#leyenda").show()';
            //$json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->getResponse()->setBody(Zend_Json::encode($json));
      
        }
        
        else if($estudianteListo[0]['cedula']==$estudiante[0]['cedula']){
                   /* Ya culmino el requisito de servicio comunitario*/
                  $json[] ='$("#listo").show()';
                  $json[] ='$("#info").hide()';
                  $json[] ='$("#c_estudiantetxt").html("'.$estudianteListo[0]['cedula'].'")';
                  $json[] ='$("#n_estudiantetxt").html("'.$estudianteListo[0]['nombre'].'")';
                  $json[] ='$("#a_estudiantetxt").html("'.$estudianteListo[0]['apellido'].'")';
                  $json[] ='$("#e_estudiantetxt").html("'.$estudianteListo[0]['escuela'].'")';
                  $json[] ='$("#imprimir").hide()';
                  $json[]= '$("#cartaex").hide()';
                  $json[]= '$("#cartacom").hide()';
                  $json[] ='$("#inscrito").hide()';
                   $json[] ='$("#leyenda").hide()';
                  $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");
                  //var_dump($this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'"));die;
                  $this->SwapBytes_Crud_Form->setJson($json);
                  $this->getResponse()->setBody(Zend_Json::encode($json));

            }

        

        
        else if($estudiante[0]['periodo']!=$periodoactual ){
          
          /*
            Estudiante que aprobo el taller en semestre pasados y no inscribio el proyecto
           */
          
           $mensajeuno = "Tiene que esperar a la fecha: "; 
           $mensajedos = " para preinscribir el proyecto";
           $json[] = '$("#tblinscrito").hide()';
           $json[] = '$("#tblestudiante").show()';
           $json[] = '$("#imprimir").hide()';
           $json[] = '$("#mensaje").show()';
           $json[] = '$("#rangofecha").show()';
           $json[] = '$("#c_estudiantetxt").html("'.$estudiante[0]['cedula'].'")';
           $json[] = '$("#n_estudiantetxt").html("'.$estudiante[0]['nombre'].'")';
           $json[] = '$("#a_estudiantetxt").html("'.$estudiante[0]['apellido'].'")';
           $json[] = '$("#e_estudiantetxt").html("'.$estudiante[0]['escuela'].'")';
           $json[] = '$("#e_fechatxt").html("'.$mensajeuno.$fechainicioR." y tiene hasta ".$fechafinR.$mensajedos.'")';
           //$json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");
           
           $this->SwapBytes_Crud_Form->setJson($json);

           if ($estudiante[0]['fecha']>=$fechaRezagado[0]['fechainicio']) {
               $json[] = '$("#tblinscrito").hide()';
               $json[] = '$("#tblestudiante").show()';
               $json[] = '$("#imprimir").hide()';
               $json[] = '$("#info").hide()';
               $json[] = '$("#tableData").show()';
               $json[] = '$("#mensaje").show()';
               $json[] = '$("#rangofecha").hide()';
               $json[] = '$("#c_estudiantetxt").html("'.$estudiante[0]['cedula'].'")';
               $json[] = '$("#n_estudiantetxt").html("'.$estudiante[0]['nombre'].'")';
               $json[] = '$("#a_estudiantetxt").html("'.$estudiante[0]['apellido'].'")';
               $json[] = '$("#e_estudiantetxt").html("'.$estudiante[0]['escuela'].'")';
               //$json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");
               $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
               $this->SwapBytes_Crud_Form->setJson($json);
               $this->getResponse()->setBody(Zend_Json::encode($json));


               if($estudiante[0]['fecha']>$fechaRezagado[0]['fechafin']){

                 //paso la fecha para el estudiante 
                 $json[] = '$("#tblinscrito").hide()';
                 $json[] = '$("#tblestudiante").show()';
                 $json[] = '$("#imprimir").hide()';
                 $json[] = '$("#mensaje").show()';
                 $json[] = '$("#info").hide()';
                 $json[] = '$("#fin").show()';
                 $json[] = '$("#tableData").hide()';
                 $json[] = '$("#c_estudiantetxt").html("'.$estudiante[0]['cedula'].'")';
                 $json[] = '$("#n_estudiantetxt").html("'.$estudiante[0]['nombre'].'")';
                 $json[] = '$("#a_estudiantetxt").html("'.$estudiante[0]['apellido'].'")';
                 $json[] = '$("#e_estudiantetxt").html("'.$estudiante[0]['escuela'].'")';
                 //$json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");
                 
                 $this->SwapBytes_Crud_Form->setJson($json);
                 $this->getResponse()->setBody(Zend_Json::encode($json));

               }


           }
       }

       
       else if ($estudiante[0]['fecha']<$fechaEstudiantePuntual){

          /*
            EL estudiante no se encuentra en la fecha que fue indicada para preinscribir el proyecto
           */
           $mensajeuno = "Tiene que esperar a la fecha: "; 
           $mensajedos = " para preinscribir el proyecto";
           $json[] = '$("#tblinscrito").hide()';
           $json[] = '$("#tblestudiante").show()';
           $json[] = '$("#imprimir").hide()';
           $json[] = '$("#rangofecha").show()';
           $json[] = '$("#c_estudiantetxt").html("'.$estudiante[0]['cedula'].'")';
           $json[] = '$("#n_estudiantetxt").html("'.$estudiante[0]['nombre'].'")';
           $json[] = '$("#a_estudiantetxt").html("'.$estudiante[0]['apellido'].'")';
           $json[] = '$("#e_estudiantetxt").html("'.$estudiante[0]['escuela'].'")';
           $json[] = '$("#e_fechatxt").html("'.$mensajeuno.$fechainicioPE." y tiene hasta ".$fechafinP.$mensajedos.'")';
           //$json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");
           $this->SwapBytes_Crud_Form->setJson($json);
           $this->getResponse()->setBody(Zend_Json::encode($json));

       } 

       else if ($estudiante[0]['fecha']>$fechaPuntual[0]['fechafin'] ) {
          /*
            La fecha culmino estudiante puntual
           */
           
           $json[] = '$("#tblinscrito").hide()';
           $json[] = '$("#tblestudiante").show()';
           $json[] = '$("#fin").show()';
           $json[] = '$("#imprimir").hide()';
           $json[] = '$("#tableData").hide()';
           $json[] = '$("#c_estudiantetxt").html("'.$estudiante[0]['cedula'].'")';
           $json[] = '$("#n_estudiantetxt").html("'.$estudiante[0]['nombre'].'")';
           $json[] = '$("#a_estudiantetxt").html("'.$estudiante[0]['apellido'].'")';
           $json[] = '$("#e_estudiantetxt").html("'.$estudiante[0]['escuela'].'")';
           //$json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");
           $this->SwapBytes_Crud_Form->setJson($json);
           $this->getResponse()->setBody(Zend_Json::encode($json));

       }

       else {
           //Sin proyecto y cumple con la fecha 
           $json[] = '$("#tblinscrito").hide()';
           $json[] = '$("#tblestudiante").show()';
           $json[] = '$("#imprimir").hide()';
           $json[] = '$("#info").hide()';
           $json[] = '$("#tableData").show()';
           $json[] = '$("#c_estudiantetxt").html("'.$estudiante[0]['cedula'].'")';
           $json[] = '$("#n_estudiantetxt").html("'.$estudiante[0]['nombre'].'")';
           $json[] = '$("#a_estudiantetxt").html("'.$estudiante[0]['apellido'].'")';
           $json[] = '$("#e_estudiantetxt").html("'.$estudiante[0]['escuela'].'")';
           //$json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");
           $this->SwapBytes_Crud_Form->setJson($json);
           //var_dump($json);die;
           
       }
      $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");
      //var_dump($this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'"));die;
      $this->SwapBytes_Crud_Form->setJson($json);
      $this->getResponse()->setBody(Zend_Json::encode($json)); 
      
      }
      public function tablaAction(){
            if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
           
            $ci= $this->authSpace->userId;
            $estudiante = $this->preinproyecto->getInfoEstudiante($ci);
            $escuela=$estudiante[0]['fkescuela'];
            $sede = $estudiante[0]['sede'];
                      
            $rows = $this->preinproyecto->getProyectos($escuela,$sede);
           
           if(empty($rows)){

            $mensaje = "No hay proyectos cargados para este periodo";
            $HTML  = $this->SwapBytes_Html_Message->alert($mensaje);

           }
           else
           {

            $table = array('class'=> 'tableData',
                           'width'=> '1020pxx');
            $columns = array(array( 'column'   => 'pk_asignacionproyecto',
                                    'primary' => true,
                                    'hide'     =>true),
                             array( 'name'     => '#',
                                    'width'    => '100px',
                                    'function' => 'rownum',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Proyecto',
                                    'column'   => 'proyecto',
                                    'width'    => '500px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Institución',
                                    'column'   => 'institucion',
                                    'width'    => '350px',
                                    'rows'     => array('style' => 'text-align:center')),
                            array( 'name'     => 'Tutor Académico',
                                    'column'   => 'tutoracademico',
                                    'width'    => '500px',
                                    'rows'     => array('style' => 'text-align:center')),
                            array( 'name'     => 'Tutor Institucional',
                                    'column'   => 'tutorinstitucional',
                                    'width'    => '500px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Horario',
                                    'column'   => 'horario',
                                    'width'    => '350px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Cupos',
                                    'column'   => 'cupos',
                                    'width'    => '150px',
                                    'rows'     => array('style' => 'text-align:center')),

                );
               $other = Array(
                         Array( 'actionName' => 'inscribir',
                                'action' => 'inscribir(##pk##)',
                                'label' => 'Inscribir',
                                 ));
                 
                                                       
               $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns,'VO', $other);

           }
               
               $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
               $this->getResponse()->setBody(Zend_Json::encode($json));
            }
            
          
      }
      
      public function inscribirAction(){
           if ($this->_request->isXmlHttpRequest()) {
               
            $this->SwapBytes_Ajax->setHeader();   

            //funciona como un addoreditload, simplemente abre el formulario
            
            //traigo las variables
            $dataRow = array();
            $json = array();
            $dataRow = $this->preinproyecto->getRow($this->_params['modal']['id']);
            $dataRow['id'] = $this->_params['modal']['id'];
            $dataRow['pk_asignacionproyecto'] = $this->_params['modal']['id'];
            
            $pk = $dataRow['pk_asignacionproyecto'];
            $proyecto = $this->preinproyecto->getNombreProyecto($pk);
            
            $ci= $this->authSpace->userId;
            $estudiante = $this->preinproyecto->getInfoEstudiante($ci);
                               
            $tutor= $this->preinproyecto->getTutor($pk);
            
            $tutor_ins = $tutor[0]['tutor_ins'];
            $id_ins = $tutor[0]['fk_institucion'];
                        
            $periodo_siguiente = $this->periodo->getMasNuevo();
            $escuela=$estudiante[0]['fkescuela'];
           
         
            $cupos = $this->preinproyecto->getCuposFull($escuela,$periodo_siguiente,$pk);
            //var_dump($cupos);die;
              
            if ($cupos[0]['lleno'] == true) {
               
                $message = "<b>No se podra Inscribir en este proyecto puesto que no tiene Cupo</b>";
                
                $this->SwapBytes_Crud_Form->getDialog('No se puede lograr la Inscripción', $message,swOkOnly);
               $json1[]='$("#frmDialog").parent().find("button:contains(\"Ok\")").hide()';
                   

            }

            elseif ($cupos[0]['lleno'] == false) {
                
               
            $title = 'Feria de Proyectos';
            $this->SwapBytes_Form->fillSelectBox('pk_asignacionproyecto', $proyecto  , 'pk_proyecto', 'proyecto');
            $this->SwapBytes_Crud_Form->setProperties($this->view->formpreinpro, $dataRow, $title);
            $this->SwapBytes_Form->enableElement('pk_asignacionproyecto',false);
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->setWidthLeft('400px');
            $this->SwapBytes_Crud_Form->getAddOrEditLoad();
          
            }
           }


           
      }



      public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();  
            //envia los datos al response           
            $this->SwapBytes_Crud_Form->setProperties($this->view->formpreinpro, $this->_params);
            $this->SwapBytes_Form->readOnlyElement('pk_asignacionproyecto','true');
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->setWidthLeft('400px');
            $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
        }

      }

      public function addoreditresponseAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
                
            //realiza todos los inserts en cascada
            
            $dataRow = array();
            $json = array();
            $dataRow = $this->preinproyecto->getRow($this->_params['modal']['id']);
            $dataRow['id'] = $this->_params['modal']['id'];
            $dataRow['pk_asignacionproyecto'] = $this->_params['modal']['id'];
            
            $pk = $dataRow['pk_asignacionproyecto'];

            $ci= $this->authSpace->userId;
            $estudiante = $this->preinproyecto->getInfoEstudiante($ci);
            $escuela = $estudiante[0]['fkescuela'];
            $pensum = $estudiante[0]['fk_pensum'];
            //var_dump($escuela);die;
                               
            $tutor= $this->preinproyecto->getTutor($pk);
            
            $tutor_ins = $tutor[0]['tutor_ins'];
            $id_ins = $tutor[0]['fk_institucion'];
            
            $pk_servicio = $this->preinproyecto->getPkServicioII($escuela,$pensum);
            $asignatura = $pk_servicio[0]['pk_asignatura'];
            $inscripcion = $estudiante[0]['inscripcion'];
            //var_dump($inscripcion);die;
            $getRecord = $this->preinproyecto->getRecord($asignatura,$inscripcion); //para los que retiraron la materia
            
            if(!empty($getRecord)){
              $this->preinproyecto->insertData($getRecord[0]['pk_recordacademico'], $pk, $id_ins, $tutor_ins);
            }
            else{
            $this->preinproyecto->insertRecord($asignatura,$inscripcion);
            $pk_recordacademico=$this->preinproyecto->getRecord($asignatura,$inscripcion);
            $record=$pk_recordacademico[0]['pk_recordacademico'];

            $this->preinproyecto->insertData($record, $pk, $id_ins, $tutor_ins);
          }
            $json[]='window.location.reload()';
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->getResponse()->setBody(Zend_Json::encode($json));
            $this->SwapBytes_Crud_Form->getAddOrEditEnd();
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
        
      public function viewAction(){
              
        $pk=$this->_params['modal']['id'];
        $filasForm = $this->preinproyecto->getViewForm($pk);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $filasForm[0], 'Ver Proyecto');
        $this->SwapBytes_Crud_Form->getView();
 
               
           }
          
          public function imprimirAction(){
                        
            //planilla de preinscripción    
            $ci = $this->authSpace->userId;
            $queryArray = array($ci);
                $config = Zend_Registry::get('config');
                $dbname = $config->database->params->dbname;
                $dbuser = $config->database->params->username;
                $dbpass = $config->database->params->password;
                $dbhost = $config->database->params->host;
                $report = APPLICATION_PATH . '/modules/transactions/templates/servicio/planilla.jasper';
                $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
                //var_dump($imagen);die; 
                //$imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
                //poner asi cuando lo subas a producción 
                $filename    = 'servicioreporte';
                $filetype    = 'pdf';


                $params = "'ci=integer:{$queryArray[0]}|Imagen=string:{$imagen}'";



                $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D pgsql -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
               

                Zend_Layout::getMvcInstance()->disableLayout();
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );

                $outstream = exec($cmd); 
                //echo $cmd;die;

                echo base64_decode($outstream);
            
           
        
    } 
    public function cartaexAction(){
                        
            //carta de exoneración    
            $ci = $this->authSpace->userId;

                $queryArray = array($ci);
                $config = Zend_Registry::get('config');
                $dbname = $config->database->params->dbname;
                $dbuser = $config->database->params->username;
                $dbpass = $config->database->params->password;
                $dbhost = $config->database->params->host;
                $report = APPLICATION_PATH . '/modules/transactions/templates/servicio/cartaex.jasper';
                $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
                $filename    = 'cartaexoneracion';
                $filetype    = 'pdf';


                $params = "'ci=integer:{$queryArray[0]}|Imagen=string:{$imagen}'";
                
                $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D pgsql -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                
                Zend_Layout::getMvcInstance()->disableLayout();
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );

                $outstream = exec($cmd); 

                echo base64_decode($outstream);
            
           
        
    }

     public function cartacomAction(){
                        
            //carta de compromiso   
            $ci = $this->authSpace->userId;

                $queryArray = array($ci);
                $config = Zend_Registry::get('config');
                $dbname = $config->database->params->dbname;
                $dbuser = $config->database->params->username;
                $dbpass = $config->database->params->password;
                $dbhost = $config->database->params->host;
                $report = APPLICATION_PATH . '/modules/transactions/templates/servicio/cartacompromiso.jasper';
                $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
                $filename    = 'cartacompromiso';
                $filetype    = 'pdf';


                $params = "'ci=integer:{$queryArray[0]}|Imagen=string:{$imagen}'";
                
                $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D pgsql -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                
                Zend_Layout::getMvcInstance()->disableLayout();
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );

                $outstream = exec($cmd); 

                echo base64_decode($outstream);
            
           
        


    }  


   
}
?>