<?php

class Transactions_CertificadocompetenciadirectorescuelaController extends Zend_Controller_Action {

    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Certificadocompetencia');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');

        $this->Certificadocompentencia = new Models_DbTable_Certificadocompetencia();
        $this->filtros = new Une_Filtros();
        $this->atributos = new Models_DbTable_Atributos();
        $this->periodos = new Models_DbTable_Periodos();
        $this->sedes = new Models_DbTable_Estructuras();
        $this->escuelas = new Models_DbTable_EstructurasEscuelas();
        $this->grupo = new Models_DbTable_UsuariosGrupos();

        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri = new SwapBytes_Uri();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->_params['filters'] = $this->filtros->getParams();

        $this->filtros->setDisplay(true, true, true, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, true, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(true, true, true, false, false, false, false, false, false);
//		$this->filtros->setType('seccion', FILTER_TYPE_SECCION_PADRES);

        $this->SwapBytes_Crud_Action->setDisplay(true, true);
        $this->SwapBytes_Crud_Action->setEnable(true, true);
        $this->SwapBytes_Crud_Search->setDisplay(false);
        
        $this->logger = Zend_Registry::get('logger');
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
        function preDispatch() {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
                $this->_helper->redirector('index', 'login', 'default');
            }

            if (!$this->grupo->haveAccessToModule()) {
                $this->_helper->redirector('accesserror', 'profile', 'default');
            }
        }

    public function indexAction() {
        $this->view->title = "Transacciones \ Certificación de Competencia";
        $this->view->filters = $this->filtros;
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
    }

    public function periodoAction() {
        $this->filtros->getAction();
    }

    public function sedeAction() {
        $this->filtros->getAction(array('periodo'));
    }

    public function escuelaAction() {
        
        $ci = $this->authSpace->userId;
        $grupos = $this->Certificadocompentencia->getGrupos($ci);
        $atributo_dir_esc = $this->Certificadocompentencia->getPkAtributo();
        $escuelas = array();
        $dataRows = array();  
        
        foreach ($grupos as $grupo){
            if($grupo['fk_grupo'] == $atributo_dir_esc[1]['pk_atributo']){
                array_push($escuelas,array('11','Administración'));
            }
            if($grupo['fk_grupo'] == $atributo_dir_esc[3]['pk_atributo']){
                array_push($escuelas,array('12','Computación'));
            }
            if($grupo['fk_grupo'] == $atributo_dir_esc[4]['pk_atributo']){
                array_push($escuelas,array('13','Administración de Empresas de Diseño'));
            }
            if($grupo['fk_grupo'] == $atributo_dir_esc[2]['pk_atributo']){
                array_push($escuelas,array('14','Ingeniería Civil'));
            }
            if($grupo['fk_grupo'] == $atributo_dir_esc[5]['pk_atributo']){
                array_push($escuelas,array('15','Ingeniería Electrónica'));
            }
            if($grupo['fk_grupo'] == $atributo_dir_esc[6]['pk_atributo']){
                array_push($escuelas,array('16','Administración de Empresas de Turísticas'));
            }
        }
        
        For ($i=0;$i<count($escuelas);$i++){
            array_push($dataRows,array($escuelas[$i][0],$escuelas[$i][1]));
        }
        
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
        
    }

    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
            $json = array();

//            $HtmlObjectName = 'usuario';
            $ra_data = $this->Certificadocompentencia->getSolicitudesCertificado($this->_params['filters']['periodo'], $this->_params['filters']['sede'], $this->_params['filters']['escuela']);

            // Definimos las propiedades de la tabla.
            $ra_property_table = array('class' => 'tableData',
                                       'width' => '1100px',
                                       'column' => 'disponible');

            $ra_property_column = array(array('column'   => 'codigo',
                                              'primary'  => true,
                                              'hide'     => true),
                                        array('name'     => '#',
                                              'width'    => '20px',
                                              'function' => 'rownum',
                                              'rows'     => array('style' => 'text-align:center')),
                                        array('name'     => 'C.I.',
                                              'column'   => 'ci',
                                              'width'    => '70px',
                                              'rows'     => array('style' => 'text-align:center')),
                                        array('name'     => 'Nombre',
                                              'column'   => 'nombre',
                                              'width'    => '300px',
                                              'rows'     => array('style' => 'text-align:center')),
                                        array('name'     => 'Apellido',
                                              'column'   => 'apellido',
                                              'width'    => '200px',
                                              'rows'     => array('style' => 'text-align:center')),
                                        array('name'     => 'Estado',
                                              'column'   => 'estado',
                                              'width'    => '250px',
                                              'rows'     => array('style' => 'text-align:center')),
                                        );
            
            $other = array(array('actionName'  => 'estado',
                                 'action'      => 'myestado(##pk##)',
                                 'label'       => 'Aprobar',
                                 'column'      => 'estado',
                                 'validate'    => 'true',
                                 'intrue'      => 'Solicitado',
                                 'intruelabel' => 'Desaprobar'));
            
            $HTML = $this->SwapBytes_Crud_List->fill($ra_property_table, $ra_data, $ra_property_column, 'O', $other);   

            }
            
            $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
    }

    public function estadoAction() {

        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
            $id = $this->_getParam('id');
            $pk_documento = $this->Certificadocompentencia->getPkDocumentoSolicitado($id);
            $estado = $this->Certificadocompentencia->getEstadoDocumento($pk_documento[0]['pk_documentosolicitado']);
            $atributo_dir_esc = $this->Certificadocompentencia->getPkAtributo();
            
            if($estado[0]['fk_estado'] == $atributo_dir_esc[9]['pk_atributo']){
                $this->Certificadocompentencia->updateEstadoDocumento($atributo_dir_esc[0]['pk_atributo'],$pk_documento[0]['pk_documentosolicitado']);
            }elseif($estado[0]['fk_estado'] == $atributo_dir_esc[0]['pk_atributo']){
                $this->Certificadocompentencia->updateEstadoDocumento($atributo_dir_esc[9]['pk_atributo'],$pk_documento[0]['pk_documentosolicitado']);
            }
            
//            if($estado[0]['fk_estado'] == 14146){
//                $this->Certificadocompentencia->updateEstadoDocumento(14145,$pk_documento); 
//            }elseif($estado[0]['fk_estado'] == 14145){
//                $this->Certificadocompentencia->updateEstadoDocumento(14146,$pk_documento);    
//            }
            
        }

    }  
    
}

    