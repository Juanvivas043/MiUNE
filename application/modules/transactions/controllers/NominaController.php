<?php

class Transactions_NominaController extends Zend_Controller_Action {


    private $Title = 'Transacciones \ Recibos de Pago';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Forms_Changepass');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Tmprecibos');
      
        $this->usuarios        = new Models_DbTable_Usuarios();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->filtros         = new Une_Filtros();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->recibos = new Models_DbTable_tmprecibos();

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
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();

        $this->CmcBytes_Profit = new CmcBytes_Profit();
        $this->CmcBytes_Redirect = new CmcBytes_Redirect();

     //   $this->logger = Zend_Registry::get('logger');

       // $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        //$this->redirect_session = new Zend_Session_Namespace('redirect_session');

        //filtro
        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(true, true, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);



        $this->SwapBytes_Crud_Search->setDisplay(true);

        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, false, true, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, false, true, false, false, false);
        $regresar = "<button id='btnImp' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnReturn' role='button' aria-disabled='false'>Imprimir</button>";

        $this->SwapBytes_Crud_Action->addCustum($regresar);


        
        $this->view->form = new Forms_Changepass();
        $this->SwapBytes_Form->set($this->view->form);
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
        $this->_params['redirect'] = $this->redirect_session->params;

      //  $this->custom = $this->CmcBytes_Profit->getRecibosPagos($this->authSpace->userId);  

       $this->needsChangePass = $this->usuarios->samePassword($this->authSpace->userId);
        
    }



        function preDispatch() {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
                $this->_helper->redirector('index', 'login', 'default');
            }

            if (!$this->grupo->haveAccessToModule()) {
                $this->_helper->redirector('accesserror', 'profile', 'default');
            }
        }

        private function masterInfo($midata){



        $this->logger->log($data,ZEND_LOG::ALERT);

            //$info['sede'] = $this->vsede->getSedeName($info['sede']);
            //$info['escuela'] = $this->vescuelas->getEscuelaName($info['escuela']);
            $properties = array('width' => '700',
                                    'align' => 'center',
                                    'style' => 'margin-left:auto;margin-right:auto;');

            $styles = array(array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px'),
                            array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px')
                );

            $styles2 = array(array('style' => 'text-align:left;font-size:14px;font-weight:bold;width:160px;'),
                                array('style' => 'text-align:left;font-size:14px')
                );

            $data[] = array('Cargo:', $midata[0]['des_contrato'], 'C.I: ', $this->authSpace->userId);


            $html = $this->SwapBytes_Html->table($properties, $data, $styles);

            $data2[] = array('Pago Correspondiente:  ', $midata[0]['desde'] . ' al ' . $midata[0]['hasta']);

            $html .= $this->SwapBytes_Html->table($properties, $data2, $styles2);
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

            if ($this->needsChangePass[0]["pk_usuario"] == $this->authSpace->userId){
                $this->view->trigger = "changepass();";
            }

            

        }

        public function changepassAction(){
            
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Cancelar');
            $json[] = "$('.ui-dialog-titlebar-close').attr('hidden','hidden');";
            $json[] = "$('#frmModal' ).dialog( 'option', 'closeOnEscape', false );";
            $this->SwapBytes_Crud_Form->setJson($json);

            $this->SwapBytes_Crud_Form->setProperties($this->view->form, null, 'Cambiar Contrseña', 'Por su seguridad debe cambiar la contraseña');
            $this->SwapBytes_Crud_Form->getAddOrEditLoad();


        }


        public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

                      $dataRow = $this->_params["modal"];

                if($dataRow["passwordNew"] == $this->authSpace->userId){
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->cleanErrors();
                    $id = 'passwordNew';
                    $msg = 'La contraseña no debe ser la cedula';
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->displayError($msg, $id);
                    $this->getResponse()->setBody(Zend_Json::encode($json));
                    $this->SwapBytes_Crud_Form->setJson($json);
                }else{
                    $this->SwapBytes_Crud_Form->setJson($json);
                    $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Cambiar Contrseña', 'Por su seguridad debe cambiar la contraseña');
                    $this->SwapBytes_Crud_Form->getAddOrEditConfirm();

                }

                
		}
	}

        public function addoreditresponseAction() {
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();

                        $dataRow = $this->_params['modal'];
                        $ci = $this->authSpace->userId;
                        $this->usuarios->changePassword($ci, $dataRow["passwordNew"]);

			$this->SwapBytes_Crud_Form->getAddOrEditEnd();

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

            if(isset($this->custom)){
                //var_dump($this->custom);
                //$vad = $this->CmcBytes_Profit->getRecibosPagos(19606300);
                $json[] = $this->CmcBytes_Filtros->addCustom('Recibo - Fecha', $this->custom);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }

        public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json   = array();
            $searchData = $this->_getParam('buscar');

            //$this->usuvehiculossorteos->setSearch($searchData);

            $data = $this->_params['filters'];
            $rows = $this->CmcBytes_Profit->getReciboEspecifico($this->authSpace->userId, $data['Recibo - Fecha']);
            $info = $this->masterInfo($rows);

            $preloded = $this->recibos->getall($this->authSpace->userId);


            foreach($preloded as $load){

                $id = $load["pk_tmprecibopago"];
                $this->recibos->deleteRow($id);

            }
            

            foreach($rows as $row){

                $dataRow["cedula"] = $this->authSpace->userId;
                $dataRow["horas"] = $row["horas"];
                $dataRow["concepto"] = $row["cod_concepto"] . ' ' . $row["des_concepto"];
                $dataRow["asignacion"] = $row["asignacion"];
                $dataRow["deduccion"] = $row["deduccion"];
                $dataRow["recibo"] = $row["recibo"];
                $dataRow["contrato"] = $row["des_contrato"];
                $dataRow["fecha"] = $row["desde"] . ' al ' . $row["hasta"];
                

                if($dataRow["recibo"]){

                    $this->recibos->addRow($dataRow);
                }

            }


            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_usuariovehiculosorteo',
                                       'primary' => true,
                                        'hide' => true),

                                 array('name'    => '',
                                       'width'   => '30px',
                                       'column'  => 'cod_concepto',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Concepto',
                                       'width'   => '230px',
                                       'column'  => 'des_concepto',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Horas/Dias',
                                       'width'   => '80px',
                                       'column'  => 'horas',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Asignacion',
                                       'width'   => '120px',
                                       'column'  => 'asignacion',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Deduc/Reten',
                                       'width'   => '120px',
                                       'column'  => 'deduccion',
                                       'rows'    => array('style' => 'text-align:center'))

                                 );



                //$HTML2 = "<div style=\'border: 1px solid black;width:100px;height:100px;background-color:white;text-align:center;font-size:40px;\'>";
                //$HTML2 .= "<span style=\'display:table-cell; vertical-align:middle; text-align:center; font-size:18px\'>Disponibles:</span>{$puestos[0]['restantes']}";
                //$HTML2 .= "<span align=\'center\' style=\'float:left; margin-left:20px; display:table-cell; vertical-align:middle; text-align:center; font-size:18px\'>Puestos</span></div>";

                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'O',$other);

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML) . ";";
                //$json[] = $this->SwapBytes_Jquery->setHtml('puestoscant', $HTML2);
                $json[] = $this->SwapBytes_Jquery->setHtml('under', $info);
                $json[] = "$('.tabledata').children().children().last().children(':eq(2)').html('<center><b>TOTAL</b></center>');";
                $json[] = "$('.tabledata').children().children().last().children(':eq(3)').html('{$rows[count($rows)]['total_acu']}');";
                $json[] = "$('.tabledata').children().children().last().children(':eq(4)').html('{$rows[count($rows)]['total_ded']}');";
                $json[] = "$('#undertable').html('<br><center><b>Neto a Pagar: {$rows[count($rows)]['neto']}</b></center>')";
                $this->logger->log($rows,ZEND_LOG::INFO);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkRecordAcademico');

            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen inscritos en el sorteo para este turno.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));

        }

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

        public function imprimirAction(){
            //$this->SwapBytes_Ajax->setHeader();
                $pk = $this->authSpace->userId;
                
                
                $config = Zend_Registry::get('config');

                        $dbname = $config->database->params->dbname;
                        $dbuser = $config->database->params->username;
                        $dbpass = $config->database->params->password;
                        $dbhost = $config->database->params->host;
                        //$xml = APPLICATION_PATH . '/../public/tempxml/' . $pk . '.xml';
                        $report = APPLICATION_PATH . '/modules/reports/templates/Administrativo/nomina_recibo.jasper';
                        $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
                        $filename    = 'Recibo';
                        $filetype    = 'PDF';//strtolower($Params['rdbFormat']);

                        $params      = "'logo=string:{$imagen}|cedula=string:{$pk}'";
                        $cmd         = "java -jar -Djava.awt.headless=true " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                        // local -Djava.awt.headless=true
                        //echo $cmd;
                Zend_Layout::getMvcInstance()->disableLayout();
                Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype }");
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
                 $outstream = exec($cmd);
                 echo base64_decode($outstream);


        }



}


?>

