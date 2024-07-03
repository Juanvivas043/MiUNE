<?php

class Mantenimiento_AccesosipController extends Zend_Controller_Action{
    private $Title = "Acceso ip";
    
      public function init() {
       $this->SwapBytes_Ajax = new SwapBytes_Ajax();
       $this->SwapBytes_Ajax->setView($this->view);
       
       $this->valorid = new Zend_Session_Namespace("valor");
       $this->verid = new Zend_Session_Namespace("ver");
       
       Zend_Loader::loadClass('Models_DbTable_Accesosip');
       $this->accesos = new Models_DbTable_Accesosip(); 

      }
      
      public function preDispatch() {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
              $this->_helper->redirector('index', 'login', 'default');
            }

       //     if (!$this->grupo->haveAccessToModule()) {
       //       $this->_helper->redirector('index', 'login', 'default');
       //     }
      }
       
      public function accesoAction(){
          $this->SwapBytes_Ajax->setHeader(); 
          $json = array();
          $todoaccesos = $this->accesos->getAcceso1();
           foreach($todoaccesos as $todo){
            $json2 .= "<option value={$todo[pk_acceso]} select>{$todo[nombre]}</option>"; 
          }
          $json2 .="</select>";
          
        $json[] = '$("#accesosbox").html("'.$json2.'")';
        
        $this->getResponse()->setBody(Zend_Json::encode($json));

      }
      
      public function misipAction(){
          
         $this->SwapBytes_Ajax->setHeader(); 
         $ace= $this->_getParam('acc');
         $todoip = $this->accesos->getIP($ace);
         $json[]=array();
         $cont = 0;
         
         $this->verid->array = array();
         array_push($this->verid->array,$ace);
         
         $help = "";
         $this->valorid->array = array();
         
         if( $this->verid->array[0]!="")
         {
         foreach($todoip as $ip){
            $json2 .="<tr>"; 
            $json2 .="<td>"; 
            $json2 .= "<input id='{$ip[pk_accesoip]}' type='text' value={$ip[client_ip]} style ='width: 85' READONLY>";
            $json2 .="</td>";
            $json2 .="<td>"; 
            $json2 .= "<input id='valor$cont' type='checkbox' value='{$ip[pk_accesoip]}' >";
            $json2 .="</td>";
            $json2 .="</tr>"; 
            // guardo todo los id valor$cont
            $help = "$ip[pk_accesoip]".",".$help; 
            //
            if($ip[pk_accesoip]!=""){
             $json[] = '$("#eliminar").show()'; 
            }
            $cont = $cont +1;
          }
          
          array_push($this->valorid->array,$help);
          
          $json[] = '$("#Misip").html("'.$json2.'")';
          $json[] = '$("#mensaje").hide()';
          $json[] = '$("#agregarIP").show()';

        $this->getResponse()->setBody(Zend_Json::encode($json));
         }
      }

      public function agregaripAction(){
         $this->SwapBytes_Ajax->setHeader(); 
         $ace= $this->_getParam('acc');
         $ip= $this->_getParam('ip');
         $json[]=array();
         
         
         if( $this->verid->array[0]!= "" && $ace == $this->verid->array[0] )
         {
         
         if($this->validarip($ip)){
             
             if($this->validariprep($ace, $ip)){
                 
                    $this->accesos->getAgregar($ace,$ip);
                    $this->misipAction();
                    $json[] = '$("#mensaje").hide()';
                    $this->getResponse()->setBody(Zend_Json::encode($json));
                    $this->misipAction();
                    
             }else{
             $json[] = '$("#mensaje").html("IP Repetido")';
             $json[] = '$("#mensaje").show()';
             $this->getResponse()->setBody(Zend_Json::encode($json));
         }
             
         }else{
             $json[] = '$("#mensaje").html("Formato de IP Incorrecto")';
             $json[] = '$("#mensaje").show()';
             $this->getResponse()->setBody(Zend_Json::encode($json));
         }
         }
      }
      
      public function eliminaripAction(){
          
        $this->SwapBytes_Ajax->setHeader(); 
        $ace= $this->_getParam('acc');
        $check = $this->_getParam('t');
        $arregloval = explode(",",$this->valorid->array[0]);
        $miarreglo = explode(",", $check);
        $s = 0;
        if( $this->verid->array[0]!= "" && $ace == $this->verid->array[0] )
         {
        foreach($miarreglo as $eli){
            if($eli!=""){
                foreach($arregloval as $vali){
                    if($vali == $eli){
               $this->accesos->getEliminar($ace,$eli);
                    }
                }
            } 
        }
        $this->misipAction();
         }
      }

      private function validarip($ip){
          
          $validacion ="^([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))\.";
          $validacion .="([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))\."; 
          $validacion .="([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))\.";
          $validacion .="([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-4]))$";
          
          if (ereg($validacion,$ip)) {
              return true;
              //echo "Formato de IP correcto";
             } else {
                 return false;
              //echo "Formato Incorrecto";
                    }
      }
      
      private function validariprep($ace,$ip){
         $info = $this->accesos->getValidar($ace,$ip);
         
         if($info[0]['pk_accesoip'] == ""){
             return true;
         }else{
             return false;
         }
                                          
      }
          
      public function indexAction() {
	$this->view->title = $this->Title;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
      }
      
}
?>
