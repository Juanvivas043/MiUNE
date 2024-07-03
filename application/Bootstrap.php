<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    /**
     * Define la configuración basica de la base de datos, evitando
     * por completo el traer la configuración, realizar la coneccion
     * cada vez que sea necesario y en cada clase que se requiera.
     */
    protected function _initDatabase() {
        $config    = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $database  = Zend_Db::factory($config->database);

        $database->getConnection();

        if(Zend_Auth::getInstance()->hasIdentity()) {
            $authSpace = new Zend_Session_Namespace('Zend_Auth');

            /*
             * Guardamos el UserId del usuario en la sesion de la Base de datos
             * para que puedan ser auditado.
             */
            $database->query("SELECT set_session('USER_ID', '{$authSpace->userId}');");
        }

        Zend_Registry::set('ZendDb',$database);
        Zend_Db_Table::setDefaultAdapter($database);
    }

    /**
     * Define las configuración necesaria para poder implementar
     * el Framework de AJAX llamado jQuery.
     */
    protected function _initJQuery() {
        $view = new Zend_View();
        $view->setEncoding('UTF-8');
        $view->doctype('XHTML1_STRICT');
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $view->addHelperPath('ZendX/JQuery/View/Helper/', 'ZendX_JQuery_View_Helper');
        $viewRenderer->setView($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
    }

    /**
     * Define la configuración del lenguaje, uno de los aspectos que se contempla
     * para esta configuración es la de permitir personalizar cada uno de
     * los mensajes de error que son mostrados al momento de validar un form.
     */
    protected function _initLangs() {
        $translate = new Zend_Translate('array', APPLICATION_PATH. '/languages/es.php', 'es_VE');
        Zend_Form::setDefaultTranslator($translate);
    }

    /**
     * Define la configuración para la Paginación.
     */
    protected function _initPagination() {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->addScriptPath(APPLICATION_PATH . '/templates/');
        
        Zend_Paginator::setDefaultScrollingStyle('Sliding');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    /**
     * Define la configuración regional.
     */
    protected function _initLocale() {
        Zend_Locale::setDefault('es_VE');

        Zend_Locale_Format::setOptions(array('locale'      => 'es_VE',
                                             'date_format' => 'dd/MM/YYYY'));
    }

    /**
     * Define la configuración para el Log's.
     */
    protected function _initLogger() {
        $logger = new Zend_Log();
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../logs/application.log');
        $logger->addWriter($writer);
        
        Zend_Registry::set('logger', $logger);
    }

    /**
     * Precarga todos los parametros del archivo de configuración para ser accedidos
     * mediante el registro con Zend_Registry.
     *
     * @return Zend_Config
     */
    protected function _initConfig()
    {
        $config = new Zend_Config($this->getOptions());
        Zend_Registry::set('config', $config);
        return $config;
    }

    /**
     * Define la estructura de archivos y directorios para adminitir de otra forma
     * con nuestros propios requerimientos el MVC, con el fin de contemplar varias
     * aplicaciones dentro de un mismo proyecto.
     */
    protected function _initApplication() {
        $this->bootstrap('frontController');
        Zend_Layout::startMvc(array('layoutPath' => '../application/modules/default/views/layouts'));

        $this->frontController->addModuleDirectory(APPLICATION_PATH . '/modules/');
    }
}