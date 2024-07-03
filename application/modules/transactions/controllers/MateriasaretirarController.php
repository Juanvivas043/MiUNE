<?php

class Transactions_MateriasaretirarController extends Zend_Controller_Action {


    private $Title = 'Transacciones \ Retiro de Materias';

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
        Zend_Loader::loadClass('Models_DbView_Sedes');
        Zend_Loader::loadClass('Models_DbView_Escuelas');
        Zend_Loader::loadClass('Forms_Retirarmaterias');

        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->ugs             = new Models_DbTable_UsuariosGruposSolicitudes();
        $this->estudiante      = new Models_DbTable_Usuarios();
        $this->recordacademico = new Models_DbTable_Recordsacademicos();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->calendarios     = new Models_DbView_Calendarios();
        $this->inscripciones   = new Models_DbTable_Inscripciones();
        $this->mar             = new Models_DbTable_Materiasaretirar();
        $this->vsede           = new Models_DbView_Sedes();
        $this->vescuelas       = new Models_DbView_Escuelas();
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

        $this->logger = Zend_Registry::get('logger');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->redirect_session = new Zend_Session_Namespace('redirect_session');

        //filtro
       $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
//        $this->filtros->setDisabled(false, false, true, true, true, true, true, true, true);
//        $this->filtros->setRecursive(true, true, false, false, false, false, false, false, false);

        //botones

        $Regreso = "<button id='btnReturn' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnReturn' role='button' aria-disabled='false'>Regresar";

        $this->SwapBytes_Crud_Action->addCustum($Regreso);

       $filter = $this->ugs->getSolicitudesFilter($this->redirect_session->params['fk_ugs']);
       
       if($this->redirect_session->params['permit'] == true && $filter[0]['fk_periodo'] == $this->periodos->getUltimo()){ //si ya esta cerrada escondo el boton de agregar
            $this->SwapBytes_Crud_Action->setDisplay(true, false, true);
            $this->SwapBytes_Crud_Action->setEnable(true, false, true);
       }else{
           $this->SwapBytes_Crud_Action->setDisplay(true, false, false);
           $this->SwapBytes_Crud_Action->setEnable(true, false, false);
       }

        $this->SwapBytes_Crud_Search->setDisplay(false);
        $this->view->form = new Forms_Retirarmaterias();
        $this->SwapBytes_Form->set($this->view->form);
        $this->view->message = '';
        $this->view->form = $this->SwapBytes_Form->get();
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->filtros->getParams();
        $this->_params['redirect'] = $this->redirect_session->params;
        //$this->redirect_session->unsetAll();
        $this->FormTitle_Info = 'Retirar Materia';
    }

    function preDispatch() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if (!$this->grupo->haveAccessToModule()) {
//            $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

        public function indexAction() {

            $this->view->title   = $this->Title;
            $this->view->filters = $this->filtros;
            $this->view->info    = $this->masterInfo();
            $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
            $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
            $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
            $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
            $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
            $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
            $this->view->filterwidth              = "";
            if($this->checkRedirect()==true){
                $this->view->trigger = $this->CmcBytes_Redirect->triggerButton($this->_params['redirect']['action']);
            }
            $this->view->SwapBytes_Ajax->setView($this->view);        
        }

        private function masterInfo(){

            $info = $this->ugs->getSolicitudesFilter($this->redirect_session->params['fk_ugs']);
            //$info = $this->redirect_session->params;
            $properties = array('width' => '700',
                                    'align' => 'center');
                                    
            //$info['sede'] = $this->vsede->getSedeName($info['sede']);
            //$info['escuela'] = $this->vescuelas->getEscuelaName($info['escuela']);

            $styles = array(array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px'),
                            array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px'),
                array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px'),
                array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px')
                );

            $data[] = array('N˚ de Solicitud: ', $info[0]['pk_usuariogruposolicitud'], 'Nombre:', $info[0]['nombre'], 'Apellido: ', $info[0]['apellido'],'Periodo: ', $info[0]['fk_periodo']);
            

            $html  = $this->SwapBytes_Html->table($properties, $data, $styles);

            $data2[] = array('Sede:', $info[0]['sed'],'Escuela:', $info[0]['escuela'],'Semestre:', $info[0]['sem_ubic']);
            
            $html .= $this->SwapBytes_Html->table($properties, $data2, $styles);
            return $html;
        }

        private function checkRedirect(){

            if($this->_params['redirect']['set'] == true){
                return true;
            }else{
                return false;
            }
        }

        public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $Data   = $this->filtros->getParams();
            $json   = array();
            $ci = $this->authSpace->userId;
            $filter = $this->_params['redirect'];
            $Escuela = $this->inscripciones->getUltimaEscuelapk($ci);
            $rows = $this->mar->listMateriasRetiradas($filter['fk_ugs'], $filter['periodo']);
            //$rows = $this->recordacademico->getList_record_especifico($filter['periodo'], $filter['sede'], $Escuela, $ci);
            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_materiaretirar',
                                       'primary' => true,
                                        'hide' => true),
                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'function' => 'rownum',
                                       'rows'    => array('style' => 'text-align:right')),
                                 array('name'     => 'Ordinal',
                                       'width'    => '20px',
                                       'column' => 'cod_ord',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Materia',
                                       'width'   => '250px',
                                       'column'  => 'materia'),
                                 array('name'     => 'Escuela',
                                       'width'    => '20px',
                                       'column' => 'cod_esc',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'     => 'Semestre',
                                       'width'    => '20px',
                                       'column' => 'cod_sem',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Seccion',
                                       'width'   => '70px',
                                       'column'  => 'valor',
                                       'rows'    => array('style' => 'text-align:center')),
                                 
                                 );

                if($this->redirect_session->params['permit'] == true){
                    $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VD',$other);
                }else{
                    $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'V',$other);
                }
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkClase');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No se han agregado materias a esta solicitud.");
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

         private function errorSolvente($msg = null){

            $html = "<div class=\'alert\'><div class=\'message\'>{$msg}</div></div>";
            $js .= $this->SwapBytes_Jquery->setHtml('error_helper', $html) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('fk_tipo-element','fk_tipo-label')) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->setCenter('frmModal') . ';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Aceptar').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Siguiente').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Cancelar').';';
            return $js;
        }

        public function addoreditloadAction() { 
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();
                $ci = $this->authSpace->userId;
                if($this->checkRedirect() == true){
                    $filter = $this->ugs->getSolicitudesFilter($this->redirect_session->params['fk_ugs']);
                }else{
                }
                $up = $this->periodos->getUltimo();
                   //es  $filter[0]['fk_periodo']
                if($filter[0]['fk_periodo'] == $up){
                    $dataRow = $this->recordacademico->getList_record_especifico($filter[0]['fk_periodo'], $filter[0]['sed_cod'], $filter[0]['esc_cod'], $ci, true);
                    $this->SwapBytes_Form->fillSelectBox('pk_record', $this->recordacademico->getList_record_especifico($filter[0]['fk_periodo'], $filter[0]['sede_cod'], $filter[0]['esc_cod'], $ci, true) , 'pk_recordacademico', 'materia');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Retirar').';';
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Deshacer').';';
                	$json[] = "$('#frmModal').parent().find(\"button:contains('Guardar')\").children().html('Retirar');";
                    $this->SwapBytes_Crud_Form->setJson($json);
                    $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Retirar Materia');
                    $this->SwapBytes_Crud_Form->getAddOrEditLoad();
                }else{
                    
                    //$this->logger->log($filter[0]['fk_periodo'],ZEND_LOG::WARN);

                }

            }

        }

        public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $message .='Esta seguro que desea retirar la materia?';
            $dataRow = $this->_params['modal'];
            $entro = true;
            $asignaturas_preladas = array();
            $estudiante = $this->recordacademico->getInfoEstudiantePorRecord($dataRow['pk_record']);
            $asignatura = $this->recordacademico->getAsignaturas($dataRow['pk_record']);
            $asignaturas = $this->recordacademico->getAsignaturasPreladas($asignatura);
            foreach ($asignaturas as $key => $value) {
                foreach ($value as $sub_key => $sub_value) {
                    array_push($asignaturas_preladas,$sub_value);
                }
            }
            foreach ($asignaturas_preladas as $key2 => $value2) {
                $materiasestados = $this->recordacademico->getEstadoMateriaEstudiante($estudiante[0]['cedula'],$estudiante[0]['escuela'],$estudiante[0]['periodo'],$estudiante[0]['pensum'], $value2);
                if(count($materiasestados) > 0){
                    if($entro == true){
                        $message .= "<br><b>Las siguientes materias están siendo vistas en curso simultaneo, en caso de que decida aceptar, también se retirarán: </b><br>";
                        $entro = false;
                    }
                    //$this->recordacademico->updateRow($materiasestados[0]['pk_recordacademico'], null, null, null, 863, null);
                    $message .= "<br><b><div style=\"color:#FF0000;\">".$materiasestados[0]['materia']."</div></b>";
                }

            }
            $filter = $this->ugs->getSolicitudesFilter($this->redirect_session->params['fk_ugs']);
            $ci = $this->authSpace->userId;
            $this->SwapBytes_Form->fillSelectBox('pk_record', $this->recordacademico->getList_record_especifico($filter[0]['fk_periodo'], $filter[0]['sede_cod'], $filter[0]['esc_cod'], $ci) , 'pk_recordacademico', 'materia');
            $ci = $this->authSpace->userId;
            $UIAPP = $this->recordacademico->getUltimoIndiceAcademicoPorPeriodo($ci);
            //obtenemos los datos correspondientes al usuario (escuela, periodo, pensum)
            $datosEstudiante = $this->recordacademico->getPeriodoEscuelaPensum($ci);
            //en esta seccion se toma la funcion del indice academico que toma el pensum (la misma del recordacademico)
            $IA = $this->recordacademico->getIAAEscuelaPensumArticulado($ci,$datosEstudiante[0]['escuela'],$datosEstudiante[0]['periodo'],$datosEstudiante[0]['pensum']);
            $record = $this->recordacademico->getDetalleRecord($dataRow['pk_record']);
            $materias = $this->recordacademico->countInscritas($ci, $filter[0]['fk_periodo']);
            $upc = $this->recordacademico->getUltimoPeriodocursado($ci);
            $periodoactual = $this->periodos->getUltimo();
            if($periodoactual - $upc[0]['fn_xrxx_reinscripcion_upc'] > 1){
                $reingreso = true;
            } else {
                $reingreso = false;
            }
            if ($materias['materias'] > 1) {
			$NI = $this->recordacademico->isNuevoIngreso($ci);
            if (isset($UIAPP) && $UIAPP < 11 && $IA< 11 && $reingreso == false && $NI == false) {
				$raspada = $this->recordacademico->getBuscarMateriasRaspadas($record['fk_atributo'], 
				$ci, $record['periodo'], $record['codigopropietario']);
				if (isset($raspada[0]) ||  //////////////////////////servicio comunitario
					$record['fk_materia'] == 718 ||$record['fk_materia'] == 1701|| $record['fk_materia'] == 719 || $record['fk_materia'] == 913) {
				   		//comentaria esto
						if ($raspada[0]['calificacion'] >= 0 && $raspada[0]['calificacion'] < 10 
							&& $record['estado'] == 864 || $record['fk_materia'] == 1701) {


                    	}
					} else {
						//retiradas < 3 Cambio para el periodo 129 donde solo se podia retirar menos 
						//de 3 materias
						/*$retiradas = $this->recordacademico->getCountRetiradasEstudiantePeriodo($ci,$filter[0]['fk_periodo']);
						if ($retiradas < 3) {*/
                    		$this->SwapBytes_Crud_Form->getDialog('¿Desea retirar la materia?', $message, swYesNo);
                    		//$this->logger->log($filter,ZEND_LOG::ALERT);
                    		$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow);
                    		$this->SwapBytes_Crud_Form->getAddOrEditConfirm();
						/*} else {
							$message = "Usted ya retiró el máximo(3) de materias para este periodo";	
                			$this->SwapBytes_Crud_Form->getDialog('No puede retirar', $message, swOkOnly);
						}*/
                }
			} else if ($record['fk_materia'] == 718 || /////////////////////////////////servicio comunitario
						$record['fk_materia'] == 1701|| $record['fk_materia'] == 719 || $record['fk_materia'] == 913){ //comentaria esto

            		} else {
						//retiradas < 3
						/*$retiradas = $this->recordacademico->getCountRetiradasEstudiantePeriodo($ci,$filter[0]['fk_periodo']);
						if ($retiradas < 3) {*/
                			//$this->logger->log('saddsadasd',ZEND_LOG::ALERT);
                			$this->SwapBytes_Crud_Form->getDialog('¿Desea retirar la materia?', $message, swYesNo);
                			$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow);
							$this->SwapBytes_Crud_Form->getAddOrEditConfirm();
						/*} else {
							$message = 'Usted ya retiró el máximo(3) de materias para este periodo';	
                			$this->SwapBytes_Crud_Form->getDialog('No puede retirar', $message, swOkOnly);
						}*/
            		}

            } else {
                $message = "Solo tiene una materia inscrita, para retirarla debe realizar una solicitud de retiro de semestre.";
                $this->SwapBytes_Crud_Form->getDialog('¿Desea retirar El semestre?', $message, swOkOnly);
            }
			//$this->SwapBytes_Form->fillSelectBox('pk_record', $this->recordacademico->getList_record_especifico($filter['periodo'],
			//$filter['sede'], $filter['escuela'], $ci) , 'pk_recordacademico', 'materia');
            }

        }

        public function addoreditresponseAction() {

        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $ci = $this->authSpace->userId;
            $dataRow = $this->_params['modal'];
            $simultaneo = $this->_params['modal'];
            $dataRow['fk_recordacademico'] = $dataRow['pk_record'];
            $asignaturas_preladas = array();
            $estudiante = $this->recordacademico->getInfoEstudiantePorRecord($dataRow['fk_recordacademico']);
            $asignatura = $this->recordacademico->getAsignaturas($dataRow['fk_recordacademico']);
            $asignaturas = $this->recordacademico->getAsignaturasPreladas($asignatura);
            foreach ($asignaturas as $key => $value) {
                foreach ($value as $sub_key => $sub_value) {
                    array_push($asignaturas_preladas,$sub_value);
                }
            }
            
            $dataRow['fk_usuariogruposolicitud'] = $this->redirect_session->params['fk_ugs'];
            $dataRow['id'] == null;
            $dataRow['hiddenhelper'] = null;
            $dataRow['pk_record'] = null;
            $this->mar->addRow($dataRow); 

            foreach ($asignaturas_preladas as $key2 => $value2) {
                $materiasestados = $this->recordacademico->getEstadoMateriaEstudiante($estudiante[0]['cedula'],$estudiante[0]['escuela'],$estudiante[0]['periodo'],$estudiante[0]['pensum'], $value2);
                
                if(count($materiasestados) > 0){
                    $simultaneo['fk_recordacademico'] = $materiasestados[0]['pk_recordacademico'];
                    $simultaneo['fk_usuariogruposolicitud'] = $this->redirect_session->params['fk_ugs'];
                    $simultaneo['id'] == null;
                    $simultaneo['hiddenhelper'] = null;
                    $simultaneo['pk_record'] = null;
                    $this->mar->addRow($simultaneo); 
                    $this->recordacademico->updateRow($materiasestados[0]['pk_recordacademico'], null, null, null, 863, null);
                    
                }

            }
            $this->recordacademico->updateRow($dataRow['fk_recordacademico'], null, null, null, 863, null);
            $this->SwapBytes_Crud_Form->getAddOrEditEnd();
            }
        }

        public function deleteloadAction() {

             

             $dataRow = $this->_params['modal'];
             $sb = $this->mar->displayInfo($dataRow['id']);

            $entro = true;
            $message = '';
            $css = "$('#frmModal').css('height','95');";
            $asignaturas_preladas = array();

            $estudiante = $this->recordacademico->getInfoEstudiantePorRecord($sb[0]['pk_recordacademico']);
            
            $asignatura = $this->recordacademico->getAsignaturas($sb[0]['pk_recordacademico']);

            $asignaturas = $this->recordacademico->getAsignaturasPreladas($asignatura);

            foreach ($asignaturas as $key => $value) {
                foreach ($value as $sub_key => $sub_value) {
                    array_push($asignaturas_preladas,$sub_value);
                }
            }

            
            foreach ($asignaturas_preladas as $key2 => $value2) {
                
                $materiasestados = $this->recordacademico->getEstadoMateriaEstudianteRetirada($estudiante[0]['cedula'],$estudiante[0]['escuela'],$estudiante[0]['periodo'],$estudiante[0]['pensum'], $value2);

                if(count($materiasestados) > 0){

                    if($entro == true){
                        $message .= "<br><b>Las siguientes materias estuvieron siendo vistas en curso simultaneo, en caso de que decida aceptar, los cambios se aplicaran tambien para: </b><br>";
                        $entro = false;
                        $css = "$('#frmModal').css('height','130px');";
                    }
                    
                    $message .= "<br><b><div style=\"color:#FF0000;\">".$materiasestados[0]['materia']."</div></b>";
  
                }

            }



             $this->SwapBytes_Form->fillSelectBox('pk_record', $sb, 'pk_recordacademico', 'materia');
             $json[] = $this->displayMateriaInfo($sb[0]['pk_recordacademico']);
             $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Retirar').';';
             $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Deshacer').';';
             $json[] = "$('#frmModal').parent().find(\"button:contains('Eliminar')\").children().html('Deshacer');";
             $json[] = $css;

             $this->SwapBytes_Crud_Form->setJson($json);

             $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Deshacer retiro', $message);
             $this->SwapBytes_Crud_Form->setWidthLeft('80px');
             $this->SwapBytes_Crud_Form->getDeleteLoad(true);

         }


        public function deletefinishAction() {
            
            $dataRow = $this->_params['modal'];
            $entro = true;
            $sb = $this->mar->displayInfo($dataRow['id']);


            $asignaturas_preladas = array();

            $estudiante = $this->recordacademico->getInfoEstudiantePorRecord($sb[0]['pk_recordacademico']);
            
            $asignatura = $this->recordacademico->getAsignaturas($sb[0]['pk_recordacademico']);

            $asignaturas = $this->recordacademico->getAsignaturasPreladas($asignatura);

            foreach ($asignaturas as $key => $value) {
                foreach ($value as $sub_key => $sub_value) {
                    array_push($asignaturas_preladas,$sub_value);
                }
            }

            
            foreach ($asignaturas_preladas as $key2 => $value2) {
                
                $materiasestados = $this->recordacademico->getEstadoMateriaEstudiante($estudiante[0]['cedula'],$estudiante[0]['escuela'],$estudiante[0]['periodo'],$estudiante[0]['pensum'], $value2);

                if(count($materiasestados) > 0){
                    $this->recordacademico->updateRow($materiasestados[0]['pk_recordacademico'], null, null, null, 864, null);
                    $this->mar->deleteByRecord($materiasestados[0]['pk_recordacademico']);
                    
                }

            }

            $this->recordacademico->updateRow($sb[0]['pk_recordacademico'], null, null, null, 864, null);
            $this->mar->deleteRow($dataRow['id']);
            $this->SwapBytes_Crud_Form->setProperties($this->view->form);
            $this->SwapBytes_Crud_Form->getDeleteFinish();
          }

        public function viewAction() {

             $dataRow = $this->_params['modal'];
             $sb = $this->mar->displayInfo($dataRow['id']);
             $this->SwapBytes_Form->fillSelectBox('pk_record', $sb, 'pk_recordacademico', 'materia');
             $json[] = $this->displayMateriaInfo($sb[0]['pk_recordacademico']);
             $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Retirar').';';
             $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Deshacer').';';
             $this->SwapBytes_Crud_Form->setJson($json);
             $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Detalles de la materia');
             $this->SwapBytes_Crud_Form->getView();
        }

        private function displayMateriaInfo($pkrecord){

            $record = $this->recordacademico->getDetalleRecord($pkrecord);
            $ci = $this->authSpace->userId;
            $properties = array('width' => '230',
                                    'align' => 'center',
                                    'style' => 'float:left; margin-left:70px;');

            $styles = array(array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px'));
            $UIAPP = $this->recordacademico->getUltimoIndiceAcademicoPorPeriodo($ci);
            //obtenemos los datos correspondientes al usuario (escuela, periodo, pensum)
            $datosEstudiante = $this->recordacademico->getPeriodoEscuelaPensum($ci);
            //en esta seccion se toma la funcion del indice academico que toma el pensum (la misma del recordacademico)
            $IA = $this->recordacademico->getIAAEscuelaPensumArticulado($ci,$datosEstudiante[0]['escuela'],$datosEstudiante[0]['periodo'],$datosEstudiante[0]['pensum']);
            $upc = $this->recordacademico->getUltimoPeriodocursado($ci);
            $periodoactual = $this->periodos->getUltimo();
            $NI = $this->recordacademico->isNuevoIngreso($ci);
            if($periodoactual - $upc[0]['fn_xrxx_reinscripcion_upc'] > 1){
                $reingreso = true;
            }else{
                $reingreso = false;
            }
            if(isset($UIAPP) && $UIAPP < 11 && $IA < 11 && $reingreso == false && $NI == false){//el estudiante esta en pira
            //$this->logger->log($record,ZEND_LOG::NOTICE);
               $raspada = $this->recordacademico->getBuscarMateriasRaspadas($record['fk_atributo'], $ci, $record['periodo'], $record['codigopropietario']);
               //var_dump($raspada);
                if(isset($raspada[0])){
                    if($raspada[0]['calificacion'] >= 0 && $raspada[0]['calificacion'] < 10 && $record['estado'] == 864){
                        
                        $record['estadovalor'] = 'No Retirable';
                        $js .= $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Retirar').';';
                        $span = "style=\'color:red;\'";
                    }
                }elseif($record['fk_materia'] == 1701){ //|| $record['fk_materia'] == 718 || $record['fk_materia'] == 719 || $record['fk_materia'] == 913){ //comentaria esto
                        $record['estadovalor'] = 'No Retirable';
                        $js .= $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Retirar').';';
                        $span = "style=\'color:red;\'";
                }else{
                    $js .= $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Retirar').';';
                }
                /////////////servicio comuitario
            }elseif($record['fk_materia'] == 718 ||$record['fk_materia'] == 1701|| $record['fk_materia'] == 719 || $record['fk_materia'] == 913 || $record['fk_materia']==10653|| $record['fk_materia']==10652){ //comentaria esto
                        $record['estadovalor'] = 'No Retirable';
                        $js .= $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Retirar').';';
                        $span = "style=\'color:red;\'";
            }else{
                $js .= $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Retirar').';';
            }
                $data[] = array('Seccion: ', $record['valor']);
                $data[] = array('Unidades de Credito: ', $record['unidadcredito']);
                $data[] = array('Estado: ', '<span ' . $span . '>'. $record['estadovalor'] .'</span>');
                $html  = $this->SwapBytes_Html->table($properties, $data, $styles);
                $js .= $this->SwapBytes_Jquery->setHtml('error_helper', $html) .';';
                $js .= $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 200) .';';
                $js .= $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 370) .';';
                $js .= $this->SwapBytes_Jquery_Ui_Form->setCenter('frmModal') . ';';
                return $js;
        }

        public function existsAction(){
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();
                $querystring = $this->_getParam('data');
                $params = $this->SwapBytes_Uri->queryToArray($querystring);
                $json[] = $this->displayMateriaInfo($params['pk_record']);
                $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }

        public function regresoAction(){
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();
                $this->redirect_session->unsetAll();

                $data = Array('module' => 'transactions',
                              'controller' => 'solicitudretiromaterias',
                              'params' => Array('set' => 'true',
                                                'action' => 'listar'
                                                ));



                $json[] = $this->CmcBytes_Redirect->getRedirect($data);
                $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }

        

}


?>
