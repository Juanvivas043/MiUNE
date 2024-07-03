<?php

  class Transactions_NuevousuarioController extends Zend_Controller_Action{

    private $Title                = 'Transacciones \ Agregar Nuevo Usuario';
    private $msg_error_seniat     = 'No se pudo establecer conexion con el servidor del Seniat, por favor intente mas tarde.';
    private $msg_error_exist      = 'El Usuario ya esta registrado, por favor solicite desde el Sistema MiUNE.';
    private $msg_error_user       = 'No se pudo registrar el usuario, por favor intente mas tarde.';
    private $msg_error_captcha    = 'No se pudo registrar el usuario, debe comprobar el Captcha.';
    private $msg_error_getcaptcha = 'No se pudo comprobar el Captcha, por favor internte mas tarde';
    private $msg_success_user     = 'El Usuario se Registro exitosamente, recuerde su contraseña es su numero de cedula.';

    public function init() {
      //instanciar clases
      Zend_Loader::loadClass('Models_DbTable_Usuarios');
      Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos'); 
      Zend_Loader::loadClass('Models_DbTable_Instituciones');   
      Zend_Loader::loadClass('Models_DbTable_Solicitudesempleadores');     
      Zend_Loader::loadClass('Forms_Createuser');
      Zend_Loader::loadClass('Une_Seniat');
      Zend_Loader::loadClass('Une_Recaptcha');
      Zend_Loader::loadClass('Une_Sweetalert');

      //instanciar models
      $this->usuarios                 = new Models_DbTable_Usuarios();
      $this->grupo                    = new Models_DbTable_UsuariosGrupos();
      $this->instituciones            = new Models_DbTable_Instituciones();
      $this->solicitudempleador       = new Models_DbTable_Solicitudesempleadores();
      //se instancia request que es el que maneja los _params
      $this->Request                  = Zend_Controller_Front::getInstance()->getRequest();
      $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
      $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
      $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
      $this->SwapBytes_Form           = new SwapBytes_Form();
      $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
      $this->SwapBytes_Html           = new SwapBytes_Html();
      $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
      $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
      $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
      $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
      $this->seniat                   = new Une_Seniat();
      $this->recaptcha                = new Une_ReCaptcha();
      $this->sweetalert               = new Une_Sweetalert(); 
      $this->Instituciones            = new Models_DbTable_Instituciones();
      $this->authSpace                = new Zend_Session_Namespace('Zend_Auth');
      // FORM
      $this->view->form               = new Forms_Createuser();
      $this->SwapBytes_Form->set($this->view->form);
      $this->SwapBytes_Form->fillSelectBox('tipo_rif',$this->seniat->array_rif);
      $this->view->form = $this->SwapBytes_Form->get();
  }
  
  function preDispatch() {
      if (!Zend_Auth::getInstance()->hasIdentity()) {
          $this->_helper->redirector('index', 'login', 'default');
      }

      if (!$this->grupo->haveAccessToModule()) {
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
      $this->view->sweetalert               = $this->sweetalert;
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

  public function getuserAction(){
    $json_array = array();
    if($this->_request->isXmlHttpRequest()){
        $this->SwapBytes_Ajax->setHeader();
        $user = $this->usuarios->getCount($_GET['pk_usuario']);
        if(!empty($user)){
            $json_array[] = true;
        }
        else {
            $json_array[] = false;
            $json_array[] = "sweetAlert(\"Error ...\", \"$this->msg_error_exist\", \"error\")";
        }
        echo Zend_Json::encode($json_array);
    }
  }

  public function agregarAction(){
    $this->SwapBytes_Ajax->setHeader();
    $empleador            = $this->_getAllParams();

    $captcha = $this->recaptcha->checkCaptcha($empleador['recaptcha']);
    if($captcha->success and $captcha->errorCodes == null){
      //Set Rif
      $empleador['rif']     = $this->seniat->setRif($empleador['rif']);
      $tipopasantia         = 8223;
      //Data de la Empresa en la Base de Datos
      $institucion = $this->Instituciones->getEmpresaByRif($empleador['tipo_rif'].$empleador['rif']);
      $institucion = $institucion[0];
      if(empty($institucion)){
        //Data Empresa (RIF y Razon Social)
        $data_seniat = $this->seniat->getRifInformation($empleador['rif'],$empleador['tipo_rif']);
        if(!empty($data_seniat)){
          $arrayEmpresa = array(
            'rif'         => $data_seniat['rif'],
            'razonsocial' => $data_seniat['razonsocial'],
            'nombre'      => substr($data_seniat['razonsocial'],0,25)
          );
        }
      }
      $arrayDataUser = array(
        'pk_usuario'            => $empleador['pk_usuario'],
        'status'                => 0,
        'nacionalidad'          => $empleador['nacionalidad'],
        'sexo'                  => $empleador['sexo'],
        'nombre'                => strtoupper($empleador['primer_nombre']." ".$empleador['segundo_nombre']),
        'apellido'              => strtoupper($empleador['primer_apellido']." ".$empleador['segundo_apellido']),
        'direccion'             => strtoupper($empleador['direccion']),
        'fechanacimiento'       => $empleador['fechanacimiento'],
        'correo'                => $empleador['correo'],
        'passwordhash'          => md5($empleador['pk_usuario']),
        'deleted'               => false,
        'telefono'              => $empleador['telefono'],
        'foto'                  => "",
        'telefono_movil'        => $empleador['telefono_movil'],
        'passwordehash'         => "",
        'primer_nombre'         => strtoupper($empleador['primer_nombre']),
        'segundo_nombre'        => strtoupper($empleador['segundo_nombre']),
        'primer_apellido'       => strtoupper($empleador['primer_apellido']),
        'segundo_apellido'      => strtoupper($empleador['segundo_apellido']),
        'actualizado'           => false     
      );  
      if (!empty($arrayDataUser)){
        $user = $this->usuarios->getCount($arrayDataUser['pk_usuario']);
        //Verifico que el usuario no exista
        if(!$user){
          //Creo la Institucion si no existe
          if(empty($institucion) and !empty($arrayEmpresa)){
            //Agrego Empresa
            $this->Instituciones->addEmpresa($arrayEmpresa);
            //Refresco Data de la Institucion
            $institucion = $this->Instituciones->getEmpresaByRif($arrayEmpresa['rif']);
            $institucion = $institucion[0];
          }
          if(empty($institucion) and empty($arrayEmpresa)) {
            $json[] = "sweetAlert(\"Error ...\", \"$this->msg_error_seniat\", \"error\")";
          }
          if(!empty($institucion)){
            //Agrego Usuario 
            $this->usuarios->addRow($arrayDataUser);
            //Asocio Usuario con Grupo de Pre-Empleador
            $this->usuarios->agregarPreEmpleador($arrayDataUser['pk_usuario']);
            //Asocio Usuario con la Institucion
            $this->solicitudempleador->asociarEmpleador($institucion['pk_institucion'],$arrayDataUser['pk_usuario']);
            //Mensaje de Registro Exitoso
            $json[] = 'swal({ title: "Exito ...", text: "'.$this->msg_success_user.'", type: "success", closeOnConfirm: false }, function(){ window.location="http://'.$_SERVER['HTTP_HOST'].'/MiUNE2/inicio" });';
          }
        }
        else {
          $json[] = "sweetAlert(\"Error ...\", \"$this->msg_error_exist\", \"error\")";
        }
      }
      else {
        $json[] = "sweetAlert(\"Error ...\", \"$this->msg_error_user\", \"error\")";
      }
    }
    else {
      $json[] = "sweetAlert(\"Error ...\", \"$this->msg_error_getcaptcha\", \"error\")";
    }
    echo Zend_Json::encode($json); 
  }

}
?>