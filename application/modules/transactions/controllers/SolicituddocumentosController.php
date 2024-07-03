<?php

class Transactions_SolicituddocumentosController extends Zend_Controller_Action {


    private $Title = 'Transacciones \ Solicitud de Documentos';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGruposSolicitudes');
        Zend_Loader::loadClass('Forms_Solicituddocumentos');
        Zend_Loader::loadClass('Models_DbTable_Documentossolicitados');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbView_Calendarios');

        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->ugs           = new Models_DbTable_UsuariosGruposSolicitudes();
        $this->inscripciones = new Models_DbTable_Inscripciones;
        $this->ds            = new Models_DbTable_Documentossolicitados;
        $this->periodos       = new Models_DbTable_Periodos;
        $this->filtros         = new Une_Filtros();
        $this->calendarios     = new Models_DbView_Calendarios();
        
        
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
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();


        $this->CmcBytes_Profit = new CmcBytes_Profit();
        $this->CmcBytes_Redirect = new CmcBytes_Redirect();

        $this->logger = Zend_Registry::get('logger');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->redirect_session = new Zend_Session_Namespace('redirect_session');
        $this->documents = new Zend_Session_Namespace('documents');

        //filtro
        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(true, true, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);

        $this->SwapBytes_Crud_Search->setDisplay(false);

        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, false, true, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, false, true, false, false, false);


        $this->view->form = new Forms_Solicituddocumentos();

        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->filtros->getParams();
        $this->_params['redirect'] = $this->redirect_session->params;
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
            $this->view->SwapBytes_Ajax->setView($this->view);
            
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
            $json   = array();
            $other = array('cedula' => $this->authSpace->userId,
                            'grupo' => 855);

            $rows = $this->ugs->getSolicitudesDocumentos($other);

            //$this->logger->log($rows,ZEND_LOG::ALERT);

            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_usuariogruposolicitud',
                                       'primary' => true,
                                        'hide' => true),

                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'function' => 'rownum',
                                       'rows'    => array('style' => 'text-align:right')),
                                 array('name'    => 'Número',
                                       'width'   => '80px',
                                       'column'  => 'pk_usuariogruposolicitud',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Periodo',
                                       'width'   => '70px',
                                       'column'  => 'periodo',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Sede',
                                       'width'   => '70px',
                                       'column'  => 'sede',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Número de Pago',
                                       'width'   => '120px',
                                       'column'  => 'numeropago',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Fecha',
                                       'width'   => '70px',
                                       'column'  => 'fechasolicitud'),
                                 array('name'    => 'Estado',
                                       'width'   => '70px',
                                       'column'  => 'estado',
                                       'rows'    => array('style' => 'text-align:center'))
                                 );


                $other = Array(
                         Array( 'actionName' => '',
                                'action' => 'documentos(##pk##)',
                                'label' => 'Documentos',
                                )
                );


                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VO',$other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkClase');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen solicitudes de documentos que mostrar.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }


            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
        
    }

        private function errorSolvente($msg = null){


            $html = "<div class=\'alert\'><div class=\'message\'>{$msg}</div></div>";

            $js .= $this->SwapBytes_Jquery->setHtml('error_helper', $html) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('pago-element','pago-label')) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->setCenter('frmModal') . ';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Aceptar').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Siguiente').';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Cancelar').';';

            return $js;
        }

        public function addoreditloadAction() {
            
		$ci = $this->authSpace->userId;

            $solvente = $this->CmcBytes_Profit->getSolvente($ci);
            $ultimoperiodo = $this->periodos->getUltimo();
//            $sede = $this->inscripciones->getUltimaSedeInscripcion($ci);
            $countdown = $this->calendarios->getDocCountdown($ultimoperiodo);
            //$solvente = true; //forzar valor a  solvente
            $json[] = "$('#frmModal').parent().find(\"button:contains('Guardar')\").children().html('Siguiente');";
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Siguiente').';';
            
            if($solvente == false){

            $this->logger->log('entreee',ZEND_LOG::INFO);
                $msg = "No se puede realizar la solicitud debido a que no se encuentra solvente administrativamente";
                $json[] = $this->errorSolvente($msg);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Siguiente').';';
            }elseif($countdown[0]['restante'] < 0){
                $msg = "No se puede realizar la solicitud, la fecha tope para la realizarla era el " . $countdown[0]['fin'];
                $json[] = $this->errorSolvente($msg);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Siguiente').';';
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar').';';

            }else{

                //$json[] = $this->getinfo(true);
                //$json[] = "$('#frmModal').parent().find(\"button:contains('Guardar')\").children().html('Siguiente');";
                //$json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Siguiente').';';

            }
            
                $this->SwapBytes_Crud_Form->setJson($json);
                
            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Realizar Solicitud');
            $this->SwapBytes_Crud_Form->getAddOrEditLoad();


        }

        public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
                $this->logger->log($this->_params['modal'],ZEND_LOG::INFO);
                $dataRow = $this->_params['modal'];
                
                
                $dataRow['pago'] = $this->documents->pago;
                

                $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Editar Actividad');
                $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
            }

        }

        public function addoreditresponseAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $today = date("Y-m-d");
            $ci = $this->authSpace->userId;
            $escuela = $this->inscripciones->getUltimaEscuelapk($this->authSpace->userId);
            $ultimoperiodo = $this->periodos->getUltimo();
            $sede = $this->inscripciones->getUltimaSedeInscripcion($this->authSpace->userId);
            

            $dataRow = $this->_params['modal']; 
           
            $dataRow['id'] = null;
            $dataRow['fk_usuariogrupo'] = $this->grupo->getPK($ci, 'AND fk_grupo = 855');
            $dataRow['hiddenerror_helper'] = null;
            $dataRow['fk_periodo'] = $ultimoperiodo;
            $dataRow['fk_estructura'] = $sede[0]['fk_estructura'];//$this->_params['filters']['sede'];
            $dataRow['fechasolicitud'] = $today;
            $dataRow['numeropago'] = $this->documents->pago;
            $dataRow['pago'] = null;
            $dataRow['fk_tipo'] = 8266;
            
            $agrego = false;
            
//            verifico que tenga algun documento, a pesar de que haya vacios
            $documentos = $this->documents->list;
         
               foreach($documentos as $doc){
                   
                    if($doc !== NULL){
                        $agrego = true;
                        break;
                    }

                }
            
              if($agrego === true){
                  $new_pk = $this->ugs->addRow($dataRow);
              }else{
                  die;
              }
              

            

            $pk_docs = $this->documents->list;
            $this->documents->unsetAll();
                foreach($pk_docs as $docs){

                    $dataDocs['fk_usuariogruposolicitud'] = $new_pk;
                    $dataDocs['fk_documento'] = $docs;

                    
                    $this->ds->addRow($dataDocs);
                }
                
            $data = Array('module' => 'transactions',
                          'controller' => 'Documentosasolicitar',
                          'params' => Array('set' => 'true',
                                            'action' => 'listar',
                                            'fk_ugs' => $new_pk));

            $this->SwapBytes_Crud_Form->getAddOrEditEnd($data);
            }
        }

        public function documentosAction(){
            if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $pk = $this->_getParam('pk');

            $data = Array('module' => 'transactions',
                          'controller' => 'Documentosasolicitar',
                          'params' => Array('set' => 'true',
                                            'action' => 'listar',
                                            'fk_ugs' => $pk));
            
            $json[] = $this->CmcBytes_Redirect->getRedirect($data);
            $this->getResponse()->setBody(Zend_Json::encode($json));

            }
        }

        public function existsAction(){
            if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $querystring = $this->_getParam('data');
            $params = $this->SwapBytes_Uri->queryToArray($querystring);
            $close = true;
            $set = false;
            $claimed = false;
            $Ci = $this->authSpace->userId;
            $ultimoperiodoinscrito = $this->inscripciones->getUltimoPeriodoInscripcion($Ci);
            $ultimoperiodo = $this->periodos->getUltimo();
            
            $existe = $this->ugs->getSolDocByPago($params['pago']);
            
            $this->logger->log($existe,ZEND_LOG::INFO);
            
            
            if(!isset($existe[0]['pk_usuariogruposolicitud'])){
//                $articulos = $this->CmcBytes_Profit->getArticulosPago(14952398,10016043); //prueba pago
//                $articulos = $this->CmcBytes_Profit->getArticulosPago(24671506,10015970); //prueba 2 constancias
//                $articulos = $this->CmcBytes_Profit->getArticulosPago(18493670,150162); //prueba 2 constancias
                  $articulos = $this->CmcBytes_Profit->getArticulosPago($Ci,$params['pago']);
                
                $claimed = false;
            }else{

                $claimed = true;
            }
            $span = 'font-weight:bold;font-size:16px;';
            $properties = array('width' => '340',
                                    'align' => 'center');

                $styles = array(array('style' => 'text-align:center;font-size:14px;'));
                
                
            if(isset($articulos) && $articulos != false){


                

                $data[] = array('Los siguientes documentos estan asociados al pago:');
                $data[] = array('');
                $data[] = array('');
                $data[] = array('');
                $data[] = array('');

                

                foreach($articulos as $art){
                    foreach($art as $t){

                    $set = true;
//                    verifica si es egresado el estudiante o no
                    if($ultimoperiodoinscrito < $ultimoperiodo){
//                    si lo es no podra descargar una constancia de estudios o actuacion estudiantil
                        SWITCH (trim($t)){

                            CASE 'CERTPENSUM':
                                $documento = 'Certificación de Pensum';
                                //$docs[] = 8270; //local
                                $docs[] = 8259;
                                //$html .= "<li>". $documento ."</li>";

                                break;
    //                        CASE 'CERTTITULO':
    //                            $documento = 'Certificación de Título';
    //                            $docs[] = 8271;
    //                            //$html .= "<li>". $documento ."</li>";
    //                            break;
                            CASE 'CERTPROGRAMAS':
                                $documento = 'Certificación de Programas';
                                //$docs[] = 8269; //local
                                $docs[] = 8258;
                                $html .= "<li>". $documento ."</li>";
                                break;
                            CASE 'CERTNOTAS':
                                $documento = 'Certificación de Notas';
                                //$docs[] = 8276; //local
                                $docs[] = 8265;
                                //$html .= "<li>". $documento ."</li>";
                                break;
                            CASE 'CONS-TRAMTITULO':
                                $documento = 'Constancia de Tramitación de Título';
                                //$docs[] = 8274;//local
                                $docs[] = 8263;
                                //$html .= "<li>". $documento ."</li>";
                                break;
                            CASE 'CONS-SOLOFTESIS':
                                $documento = 'Constancia Solo Falta tesis';
                                //$docs[] = 8275;//local
                                $docs[] = 8264;
                                //$html .= "<li>". $documento ."</li>";
                                break;

                            default :
                                $documento = '';
                                break;
                        }
                    }else{
                       SWITCH (trim($t)){

                        CASE 'CERTPENSUM':
                            $documento = 'Certificación de Pensum';
                            //$docs[] = 8270; //local
                            $docs[] = 8259;
                            //$html .= "<li>". $documento ."</li>";
                            
                            break;
//                        CASE 'CERTTITULO':
//                            $documento = 'Certificación de Título';
//                            $docs[] = 8271;
//                            //$html .= "<li>". $documento ."</li>";
//                            break;
                        CASE 'CERTPROGRAMAS':
                            $documento = 'Certificación de Programas';
                            //$docs[] = 8269; //local
                            $docs[] = 8258;
                            $html .= "<li>". $documento ."</li>";
                            break;
                        CASE 'CERTNOTAS':
                            $documento = 'Certificación de Notas';
                            //$docs[] = 8276; //local
                            $docs[] = 8265;
                            //$html .= "<li>". $documento ."</li>";
                            break;
                        CASE 'CONS-ESTUDIOS':
                            $documento = 'Constancia de Estudios';
                            //$docs[] = 8272;//local
                            $docs[] = 8261;
                            //$html .= "<li>". $documento ."</li>";
                            break;
                        CASE 'CONS-ACTESTU':
                            $documento = 'Constancia de Actuacion Estudiantil';
                            //$docs[] = 8273;//local
                            $docs[] = 8262;
                            //$html .= "<li>". $documento ."</li>";
                            break;
                        CASE 'CONS-TRAMTITULO':
                            $documento = 'Constancia de Tramitación de Título';
                            //$docs[] = 8274;//local
                            $docs[] = 8263;
                            //$html .= "<li>". $documento ."</li>";
                            break;
                        CASE 'CONS-SOLOFTESIS':
                            $documento = 'Constancia Solo Falta tesis';
                            //$docs[] = 8275;//local
                            $docs[] = 8264;
                            //$html .= "<li>". $documento ."</li>";
                            break;

                        default :
                            $documento = ''; 
                            break;
                        }
                    }
                    

                    if($documento != ''){
                        $data[] = array('<span style='. $span .'>'.$documento.'</span>');
                        $documento = '';
                    }
                    }

                    if($set !=true){
                        //$html = 'No existen documentos asociados al numero de pago';
                        //$data[] = array('No existen documentos asociados al numero de pago');
                        //$json[] = $this->SwapBytes_Jquery->setHtml('documentos', $html);
                    }
                }
                if($close != false && $set == true){

                
                $html  = $this->SwapBytes_Html->table($properties, $data, $styles);
                $json[] = $this->SwapBytes_Jquery->setHtml('documentos', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 370) .';';
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Siguiente').';';
                $json[] = "$('#pago-label').attr('style','margin-left: 20px; font-size:14px;');";
                $json[] = "$('#pago').attr('disabled','disabled');";
                $this->documents->pago = $params['pago'];
                

                }else {
                    $data2[] = array('No existen documentos asociados al numero de pago');

                $html  = $this->SwapBytes_Html->table($properties, $data2, $styles);
                $json[] = $this->SwapBytes_Jquery->setHtml('documentos', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 370) .';';
                $json[] = "$('#pago-label').attr('style','margin-left: 20px; font-size:14px;');";
                }


            }else{
                $styles = array(array('style' => 'text-align:center;font-size:14px;'));
                if($claimed == true){
                    $data2[] = array('Los documentos asociados al pago ya fueron solicitados.');
                }else{

                    $data2[] = array('No existen documentos asociados al pago.');
                }

                $html  = $this->SwapBytes_Html->table($properties, $data2, $styles);
                $json[] = $this->SwapBytes_Jquery->setHtml('documentos', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 370) .';';
                $json[] = "$('#pago-label').attr('style','margin-left: 20px; font-size:14px;');";
            }

            $this->documents->list = $docs;
            //$dataRow['doc_atrs'] = $docs;
            
            $this->getResponse()->setBody(Zend_Json::encode($json));


            }
        }

        public function viewAction(){
            $this->SwapBytes_Ajax->setHeader();
            $dataRow = $this->_params['modal'];

             

             $info = $this->ugs->getSolDocInfo($dataRow['id']);

             $span = 'font-weight:bold;font-size:16px;';
             $properties = array('width' => '240',
                                    'align' => 'center');
             $styles = array(array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px'));


                 $data[] = array('Periodo: ',$info[0]['periodo']);
                 $data[] = array('Sede: ',$info[0]['sede']);
                 $data[] = array('Número de Solicitud: ',$info[0]['pk_usuariogruposolicitud']);
                 $data[] = array('Fecha de Solicitud: ',$info[0]['fechasolicitud']);
                 $data[] = array('Número de Pago: ',$info[0]['numeropago']);
                 $data[] = array('Estado: ',$info[0]['estado']);

             $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(Array('pago-label','pago-element'));
             $html  = $this->SwapBytes_Html->table($properties, $data, $styles);
             $json[] = $this->SwapBytes_Jquery->setHtml('documentos', $html);
             $this->logger->log($info,ZEND_LOG::ALERT);

//             $json[] = $this->viewInfo($dataRow['id']);
             $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Siguiente').';';
             $this->SwapBytes_Crud_Form->setJson($json);

             $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Detalles de la Solicitud');
             $this->SwapBytes_Crud_Form->getView();
        }
        

}


?>
