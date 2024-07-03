<?php

class Reports_MiscursosController extends Zend_Controller_Action {
    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Une_Filtros');

        $this->filtros         = new Une_Filtros();
        $this->seguridad       = new Models_DbTable_UsuariosGrupos();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->sedes           = new Models_DbTable_Estructuras();
        $this->escuelas        = new Models_DbTable_EstructurasEscuelas();
        $this->RecordAcademico = new Models_DbTable_Recordsacademicos();

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
        
        $this->cedulas                  = new Zend_Session_Namespace('Zend_Auth');
        
        $this->_params['filters'] = $this->filtros->getParams();
        
        $this->filtros->setParam('usuario',$this->cedulas->userId);
        
        // $this->filtros->setDisplay(true, true, true, false, true, true, false, true, false);
        $this->filtros->setDisplay(true, true, true, true, true, true, false, true, false);
        $this->filtros->setDisabled(false, false, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(true, true, true, true, true, true, false, true, false);
        //$this->filtros->setType('seccion', FILTER_TYPE_SECCION_PADRES);

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
        $this->view->title                  = "Reportes \ Mis Cursos";
        $this->view->filters                = $this->filtros;
        $this->view->module                 = $this->Request->getModuleName();
        $this->view->controller             = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery       = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax         = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Crud_Action  = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search  = $this->SwapBytes_Crud_Search;
    }

 public function periodoAction(){
    $this->filtros->getAction(array('usuario'), null);
  }
  public function sedeAction() {
    $this->filtros->getAction(array('usuario','periodo'), null);
  }

  public function escuelaAction() {
    $this->filtros->getAction(array('usuario','periodo', 'sede'), null);
  }

  public function pensumAction() {
    $this->filtros->getAction(array('usuario','periodo', 'sede', 'escuela'), null);
  }

  public function semestreAction() {
    $this->filtros->getAction(array('usuario','periodo', 'sede', 'escuela', 'pensum'), null);
  }

  public function materiaAction() {
    $this->filtros->getAction(array('usuario','periodo', 'sede', 'escuela', 'pensum', 'semestre'), null);
  }

  public function seccionAction() {
    $this->filtros->getAction(array('usuario','periodo', 'sede', 'escuela', 'pensum', 'semestre', 'materia'), null);
  }


    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json = array();
            $HtmlObjectName = 'pk_usuario';
            $ra_data = $this->RecordAcademico->getEstudiantes($this->_params['filters'],true);
            // Definimos las propiedades de la tabla.
            $ra_property_table = array('class'  => 'tableData',
                                       'width'  => '650px',
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
                                               'width'    => '225px',
                                               'rows'     => array('style' => 'text-align:left')),
                                         array('name'     => 'Nombres',
                                               'column'   => 'nombre',
                                               'width'    => '225px',
                                               'rows'     => array('style' => 'text-align:left')),
                                         array('name'     => 'Correo',
                                               'column'   => 'correo',
                                               'width'    => '225px',
                                               'rows'     => array('style' => 'text-align:left')),
					array(
                                            'name'     => 'Telefono',
                                            'column'   => 'telefono',
                                            'width'    => '225px',
                                            'rows'     => array('style' => 'text-align:left')
                                          ),
                                         array('name'     => 'Estado',
                                               'column'   => 'estadoe',
                                               'width'    => '225px',
                                               'rows'     => array('style' => 'text-align:center')),
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
            $json[] = $this->SwapBytes_Jquery->setAttr('rdbFormatXLS', 'disabled', 'false');
            $json[] = $this->SwapBytes_Jquery->setAttr('rdbFormatPDF', 'disabled', 'false');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnDescargar', false);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function descargarAction() {
        $Params = $this->_getParam('data');
        $Params = $this->SwapBytes_Uri->queryToArray($Params);
        $config = Zend_Registry::get('config');

        $pensum = $this->RecordAcademico->getPensumCodigoPropietario($Params['selPensum']);

        $dbname = $config->database->params->dbname;
        $dbuser = $config->database->params->username;
        $dbpass = $config->database->params->password;
        $dbhost = $config->database->params->host;
        $report = APPLICATION_PATH . '/modules/reports/templates/Listadoestudiantes/ListadoEstudiantesSeleccionBienPensum.jrxml'; 
        $filename    = 'listablanca';
        $filetype    = strtolower($Params['rdbFormat']);
      if(!is_array($Params['chkEstudiante'])){
         $Estudiantes = $Params['chkEstudiante'];
      }else{
         $Estudiantes = implode(',', $Params['chkEstudiante']);
      }
    $params      = "'Sede=string:{$Params['selSede']}|Periodo=string:{$Params['selPeriodo']}|Escuela=string:{$Params['selEscuela']}|Semestre=string:{$Params['selSemestre']}|Materia=string:{$Params['selMateria']}|Seccion=string:{$Params['selSeccion']}|CIs=string:{$Estudiantes}|Pensum=string:{$pensum[0]['codigopropietario']}'";
    $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmd.jar -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
      
        $outstream = exec($cmd);
        echo base64_decode($outstream);  
    }
}
