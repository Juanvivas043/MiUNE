<?php
class Mantenimiento_GruposController extends Zend_Controller_Action{
    private $Title = "Grupos";

    public function init() {
       $this->SwapBytes_Ajax = new SwapBytes_Ajax();
       $this->SwapBytes_Ajax->setView($this->view);
       Zend_Loader::loadClass('Models_DbTable_Grupos');
       Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
       $this->grupos = new Models_DbTable_Grupos();
       $this->grupo  = new Models_DbTable_UsuariosGrupos();
      }
    
      public function preDispatch() {
           if (!Zend_Auth::getInstance()->hasIdentity()) {
              $this->_helper->redirector('index', 'login', 'default');
            }

            if (!$this->grupo->haveAccessToModule()) {
              $this->_helper->redirector('index', 'login', 'default');
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

      public function busquedaAction(){
          $this->SwapBytes_Ajax->setHeader(); 
          $ci= $this->_getParam('ci');
          $json = array();
           if($this->validar($ci)){
          $entro = false;
          $usuarioes = $this->grupos->getUsuarioestudiante($ci);
          $usuariodoce = $this->grupos->getUsuariodocente($ci);
          $grupo_s = $this->grupos->getCuadro($ci);
          $grupo_n = $this->grupos->getCuadron($ci);
          
          if($usuarioes[0]['nombre'] != ""){
          $json[] = '$("#n_usuariotxt").html("'.$usuarioes[0]['nombre'].'")';
          $json[] = '$("#a_usuariotxt").html("'.$usuarioes[0]['apellido'].'")';
          $json[] = '$("#e_usuariotxt").html("'.$usuarioes[0]['valor'].'")';
          $entro = true; 
          }
           if($usuariodoce[0]['nombre'] != "" && $entro==false){
          $json[] = '$("#n_usuariotxt").html("'.$usuariodoce[0]['nombre'].'")';
          $json[] = '$("#a_usuariotxt").html("'.$usuariodoce[0]['apellido'].'")';
          $json[] = '$("#e_usuariotxt").html("Docente")';
          $entro = true; 
          }
          
          if($entro == true){
          $json2 .= "<select multiple id ='listbox1'style='width:280px' size='10'>";
          foreach ($grupo_s as $gg){
          If($gg[grupo]=='Estudiante'|| $gg[grupo]=='Docente'){    
          $json2 .= "<option value={$gg[pk_atributo]} style='font-weight:bold' select>{$gg[grupo]}</option>"; // en el value deberias poner el pk del grupo
          }else{
           $json2 .= "<option value={$gg[pk_atributo]} select>{$gg[grupo]}</option>";   
          }
          }
          $json2 .= "</select>";
          $json[] = '$("#migrupos").html("'.$json2.'")';
          
          
          $json3 .= "<select multiple id ='listbox2'style='width:280px' size='10'>";
          foreach ($grupo_n as $gn){
          If($gn[grupo]=='Estudiante'|| $gn[grupo]=='Docente'){ 
          $json3 .= "<option value={$gn[pk_atributo]} style='font-weight:bold' select>{$gn[grupo]}</option>"; 
          }else{
          $json3 .= "<option value={$gn[pk_atributo]} select>{$gn[grupo]}</option>";
          }
          
          }
          $json3 .= "</select>";
          $json[] = '$("#n_grupos").html("'.$json3.'")';
          $json[] = ' $("#marcos").show()';
          $json[] = ' $("#mensaje").hide()';
          $json[] = '$("#claveclik").attr("disabled",false)';
          $this->getResponse()->setBody(Zend_Json::encode($json));
          }else{
              $json[] = ' $("#marcos").hide()';
              $json[] = ' $("#mensaje").show()';
              $json[] = '$("#claveclik").attr("disabled",true)';
              $this->getResponse()->setBody(Zend_Json::encode($json));
          }  
           
       }else{
           $json[] = '$("#mensaje").show()';
           $json[] = '$("#marcos").hide()';
           $json[] = '$("#claveclik").attr("disabled",true)';
           $this->getResponse()->setBody(Zend_Json::encode($json));
       }
            
      }
      
      public function cambiotsAction(){
          
           $this->SwapBytes_Ajax->setHeader(); 
           $ci = $this->_getParam('ci');
            if($this->validar($ci)){
           
           $value_s = $this->grupos->getCuadron($ci);;   
           
     
               foreach ($value_s as $vs){
                  $this->grupos->getCambio_s($ci,$vs['pk_atributo']); 
               }
            }
           $this->busquedaAction();   
           
      }
      
      public function cambiosAction(){
          
          $this->SwapBytes_Ajax->setHeader(); 
          $ci = $this->_getParam('ci');
          $value_s = $this->_getParam('value_s');
          
          $miarreglo = array();
          $miarreglo = explode(",", $value_s);
          
          if( $value_s != '' && $this->validar($ci))
            {
              foreach ($miarreglo as $vs)
              {
                   $this->grupos->getCambio_s($ci,$vs);   
              }
            }
           
          $this->busquedaAction();
          
      }

      public function cambionAction(){ 
          
          $this->SwapBytes_Ajax->setHeader(); 
          $ci = $this->_getParam('ci');
          $value_n = $this->_getParam('value_n'); 
          $miarreglo = array();
          $miarreglo = explode(",", $value_n);
          
          if($value_n != "" && $this->validar($ci))
          {
              foreach ($miarreglo as $vn)
              {
                   $this->grupos->getCambio_n($ci,$vn);   
              }
          }
          $this->busquedaAction();
          
      } 

      public function cambiotnAction(){
          
           $this->SwapBytes_Ajax->setHeader(); 
           $ci = $this->_getParam('ci');
           if($this->validar($ci))
           {
           $value_n = $this->grupos->getCuadro($ci);  
          
               foreach ($value_n as $vn){
                  $this->grupos->getCambio_n($ci,$vn['pk_atributo']); 
                                       }
           }     
           $this->busquedaAction();
           
      } 
      public function reiniciarpassAction()    {
            $this->SwapBytes_Ajax->setHeader();
            $ci= $this->_getParam('ci');
            $this->grupos->getCambiopass($ci);
      }
      
      
      public function indexAction() {
	$this->view->title = $this->Title;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
      }
}

?>
