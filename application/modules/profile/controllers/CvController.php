<?php
class Profile_CvController extends Zend_Controller_Action {

    private $Title                  = 'Curriculum Vitae';
    private $msg_new                = 'Usted no posee su Curriculum Vitae en el sistema.';
    private $msg_update             = 'Usted posee su Curriculum Vitae en el sistema actualizado a la fecha ';
    private $msg_success            = 'La actualizacion del perfil de la empresa se realizo exitosamente.';
    private $msg_error              = 'Revisar los campos asegurese que no esten vacios o mal escritos.';
    
    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Usuarios');  
    	Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Instituciones'); 
        Zend_Loader::loadClass('Models_DbTable_Usuariosarchivos');
        Zend_Loader::loadClass('Une_Seniat');

        $this->current_user             = new Zend_Session_Namespace('Zend_Auth');
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->Request                  = Zend_Controller_Front::getInstance()->getRequest();

        $this->usuario                  = new Models_DbTable_Usuarios();
        $this->grupo                    = new Models_DbTable_UsuariosGrupos();
        $this->instituciones            = new Models_DbTable_Instituciones();
        $this->usuariosarchivos         = new Models_DbTable_Usuariosarchivos();
        $this->seniat                   = new Une_Seniat();

        $this->cv                       = $this->usuariosarchivos->countCV($this->current_user->userId);
        $this->cv_user                  = $this->usuariosarchivos->getDocument(20117,$this->current_user->userId);
    }
    
     function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
             $this->_helper->redirector('index', 'login', 'default');
         }
         if (!$this->grupo->haveAccessToModule()) {
             $this->_helper->redirector('accesserror', 'profile', 'default');
         }
     }
    
    public function indexAction() {
        $this->view->titulo                = $this->Title;
        $this->view->SwapBytes_Ajax        = $this->SwapBytes_Ajax;
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Jquery_Mask = $this->SwapBytes_Jquery_Mask;
        $this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
        if($this->cv){
            $this->cv_user        = $this->usuariosarchivos->getDocument(20117,$this->current_user->userId);
            $this->view->document = $this->cv_user[0]['ruta'];
        }
        //Carga 
        if(isset($_GET['txtBuscar']) and isset($_GET['selDateDesde']) and isset($_GET['selDateHasta']) and isset($_GET['id'])){
            $this->view->search = $_GET['txtBuscar'];
            $this->view->desde  = $_GET['selDateDesde'];
            $this->view->hasta  = $_GET['selDateHasta'];
            $this->view->id     = $_GET['id'];
        }
    }

    public function getstatusAction(){
        if($this->_request->isXmlHttpRequest()){
            $this->SwapBytes_Ajax->setHeader();
            if($this->cv) {
                $json[] = true;
                $json[] = $this->msg_update."<strong>".$this->cv_user[0]['fecha']."</strong>";
            }
            else {
                $json[] = false;
                $json[] = $this->msg_new;
            }    
            echo Zend_Json::encode($json);
        }
    }

    public function updateAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
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
                    'razonsocial'       => $data['razonsocial']
                );
                if ($this->instituciones->updateRow($empresa['empresa_id'],$arrayEmpresa)){
                    $json[] = 'sweetAlert("Actualizado", "'.$this->msg_success.'", "success");';
                }
                else {
                    $json[] = 'sweetAlert("Error", "'.$this->msg_error.'", "error");';
                }  
            }
            else {
                $json[] = 'sweetAlert("Error", "'.$this->msg_error_seniat.'", "error");';
            }            
            echo Zend_Json::encode($json);
        }
    }  

    public function uploadAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            if(isset($_FILES['file']) and $_FILES['file']['size'] > 0){
                $tmp       = $_FILES['file']['tmp_name'];
                $extension = substr($_FILES['file']['type'],strpos($_FILES['file']['type'],"/") + 1);
                $file_name = $this->current_user->userId.".".$extension;
                $route     = exec("pwd")."/uploads/cv/".$file_name;
                if(move_uploaded_file($tmp,$route)){
                    if($this->cv){
                        //Update
                        $dataRow                      = $this->cv_user;
                        $dataRow                      = $dataRow[0];
                        $id                           = $dataRow['pk_usuarioarchivo'];
                        $dataRow['ruta']              = "../uploads/cv/".$file_name;
                        $dataRow['pk_usuarioarchivo'] = null;
                        $this->usuariosarchivos->updateRow($id,$dataRow);
                    }
                    else{
                        //Add
                        $dataRow['ruta']       = "../uploads/cv/".$file_name;
                        $dataRow['fk_usuario'] = $this->current_user->userId;
                        $dataRow['fk_tipo']    = 20117;
                        $dataRow['fecha']      = date("Y-m-d");
                        $this->usuariosarchivos->addRow($dataRow);
                    }
                    $json[] = 'sweetAlert("Exito", "'.$this->msg_success.'", "success");';
                }
                else {
                    $json[] = 'sweetAlert("Error", "'.$this->msg_error.'", "error");';
                }
            }
            else{
                $json[] = 'sweetAlert("Error", "'.$this->msg_error.'", "error");';
            }
            echo Zend_Json::encode($json);
        }
    }

}
?>