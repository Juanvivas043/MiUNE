<?php

class Reports_SolicitudesretirosController extends Zend_Controller_Action {


    private $Title = 'Transacciones \ Gestion de solicitudes de Retiros';

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
        Zend_Loader::loadClass('Forms_Solicitudretiromateria');

        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->ugs             = new Models_DbTable_UsuariosGruposSolicitudes();
        $this->estudiante      = new Models_DbTable_Usuarios();
        $this->recordacademico = new Models_DbTable_Recordsacademicos();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->calendarios     = new Models_DbView_Calendarios();
        $this->inscripciones   = new Models_DbTable_Inscripciones();
        $this->mar   = new Models_DbTable_Materiasaretirar();

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
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();
        
        $this->logger = Zend_Registry::get('logger');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');

        //filtro
        //$this->filtros->setDisplay(true, false, false, false, false, false, false, false, false);
        //$this->filtros->setDisabled(false, false, true, true, true, true, true, true, true);
        //$this->filtros->setRecursive(true, false, false, false, false, false, false, false, false);

        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, false, true, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, false, true, false, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(true);


        $this->view->form = new Forms_Solicitudretiromateria();

        $this->SwapBytes_Form->set($this->view->form);
      //  $this->SwapBytes_Form->fillSelectBox('fk_tipo', $this->atributos->getTipes(39,'8266') , 'pk_atributo', 'valor');


        $this->view->form = $this->SwapBytes_Form->get();

        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
        $this->_params['redirect'] = $this->redirect_session->params;

        $this->FormTitle_Info = 'Realizar Solicitud';

        $this->tablas = Array('periodo'  => Array('tbl_periodos',
                                                  null,
                                                  Array('pk_periodo',
                                                        'lpad(pk_periodo::text, 4, \'0\') || \', \' || to_char(fechainicio, \'MM-yyyy\') || \' / \' ||  to_char(fechafin, \'MM-yyyy\')'),
                                                  'DESC'),
                              'sede'    => Array('vw_sedes',
                                                 null     ,
                                                 Array('pk_estructura',
                                                       'nombre'),
                                                 'DESC'),
                              'estado'  => Array('tbl_atributos',
                                                 //array('fk_atributotipo = 70'), //local
                                                 array('fk_atributotipo = 41'),
                                                 array('pk_atributo',
                                                       'valor')

                                                )
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


        public function indexAction() {


            $this->view->title   = $this->Title;
            $this->view->filters = $this->filtros;
            $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
            $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
            $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
            $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
            $this->view->search_span           = 2;
            $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
            $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
            $this->view->SwapBytes_Ajax->setView($this->view);
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

            //$Data   = $this->filtros->getParams();
            $json   = array();
            $other = $this->_params['filters'];
            $this->logger->log($other,ZEND_LOG::WARN);
            $searchData = $this->_getParam('buscar');
            $this->ugs->setSearch($searchData);
            $rows = $this->ugs->getSolicitudesRetiroMateriasCDE($other['estado'],$other['periodo']);



            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_usuariogruposolicitud',
                                       'primary' => true,
                                        'hide' => true),

                                 array('name'     => '#',
                                       'width'    => '10px',
                                       'function' => 'rownum',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Número',
                                       'width'   => '60px',
                                       'column'  => 'pk_usuariogruposolicitud',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Cédula',
                                       'width'   => '80px',
                                       'column'  => 'pk_usuario',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Nombre',
                                       'width'   => '200px',
                                       'column'  => 'name',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Apellido',
                                       'width'   => '200px',
                                       'column'  => 'apellido',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Tipo',
                                       'width'   => '120px',
                                       'column'  => 'valor',
                                       'rows'    => array('style' => 'text-align:center')),
                                 
                                 array('name'    => 'Escuela',
                                       'width'   => '120px',
                                       'column'  => 'escuela',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Semestre',
                                       'width'   => '80px',
                                       'column'  => 'semubic',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Fecha',
                                       'width'   => '70px',
                                       'column'  => 'fechasolicitud')
                                 );




                $other = Array(
                          Array( 'actionName' => '',
                                'action' => 'imprimir(##pk##)',
                                'label' => 'Imprimir',
                                'column' => 'estado',
                                'validate' => 'true',
                                'intrue' => 'Completada',
                                'intruelabel' => ''
                                )
                );


                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'OO',$other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkClase');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen solicitudes de retiro de materias o semestre que mostrar.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }


            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }


        public function preprintAction(){
            $this->SwapBytes_Ajax->setHeader();
            $pk = $this->_getParam('pk');
            $data['fk_impreso'] = 8248;
            $this->ugs->updateRow($pk, $data);
             $json[] = "$('#btnList').trigger('click')";
             //$json[] = "console.log('pipipipi');";
             //$json[] = "reporte($pk);";
             //$json[] = "window.location.href = urlAjax + 'imprimir/pk/' + $pk;";
                $this->getResponse()->setBody(Zend_Json::encode($json));
             
        }

        public function imprimirAction(){
            //$this->SwapBytes_Ajax->setHeader();
                $pk = $this->_getParam('pk');
                //$data['fk_impreso'] = 12131; //local
                $data['fk_impreso'] = 8248;
                $this->ugs->updateRow($pk, $data);
                $json[] = "$('#btnList').trigger('click');";
                $this->getResponse()->setBody(Zend_Json::encode($json));
                $config = Zend_Registry::get('config');

                        $dbname = $config->database->params->dbname;
                        $dbuser = $config->database->params->username;
                        $dbpass = $config->database->params->password;
                        $dbhost = $config->database->params->host;
                        $report = APPLICATION_PATH . '/modules/reports/templates/retiromaterias/retiromaterias_cde.jasper';
                        $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
                        $filename    = 'ConstanciadeRetiro';
                        $filetype    = 'PDF';//strtolower($Params['rdbFormat']);

                        $params      = "'Solicitud=string:{$pk}|Imagen=string:{$imagen}'";
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

