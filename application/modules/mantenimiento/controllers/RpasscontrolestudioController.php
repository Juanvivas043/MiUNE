<?php
class Mantenimiento_RpasscontrolestudioController extends Zend_Controller_Action{
    private $Title = "Reiniciar clave";

    public function init() {
       $this->SwapBytes_Ajax = new SwapBytes_Ajax();
       $this->SwapBytes_Ajax->setView($this->view);
       $this->namespace = new Zend_Session_Namespace("nop");
       $this->cedulas = new Zend_Session_Namespace("cedula");
       Zend_Loader::loadClass('Models_DbTable_Grupos');
       Zend_Loader::loadClass('Models_DbTable_Reiniciarpass');
       Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');

       $this->Misgrupos = new Models_DbTable_Grupos();
       $this->reiniciar = new Models_DbTable_Reiniciarpass();
       $this->grupo     = new Models_DbTable_UsuariosGrupos(); 
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
          $DDTI = false;
          if($this->validar($ci)){  
                $entro = false;
                $this->namespace->array = array();
                $this->cedulas->array = array();
                array_push($this->namespace->array,"false");
                array_push($this->cedulas->array,$ci);
                
                $mostra = false;
                $usuarioes = $this->Misgrupos->getUsuarioestudiante($ci);
                $usuariodoce = $this->Misgrupos->getUsuariodocente($ci);
                $cuadro =  $this->Misgrupos->getCuadro($ci);
                $cantidad = $this->reiniciar->getCantidadgrupos($ci);
                
                if($usuarioes[0]['nombre']!= "" && $cantidad[0]['count']<2 ){
                
                    $json[] = '$("#n_usuariotxt").html("'.$usuarioes[0]['nombre'].'")';
                    $json[] = '$("#a_usuariotxt").html("'.$usuarioes[0]['apellido'].'")';
                    $json[] = '$("#e_usuariotxt").html("'.$usuarioes[0]['valor'].'")';
                    $json[] = '$("#mensaje").html("")';
                    $json[] = '$("#claveclik").attr("disabled",false)';
                    $entro = true;
                    $mostrar = true;
                }
                if(!$entro && $usuariodoce[0]['nombre']!=""){
                    $escuela = false;
                    
                    $json[] = '$("#n_usuariotxt").html("'.$usuariodoce[0]['nombre'].'")';
                    $json[] = '$("#a_usuariotxt").html("'.$usuariodoce[0]['apellido'].'")';
                    
                     foreach($cuadro as $misgrupo)
                     {
                          
                         if($misgrupo['grupo']=="Estudiante" && !$escuela){
                           $json[] = '$("#e_usuariotxt").html("'.$usuarioes[0]['valor'].'")';
                           $json[] = '$("#claveclik").attr("disabled",false)';
                           $escuela = true;
                         }
                         
                          if($misgrupo['grupo']=="Docente" && !$escuela){
                           $json[] = '$("#e_usuariotxt").html("Docente")'; 
                           $json[] = '$("#claveclik").attr("disabled",false)';
                           $escuela = true;
                         }
                         if($misgrupo['grupo']=="DDTI"){
                             $DDTI =true;
                           $this->namespace->array[0] = "true";
                            $json[] = '$("#mensaje").html("Contactar con el Administrador")';
                            $json[] = '$("#claveclik").attr("disabled",true)';
                         }
                         
                        
                     }
                    if(!$escuela){
                       $json[] = '$("#e_usuariotxt").html("Administrativo")';  
                    }
                    

                    $mostrar = true;
                }
                
                if($mostrar){
                    $json[] = '$("#informacion").show()';
                    $json[] = '$("#mensaje").hide()';
                    
                    if($DDTI){
                      $json[] = '$("#mensaje").show()';
                    }
                    
                    
                    $this->getResponse()->setBody(Zend_Json::encode($json));
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
      
     public function reiniciarpassAction(){ 
            $json = array();
            $this->SwapBytes_Ajax->setHeader();
            $ci= $this->_getParam('ci');
            
            
            if($this->cedulas->array[0]==$ci && $ci != "")
            {
            if($this->namespace->array[0]=="false"){
            $this->Misgrupos->getCambiopass($ci);
            $json[] = '$("#mensaje").html("clave reiniciada")';
            $json[] = '$("#mensaje").show()';
           
            
            }else{
                $json[] = '$("#mensaje").html(":) Contactar con el Administrador")';
                $json[] = '$("#mensaje").show()';
            }
            $this->getResponse()->setBody(Zend_Json::encode($json));
            }
            
            }

     public function indexAction() {
	$this->view->title = $this->Title;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
      }
}
      
?>
