<?php

class Transactions_GestiondesolicitudesController extends Zend_Controller_Action {


    private $Title = 'Transacciones \ Gestion de Solicitudes';
    private $FormTitle_Detalle = 'Ver informacion de Solicitud';
    
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
        


        //loaders de reportes:
        Zend_Loader::loadClass('Une_Cde_Reportes_CertificacionNotas');
        Zend_Loader::loadClass('Une_Cde_Reportes_CertificacionPensum');
        Zend_Loader::loadClass('Une_Cde_Reportes_ConstanciaDeEstudios');
        Zend_Loader::loadClass('Une_Cde_Reportes_ConstanciaActuacionEstudiantil');
        Zend_Loader::loadClass('Une_Cde_Reportes_ConstanciaSoloFaltaTesis');
        Zend_Loader::loadClass('Une_Cde_Reportes_ConstanciaTramitacionDeTitulo');
        Zend_Loader::loadClass('Une_Cde_Reportes_CertificacionProgramas');

        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->ugs           = new Models_DbTable_UsuariosGruposSolicitudes();
        $this->inscripciones = new Models_DbTable_Inscripciones;
        $this->ds            = new Models_DbTable_Documentossolicitados;
        $this->periodos       = new Models_DbTable_Periodos;
        $this->filtros         = new Une_Filtros();
        $this->calendarios     = new Models_DbView_Calendarios();
        $this->RecordAcademico     = new Models_DbTable_Recordsacademicos();
        $this->atributos       = new Models_DbTable_Atributos();
        
        
        //reportes:
        $this->certificacionnotas = new Une_Cde_Reportes_CertificacionNotas();
        $this->certificacionpensum = new Une_Cde_Reportes_CertificacionPensum();
        $this->constanciadeestudios = new Une_Cde_Reportes_ConstanciaDeEstudios();
        $this->constanciatramitaciondetitulo = new Une_Cde_Reportes_ConstanciaTramitacionDeTitulo();
        $this->constanciaactuacionestudiantil = new Une_Cde_Reportes_ConstanciaActuacionEstudiantil();
        $this->constanciasolofaltatesis = new Une_Cde_Reportes_ConstanciaSoloFaltaTesis();
        $this->certificacionprogramas = new Une_Cde_Reportes_CertificacionProgramas();

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

        //filtro
        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(true, true, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);

        $this->SwapBytes_Crud_Search->setDisplay(true);

        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, false, false, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, false, false, false, false, false);
        $this->SwapBytes_Crud_Action->addCustum('&nbsp;<select id="selEstado" name="selEstado" style="width:100px"></select>');

        

        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
        $this->_params['redirect'] = $this->redirect_session->params;
        
        $this->tablas = Array('periodo'  => Array('tbl_periodos',
                                                  null,
                                                  Array('pk_periodo',
                                                        'lpad(pk_periodo::text, 4, \'0\') || \', \' || to_char(fechainicio, \'MM-yyyy\') || \' / \' ||  to_char(fechafin, \'MM-yyyy\')'),
                                                  'DESC'),
                              'sede'    => Array('vw_sedes',
                                                 null     ,
                                                 Array('pk_estructura',
                                                       'nombre'),
                                                 null),
//                              'escuela' => Array(Array('tbl_estructurasescuelas ee',
//                                                       'vw_escuelas es'),
//                                                 Array('ee.fk_atributo = es.pk_atributo',
//                                                       'ee.fk_estructura = ##sede##'),//'fk_estructura = 7','fk_estructura = ##sede##',
//                                                 Array('ee.fk_atributo',
//                                                       'es.escuela'),
//                                                 'ASC'),
                              'estado'  => Array('tbl_atributos',
                                                 array('fk_atributotipo = 46',
                                                        'pk_atributo not in (14145,14146)'),
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
            $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
            $this->view->search_span           = 2;
            $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
            $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
            $this->view->SwapBytes_Ajax->setView($this->view);

        }


        public function estadoAction() {
        //$dataRows = $this->atributos->getTipes(37, null); //local
        $dataRows = $this->atributos->getTipes(46,array(14145,14146));
        $this->logger->log($dataRows,ZEND_LOG::ALERT);
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows, "Estado");
        }

        public function cambiarAction() {
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();

                $json = array();
                $Data = $this->_getParam('data');
                $Rows = $this->SwapBytes_Uri->queryToArray($Data);


                if(isset($Rows['chkRecordAcademico'])){
                    if (is_array($Rows['chkRecordAcademico'])) {

                        foreach($Rows['chkRecordAcademico'] as $doc){
                            $this->logger->log($Rows,ZEND_LOG::WARN);
                            $pk = $doc;
                            $up_data['fk_estado'] = $Rows['selEstado'];
                            $update = $this->ds->updateRow($pk, $up_data);
                        }

                    }else{

                        $pk = $Rows['chkRecordAcademico'];
                        $up_data['fk_estado'] = $Rows['selEstado'];
                        $update = $this->ds->updateRow($pk, $up_data);
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
            $searchData = $this->_getParam('buscar');
            $this->ugs->setSearch($searchData);


            $data = $this->_params['filters'];
            
            $rows = $this->ugs->getAllSolicitudesDoc($data);

            //$this->logger->log($rows,ZEND_LOG::ALERT);

            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_documentosolicitado',
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
                                            'value' => '##pk_documentosolicitado##')),
                                 array('name'    => 'Número',
                                       'width'   => '45px',
                                       'column'  => 'num_sol',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Cédula',
                                       'width'   => '60px',
                                       'column'  => 'ced',
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
                                 array('name'    => 'Fecha',
                                       'width'   => '70px',
                                       'column'  => 'fechasolicitud'),
                                 array('name'    => 'Documento',
                                       'width'   => '160px',
                                       'column'  => 'doc',
                                       'rows'    => array('style' => 'text-align:left'))
                                 );


                $other = Array(
                         Array( 'actionName' => '',
                                'action' => 'imprimir(##pk##)',
                                'label' => 'Emitir',
                                )
                );


                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'O',$other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkRecordAcademico');
                
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen solicitudes que mostrar.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }


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
        
        

        public function imprimirAction(){
            //$this->SwapBytes_Ajax->setHeader();
                $pk = $this->_getParam('pk');
                
                

                $doc = $this->ds->get($pk);
//                var_dump($doc);die;
                $data = $this->ds->getInfoSolicitante($doc[0]['pk_documentosolicitado']);
                
//                var_dump($data);die;
                //$this->logger->log($doc[0]['fk_documento'],ZEND_LOG::ALERT);
                //$urlAjax = $this->SwapBytes_Ajax->getUrlAjax();

                //if($doc[0]['fk_estado'] == 8232){ //local
                if($doc[0]['fk_estado'] == 8267){

                    //$up_data['fk_estado'] = 8233; //local
                    $up_data['fk_estado'] = 8268;
                }else{
                    $up_data['fk_estado'] = $doc[0]['fk_estado'];
                }

                $update = $this->ds->updateRow($pk, $up_data);

                SWITCH ($doc[0]['fk_documento']){

                    //CASE 8276: //local
                    CASE 8265:
                        $this->certNotas($data);
                        break;
                    //CASE 8270: //local
                    CASE 8259:
                        $this->certpensum($data);
                        break;
                    //CASE 8269: //local
                    CASE 8258:
                        $this->certprogramas($data);
                        break;
                    //CASE 8228: //local
                    CASE 8261:
                        $this->constancias($data,1);
                        break;
                    //CASE 8273: //local
                    CASE 8262:
                        $this->constancias($data,2);
                        break;
                    //CASE 8275: //local
                    CASE 8264:
                        $this->constancias($data,3);
                        break;
                    //CASE 8274: //local
                    CASE 8263:
                        $this->constancias($data,4);
                        break;
                    default:
                        break;

                }

                 //$this->getResponse()->setBody(Zend_Json::encode($json));
                //$this->logger->log($this->SwapBytes_Ajax->getUrlAjax(),ZEND_LOG::ALERT);

            
        }

        public function certNotas($data){
            $Ci = $data[0]['ced'];
            $Escuela = $data[0]['pk_atributo'];
            $Pensum = $data[0]['codigopropietario'];
            $per = $this->periodos->getUltimo();
            $servic = $this->RecordAcademico->getInfoPasantia($per,$Ci);
            //$this->logger->log('sadasd',ZEND_LOG::ALERT);
            if(isset($servic[0])){
                if($servic[0]['estado'] == 1){

                    $sc = '- Servicio Comunitario Aprobado.';
                }elseif($servic[0]['estado'] == 2){
                    $sc = '- Servicio Comunitario Convalidado segun acuerdo del CU No.107.Fecha 21-07-2008.';
                }elseif($servic[0]['estado'] == 3){
                    $sc = '- Servicio Comunitario Exento.';
                }else{
                    $sc ='';
                }
            }else{
                $sc = '';
            }

            $cantesc    = $this->RecordAcademico->getCantidadEscuela($Ci);
            
            $Periodos    = $this->RecordAcademico->getPeriodosCursados($Ci);
            
            $Estudiante  = $this->RecordAcademico->getInfoEstudianteEscuela($Ci, $Escuela);
            
            $Asignaturas = $this->RecordAcademico->getCompletoEscuela2($Ci, $Escuela,$Pensum);
            
            $iIndiceAcum = $this->RecordAcademico->getIAAEscuelaPensum($Ci, $Escuela,$Pensum);
            
            $iTUCA       = $this->RecordAcademico->getUCACEscuelaPensum($Ci, $Escuela,$Pensum);
            
            //a partir de aqui se jode
            $EquivalenciaDefinitivo = $this->RecordAcademico->getEquivalencias($Ci, 1266,$Pensum);
            
            $Traslado = $this->RecordAcademico->getEquivalencias($Ci, 1264,$Pensum);
            
            $Universidades = $this->RecordAcademico->getUniversidadesEquivalencias($Ci);
            
            $this->certificacionnotas->generar($Ci, $Asignaturas, $Estudiante, $iIndiceAcum, $iTUCA, $EquivalenciaDefinitivo, $Traslado, $Universidades, $sc);

        }

        public function certpensum($data) {

            $Ci = $data[0]['ced'];
            
            if(ctype_digit($Ci)) {
                $Estudiante = $this->RecordAcademico->getInfoEstudiante($Ci,$data[0]['pk_atributo']);
                $this->certificacionpensum->generar($Ci, $Estudiante);
            }
        }

        public function certprogramas($data) {//sirve
            $Ci = $data[0]['ced'];
            
            //if(ctype_digit($Ci)) {
                $Estudiante = $this->RecordAcademico->getInfoEstudiante($Ci,$data[0]['pk_atributo']);
//                var_dump($Estudiante);die;
                $this->certificacionprogramas->generar($Ci, $Estudiante);
            //}
        }

        public function constancias($data,$constancia) {

            $Ci = $data[0]['ced'];
            $Escuela = $data[0]['pk_atributo'];
            
            //if(ctype_digit($Ci)) {
            $Estudiante = $this->RecordAcademico->getInfoEstudianteEscuela($Ci, $Escuela);
//            var_dump($Estudiante);die;
            switch($constancia){
            case 1:
            $this->constanciadeestudios->generar($Ci, $Estudiante);//sirve
            break;
            case 2:
            $this->constanciaactuacionestudiantil->generar($Ci, $Estudiante);
            break;
            case 3:
            $this->constanciasolofaltatesis->generar($Ci, $Estudiante);
            break;
            case 4:
            $this->constanciatramitaciondetitulo->generar($Ci, $Estudiante);
            break;
            }

            //}
        }

        

}


?>

