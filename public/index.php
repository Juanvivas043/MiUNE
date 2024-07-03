<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library/'),
    realpath(APPLICATION_PATH . '/../library/Pear/FileFormats/'),
    realpath(APPLICATION_PATH . '/../application/'),
    realpath(APPLICATION_PATH . '/../application/templates/'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';  

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV, 
    APPLICATION_PATH . '/configs/application.ini'
);
//Captura cualquier error que haya al tratar de conectarse con MiUNE
try{
    $application->bootstrap()
                ->run();
}catch(Exception $e){
        session_start();
        $_SESSION['number']  = $e->getCode();
        $_SESSION['message'] = $e->getMessage();
        echo  $_SESSION['number'] . " : " . $_SESSION['message'];
}
