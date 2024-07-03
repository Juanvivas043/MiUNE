<?php

class Mantenimiento_MododiosController extends Zend_Controller_Action{
    private $Title = "Modo dios";
    
      public function init() {
       $this->SwapBytes_Ajax = new SwapBytes_Ajax();
       $this->SwapBytes_Ajax->setView($this->view);
       $this->SwapBytes_Jquery = new SwapBytes_Jquery();
       $this->SwapBytes_Crud_List = new SwapBytes_Crud_List();
       $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
       $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();

        Zend_Loader::loadClass('Models_DbTable_Accesos');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Une_Sweetalert');
        Zend_Loader::loadClass('Une_Filtros');
        $this->Une_Filtros     = new Une_Filtros();
        $this->accesos    = new Models_DbTable_Accesos();
        $this->usuarios   = new Models_DbTable_Usuarios();
        $this->periodos   = new Models_DbTable_Periodos();
        $this->grupo      = new Models_DbTable_UsuariosGrupos();
        $this->sweetalert = new Une_Sweetalert();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        //var_dump($_SERVER);die;
      }
      
      public function preDispatch() {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
              $this->_helper->redirector('index', 'login', 'default');
            }

            if (!$this->grupo->haveAccessToModule()) {
              $this->_helper->redirector('accesserror', 'profile', 'default');
            }
      }
       
      public function indexAction() {
        $this->view->title = $this->Title;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->ultimoperiodo = $this->periodos->getMasNuevo();
        $this->view->ultimoperiodo = $this->ultimoperiodo;
      }

      public function switcherAction() {
          if ($this->_request->isXmlHttpRequest()) {
              $this->SwapBytes_Ajax->setHeader();
              
              $id = $this->_getParam('id');
              $acceso = $this->_getParam('acceso');

              $estadoActual = $this->accesos->getVisibility($acceso);
              
              if($estadoActual){
                //Setea el nuevo estado del acceso
                $resultado = $this->accesos->setVisibility($acceso, 'false');
              }else{
                //Setea el nuevo estado del acceso
                $resultado = $this->accesos->setVisibility($acceso, 'true');
              }
          }
      }

      public function setclassswitcherAction(){

        if ($this->_request->isXmlHttpRequest()) {
              $this->SwapBytes_Ajax->setHeader();
              
              $inputs = $this->_getParam('inputs');

              foreach ($inputs as $key => $value) {

                  $id_key = "id".$key;
                  $pk_key = "pkacceso".$key;

                  $input_id = $value[$id_key];
                  $input_pk = $value[$pk_key]; 

                  $input_estado = $this->accesos->getVisibility($input_pk);

                  if($input_estado){
                    $json[] .= "$('#".$input_id."').prop('checked', true)";
                    $json[] .= "$('#".$input_id."').parent('.carry').css({background:'#2F7D62'})";
                  }else{
                    $json[] .= "$('#".$input_id."').prop('checked', false)";
                    $json[] .= "$('#".$input_id."').parent('.carry').css({background:'grey'})";
                  }

              }
              
              $this->getResponse()->setBody(Zend_Json::encode($json));
          }
      }

      public function resetAction(){
       /* if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $direccion=$_SERVER['SERVER_ADDR'];
            if ($direccion=='127.0.0.1') {
              $this->usuarios->resetPassword();
              $json[]="alert('Contraseñas reestablecidas')";
            }else{
              $json[]="alert('Solo se pueden reestablecer las contraseñas desde el servidor de pruebas')";
            }
            
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
              //var_dump($_SERVER['REMOTE_ADDR']);
              //var_dump("hola");
              //$this->usuarios->resetPassword();*/
      }

      public function listarAction(){
        if($this->_request->isXmlHttpRequest()){
          $this->SwapBytes_Ajax->setHeader();
          $limite=$this->_getParam('limit');
          $json=array();
          if ($limite>0){
            $periodo=$this->periodos->getPeriodos($limite);
            $pptytable=array('class'=>'periodotabla',
                             'width'=>'650px',
                             'column'=>'disponible');
            $pptycolumn=array(array(   'column'=>'pk_periodo',
                                       'primary'=>true,
                                       'hide'=>true),
                              array(   'name'=>'Periodo',
                                       'column'=>'pk_periodo',
                                       'width'=>'100px',
                                       'rows'=>array('style'=>'text-align:center')),
                              array(   'name'=>'Fecha de Inicio',
                                       'column'=>'fechainicio',
                                       'width'=>'100px',
                                       'rows'=>array('style'=>'text-align:center')),
                              array(   'name'=>'Fecha Final',
                                       'column'=>'fechafin',
                                       'width'=>'100px',
                                       'rows'=>array('style'=>'text-align:center')),
                              array(   'name'=>'Fecha de Corte',
                                       'column'=>'fechacorte',
                                       'width'=>'100px',
                                       'rows'=>array('style'=>'text-align:center')),
                              array(   'name'=>'Inicio de Clases',
                                       'column'=>'inicioclases',
                                       'width'=>'100px',
                                       'rows'=>array('style'=>'text-align:center')),
                              );
            $HTML   = $this->SwapBytes_Crud_List->fill($pptytable, $periodo, $pptycolumn);
            $json[] = $this->SwapBytes_Jquery->setHtml('tablaperiodos', $HTML);
          }else{
            $json[]="alert('Por favor elija el número de periodos que desea ver')";
          }
          $this->getResponse()->setBody(Zend_Json::encode($json));
        }
      }
      public function agregarAction(){
        if($this->_request->isXmlHttpRequest()){
          $this->SwapBytes_Ajax->setHeader();

          $fecini = $this->_getParam('fechainicio',0);
          $fecfin = $this->_getParam('fechafin',0);
          $feccorte = $this->_getParam('fechacorte',0);
          $fecclases = $this->_getParam('fechaclases',0);
          $this->periodo = $this->periodos->getMasNuevo();
          $nuevo_periodo = $this->periodo + 1;

          $fechainicio = date("Y-m-d", strtotime($fecini));
          $fechafin    = date("Y-m-d", strtotime($fecfin));
          $fechacorte  = date("Y-m-d", strtotime($feccorte));
          $fechaclases = date("Y-m-d", strtotime($fecclases));

          $result = $this->periodos->addPeriodo($nuevo_periodo,$fechainicio,$fechafin,$fechaclases,$fechacorte);
          $json[] = $this->sweetalert->setBasicAlert("success","Éxito","Se ha creado el período con éxito.");
          $this->getResponse()->setBody(Zend_Json::encode($json));
        }

      }
      public function modificarAction(){
        if($this->_request->isXmlHttpRequest()){
          $this->SwapBytes_Ajax->setHeader();

          $fecini = $this->_getParam('fechainicio',0);
          $fecfin = $this->_getParam('fechafin',0);
          $feccorte = $this->_getParam('fechacorte',0);
          $fecclases = $this->_getParam('fechaclases',0);
          $this->periodo = $this->periodos->getMasNuevo();
          /*$this->periodo = $this->periodos->getSelect();
          var_dump($this->periodo);die;*/

          $fechainicio = date("Y-m-d", strtotime($fecini));
          $fechafin    = date("Y-m-d", strtotime($fecfin));
          $fechacorte  = date("Y-m-d", strtotime($feccorte));
          $fechaclases = date("Y-m-d", strtotime($fecclases));
          $result = $this->periodos->modPeriodo($this->periodo,$fechainicio,$fechafin,$fechaclases,$fechacorte);
          $json[] = $this->sweetalert->setBasicAlert("success","Éxito","Se ha modificado el período con éxito.");
          $this->getResponse()->setBody(Zend_Json::encode($json));
        }
      }
/*
      public function periodoAction() {        
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $this->periodo = $this->periodos->getSelect();
            $this->SwapBytes_Ajax_Action->fillSelect($this->periodo);
        }

      }*/

}
      

?>
