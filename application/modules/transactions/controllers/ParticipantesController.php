<?php

class Transactions_ParticipantesController extends Zend_Controller_Action {


    private $Title = 'Transacciones \ Participantes Sorteo';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGruposSolicitudes');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Forms_Solicituddocumentos');
        Zend_Loader::loadClass('Models_DbTable_Documentossolicitados');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbView_Calendarios');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Sorteo');


        //loaders de reportes:
        Zend_Loader::loadClass('Une_Cde_Reportes_CertificacionNotas');
        Zend_Loader::loadClass('Une_Cde_Reportes_CertificacionPensum');
        Zend_Loader::loadClass('Une_Cde_Reportes_ConstanciaDeEstudios');
        Zend_Loader::loadClass('Une_Cde_Reportes_ConstanciaActuacionEstudiantil');
        Zend_Loader::loadClass('Une_Cde_Reportes_ConstanciaSoloFaltaTesis');
        Zend_Loader::loadClass('Une_Cde_Reportes_ConstanciaTramitacionDeTitulo');
        Zend_Loader::loadClass('Une_Cde_Reportes_CertificacionProgramas');
        Zend_Loader::loadClass('Models_DbTable_Usuariosvehiculossorteos');

        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->ugs           = new Models_DbTable_UsuariosGruposSolicitudes();
        $this->inscripciones = new Models_DbTable_Inscripciones;
        $this->ds            = new Models_DbTable_Documentossolicitados;
        $this->periodos       = new Models_DbTable_Periodos;
        $this->filtros         = new Une_Filtros();
        $this->calendarios     = new Models_DbView_Calendarios();
        $this->RecordAcademico     = new Models_DbTable_Recordsacademicos();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->usuvehiculossorteos = new Models_DbTable_Usuariosvehiculossorteos();
        $this->sorteo = new Models_DbTable_Sorteo();


        //reportes:
        $this->certificacionnotas = new Une_Cde_Reportes_CertificacionNotas();
        $this->certificacionpensum = new Une_Cde_Reportes_CertificacionPensum();
        $this->constanciadeestudios = new Une_Cde_Reportes_ConstanciaDeEstudios();
        $this->constanciatramitaciondetitulo = new Une_Cde_Reportes_ConstanciaTramitacionDeTitulo();
        $this->constanciaactuacionestudiantil = new Une_Cde_Reportes_ConstanciaActuacionEstudiantil();
        $this->constanciasolofaltatesis = new Une_Cde_Reportes_ConstanciaSoloFaltaTesis();
        $this->certificacionprogramas = new Une_Cde_Reportes_CertificacionProgramas();

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
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();

        $this->CmcBytes_Profit = new CmcBytes_Profit();
        $this->CmcBytes_Redirect = new CmcBytes_Redirect();

        $this->logger = Zend_Registry::get('logger');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->redirect_session = new Zend_Session_Namespace('redirect_session');

        //filtro
        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(true, true, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);

        $this->SwapBytes_Crud_Search->setDisplay(true);

        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, false, false, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, false, true, false, false, false);
        $imprimir = "<button id='btnImp' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnImp' role='button' aria-disabled='false'>Imprimir</button>";
        $imprimirS = "<button id='btnImpS' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnImpS' role='button' aria-disabled='false'>Seleccionados</button>";
        $regresar = "<button id='btnReturn' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnReturn' role='button' aria-disabled='false'>Regresar</button>";

        $this->SwapBytes_Crud_Action->addCustum($imprimir);
        $this->SwapBytes_Crud_Action->addCustum($imprimirS);
        $this->SwapBytes_Crud_Action->addCustum($regresar);


        $this->SwapBytes_Crud_Action->addCustum('&nbsp;<select id="selEstado" name="selEstado" style="width:100px"></select>');

        $this->view->form = new Forms_Solicituddocumentos();

        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
        $this->_params['redirect'] = $this->redirect_session->params;

        $pk = $this->_params['redirect']['fk_ugs'];
        $administrativo = $this->sorteo->checkifisAdministrativo($pk);

        if($administrativo == true){

            $this->Title = 'Transacciones \ Puestos Administartivos';
        }else{

        }


        $this->tablas = Array(
                              'Turno'  => Array('tbl_atributos',
                                                 //array('fk_atributotipo = 37'), //local
                                                 array('fk_atributotipo = 2',
                                                       'pk_atributo <> 892'),
                                                 array('pk_atributo',
                                                       'valor')),
//                              'Tipo'  => Array('tbl_atributos',
//                                                 //array('fk_atributotipo = 37'), //local
//                                                 array('fk_atributotipo = 69'),
//                                                 array('pk_atributo',
//                                                       'valor')),
            );
        
    }



        function preDispatch() {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
                $this->_helper->redirector('index', 'login', 'default');
            }

            if (!$this->grupo->haveAccessToModule()) {
                $this->_helper->redirector('accesserror', 'profile', 'default');
            }
        }


        private function masterInfo(){


            //$info = $this->ugs->getSolicitudesFilter($this->redirect_session->params['fk_ugs']);

            $ci = $this->authSpace->userId;
            $per = $this->inscripciones->getUPI($ci);
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

            $data[] = array('Nombre:', $info[0]['nombre'], 'Apellido: ', $info[0]['apellido'],'Periodo: ', $info[0]['fk_periodo']);


            $html  = $this->SwapBytes_Html->table($properties, $data, $styles);

            $data2[] = array('Sede:', $info[0]['sed'],'Escuela:', $info[0]['escuela'], 'turno: ', $info[0]['valor']);

            $html .= $this->SwapBytes_Html->table($properties, $data2, $styles);
            return $html;
        }

        public function indexAction() {

            $this->view->title   = $this->Title;
            $this->view->filters = $this->filtros;
            $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
            $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
            $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
            $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
            $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
            $this->view->search_span           = 2;
            $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
            $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
            $this->view->trigger = $this->CmcBytes_Redirect->triggerButton('btnList');
            if($this->checkRedirect()==true){
                //$this->view->trigger = $this->CmcBytes_Redirect->triggerButton($this->_params['redirect']['action']);
                //$this->redirect_session->unsetAll();

            }
            $this->view->SwapBytes_Ajax->setView($this->view);

        }

        private function checkRedirect(){

            if($this->_params['redirect']['set'] == true){
                return true;
            }else{
                return false;
            }
        }

        public function estadoAction() {
            $dataRows = Array(Array('1','Seleccionado'),
                              Array('2','Inscrito'),
                              Array('3','Retirado'),
                             );
        $this->logger->log($dataRows,ZEND_LOG::ALERT);
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows, "Estado");
        }

        public function cambiarAction() {
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();

                $json = array();
                $Data = $this->_getParam('data');
                $filterData = $this->_getParam('filter');
                $Filter = $this->SwapBytes_Uri->queryToArray($filterData);
                $Rows = $this->SwapBytes_Uri->queryToArray($Data);
                //$this->logger->log($Rows,ZEND_LOG::INFO);
                $fk = $this->_params['redirect']['fk_ugs'];
                $periodo = $this->_params['redirect']["per"];
                //var_dump($this->_params['redirect']);
                $puestos = $this->usuvehiculossorteos->cantidadPuestosSorteo($fk,$Filter['Turno'],$periodo);
                
                if(isset($Rows['chkRecordAcademico'])){
                    if (is_array($Rows['chkRecordAcademico'])) {

                        if(count($Rows['chkRecordAcademico']) > $puestos[0]['restantes']){
                            $permit = false;

                        }else{
                            $permit = true;
                        }

                            foreach($Rows['chkRecordAcademico'] as $doc){
                                $pk = $doc;

                                switch ($Filter['selEstado']){

                                    CASE 1:
                                       if($permit == true){

                                           $up_data['seleccionado'] = true;
                                           $update = $this->usuvehiculossorteos->updateRow($pk, $up_data);
                                           $up_data['retirado'] = 'f';
                                           $update = $this->usuvehiculossorteos->updateRow($pk, $up_data);

                                       }else{
                                           $message = "No existen suficientes puestos disponibles para asignar.";
                                           $this->SwapBytes_Crud_Form->getDialog('Error al seleccionar', $message, swOkOnly);
                                       }
                                       break;
                                    CASE 2:
                                       $up_data['seleccionado'] = 'f';
                                       $update = $this->usuvehiculossorteos->updateRow($pk, $up_data);
                                       break;
                                   CASE 3:
                                       $up_data['seleccionado'] = 'f';
                                       $update = $this->usuvehiculossorteos->updateRow($pk, $up_data);
                                       $up_data['retirado'] = true;
                                       $update = $this->usuvehiculossorteos->updateRow($pk, $up_data);
                                       break;
                                   default :
                                       break;
                                }
                            }


                    }else{

                        $pk = $Rows['chkRecordAcademico'];

                        if($puestos[0]['restantes'] >= 1){
                            $permit = true;

                        }else{
                            $permit = false;
                        }

                        switch ($Filter['selEstado']){

                                CASE 1:
                                   if($permit == true){

                                       $up_data['seleccionado'] = true;
                                       $update = $this->usuvehiculossorteos->updateRow($pk, $up_data);
                                       $up_data['retirado'] = 'f';
                                       $update = $this->usuvehiculossorteos->updateRow($pk, $up_data);
                                   }else{
                                       $message = "No existen suficientes puestos disponibles para asignar.";
                                       $this->SwapBytes_Crud_Form->getDialog('Error al seleccionar', $message, swOkOnly);
                                   }
                                   break;
                                CASE 2:
                                   $up_data['seleccionado'] = 'f';
                                    //$this->logger->log($up_data,ZEND_LOG::INFO);
                                    $update = $this->usuvehiculossorteos->updateRow($pk, $up_data);
                                   break;
                                CASE 3:
                                       $up_data['seleccionado'] = 'f';
                                       $update = $this->usuvehiculossorteos->updateRow($pk, $up_data);
                                       $up_data['retirado'] = true;
                                       $update = $this->usuvehiculossorteos->updateRow($pk, $up_data);
                                       break;
                               default :
                                   break;


                            }
                        //$up_data['fk_estado'] = $Rows['selEstado'];
                        //$update = $this->ds->updateRow($pk, $up_data);
                    }

                }

               

                $json[] = $this->SwapBytes_Jquery->getJSON('list', null, array('buscar' => $this->SwapBytes_Jquery->getValEncodedUri('txtBuscar'),
                                            'filters' => $this->SwapBytes_Jquery->serializeForm()

                                        ));
                $json[] = $this->SwapBytes_Jquery->setValSelectOption('selEstado', 0);
                $this->getResponse()->setBody(Zend_Json::encode($json));

            }
        }

        public function filterAction(){
            $this->SwapBytes_Ajax->setHeader();
            $select = $this->_getParam('select');
            $values = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));
            $pk = $this->_params['redirect']['fk_ugs'];
            $administrativo = $this->sorteo->checkifisAdministrativo($pk);
            
            if($administrativo[0]['administrativo'] == false){

                if(!$select || !$values){
                    $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,null,1,null);
                }else{
                    $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,$values,null,$select);
                }
            }


            $this->getResponse()->setBody(Zend_Json::encode($json));
        }

        public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json   = array();
            $searchData = $this->_getParam('buscar');

            $this->usuvehiculossorteos->setSearch($searchData);

            $data = $this->_params['filters'];
            $pk = $this->_params['redirect']['fk_ugs'];
            $periodo = $this->_params['redirect']['per'];
            $administrativo = $this->sorteo->checkifisAdministrativo($pk);

            $rows = $this->usuvehiculossorteos->participantesSorteo($pk,$data['Turno']);
           if($administrativo[0]['administrativo'] == false){
            
            $puestos = $this->usuvehiculossorteos->cantidadPuestosSorteo($pk,$data['Turno'],$periodo);

            foreach($rows as $i => $data){

		if($rows[$i]['pago'] != 'Si' && $rows[$i]['seleccionado'] == 'Seleccionado'){
			$esta = $this->CmcBytes_Profit->getEstacionamiento($data['cedula'],$periodo);
		}
                //$esta = $this->CmcBytes_Profit->getEstacionamiento(20123384,$periodo);
                //var_dump($esta);
                if(isset($esta['articulo'][0])){

                    $esta = trim($esta['articulo'][0]);

                    if($esta == 'ESTACIONAMIENTO'){

                    //$data['pago'] = 'Si';
                    $this->logger->log($data,ZEND_LOG::INFO);
                    if($data['seleccionado'] == 'Inscrito'){
                        $rows[$i]['pago'] = 'Invalido';
                    }else{

                        $pk = $rows[$i]['pk_usuariovehiculosorteo'];
                        $update_data['pago'] = 'true';
                        $pago_change = $this->usuvehiculossorteos->updateRow($pk, $update_data);
                        $rows[$i]['pago'] = 'Si';
                        if($rows[$i]['wiegand'] != 'N/T' && $rows[$i]['seleccionado'] == 'Seleccionado')
                            $rows[$i]['activable'] = 'Si';
                    }
                    //$this->logger->log($data,ZEND_LOG::INFO);

                    }
                }
                if($rows[$i]['retirado'] ==  true){
                    $rows[$i]['Seleccionado'] == 'Retirado';
                }
            }

            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_usuariovehiculosorteo',
                                       'primary' => true,
                                        'hide' => true),
                                 array('name' => array('control' => array('tag' => 'input',
                                                'type' => 'checkbox',
                                                'name' => 'chkSelectDeselect')),
                                        'width' => '30px',
                                        'column' => 'accion',
                                        'rows' => array('style' => 'text-align:center'),
                                        'control' => array('tag' => 'input',
                                            'type' => 'checkbox',
                                            'name' => 'chkRecordAcademico',
                                            'id' => 'chkRecordAcademico',
                                            'value' => '##pk_usuariovehiculosorteo##')),
                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'function' => 'rownum',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Cédula',
                                       'width'   => '60px',
                                       'column'  => 'cedula',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Nombre',
                                       'width'   => '190px',
                                       'column'  => 'nombre',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'apellido',
                                       'width'   => '190px',
                                       'column'  => 'apellido',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Escuela',
                                       'width'   => '130px',
                                       'column'  => 'escuela',
                                       'rows'    => array('style' => 'text-align:left')), 
                                 array('name'    => 'Estado',
                                       'width'   => '70px',
                                       'column'  => 'seleccionado'),
                                 array('name'    => 'Pago',
                                       'width'   => '60px',
                                       'column'  => 'pago',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Emitido',
                                       'width'   => '70px',
                                       'column'  => 'emitido',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'C.Carnet',
                                       'width'   => '70px',
                                       'column'  => 'wiegand',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'C.Estado',
                                       'width'   => '70px',
                                       'column'  => 'cactivo',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Placa',
                                       'width'   => '70px',
                                       'column'  => 'placa',
                                       'rows'    => array('style' => 'text-align:left')),
                                 );


                $other = Array(
                         Array( 'actionName' => '',
                                'action' => 'emitir(##pk##)',
                                'label' => 'Emitir',
                                'column' => 'pago',
                                'validate' => 'true',
                                'intrue' => 'Si',
                                'intruelabel' => ''),
                         Array( 'actionName' => '',
                                'action' => 'activar(##pk##,$(this));return false;',
                                'label' => 'Activar',
                                'column' => 'activable',
                                'validate' => 'true',
                                'intrue' => 'Si',
                                'intruelabel' => ''),
                         Array( 'actionName' => '',
                                'action' => 'desactivar(##pk##,$(this));return false;',
                                'label' => 'Desactivar',
                                'column' => 'cactivo',
                                'validate' => 'true',
                                'intrue' => 'Activo',
                                'intruelabel' => '')
                                
                );

                $HTML2 = "<div style=\'border: 1px solid black;width:100px;height:100px;background-color:white;text-align:center;font-size:40px;\'>";
                $HTML2 .= "<span style=\'display:table-cell; vertical-align:middle; text-align:center; font-size:18px\'>Disponibles:</span>{$puestos[0]['restantes']}";
                $HTML2 .= "<span align=\'center\' style=\'float:left; margin-left:20px; display:table-cell; vertical-align:middle; text-align:center; font-size:18px\'>Puestos</span></div>";

                $validacion = "var cont=0; $('#tableData').children().children().children().each(function(){
                                 if (cont != 0){
                                   var pago = $(this).children(':eq(7)').html();
                                   var seleccionado = $(this).children(':eq(6)').html();
                                   if(pago == 'Si' && seleccionado == 'Seleccionado'){
                                   $(this).children(':eq(0)').children().attr('checked',true);
                                   }
                                 }
                                 cont++;
                                 });";

                $validacion2 = "var cont=0; $('#tableData').children().children().children().each(function(){
                                 if (cont != 0){
                                   var pago = $(this).children(':eq(7)').html();
                                   var seleccionado = $(this).children(':eq(6)').html();
                                   if((pago == 'Si' && seleccionado == 'Seleccionado') || seleccionado == 'Retirado'){
                                   $(this).children(':eq(0)').children().attr('disabled','disabled');
                                   }
                                 }
                                 cont++;
                                 });"; //deshabilitar retirados y seleccionados pagos



                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'O',$other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML) . ";";
                $json[] = $this->SwapBytes_Jquery->setHtml('puestoscant', $HTML2);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkRecordAcademico');
                $json[] = $validacion;
                $json[] = $validacion2;
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen inscritos en el sorteo para este turno.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }


           }else{

                if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_usuariovehiculosorteo',
                                       'primary' => true,
                                        'hide' => true),
                                 array('name' => array('control' => array('tag' => 'input',
                                                'type' => 'checkbox',
                                                'name' => 'chkSelectDeselect')),
                                        'width' => '30px',
                                        'column' => 'accion',
                                        'rows' => array('style' => 'text-align:center'),
                                        'control' => array('tag' => 'input',
                                            'type' => 'checkbox',
                                            'name' => 'chkRecordAcademico',
                                            'id' => 'chkRecordAcademico',
                                            'value' => '##pk_usuariovehiculosorteo##')),
                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'function' => 'rownum',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Cédula',
                                       'width'   => '60px',
                                       'column'  => 'cedula',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Nombre',
                                       'width'   => '190px',
                                       'column'  => 'nombre',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'apellido',
                                       'width'   => '190px',
                                       'column'  => 'apellido',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Estado',
                                       'width'   => '70px',
                                       'column'  => 'seleccionado'),
                                 array('name'    => 'Tipo Empleado',
                                       'width'   => '100px',
                                       'column'  => 'tiposorteo'),
                                 array('name'    => 'Emitido',
                                       'width'   => '70px',
                                       'column'  => 'emitido',
                                       'rows'    => array('style' => 'text-align:left')),
                                 );


                $other = Array(
                         Array( 'actionName' => 'emetir',
                                'action' => 'emitir(##pk##)',
                                'label' => 'Emitir',
                                )

                );


                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'O',$other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML) . ";";
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkRecordAcademico');

            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen inscritos en el sorteo para este turno.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }
           }

            //$this->logger->log($rows,ZEND_LOG::ALERT);

            //$this->logger->log($rows,ZEND_LOG::ALERT);

            


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

        public function regresoAction(){
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();
                $this->redirect_session->unsetAll();

                $data = Array('module' => 'transactions',
                              'controller' => 'gestiondesorteos',
                              'params' => Array('set' => 'true',
                                                'action' => 'listar'
                                                ));



                $json[] = $this->CmcBytes_Redirect->getRedirect($data);
                $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }


        public function imprimirliataAction(){
            $this->SwapBytes_Ajax->setHeader();
            $turno = $this->_getParam('turno');
            $sorteo = $this->_params['redirect']['fk_ugs'];
            $config = Zend_Registry::get('config');

                        $dbname = $config->database->params->dbname;
                        $dbuser = $config->database->params->username;
                        $dbpass = $config->database->params->password;
                        $dbhost = $config->database->params->host;
                        $report = APPLICATION_PATH . '/modules/reports/templates/estacionamiento/gsorteo.jasper';
                        $filename    = 'Listado_Completo';
                        $filetype    = 'PDF';
                        $params      = "'sorteo=string:{$sorteo}|turno=string:{$turno}'";
                        $cmd         = "java -jar -Djava.awt.headless=true " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";

            Zend_Layout::getMvcInstance()->disableLayout();
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype }");
            Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );

            $outstream = exec($cmd);
            echo base64_decode($outstream);
        }

	public function activarAction(){
            	$this->SwapBytes_Ajax->setHeader();
                $pk = $this->_getParam('pk');
                // $data['cactivo'] = 'true';
                // $emitido = $this->usuvehiculossorteos->updateRow($pk, $data);
                $this->usuvehiculossorteos->updateEstadoCarnet($pk, 'true');
	}

	public function desactivarAction(){
            	$this->SwapBytes_Ajax->setHeader();
                $pk = $this->_getParam('pk');
                // $data['cactivo'] = 'false';
                $this->usuvehiculossorteos->updateEstadoCarnet($pk, 'false');
                // $emitido = $this->usuvehiculossorteos->updateRow($pk, $data);
	}

        public function imprimirAction(){
            //$this->SwapBytes_Ajax->setHeader();
                $pk = $this->_getParam('pk');
                $per = $this->periodos->getUltimo();
                $pku = $this->usuvehiculossorteos->getCiFromSorteo($pk);
                $tipo = $this->usuvehiculossorteos->getSorteoTipo($pk);
                $data['emitido'] = 'true';
                $emitido = $this->usuvehiculossorteos->updateRow($pk, $data);
                //var_dump($tipo);
                $pks = $this->_params['redirect']['fk_ugs'];
                $administrativo = $this->sorteo->checkifisAdministrativo($pks);
                //var_dump($administrativo);
                if($administrativo[0]['administrativo'] == false){

                    if($tipo[0]['fk_tiposorteo'] == 9687){//es moto

                    $report = APPLICATION_PATH . '/modules/reports/templates/Constancias/pase_estacionamiento_motos.jasper';

                    }else if($tipo[0]['fk_tiposorteo'] == 9688){ //es carro

                    $report = APPLICATION_PATH . '/modules/reports/templates/Constancias/pase_estacionamiento.jasper';

                    }
                    
                }else{

                    $report = APPLICATION_PATH . '/modules/reports/templates/estacionamiento/pase_administrativo.jasper';
                }

                
                //9687 motos
                //9688
                $this->logger->log($pku,ZEND_LOG::INFO);
                $config = Zend_Registry::get('config');

                        $dbname = $config->database->params->dbname;
                        $dbuser = $config->database->params->username;
                        $dbpass = $config->database->params->password;
                        $dbhost = $config->database->params->host;
                        
                        $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
                        $filename    = 'ConstanciaSolicitud';
                        $filetype    = 'PDF';//strtolower($Params['rdbFormat']);

                        $params      = "'ci=string:{$pku[0]['fk_usuario']}|Periodo=string:{$per}'";
                        $cmd         = "java -jar -Djava.awt.headless=true " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                        // local -Djava.awt.headless=true
                    //  echo $cmd;
                Zend_Layout::getMvcInstance()->disableLayout();
                Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype }");
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );

//    echo $per;
                 $outstream = exec($cmd);
                 echo base64_decode($outstream);



        }

        public function imprimirselectedAction(){
            $this->SwapBytes_Ajax->setHeader();
            $turno = $this->_getParam('turno');
            $sorteo = $this->_params['redirect']['fk_ugs'];
            $config = Zend_Registry::get('config');

                        $dbname = $config->database->params->dbname;
                        $dbuser = $config->database->params->username;
                        $dbpass = $config->database->params->password;
                        $dbhost = $config->database->params->host;
                        $report = APPLICATION_PATH . '/modules/reports/templates/estacionamiento/gsorteo.jasper';
                        $filename    = 'Listado_ganadores';
                        $filetype    = 'PDF';
                        $params      = "'sorteo=string:{$sorteo}|turno=string:{$turno}'";
                        $cmd         = "java -jar -Djava.awt.headless=true " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                        // local -Djava.awt.headless=true
                        //echo $cmd;
                Zend_Layout::getMvcInstance()->disableLayout();
                Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype }");
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );

//    echo $per;
                 $outstream = exec($cmd);
                 echo base64_decode($outstream);


        }


}


?>

