<?php

class Profile_ActualizaciondatosController extends Zend_Controller_Action{

	private $title       = 'Actualizacion de perfil';
	private $msg_success = "Su actualizacion de perfil se realizo exitosamente";
	private $msg_error   = "Revisar los campos asegurese que no esten vacios o mal escritos";

    public function init(){
        /* Initialize action controller here */
        Zend_Loader::loadClass('Forms_Actualizaciondatos');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');  
		Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
		Zend_Loader::loadClass('Une_Sweetalert');
		$this->grupo 					= new Models_DbTable_UsuariosGrupos();
        $this->view->form 				= new Forms_Actualizaciondatos();
        $this->current_user 			= new Zend_Session_Namespace('Zend_Auth');
        $this->usuario 					= new Models_DbTable_Usuarios();
        $this->SwapBytes_Ajax         	= new SwapBytes_Ajax();
        $this->SwapBytes_Jquery_Mask 	= new SwapBytes_Jquery_Mask();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->user  					= $this->usuario->getUsuario($this->current_user->userId);
        $this->sweetalert               = new Une_Sweetalert(); 
    }

    public function indexAction(){
        $this->view->titulo 			   = $this->title;
        $this->view->SwapBytes_Ajax 	   = $this->SwapBytes_Ajax;
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Jquery_Mask = $this->SwapBytes_Jquery_Mask;
        $this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
        $this->view->sweetalert            = $this->sweetalert;
        $array   = array(
        					array("pk_usuario",$this->user['pk_usuario']),
        					array("primer_nombre",$this->user['primer_nombre']),
        					array("segundo_nombre",$this->user['segundo_nombre']),
        					array("primer_apellido",$this->user['primer_apellido']),
        					array("segundo_apellido",$this->user['segundo_apellido']),
        					array("fechanacimiento",$this->user['fechanacimiento']),
        					array("direccion",$this->user['direccion']),
        					array("correo",$this->user['correo']),
        					array("telefono",$this->user['telefono']),
        					array("telefono_movil",$this->user['telefono_movil']),
                        );
        //radio buttons
        $sexo 		  = $this->SwapBytes_Jquery->setRadio("sexo",$this->user['sexo']);
        $nacionalidad = $this->SwapBytes_Jquery->setRadio("nacionalidad",$this->user['nacionalidad']);
       	$this->view->data = $this->SwapBytes_Jquery->setVals($array) . $sexo . $nacionalidad;
    }

    public function validateAction(){
        $this->SwapBytes_Ajax->setHeader();
        $usuario = $this->_getAllParams();
    }

    public function updateAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $form 			= new Forms_Actualizaciondatos();
            $usuario 		= $this->_getAllParams();
            $arrayDataUser 	= array(            
	            'primer_nombre'         => $usuario['primer_nombre'],
	            'segundo_nombre'        => $usuario['segundo_nombre'],
	            'primer_apellido'       => $usuario['primer_apellido'],
	            'segundo_apellido'      => $usuario['segundo_apellido'],
	            'fechanacimiento'       => $usuario['fechanacimiento'],
	            'sexo'                  => $usuario['sexo'],
	            'nacionalidad'          => $usuario['nacionalidad'],
	            'direccion'             => $usuario['direccion'],
	            'telefono_movil'        => $usuario['telefono_movil'],
	            'telefono'              => $usuario['telefono'],
	            'correo'                => $usuario['correo'],
	            'actualizado'			=> true
	        );
            if ($form->isValid($arrayDataUser)){
               $this->user = $this->usuario->updateUsuario($this->current_user->userId, $arrayDataUser, true);
               $function = 'window.location="http://'.$_SERVER['HTTP_HOST'].'/MiUNE2/inicio";';
               $json[] = $this->sweetalert->setFunctionAlert("success","Actualizado",$this->msg_success,$function,'confirmButtonColor: "#00787A", showCancelButton: false');
            }else{
            	$json[] = $json[] = $this->sweetalert->setBasicAlert("error","Error",$this->$msg_error);
            }             
            echo Zend_Json::encode($json);
         }  

    }

}

?>