<?php

class Transactions_CalendariosController extends Zend_Controller_Action {


    private $Title = 'Transacciones \ Carga de Calendario';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbView_Calendarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Forms_Calendario');

        $this->Calendarios = new Models_DbView_Calendarios();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->filtros         = new Une_Filtros();
        $this->Atributos = new Models_DbTable_Atributos();

        
        //se instancia request que es el que maneja los _params
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        

        //$this->CmcBytes_Profit = new CmcBytes_Profit();


        //logger
        $this->logger = Zend_Registry::get('logger');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');

        $this->aux = Array();
        //Filtros//

        $this->filtros->setDisplay(true, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, false, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(true, false, false, false, false, false, false, false, false);
	

        //Botones//

        $this->SwapBytes_Crud_Action->setDisplay(true, false, true, true, true, true);
	$this->SwapBytes_Crud_Action->setEnable(true, false, true, true, true, true);

        //Formulario//

        $this->view->form = new Forms_Calendario();

        $this->SwapBytes_Form->set($this->view->form);
        $this->SwapBytes_Form->fillSelectBox('fk_renglon', $this->Atributos->getSelect(32) , 'pk_atributo', 'valor');
        //$this->SwapBytes_Form->fillSelectBox('fk_actividad', $this->Atributos->getSelect(31) , 'pk_atributo', 'valor');

        $this->view->form = $this->SwapBytes_Form->get();

        $this->isNew;
        $this->valid;

        //prueba ahorita
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->filtros->getParams();

        }

        function preDispatch() {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
                $this->_helper->redirector('index', 'login', 'default');
            }

            if (!$this->grupo->haveAccessToModule()) {
                $this->_helper->redirector('accesserror', 'profile', 'default');
            }
        }


        public function indexAction() {

           // $saldo_solvente = $this->CmcBytes_Profit->getSolvente(19022630);
            //$this->logger->log($saldo_solvente,ZEND_LOG::WARN);

            $this->view->title   = $this->Title;
            $this->view->filters = $this->filtros;
            $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
            $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
            $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
            $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
            $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
            $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
            $this->view->SwapBytes_Ajax->setView($this->view);
        }
        
        public function periodoAction() {
		$this->filtros->getAction();
        }


        public function actividadAction(){
            $data = $this->Calendarios->getActividades($this->_getParam('renglon'));
            $this->SwapBytes_Ajax_Action->fillSelect($data);

        }


        public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $Data   = $this->filtros->getParams();
            $json   = array();
            $searchData = $this->_getParam('buscar');
            //var_dump($searchData);
            $this->Calendarios->setSearch($searchData);
            $rows = $this->Calendarios->getCalendario($Data);
            
            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_calendario',
                                       'primary' => true,
                                        'hide' => true),

                                 array('column'  => 'titulo',
                                        'hide' => true),

                                 array('name'    => array('control' => array('tag'        => 'input',
                                                                             'type'       => 'checkbox',
                                                                             'name'       => 'chkSelectDeselect')),
                                       'column'  => 'nc',
                                       'width'   => '20px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'control' => array('tag'   => 'input',
                                                          'type'  => 'checkbox',
                                                          'name'  => 'chkClase',
                                                          'value' => '##pk_calendario##')),
                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'column'   => 'consecutivo',
                                       'rows'    => array('style' => 'text-align:right')),
                                 array('name'    => 'Actividad',
                                       'width'   => '400px',
                                       'column'  => 'valor',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Fecha de inicio',
                                       'width'   => '70px',
                                       'column'  => 'fechainicio',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Fecha de Culminacion',
                                       'width'   => '70px',
                                       'column'  => 'fechafin',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Destacada',
                                       'width'   => '60px',
                                       'column'  => 'destacada',
                                       'rows'    => array('style' => 'text-align:center')),
                                 );


                $other = Array(
                         Array( 'actionName' => '',
                                'action' => 'subactividades(##pk##)',
                                'label' => 'subactividad',
                                'column' => 'titulo',//'fechainicio',
                                'validate' => 'true',
                                'intrue' => 'true',
                                'intruelabel' => '')
                );

                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VUOD',$other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkClase');
                $json[] = $this->SwapBytes_Jquery->setAttr('rdbFormatPDF', 'disabled', 'false');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnDescargar', false);
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen actividades cargadas.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }


            $this->getResponse()->setBody(Zend_Json::encode($json));
            
        }
    }

    public function subactividadesAction($id){
        
            $this->SwapBytes_Ajax->setHeader();
            $idTitulo = $this->_getParam('pk');

            $this->_fillSelectsRecursive(1693);
            $periodo   = $this->_getParam('periodo');
            $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fechainicio');
            $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fechafin');

            $prevData = $this->Calendarios->getRow($idTitulo);
            $next = $this->Calendarios->getNextConsecutivo($periodo, $idTitulo);
            
            
            $dataRow['seq'] = $prevData['consecutivo'] . '.' . $next[0]['next'];
            $dataRow['consecutivo'] = $prevData['consecutivo'] . '.' . $next[0]['next'];
            $dataRow['titulo']      = $this->SwapBytes_Form->setValueToBoolean($dataRow['titulo']);
            $this->SwapBytes_Form->enableElement('consecutivo', false);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('titulo-element','titulo-label'));

            $json[] = "$('#fk_actividad').children().each(function(t){ if ($(this).html().length > 60) { $(this).html($(this).html().substr(0,60) + '...')} });";
            $json[] = "$('#extendida').val($('#fk_actividad').find(':selected').attr('label'));";

            $this->SwapBytes_Crud_Form->setJson($json);

            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Agregar Subactividad');

            $this->SwapBytes_Crud_Form->getAddOrEditLoad();
        
    }

    public function addoreditloadAction() {
       
		if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
                        
			$dataRow       = $this->Calendarios->getRow($this->_params['modal']['id']);
			$dataRow['id'] = $this->_params['modal']['id'];
                        $dataRow['titulo']      = $this->SwapBytes_Form->setValueToBoolean($dataRow['titulo']);
                        $dataRow['destacada']      = $this->SwapBytes_Form->setValueToBoolean($dataRow['destacar']);

                        //$this->SwapBytes_Form->fillSelectBox('fk_actividad', $this->Atributos->getSelect(31) , 'pk_atributo', 'valor');
                        
                        $this->SwapBytes_Form->fillSelectBox('fk_actividad', $this->Calendarios->getActividades($dataRow['fk_renglon']) , 'pk_atributo', 'valor');
                        
                        if(substr_count($dataRow['consecutivo'], '.') || $dataRow['titulo'] == 't'){
                            $dataRow['seq'] = $dataRow['consecutivo'];
                            $this->SwapBytes_Form->enableElement('consecutivo', false);
                            $msg = 'Editar Subactividad';

                        }else{
                           $msg = 'Editar Actividad';
                        }
                        
                        if($dataRow['seq'] || $dataRow['titulo'] == 't'){
                            $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('titulo-element','titulo-label'));
                            $this->SwapBytes_Form->enableElement('consecutivo', false);
                            $this->_params['modal']['consecutivo'] = $this->_params['modal']['seq'];
                            $valid = true;
                        }

		}else{
                    $this->isNew = true;
                    $this->_fillSelectsRecursive(1693);
                    $msg = 'Agregar Actividad';
                }

                
                 
		$dataRow['fechainicio'] = $this->SwapBytes_Date->convertToForm($dataRow['fechainicio']);
                $dataRow['fechafin'] = $this->SwapBytes_Date->convertToForm($dataRow['fechafin']);
		$json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fechainicio');
                $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fechafin');
                        
        if($dataRow['titulo'] == 't'){

                    $json[] = $this->toggleDates('hide');

                }

                
        $json[] = "$('#fk_actividad').children().each(function(t){ if ($(this).html().length > 60) { $(this).html($(this).html().substr(0,60) + '...')} });";
        $json[] = "$('#extendida').val($('#fk_actividad').find(':selected').attr('label'));;";
        $this->SwapBytes_Crud_Form->setJson($json);


        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $msg);
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();

    }

    public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            if($this->_params['modal']['seq']){
            $this->SwapBytes_Form->enableElement('consecutivo', false);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('titulo-element','titulo-label'));
            $this->_params['modal']['consecutivo'] = $this->_params['modal']['seq'];
            $valid = true;
            }
            $this->logger->log($this->_params['modal'],ZEND_LOG::WARN);
            if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] <= 0) {
                
                $valid = $this->checkConsecutivo($this->_params['modal']);
                
                
                   
            }else{
                $valid = true;
            }

            

            //$this->logger-log($this->_params['modal'],ZEND_LOG::ALERT);
			$json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fechainicio');
                        $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fechafin');

                        

			$this->SwapBytes_Crud_Form->setJson($json);

                        if($this->_params['modal']['titulo'] == 't'){
                            $this->toggleDates('hide');
                    
                        }

                        $this->_fillSelectsRecursive($this->_params['modal']['fk_renglon']);

                        

                        if(($this->_params['modal']['titulo'] == 'f'
                         && ($this->_params['modal']['fechafin'] == ''
                         || $this->_params['modal']['fechainicio'] == ''))
                         ){

                           
                            //aqui debe mandar error al form
                            //$this->validateElements($this->_params['modal']);
                             $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
                            $this->SwapBytes_Crud_Form->getAddOrEditConfirm();

                        }elseif($this->_params['modal']['titulo'] == 't' && $valid == true){

                            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
                            $this->SwapBytes_Crud_Form->getAddOrEditConfirm();

                        }elseif($this->_params['modal']['titulo'] == 'f'
                             && $this->_params['modal']['fechafin'] != ''
                             && $this->_params['modal']['fechainicio'] != ''
                             && $valid == true){

                            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
                            $this->SwapBytes_Crud_Form->getAddOrEditConfirm();

                        }



			
			
		}
	}

    public function addoreditresponseAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
            
			// Obtenemos los parametros que se esperan recibir.

			$dataRow                  = $this->_params['modal'];
                        $dataRow['fk_periodo']    = $this->_params['filters']['periodo'];
			$id                       = $dataRow['id'];
			$dataRow['id']            = null;
			$dataRow['filtro']        = null;
                        
			$dataRow['fechainicio']         = $this->SwapBytes_Date->convertToDataBase($dataRow['fechainicio']);
                        $dataRow['fechafin']         = $this->SwapBytes_Date->convertToDataBase($dataRow['fechafin']);
                        $dataRow['destacar'] = $dataRow['destacada'];
                        $dataRow['destacada'] = null;

                        if(!$dataRow['consecutivo']){
                            
                            $dataRow['consecutivo']           = $dataRow['seq'];
                            $dataRow['seq']           = null;
                            
                        }

                        

			if(is_numeric($id) && $id > 0) {
				$this->Calendarios->updateRow($id, $dataRow);
			} else{
                          
				$this->Calendarios->addRow($dataRow);
			}

			$this->SwapBytes_Crud_Form->getAddOrEditEnd();
                        
		}
    }

    public function viewAction() {
        $dataRow          = $this->Calendarios->getRow($this->_params['modal']['id']);
        $dataRow['titulo']      = $this->SwapBytes_Form->setValueToBoolean($dataRow['titulo']);
        $dataRow['fechainicio'] = $this->SwapBytes_Date->convertToForm($dataRow['fechainicio']);
        $dataRow['fechafin'] = $this->SwapBytes_Date->convertToForm($dataRow['fechafin']);
        $dataRow['destacada']      = $this->SwapBytes_Form->setValueToBoolean($dataRow['destacar']);
        $this->_fillSelectsRecursive($dataRow['fk_renglon']);
        
        if($dataRow['titulo'] == 't'){
                    $json[] = $this->toggleDates('hide');
                }


        $json[] = "$('#fk_actividad').children().each(function(t){ if ($(this).html().length > 60) { $(this).html($(this).html().substr(0,60) + '...')} });";
        $json[] = "$('#extendida').val($('#fk_actividad').find(':selected').attr('label'));";
        $this->SwapBytes_Crud_Form->setJson($json);

        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Calendario');
        $this->SwapBytes_Crud_Form->getView();
    }

    public function deleteloadAction() {
         $this->SwapBytes_Ajax->setHeader();
	$message = 'Seguro desea elmimnar esto?:';
	$permit = true;
        $params = $this->_params['modal'];
        

        
        if(!isset($params['chkClase'])){
            
            $dataRow          = $this->Calendarios->getRow($this->_params['modal']['id']);
            $dataRow['id'] = $this->_params['modal']['id'];
            $dataRow['titulo'] = $this->SwapBytes_Form->setValueToBoolean($dataRow['titulo']);
            $dataRow['destacada']      = $this->SwapBytes_Form->setValueToBoolean($dataRow['destacar']);
            if($dataRow['titulo'] == 't'){
                    $this->toggleDates('hide');
                    $message = 'Se eliminara esta actividad y todas sus dependencias';
            }
            $this->_fillSelectsRecursive($dataRow['fk_renglon']);
            

        }else {

            
            if(is_array($params['chkClase'])) {
                
                $message = 'Seguro que desea eliminar ' . count($params['chkClase']) . ' elementos?';
                $msg = true;
                $dataRow['chkClase'] = implode(',', $this->_params['modal']['chkClase']);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->open('frmModal');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideAllInputs('frmModal');
                $json[] = $this->SwapBytes_Crud_Form->setWidthLeft('80px');
                
            }else {
                
                $dataRow = $this->Calendarios->getRow($params['chkClase']);
                $dataRow['id'] = $this->_params['modal']['chkClase'];
                
                $this->_fillSelectsRecursive($dataRow['fk_renglon']);
                if($dataRow['titulo'] == 't'){
                    //$this->logger->log('eentre',ZEND_LOG::ALERT);
                    $json[] = $this->toggleDates('hide');
                    $message = 'Se eliminara esta actividad y todas sus dependencias';
                }

            }

        }
        
        $json[] = "$('#fk_actividad').children().each(function(t){ if ($(this).html().length > 60) { $(this).html($(this).html().substr(0,60) + '...')} });";
        $json[] = "$('#extendida').val($('#fk_actividad').find(':selected').attr('label'));";

	$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Acividad', $message);
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->getDeleteLoad($permit,$msg);
        
  }

 public function deletefinishAction() {
          
        $params = $this->_params['modal'];
        $params['chkClase'] = explode(',',$params['chkClase']);


        if(!is_numeric($params['id']) || $params['id'] == 0){
        
           
            if(is_array($params['chkClase'])){
                
                foreach($params['chkClase'] as $actividad) {
                    $row = $this->Calendarios->getConsecutivo($actividad);
                    if(substr_count($row[0]['consecutivo'],'.')){
                        $this->updateCascade($this->_params['filters']['periodo'], $actividad);
                    }
                    $this->Calendarios->deleteRow($actividad);
                    

                    }

            }else{
                    $row = $this->Calendarios->getConsecutivo($params['chkClase']);
                    if(substr_count($row[0]['consecutivo'],'.')){
                        $this->updateCascade($this->_params['filters']['periodo'], $params['chkClase']);
                    }
                   $this->Calendarios->deleteRow($params['chkClase']);

            }
            
        }else {
            $row = $this->Calendarios->getConsecutivo($params['id']);
            $this->deleteCascade($params['id']);
            if(substr_count($row[0]['consecutivo'],'.')){
                $this->updateCascade($this->_params['filters']['periodo'], $params['id']);
            }
            $this->Calendarios->deleteRow($params['id']);
        }


	$this->SwapBytes_Crud_Form->setProperties($this->view->form);
	$this->SwapBytes_Crud_Form->getDeleteFinish();


  }

 private function deleteCascade($pk_titulo){

     $hijos = $this->Calendarios->getHijos($this->_params['filters']['periodo'], $pk_titulo);
     if(isset($hijos)){
        foreach($hijos as $pkhijo){
           $this->Calendarios->deleteRow($pkhijo['pk_calendario']);
        }
     }else{
         return false;
     }

 }

 public function copyAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

			$Params = $this->_params['modal'];

			$this->authSpace->copyItems = $Params['chkClase'];
                         
		}
	}

 public function pasteAction() {
	    if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

			if(!isset($this->authSpace->copyItems)) { return; }

//            $Data['usuario']  = $this->authSpace->userId;


                        $periodo = $this->_params['filters']['periodo'];
//                        $this->Asignaciones->setData($this->_params['filters'], array('periodo','sede','escuela','pensum','semestre','materia','turno','seccion','usuario'));
//			$asignacion = $this->Asignaciones->getPK();

                        $actividades     = (is_array($this->authSpace->copyItems))? implode(',', $this->authSpace->copyItems) : $this->authSpace->copyItems;

			$this->Calendarios->copyRow($actividades, $periodo);

			$this->SwapBytes_Crud_Form->getRefresh();
		}
	}

 public function descargarAction() {
        $Params = $this->_getParam('data');
        $Params = $this->SwapBytes_Uri->queryToArray($Params);
        $config = Zend_Registry::get('config');

		$dbname = $config->database->params->dbname;
		$dbuser = $config->database->params->username;
		$dbpass = $config->database->params->password;
		$dbhost = $config->database->params->host;
		$report = APPLICATION_PATH . '/modules/reports/templates/Calendario/calendario.jasper';
                $subreport = APPLICATION_PATH . '/modules/reports/templates/Calendario/';
                $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
		$filename    = 'CalendarioAcademico';
		$filetype    = strtolower($Params['rdbFormat']);

		$params      = "'Periodo=string:{$Params['selPeriodo']}|SUBREPORT_DIR=string:{$subreport}|ruta_imagen=string:{$imagen}'";
		$cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                $othercmd = " sudo -u root -S ".$cmd . " | echo '102230' ";

        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype }");
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );


        $outstream = exec($cmd);
        echo base64_decode($outstream);
    }

 private function _fillSelectsRecursive($RowData) {

	$actividad = $this->Calendarios->getActividades($RowData);

	$this->SwapBytes_Form->fillSelectBox('fk_actividad', $actividad, 'pk_atributo', 'valor');

  }

 private function validateElements($data){

      $msg = '';
      $json[] = $this->SwapBytes_Jquery_Ui_Form->cleanErrors();

      if ($data['fechainicio'] == ''){

          $msg = 'Se debe ingresar una fecha de inicio';
          $id = 'fechainicio';
          $json[] = $this->SwapBytes_Jquery_Ui_Form->displayError($msg, $id);
      }

      if($data['fechafin'] == ''){

          $msg = 'Se debe ingresar una fecha de culminación';
          $id = 'fechafin';
          $json[] = $this->SwapBytes_Jquery_Ui_Form->displayError($msg, $id);
      }

      


      $this->SwapBytes_Crud_Form->setJson($json);
      $this->getResponse()->setBody(Zend_Json::encode($json));

  }

 private function checkConsecutivo($data){

      $json[] = $this->SwapBytes_Jquery_Ui_Form->cleanErrors();
      if(substr_count($data['consecutivo'], '.') == 1 && !is_numeric($data['consecutivo'])){
          $msg = "El Consecutivo debe ser un número entero";
          $id = 'consecutivo';
          $json[] = $this->SwapBytes_Jquery_Ui_Form->displayError($msg, $id);
      $this->logger->log(substr_count($data['consecutivo'], '.'),ZEND_LOG::INFO);

      }else{
          return true;
      }

      $this->SwapBytes_Crud_Form->setJson($json);
      $this->getResponse()->setBody(Zend_Json::encode($json));
  }

 private function toggleDates($toggle){


      if($toggle == 'hide'){
          //$json[]
          $js = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('fechainicio-element',
                                                                          'fechafin-element',
                                                                          'fechainicio-label',
                                                                          'fechafin-label'));

      }else{

          $js = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('fechainicio-element',
                                                                          'fechafin-element',
                                                                          'fechainicio-label',
                                                                          'fechafin-label'));
      }
      //$this->getResponse()->setBody(Zend_Json::encode($json));
      return $js;
      //$this->SwapBytes_Crud_Form->setProperties($this->view->form);
      //$this->SwapBytes_Crud_Form->setJson($json);

  }

 private function updateCascade($periodo, $pktitulo){

        $this->Calendarios->updateCascade($periodo, $pktitulo);
  }

  public function hideAction(){
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();

          if($this->_getParam('show') != 'true'){

              $json[] = $this->toggleDates('hide');
          }else{

              $json[] = $this->toggleDates('show');
          }
          $this->getResponse()->setBody(Zend_Json::encode($json));
      }
  }

  public function checkhijosAction(){
    $this->SwapBytes_Ajax->setHeader();
      $pk = $this->_getParam('id');
      $periodo = $this->_getParam('periodo');
      $hijos = $this->Calendarios->getHijos($periodo, $pk);
      
      if($this->_getParam('unchek') == true){
            
          foreach($hijos as $pkhijo){
          $json[] .= "$('input[name=chkClase][value=\'{$pkhijo['pk_calendario']}\']').attr('checked',false);";
          $json[] .= "$('input[name=chkClase][value=\'{$pkhijo['pk_calendario']}\']').attr('disabled','');";
          }

      }else{

          foreach($hijos as $pkhijo){
          $json[] .= "$('input[name=chkClase][value=\'{$pkhijo['pk_calendario']}\']').attr('checked',true);";
          $json[] .= "$('input[name=chkClase][value=\'{$pkhijo['pk_calendario']}\']').attr('disabled','disabled');";
          }

      }

      
      //$this->logger->log($json,ZEND_LOG::ALERT);
      $this->getResponse()->setBody(Zend_Json::encode($json));
  }

  public function displayactAction(){
      $this->SwapBytes_Ajax->setHeader();
      $params = $this->_getParam('data');
      $params = $this->SwapBytes_Uri->queryToArray($params);

      $act = $this->Calendarios->getact($params['fk_actividad']);
      $acti = $act[0]['valor'];
      $json[] = "$('#extendida').val('$acti');";

      $this->SwapBytes_Crud_Form->setJson($json);
      $this->getResponse()->setBody(Zend_Json::encode($json));
      //$this->logger->log($act,ZEND_LOG::INFO);

      
      
  }

}

?>
