<?php

class Transactions_inscripcionsorteoController extends Zend_Controller_Action {


    private $Title = 'Transacciones \ Inscripcion Sorteo de Puestos de Estacionamiento';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Sorteo');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Vehiculos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Usuariosvehiculos');
        Zend_Loader::loadClass('Models_DbTable_Usuariosvehiculossorteos');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Forms_Inscripcionsorteo');

        $this->vehiculos = new Models_DbTable_Vehiculos();
        $this->sorteo    = new Models_DbTable_Sorteo();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->usuarios          = new Models_DbTable_Usuarios();
        $this->atributos = new Models_DbTable_Atributos();
        $this->usuvehiculos = new Models_DbTable_Usuariosvehiculos();
        $this->usuvehiculossorteos = new Models_DbTable_Usuariosvehiculossorteos();
        $this->periodos     = new Models_DbTable_Periodos();
        $this->inscripciones     = new Models_DbTable_Inscripciones();
        $this->asignaciones     = new Models_DbTable_Asignaciones();
        //$this->vehiculos = new Models_DbTable_Vehiculos();

        $this->filtros         = new Une_Filtros();


        $this->Request = Zend_Controller_Front::getInstance()->getRequest();


        $this->Swapbytes_array      = new SwapBytes_Array();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->CmcBytes_Redirect        = new CmcBytes_Redirect();
        $this->CmcBytes_Profit       = new CmcBytes_Profit();
        $this->logger = Zend_Registry::get('logger');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->vehiculo_session = new Zend_Session_Namespace('vehiculo_session');
        $this->redirect_session = new Zend_Session_Namespace('redirect_session');

        //filtro
        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(true, true, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);

        $carro = "<button id='btnCarro' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnCarro' role='button' aria-disabled='false'>Registrar Vehiculo";

        $this->SwapBytes_Crud_Action->addCustum($carro);

        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, false, true, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, false, true, false, false, false);

        

        $this->SwapBytes_Crud_Search->setDisplay(false);
        $this->view->form = new Forms_Inscripcionsorteo();
        $this->SwapBytes_Form->set($this->view->form);

        $tipos = $this->usuvehiculos->getUserVehiculoTipes($this->authSpace->userId);
        if(isset($tipos[0])){
        $otype = Array();
                   foreach($tipos as $tp){
                        array_push($otype,$tp['tipo']);   
                   }
                   $implode = implode(',', $otype);
                   $this->logger->log($implode,ZEND_LOG::ALERT);
                   $this->SwapBytes_Form->fillSelectBox('tipo', $this->atributos->getWithIn(68,$implode) , 'pk_atributo', 'valor');//getWithIn

        }


        //$this->SwapBytes_Form->fillSelectBox('tipo', $this->atributos->getSelect(73) , 'pk_atributo', 'valor');
        $this->SwapBytes_Form->fillSelectBox('vehiculo', $this->usuvehiculos->getUserVehicules($this->authSpace->userId, 8976) , 'pk_usuariovehiculo', 'valor');
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->filtros->getParams();
        $this->_params['redirect'] = $this->redirect_session->params;
        $this->_params['exists'];

        
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

            $this->view->title   = $this->Title;
            $this->view->filters = $this->filtros;
            $this->view->info    = $this->masterInfo();
            $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
            $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
            $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
            $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
            $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
            $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
            if($this->checkRedirect()==true){
                $this->view->trigger = $this->CmcBytes_Redirect->triggerButton($this->_params['redirect']['action']);
                $this->redirect_session->unsetAll();
            }
            $this->view->trigger = $this->CmcBytes_Redirect->triggerButton('btnList');
            
            $this->view->SwapBytes_Ajax->setView($this->view);
        }

        private function checkRedirect(){

            if($this->_params['redirect']['set'] == true){
                return true;
            }else{
                return false;
            }
        }

        private function masterInfo(){


            //$info = $this->ugs->getSolicitudesFilter($this->redirect_session->params['fk_ugs']);

            $grupos = $this->grupo->getGrupos();

            if($this->Swapbytes_array->in_array_recursivo(855,$grupos)){

            $ci = $this->authSpace->userId;
            $per = $this->inscripciones->getUPI($ci);
            $periodo = $this->periodos->getUltimo();
            $info = $this->usuarios->getInfoGeneral($ci, $per);
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

            $data[] = array('Nombre:', $info[0]['nombre'], 'Apellido: ', $info[0]['apellido'],'Periodo: ', $periodo);


            $html  = $this->SwapBytes_Html->table($properties, $data, $styles);

            $data2[] = array('Sede:', $info[0]['sed'],'Escuela:', $info[0]['escuela'], 'turno: ', $info[0]['valor']);

            $html .= $this->SwapBytes_Html->table($properties, $data2, $styles);
            return $html;
            
            }

        }

        public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $Data   = $this->filtros->getParams();
            $json   = array();
            $ci = $this->authSpace->userId;
                       

            //$rows = $this->usuvehiculos->checkIfExists($ci);
            //$ci = 18941312; //pacheco
            $rows = $this->usuvehiculossorteos->getAllInscripciones($ci);
            $this->logger->log($rows,ZEND_LOG::ALERT);
            
            if(isset($rows) && count($rows) > 0) {
                
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_usuariovehiculosorteo',
                                       'primary' => true,
                                        'hide' => true),
                                 
                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'function' => 'rownum',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Sorteo',
                                       'width'   => '120px',
                                       'column'  => 'descripcion',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Periodo',
                                       'width'   => '70px',
                                       'column'  => 'fk_periodo',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Tipo',
                                       'width'   => '120px',
                                       'column'  => 'sort_tipo',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Turno',
                                       'width'   => '120px',
                                       'column'  => 'turno',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Vehiculo',
                                       'width'   => '100px',
                                       'column'  => 'modelo',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Placa',
                                       'width'   => '70px',
                                       'column'  => 'placa',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Estatus',
                                       'width'   => '70px',
                                       'column'  => 'estatus',
                                       'rows'    => array('style' => 'text-align:center')),
//                                 array('name'    => 'Pago',
//                                       'width'   => '70px',
//                                       'column'  => 'numeropago',
//                                       'rows'    => array('style' => 'text-align:center')),
                                 
                                 );


//                $other = Array(
//                         Array( 'actionName' => '',
//                                'action' => 'pagar(##pk##)',
//                                'label' => 'Pagar',
//                                'column' => 'estatus',
//                                'validate' => 'true',
//                                'intrue' => 'seleccionado',
//                                'intruelabel' => '')
//
//                );


                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'D',$other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkClase');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No tiene inscripciones en sorteos.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }


            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }

        private function errorMessage($mensaje){

            $properties = array('width' => '370',
                                    'align' => 'center');
            $styles = array(array('style' => 'text-align:center;font-size:14px;font-weight:bold'));

            $data[] = array($mensaje);

            $html   = $this->SwapBytes_Html->table($properties, $data, $styles);

            $js = $this->SwapBytes_Jquery->setHtml('datos', $html);
            return $js;


        }

        private function infoMessage($mensaje,$datos){

            $properties = array('width' => '370',
                                    'align' => 'center');
            $styles = array(array('style' => 'text-align:center;font-size:14px;'));

            $data[] = array($mensaje);

            $html   = $this->SwapBytes_Html->table($properties, $data, $styles);

            $js = $this->SwapBytes_Jquery->setHtml('datos1', $html);
            return $js;


        }

        private function underMessage($mensaje,$datos){

            $properties = array('width' => '370',
                                    'align' => 'center');
            $styles = array(array('style' => 'text-align:center;font-size:12px;'));

            $data[] = array($mensaje);

            $html   = $this->SwapBytes_Html->table($properties, $data, $styles);

            $js = $this->SwapBytes_Jquery->setHtml('datos', $html);
            return $js;


        }

        public function addoreditloadAction() {

                $grupos = $this->grupo->getGrupos();
                $json[] = "$('#frmModal').parent().find(\"button:contains('Pagar')\").children().html('Guardar');";
                $json[] = "$('#frmModal').parent().find(\"button:contains('Guardar')\").children().html('Inscribir');";
                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(Array('placa-label',
                                                                                'placa-element'));
                $dataRow = $this->_params['modal'];
                $ci = $this->authSpace->userId;
                $periodo = $this->periodos->getUltimo();
                $tipos = $this->usuvehiculos->getUserVehiculoTipes($ci);
                $info = $this->usuarios->getInfoGeneral($ci, $periodo);
                $this->logger->log($info,ZEND_LOG::ALERT);
                $tipes = Array();
                $otype = Array();
                $this->asignaciones->setData(array('usuario' => $ci,
                                                      'periodo' => $periodo),array('usuario', 'periodo'));
                $docente = $this->asignaciones->getRows();

                if(isset($tipos[0])){

                   foreach($tipos as $tp){

                        if($tp['tipo'] == 8976){
                            array_push($tipes,9688);
                        }elseif($tp['tipo'] == 8977){
                            array_push($tipes,9687);
                        }
                        array_push($otype,$tp['tipo']);
                    }

                    if(count($otype) == 1 && in_array(8977, $otype)){
                        $this->SwapBytes_Form->fillSelectBox('vehiculo', $this->usuvehiculos->getUserVehicules($this->authSpace->userId, 8977) , 'pk_usuariovehiculo', 'valor');
                    }
                    $implode = implode(',', $otype);
                    
                    $this->logger->log($tipes,ZEND_LOG::INFO);
                    //$this->SwapBytes_Form->fillSelectBox('tipo', $this->atributos->getWithIn(73,$implode) , 'pk_atributo', 'valor');//getWithIn


                    if($this->Swapbytes_array->in_array_recursivo(1745,$grupos) == true || count($docente) > 0){
                        //echo 'test';
                        $sorteos = $this->sorteo->checkSorteosAdmin($periodo, $tipes);
                        $participante = $this->usuvehiculossorteos->isParticipantes($ci, $periodo);
                    }else{


                        $sorteos = $this->sorteo->checkSorteos($periodo, $tipes);
                        $participante = $this->usuvehiculossorteos->isParticipantes($ci, $periodo);
                    }

                    if(isset($sorteos[0])){ //existen sorteos para el

                        if(isset($participante[0])){ //ya participa
                        //$this->logger->log($participante,ZEND_LOG::INFO);
                            
                            $mensaje = 'Usted ya esta participando en un sorteo.';
                            $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(Array('tipo-label',
                                                                                            'tipo-element',
                                                                                            'vehiculo-label',
                                                                                            'vehiculo-element',
                                                                                            'placa-label',
                                                                                            'placa-element'));
                            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Inscribir').';';
                            $json[] = $this->errorMessage($mensaje);
                            //$this->logger->log($this->errorMessage($mensaje),ZEND_LOG::WARN);

                        }else{
                            
                            if(isset($info[0]) || $this->Swapbytes_array->in_array_recursivo(1745,$grupos) == true || count($docente) > 0){//esta inscrito academicamente


                                if($this->Swapbytes_array->in_array_recursivo(1745,$grupos) == true || count($docente) > 0){
                                    $mensaje = 'Usted registrara este vehiculo para su pase Administrativo en el periodo <b>'. $periodo . '</b>';
                                    $json[] = $this->infoMessage($mensaje, $datos);
                                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Inscribir').';';
                                    $json[] = "$('#frmModal').parent().find(\"button:contains('Inscribir')\").children().html('Inscribir');";
                                }else{

                                    $mensaje = 'Usted participara el Sorteo del </br>Periodo: <b>' . $periodo . ' </b> Turno: <b> ' . $info[0]['valor'] . '</b></br></br>';
                                    $json[] = $this->infoMessage($mensaje, $datos);
                                    $mensaje = '</br></br>Si el vehiculo que desea inscribir no aparece listado, cancele y registre el vehiculo en la opcion correspondiente';
                                    $json[] = $this->underMessage($mensaje, $datos);
                                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Inscribir').';';
                                    $json[] = "$('#frmModal').parent().find(\"button:contains('Inscribir')\").children().html('Inscribir');";
                                }


                            }else if($this->Swapbytes_array->in_array_recursivo(1745,$grupos) == false  || count($docente) == 0){ //no esta inscrito academicacmente

                                $mensaje = 'Usted no tiene inscripcion academica en el periodo.';
                                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(Array('tipo-label',
                                                                                                'tipo-element',
                                                                                                'vehiculo-label',
                                                                                                'vehiculo-element',
                                                                                                'placa-label',
                                                                                                'placa-element'));
                                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Inscribir').';';
                                $json[] = $this->errorMessage($mensaje);
                            }
                        }


                    }else{ //noexiste sorteo

                            $mensaje = 'No existen sorteos en curso.';
                            $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(Array('tipo-label',
                                                                                            'tipo-element',
                                                                                            'vehiculo-label',
                                                                                            'vehiculo-element',
                                                                                            'placa-label',
                                                                                            'placa-element',
                                                                                            'titulo-label',
                                                                                            'titulo-element'));
                            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Inscribir').';';
                            $json[] = $this->errorMessage($mensaje);
                    }


                    $titulo = 'Inscripción en sorteo';



                    $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 400) .';';

                    $this->SwapBytes_Crud_Form->setJson($json);

                    $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $titulo);
                    $this->SwapBytes_Crud_Form->getAddOrEditLoad();
                }else{
                    $mensaje = 'Usted no tiene vehiculos registrados en el sistema, por favor registre su vehiculo a traves de la opcion correspondiente.';
                    $this->SwapBytes_Crud_Form->getDialog('Error de inscripcion', $mensaje, swOkOnly);

                }
                


        }

        public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
                        $grupos = $this->grupo->getGrupos();
                        $dataRow = $this->_params['modal'];
                        
                        $dataRow['ispago'] = null;
                        
                        $periodo = $this->periodos->getUltimo();
                        $ci = $this->authSpace->userId;
                        $info = $this->usuarios->getInfoGeneral($ci, $periodo);
                        $this->asignaciones->setData(array('usuario' => $ci,
                                                               'periodo' => $periodo),array('usuario', 'periodo'));
                        $docente = $this->asignaciones->getRows();

                        if($this->Swapbytes_array->in_array_recursivo(1745,$grupos) == true || count($docente) > 0){
                            $participa = $this->usuvehiculossorteos->vehiculoIsInSorteoAdmin($periodo, $info[0]['turno'],$dataRow['vehiculo']);

                        }else{

                            $participa = $this->usuvehiculossorteos->vehiculoIsInSorteo($periodo, $info[0]['turno'],$dataRow['vehiculo']);
                        }
                        $this->logger->log($dataRow,ZEND_LOG::WARN);
                        $this->SwapBytes_Form->fillSelectBox('vehiculo', $this->usuvehiculos->getUserVehicules($this->authSpace->userId, $dataRow['tipo']) , 'pk_usuariovehiculo', 'valor');
                        if(isset($participa[0])){


                                $mensaje = 'Otro usuario esta participando con el vehiculo seleccionado en el mismo turno. Debe seleccionar otro vehiculo.';
                                $this->SwapBytes_Crud_Form->getDialog('Error de inscripcion', $mensaje, swOkOnly);

                        }

                        if(!isset($dataRow['placa'])){

                            if(!isset($dataRow['vehiculo'])){ //no elije vehiculo


                            $mensaje = 'Usted no posee vehiculo para registrarse en el tipo de sorteo seleccionado.';
                            $this->SwapBytes_Crud_Form->getDialog('Error de inscripcion', $mensaje, swOkOnly);

                            }
                        }else{
                            $cedula = $this->authSpace->userId;
                            $pago = $dataRow['placa'];
                            //$articulos = $this->CmcBytes_Profit->getArticulosPago(18941097, $pago);
                            $array_art = Array();
                            foreach($articulos['articulo'] as $art){
                                array_push($array_art, trim($art));
                            }

                            if(in_array('ESTACIONAMIENTO', $array_art)){

                                 $this->vehiculo_session->ispago = true;
                                 $this->logger->log($dataRow,ZEND_LOG::INFO);
                            }

                            

                        }

                            $this->SwapBytes_Crud_Form->setJson($json);
                            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow);
                            $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
		}
	}

        public function addoreditresponseAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
                        $grupos = $this->grupo->getGrupos();
                        $dataRow = $this->_params['modal'];

                        if($this->vehiculo_session->ispago == true){
                            $this->vehiculo_session->ispago = null;
                            $id = $this->vehiculo_session->id;
                            $this->vehiculo_session->id = null;
                            $data['numeropago'] = $dataRow['placa'];
                            $data['pago'] = 't';
                            $this->usuvehiculossorteos->updateRow($id, $data);
                            $this->logger->log($dataRow,ZEND_LOG::ALERT);

                        }else{

                            $ci = $this->authSpace->userId;
                            if($dataRow['tipo'] == 8976){
                                $dataRow['fk_tiposorteo'] = 9688;
                            }elseif($dataRow['tipo'] == 8977){
                                $dataRow['fk_tiposorteo'] = 9687;
                            }
                            $dataRow['tipo'] = null;
                            $periodo = $this->periodos->getUltimo();

                            $data['periodo'] = $periodo;
                            $data['tipo'] = $dataRow['fk_tiposorteo'];
                            $this->logger->log($data['tipo'],ZEND_LOG::WARN);
                            $this->asignaciones->setData(array('usuario' => $ci,
                                                                  'periodo' => $periodo),array('usuario', 'periodo'));
                            $docente = $this->asignaciones->getRows();
                            $entro = false;
                            if($this->Swapbytes_array->in_array_recursivo(1745,$grupos) == true) {
                               $entro = true; 
                            }
                            if( count($docente) > 0){
                                $entro = true;
                            }
                            if($entro == false){
                                
                                $sorteo = $this->sorteo->getSpecificSorteo($data);

                                $info = $this->usuarios->getInfoGeneral($ci, $periodo);
                                if(isset($sorteo[0])){

                                    $dataRow['fk_sorteo'] = $sorteo[0]['pk_sorteo'];
                                }else{
                                    $message = 'No existen sorteos para el tipo de vehiculo seleccionado en estos momentos.';
                                    $this->SwapBytes_Crud_Form->getDialog('Error al intentar Eliminar vehiculo', $message, swOkOnly);
                                }

                                $dataRow['fk_turno'] = $info[0]['turno'];
                                $dataRow['fk_usuariovehiculo'] = $dataRow['vehiculo'];
                                $dataRow['vehiculo'] = null;
                                $dataRow['id'] = null;
                                $dataRow['fk_tiposorteo'] = null;
                                $dataRow['placa'] = null;
                                $dataRow['ispago'] = null;
                                $this->usuvehiculossorteos->addRow($dataRow);


                            }else{ //es administrativo
                                $sorteo = $this->sorteo->getSpecificSorteoAdmin($data);
    
                                //$info = $this->usuarios->getInfoGeneral($ci, $periodo);
                                if(isset($sorteo[0])){

                                    $dataRow['fk_sorteo'] = $sorteo[0]['pk_sorteo'];
                                }else{
                                    $message = 'No existen sorteos para el tipo de vehiculo seleccionado en estos momentos.';
                                    $this->SwapBytes_Crud_Form->getDialog('Error al intentar Eliminar vehiculo', $message, swOkOnly);
                                }

                                $dataRow['fk_turno'] = 10;
                                $dataRow['fk_usuariovehiculo'] = $dataRow['vehiculo'];
                                $dataRow['vehiculo'] = null;
                                $dataRow['id'] = null;
                                $dataRow['fk_tiposorteo'] = null;
                                $dataRow['placa'] = null;
                                $dataRow['ispago'] = null;
                                $this->usuvehiculossorteos->addRow($dataRow);

                            }

                            

                        }


                        $this->logger->log($dataRow,ZEND_LOG::INFO);
			$this->SwapBytes_Crud_Form->getAddOrEditEnd();
                        

		}
        }

        public function deleteloadAction() {
            $this->SwapBytes_Ajax->setHeader();
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Inscribir').';';
            $id = $this->_params['modal']['id'];
            $info = $this->usuvehiculos->getaVehiculo($id);

            $dataRow['id'] = $id;
            if(!isset($info[0]['fk_atributo'])){
            $dataRow['tipo'] = $info[0]['pk_atributo'];
            }else{
            $dataRow['tipo'] = $info[0]['fk_atributo'];

            }

            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Vehiculo', $message);
             $this->SwapBytes_Crud_Form->setJson($json);
             $this->SwapBytes_Crud_Form->setWidthLeft('80px');
             $this->SwapBytes_Crud_Form->getDeleteLoad(true);
        }

        public function deletefinishAction() {

             $dataRow = $this->_params['modal'];

             $inscripciones = $this->sorteo->getActive_DeleteAction($this->periodos->getUltimo());

             if(!isset($inscripciones[0])){
                 $message = 'Ha culminado el proceso de inscripciones en el sorteo por lo que no puede retirarse del mismo.';
                 $this->SwapBytes_Crud_Form->getDialog('Error al intentar retirarse vehiculo', $message, swOkOnly);

             }else{

                 $this->usuvehiculossorteos->deleteRow($dataRow['id']);
             }

             


             $this->SwapBytes_Crud_Form->setProperties($this->view->form);
             $this->SwapBytes_Crud_Form->getDeleteFinish();
        }

        private function validateElementsmsg($data){

              $msg = '';
              $json[] = $this->SwapBytes_Jquery_Ui_Form->cleanErrors();
              $inicio = new Zend_Date($data['fechainicio'],'dd-MM-YYYY');
              $tope = new Zend_Date($data['fechatope'],'dd-MM-YYYY');
              $fin = new Zend_Date($data['fechafin'],'dd-MM-YYYY');

              if ($tope < $fin){

                  $msg = 'la Fecha Tope debe ser mayor o igual a la de culminacion';
                  $id = 'fechatope';
                  $json[] = $this->SwapBytes_Jquery_Ui_Form->displayError($msg, $id);
              }

              if($fin < $inicio){
                  $msg = 'la Fecha de culminacion debe ser mayor o igual a la de inicio';
                  $id = 'fechafin';
                  $json[] = $this->SwapBytes_Jquery_Ui_Form->displayError($msg, $id);
              }

              $this->getResponse()->setBody(Zend_Json::encode($json));
              $this->SwapBytes_Crud_Form->setJson($json);


          }

        private function validateElements($data){

              $msg = '';
              //$jr = $this->SwapBytes_Jquery_Ui_Form->cleanErrors();
              //$this->getResponse()->setBody(Zend_Json::encode($json));
              $inicio = new Zend_Date($data['fechainicio'],'dd-MM-YYYY');
              $tope = new Zend_Date($data['fechatope'],'dd-MM-YYYY');
              $fin = new Zend_Date($data['fechafin'],'dd-MM-YYYY');

              if ($tope < $fin || $fin < $inicio){
              //$this->logger->log('dsdsd',ZEND_LOG::ALERT);
                  return false;
              }else{
                  return true;
              }

              //$this->SwapBytes_Crud_Form->setJson($json);


          }

        public function existsAction(){
            if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $querystring = $this->_getParam('data');
            $params = $this->SwapBytes_Uri->queryToArray($querystring);

            $params['placa'] = str_replace("-","",$params['placa']);
            
            $exists = $this->vehiculos->getRowByPlaca($params['placa']);
            $this->logger->log($exists,ZEND_LOG::ALERT);
            if(isset($exists[0]['pk_vehiculo'])){
                
                $this->vehiculo_session->exists = true;
                $this->SwapBytes_Form->fillSelectBox('tipo', $this->atributos->getByPk($exists[0]['fk_tipo']) , 'pk_atributo', 'valor');
                $this->SwapBytes_Form->fillSelectBox('marcamotos', $this->atributos->getByPk($exists[0]['fk_modelo']) , 'pk_atributo', 'valor'); //motos
                if(isset($exists[0]['marca_pk'])){

                    $this->SwapBytes_Form->fillSelectBox('marca', $this->atributos->getByPk($exists[0]['marca_pk']) , 'pk_atributo', 'valor');
                    
                }
                $this->SwapBytes_Form->fillSelectBox('modelo', $this->atributos->getByPk($exists[0]['fk_modelo']) , 'pk_atributo', 'valor');
                $rowData['id'] = $exists[0]['pk_vehiculo'];
                $rowData['marca'] = $exists[0]['marca_pk'];
                $rowData['marcamotos'] = $exists[0]['fk_modelo'];
                $rowData['modelo'] = $exists[0]['fk_modelo'];
                $rowData['tipo'] = $exists[0]['fk_tipo'];
                $rowData['ano'] = $exists[0]['ano'];
                $rowData['placa'] = $exists[0]['placa'];
            }else{
                $this->vehiculo_session->exists = false;
            }


           
            if(isset($rowData)){
                $this->SwapBytes_Form->set($this->view->form);
                $this->view->form->populate($rowData);

                $this->view->form = $this->SwapBytes_Form->get();
                 $html  = $this->SwapBytes_Ajax->render($this->view->form);
                $html  = $this->SwapBytes_Ajax->render($this->view->form);

                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                //$json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 400) .';';
            }

            if(isset($exists[0]['pk_vehiculo']) && isset($exists[0]['marca_pk'])){
                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('marcamotos-label','marcamotos-element')).';';
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('marca-label','marca-element',
                                                                                'modelo-label','modelo-element')).';';
            }elseif(isset($exists[0]['pk_vehiculo'])){
                $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('marcamotos-label','marcamotos-element')).';';
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('marca-label','marca-element',
                                                                                'modelo-label','modelo-element')).';';
            }
            
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar').';';

            //$this->SwapBytes_Crud_Form->setJson($json);
            $this->getResponse()->setBody(Zend_Json::encode($json));
            
            }

        }

        public function tipoAction(){
            $this->SwapBytes_Ajax->setHeader();
            $data = $this->_getParam('data');
            $params = $this->SwapBytes_Uri->queryToArray($data);

            if($params['tipo'] == 8235){

                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('marca-label','marca-element',
                                                                                'modelo-label','modelo-element')).';';
                
                $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('marcamotos-label','marcamotos-element')).';';
                $this->logger->log($json,ZEND_LOG::ALERT);
            }else{

                $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('marca-label','marca-element',
                                                                                'modelo-label','modelo-element')).';';
                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('marcamotos-label','marcamotos-element')).';';

            }


            $this->getResponse()->setBody(Zend_Json::encode($json));
            
        }

        public function modelosAction(){
            $ci = $this->authSpace->userId;
            $data = $this->usuvehiculos->getUserVehicules($ci, $this->_getParam('tipo'));
            
            $this->SwapBytes_Ajax_Action->fillSelect($data);

          

        }

        public function inscribirAction(){
            $this->SwapBytes_Ajax->setHeader();
            $pk = $this->_getParam('pk');
            $ci = $this->authSpace->userId;
            $upi = $this->inscripciones->getUPI($ci);
            $per = $this->periodos->getUltimo();
            $veh = $this->usuvehiculos->getAllData($pk);
            $info = $this->usuarios->getInfoGeneral($ci, $per);
            $data['periodo'] = $info[0]['fk_periodo'];
            $data['tipo'] = $veh[0]['tipo'];

            $sorteo = $this->sorteo->getSpecificSorteo($data);
            $yainscrito = $this->usuvehiculossorteos->checkIfInscrito($ci, $data['periodo'],$data['tipo']);

            if(isset($sorteo[0]) && !isset($yainscrito[0])){
                $message = 'Usted Participara en el sorteo <b>' . $sorteo[0]['descripcion'] . '</b>';
                $message .='</br>En el turno <b>' . $info[0]['valor'] . '</b>';
                $message .= '</br> Con el Vehiculo <b>' . $veh[0]['marca'] . ' ' . $veh[0]['modelo'];
                $message .='</b></br>De placa: <b>' . $veh[0]['placa'];
                $insert['fk_sorteo'] = $sorteo[0]['pk_sorteo'];
                $insert['fk_usuariovehiculo'] = $veh[0]['pk_usuariovehiculo'];
                $insert['fk_turno'] = $info[0]['turno'];
                unset($this->_params['modal']);
                $this->vehiculo_session->insert = $insert;
                $this->logger->log($this->_params['modal'],ZEND_LOG::ALERT);
                $this->SwapBytes_Crud_Form->getDialog('¿Desea inscribirse en el sorteo?', $message, swYesNo);
            }elseif(isset($yainscrito[0])){
                $message = 'Usted ya esta participando en el sorteo.';
                $this->SwapBytes_Crud_Form->getDialog('¿Desea inscribirse en el sorteo?', $message, swOkOnly);
            }else{
                $message = 'El lapso para la inscripcion en el sorteo ha culminado.';
                $this->SwapBytes_Crud_Form->getDialog('¿Desea inscribirse en el sorteo?', $message, swOkOnly);
            }

            
        }

        public function viewAction() {

               $id = $this->_params['modal']['id'];
             $info = $this->usuvehiculos->getAllData($id);
             $dataRow = $info[0];
             $dataRow['id'] = $id;
             $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Inscribir').';';
             if($dataRow['modelo'] == null){ // si es moto
                 $dataRow['marcamotos'] = $dataRow['marca_pk'];
                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('marca-label','marca-element',
                                                                                'modelo-label','modelo-element')).';';

                $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('marcamotos-label','marcamotos-element')).';';
             }else{
                $this->SwapBytes_Form->fillSelectBox('modelo', $this->atributos->getChilds($dataRow['marca_pk']) , 'pk_atributo', 'valor');
                $dataRow['modelo'] = $dataRow['modelo_pk'];
                $dataRow['marca'] = $dataRow['marca_pk'];
                $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('marca-label','marca-element',
                                                                                'modelo-label','modelo-element')).';';
                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('marcamotos-label','marcamotos-element')).';';

             }

             $this->SwapBytes_Crud_Form->setJson($json);

             $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Detales el Vehiculo');
             $this->SwapBytes_Crud_Form->getView();
        }

        public function carroAction(){
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();
                $this->redirect_session->unsetAll();

                $data = Array('module' => 'transactions',
                              'controller' => 'registrovehiculo',
                              'params' => Array('set' => 'true',
                                                'action' => 'listar'
                                                ));



                $json[] = $this->CmcBytes_Redirect->getRedirect($data);
                $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }

        public function pagarAction(){
            $this->SwapBytes_Ajax->setHeader();
            $pk = $this->_getParam('pk');

            $pago = $this->usuvehiculossorteos->hasPago($pk);
            $this->vehiculo_session->id = $pk;
            if(!isset($pago[0]['numeropago'])){

            //$this->logger->log($pk,ZEND_LOG::ALERT);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(Array('tipo-label',
                                                                                'tipo-element',
                                                                                'vehiculo-label',
                                                                                'vehiculo-element'));
                $json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter('frmModal') . ';';
                $json[] = "$('#frmModal').parent().find(\"button:contains('Guardar')\").children().html('Pagar');";
                $json[] = "$('#frmModal').parent().find(\"button:contains('Inscribir')\").children().html('Pagar');";
                $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 220) .';';

            }


            $this->SwapBytes_Crud_Form->setJson($json);

            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Agregar Subactividad');

            $this->SwapBytes_Crud_Form->getAddOrEditLoad();


        }



        
}


?>


