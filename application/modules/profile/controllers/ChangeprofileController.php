<?php

class Profile_ChangeprofileController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Forms_Changeprofile');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');  
	Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
	$this->grupo = new Models_DbTable_UsuariosGrupos();
        $this->view->form = new Forms_Changeprofile();
        $this->current_user = new Zend_Session_Namespace('Zend_Auth');
        $this->usuario = new Models_DbTable_Usuarios();
        $this->SwapBytes_Ajax         = new SwapBytes_Ajax();
        $this->SwapBytes_Jquery_Mask = new SwapBytes_Jquery_Mask();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();

        $this->user = $this->usuario->getUsuario($this->current_user->userId); 

        
    }

    /** 
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     *
     * @todo Agregar la validación por el permiso en la DB.
     */
    function preDispatch() {
       if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
        if (!$this->grupo->haveAccessToModule()) {
                $this->_helper->redirector('accesserror', 'profile', 'default');
            }
    }


    public function indexAction()
    {
        // action body
        $this->view->titulo = 'Actualizacion de perfil';
        // $this->formulario-> ;      
        $this->view->form->populate($this->user); //--------->el populate nos permite llenar las casillas de los input con los datos que se desean
        $this->view->SwapBytes_Ajax = $this->SwapBytes_Ajax;
        $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Jquery_Mask = $this->SwapBytes_Jquery_Mask;
        $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;




        /* var_dump($this->getUsuario->userId);
        die; dice los que esta trayendo la variable */       

       
    }

    public function validateAction(){

        $this->SwapBytes_Ajax->setHeader();
        $usuario = $this->_getAllParams();
        

            

    }

    public function updateAction(){
        //if ($this->_request->isXmlHttpRequest()) {

                $this->SwapBytes_Ajax->setHeader();
                $form = new Forms_Changeprofile();
                $usuario = $this->_getAllParams();
                $arrayDataUser = array(            
                'primer_nombre'         => $usuario['primer_nombre'],
                'segundo_nombre'        => $usuario['segundo_nombre'],
                'primer_apellido'       => $usuario['primer_apellido'],
                'segundo_apellido'      => $usuario['segundo_apellido'],
                'fechanacimiento'       => $usuario['fechanacimiento'],
                'sexo'                  => $usuario['sexo'],
                'nacionalidad'          => $usuario ['nacionalidad'],
                'direccion'             => $usuario['direccion'],
                'telefono_movil'        => $usuario['telefono_movil'],
                'telefono'              => $usuario['telefono'],
                'correo'                => $usuario['correo']);   

                // $form->populate($arrayDataUser);

                   // var_dump( $arrayDataUser);die; 

                 // var_dump($form->isValid($arrayDataUser));die;
           $json[] = '$(\'#frmDialog\').dialog(\'open\');';

           $json[] = "$('#frmDialog').dialog({ buttons: {'Ok': function(){ if(!$('.invalid').length){window.location.reload()}q }}})";



            if ($form->isValid($arrayDataUser)){

                $this->user = $this->usuario->updateUsuario($this->current_user->userId, $arrayDataUser);

                   $json[] = "$('#frmDialog').html('<div style=\"text-align:left;\">Su actualizacion de perfil se realizo exitosamente.</div>')";
                   $json[] = '$(\'#frmDialog\').dialog({title: \'Actualizado\'});';
            
            }else{
                $json[] = "$('#frmDialog').html('<div style=\"text-align:left;\">Revisar los campos asegurese que no esten vacios o mal escritos.</div>')";
                $json[] = '$(\'#frmDialog\').dialog({title: \'Error\'});';
                } 

             // var_dump($this->user);die;

            
          $json[] = "$('#frmDialog').dialog('option','position','center')";
                // $this->getResponse()->setBody(Zend_Json::encode($json));
                echo Zend_Json::encode($json);

         }  

    }
?>
 



