<?php
class Profile_PerfilempresaController extends Zend_Controller_Action {

    private $Title                  = "Actualizacion de Empresa";
    private $msg_success            = "La actualizacion del perfil de la empresa se realizo exitosamente.";
    private $msg_success_empresas   = "Para  cambiar el perfil de la empresa no se debe dejar ningun espacio en blanco y los campos en rojo estan mal incorrectos.";
    private $msg_success_picture    = "Se actualizo correctamente la Foto.";
    private $msg_error              = "Revisar los campos asegurese que no esten vacios o mal escritos.";
    private $msg_error_seniat       = "No se pudo establecer conexion con el servidor del Seniat, por favor intente mas tarde.";
    private $msg_error_empresas     = "Usted no tiene Empresas Asociadas.";
    private $msg_error_picture      = "No se pudo actualizar la Foto.";
    
    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Forms_Empresaperfil');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');  
    	Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Instituciones'); 
        Zend_Loader::loadClass('Une_Seniat');
        Zend_Loader::loadClass('Une_Googlemaps');
        Zend_Loader::loadClass('Une_Sweetalert');

        $this->current_user             = new Zend_Session_Namespace('Zend_Auth');
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->Request                  = Zend_Controller_Front::getInstance()->getRequest();

        $this->view->form               = new Forms_Empresaperfil();
        $this->usuario                  = new Models_DbTable_Usuarios();
        $this->grupo                    = new Models_DbTable_UsuariosGrupos();
        $this->instituciones            = new Models_DbTable_Instituciones();
        $this->seniat                   = new Une_Seniat();
        $this->maps                     = new Une_Googlemaps();
        $this->sweetalert               = new Une_Sweetalert();

        $this->SwapBytes_Form->set($this->view->form);
        $this->SwapBytes_Form->fillSelectBox('tipo_rif',$this->seniat->array_rif, NULL, NULL);
        $this->SwapBytes_Form->fillSelectBox('empresa_id', $this->instituciones->getEmpresaNameByEmpleador($this->current_user->userId) , 'pk_institucion', 'nombre');
        $this->empresas = $this->instituciones->getEmpresaByEmpleador($this->current_user->userId);
        $this->id       = $this->empresas[0]['pk_institucion'];
    }
    
    function preDispatch() {
       if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
        if (!$this->grupo->haveAccessToModule()) {
                //$this->_helper->redirector('accesserror', 'profile', 'default');
            }
    }
    
    public function indexAction() {
        $this->view->titulo                = $this->Title;
        $this->view->SwapBytes_Ajax        = $this->SwapBytes_Ajax;
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Jquery_Mask = $this->SwapBytes_Jquery_Mask;
        $this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
        $this->view->sweetalert            = $this->sweetalert;
        $this->view->map                   = $this->SwapBytes_Jquery->setHtml('mapTable',$this->maps->setMarkup(NULL));
        if(!empty($this->empresas)) {
            $empresa             = $this->empresas[0];
            $empresa["tipo_rif"] = substr($empresa["rif"],0,1);
            $empresa["rif"]      = str_replace($this->seniat->array_rif,"",$empresa["rif"]);
            $this->view->form->populate($empresa);        
        }
    }

    public function getstatusAction(){
        if($this->_request->isXmlHttpRequest()){
            $this->SwapBytes_Ajax->setHeader();
            if(!empty($this->empresas)) {
                $json[] = $this->SwapBytes_Jquery->setText('msg',$this->msg_success_empresas);
                $map = $this->maps->setMap($this->empresas[0]['nombre']);
                $json[] = $this->SwapBytes_Jquery->setHtml('mapScript',$map);
            }
            else {
                $json[] = $this->SwapBytes_Jquery->setText('msg',$this->msg_error_empresas);
                $json[] = $this->SwapBytes_Jquery->setHideTag('dd');
                $json[] = $this->SwapBytes_Jquery->setHideTag('dt');
            }
            echo Zend_Json::encode($json);
        }
    }

    public function getempresaAction(){
        if($this->_request->isXmlHttpRequest()){
            $this->SwapBytes_Ajax->setHeader();
            //Get Data of Institution
            $empresa = intval($this->_getParam('empresa_id'));
            $data    = $this->instituciones->getRow($empresa);
            $id      = $data['pk_institucion'];
            $map     = $this->maps->setMap($data['nombre']);
            $array   = array(
                            array("id",$data['pk_institucion']),
                            array("tipo_rif",substr($data["rif"],0,1)),
                            array("rif",str_replace($this->seniat->array_rif,"",$data["rif"])),
                            array("razonsocial",$data['razonsocial']),
                            array("nombre",$data['nombre']),
                            array("direccion",$data['direccion']),
                            array("telefono",$data['telefono']),
                            array("telefono2",$data['telefono2'])
                        );
            $json[]  = $this->SwapBytes_Jquery->setVals($array);
            $json[]  = $this->SwapBytes_Jquery->setAttr("foto", "src", "urlAjax + 'getPicture/id/{$id}'") . ";";
            $json[]  = $this->SwapBytes_Jquery->setHtml('mapScript',$map);
            $json[]  = "$('#file-dropzone').fadeOut(\"slow\");";
            $json[]  = "pic = false;";
            //Send Data
            echo Zend_Json::encode($json);
        }
    }

    public function validateAction(){
        $this->SwapBytes_Ajax->setHeader();
        $usuario = $this->_getAllParams();
    }

    // Verifcamos RIF y enviamos a la vista
    public function getrifAction(){
        $json = array();
        if($this->_request->isXmlHttpRequest()){
            $this->SwapBytes_Ajax->setHeader();
            $rif  = $this->_getParam('rif');
            $tipo = $this->_getParam('tipo_rif');
            //Consultamos informacion del RIF
            $data = $this->seniat->getRifInformation($rif,$tipo);
            if(!empty($data)){
                $json[] = $this->SwapBytes_Jquery->setVal('razonsocial',$data['razonsocial']);
            }
            else {
                $json[] = $this->sweetalert->setBasicAlert("error","Error",$this->$msg_error_seniat);
            }
            echo Zend_Json::encode($json);
        }
    }

    public function updateAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $form    = new Forms_Empresaperfil();
            //Params
            $empresa = $this->_getAllParams();
            //Consulta Seniat
            $data = $this->seniat->getRifInformation($empresa['rif'],$empresa['tipo_rif']);
            //Mensaje
            if(!empty($data)) { 
                $arrayEmpresa = array(
                    'nombre'            => strtoupper($empresa['nombre']),
                    'direccion'         => $empresa['direccion'],
                    'telefono'          => $empresa['telefono'],
                    'telefono2'         => $empresa['telefono2'],
                    'fk_tipopasantia'   => $this->empresa['fk_tipopasantia'],
                    'rif'               => $data['rif'],
                    'razonsocial'       => $data['razonsocial'],
                    'latitud'           => floatval($empresa['Latitud']),
                    'longitud'          => floatval($empresa['Longitud'])
                );
                if($this->instituciones->updateRow($empresa['empresa_id'],$arrayEmpresa)){
                    $json[] = $this->sweetalert->setFunctionAlert("success","Actualizado",$this->msg_success,"location.reload(true);",'confirmButtonColor: "#00787A"');
                }
                else {
                    $json[] = $this->sweetalert->setBasicAlert("error","Error",$this->$msg_error);
                }  
            }
            else {
                $json[] = $this->sweetalert->setBasicAlert("error","Actualizado",$this->$msg_error_seniat);
            }            
            echo Zend_Json::encode($json);
        }
    }  

    public function uploadAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $empresa = intval($this->_getParam('id'));
            if(isset($_FILES['file']) and $_FILES['file']['size'] > 0){
                $this->instituciones->uploadPicture($_FILES['file'],$empresa);
                $json[] = $this->sweetalert->setFunctionAlert("success","Actualizado",$this->msg_success_picture,"location.reload(true);",'confirmButtonColor: "#00787A"');
            }
            else{
                $json[] = $this->sweetalert->setBasicAlert("error","Actualizado",$this->$msg_error_picture);
            }
            echo Zend_Json::encode($json);
        }
    }

    public function getpictureAction(){
        $this->SwapBytes_Ajax->setHeader();
        $empresa = $this->_getAllParams();
        if(isset($empresa['id'])){
            $id = $empresa['id'];
        }
        else {
            $id = $this->empresas[0]['pk_institucion'];
        }
        $foto = $this->instituciones->getPicture($id);
        if(empty($foto)){
            $foto = file_get_contents(APPLICATION_PATH . '/../public/images/empresa-not-found-large.jpg');
        }
        $this->getResponse()
                 ->setHeader('Content-type', 'image/jpeg')
                 ->setBody($foto);
    }
}
?>