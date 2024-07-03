<?php

class Transactions_DocumentosasolicitarController extends Zend_Controller_Action {


    private $Title = 'Transacciones \ Solicitud de Documentos';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGruposSolicitudes');
        Zend_Loader::loadClass('Forms_Documentossolicitados');
        Zend_Loader::loadClass('Models_DbTable_Documentossolicitados');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Periodos');

        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->ugs           = new Models_DbTable_UsuariosGruposSolicitudes();
        $this->inscripciones = new Models_DbTable_Inscripciones;
        $this->ds            = new Models_DbTable_Documentossolicitados;
        $this->periodos       = new Models_DbTable_Periodos;
        $this->filtros         = new Une_Filtros();

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

        //filtro
        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(true, true, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);

        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, false, false, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, false, false, false, false, false);

        $this->SwapBytes_Crud_Search->setDisplay(false);

        $Regreso = "<button id='btnReturn' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnReturn' role='button' aria-disabled='false'>Regresar";

        $this->SwapBytes_Crud_Action->addCustum($Regreso);

        $this->view->form = new Forms_Documentossolicitados();

        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->filtros->getParams();
        $this->_params['redirect'] = $this->redirect_session->params;
    }



    function preDispatch() {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
                $this->_helper->redirector('index', 'login', 'default');
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

        private function masterInfo(){


            $info = $this->ugs->getSolicitudesFilter($this->redirect_session->params['fk_ugs']);
            //$info = $this->redirect_session->params;
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

            $data[] = array('N˚ de Solicitud: ', $info[0]['pk_usuariogruposolicitud'], 'Nombre:', $info[0]['nombre'], 'Apellido: ', $info[0]['apellido'],'Periodo: ', $info[0]['fk_periodo']);


            $html  = $this->SwapBytes_Html->table($properties, $data, $styles);

            $data2[] = array('Sede:', $info[0]['sed'],'Escuela:', $info[0]['escuela'],'Semestre:', $info[0]['sem_ubic']);

            $html .= $this->SwapBytes_Html->table($properties, $data2, $styles);
            return $html;
        }

        public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $Data   = $this->filtros->getParams();
            $json   = array();
            $other = array('cedula' => $this->authSpace->userId,
                            'grupo' => 855);

            $rows = $this->ds->getDocsSolicitud($this->_params['redirect']['fk_ugs']);
            //$this->logger->log($rows,ZEND_LOG::ALERT);

            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_documentosolicitado',
                                       'primary' => true,
                                        'hide' => true),

                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'function' => 'rownum',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Documento',
                                       'width'   => '180px',
                                       'column'  => 'documento',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Estado',
                                       'width'   => '70px',
                                       'column'  => 'estado',
                                       'rows'    => array('style' => 'text-align:center'))
                                 );



                $other = Array(
                          Array( 'actionName' => '',
                                'action' => 'imprimir(##pk##)',
                                'label' => 'Imprimir Solicitud',) 
                    );


                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VO',$other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkClase');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No Existen documentos en la solicitud");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }


            $this->getResponse()->setBody(Zend_Json::encode($json));

        }

    }

        public function getInfo($pk){

            $rows = $this->ds->getInfoDoc($pk);

            $properties = array('width' => '280',
                                    'align' => 'center');
            $styles = array(array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px'));


                $data[] =Array('Documento: ',$rows[0]['documento']);
                $data[] =Array('Estado: ',$rows[0]['estado']);

            $html .= $this->SwapBytes_Html->table($properties, $data, $styles);

            $js .= $this->SwapBytes_Jquery->setHtml('documentos', $html) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 160) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 300) .';';
            $js .= $this->SwapBytes_Jquery_Ui_Form->setCenter('frmModal') . ';';
            return $js;

        }

        public function viewAction() {

             $dataRow = $this->_params['modal'];
             $json[] = $this->getInfo($dataRow['id']);
             //$this->logger->log($dataRow,ZEND_LOG::WARN);
             //$json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Retirar').';';
             //$json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Deshacer').';';

             $this->SwapBytes_Crud_Form->setJson($json);

             $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Estado del Documento');
             $this->SwapBytes_Crud_Form->getView();
        }

        public function regresoAction(){
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();
                $this->redirect_session->unsetAll();

                $data = Array('module' => 'transactions',
                              'controller' => 'solicituddocumentos',
                              'params' => Array('set' => 'true',
                                                'action' => 'listar'
                                                ));



                $json[] = $this->CmcBytes_Redirect->getRedirect($data);
                $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }

         public function imprimirAction(){
            //$this->SwapBytes_Ajax->setHeader();
                $pk = $this->_getParam('pk');
                
                $config = Zend_Registry::get('config');

                        $dbname = $config->database->params->dbname;
                        $dbuser = $config->database->params->username;
                        $dbpass = $config->database->params->password;
                        $dbhost = $config->database->params->host;
                        $report = APPLICATION_PATH . '/modules/reports/templates/Constancias/constancia_solicitudes.jasper';
                        $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
                        $filename    = 'ConstanciaSolicitud';
                        $filetype    = 'PDF';//strtolower($Params['rdbFormat']);

                        $params      = "'documentosolicitado=string:{$pk}|Imagen=string:{$imagen}'";
                        $cmd         = "java -jar -Djava.awt.headless=true " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                        // local -Djava.awt.headless=true
//                        echo $cmd;die;
                Zend_Layout::getMvcInstance()->disableLayout();
                Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype }");
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );


                $outstream = exec($cmd);
                echo base64_decode($outstream);


        }
}


?>

