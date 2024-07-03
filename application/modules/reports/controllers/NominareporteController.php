<?php

class Reports_NominareporteController extends Zend_Controller_Action {


    private $Title = 'Reporte de Nomina';
  
    public function init() {
        
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Nominareporte');
        Zend_Loader::loadClass('CmcBytes_Filtros');
       
       
        $this->grupo                    = new Models_DbTable_UsuariosGrupos();
        $this->filtros                  = new Une_Filtros();
        $this->nomina                   = new Models_DbTable_Nominareporte();   
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
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->Request                  = Zend_Controller_Front::getInstance()->getRequest();
        $this->SwapBytes_Crud_Search->setDisplay(true); //  oculta el buscar
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->CmcBytes_Profit = new CmcBytes_Profit();
        //Filtros//

        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);
   
         //$Imprimir = "<button id='btnImprimir' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnImprimir' role='button' aria-disabled='false'>Imprimir";
        // $this->SwapBytes_Crud_Action->addCustum($Imprimir);
       
       
        $estatus[0]['valor']='A';
        $estatus[0]['display']='Activo';
        $estatus[1]['valor']='I';
        $estatus[1]['display']='Inactivo';
        $estatus[2]['valor']='L';
        $estatus[2]['display']='Liquidado';
        $estatus[3]['valor']='O';
        $estatus[3]['display']='Otro';
        $estatus[4]['valor']='PL';
        $estatus[4]['display']='Parcialmete Liquidado';
        $estatus[5]['valor']='T';
        $estatus[5]['display']='Todos';
        $this->estatus =  $estatus;
       
        $banco[0]['valor']='B001';
        $banco[0]['display']='Mercantil';
        $banco[1]['valor']='B002';
        $banco[1]['display']='Banesco';
        $this->banco = $banco;
           
        $contrato[0]['valor']='01';
        $contrato[0]['display']='ADM y APO, DIR COORD Quincenal';
       
//        $contrato[1]['valor']='02';
//        $contrato[1]['display']='Direccion y Coordinacion (Inactiva)';
       
        $contrato[1]['valor']='03';
        $contrato[1]['display']='Docentes, DIR y COORD Mensual';
       
//        $contrato[3]['valor']='04';
//        $contrato[3]['display']='Docentes Nomina Eventual';
       
//        $contrato[4]['valor']='11';
//        $contrato[4]['display']='TICKETS ELECTRONICO ALIMENTACION';
//       
//        $contrato[5]['valor']='29';
//        $contrato[5]['display']='Vacaciones (Administrativas)(s)';
//       
//        $contrato[6]['valor']='30';
//        $contrato[6]['display']='Vacaciones (Docente)(s+h)';
//       
//        $contrato[7]['valor']='31';
//        $contrato[7]['display']='Vacaciones (Docente)(h)';
//       
//        $contrato[8]['valor']='32';
//        $contrato[8]['display']='Vacaciones Mantenimiento y Seguridad';
//       
//        $contrato[9]['valor']='33';
//        $contrato[9]['display']='Vacaciones (Docente)(s)';
       
//        $contrato[10]['valor']='90';
//        $contrato[10]['display']='Utilidades';
//       
//        $contrato[11]['valor']='91';
//        $contrato[11]['display']='Vacaciones (Mant / Seg )';
//       
//        $contrato[12]['valor']='92';
//        $contrato[12]['display']='Liquidaciones';
//       
//        $contrato[13]['valor']='93';
//        $contrato[13]['display']='Pago Ingereses de Prestaciones';
//       
        $this->contrato =  $contrato;
       
       
        //Botones//

        $this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);
        $this->SwapBytes_Crud_Action->setEnable(true, true, false, false, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(false); //quita la barra de busqueda
       
    
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
            }else{
                $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,$values,null,$select);
            }

                 $html1 = "<td><input type='text' name='fecha_ini' id='fecha_ini'></td ";
                 $html2 = "<td><input type='text' name='fecha_fin' id='fecha_fin'></td ";
                
                $json[] = '$("#select_filters").children().children().first().append("<td>Fecha Inicial</td>")';
                $json[] = "$('#select_filters').children().children().last().append(\"" . $html1 . "\");";
                $json[] = '$("#select_filters").children().children().first().append("<td>Fecha Final</td>")';
                $json[] = "$('#select_filters').children().children().last().append(\"" . $html2 . "\");";
                //$json[] =  '$("#fecha_ini").datepicker()';
                //$json[] =  '$("#fecha_fin").datepicker()';
                
                $json[] =  '$("#fecha_ini").datepicker({"dateFormat": "mm-dd-yy"})';
                $json[] =  '$("#fecha_fin").datepicker({"dateFormat": "mm-dd-yy"})';
                
                $json[] = $this->CmcBytes_Filtros->addCustom('Estado',$this->estatus);
                $json[] = $this->CmcBytes_Filtros->addCustom('Banco',$this->banco);
                $json[] = $this->CmcBytes_Filtros->addCustom('Contrato',$this->contrato);
               
                //var_dump($json);

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
   
   
        
    public function listAction() {
      
    // Verificamos si es una llamada de tipo AJAX.
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
             $data = $this->_params['filters'];
             //var_dump($data);die;
             /*
              * Cambio los - por / para que se puedan comparar con los de 
              * profit, pero dejando los inputs en - para que pueda generar 
              * el reporte
              */
             $data['fecha_ini']=  str_replace("-","/",$data['fecha_ini']);
             $data['fecha_fin']=  str_replace("-","/",$data['fecha_fin']);
             
             $rows = $this->nomina->getReporte($data['fecha_ini'],$data['fecha_fin'],$data['Estado'],$data['Banco'],$data['Contrato']);
             $rows= $this->transformRows($rows);
             $this->insert_tmp($rows);
             //var_dump($data);
           
            $table = array('class' => 'tableData',
                           'width' => '800px');

            $columns = array(array('name'  => 'ID',
                                   'column'  => 'cedula',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'width'   => '80px',
                                   'primary' => true,
                                   'hide'   => false ),
                             
                             array('name'    => 'Nombre',
                                   'width'   => '150px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'nombre'),
                              
                             array('name'    => 'Cuenta',
                                   'width'   => '100px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'cuenta'),
               
                            array( 'name'    => 'Monto',
                                   'width'   => '70px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'monto')
               
                          
                );
        
               //Generamos la lista. 
               $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
               $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnDescargar', false);
               $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
               $this->getResponse()->setBody(Zend_Json::encode($json));
          
        }
    }
 
    public function transformRows($rows){
        $nrows = Array();
        $i = 0;
        $fin = count($rows['ci']);
     
       
        for ($i = 0 ; $i<$fin ; $i++){
        $nrows[$i]['cedula'] = $rows['ci'][$i];
        $nrows[$i]['nombre'] = $rows['nombre'][$i];
        $nrows[$i]['cuenta'] = $rows['cuenta'][$i];
        $nrows[$i]['monto'] =  $rows['monto'][$i];
        }
     
        return $nrows;
    }
   
    public function insert_tmp($rows){
        $this->nomina->Delete_table();
         foreach ($rows as $valor){
        $this->nomina->Insert_table($valor['cedula'],$valor['nombre'],$valor['cuenta'],$valor['monto']);
        }
       
    }
  
   public function descargarAction() {
            $this->SwapBytes_Ajax->setHeader();
       
            
            $datos=$this->_getParam('data');
            
            $queryArray=$this->SwapBytes_Uri->queryToArray($datos);
            
            //var_dump($queryArray);
            
            
            
                 
                $config = Zend_Registry::get('config');

		$dbname = $config->database->params->dbname;
		$dbuser = $config->database->params->username;
		$dbpass = $config->database->params->password;
		$dbhost = $config->database->params->host;
		$report = APPLICATION_PATH . '/modules/reports/templates/NominaReporte/nominareporte.jasper';
		$filename    = 'reportenomina';
		$filetype    = 'xls';
     

		$params      = "'fecha_ini=string:{$queryArray['fecha_ini']}|fecha_fin=string:{$queryArray['fecha_fin']}|Estado=string:{$queryArray['Estado']}|Banco=string:{$queryArray['Banco']}|Contrato=string:{$queryArray['Contrato']}'";
		$cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D pgsql -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                //echo $cmd;

          Zend_Layout::getMvcInstance()->disableLayout();
          Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
          Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
		
        $outstream = exec($cmd);
        //echo $outstream;
        
        echo base64_decode($outstream);
        
  }
  
}
?>