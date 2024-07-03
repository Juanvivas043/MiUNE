<?php
class LoginController extends Zend_Controller_Action {

    public function init() {
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Forms_Login');

        $this->configs        = Zend_Registry::get('config');
        $this->grupo = new Models_DbTable_UsuariosGrupos();
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
	    $this->AuthSpace->setExpirationSeconds($this->configs->session->timeout * 60);
        $this->Zend_Auth = Zend_Auth::getInstance();

        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->SwapBytes_Uri  = new SwapBytes_Uri();
    }

    public function getForm() {
        return new Forms_Login(array(
        'action' => $this->view->baseUrl() . '/login/process',
        'method' => 'post',
        ));
    }

    public function indexAction() {
        /*
         *
         */
        //if(isset($_SERVER['SSL_CLIENT_VERIFY']) && $_SERVER['SSL_CLIENT_VERIFY'] == 'SUCCESS') {
            $this->view->form = $this->getForm();
        //}
    }

    /**
     * Se inicia antes de ejecutar el metodo indexAction, con el fin de validar
     * si existe autentificación y evitar mostrar el formulario de login.
     */
    public function preDispatch() {
        if(Zend_Auth::getInstance()->hasIdentity()) {
            if('logout' != $this->getRequest()->getActionName()) {
                //$this->_helper->redirector('index', 'index');
            }
        } else {
            if('logout' == $this->getRequest()->getActionName()) {
                $this->_helper->redirector('index');
            }
        }
    }

    /**
     * Define los parametros de busqueda del usuario en la Base de Datos y los
     * valores capturados en el formulario, para poder realizar la comparación
     * de los mismos y realizar el debido proceso de autentificación.
     *
     * @param array $params
     * @return Zend_Auth_Adapter_DbTable
     */
    public function getAuthAdapter(array $params) {
        $adapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());  
        $adapter->setTableName('tbl_usuarios');
        $adapter->setIdentityColumn('pk_usuario');
        $adapter->setCredentialColumn('passwordhash');
        $adapter->setIdentity($params['username']);
        $adapter->setCredential(hash('MD5', $params['password']));

        return $adapter;
    }

    /**
     * Procesa la autentificación.
     */
    public function processAction() {
        $request  = $this->getRequest();
        $form     = $this->getForm();
	
        // Verifica si tiene un POST request.
        if(!$request->isPost()) {
            return $this->_helper->redirector('index');
        }

        // Valida el formulario.
        if(!$form->isValid($request->getPost())) {
            // En caso de que no es correcto los datos del formulario y vuelve
            // a mostrar la pantalla de login.
            $this->view->form = $form;
            return $this->render('index');
        }

        // Obtenemos los datos del formulario.
        $params = $form->getValues();

        // Verificamos si el usuario tiene acceso a la aplicación.
        $haveAccess = $this->grupo->haveAccessToApp($params['username'], 'MiUNE Control De Estudios');
        
        // Obtiene el adaptador de autentificacion y verifica las credenciales.
        $adapter = $this->getAuthAdapter($params);
        $result  = $this->Zend_Auth->authenticate($adapter);
        
        if((!$result->isValid()) || empty($haveAccess) || ($haveAccess == 0)) {
            // En caso de que las credenciales no son validas muestra el siguiente
            // mensaje y vuelve a mostrar la pantalla de login.
            $this->Zend_Auth->clearIdentity();
            $this->view->failedAuthentication = true;
            $this->view->form = $form;

            return $this->render('index');
        } else {
             Zend_Session::start();
             Zend_Session::regenerateId();
             
             $storage = $this->Zend_Auth->getStorage();
             $bddResultRow = $adapter->getResultRowObject();
             $storage->write($bddResultRow);
             
             $this->AuthSpace->userId = $params['username'];
        }

        // Si la autentificación es correcta redirecciona a la pagina de inicio.
        //$this->_helper->redirector('index','index');
        $this->_helper->redirector('','inicio');
    }

    /**
     * Verifica si la sesión a expirado por el tiempo de inactividad, al superar
     * el tiempo definido en el archivo de configuración de la aplicación, esta
     * destruye la sesión y refresca la pagina para que pregunte nuevamente los
     * datos de autentificación.
     */
    public function expiredAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
            if(getenv('APPLICATION_ENV') == 'production') {
                $json = array();
                $configs        = Zend_Registry::get('config');
                $dateLastLoad   = new Zend_Date($this->AuthSpace->lastLoadPage);
                $dateCurrent    = new Zend_Date(Zend_Date::now());
                $dateDifference = $dateCurrent->sub($dateLastLoad)->toString(Zend_Date::MINUTE);

                // if($dateDifference >= $configs->session->timeout) {
                //     $this->Zend_Auth->clearIdentity();

                //     $json[] = "document.location.href = '{$this->SwapBytes_Uri->getProtocol()}://{$this->getRequest()->getHttpHost()}{$this->view->baseUrl()}'";
                // }
                
                $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }
    }

    /**
     * Destruye la sesion y redirecciona a la pantalla de login.
     */
    public function logoutAction() {
        $this->Zend_Auth->clearIdentity();
        $this->_helper->redirector('index');
    }

	// Refresca la sesion del u
    public function refreshAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
		$this->AuthSpace->setExpirationSeconds($this->configs->session->timeout * 60);	
            } 
    }

}
?>