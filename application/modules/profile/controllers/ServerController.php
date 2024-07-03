<?php

class Profile_ServerController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
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
    }

    public function indexAction() {
        $this->view->title = 'Perfil \ Servidor';
        
        $this->getSVN();
        $this->getPHP();
        $this->getDB();
    }

    private function getSVN() {
        $url = APPLICATION_PATH;
        $cmdVersion = "svn info $url | grep Revision: | cut -c11-";
        $cmdDate = "svn info $url | grep 'Last Changed Date:' | cut -c20-";
        $valVersion = exec($cmdVersion);
        $valDate = exec($cmdDate);
        
        echo "<br>";
        echo "SVN Revisión: {$valVersion}<br>";
        echo "SVN Ultima actualización: {$valDate}<br>";
    }

    private function getPHP() {
        $version = phpversion();
        
        echo "<br>";
        echo "PHP Versión: {$version}<br>";
    }

    private function getDB() {
        $config  = Zend_Registry::get('config');

        $dbtype  = $config->database->adapter;
		$dbhost  = $config->database->params->host;
		$dbname  = $config->database->params->dbname;
		$dbuser  = $config->database->params->username;
		$dbpass  = $config->database->params->password;
        $conn    = pg_connect("user={$dbuser} password={$dbpass} dbname={$dbname} host={$dbhost}");
        $version = pg_version($conn);
        $port    = pg_port($conn);

        echo "<br>";
        echo "Database Type: {$dbtype}<br>";
        echo "Database Host: {$dbhost}<br>";
        echo "Database Port: {$port}<br>";
        echo "Database Name: {$dbname}<br>";
        echo "Database Versión: {$version['server']}<br>";
    }
}
