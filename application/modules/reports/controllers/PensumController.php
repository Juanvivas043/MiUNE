<?php

class Reports_PensumController extends Zend_Controller_Action {
    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Pensums');
        
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        
        $this->CmcBytes_Filtros         = new CmcBytes_Filtros();
        $this->filtros         = new Une_Filtros();
        $this->seguridad       = new Models_DbTable_UsuariosGrupos();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->sedes           = new Models_DbTable_Estructuras();
        $this->escuelas        = new Models_DbTable_EstructurasEscuelas();
        $this->RecordAcademico = new Models_DbTable_Recordsacademicos();
        $this->pensum          = new Models_DbTable_Pensums();

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

		$this->SwapBytes_Crud_Action->setDisplay(false,false);
		$this->SwapBytes_Crud_Action->setEnable(false, false);
		$this->SwapBytes_Crud_Search->setDisplay(false);
                
                $this->view->filters                    = $this->filtros;
               
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
                                                       'ee.fk_estructura = ##Sede##'),
                                                 Array('ee.fk_atributo',
                                                       'es.escuela'),
                                                 'ASC'),
            
                              'Pensum'  => Array(Array('tbl_asignaturas aa',
                                                        'tbl_pensums p'),
                                                  Array('p.pk_pensum = aa.fk_pensum',
                                                        'p.fk_escuela = ##Escuela##'),
                                                  Array('p.pk_pensum',
                                                        'p.nombre'),
                                                  'ASC'));
        
//     * Obtiene los parametros de los filtros y del modal.
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
                
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
        $this->view->title = "Reportes \ Pensum";
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
		$this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
		$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
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
    
    public function descargarAction() {
        
        $this->SwapBytes_Ajax->setHeader();
        $Params = $this->_getParam('data');
        $Tipo = $this->_getParam('tipo');
        $Params = $this->SwapBytes_Uri->queryToArray($Params);
        $config = Zend_Registry::get('config');
        $pensum = $this->RecordAcademico->getPensum($Params['Pensum']);
        
        $pensum = $this->RecordAcademico->getPensum($Params['Pensum']);
        
		$dbname = $config->database->params->dbname;
		$dbuser = $config->database->params->username;
		$dbpass = $config->database->params->password;
		$dbhost = $config->database->params->host;             
                
                if($pensum[0]['nombre'] >= 2012){//Si el pensum es el nuevo o el viejo.
                    
                    if($Tipo == 'rdbFormatOEH')  {                      
                       
                        $report = APPLICATION_PATH . '/modules/reports/templates/Pensums/Pensums.jasper';
                        
                    }else if($Tipo == 'rdbFormatNormal'){
                        
                        $report = APPLICATION_PATH . '/modules/reports/templates/Pensums/Pensums2New.jasper';
			
                    }
                    
                    
                }else{
                    
                    if($Tipo == 'rdbFormatOEH'){  

                        $report = APPLICATION_PATH . '/modules/reports/templates/Pensums/Pensums.jasper';
			
                    }else if($Tipo == 'rdbFormatNormal'){
                        $report = APPLICATION_PATH . '/modules/reports/templates/Pensums/Pensums2.jasper';
			
                    }
                    
                }
                

                
		$filename    = 'pensums';
		$filetype    = 'PDF';
      if(!is_array($Params['chkEstudiante'])){
         $Estudiantes = $Params['chkEstudiante'];
      }else{
         $Estudiantes = implode(',', $Params['chkEstudiante']);
      }
      
      if($Params['Pensum'] == 6){ //Pensum de Turismo
          
          $Params['Pensum'] = 12;
          
      }
//                $direccion = '/var/www/default/http/MiUNE2gitINTER/application/modules/reports/templates/Pensums/';
                $direccion = APPLICATION_PATH . '/modules/reports/templates/Pensums/';
                $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
                
		$params      = "'sede=string:{$Params['Sede']}|periodo=string:{$Params['Periodo']}|escuela=string:{$Params['Escuela']}|pensum=string:{$Params['Pensum']}|SUBREPORT_DIR=string:{$direccion}|image=string:{$imagen}'";
//                echo $params;
		$cmd         = "java -jar -Djava.awt.headless=true " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
//		
        $outstream = exec($cmd);
//        echo base64_decode($outstream)
        $this->getResponse()->setBody(base64_decode($outstream));

    }
    
}
