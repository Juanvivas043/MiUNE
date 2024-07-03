<?php

class Reports_PlanillasmasivasController extends Zend_Controller_Action {


    private $Title = 'Planillas masivas';
   
    public function init() {
         
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
         Zend_Loader::loadClass('Models_DbTable_Planillasmasivas');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        
        
        $this->grupo               = new Models_DbTable_UsuariosGrupos();
        $this->planillas           = new Models_DbTable_Planillasmasivas();
		$this->filtros         = new Une_Filtros();
        $this->CmcBytes_Filtros         = new CmcBytes_Filtros();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Ui = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        
       
        //Filtros//

        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);
	
        $Imprimir = "<button id='btnImprimir' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnImprimir' role='button' aria-disabled='false'>Imprimir";
        $this->SwapBytes_Crud_Action->addCustum($Imprimir);
        
      
        //Filtros Pocho
            $this->tablas = Array(
                                'Periodo'   => Array(Array('tbl_periodos p1','tbl_periodos p2'),
                                                  Array( 'p1.pk_periodo = p2.pk_periodo'),
                                                  Array('p1.pk_periodo','p2.pk_periodo as valor'),
                                                  'DESC'),
            
                                'Sede'      =>     Array('tbl_estructuras',
                                                  'pk_estructura = 7 OR pk_estructura = 8',
                                                   Array('pk_estructura','nombre'),
                                                  'DESC'),
                                                
                                'Escuela'    =>     Array('vw_escuelas',
                                                 'pk_atributo <> 920',
                                                Array('pk_atributo','escuela'),
                                                  'DESC'),
                                                  
                                'Pensum'    =>    Array('tbl_pensums',
                                                 Array('fk_escuela = ##Escuela##'),
                                                 Array('codigopropietario','nombre'),
                                                'DESC')
                           
            
            );
              
        //Botones//

        $this->SwapBytes_Crud_Action->setDisplay(false, false, false, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(false, false, false, false, false, false);
        
     
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

    public function filterAction(){
            $this->SwapBytes_Ajax->setHeader(); 
            $select = $this->_getParam('select');
            $values = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));
            
           
                    
            if(!$select || !$values){
            $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,null,1,null);
            $tipo[0]['valor'] = "0";
            $tipo[0]['display'] = "-----------------";
            $tipo[1]['valor'] = "1";
            $tipo[1]['display'] = "Online";
            $tipo[2]['valor'] = "2";
            $tipo[2]['display'] = "De Ingreso";
            $tipo[3]['valor'] = "3";
            $tipo[3]['display'] = "Todos";
            $json[] .= $this->CmcBytes_Filtros->addCustom("Tipo",$tipo);
            }else{
                $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,$values,null,$select);
                
            }  
           
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
        
    public function indexAction() {
        $this->view->title      = $this->Title;
        $this->view->filters    = $this->filtros;
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
		$this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
		$this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
		$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
             
    }
    
    public function imprimirAction(){
           
              $this->SwapBytes_Ajax->setHeader();
             
            $periodo = $this->_params['filters']['Periodo'];
            $sede    = $this->_params['filters']['Sede'];
            $escuela = $this->_params['filters']['Escuela'];
            $pensum  = $this->_params['filters']['Pensum'];
            $tipos    = $this->_params['filters']['Tipo'];
            $valido  = true;
            $tipo    = "";
            if($tipos === "0" ){ // sin nada
                $valido = false;                
            }
            if($tipos === "1" ){ //Online
               $ci = $this->planillas-> getCedulasOnline($periodo,$sede,$escuela,$pensum);
               $tipo = "MiUNE Online";
            }
            if($tipos === "2" ){ // Nuevo Ingreso
                $ci = $this->planillas-> getCedulasNuevos($periodo,$sede,$escuela,$pensum);
                $tipo = "MiUNE De Ingreso";
            }
            if($tipos === "3" ){// todos
                $ci = $this->planillas-> getCedulasTodos($periodo,$sede,$escuela,$pensum);   
                $tipo = "MiUNE ";
            }
            
           if($valido){
               
          
            foreach($ci AS $ids){
                
               
               $id .= $ids['pk_usuario'].', ';
            }
            $id = substr($id,0,  strlen($id)-2);
            
         
       
            $ci= $id;
            
            //echo $ci;
            
            $config = Zend_Registry::get('config');

                    $dbname = $config->database->params->dbname;
                    $dbuser = $config->database->params->username;
                    $dbpass = $config->database->params->password;
                    $dbhost = $config->database->params->host;
                    $report = APPLICATION_PATH . '/modules/reports/templates/Planilla/HomePlanillaOnline.jasper';
                    $subreport = APPLICATION_PATH . '/modules/reports/templates/Planilla/';
                    $filename    = 'planilla';
                    $filetype    = 'pdf';

                    $params      = "'ci=string:{$ci}|escuela=string:{$escuela}|pensum=string:{$pensum}|periodo=string:{$periodo}|sede=string:{$sede}|tipo=string:{$tipo}|SUBREPORT_DIR=string:{$subreport}'";                    
                    $cmd         = "java -jar -Djava.awt.headless=true " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                    
                 //   echo $cmd;

            Zend_Layout::getMvcInstance()->disableLayout();
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
            Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );

            $outstream = exec($cmd);
            echo base64_decode($outstream);
        
         }    
    
         }
  
   
   
}

