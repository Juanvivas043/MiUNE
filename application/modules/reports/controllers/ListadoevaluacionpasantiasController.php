<?php

class Reports_ListadoevaluacionpasantiasController extends Zend_Controller_Action{
 
     public function init(){
         Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Models_DbTable_Inscripcionespasantias');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Une_Filtros');

        $this->filtros         = new Une_Filtros();
        $this->seguridad       = new Models_DbTable_UsuariosGrupos();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->sedes           = new Models_DbTable_Estructuras();
        $this->escuelas        = new Models_DbTable_EstructurasEscuelas();
        $this->inscripcionespasantias   = new Models_DbTable_Inscripcionespasantias();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->_params['filters'] = $this->filtros->getParams();

    $this->filtros->setDisplay(true, true, true, false, false, false, false, false, false);
    $this->filtros->setDisabled(false, true, true, true, true, true, true, true, true);
    $this->filtros->setRecursive(true, true, true, false, false, false, false, false, false);


    $this->SwapBytes_Crud_Action->setDisplay(true, true);
    $this->SwapBytes_Crud_Action->setEnable(true, true);
    $this->SwapBytes_Crud_Search->setDisplay(false);
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    function preDispatch() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if (!$this->seguridad->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
        }
     }

    public function indexAction() {
        $this->view->title = "Reportes \ Listado: Evaluación Pasantías Laborales";
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

    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json = array();

            $queryString = $this->_getParam('filters');
            $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);
            $periodo = $queryArray['selPeriodo'];
            $escuela = $queryArray['selEscuela'];
            $sede = $queryArray['selSede'];
            $cuestionario = $periodo."-CPLE";
            $academico = $periodo."-TAPLE";
            $empresarial = $periodo."-TEPLE";
           
            $rows = $this->inscripcionespasantias->getEvaluacion($periodo,$escuela,$sede);

            //var_dump($rows);die();

            // Definimos las propiedades de la tabla.
            $ra_property_table = array('class'  => 'tableData',
                                       'width'  => '1100px',
                                       'column' => 'disponible');

            $ra_property_column = array( array('name'     => '#',
                                               'width'    => '20px',
                                               'function' => 'rownum',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'C.I.',
                                               'column'   => 'cedula',
                                               'width'    => '70px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'Estudiante',
                                               'column'   => 'estudiante',
                                               'width'    => '300px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'Empresa',
                                               'column'   => 'empresa',
                                               'width'    => '200px',
                                               'rows'     => array('style' => 'text-align:center')),      
                                         array('name'     => 'Dirección',
                                               'column'   => 'direccion',
                                               'width'    => '250px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'Cédula Tutor Empresarial',
                                               'column'   => 'cedulate',
                                               'width'    => '250px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'Tutor Empresarial',
                                               'column'   => 'tutor_ins',
                                               'width'    => '250px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'Cédula Tutor Académico',
                                               'column'   => 'cedulata',
                                               'width'    => '250px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'Tutor Académico',
                                               'column'   => 'tutor_a',
                                               'width'    => '200px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'Nota Cuestionario',
                                               'column'   => 'nota',
                                               'width'    => '70px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'Evaluación Académica',
                                               'column'   => 'evacademica',
                                               'width'    => '70px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'Evaluación Empresarial',
                                               'column'   => 'evempresarial',
                                               'width'    => '70px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'Total',
                                               'column'   => 'total',
                                               'width'    => '70px',
                                               'rows'     => array('style' => 'text-align:center')));

            // Generamos la lista.
            $HTML   = $this->SwapBytes_Crud_List->fill($ra_property_table, $rows, $ra_property_column);

            $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
            $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkEstudiante');
            $json[] = $this->SwapBytes_Jquery->setAttr('rdbFormatXLS', 'disabled', 'false');
            $json[] = $this->SwapBytes_Jquery->setAttr('rdbFormatPDF', 'disabled', 'false');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnDescargar', false);

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function descargarAction() {
        $Params = $this->_getParam('data');
        $Params = $this->SwapBytes_Uri->queryToArray($Params);
        $periodo = $Params['selPeriodo'];
        $escuela = $Params['selEscuela'];
        $sede = $Params['selSede'];
        
        $config = Zend_Registry::get('config');
        

    $dbname = $config->database->params->dbname;
    $dbuser = $config->database->params->username;
    $dbpass = $config->database->params->password;
    $dbhost = $config->database->params->host;
    $report = APPLICATION_PATH . '/modules/reports/templates/Listadoevaluacionpasantias/listadoevaluacionpasantias.jasper';
    $filename    = 'listadoevaluacionpasantias';
    $filetype    = 'xls';
    
    $params      = "'sede=string:{$sede}|periodo=string:{$periodo}|escuela=string:{$escuela}'";
    //var_dump($params);die();
    $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D pgsql -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
    //echo $cmd;die();           
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
    
        $outstream = exec($cmd);
        echo base64_decode($outstream);
                
    }
}
