<?php
class Consultas_PlanillaservicioController extends Zend_Controller_Action{
    private $Title = "Consultas/Planilla Servicio Comunitario";

    public function init() {
       $this->SwapBytes_Ajax = new SwapBytes_Ajax();
       $this->SwapBytes_Ajax->setView($this->view);
       Zend_Loader::loadClass('Models_DbTable_Grupos');
       $this->grupos = new Models_DbTable_Grupos();
       
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
                Zend_Loader::loadClass('Models_DbTable_Usuarios');
        $this->estudiante              = new Models_DbTable_Usuarios();

        Zend_Loader::loadClass('Models_DbTable_Inscripcionespasantias');
        $this->inscripcionpasantia      =new Models_DbTable_Inscripcionespasantias();
        $this->grupo = new Models_DbTable_UsuariosGrupos();//
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
		    $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
		    $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
       
      }
    
      public function preDispatch() {
           if (!Zend_Auth::getInstance()->hasIdentity()) {
              $this->_helper->redirector('index', 'login', 'default');
            }

            if (!$this->grupo->haveAccessToModule()) {
              $this->_helper->redirector('accesserror', 'profile', 'default');
            }
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

      
      public function buscarAction(){
        if($this->_request->isXmlHttpRequest()){
          $this->SwapBytes_Ajax->setHeader();
          $ciEstudiante=$this->_getParam('ci');
          $json=array();

          $estudiante=$this->inscripcionpasantia->getPlanilla($ciEstudiante);
          if (empty($estudiante)){
            $mensaje = "No hay alumno registrado";
            $HTML  = $this->SwapBytes_Html_Message->alert($mensaje);
            $json[] = $this->SwapBytes_Jquery->setHtml('mensaje2', $HTML);
            $json[]='$("#mensaje2").show()';
            $json[]= '$("#consulta").hide()';
                      $json[]= '$("#fotoDiv").hide()';


          }else{
          $json[] ='$("#id").html("'.$estudiante[0]['cedula'].'")';
          $json[] ='$("#estudiante").html("'.$estudiante[0]['estudiante'].'")';
          $json[] ='$("#direccion").html("'.$estudiante[0]['direccionusuario'].'")';
          $json[] ='$("#telefono").html("'.$estudiante[0]['telefonousuario'].'")';
          $json[] ='$("#celular").html("'.$estudiante[0]['celular'].'")';
          $json[] ='$("#correo").html("'.$estudiante[0]['mail'].'")';
          $json[] ='$("#escuela").html("'.$estudiante[0]['escuela'].'")';
          $json[] ='$("#proyecto").html("'.$estudiante[0]['proyecto'].'")';
          $json[] ='$("#institucion").html("'.$estudiante[0]['institucion'].'")';
          $json[] ='$("#dirinst").html("'.$estudiante[0]['direccionins'].'")';
          $json[] ='$("#telinst").html("'.$estudiante[0]['tin'].'")';
          $json[] ='$("#periodo").html("'.$estudiante[0]['periodo'].'")';
          $json[]= '$("#consulta").show()';
          $json[]='$("#mensaje2").hide()';
          $json[]= '$("#fotoDiv").show()';
          }
         

          }
         $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ciEstudiante}'");

         $this->SwapBytes_Crud_Form->setJson($json);
          $this->getResponse()->setBody(Zend_Json::encode($json));
      }
      
      
      public function indexAction() {
          
            $this->view->title = $this->Title;
            $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();

            $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
            $this->view->SwapBytes_Ajax->setView($this->view);
            $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
            $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;

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
    public function imprimirAction(){
                        
            //planilla de preinscripción    
            $ci = $this->_getParam('ci');
            $queryArray = array($ci);
            //var_dump($queryArray);die;
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
}


?>
