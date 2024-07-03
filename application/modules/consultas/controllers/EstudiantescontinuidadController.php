<?php
class Consultas_EstudiantescontinuidadController extends Zend_Controller_Action {

  private $_title = 'Consultas \ Estudiantes en Continuidad Servicio Comunitario';

  public function init() {
  /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Inscripcionespasantias');
        Zend_Loader::loadClass('Models_DbTable_Asignacionesproyectos');
        Zend_Loader::loadClass('Models_DbTable_Contactos');
        Zend_Loader::loadClass('Models_DbView_Sedes');
        Zend_Loader::loadClass('Models_DbView_Escuelas');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Forms_Inscripcionproyecto');

        $this->inscripcionespasantias   = new Models_DbTable_Inscripcionespasantias();
        $this->asignacionesproyectos    = new Models_DbTable_Asignacionesproyectos();
        $this->contactos                = new Models_DbTable_Contactos();
        $this->sedes                    = new Models_DbView_Sedes();
        $this->escuelas                 = new Models_DbView_Escuelas();
        $this->usuariosgrupos           = new Models_DbTable_UsuariosGrupos();
        $this->filtros                  = new Une_Filtros();
        $this->CmcBytes_Filtros         = new CmcBytes_Filtros();
        
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
                
        
        // key -> nombre del filtro
        // values -> array(tabla o vista, where,columnas, ordenamiento)        
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
                              'Escuela' => Array(Array('tbl_estructurasescuelas ee',
                                                       'vw_escuelas es'),
                                                 Array('ee.fk_atributo = es.pk_atributo',
                                                       'ee.fk_estructura = ##Sede##'),//'fk_estructura = 7','fk_estructura = ##sede##',
                                                 Array('ee.fk_atributo',
                                                       'es.escuela'),
                                                 'ASC'));
        //$x = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));
        // * Obtiene los parametros de los filtros y del modal.
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
        $this->_params['modal'] = $this->SwapBytes_Crud_Form->getParams();
        // * Configuramos los filtros.
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');

        $this->SwapBytes_Crud_Action->setDisplay(true, false, false);
        $this->SwapBytes_Crud_Action->setEnable(true, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(false);
        $this->SwapBytes_Crud_Action->addCustum("<button class='button-material ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' id='btnPrinter' disabled>Imprimir</button>");
        $this->SwapBytes_Crud_Action->addJavaScript("$('#btnPrinter').click(function(){
                                                                        window.print();
                                                                      });");         
  }

  function preDispatch() {
    if (!Zend_Auth::getInstance()->hasIdentity()) {
        $this->_helper->redirector('index', 'login', 'default');
    }

    if (!$this->usuariosgrupos->haveAccessToModule()) {
        $this->_helper->redirector('accesserror', 'profile', 'default');
    }
  }

  public function indexAction() {
    $this->view->title                      = $this->_title;
    $this->view->filters                    = $this->filtros;
    $this->view->SwapBytes_Jquery           = $this->SwapBytes_Jquery;
    $this->view->SwapBytes_Crud_Action      = $this->SwapBytes_Crud_Action;
    $this->view->SwapBytes_Crud_Form        = $this->SwapBytes_Crud_Form;
    $this->view->SwapBytes_Crud_Search      = $this->SwapBytes_Crud_Search;
    $this->view->SwapBytes_Ajax             = new SwapBytes_Ajax();
    $this->view->SwapBytes_Ajax->setView($this->view);
    $this->view->button_span                = 2;
    $this->view->SwapBytes_Jquery_Ui_Form   = new SwapBytes_Jquery_Ui_Form();     
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
    

        
  public function listAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $json = array();

      $rows = $this->inscripcionespasantias->getEstudiantesContinuidad($this->_params['filters']['Sede'], $this->_params['filters']['Periodo'], $this->_params['filters']['Escuela']);

      if (!empty($rows)) {
        $table = array('class' => 'tableData',
                                 'width' => '1175px');
        $columns = array(array('column' => 'pk_inscripcionpasantia',
            'primary' => true,
            'hide' => true),
         array('name'     => '#',
               'width'    => '20px',
               'function' => 'rownum',
               'rows'     => array('style' => 'text-align:center')),
          array('name' => 'Cedula',
            'width' => '65px',
            'column' => 'cedula',
            'rows' => array('style' => 'text-align:center')),
          array('name' => 'Estudiante',
            'width' => '300px',
            'column' => 'estudiante',
            'rows' => array('style' => 'text-align:center')),
          array('name' => 'Correo',
            'width' => '100px',
            'column' => 'correo',
            'rows' => array('style' => 'text-align:center')),
          array('name' => 'Telefono',
            'width' => '80px',
            'column' => 'telefono',
            'rows' => array('style' => 'text-align:center')),
          array('name' => 'Celular',
            'width' => '80px',
            'column' => 'celular',
            'rows' => array('style' => 'text-align:center')),
          array('name' => 'Proyecto',
                'width' => '300px',
                'column' => 'proyecto',
                'rows' => array('style' => 'text-align:center')));

          $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
          $json[] = $this->SwapBytes_Jquery->setAttr('rdbFormatXLS', 'disabled', 'false');
          $json[] = $this->SwapBytes_Jquery->setAttr('rdbFormatPDF', 'disabled', 'false'); 
          $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnDescargar', false);
          $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnPrinter', false); 
      } 
      else {
        $HTML   = $this->SwapBytes_Html_Message->alert("No existen Estudiantes Inscritos en Proyectos en este periodo.");
        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnAdd', false);
        $json[] = $this->SwapBytes_Jquery->setAttr('rdbFormatXLS', 'disabled', 'true');
        $json[] = $this->SwapBytes_Jquery->setAttr('rdbFormatPDF', 'disabled', 'true'); 
        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnDescargar', true);
        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnPrinter', true); 
      }
      $json[] = $this->SwapBytes_Jquery->setHtml('tblServicioComunitario', $HTML);         
      $this->getResponse()->setBody(Zend_Json::encode($json));
    }
  }   

  public function descargarAction() {
    /*Arreglar Query*/
    $Params  = $this->_getParam('data');
    $Params  = $this->SwapBytes_Uri->queryToArray($Params);
    $sede    = $this->sedes->getSedeName($Params['Sede']);
    $escuela = $this->escuelas->getEscuelaName($Params['Escuela']);
    $config = Zend_Registry::get('config');
    $dbname = $config->database->params->dbname;
    $dbuser = $config->database->params->username;
    $dbpass = $config->database->params->password;
    $dbhost = $config->database->params->host;
    $report = APPLICATION_PATH . '/modules/reports/templates/Serviciocomunitario/EstudianteContinuidad.jrxml'; 
    $filename    = 'Servicio Comunitario';
    $filetype    = strtolower($Params['rdbFormat']);
    $params      = "'Sede=string:{$Params['Sede']}|Periodo=string:{$Params['Periodo']}|Escuela=string:{$Params['Escuela']}|nombreSede=string:{$sede}|nombreEscuela=string:{$escuela}'";
    $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmd.jar -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
    Zend_Layout::getMvcInstance()->disableLayout();
    Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
    Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
    $outstream = exec($cmd);
    echo base64_decode($outstream);  
  }    

}
?>