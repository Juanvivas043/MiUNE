<?php

class Reports_ReportetxtvenezuelaController 	extends Zend_Controller_Action {
	
	private $Title = 'Transacciones \ Reporte Txt Banco Venezuela';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
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

        $this->logger = Zend_Registry::get('logger');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        //$this->redirect_session = new Zend_Session_Namespace('redirect_session');

        $this->SwapBytes_Crud_Search->setDisplay(false);

        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, false, false, false, false, false);
	    $this->SwapBytes_Crud_Action->setEnable(true, false, false, false, false, false);

        $descargar = "<button id='btnImp' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnImp' role='button' aria-disabled='false'>Descargar</button>";
        $this->SwapBytes_Crud_Action->addCustum($descargar);

        $this->SwapBytes_Form->set($this->view->form);
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
        $this->_params['redirect'] = $this->redirect_session->params;

        //$this->custom = $this->CmcBytes_Profit->getRecibosPagos($this->authSpace->userId);

        $this->custom = $this->CmcBytes_Profit->getFechaPagoNomina();
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
            $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
            $this->view->search_span           = 2;
            $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
            $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();            

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
            if(!is_string($this->custom)){
                if(isset($this->custom)){
                    $json[] = $this->CmcBytes_Filtros->addCustom('Recibo - Fecha', $this->custom, "FechasNominas");
                }
            }else{
                $HTML  = $this->SwapBytes_Html_Message->alert("Necesita estar en la red interna de la Universidad.");
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }

	/** Si no lista la nomina, revisar las fe_ult de sncont en UNE_N1 y ver si ya estan empezando a expirar las fechas **/

        public function listAction() {

            $text = "";
            $totalnomina = 0;
            $espaciosEnBlanco = "  ";

        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $data = $this->_params['filters'];

            $parametros = explode(" ", $data["Recibo - Fecha"]);

            $rows = $this->CmcBytes_Profit->getDatosPersonal($parametros[0], $parametros[1]);

            /* PROCESO PARA GENERAR EL ARCHIVO. DANIEL CASTRO*/

            $date = date( "d/m/y", strtotime($parametros[1]) );
            //Construye el numero relativo del archivo
            $correlativo = str_pad($parametros[2], 2, "0", STR_PAD_LEFT);
            $numerodecuenta = str_pad("01020448800000005791", 20, "0", STR_PAD_LEFT);

            //Suma el total de la nomina sumando todos los sueldos que vienen de profit
            foreach($rows as $index => $value){ $totalnomina += $value["montoneto"]; }
            $totalnomina = number_format($totalnomina,2,',','');


            //Elimina el punto de coma del monto
            $totalnominatxt = str_replace(",", "", $totalnomina);

            //Rellena con 0 hasta llenar 13 numeros
            $totalnominatxt = str_pad($totalnominatxt, 13, "0", STR_PAD_LEFT);


            //Se crea el header del documento
            $header = "HUNIVERSIDAD NUEVA ESPARTA               " . $numerodecuenta . $correlativo . $date . $totalnominatxt ."03291 ";
            $text .= $header . "\n"; //Se agrega de primero en el archivo

            foreach($rows as $index => $value){

                //Se crea una linea por cada linea que venga de la base de datos.
		unset($nombre);
		unset($new_array);
                $value["cta_banc"] = str_replace(" ", "", $value["cta_banc"]);
                $value["cod_emp"] = str_replace(" ", "", $value["cod_emp"]);
                $montoneto = str_replace(".", "", $value["montoneto"]);
                $montoneto = str_pad($montoneto, 11, "0", STR_PAD_LEFT);
                $nombre40 = substr($value["beneficiario"], 0, 40);
		$array_nombre = str_split($nombre40);
		foreach ($array_nombre as $char){
			if(ord($char)==195){
				$new_array[] = chr(209);
			}elseif(ord($char)==145){
				$new_array[] = "";
			}else{
				$new_array[] = $char;
			}
		}
		foreach ($new_array as $char){
			$nombre .= $char;
		}	
                $nombre = str_pad($nombre, 40, " ", STR_PAD_RIGHT);
		
                $cedula = str_pad($value["cod_emp"], 10, "0", STR_PAD_LEFT);
                //Se crea la linea concatenando todos los datos
                $text1 = $value["id_reg"] . $value["cta_banc"] . $montoneto . $value["tipo_cta"] . $nombre . $cedula . $value["serial"] . $espaciosEnBlanco;
                $text .= $text1 . chr(10); //Se agrega al documento y un espacio hacia abajo.
            }

            //Se crean 3 variables temporales. La primera almacena lo que tendra el archivo txt
            
            $this->createVarTemp("text", $text);
            $this->createVarTemp("fecha_emis", $parametros[1]);
            $this->createVarTemp("fecha_inic", $parametros[0]);

            /*----*/

            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('name'    => 'Cuenta Bancaria',
                                       'width'   => 'auto',
                                       'column'  => 'cta_banc',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Id. de Registro',
                                       'width'   => 'auto',
                                       'column'  => 'id_reg',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Tipo de Cuenta',
                                       'width'   => 'auto',
                                       'column'  => 'tipo_cta',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Serial',
                                       'width'   => 'auto',
                                       'column'  => 'serial',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Beneficiario',
                                       'width'   => 'auto',
                                       'column'  => 'beneficiario',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Cédula',
                                       'width'   => 'auto',
                                       'column'  => 'cod_emp',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Monto Neto',
                                       'width'   => 'auto',
                                       'column'  => 'montoneto',
                                       'rows'    => array('style' => 'text-align:center'))
                                 );

                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML) . ";";
                $this->logger->log($rows,ZEND_LOG::INFO);
		$totalnomina	=	str_replace(",",".",$totalnomina);
                $json[] = "$('#montoTotal').html('<span style=\'font-weight: normal\'>Monto total: </span>". number_format( $totalnomina,2) . "')";

            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen registros de nomina.");
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));

        }

    }

        public function descargarAction(){
                $this->SwapBytes_Ajax->setHeader();

                $fecha_emis = $_SESSION["fecha_emis"];
                $fecha_inic = $_SESSION["fecha_inic"];

                $text = $this->_params['text'];
                $string = $_SESSION["text"];

                Zend_Layout::getMvcInstance()->disableLayout();
                Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "text/html");
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename=Nomina-$fecha_inic-$fecha_emis.txt" );

                echo $string;
                $this->deleteTemp("fecha_emis");
                $this->deleteTemp("fecha_inic");
                $this->deleteTemp("text");
        }

        private function createVarTemp($name, $value){
            $_SESSION[$name] = $value;
        }

        private function deleteTemp($name){
            unset($_SESSION[$name]);
        }


}
