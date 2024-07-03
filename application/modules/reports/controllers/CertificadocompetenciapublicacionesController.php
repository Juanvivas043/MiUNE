<?php

class Reports_CertificadocompetenciapublicacionesController extends Zend_Controller_Action {

     public function init() {
        Zend_Loader::loadClass('Models_DbTable_Certificadocompetencia');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');

        $this->Certificadocompentencia = new Models_DbTable_Certificadocompetencia();
        $this->filtros = new Une_Filtros();
        $this->atributos = new Models_DbTable_Atributos();
        $this->periodos = new Models_DbTable_Periodos();
        $this->sedes = new Models_DbTable_Estructuras();
        $this->escuelas = new Models_DbTable_EstructurasEscuelas();
        $this->grupo = new Models_DbTable_UsuariosGrupos();

        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri = new SwapBytes_Uri();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->_params['filters'] = $this->filtros->getParams();

        $this->filtros->setDisplay(true, true, true, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, true, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(true, true, true, false, false, false, false, false, false);
//		$this->filtros->setType('seccion', FILTER_TYPE_SECCION_PADRES);

        $this->SwapBytes_Crud_Action->setDisplay(true, true);
        $this->SwapBytes_Crud_Action->setEnable(true, true);
        $this->SwapBytes_Crud_Search->setDisplay(false);
        
        $this->logger = Zend_Registry::get('logger');
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
    }
    
    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    function preDispatch() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if (!$this->grupo->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

    public function indexAction() {
        $this->view->title = "Reportes \ Certificación de Competencia";
        $this->view->filters = $this->filtros;
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
    }
    
    public function periodoAction() {
        $this->filtros->getAction();
    }

    public function sedeAction() {
        $this->filtros->getAction(array('periodo'));
    }
    
    public function escuelaAction() {
        $this->filtros->getAction(array('periodo'));
    }
    
    public function descargarAction(){
        
            //$ci = $this->authSpace->userId;
            $pk = $this->_getParam('id');
          
            $ci_escuela = $this->Certificadocompentencia->getUsuarioPorDocumento($pk);
            
            $queryArray = array($ci_escuela[0]['cedula'],$ci_escuela[0]['escuela']);
            
            $config = Zend_Registry::get('config');

            $dbname = $config->database->params->dbname;
            $dbuser = $config->database->params->username;
            $dbpass = $config->database->params->password;
            $dbhost = $config->database->params->host;
            
            $ruta = '/modules/reports/templates/certificadocompetencia/';
            
            if($queryArray[1] == 11){
                $report = APPLICATION_PATH . $ruta . 'ReporteEscuela5.jasper';
                $imagen = APPLICATION_PATH . $ruta . 'Administracion.png';
                $filename    = 'ReporteEscuela5';
            }else if ($queryArray[1] == 12){
                $report = APPLICATION_PATH . $ruta . 'ReporteEscuela10.jasper';
                $imagen = APPLICATION_PATH . $ruta . 'Computacion.png';
                $filename    = 'ReporteEscuela10';
            }else if ($queryArray[1] == 13){
                $report = APPLICATION_PATH . $ruta . 'ReporteEscuela15.jasper';
                $imagen = APPLICATION_PATH . $ruta . 'Diseno.png';
                $filename    = 'ReporteEscuela15';
            }else if ($queryArray[1] == 14){
                $report = APPLICATION_PATH . $ruta . 'ReporteEscuela20.jasper';
                $imagen = APPLICATION_PATH . $ruta . 'Civil.png';
                $filename    = 'ReporteEscuela20';
            }else if ($queryArray[1] == 15){
                $report = APPLICATION_PATH . $ruta . 'ReporteEscuela25.jasper';
                $imagen = APPLICATION_PATH . $ruta . 'Electronica.png';
                $filename    = 'ReporteEscuela25';
            }else if ($queryArray[1] == 16){
                $report = APPLICATION_PATH . $ruta . 'ReporteEscuela30.jasper';
                $imagen = APPLICATION_PATH . $ruta . 'Turismo.png';
                $filename    = 'ReporteEscuela30';
            }
            $logo = APPLICATION_PATH .'/../public/images/logo_UNE_color.jpg';
            $Imagen2 =  APPLICATION_PATH . $ruta . 'ACTA';
            $filetype    = 'pdf';

            $params      = "'Imagen=string:{$imagen}|cedula=string:{$ci_escuela[0]['cedula']}|Imagen2=string:{$Imagen2}|logo=string:{$logo}'";
            $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D pgsql -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
      //echo $cmd;exit;

            Zend_Layout::getMvcInstance()->disableLayout();
            Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
            Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );

            $outstream = exec($cmd); //exec ejecuta un programa externo indicado por la ruta $cmd
//            echo $outstream;

            echo base64_decode($outstream);
            
            
            $this->updatearEstado($ci_escuela[0]['solicitud']);
            
    }
    
    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            //cambiar de 10669 a 14145
            $atributo_estado = $this->Certificadocompentencia->getPkAtributo();
            $json = array();
            
            $ra_data = $this->Certificadocompentencia->getSolicitudesCertificadoPorEstado($this->_params['filters']['periodo'], $this->_params['filters']['sede'], $this->_params['filters']['escuela'], $atributo_estado[0]['pk_atributo']);

            // Definimos las propiedades de la tabla.
            $ra_property_table = array('class' => 'tableData',
                                       'width' => '1100px',
                                       'column' => 'disponible');

            $ra_property_column = array(array('column'   => 'pkdocumento',
                                              'primary'  => true,
                                              'hide'     => true),
                                        array('name'     => '#',
                                              'width'    => '20px',
                                              'function' => 'rownum',
                                              'rows'     => array('style' => 'text-align:center')),
                                        array('name'     => 'C.I.',
                                              'column'   => 'ci',
                                              'width'    => '70px',
                                              'rows'     => array('style' => 'text-align:center')),
                                        array('name'     => 'Nombre',
                                              'column'   => 'nombre',
                                              'width'    => '300px',
                                              'rows'     => array('style' => 'text-align:center')),
                                        array('name'     => 'Apellido',
                                              'column'   => 'apellido',
                                              'width'    => '200px',
                                              'rows'     => array('style' => 'text-align:center')),
                                        array('name'     => 'Estado',
                                              'column'   => 'impreso',
                                              'width'    => '250px',
                                              'rows'     => array('style' => 'text-align:center')),
                                        );
            
            $other = array(array('actionName'  => 'imprimir',
                                 'action'      => 'myimprimir(##pk##)',
                                 'label'       => 'Imprimir'));
            
            $HTML = $this->SwapBytes_Crud_List->fill($ra_property_table, $ra_data, $ra_property_column, 'O', $other);   

            }
            
            $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
    }
    
    public function updatearEstado($usuarioGrupoSolicitud){
        $this->Certificadocompentencia->updateEstadoImpreso($usuarioGrupoSolicitud);
    }
    
}
