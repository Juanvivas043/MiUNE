<?php
class Mantenimiento_GruposaccesoController extends Zend_Controller_Action{
    private $Title = "Acceso de los grupos";
    
    public function init() {
       $this->SwapBytes_Ajax = new SwapBytes_Ajax();
       $this->SwapBytes_Ajax->setView($this->view);
       Zend_Loader::loadClass('Models_DbTable_Gruposacceso'); 
       Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
       $this->grupo           = new Models_DbTable_UsuariosGrupos();
       $this->acceso = new Models_DbTable_Gruposacceso(); 

      }
      
      public function preDispatch() {
           if (!Zend_Auth::getInstance()->hasIdentity()) {
              $this->_helper->redirector('index', 'login', 'default');
            }

          if (!$this->grupo->haveAccessToModule()) {
             $this->_helper->redirector('index', 'login', 'default');
           }
      }
      
      public function grupoboxAction(){
        $this->SwapBytes_Ajax->setHeader(); 
        $json = array();
        $todos = $this->acceso->getLlenado();

        foreach($todos as $gr){
            $json2 .= "<option value={$gr[pk_atributo]}  select>{$gr[grupo]}</option>"; 
        }
        $json2 .= "</select>";
        $json[] = '$("#grupobox").html("'.$json2.'")';
        $this->getResponse()->setBody(Zend_Json::encode($json));
      }

      public function misaccesosAction(){
           $this->SwapBytes_Ajax->setHeader();
           $grupo= $this->_getParam('grupo');
           $json[]=array();
           $acceso_s = $this->acceso->getAcceso($grupo);
           $acceso_n = $this->acceso->getTodo($grupo);

           foreach ($acceso_s as $ac){
           $json2 .= "<option value={$ac[pk_acceso]}  select>{$ac[nombre]}</option>"; 
           }
           $json[] = '$("#lista1").html("'.$json2.'")';
           
          
           foreach ($acceso_n as $an){   
        //   $json3 .= "<option value={$an[pk_acceso]} select>{$an[nombre]}</option>";
             $json3 .= "<option value={$an[pk_acceso]}select>{$an[nombre]} </option>";
           }  

           $json[]= '$("#lista2").html("'.$json3.'")';
           
           $this->getResponse()->setBody(Zend_Json::encode($json));

      }

      public function accesosAction(){
           $this->SwapBytes_Ajax->setHeader();
           $grupo= $this->_getParam('grupo');           
           $json = array();
           $acceso_n = $this->acceso->getTodo($grupo);
             foreach ($acceso_n as $an){   
             $json3 .= "<option value={$an[pk_acceso]} select>{$an[nombre]}</option>";
             }  

           $json[]= '$("#lista2").html("'.$json3.'")';

           $this->getResponse()->setBody(Zend_Json::encode($json));
          
      }
      
      public function agregarAction(){
          
          $this->SwapBytes_Ajax->setHeader(); 
          
          $grupo = $this->_getParam('grupo');
          
          $value = $this->_getParam('value'); 
          
          $value = str_replace('select', '', $value);
          $miarreglo = array();

          $miarreglo = explode(",", $value);

          foreach ($miarreglo as $ag)
              {
                
                   $this->acceso->getAgregar($grupo,$ag);   
              }
              $this->misaccesosAction();
      }

      public function eliminarAction(){
          
          $this->SwapBytes_Ajax->setHeader();
          
          $grupo = $this->_getParam('grupo');
          
          $value = $this->_getParam('value'); 
          
          $miarreglo = array();
          $miarreglo = explode(",", $value);

          foreach ($miarreglo as $eli)
              {
                   $this->acceso->getEliminar($grupo,$eli);   
              }
              $this->misaccesosAction();

          
      }
      

      public function indexAction() {
	$this->view->title = $this->Title;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
      }
}
?>
