<?php

class Reports_ListasverdesController extends Zend_Controller_Action {    

    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Une_Cde_Reportes_ListaVerde');
        Zend_Loader::loadClass('Une_Filtros');

        $this->filtros         = new Une_Filtros();
        $this->seguridad       = new Models_DbTable_UsuariosGrupos();
        $this->RecordAcademico = new Models_DbTable_Recordsacademicos();
        $this->listaVerde      = new Une_Cde_Reportes_ListaVerde();

        //Reportes Masivos
        $this->reportes        = new CmcBytes_GenerarReportes();

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
        $this->listasblancas = new Zend_Session_Namespace('ListasBlancas');
        

		$this->_params['filters'] = $this->filtros->getParams();
      $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
      $this->ci = (int) $this->authSpace->userId;
      

		$this->filtros->setDisplay(true, true, true, true, true, true, false, true, false);
		$this->filtros->setDisabled(false, true, true, true, true, true, true, true, true);
		$this->filtros->setRecursive(true, true, true, true, true, true, false, true, false);

		$this->SwapBytes_Crud_Action->setDisplay(true, true);
		$this->SwapBytes_Crud_Action->setEnable(true, true);
		$this->SwapBytes_Crud_Search->setDisplay(false);
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if(!$this->seguridad->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

    public function indexAction() {
        $this->view->title      = "Reportes \ Listas de estudiantes \ Verdes y Blancas";
        $this->view->filters    = $this->filtros;
        $this->view->module     = $this->Request->getModuleName();
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
        $this->filtros->getAction(array('periodo', 'sede'));
	}

    public function pensumAction() {
        $this->filtros->getAction(array('periodo', 'sede', 'escuela'));
    }

    public function semestreAction() {
        $this->filtros->getAction(array('periodo', 'sede', 'escuela', 'pensum'));
    }

    public function materiaAction() {
        $this->filtros->getAction(array('periodo', 'sede', 'escuela', 'pensum', 'semestre'));
    }

    public function seccionAction() {
        $this->filtros->getAction(array('periodo', 'sede', 'escuela', 'pensum', 'semestre', 'seccion'));
    }

    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json           = array();
            $HtmlObjectName = 'pk_usuario';
            $ra_data = $this->RecordAcademico->getEstudiantes($this->_params['filters']);

            // Definimos las propiedades de la tabla.
            $ra_property_table = array('class'  => 'tableData',
                                       'width'  => '600px',
                                       'column' => 'disponible');

            $ra_property_column = array( array('column'   => 'pk_asignatura',
                                               'primary'  => true,
                                               'hide'     => true),
                                         array('name'     => '#',
                                               'width'    => '20px',
                                               'function' => 'rownum',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'C.I.',
                                               'column'   => 'ci',
                                               'width'    => '60px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'Apellidos',
                                               'column'   => 'apellido',
                                               'width'    => '200px',
                                               'rows'     => array('style' => 'text-align:left')),
                                         array('name'     => 'Nombres',
                                               'column'   => 'nombre',
                                               'width'    => '200px',
                                               'rows'     => array('style' => 'text-align:left')),
                                         array('name'     => array('control' => array('tag'        => 'input',
                                                                                      'type'       => 'checkbox',
                                                                                      'checked'    => 'checked',
                                                                                      'name'       => 'chkSelectDeselect')),
                                               'width'    => '30px',
                                               'column'   => $HtmlObjectName,
                                               'rows'     => array('style'      => 'text-align:center'),
                                               'control'  => array('tag'        => 'input',
                                                                   'type'       => 'checkbox',
                                                                   'checked'    => 'checked',
                                                                   'name'       => 'chkEstudiante',
                                                                   'value'      => "##{$HtmlObjectName}##")),);

            // Generamos la lista.
            $HTML   = $this->SwapBytes_Crud_List->fill($ra_property_table, $ra_data, $ra_property_column);
            
            $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
            $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkEstudiante');
            $json[] = "$('#btnDescargarListaBlanca').button({ disabled: false })";
            $json[] = "$('#btnDescargar').button({ disabled: false })";
            
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function descargarAction() {
        $Data = $this->_getParam('data');
        $Data = $this->SwapBytes_Uri->queryToArray($Data);

        $this->listaVerde->generar($Data);
    }

    public function generarlistasblancasAction() {

        $this->SwapBytes_Ajax->setHeader();

        $Data = $this->_getParam('data');
        $Data = $this->SwapBytes_Uri->queryToArray($Data);
        $config = Zend_Registry::get('config');

        $Periodo     = $Data['selPeriodo'];
        $Sede        = $Data['selSede'];
        $Escuela     = $Data['selEscuela'];
        $Semestre    = $Data['selSemestre'];
        $Materia     = $Data['selMateria'];
        $Seccion     = $Data['selSeccion'];
        $Pensum      = $Data['selPensum'];
        $Estudiantes = $Data['chkEstudiante'];
        $TipoReporte = $Data['tipoListaVerde'];
		$Data['selPensum'] = $this->RecordAcademico->getCodigopropietario($Pensum);
        $dbname = $config->database->params->dbname;
        $dbuser = $config->database->params->username;
        $dbpass = $config->database->params->password;
        $dbhost = $config->database->params->host;

        if($TipoReporte < 2){
           if(!isset($Semestre))
              $Semestre = 873;

           $filename    = "listablanca{$Periodo}_{$Sede}_{$Escuela}_{$Semestre}";
           $report = APPLICATION_PATH . '/modules/reports/templates/Listadoestudiantes/listadoestudiantesmasivo.jasper';
           $subreport = APPLICATION_PATH . '/modules/reports/templates/Listadoestudiantes/';
           $params      = "'Sede=integer:{$Sede}|Periodo=integer:{$Periodo}|Escuela=integer:{$Escuela}|Semestre=integer:{$Semestre}|Pensum=integer:{$Pensum}|SUBREPORT_DIR=string:{$subreport}'";
        }else if($TipoReporte == 2){
           $filename    = "listablanca{$Periodo}_{$Sede}_{$Escuela}_{$Semestre}_{$Materia}_{$Seccion}";
		     $report = APPLICATION_PATH . '/modules/reports/templates/Listadoestudiantes/ListadoEstudiantesSeleccionBienPensum.jasper';
           if(!is_array($Data['chkEstudiante'])){
              $Estudiantes = $Data['chkEstudiante'];
           }else{
              $Estudiantes = implode(',', $Data['chkEstudiante']);
           }
           $params      = "'Sede=string:{$Data['selSede']}|Periodo=string:{$Data['selPeriodo']}|Escuela=string:{$Data['selEscuela']}|Pensum=string:{$Data['selPensum']}|Semestre=string:{$Data['selSemestre']}|Materia=string:{$Data['selMateria']}|Seccion=string:{$Data['selSeccion']}|CIs=string:{$Estudiantes}'";
        }

        $filetype    = strtolower($Params['rdbFormat']);
        $filetype    = 'pdf';
        if(!is_array($Params['chkEstudiante'])){
           $Estudiantes = $Params['chkEstudiante'];
        }else{
           $Estudiantes = implode(',', $Params['chkEstudiante']);
        }
        $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";

        // $outstream = $this->reportes->descargarConNombre($filename);
        // if (empty($outstream)){
        // $this->reportes->preparar($this->ci, $report, $params, $filetype, $filename, false);
        // }

        // if($TipoReporte < 2){
           $this->reportes->preparar($this->ci, $report, $params, $filetype, $filename, false);
           $html = "<b> Su reporte ha sido generado </br> <center><button id=\"btnDescargarLB\"> Descargar </button></center>";
           $json[] = $this->SwapBytes_Jquery->setHtml("Cargando", $html);
           $json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter("Cargando");
           $json[] = $this->SwapBytes_Jquery_Ui_Form->open("Cargando");
           $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle("Cargando", "Listo");
           $json[] = $this->SwapBytes_Jquery_Ui_Form->setDraggable("Cargando", false);
           $json[] = $this->SwapBytes_Jquery_Ui_Form->setAlignWidthLeft("Cargando", "120px");
           echo Zend_Json::encode($json);
           
        // } else if($TipoReporte == 2){
        //    Zend_Layout::getMvcInstance()->disableLayout();
        //    Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        //    Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
        //    Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
        //    echo $this->reportes->preparar($this->ci, $report, $params, $filetype, $filename);
        //    // echo $this->reportes->descargar($this->ci);
        //    // echo base64_decode($outstream);
        // }
    }

    public function descargarlistasblancasAction() {
        // $this->SwapBytes_Ajax->setHeader();
        $filetype = $this->reportes->getTipo_Reporte($this->ci);
        $filename = $this->reportes->getNombre_Reporte($this->ci);
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
        // echo $this->reportes->descargar();
        // if (!empty($outstream)){
        //    echo base64_decode($outstream);
        // }else{
        echo base64_decode($this->reportes->descargar($this->ci));
        // }
        // echo base64_decode($this->listasblancas->resultadolistablanca);
    }


    public function generandoAction(){
         $this->SwapBytes_Ajax->setHeader();
         
         $Data = $this->_getParam('data');
         $Listo = $this->_getParam('listo');
         $Data = $this->SwapBytes_Uri->queryToArray($Data);
         $TipoReporte = $Data['tipoListaVerde'];

         // if($TipoReporte < 2){
         $html = "<b> Por favor espere mientras su reporte es generado <br> &nbsp; <br><img src=\"../images/loading.gif\" alt=\"no la conegui\">";
         $json[] = $this->SwapBytes_Jquery->setHtml("Cargando", $html);
         $json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter("Cargando");
         $json[] = $this->SwapBytes_Jquery_Ui_Form->open("Cargando");
         $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle("Cargando", "Cargando");
         $json[] = $this->SwapBytes_Jquery_Ui_Form->setDraggable("Cargando", false);
         $json[] = $this->SwapBytes_Jquery_Ui_Form->setAlignWidthLeft("Cargando", "120px");

         // } else if($TipoReporte == 2){
         //    $json[] = "window.location.href = urlAjax + \"descargarlistasblancas/\"";
         // }

         if ($Listo == 1){
            $json[] = "window.location.href = urlAjax + \"descargarlistasblancas/\"";
         }else{
            $json[] = "$.getJSON(urlAjax + \"generarlistasblancas/data/\" + $(':input').serialize(), function (d) { executeCmdsFromJSON(d);})";
         }

         echo Zend_Json::encode($json);
    }
}
