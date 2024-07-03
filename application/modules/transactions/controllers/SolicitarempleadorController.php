<?php

class Transactions_solicitarempleadorController extends Zend_Controller_Action{

    private $Title                 = 'Transacciones \ Solicitar Empleador';
    private $msg_error_seniat      = 'No se pudo establecer conexion con el servidor del Seniat, por favor intente mas tarde.';
    private $msg_error_exist       = 'El Usuario ya es empleador o envio la solicitud, por favor espere pronto sera procesada su solicitud.';
    private $msg_error_user        = 'No se pudo registrar el usuario, por favor intente mas tarde.';
    private $msg_success_user      = 'Se realizo correctamente su solicitud, por favor espere pronto sera procesada';

  public function init() {
    //instanciar clases
    Zend_Loader::loadClass('Models_DbTable_Usuarios');
    Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos'); 
    Zend_Loader::loadClass('Models_DbTable_Instituciones');   
    Zend_Loader::loadClass('Models_DbTable_Solicitudesempleadores');     
    Zend_Loader::loadClass('Forms_Solicitudempleador');
    Zend_Loader::loadClass('Une_Seniat');

    $this->current_user             = new Zend_Session_Namespace('Zend_Auth');
    //instanciar models
    $this->usuarios                 = new Models_DbTable_Usuarios();
    $this->seguridad                = new Models_DbTable_UsuariosGrupos();
    $this->instituciones            = new Models_DbTable_Instituciones();
    $this->solicitudempleador       = new Models_DbTable_Solicitudesempleadores();
    //se instancia request que es el que maneja los _params
    $this->Request = Zend_Controller_Front::getInstance()->getRequest();
    $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
    //$this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
    $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
    $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
    $this->SwapBytes_Form           = new SwapBytes_Form();
    $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
    $this->SwapBytes_Html           = new SwapBytes_Html();
    //$this->SwapBytes_Uri            = new SwapBytes_Uri();
    $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
    $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
    $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
    $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
    $this->seniat                   = new Une_Seniat();
    $this->Instituciones            = new Models_DbTable_Instituciones();
    // FORM
    $this->view->form               = new Forms_Solicitudempleador();
    $this->SwapBytes_Form->set($this->view->form);
    $this->SwapBytes_Form->fillSelectBox('tipo_rif',$this->seniat->array_rif,NULL,NULL);
    $this->view->form = $this->SwapBytes_Form->get();

    $this->grupo = $this->usuarios->getEmpleadorCount($this->current_user->userId);
  }
  
  function preDispatch() {
      if (!Zend_Auth::getInstance()->hasIdentity()) {
          $this->_helper->redirector('index', 'login', 'default');
      }

      if (!$this->seguridad->haveAccessToModule()) {
          $this->_helper->redirector('accesserror', 'profile', 'default');
      }
  }  
  
  public function indexAction() { 
    $this->view->title                    = $this->Title;
    $this->view->SwapBytes_Ajax           = $this->SwapBytes_Ajax;
    $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
    $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
    $this->view->SwapBytes_Jquery_Ui_Form = $this->SwapBytes_Jquery_Ui_Form;
    $this->view->SwapBytes_Jquery_Mask    = $this->SwapBytes_Jquery_Mask;
    $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
    $this->view->SwapBytes_Ajax->setView($this->view);
  }

  // Verifcamos RIF y enviamos a la vista
  public function getrifAction(){
    if(!$this->used_rif){
      $json_array = array();
      if($this->_request->isXmlHttpRequest()){
        $this->SwapBytes_Ajax->setHeader();
        //Consultamos informacion del RIF
        $data = $this->seniat->getRifInformation($_GET['rif'],$_GET['tipo_rif']);
        if(!empty($data)){
          $json_array[] = true;
          $json_array[] = $data['rif'];
          $json_array[] = $data['razonsocial'];
          echo Zend_Json::encode($json_array);
        }
        else {
          $json_array[] = false; $json_array[] = null;  $json_array[] = null;
          echo Zend_Json::encode($json_array); 
        }
      }
    }
  }

  public function agregarAction(){
    $this->SwapBytes_Ajax->setHeader();
    $solicitud     = $this->_getAllParams();
    $tipopasantia  = 8223;
    //Data de la Empresa en la Base de Datos
    $institucion = $this->Instituciones->getEmpresaByRif($solicitud['tipo_rif'].$solicitud['rif']);
    $institucion = $institucion[0];
    if(empty($institucion)){
      //Data Empresa (RIF y Razon Social)
      $data_seniat = $this->seniat->getRifInformation($solicitud['rif'],$solicitud['tipo_rif']);
      if(!empty($data_seniat)){
        $arrayEmpresa = array(
          'rif'          => $data_seniat['rif'],
          'razonsocial'  => $data_seniat['razonsocial']
        );
      }
    }
    //verifico cedula 
    if (!empty($this->current_user->userId) and $this->current_user->userId > 0){
      //Verifico que el usuario exista y no tenga grupo
      if(!$this->grupo){
        //Asocio Usuario con Grupo de Pre-Empleador
        $this->usuarios->agregarPreEmpleador($this->current_user->userId);
        //Refresco Data del Grupo
        $this->grupo = $this->usuarios->getEmpleadorCount($this->current_user->userId);
      }
      //Creo la Institucion si no existe
      if(empty($institucion) and !empty($arrayEmpresa)){
        //Agrego Empresa
        $this->Instituciones->addEmpresa($arrayEmpresa);
        //Refresco Data de la Institucion
        $institucion = $this->Instituciones->getEmpresaByRif($arrayEmpresa['rif']);
        $institucion = $institucion[0];
      }
      if(!empty($institucion)){
        //Verifico que no este asociado a la Institcion
        $exist = $this->solicitudempleador->getInstitucionEmpleador($institucion['pk_institucion'],$this->current_user->userId);
        if(!$exist){
          //Asocio Usuario con la Institucion
          $this->solicitudempleador->asociarEmpleador($institucion['pk_institucion'],$this->current_user->userId);
          //Registro Exitoso
          $json[] = 'sweetAlert({title: "Registro Exitoso", text: "'.$this->msg_success_user.'", type: "success",  showCancelButton: false, confirmButtonColor: "#00787A"},function(){});';
        }
        else{
          //Ya esta asociado el Empleador a la Institucion
          $json[] = "sweetAlert(\"Error\", \"$this->msg_error_exist\", \"error\")";
        }
      }
      if(empty($institucion) and empty($arrayEmpresa)) {
        //Error no tengo data de la Institucion
        $json[] = "sweetAlert(\"Error ...\", \"$this->msg_error_seniat\", \"error\")";
      }
    }
    else {
      $json[] = "sweetAlert(\"Error ...\", \"$this->msg_error_user\", \"error\")";
    }
    echo Zend_Json::encode($json);
  }

}

?>