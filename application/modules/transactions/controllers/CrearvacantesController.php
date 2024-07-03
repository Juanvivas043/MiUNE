<?php

  class Transactions_CrearvacantesController extends Zend_Controller_Action{

    private $Title            = 'Transacciones \ Agregar Nueva Vacante';
    private $msg_error_vacant = 'Error requiere almenos 1 un cupo para la vacante';
    private $msg_error_date   = 'Error la fecha de Culminacion no puede ser antes de la fecha actual';
    private $msg_error_empty  = 'Erro hay campos requeridos vacios';
    private $msg_success      = 'La Vacante fue registrada correctamente';

    public function init() {
      //instanciar clases
      Zend_Loader::loadClass('Models_DbTable_Usuarios');
      Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos'); 
      Zend_Loader::loadClass('Models_DbTable_Instituciones');   
      Zend_Loader::loadClass('Models_DbTable_Solicitudesempleadores');  
      Zend_Loader::loadClass('Models_DbTable_Vacantes');
      Zend_Loader::loadClass('Models_DbTable_Atributos');
      Zend_Loader::loadClass('Forms_Createvacante');

      //instanciar models
      $this->usuarios                 = new Models_DbTable_Usuarios();
      $this->grupo                    = new Models_DbTable_UsuariosGrupos();
      $this->solicitudempleador       = new Models_DbTable_Solicitudesempleadores();
      $this->instituciones            = new Models_DbTable_Instituciones();
      $this->vacantes                 = new Models_DbTable_Vacantes();
      $this->atributos                = new Models_DbTable_Atributos();
      //se instancia request que es el que maneja los _params
      $this->Request = Zend_Controller_Front::getInstance()->getRequest();
      $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
      $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
      $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
      $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
      $this->SwapBytes_Form           = new SwapBytes_Form();
      $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
      $this->SwapBytes_Html           = new SwapBytes_Html();
      $this->SwapBytes_Uri            = new SwapBytes_Uri();
      $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
      $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
      $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
      $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
      $this->current_user             = new Zend_Session_Namespace('Zend_Auth');
      // FORM
      $this->view->form               = new Forms_Createvacante();
      $this->SwapBytes_Form->set($this->view->form);
      $this->view->form = $this->SwapBytes_Form->get();
      $this->SwapBytes_Form->fillSelectBox('empresa_id',$this->instituciones->getEmpresaNameByEmpleador($this->current_user->userId),'pk_institucion','nombre');
      $this->SwapBytes_Form->fillSelectBox('fk_contrato',$this->atributos->getTipes(103, NULL),'pk_atributo','valor');
      $this->empresas = $this->instituciones->getEmpresaByEmpleador($this->current_user->userId);
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
      $this->view->SwapBytes_Ajax->setView($this->view);
  }

  public function getaddressAction() {
    $json_array = array();
    if($this->_request->isXmlHttpRequest()){
      $this->SwapBytes_Ajax->setHeader();
      //Consultamos informacion del RIF
      $data = $this->instituciones->getRow($_GET['empresa']);
      if(!empty($data)){
        $json_array[] = true; $json_array[] = $data['direccion'];
        echo Zend_Json::encode($json_array);
      }
      else {
        $json_array[] = false; $json_array[] = null;
        echo Zend_Json::encode($json_array); 
      }
    }
  }

  public function addvacanteAction(){
    $this->SwapBytes_Ajax->setHeader();
    $vacante = $this->_getAllParams();
    //Date
    $fecha_publicacion = date("Y-m-d");
    if(!empty($_GET['empresa']) and !empty($_GET['title']) and !empty($_GET['contrato']) and !empty($_GET['vacantes']) and !empty($_GET['descripcion']) and !empty($_GET['requisitos']) and !empty($_GET['direccion']) and !empty($_GET['fechaculminacion'])){
      if($_GET['fechaculminacion'] > $fecha_publicacion){
        if($_GET['vacantes'] > 0){
          //Limpio Variables
          if($_GET['sexo'] == null) { $sexo = 2; }
          else { $sexo = $_GET['sexo']; }
          if($_GET['edad'] == null or $_GET['edad'] < 18) { $edad = 18; }
          else { $edad = $_GET['edad']; }
          //Creo Vacante
          $arrayVacante = array(
            'fk_institucion'    => $_GET['empresa'],
            'title'             => $_GET['title'],
            'fk_contrato'       => $_GET['contrato'],
            'vacantes'          => $_GET['vacantes'],
            'fk_sexo'           => $sexo,
            'edad'              => $edad,
            'fecha_publicacion' => $fecha_publicacion,
            'fecha_culminacion' => $_GET['fechaculminacion'],
            'descripcion'       => $_GET['descripcion'],
            'requisitos'        => $_GET['requisitos'],
            'beneficios'        => $_GET['beneficios'],
            'direccion'         => $_GET['direccion'],
          );
          //Agrego Vacante
          $this->vacantes->addRow($arrayVacante);
          //Mensaje de Registro Exitoso
          $json[] = 'sweetAlert({title: "Registro Exitoso", text: "'.$this->msg_success.'", type: "success",  showCancelButton: false, confirmButtonColor: "#00787A", closeOnConfirm: false},function(){location.reload();});';
        }
        else {
          //Error vancantes menor a 0
          $json[] = 'sweetAlert("Error", "'.$this->msg_error_vacant.'", "error");';
        }
      }
      else {
        //Error fecha de culminacion menor de publicacion
        $json[] = 'sweetAlert("Error", "'.$this->msg_error_date.'", "error");';
      }
    }
    else {
      //Error campos vacios
      $json[] = 'sweetAlert("Error", "'.$this->msg_error_empty.'", "error");';;
    }
    echo Zend_Json::encode($json); 
  }

}
?>