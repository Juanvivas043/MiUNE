<?php

class Transactions_registrovehiculoController extends Zend_Controller_Action {


    private $Title = 'Transacciones \ Solicitud de Pase de Estacionamiento';

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
        Zend_Loader::loadClass('Forms_Vehiculos');

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

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->Swapbytes_array      = new SwapBytes_Array();
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

        $this->logger = Zend_Registry::get('logger');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->vehiculo_session = new Zend_Session_Namespace('vehiculo_session');
        $this->redirect_session = new Zend_Session_Namespace('redirect_session');
        //filtro
        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(true, true, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);
        // $regresar = "<button id='btnReturn' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnReturn' role='button' aria-disabled='false'>Inscribirse</button>";

        // $this->SwapBytes_Crud_Action->addCustum($regresar);
        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, false, true, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, false, true, false, false, false);



        $this->SwapBytes_Crud_Search->setDisplay(false);
        $this->view->form = new Forms_Vehiculos();
        $this->SwapBytes_Form->set($this->view->form);

        //$this->SwapBytes_Form->fillSelectBox('tipo', $this->atributos->getSelect(38) , 'pk_atributo', 'valor'); //local
        $this->SwapBytes_Form->fillSelectBox('tipo', $this->atributos->getSelect(68) , 'pk_atributo', 'valor');
        //$this->SwapBytes_Form->fillSelectBox('marca', $this->atributos->getSelect(65) , 'pk_atributo', 'valor');
        //$this->SwapBytes_Form->fillSelectBox('marca', $this->atributos->getSelect(68) , 'pk_atributo', 'valor'); //local
        $this->SwapBytes_Form->fillSelectBox('marca', $this->atributos->getSelect(65) , 'pk_atributo', 'valor');
        $this->SwapBytes_Form->fillSelectBox('modelo', $this->atributos->getChilds(8978) , 'pk_atributo', 'valor');

        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->filtros->getParams();
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
            $this->view->trigger = $this->CmcBytes_Redirect->triggerButton('btnList');
             
            $this->view->SwapBytes_Ajax->setView($this->view);
        }

        private function masterInfo(){


            $grupos = $this->grupo->getGrupos();

            $properties = array('width' => '700',
                                    'align' => 'center');

            $styles = array(array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px'),
                            array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px'),
                array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px'),
                array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px')
                );

            if($this->Swapbytes_array->in_array_recursivo(855,$grupos) && ($this->Swapbytes_array->in_array_recursivo(1745, $grupos) == false)){ //es estudiante

                $ci = $this->authSpace->userId;
                $per = $this->inscripciones->getUPI($ci);
                $info = $this->usuarios->getInfoGeneral($ci, $per);

                if(empty($info[0]['valor'])){
                    $data[] = array('Nombre:', $info[0]['nombre'], 'Apellido: ', $info[0]['apellido']);
                    $html  = $this->SwapBytes_Html->table($properties, $data, $styles);
                }else{
                    $data[] = array('Nombre:', $info[0]['nombre'], 'Apellido: ', $info[0]['apellido'],'Periodo: ', $info[0]['fk_periodo']);
                    $html  = $this->SwapBytes_Html->table($properties, $data, $styles);
                    $data2[] = array('Sede:', $info[0]['sed'],'Escuela:', $info[0]['escuela'], 'Turno: ', $info[0]['valor']);
                    $html .= $this->SwapBytes_Html->table($properties, $data2, $styles);
                }
                return $html;

            }else{

                $ci = $this->authSpace->userId;
                $per = $this->inscripciones->getUPI($ci);
                $info = $this->usuarios->getInfoGeneralUsuario($ci);

                $data[] = array('Nombre:', $info[0]['nombre'], 'Apellido: ', $info[0]['apellido']);
                $html  = $this->SwapBytes_Html->table($properties, $data, $styles);

                return $html;
            }
        }

        public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $Data   = $this->filtros->getParams();
            $json   = array();
            $ci = $this->authSpace->userId;


            $rows = $this->usuvehiculos->checkIfExists($ci);


            if(isset($rows) && count($rows) > 0) {

                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_usuariovehiculo',
                                       'primary' => true,
                                        'hide' => true),

                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'function' => 'rownum',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Marca',
                                       'width'   => '120px',
                                       'column'  => 'marca',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Modelo',
                                       'width'   => '120px',
                                       'column'  => 'modelo',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Año',
                                       'width'   => '70px',
                                       'column'  => 'ano',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Placa',
                                       'width'   => '70px',
                                       'column'  => 'placa',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Estado',
                                       'width'   => '70px',
                                       'column'  => 'nombre_sorteo',
                                       'rows'    => array('style' => 'text-align:center')),

                                 );

                $other = Array(
                         Array( 'actionName' => '',
                                'action' => 'inscribir(##pk##)',
                                'label' => 'Solicitar',
                                'column' => 'inscrito',
                                'validate' => 'true')
                                
                );

                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VDO',$other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkClase');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No ha registrado ningun vehículo.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }


            $ci = $this->authSpace->userId;
            $per = $this->periodos->getUltimo();
            $yainscrito = $this->usuvehiculossorteos->checkIfInscritoAny($ci, $per);
            if(!isset($yainscrito[0])){
                $json[] = "$('#frmDialog').html('<div style=\"text-align:left;\">Recuerde gestionar su pase de Estacionamiento, en la opción \'Solicitar\', que aparece en el listado, a un lado del vehículo.</div>')";
                $json[] = "$('#frmDialog').dialog({title: 'Solicite su Pase'})";
                $json[] = "$('#frmDialog').dialog('option','position','center')";
                $json[] = "$('#frmDialog').parent().find(\"button:contains('Ok')\").show()";
                $json[] = "$('#frmDialog').parent().find(\"button:contains('Si')\").hide()";
                $json[] = "$('#frmDialog').parent().find(\"button:contains('No')\").hide()";
                $json[] = "$('#frmDialog').dialog('open')";
            }
            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }

        public function addoreditloadAction() {


                $dataRow = $this->_params['modal'];

                $titulo = 'Agregar vehiculo';
                $ci = $this->authSpace->userId;
                unset($this->vehiculo_session->insert);

                $this->SwapBytes_Crud_Form->setJson($json);
                $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $titulo);
                $this->SwapBytes_Crud_Form->getAddOrEditLoad();


        }

        public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

                $dataRow = $this->_params['modal'];
                $datos = explode(',', $dataRow['hiden']);
                
                //placa - ano - tipo - marca - modelo
                
                if(isset($datos[1])){
                    if($datos[4] == 'no'){
                        $dataRow['placa'] = $datos[0];
                        $dataRow['ano'] = $datos[1];
                        $dataRow['tipo'] = $datos[2];
                        $dataRow['marca'] = $datos[3];
                        $data['ano'] = $datos[1];
                        
                        $this->SwapBytes_Form->fillSelectBox('marca', $this->atributos->getByPk($datos[3]) , 'pk_atributo', 'valor');
                    }else{
                        $this->SwapBytes_Form->fillSelectBox('marca', $this->atributos->getByPk($datos[3]) , 'pk_atributo', 'valor');
                        $this->SwapBytes_Form->fillSelectBox('modelo', $this->atributos->getChilds($datos[3]) , 'pk_atributo', 'valor');
                        $dataRow['placa'] = $datos[0];
                        $dataRow['ano'] = $datos[1];
                        $dataRow['tipo'] = $datos[2];
                        $dataRow['marca'] = $datos[3];
                        $data['ano'] = $datos[1];

                    }

                }else{

                    if($dataRow['tipo'] == 8977){
                        $this->SwapBytes_Form->fillSelectBox('marca', $this->atributos->getByPk($dataRow['marca']) , 'pk_atributo', 'valor');
                        $data['ano'] = $dataRow['ano'];
                    }elseif($dataRow['tipo'] == 8976){
                        $this->SwapBytes_Form->fillSelectBox('marca', $this->atributos->getByPk($dataRow['marca']) , 'pk_atributo', 'valor');
                        $this->SwapBytes_Form->fillSelectBox('modelo', $this->atributos->getChilds($dataRow['marca']) , 'pk_atributo', 'valor');
                        $data['ano'] = $dataRow['ano'];
                    }
                }

                    $this->validateElements($data);
                if($this->validateElementsValue($data) == true){

                }else{

                    $this->SwapBytes_Crud_Form->setJson($json);
                    $this->SwapBytes_Crud_Form->setProperties($this->view->form,$dataRow);
                    $message = 'El vehículo ha sido registrado satisfactoriamente, para finalizar el proceso debe inscribir el vehiculo en el periodo actual haciendo click en el boton "inscribirse".';
                    $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
                }




            }
	}

        public function addoreditresponseAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

                $dataRow = $this->_params['modal'];
                $datos = explode(',', $dataRow['hiden']);
                if(isset($datos[0])){//ya exustua
                $vehiculo = $this->vehiculos->getRowByPlaca($datos[0]);

                }else{

                $vehiculo = $this->vehiculos->getRowByPlaca($dataRow['placa']);
                }
                $uv_exists = $this->usuvehiculos->checkIfExists($this->authSpace->userId, $vehiculo[0]['pk_vehiculo']);
                //placa - ano - tipo - marca - modelo
                
                if(isset($this->vehiculo_session->insert)){
                        $grupos = $this->grupo->getGrupos();
                        unset($dataRow);
                        $dataRow['vehiculo'] = $this->vehiculo_session->insert['fk_usuariovehiculo'];

                        if($this->vehiculo_session->ispago == true){
                            $this->vehiculo_session->ispago = null;
                            $id = $this->vehiculo_session->id;
                            $this->vehiculo_session->id = null;
                            $data['numeropago'] = $dataRow['placa'];
                            $data['pago'] = 't';
                            $this->usuvehiculossorteos->updateRow($id, $data);

                        }else{

                            $ci = $this->authSpace->userId;
                                // var_dump($this->vehiculo_session->insert);
                            $dataRow['fk_tiposorteo'] = $this->vehiculo_session->insert['fk_tipo'];
                            if($dataRow['tipo'] == 8976){
                                $dataRow['fk_tiposorteo'] = 9688;
                            }elseif($dataRow['tipo'] == 8977){
                                $dataRow['fk_tiposorteo'] = 9687;
                            }
                            $dataRow['tipo'] = null;
                            $periodo = $this->periodos->getUltimo();

                            $data['periodo'] = $periodo;
                            $data['tipo'] = $dataRow['fk_tiposorteo'];
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
                            //if($entro == false){
                                
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


                           // }
                           /*else{ //es administrativo
                                $sorteo = $this->sorteo->getSpecificSorteoAdmin($data);

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

                            }*/

                            

                        }


                $this->SwapBytes_Crud_Form->getAddOrEditEnd();
                return;

                }
                if(isset($vehiculo[0])){

                    //if($datos[4] == 'no'){ //es moto
                        if(!isset($uv_exists[0])){ //no lo tiene asignado
                        $eliminado = $this->usuvehiculos->checkIfEliminado($this->authSpace->userId,$vehiculo[0]['pk_vehiculo']);
                        //var_dump($eliminado);
                        if($eliminado[0]['eliminado'] == true){ // lotiene asignado borrado
                            $id = $eliminado[0]['pk_usuariovehiculo'];
                            $data['eliminado'] = 'f';
                            $this->usuvehiculos->updateRow($id, $data);
                        }else{

                             $data['fk_vehiculo'] = $vehiculo[0]['pk_vehiculo'];
                             $data['fk_usuario'] = $this->authSpace->userId;
                             $this->usuvehiculos->addRow($data);
                        }
                        }else{
                           $message = 'El vehiculo introducido ya esta registrado a su usuario';
                           $this->SwapBytes_Crud_Form->getDialog('Error al intentar registrar vehiculo', $message, swOkOnly);
                        }
                        
                        $dataRow['placa'] = strtoupper(str_replace("-","",$datos[0]));
                        $dataRow['ano']   = $datos[1];
                        $dataRow['fk_tipo']  = $datos[2];
                        $dataRow['fk_modelo'] = $datos[3];


//                    }else{//es carro
//                        if(!isset($uv_exists[0])){//no lo tiene asignado
//
//                            $this->logger->log($dataRow,ZEND_LOG::INFO);
//
//                            $this->usuvehiculos->addRow($data);
//                        }else{
//                           $message = 'El vehiculo introducido ya esta registrado a su usuario';
//                           $this->SwapBytes_Crud_Form->getDialog('Error al intentar registrar vehiculo', $message, swOkOnly);
//                        }
//
//                    }
                }else{

                    if($dataRow['tipo'] == 8977){ //es moto
                        $dataRow['fk_tipo']  = $dataRow['tipo'];
                        $dataRow['tipo'] = null;
                        $dataRow['id'] = null;
                        $dataRow['hiden'] = null;
                        $dataRow['fk_modelo'] = $dataRow['marca'];
                        $dataRow['marca'] = null;
                        $dataRow['modelo'] = null;
                        $dataRow['placa'] = strtoupper(str_replace("-","",$dataRow['placa']));
                        $this->logger->log($dataRow,ZEND_LOG::INFO);
                        $a = $a = $this->vehiculos->addRow($dataRow);
                        $data['fk_vehiculo'] = $a;
                        $data['fk_usuario'] = $this->authSpace->userId;
                        $b = $this->usuvehiculos->addRow($data);

                    }else{// es carro
                        
                        $dataRow['fk_tipo']  = $dataRow['tipo'];
                        $dataRow['fk_modelo'] = $dataRow['modelo'];
                        $dataRow['tipo'] = null;
                        $dataRow['id'] = null;
                        $dataRow['hiden'] = null;
                        $dataRow['modelo'] = null;
                        $dataRow['marca'] = null;
                        $dataRow['placa'] = strtoupper(str_replace("-","",$dataRow['placa']));
                        $a = $this->vehiculos->addRow($dataRow);
                        $data['fk_vehiculo'] = $a;
                        $data['fk_usuario'] = $this->authSpace->userId;
                        $b = $this->usuvehiculos->addRow($data);
                    }
                }
               
               $this->SwapBytes_Crud_Form->getAddOrEditEnd();



		}
        }

        public function existsAction(){
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();
                $querystring = $this->_getParam('data');
                $params = $this->SwapBytes_Uri->queryToArray($querystring);
                $params['placa'] = str_replace("-","",$params['placa']);
                $exists = $this->vehiculos->getRowByPlaca($params['placa']);
                if(isset($exists[0]['pk_vehiculo'])){

                   $this->SwapBytes_Form->fillSelectBox('tipo', $this->atributos->getByPk($exists[0]['fk_tipo']) , 'pk_atributo', 'valor');
                   $rowData['tipo'] = $exists[0]['fk_tipo'];
                   $rowData['id'] = $exists[0]['pk_vehiculo'];
                   if($exists[0]['marca_pk'] == 8977){//12373
                       //$this->logger->log($exists[0]['fk_modelo'],ZEND_LOG::INFO);
                       $this->SwapBytes_Form->fillSelectBox('marca', $this->atributos->getByPk($exists[0]['fk_modelo']) , 'pk_atributo', 'valor');
                       $rowData['marca'] = $exists[0]['fk_modelo'];
                       $ismoto = true;
                   }else{
                       $this->SwapBytes_Form->fillSelectBox('marca', $this->atributos->getByPk($exists[0]['marca_pk']) , 'pk_atributo', 'valor');
                       $this->SwapBytes_Form->fillSelectBox('modelo', $this->atributos->getByPk($exists[0]['fk_modelo']) , 'pk_atributo', 'valor');
                       $rowData['marca'] = $exists[0]['marca_pk'];
                       $rowData['modelo'] = $exists[0]['fk_modelo'];
                       $ismoto = false;
                   }

                   //$rowData['marca'] = $exists[0]['marca_pk'];
                   //$rowData['marcamotos'] = $exists[0]['fk_modelo'];
                   //$rowData['modelo'] = $exists[0]['fk_modelo'];
                   $rowData['ano'] = $exists[0]['ano'];
                   $rowData['placa'] = $exists[0]['placa'];
//                   $miarray = array('placa' => $rowData['placa'],
//                                    'ano' => $rowData['ano'],
//                                    'tipo' => $rowData['tipo'],
//                                    'marca' => $rowData['marca'],
//                                    'modelo' => $rowData['modelo']
//                                    );
                   isset($rowData['modelo']) ? $this->logger->log('modelo set',ZEND_LOG::INFO) : $rowData['modelo'] = 'no';
                   $miarray = $rowData['placa'] . ',' . $rowData['ano'] . ',' . $rowData['tipo'] . ',' . $rowData['marca'] . ',' . $rowData['modelo'];
                   $rowData['hiden'] = $miarray;
                   
                   if(isset($rowData)){
                        $this->SwapBytes_Form->set($this->view->form);
                        $this->view->form->populate($rowData);
                        //$json[] = "$('#marca').attr('disabled','disabled');";//this->SwapBytes_Form->readOnlyElement('marca', true);

                        $this->view->form = $this->SwapBytes_Form->get();
                         $html  = $this->SwapBytes_Ajax->render($this->view->form);
                        $html  = $this->SwapBytes_Ajax->render($this->view->form);
                        $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                        //$json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 400) .';';
                    }

                    if($ismoto == true){

                       $json[] = "$('#marca').attr('disabled','disabled');";
                       $json[] = "$('#placa').attr('disabled','disabled');";
                       $json[] = "$('#tipo').attr('disabled','disabled');";
                       $json[] = "$('#ano').attr('disabled','disabled');";
                       $json[] = "$('#ano').attr('disabled','disabled');";
                       $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('modelo-label','modelo-element')).';';
                    }else{
                        $json[] = "$('#marca').attr('disabled','disabled');";
                       $json[] = "$('#placa').attr('disabled','disabled');";
                       $json[] = "$('#tipo').attr('disabled','disabled');";
                       $json[] = "$('#ano').attr('disabled','disabled');";
                       $json[] = "$('#ano').attr('disabled','disabled');";
                       $json[] = "$('#modelo').attr('disabled','disabled');";
                        $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('modelo-label','modelo-element')).';';
                    }

                }


            $this->getResponse()->setBody(Zend_Json::encode($json));
            //$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $titulo);
            }
        }

        public function exists1Action(){
            if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $querystring = $this->_getParam('data');
            $params = $this->SwapBytes_Uri->queryToArray($querystring);

            $params['placa'] = str_replace("-","",$params['placa']);

            $this->logger->log($params['placa'],ZEND_LOG::ALERT);
            $exists = $this->vehiculos->getRowByPlaca($params['placa']);
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

                    $this->logger->log($this->SwapBytes_Crud_Form->getParams(),ZEND_LOG::INFO);

                    //$json[] = "$('#tipo').attr('disabled','disabled');";
                    //$json[] = "$('#marca').attr('disabled','disabled');";
                    //$json[] = "$('#modelo').attr('disabled','disabled');";
                    //$json[] = "$('#ano').attr('disabled','disabled');";
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

        public function marcasAction(){
            $data = $this->_getParam('tipo');
            $this->logger->log($data,ZEND_LOG::INFO);
            //if($data == 8235){
            if($data == 9687){
                $data = 8977;
            //}elseif($data == 8236){
            }elseif($data == 9688){
                $data = 8976;
            }
            $data = $this->atributos->getChilds($data);
            $this->SwapBytes_Ajax_Action->fillSelect($data);

        }

        public function tipoAction(){
            $this->SwapBytes_Ajax->setHeader();
            $data = $this->_getParam('data');
            $params = $this->SwapBytes_Uri->queryToArray($data);
            //$this->SwapBytes_Form->fillSelectBox('marca', $this->atributos->getByPk($exists[0]['fk_modelo']) , 'pk_atributo', 'valor');
            //if($params['tipo'] == 8235){ //local
            if($params['tipo'] == 8977){
                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('modelo-label','modelo-element')).';';
                $data =
                $this->logger->log($json,ZEND_LOG::ALERT);
            }else{

                $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('modelo-label','modelo-element')).';';
                //$json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('marcamotos-label','marcamotos-element')).';';

            }


            $this->getResponse()->setBody(Zend_Json::encode($json));

        }

        public function modelosAction(){
            $data = $this->atributos->getChilds($this->_getParam('marca'));
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
            $grupos = $this->grupo->getGrupos();
            $data['tipo'] = $veh[0]['tipo'];



            if($this->Swapbytes_array->in_array_recursivo(1745,$grupos))  
                unset($info[0]['turno']);
            
            
          /*  if($this->Swapbytes_array->in_array_recursivo(1745,$grupos) || $this->Swapbytes_array->in_array_recursivo(854,$grupos))
            {
                $info = $this->usuarios->getInfoGeneralAdministrativo($ci);
                $data['periodo'] = $per;
                if(!empty($info))
                    $sorteo = $this->sorteo->getSpecificSorteoAdmin($data);
            }
            else
            {
                */
                $data['periodo'] = $info[0]['fk_periodo'];
                $sorteo = $this->sorteo->getSpecificSorteo($data);
          // }

            $yainscrito = $this->usuvehiculossorteos->checkIfInscrito($ci, $data['periodo'],$data['tipo']);
            if(isset($sorteo[0]) && !isset($yainscrito[0]))
            {
                if(empty($info[0]['turno']))
                {
                    $message = 'Usted se inscribirá para el pase <b>' . $sorteo[0]['descripcion'] . '</b>';
                }
                else
                {
                    $message = 'Usted Participara en el sorteo <b>' . $sorteo[0]['descripcion'] . '</b>';
                }
                if(isset($info[0]['valor']))
                    $message .='</br>En el turno <b>' . $info[0]['valor'] . '</b>';
                $message .= '</br> Con el Vehiculo <b>' . $veh[0]['marca'] . ' ' . $veh[0]['modelo'];
                $message .='</b></br>De placa: <b>' . $veh[0]['placa'];
                $insert['fk_sorteo'] = $sorteo[0]['pk_sorteo'];
                $insert['fk_usuariovehiculo'] = $veh[0]['pk_usuariovehiculo'];
                $insert['fk_turno'] = $info[0]['turno'];
                $insert['fk_tipo'] = $data['tipo'];
                unset($this->_params['modal']);
                $this->vehiculo_session->insert = $insert;

                if(empty($info[0]['turno']))
                {
                    $this->SwapBytes_Crud_Form->getDialog('¿Desea solicitar su pase?', $message, swYesNo);
                }
                else
                {
                    $this->SwapBytes_Crud_Form->getDialog('¿Desea inscribirse en el sorteo?', $message, swYesNo);
                }
            }
            elseif(isset($yainscrito[0]))
            {
                if(empty($info[0]['turno']))
                {
                    $message = 'Usted ya solicitó un pase para este período.';
                    $this->SwapBytes_Crud_Form->getDialog('¿Desea solicitar su pase?', $message, swOkOnly);
                }
                else
                {
                    $message = 'Usted ya esta participando en el sorteo.';
                    $this->SwapBytes_Crud_Form->getDialog('¿Desea inscribirse en el sorteo?', $message, swOkOnly);
                }
            }                                    


            elseif(empty($info))
            {
                $message = 'Usted no posee inscripción en el período en curso';
                $this->SwapBytes_Crud_Form->getDialog('¿Desea inscribirse en el sorteo?', $message, swOkOnly);
            }
            else
            {         
                if(empty($info[0]['turno']))
                {
                    $message = 'El lapso para la solicitud de pase de estacionamiento ha culminado.';
                    $this->SwapBytes_Crud_Form->getDialog('¿Desea solicitar su pase?', $message, swOkOnly);
                }
                else
                {
                    $message = 'El lapso para la inscripcion en el sorteo ha culminado.';
                    $this->SwapBytes_Crud_Form->getDialog('¿Desea inscribirse en el sorteo?', $message, swOkOnly);
                }
            }
        }

        public function viewAction() {

               $id = $this->_params['modal']['id'];
             $info = $this->usuvehiculos->getAllData($id);
             $dataRow = $info[0];
             $dataRow['id'] = $id;

             if($dataRow['modelo'] == null)
             { // si es moto
                 $dataRow['marcamotos'] = $dataRow['marca_pk'];
                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('marca-label','marca-element',
                                                                                'modelo-label','modelo-element')).';';

                $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('marcamotos-label','marcamotos-element')).';';
             }
             else
             {
                $this->SwapBytes_Form->fillSelectBox('modelo', $this->atributos->getChilds($dataRow['marca_pk']) , 'pk_atributo', 'valor');
                $dataRow['modelo'] = $dataRow['modelo_pk'];
                $dataRow['marca'] = $dataRow['marca_pk'];
                $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('marca-label','marca-element',
                                                                                'modelo-label','modelo-element')).';';
                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('marcamotos-label','marcamotos-element')).';';

             }

             $this->SwapBytes_Crud_Form->setJson($json);

             $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Detalles el Vehiculo');
             $this->SwapBytes_Crud_Form->getView();
        }

        public function deleteloadAction() {
             $this->SwapBytes_Ajax->setHeader();


             $id = $this->_params['modal']['id'];
             $info = $this->usuvehiculos->getAllData($id);
             $dataRow = $info[0];
             $dataRow['id'] = $id;

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
             $this->logger->log($dataRow,ZEND_LOG::ALERT);
             $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Vehiculo', $message);
             $this->SwapBytes_Crud_Form->setJson($json);
             $this->SwapBytes_Crud_Form->setWidthLeft('80px');
             $this->SwapBytes_Crud_Form->getDeleteLoad(true);

         }

        public function deletefinishAction() {
            $dataRow = $this->_params['modal'];

            $vehiculo = $this->usuvehiculos->getRow($dataRow['id']);
            $per = $this->periodos->getUltimo();
            $sorteo = $this->usuvehiculossorteos->getvehiculoInSorteo($vehiculo['pk_usuariovehiculo'],$per);
            //$this->logger->log($vehiculo['pk_usuariovehiculo'],ZEND_LOG::ALERT);
            if(isset($sorteo[0])){

                $message = 'El vehiculo esta participando en un sorteo en el periodo por lo que no puede ser eliminado';
                $this->SwapBytes_Crud_Form->getDialog('Error al intentar Eliminar vehiculo', $message, swOkOnly);

            }else{

            $eliminado = 'true';
            $id = $dataRow['id'];
            
                $this->usuvehiculos->updateDeleted($id,$eliminado);
            }


            $this->SwapBytes_Crud_Form->setProperties($this->view->form);
            $this->SwapBytes_Crud_Form->getDeleteFinish();
          }

        public function regresoAction(){
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();
                $this->redirect_session->unsetAll();

                $data = Array('module' => 'transactions',
                              'controller' => 'inscripcionsorteo',
                              'params' => Array('set' => 'true',
                                                'action' => 'agregar'
                                                ));



                $json[] = $this->CmcBytes_Redirect->getRedirect($data);
                $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }

         private function validateElements($data){

              $msg = '';
              $json[] = $this->SwapBytes_Jquery_Ui_Form->cleanErrors();

                  $this->logger->log($data['ano'],ZEND_LOG::ALERT);
              if($data['ano'] < 1500 || $data['ano'] > 2013){
                  $msg = 'El valor debe estar entre 1500 y 2013';
                  $id = 'ano';
                  $json[] = $this->SwapBytes_Jquery_Ui_Form->displayError($msg, $id);
              }elseif(!is_numeric ($data['ano'])){
                  $msg = 'El valor debe ser numerico';
                  $id = 'ano';
                  $json[] = $this->SwapBytes_Jquery_Ui_Form->displayError($msg, $id);

              }





              $this->SwapBytes_Crud_Form->setJson($json);
              $this->getResponse()->setBody(Zend_Json::encode($json));

        }

        private function validateElementsValue($data){

              $msg = '';
              $json[] = $this->SwapBytes_Jquery_Ui_Form->cleanErrors();

                  //$this->logger->log($data['ano'] < 1500,ZEND_LOG::ALERT);
              if($data['ano'] < 1500 || $data['ano'] > 2013){
                  return true;
              }else{

                  return false;
              }





              $this->SwapBytes_Crud_Form->setJson($json);
              $this->getResponse()->setBody(Zend_Json::encode($json));

        }


}


