<?php
class Consultas_NuevoingresoController extends Zend_Controller_Action {


    private $_Title   = 'Transacciones \ Lista de Usuarios de MiUNE';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');

        $this->inscripciones    = new Models_DbTable_Inscripciones();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();

        $this->filtros          = new Une_Filtros();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
	$this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
	$this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
	$this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
	$this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
	$this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
	$this->SwapBytes_Date           = new SwapBytes_Date();
	$this->SwapBytes_Form           = new SwapBytes_Form();
	$this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
	$this->SwapBytes_Uri            = new SwapBytes_Uri();
	$this->SwapBytes_Jquery         = new SwapBytes_Jquery();
	$this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->AuthSpace                = new Zend_Session_Namespace('Zend_Auth');
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();


        $imprimir = "<button id='btnImp' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnImp' role='button' aria-disabled='false'>Imprimir</button>";
        $this->SwapBytes_Crud_Action->addCustum($imprimir);


        $this->tablas = Array('periodo'  => Array('tbl_periodos',
                                                  null,
                                                  Array('pk_periodo',
                                                        'lpad(pk_periodo::text, 4, \'0\') || \', \' || to_char(fechainicio, \'MM-yyyy\') || \' / \' ||  to_char(fechafin, \'MM-yyyy\')'),
                                                  'DESC'),
                               'sede'    => Array('vw_sedes',
                                                  null,
                                                  Array('pk_estructura',
                                                        'nombre'),
                                                  )
                                );

        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));


        $this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, true, true, true, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(true);

    }

      function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
        if (!$this->grupo->haveAccessToModule()) {
      	  $this->_helper->redirector('accesserror', 'profile', 'default');
      	}
    }


     public function indexAction() {

        $this->view->title                 = $this->_Title;
        $this->view->filters               = $this->filtros;
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
        $this->SwapBytes_Ajax_Action       = new SwapBytes_Ajax_Action();
	$this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
	$this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
	$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
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

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }


    public function listAction() {

     if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();


            $json   = array();
            $periodo = $this->_params['filters']['periodo'];
            $sede = $this->_params['filters']['sede'];
            //var_dump($this->_params['filters']);

            $rows = $this->inscripciones->getCuadroNuevoIngreso($periodo, $sede);

            foreach($rows as $r){

                $prueba = $prueba + $r['prueba'];
                $regular = $regular + $r['regular'];
                $total = $total + $r['prueba'] + $r['regular'];

            }

            //$rows = $this->Usuarios->getUsuariosNI($itemPerPage, $pageNumber, $periodo, $escuela, $sede);


            $this->usuariosdatos;
            if(isset($rows) && count($rows) > 0) {


// Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                           'width' => '600px');

            $columns = array(array('name'    => 'Escuela',
                                       'width'   => '220px',
                                       'column'  => 'escuela',
                                       'rows'    => array('style' => 'text-align:left')),
                             array('name'    => 'Primer Semestre Directo',
                                   'width'   => '200px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'regular'),
                             array('name'    => 'Diagnostico',
                                   'width'   => '200px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'prueba'),
                             array('name'    => 'Total',
                                   'width'   => '200px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'total')

                );


                $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, null, null, null, null);

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkUsuario');
                $json[] = "$('#tableData').children().children().children().last().after('<tr><td><b>TOTALES</b></td><td style=\'text-align:center\'><b>". $regular ."</b></td><td style = \'text-align:center\'><b>" . $regular . "</b></td><td style = \'text-align:center\'><b>" . $total . "</b></td></tr>')";
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen Usuario cargados.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }

            public function imprimirAction(){
            //$this->SwapBytes_Ajax->setHeader();
                //$pk = $this->_getParam('filtros');
                $values = $this->SwapBytes_Uri->queryToArray($this->_getParam('filtros'));
                //var_dump($values);
                $config = Zend_Registry::get('config');
                        $dbname = $config->database->params->dbname;
                        $dbuser = $config->database->params->username;
                        $dbpass = $config->database->params->password;
                        $dbhost = $config->database->params->host;
                        $report = APPLICATION_PATH . '/modules/reports/templates/nuevoingreso/cuadronuevoingreso.jasper';
                        $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
                        $filename    = 'NuevoIngreso';
                        $filetype    = 'PDF';//strtolower($Params['rdbFormat']);

                        $params      = "'Periodo=string:{$values['periodo']}|sede=string:{$values['sede']}|Imagen=string:{$imagen}'";
                        $cmd         = "java -jar -Djava.awt.headless=true " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                        // local -Djava.awt.headless=true
                        //echo $cmd;
                Zend_Layout::getMvcInstance()->disableLayout();
                Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype }");
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
//echo $cmd;
//var_dump($values);
                $outstream = exec($cmd);
                echo base64_decode($outstream);


        }

}
?>
