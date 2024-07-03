<?php
class Transactions_CredencialesscController extends Zend_Controller_Action{
    private $Title = "Credenciales Servicio Comunitario";

    public function init() {
       
       Zend_Loader::loadClass('Models_DbTable_Usuarios');
       Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
       Zend_Loader::loadClass('Models_DbTable_Solventes');
       Zend_Loader::loadClass('Models_DbTable_Grupos');
       Zend_Loader::loadClass('Models_DbTable_Reiniciarpass');
       Zend_Loader::loadClass('Models_DbTable_Inscripcionespasantias');
//       Zend_Loader::loadClass('Une_Cde_Reportes_Credenciales');  // REPORTE
       
       $this->Misgrupos                = new Models_DbTable_Grupos(); 
       $this->reiniciar                = new Models_DbTable_Reiniciarpass();
       $this->grupo                    = new Models_DbTable_UsuariosGrupos();
       $this->solvente                 = new Models_DbTable_Solventes();
       $this->pasantias                = new Models_DbTable_Inscripcionespasantias();
       $this->estudiante               = new Models_DbTable_Usuarios();
//       $this->credenciales             = new Une_Cde_Reportes_Credenciales();  // REPORTE

       $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
       $this->SwapBytes_Ajax->setView($this->view);
       $this->namespace                = new Zend_Session_Namespace("nop");
       $this->cedulas                  = new Zend_Session_Namespace("cedula");
       
       $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
       $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
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
          if($ci == ""){
              return false;
          }
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
          $this->SwapBytes_Ajax->setHeader();
          $ci= $this->_getParam('ci');
          $json = array();

          if($this->validar($ci)){  
                $entro = false;
                $this->namespace->array = array();
                $this->cedulas->array = array();
                array_push($this->namespace->array,"false");
                array_push($this->cedulas->array,$ci);
                

                $usuarioes = $this->Misgrupos->getUsuarioestudiante($ci);
                $credencial =  $this->pasantias->getCredenciales($ci);
                $inscrito   =  $this->pasantias->getboolinscritopasantia($ci);
                

                if($usuarioes[0]['nombre']!= ""){ 
                
                    $json[] = '$("#UneDiv").html("<img src= '.$this->view->baseUrl() .'/images/logo_UNE_color.png>")';
                    $json[] = '$("#n_usuariotxt").html("'.$usuarioes[0]['nombre'].'")';
                    $json[] = '$("#a_usuariotxt").html("'.$usuarioes[0]['apellido'].'")';
                    $json[] = '$("#e_usuariotxt").html("'.$usuarioes[0]['valor'].'")';
                    $json[] = '$("#mensaje").html("")';
                    $json[] = '$("#claveclik").attr("disabled",false)';
                    $entro = true;
                    $mostrar = true;
                }
                
                if($mostrar){
                    $json[] = '$("#informacion").show()';
                    $json[] = '$("#mensaje").hide()';
                    $json[] = '$("#i_usuariotxt").html("'.$credencial['institucion'].'")';
                    $json[] = '$("#p_usuariotxt").html("'.$credencial['proyecto'].'")';
                                             
                    $perfil .= "<table>";

                    $json[] = '$("#periodo_usuariotxt").html("<b><p style=color:green;>'.$credencial['periodo'].'</p></b>")';
                  
                    $perfil .= "</table>";
                    
                    $json[] = '$("#per_usuariotxt").html("'.$perfil.'")';

                    $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");
                    
              
                    
                    //$this->getResponse()->setBody(Zend_Json::encode($json));
                    $this->SwapBytes_Crud_Form->setJson($json);
                    $this->SwapBytes_Crud_Form->setWidthLeft('120px');
                    $this->SwapBytes_Crud_Form->getAddOrEditLoad();
                    
                     if ($inscrito == FALSE){
                    $json[] = '$("#informacion").hide()';
                    $json[] = '$("#mensaje").html("Estudiante No Inscrito en Servicio Comunitario o sin Proyecto inscrito.")';
                    $json[] = '$("#mensaje").show()';
                    $json[] = '$("#claveclik").attr("disabled",true)';
                    $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerar','disabled','true');
                    $this->getResponse()->setBody(Zend_Json::encode($json));
                } else { 
                          $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerar','disabled','false');
                           $this->getResponse()->setBody(Zend_Json::encode($json));
                }
                    
                }else{
                    $json[] = '$("#informacion").hide()';
                    $json[] = '$("#mensaje").html("Cedula no encontrada")';
                    $json[] = '$("#mensaje").show()';
                    $json[] = '$("#claveclik").attr("disabled",true)';
                   $this->getResponse()->setBody(Zend_Json::encode($json));
                } 
            
            }else{
               $json[] = '$("#informacion").hide()';
               $json[] = '$("#mensaje").html("Cedula no encontrada")';
               $json[] = '$("#mensaje").show()';
               $json[] = '$("#claveclik").attr("disabled",true)';
               $this->getResponse()->setBody(Zend_Json::encode($json)); 
            }  
             
                
      }
      
     /**
      * Obtiene la foto de un usuario desde la Base de Datos.
      */
     public function photoAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$id    = $this->_getParam('id', 0);
		$image = $this->estudiante->getPhoto($id);

		$this->getResponse()
		     ->setHeader('Content-type', 'image/jpeg')
		     ->setBody($image);
                
     }
     
     

     public function indexAction() {
	$this->view->title = $this->Title;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
      }
      
//       public function descargarAction() {
//        $ci= $this->_getParam('ci');
//        
//            $Credenciales = $this->pasantias->getCredenciales($ci); // poner consulta que traiga data
//        
//          //  $this->credenciales->generar($ci, $Credenciales);   //generar del reporte
//        
//            }
      
      
}
      
?>
