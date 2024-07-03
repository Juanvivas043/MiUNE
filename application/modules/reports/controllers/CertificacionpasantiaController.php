<?php

class Reports_CertificacionPasantiaController extends Zend_Controller_Action {

	private $Title = 'Reportes \ Certificacion  de Pasantias';
  private $FormTitle_Detalle = 'Ver informacion de Pasantias';
    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGruposSolicitudes');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbView_Calendarios');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Materiasaretirar');
        Zend_Loader::loadClass('Forms_Solicitudretiromateria');
        Zend_Loader::loadClass('Forms_Pasantia');
        Zend_Loader::loadClass('Models_DbTable_Pasantes');
        Zend_Loader::loadClass('Models_DbTable_Tmpcertificacionpasantia');
        
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->ugs             = new Models_DbTable_UsuariosGruposSolicitudes();
        $this->estudiante      = new Models_DbTable_Usuarios();
        $this->recordacademico = new Models_DbTable_Recordsacademicos();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->calendarios     = new Models_DbView_Calendarios();
        $this->inscripciones   = new Models_DbTable_Inscripciones();
        $this->mar   = new Models_DbTable_Materiasaretirar();
        $this->Pasantes         = new Models_DbTable_Pasantes();
        $this->certificacion = new Models_DbTable_Tmpcertificacionpasantia();
        
        $this->filtros         = new Une_Filtros();


        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Form           = new SwapBytes_Form();

        $this->CmcBytes_Profit = new CmcBytes_Profit();
        $this->CmcBytes_Redirect = new CmcBytes_Redirect();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();

        
        $this->view->form = new Forms_Pasantia();

        $this->logger = Zend_Registry::get('logger');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');

        
        $this->SwapBytes_Form->set($this->view->form);
        $this->view->form = $this->SwapBytes_Form->get();
        //filtro
        //$this->filtros->setDisplay(true, false, false, false, false, false, false, false, false);
        //$this->filtros->setDisabled(false, false, true, true, true, true, true, true, true);
        //$this->filtros->setRecursive(true, false, false, false, false, false, false, false, false);

        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, true, false);
        $this->SwapBytes_Crud_Action->setEnable(true, true, false);
        $this->SwapBytes_Crud_Search->setDisplay(true);

        

        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
        $this->_params['redirect'] = $this->redirect_session->params; 


        $this->tablas = Array(
                              'Periodo'  => Array('tbl_periodos', 
                                                  null,
                                                  Array('pk_periodo',
                                                        'lpad(pk_periodo::text, 4, \'0\') || \', \' || to_char(fechainicio, \'MM-yyyy\') || \' / \' ||  to_char(fechafin, \'MM-yyyy\')'),
                                                  'DESC'));

        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
    }


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
        $this->view->SwapBytes_Ajax->setView($this->view);
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


    /**
     * Accion que llena el objeto HTML de tipo SELECT con los datos del "sede"
     * y es usado como filtro de la lista.
     */
    public function sedeAction() {
        $this->filtros->getAction();
    }

    /**
     * Accion que llena el objeto HTML de tipo SELECT con los datos del "escuela"
     * y es usado como filtro de la lista.
     */
    public function escuelaAction() {
        $this->filtros->getAction();
    }

    


    public function listAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $queryString = $this->_getParam('filters');
            $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);

            
            $pageNumber  = $this->_getParam('page', 1);
            $searchData  = $queryArray['txtBuscar'];
            $itemPerPage = 10;
            $pageRange   = 10;
            $json   = array();

            $this->Pasantes->setSearch($searchData);

            $paginatorCount = $this->Pasantes->getPasantesCertificacionCount($queryArray['Periodo']);

            $rows = $this->Pasantes->getPasantesCertificacion($queryArray['Periodo'],$itemPerPage,$pageNumber);

            if(isset($rows) && count($rows) > 0) {

                    $table = array('class' => 'tableData',
                           'width' => '600px');

                    $columns = array(array('column'  => 'pk_usuario',
                                           'primary' => true,
                                           'hide'    => true),
                                     
                                     array('name'    => 'C.I.',
                                               'width'   => '70px',
                                               'column'  => 'pk_usuario',
                                               'rows'    => array('style' => 'text-align:center')),
                                     array('name'    => 'Apellido',
                                           'width'   => '200px',
                                           'rows'    => array('style' => 'text-align:center'),
                                           'column'  => 'apellido'),
                                     array('name'    => 'Nombre',
                                           'width'   => '200px',
                                           'rows'    => array('style' => 'text-align:center'),
                                           'column'  => 'nombre')
                                     );

                    $other = array(
                      array('actionName' => '',
                            'action'     => 'imprimir(##pk##)'  ,
                            'label'      => 'Imprimir',
                            'column' => 'acciones')
                            );
                    
                    //$HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'U',$other);
                    $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'U');
                    $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);

            }else{

                    $HTML = $this->SwapBytes_Html_Message->alert("No Existen Usuarios Inscritos");

                    $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));    
        }
        
    }

    public function addoreditloadAction() {
        // Obtenemos los parametros que se esperan recibir.
       if ($this->_request->isXmlHttpRequest()) {

            
            $this->SwapBytes_Ajax->setHeader();

            $datos = $this->_getAllParams();

            $queryString = $this->_getParam('filters');
            $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);

            $dataRow = $this->Pasantes->getInfoPasantePeriodo($datos['id'],$queryArray['Periodo']);
            $dataRow = $dataRow[0];
            $dataRow['id'] = $datos['id'];
            $escuela = $this->Pasantes->getPasanteEscuela($datos['id'],$queryArray['Periodo']);


            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $this->FormTitle_Detalle);
            $this->SwapBytes_Crud_Form->fillSelectBox('fk_atributo', $escuela, 'fk_atributo', 'escuela'); 
            $this->SwapBytes_Crud_Form->enableElements(true);
            $this->SwapBytes_Crud_Form->enableElement('pk_usuario', false);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
            $json[] = "$('#frmModal').parent().find(\"button:contains('Guardar')\").children().html('Imprimir');";
            $this->SwapBytes_Crud_Form->setJson($json);

            $this->SwapBytes_Crud_Form->getAddOrEditLoad();

                   
           
           
       }
    }


    public function addoreditconfirmAction() {

        if ($this->_request->isXmlHttpRequest()) {
          

            $this->SwapBytes_Ajax->setHeader();

            $filtros = $this->_getParam('filters');
            $queryArrayfiltros = $this->SwapBytes_Uri->queryToArray($filtros);

            $queryString = $this->_getParam('data');
            $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);

            $cedula = $queryArray['id'];
            $nombre = $queryArray['nombre'];
            $apellido = $queryArray['apellido'];
            $periodo = $queryArrayfiltros['Periodo'];
            $escuela = $queryArray['fk_atributo'];
            $empresa = $queryArray['empresa'];
            $responsable = $queryArray['responsable'];
            $departamento = $queryArray['departamento'];
            $sede = $this->Pasantes->getInfoPasantePeriodo($cedula, $periodo);
            
           
            

            if(empty($cedula)) return;
            if(empty($nombre)) return;
            if(empty($apellido)) return;
            if(empty($periodo)) return;
            if(empty($escuela)) return;
            if(empty($empresa)) return;
            if(empty($responsable) && empty($departamento)){
            return;
            }
            
           $lastpk = $this->certificacion->getlastpk();

        
            if(!isset($lastpk)){
                $codigo = 1;
                $this->certificacion->insertar_cert($codigo,$responsable,$departamento,$empresa);
            }else{
                $codigo = $lastpk[0]['codigo'] + 1;
                $this->certificacion->delete_cert($lastpk[0]['codigo']);
                $this->certificacion->insertar_cert($codigo,$responsable,$departamento,$empresa);
            }


            
            $json[] = 'window.location.href = escape(urlAjax + "imprimir/pk/" + '.$cedula.' + "/per/" + '.$periodo.' + "/se/" + '.$sede[0]['fk_estructura'].'+ "/esc/" + '.$escuela. '+ "/emp/" +'."'{$empresa}'" . '+"/res/"+' . "'{$responsable}'".'+"/dep/"+' . "'{$departamento}'".'+"/cod/"+' . "'{$codigo}')";
            

            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->getAddOrEditEnd();
        }
        
    }



    function imprimirAction(){
        mb_internal_encoding('UTF-8');
    
        $pk = $this->_getParam('pk');
        $sede = $this->_getParam('se');
        $periodo = $this->_getParam('per'); 
        $escuela = $this->_getParam('esc');
        $empresa = $this->_getParam('emp');
        $responsable = $this->_getParam('res');
        $departamento = $this->_getParam('dep');
        $codigo = $this->_getParam('cod');
        $pe = $this->Pasantes->getPensumPasante($pk, $escuela, $periodo);
        $pensum = $pe[0]['pk_pensum'];
        
    
        
        $config = Zend_Registry::get('config');

        $dbname = $config->database->params->dbname;
        $dbuser = $config->database->params->username;
        $dbpass = $config->database->params->password;
        $dbhost = $config->database->params->host;

     
        
        $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
        $imagen2 = APPLICATION_PATH . '/../public/images/pasantia.jpg';


        $filename    = 'ConstanciaPasantia';
        $filetype    = 'PDF';//strtolower($Params['rdbFormat']);
        
        if(empty($responsable) || empty($departamento)){
            
            $report = APPLICATION_PATH . '/modules/reports/templates/Certificacion/certificacionpasantia2.jasper';
            $params      = "'logo=string:{$imagen}|firma=string:{$imagen2}|codigo=string:{$codigo}|usuario=string:{$pk}|periodo=string:{$periodo}|pensum=string:{$pensum}|escuela=string:{$escuela}|sede=string:{$sede}'";
        }else{
         
            $report = APPLICATION_PATH . '/modules/reports/templates/Certificacion/certificacionpasantia.jasper';
            $params      = "'logo=string:{$imagen}|firma=string:{$imagen2}|codigo=string:{$codigo}|usuario=string:{$pk}|periodo=string:{$periodo}|pensum=string:{$pensum}|escuela=string:{$escuela}|sede=string:{$sede}'";
        }   
        
        
        $cmd         = "java -jar -Djava.awt.headless=true " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";

        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype }");
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
        

        $outstream = exec($cmd);
        
        echo base64_decode($outstream); 
        
    }

}