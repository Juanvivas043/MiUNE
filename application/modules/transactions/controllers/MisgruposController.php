<?php
class Transactions_MisgruposController extends Zend_Controller_Action{
    private $Title = "Usuario";


    public function init() {
       $this->SwapBytes_Ajax = new SwapBytes_Ajax();
       $this->SwapBytes_Ajax->setView($this->view);
       Zend_Loader::loadClass('Models_DbTable_Grupos');
       $this->Misgrupos = new Models_DbTable_Grupos(); 
       
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
      } //preguntar como hacer el llamado desde la otra clase .
      
      public function mibusquedaAction(){
            $this->SwapBytes_Ajax->setHeader();
            $ci= $this->_getParam('ci');
            $json = array();
            
           if($this->validar($ci)){ 
          $entro = false;    
          $usuarioes = $this->Misgrupos->getUsuarioestudiante($ci);
          $usuariodoce = $this->Misgrupos->getUsuariodocente($ci);
          $cuadro =  $this->Misgrupos->getCuadro($ci);
          $pos = 0;
          if($usuarioes[0]['nombre'] != ""){
          $json[] = '$("#n_usuariotxt").html("'.$usuarioes[0]['nombre'].'")';
          $json[] = '$("#a_usuariotxt").html("'.$usuarioes[0]['apellido'].'")';
          $json[] = '$("#e_usuariotxt").html("'.$usuarioes[0]['valor'].'")';
          $json2 .="<table id='tblcuadrado' align='center'>";
          $entro = true;
          }
          if($usuariodoce[0]['nombre'] != "" && $entro==false){
          $json[] = '$("#n_usuariotxt").html("'.$usuariodoce[0]['nombre'].'")';
          $json[] = '$("#a_usuariotxt").html("'.$usuariodoce[0]['apellido'].'")';
          $json[] = '$("#e_usuariotxt").html("Docente")';
          $json2 .="<table id='tblcuadrado' align='center'>";
          $entro = true; 
          }
          
          
          if($entro){
          foreach($cuadro as $cs){
             $pos = $pos +1;
             $json2 .="<tr>";
             $json2 .="<td>";
             $json2 .="<p>$pos</p>";
             $json2 .="</td>";
             $json2 .="<td>";
             $json2 .="<p style='font-weight:bold'>$cs[grupo]</p>";
             $json2 .="</td>";
             $json2 .="</tr>";   
          }
          $json2 .="</table>";
          $json[] = '$("#grupos").html("'.$json2.'")';
          $json[] = ' $("#informacion").show()';
          $json[] = ' $("#mensaje").hide()';
          $json[] = '$("#claveclik").attr("disabled",false)';
         
          }else{
              $json[] = ' $("#informacion").hide()';
              $json[] = ' $("#mensaje").show()';
              $json[] = '$("#claveclik").attr("disabled",true)';
          }
            $this->getResponse()->setBody(Zend_Json::encode($json));
           
           
           }else{
               $json[] = ' $("#informacion").hide()';
               $json[] = '$("#mensaje").show()';
               $json[] = '$("#claveclik").attr("disabled",true)';
               $this->getResponse()->setBody(Zend_Json::encode($json));
           }
      }


      public function reiniciarpassAction()    {
            $this->SwapBytes_Ajax->setHeader();
            $ci= $this->_getParam('ci');
            $this->Misgrupos->getCambiopass($ci);
      }


      public function indexAction() {
	$this->view->title = $this->Title;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
      }
}

?>

