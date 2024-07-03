<?php

//class Transactions_HorariosController extends SwapBytes_Controller_Action {
class Reports_OcupacionaulaController extends Zend_Controller_Action {

  private $Title = 'Reportes \ Ocupación Aulas';

    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Models_DbTable_Asignaturas');
        Zend_Loader::loadClass('Models_DbTable_Materiasestados');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbView_Semestres');
        Zend_Loader::loadClass('Models_DbTable_Horarios');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->Horarios = new Models_DbTable_Horarios();
        $this->asignaciones = new Models_DbTable_Asignaciones();
        $this->inscripciones = new Models_DbTable_Inscripciones();
        $this->usuarios = new Models_DbTable_Usuarios();
        $this->asignaturas = new Models_DbTable_Asignaturas();
        $this->materiasestados = new Models_DbTable_Materiasestados();
        $this->grupo = new Models_DbTable_UsuariosGrupos();
        $this->periodos = new Models_DbTable_Periodos();
        $this->filtros = new Une_Filtros();
        $this->vw_semestres = new Models_DbView_Semestres();
        $this->SwapBytes_Date = new SwapBytes_Date();
        $this->SwapBytes_Uri = new SwapBytes_Uri();
        $this->SwapBytes_Form = new SwapBytes_Form();
        $this->SwapBytes_Form_Agregar = new SwapBytes_Form();
        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search = new SwapBytes_Crud_Search();
        $this->SwapBytes_Html = new SwapBytes_Html();
        $this->SwapBytes_Html_Message = new SwapBytes_Html_Message();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask = new SwapBytes_Jquery_Mask();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();
        //var_dump($this->filtros);die;
        $this->SwapBytes_Ajax->setView($this->view);

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
              
        // $this->filtros->setDisplay(true, true, true, false, false, false, false, false, false);
        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);

        $this->SwapBytes_Crud_Action->setDisplay(true, true, false);
        $this->SwapBytes_Crud_Action->setEnable(true, true, false);
        //$this->SwapBytes_Crud_Action->addCustum('&nbsp;<select id="selEstado" name="selEstado" style="width:100px"></select>');
        
        $this->_params['modal'] = $this->SwapBytes_Crud_Form->getParams();
        
        $this->logger = Zend_Registry::get('logger');
         
        $this->tablas = Array(
                              'Periodo'  => Array('tbl_periodos',
                                                  null,
                                                  Array('pk_periodo',
                                                        'lpad(pk_periodo::text, 4, \'0\') || \', \' || to_char(fechainicio, \'MM-yyyy\') || \' / \' ||  to_char(fechafin, \'MM-yyyy\')'),
                                                  'DESC'),
                              'Sede'    => Array('vw_sedes',
                                                 null     ,
                                                 Array('pk_estructura',
                                                       'nombre'),
                                                 'DESC'),

                              'Dia'    => Array('vw_dias',
                                                 null     ,
                                                 Array('id',
                                                       'dia'),
                                                 'id ASC'),
                              'Horario' => Array('tbl_horarios',null, Array('pk_horario','horainicio'),'ASC')
                                );
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));        $this->SwapBytes_Form_Agregar->set($this->view->form_agregar);
        $this->SwapBytes_Crud_Search->setDisplay(false); //quita la barra de busqueda
       //var_dump($this->tablas);die;
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

    /**
     * Crea la estructura base de la pagina principal.
     */
    public function indexAction() {
        $this->view->filters = $this->filtros;
        $this->view->title = $this->Title;
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
        $this->view->SwapBytes_Ajax = $this->SwapBytes_Ajax;
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Jquery_Ui_Form = $this->SwapBytes_Jquery_Ui_Form;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
    }

    public function filterAction(){
        $this->SwapBytes_Ajax->setHeader();
        $select = $this->_getParam('select');
        $values = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));
        if(!$select || !$values){
            $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,null,1,null);
        }else{
            $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,$values,null,$select);
        }
        $this->getResponse()->setBody(Zend_Json::encode($json));
    }
         
    public function sedeAction() {
        $this->filtros->getAction();
    }
   
  public function listAction() {
  	if ($this->_request->isXmlHttpRequest()) {
  	  $this->SwapBytes_Ajax->setHeader();
      $queryString  = $this->_getParam('filters');
      $queryArray   = $this->SwapBytes_Uri->queryToArray($queryString);
      $periodo      = $queryArray['Periodo'];
      $dia          = $queryArray['Dia'];
      $horario      = $queryArray['Horario'];
      $estructura   = $queryArray['Sede'];
  	  $rows         =  $this->Horarios->getOcupacionAulas($periodo,$estructura,$dia,$horario);
  	  if (isset($rows) && count($rows) > 0) {
  		$table = array('class' => 'tableData',
  			array('column' => 'dia',
  				'colors' => array('odd' => 'A9D0F5',
  					'even' => 'EFF5FB')));

  		$columns = array(array('name' => 'Aula',
  				'width' => '200px',
  				'column' => 'aula',
  				'rows' => array('style' => 'text-align:center')),
  			array('name' => 'Curso',
  				'width' => '120px',
  				'column' => 'curso',
  				'rows' => array('style' => 'text-align:center')),
  			array('name' => 'Profesor',
  				'width' => '200px',
  				'column' => 'profesor',
  				'rows' => array('style' => 'text-align:center')),
  			array('name' => 'Asignatura',
  				'width' => '200px',
  				'column' => 'asignatura',
  				'rows' => array('style' => 'text-align:center')));

  		$HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
  		$json[] = $this->SwapBytes_Jquery->setHtml('tblData', $HTML);
                  $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnDescargar', false);
                  $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnDescargarTodo', false);
  		} else {
  		$HTML = $this->SwapBytes_Html_Message->alert("No existen aulas ocupadas");

  		$json[] = $this->SwapBytes_Jquery->setHtml('tblHorarios', $HTML);
  	  }

  	  $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnAdd', false);

  	  $this->getResponse()->setBody(Zend_Json::encode($json));
  	}
  }
  
  public function descargarAction() {
      $this->SwapBytes_Ajax->setHeader();
      $filtros=$this->_getParam('filters');
      $queryArray=$this->SwapBytes_Uri->queryToArray($filtros);
      //var_dump($queryArray);die;
      $config = Zend_Registry::get('config');
  		$dbname = $config->database->params->dbname;
  		$dbuser = $config->database->params->username;
  		$dbpass = $config->database->params->password;
  		$dbhost = $config->database->params->host;
  		$report = APPLICATION_PATH . '/modules/reports/templates/Ocupacionaula/ocupacionaula.jasper';
                  $filename    = 'ocupacionaula';
  		$filetype    = 'pdf';
  		$params      = "'periodo=string:{$queryArray['Periodo']}|estructura=string:{$queryArray['Sede']}|dia=string:{$queryArray['Dia']}|horario=string:{$queryArray['Horario']}'";
  		$cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D pgsql -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                  //echo $cmd;
      Zend_Layout::getMvcInstance()->disableLayout();
      Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
      Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
      $outstream = exec($cmd);
      //echo $outstream;
      echo base64_decode($outstream);
  }

  public function descargartodoAction() {
      $this->SwapBytes_Ajax->setHeader();
      $filtros=$this->_getParam('filters');
      $queryArray=$this->SwapBytes_Uri->queryToArray($filtros);
      //var_dump($queryArray);die;
      $config = Zend_Registry::get('config');
  		$dbname = $config->database->params->dbname;
  		$dbuser = $config->database->params->username;
  		$dbpass = $config->database->params->password;
  		$dbhost = $config->database->params->host;
  		$report = APPLICATION_PATH . '/modules/reports/templates/Ocupacionaula/ocupacionaulamasivo.jasper';
                $subreport = APPLICATION_PATH . '/modules/reports/templates/Ocupacionaula/';
                $filename    = 'ocupacionaulamasivo';
		  $filetype    = 'pdf';
      $params      = "'periodo=string:{$queryArray['Periodo']}|estructura=string:{$queryArray['Sede']}|dia=string:{$queryArray['Dia']}|horario=string:{$queryArray['Horario']}|SUBREPORT_DIR=string:{$subreport}'";
      $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D pgsql -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
      //var_dump($cmd);die;
      Zend_Layout::getMvcInstance()->disableLayout();
      Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
      Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
      $outstream = exec($cmd);
      //echo $outstream;
      echo base64_decode($outstream);      
  }

}