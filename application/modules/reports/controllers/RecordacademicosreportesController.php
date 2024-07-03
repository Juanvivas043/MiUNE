<?php

class Reports_RecordacademicosreportesController extends Zend_Controller_Action {

    private $Title = 'Reportes \ Materias inscritas';
    private $FormTitle_Detalle = 'Ver los datos de la materia inscrita del estudiante';
    private $FormTitle_Modificar = 'Modifica los datos de la materia inscrita del estudiante';
    private $FormTitle_Info = 'Informaci&oacute;n';

    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Models_DbTable_Materiasestados'); 
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Forms_Recordacademico');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');

        $this->asignaciones = new Models_DbTable_Asignaciones();
        $this->materiasestados = new Models_DbTable_Materiasestados();
        $this->periodos = new Models_DbTable_Periodos();
        $this->sedes = new Models_DbTable_Estructuras();
        $this->escuelas = new Models_DbTable_EstructurasEscuelas();
        $this->recordacademico = new Models_DbTable_Recordsacademicos();
        $this->grupo = new Models_DbTable_UsuariosGrupos();
        $this->filtros = new Une_Filtros();

        $this->SwapBytes_Date = new SwapBytes_Date();
        $this->SwapBytes_Uri = new SwapBytes_Uri();
        $this->SwapBytes_Form = new SwapBytes_Form();
        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search = new SwapBytes_Crud_Search();
        $this->SwapBytes_Html = new SwapBytes_Html();
        $this->SwapBytes_Html_Message = new SwapBytes_Html_Message();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask = new SwapBytes_Jquery_Mask();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();

        $this->SwapBytes_Ajax->setView($this->view);

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        /*
         * Mandamos a crear el formulario para ser utilizado mediante el AJAX.
         */
        $this->view->form = new Forms_Recordacademico();

        $this->SwapBytes_Form->set($this->view->form);
        //$this->SwapBytes_Form->fillSelectBox('estado', $this->materiasestados->getSelect("'N/A'"), 'id', 'valor');

        $this->view->form = $this->SwapBytes_Form->get();

        // $this->filtros->setDisplay(true, true, true, false, false, false, false, false, false);
        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);

        $this->SwapBytes_Crud_Action->setDisplay(true, true);
        $this->SwapBytes_Crud_Action->setEnable(true, true);
        //$this->SwapBytes_Crud_Action->addCustum('&nbsp;<select id="selEstado" name="selEstado" style="width:100px"></select>');

        $this->_params['modal'] = $this->SwapBytes_Crud_Form->getParams();
        
         $this->logger = Zend_Registry::get('logger');

$this->tablas = Array(
                              'Periodo'  => Array('tbl_periodos',
                                                  null,
                                                  Array('pk_periodo',
                                                        'lpad(pk_periodo::text, 4, \'0\') || \', \' || to_char(fechainicio, \'MM-yyyy\') || \' / \' ||  to_char(fechafin, \'MM-yyyy\')'),
                                                  'DESC'),
            
                              'Sede'    => Array('vw_sedes',
                                                 null     ,
                                                 Array('pk_estructura',
                                                       'nombre'),
                                                 'DESC'),

                              'Escuela' => Array(Array('tbl_estructurasescuelas ee',
                                                       'vw_escuelas es'),
                                                 Array('ee.fk_atributo = es.pk_atributo',
                                                       'ee.fk_estructura = ##Sede##'),//'fk_estructura = 7','fk_estructura = ##sede##',
                                                 Array('ee.fk_atributo',
                                                       'es.escuela'),
                                                 'ASC'),
            
                              'Pensum'  => Array(Array('tbl_pensums'),
                                                  Array('fk_escuela = ##Escuela##'),
                                                  Array('pk_pensum',
                                                        'nombre'),
                                                  'ASC'));
       $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters')); 
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

    /**
     * Crea la estructura base de la pagina principal.
     */
    public function indexAction() {
        $this->view->filters = $this->filtros;
        $this->view->title = $this->Title;
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();

        $this->view->SwapBytes_Ajax = $this->SwapBytes_Ajax;
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Jquery_Ui_Form = $this->SwapBytes_Jquery_Ui_Form;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
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


    /**
     * Accion que llena el objeto HTML de tipo SELECT con los datos del "sede"
     * y es usado como filtro de la lista.
     */
    public function sedeAction() {
        $this->filtros->getAction();
    }

    /**
     * Accion que llena el objeto HTML de tipo SELECT con los datos del "escuela"
     * y es usado como filtro de la lista.
     */
    public function escuelaAction() {
        $this->filtros->getAction();
    }

    /**
     * Accion que llena el objeto HTML de tipo SELECT con los datos del "estado
     * de una materia".
     */
    public function estadoAction() {
        //$dataRows = $this->materiasestados->getSelect("'N/A'");
       // $this->SwapBytes_Ajax_Action->fillSelect($dataRows, "Estado");
    }

    /**
     * Acción que permite cambiar el estado de una Materia mediante AJAX.
     */
    /*
    public function cambiarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json = array();
            $Data = $this->_getParam('data');
            
            $Rows = $this->SwapBytes_Uri->queryToArray($Data);
            $pageNumber = $this->_getParam('page', 1);
            $itemPerPage = 10;
            //var_dump($this->_params['modal']);

            $this->logger->log($Rows, Zend_Log::INFO);

                $UIAPP = $this->recordacademico->getUltimoIndiceAcademicoPorPeriodo($Rows['txtBuscar']);
                
                $IA = $this->recordacademico->getIndiceindiceAcademico($Rows['txtBuscar']);

                if (($UIAPP < 11)&&($IA[0]['fn_xrxx_estudiante_iia'] < 11)) {
                    $modificado = false; //booleano que identifica si se muestra o no el mensaje
                    $comademas = false; //booleano para la verificacion de una coma de mas en un arreglo.                    $mensaje .= "La(s) materia(s) con el codigo propietario: ";
                        // Definimos los valores
                        $this->recordacademico->setSearch($Rows['txtBuscar']);
                        $rows = $this->recordacademico->getList_records($filterPeriodo, $filterSede, $filterEscuela, $filterPensum, $itemPerPage, $pageNumber);
                
                        if (isset($Rows['chkRecordAcademico'])) {//Si no se selecciono nada

                           if (is_array($Rows['chkRecordAcademico'])) { //si es un arreglo...

                                  foreach ($Rows['chkRecordAcademico'] as $recordAcademico) { //Se recorre el arreglo de record Academico

                                      // Definimos los valores
                                      $ci = $this->recordacademico->getBuscarDatosEstudiante($recordAcademico);
                                      $BR = $this->recordacademico->getBuscarRecord($recordAcademico);//codigo propietario
                                      $BMR = $this->recordacademico->getBuscarMateriasRaspadas($Rows['Escuela'],$ci[0]['fk_usuario'],$Rows['Periodo'],$BR);
                                      $ES = $this->recordacademico->getBuscarEstado($recordAcademico);

                                      
                                      if($BMR[0]['calificacion']>=0 && $BMR[0]['calificacion']<10 && $ES == 864){ // se verifica la calificacion de la materia si es mayor a 0 y menor a 11
                                      $mensaje .=  $BR .", ";
                                      $modificado = true;
                                      $comademas = true;

                                      }else{
                                          $this->recordacademico->updateRow($recordAcademico, null, null, null, $Rows['selEstado'], null);
                                      }

                                  }

                                  

                            }else{

                                      $ci = $this->recordacademico->getBuscarDatosEstudiante($Rows['chkRecordAcademico']);
                                      $BR = $this->recordacademico->getBuscarRecord($Rows['chkRecordAcademico']);
                                      //var_dump($ci);
                                      $BMR = $this->recordacademico->getBuscarMateriasRaspadas($Rows['Escuela'],$ci[0]['fk_usuario'],$Rows['Periodo'],$BR);

                                      if($BMR[0]['calificacion']>=0 && $BMR[0]['calificacion']<10){ // se verifica la calificacion de la materia si es mayor a 0 y menor a 11
                                      
                                          $mensaje .=  $BR ."  ";
                                          $modificado = true;
                                      }else{

                                          $this->recordacademico->updateRow($Rows['chkRecordAcademico'], null, null, null, $Rows['selEstado'], null);
                                      }

                            }
                      
                                if ($modificado == true){//mensaje de que no se puede modificar
                                    if($comademas == true){ //Si es un arreglo va a tener una coma y un espacio, entonces se verifica eso...
                                        $mensaje = substr($mensaje, 0,strlen($mensaje)-2);
                                    }else{
                                        $mensaje = substr($mensaje, 0,strlen($mensaje)-1);
                                    }
                                    $mensaje .= " ";
                                    $mensaje .= "no se puede(n) modificar.";
                                    $json[] = "alert('$mensaje')";
                                }
                        

                                $json[] = $this->SwapBytes_Jquery->getJSON('list', null, array('buscar' => $this->SwapBytes_Jquery->getValEncodedUri('txtBuscar'),
                                            'filters' => $this->SwapBytes_Jquery->serializeForm(),
                                            'page' => $Rows['page'],
                                        ));

                                $json[] = $this->SwapBytes_Jquery->setValSelectOption('selEstado', 0);

                                $json[] = $this->SwapBytes_Jquery->setHtml('lblMessage', "El Estudiante se encuentra en estado de <b>Periodo de Recuperacion</b> y no se le podr&aacute;n retirar las materias seg&uacute;n el reglamento.");
                                $json[] = $this->SwapBytes_Jquery->setShow('lblMessage');
                        }else{
                            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', '');
                            $json[] = $this->SwapBytes_Jquery->setHtml('lblMessage', "No se puede cambiar el estado de la(s) materia(s), debe seleccionarlas.");
                            $json[] = $this->SwapBytes_Jquery->setShow('lblMessage');
                        }

                        $this->getResponse()->setBody(Zend_Json::encode($json));

                }else{

                    if (isset($Rows['chkRecordAcademico'])) {
                        if (is_array($Rows['chkRecordAcademico'])) {
                            foreach ($Rows['chkRecordAcademico'] as $recordAcademico) {

                                $this->recordacademico->updateRow($recordAcademico, null, null, null, $Rows['selEstado'], null);
                            }
                        } else {

                            $this->recordacademico->updateRow($Rows['chkRecordAcademico'], null, null, null, $Rows['selEstado'], null);
                        }

                        $json[] = $this->SwapBytes_Jquery->getJSON('list', null, array('buscar' => $this->SwapBytes_Jquery->getValEncodedUri('txtBuscar'),
                                    'filters' => $this->SwapBytes_Jquery->serializeForm(),
                                    'page' => $Rows['page'],
                                ));

                        $json[] = $this->SwapBytes_Jquery->setValSelectOption('selEstado', 0);

                        //$json[] = $this->SwapBytes_Jquery->setHtml('lblMessage', "El Estudiante se encuentra en estado de <b>Periodo de Recuperacion</b> y no se le podr&aacute;n retirar las materias seg&uacute;n el reglamento.");
                        //$json[] = $this->SwapBytes_Jquery->setShow('lblMessage');
                    } else {
                        $json[] = $this->SwapBytes_Jquery->setHtml('tableData', '');
                        $json[] = $this->SwapBytes_Jquery->setHtml('lblMessage', "No se puede cambiar el estado de la(s) materia(s), debe seleccionarlas.");
                        $json[] = $this->SwapBytes_Jquery->setShow('lblMessage');
                    }

                    $this->getResponse()->setBody(Zend_Json::encode($json));
               }
        }
    }
    */
    
    /**
     * Lista el contenido y las acciones pertinentes de una tabla determinada de
     * forma paginada.
     */
    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            // Obtenemos los parametros necesarios que se esta pasando por POST, y
            // los valores necesarios de las variables que se utilizaran mas adelante.
            $pageNumber = $this->_getParam('page', 1);
           // $queryString = $this->_getParam('filters');
            $searchData = $this->_getParam('buscar');
            // $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);
            $queryArray = $this->_params['filters'];
            // $filterPeriodo = $queryArray['selPeriodo'];
            // $filterSede = $queryArray['selSede'];
            // $filterEscuela = $queryArray['selEscuela'];
            $filterPeriodo = $queryArray['Periodo'];
            $filterSede = $queryArray['Sede'];
            $filterEscuela = $queryArray['Escuela'];
            $filterPensum = $queryArray['Pensum'];
            $itemPerPage = 10;
            $pageRange = 10;
            $periodoPrueba = false;
            $json[] = $this->SwapBytes_Jquery->setHtml('lblMessage', '');
            $json[] = $this->SwapBytes_Jquery->setHide('lblMessage');
            //Detectamos si el estudiante esta en probatorio y mostramos dicha informaci√≥n.
            if (is_numeric(trim($searchData)) && strlen(trim($searchData)) <= 8 && strlen(trim($searchData)) >= 6) {
                $html_estudiante = '';
                $UIAPP = $this->recordacademico->getUltimoIndiceAcademicoPorPeriodo($searchData);
                $IA = $this->recordacademico->getIndiceindiceAcademico($searchData);             
                
                if (($UIAPP < 11)&&($IA[0]['fn_xrxx_estudiante_iia'] < 11) ) {
                    $html_estudiante .= "El Estudiante se encuentra en estado de <b>Periodo de Recuperacion</b> y no se le podr&aacute;n retirar las materias seg&uacute;n el reglamento.</br>";
                    $periodoPrueba = true; 
                    
                }

                 $html_estudiante .=  "<center><button id=\"btnRetirar_Semestre\" class=\"ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\" name=\"btnRetirar_Semestre\" role=\"button\" aria-disabled=\"false\" value=\"{$searchData}\">";
                 $html_estudiante .=  "<span class=\"ui-button-text\">Retirar Semestre al Estudiante C.I. {$searchData}</span>";
                 $html_estudiante .=  "</button></center>";
                 $json[] = $this->SwapBytes_Jquery->setHtml('lblMessage', $html_estudiante);
                 if(!empty($html_estudiante)){
                    //$json[] = $this->SwapBytes_Jquery->setShow('lblMessage');
                 }
            }
            // Definimos los valores
            $this->recordacademico->setSearch($searchData);
            
            $paginatorCount = $this->recordacademico->getSQLCount($filterPeriodo, $filterSede, $filterEscuela, $filterPensum);
            $rows = $this->recordacademico->getList_records($filterPeriodo, $filterSede, $filterEscuela, $filterPensum, $itemPerPage, $pageNumber);

            // Cambia el estado de la materia dependiento si estan en periodo de prueba
            if ($periodoPrueba == true){ 
            $cont = 0;
                foreach ($rows AS $row){
                    if ($row["estado"]=="Inscrita"){
                    
                    $BMR = $this->recordacademico->getBuscarMateriasRaspadas($filterEscuela,$searchData,$filterPeriodo,$rows[$cont]["codigopropietario"]);
                     $this->logger->log($BMR[0]['calificacion'], Zend_Log::INFO);
                    if ($BMR[0]['calificacion'] < 10 && !empty($BMR) || $rows[$cont]["fk_materia"] == '1701'){
                            $rows[$cont]["estado"] = "No Retirable";
                            
                        }
                    }
                    $cont ++;
                }
            }
            
            // Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                'width' => '1130px');

            // Creamos la lista de elementos que provienen de la consulta.
            $columns = array(array('column' => 'pk_recordacademico',
                    'primary' => true,
                    'hide' => true),
             
                array('name' => 'ci',
                    'width' => '70px',
                    'column' => 'ci',
                    'rows' => array('style' => 'text-align:center')),
                array('name' => 'nombre',
                    'width' => '250px',
                    'column' => 'nombre',
                    'rows' => array('style' => 'text-align:left')),
                array('name' => 'Apellido',
                    'width' => '300px',
                    'column' => 'apellido',
                    'rows' => array('style' => 'text-align:left')),
                array('name' => 'codigo',
                    'width' => '70px',
                    'column' => 'codigopropietario',
                    'rows' => array('style' => 'text-align:center')),
                array('name' => 'sem.',
                    'width' => '70px',
                    'column' => 'semestre',
                    'rows' => array('style' => 'text-align:center')),
                array('name' => 'materia',
                    'width' => '250px',
                    'column' => 'materia',
                    'rows' => array('style' => 'text-align:left')),
                array('name' => 'sec.',
                    'width' => '50px',
                    'column' => 'seccion',
                    'rows' => array('style' => 'text-align:center')),
                array('name' => 'U.C.',
                    'width' => '30px',
                    'column' => 'unidadcredito',
                    'rows' => array('style' => 'text-align:center')),
                array('name' => 'calf.',
                    'width' => '50px',
                    'column' => 'calificacion',
                    'rows' => array('style' => 'text-align:center')),
                array('name' => 'estado',
                    'width' => '100px',
                    'column' => 'estado',
                    'rows' => array('style' => 'text-align:left')));

            // Generamos la lista.
            $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'VI');

            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            //$json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkRecordAcademico');

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    /**
     * Muestra mediante un formulario modal una serie de información
     * relevante del registro.
     */
    public function infoAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json = array();
            $data = array();

            // Obtenemos los parametros que se esperan recibir.
            $pk_recordacademico = $this->_getParam('id', 0);
            $Info = $this->asignaciones->getHorarioPorRecordAcademico($pk_recordacademico);
            if (isset($Info) && is_array($Info) && count($Info) > 0) {
                $properties = array('width' => '550',
                    'align' => 'center');

                $styles = array(array('style' => 'text-align:right;font-size:14px;font-weight:bold;vertical-align:top'),
                    array('style' => 'text-align:left;font-size:14px'));

                $data[] = array('Profesor:', $Info[0]['profesor']);
                $data[] = array('Materia:', $Info[0]['materia']);
                $data[] = array('Nota:', $Info[0]['nota']);
                $data[] = array('Edificio:', $Info[0]['edificio']);
                $data[] = array('Aula:', $Info[0]['aula']);
                $data[] = array('Secci&oacute;n:', $Info[0]['seccion']);
                $data[] = array('Turno:', $Info[0]['turno']);

                foreach ($Info AS $horarios) {
                    $horario .= "{$horarios['dia']}, {$horarios['horario']}</br>";
                }

                $data[] = array('Horario:', $horario);

                $html = "<fieldset><legend>&nbsp;<b>Horario</b>&nbsp;</legend>";
                $html .= $this->SwapBytes_Html->table($properties, $data, $styles);
                $html .= "</fieldset>";
            } else {
                $html = "El estudiante no tiene informaci&oacute;n acad&eacute;mica registrada de la asignatura seleccionada.";
            }

            // Envia los datos al modal.
            $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 420);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal', 580);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle('frmModal', $this->FormTitle_Info);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->open('frmModal');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    /**
     * Muestra mediante un formulario modal con todos los objetos del mismo
     * bloqueados, con el fin de poder visualizar todos los datos importantes
     * del registro seleccionado.
     */
    public function viewAction() {
        // Obtenemos los parametros que se esperan recibir.
        $dataRow = $this->recordacademico->getList_record($this->_params['modal']['id']);

        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $this->FormTitle_Detalle);
        $this->SwapBytes_Crud_Form->getView();
    }

    /**
     * Permite construir y mostrar el formulario de tipo modal, este puede ser
     * utilizado para la acción de agregar o modificar un registro determinado.
     */
    public function addoreditloadAction() {
        $dataRow = $this->recordacademico->getList_record($this->_params['modal']['id']);
        $dataRow['id'] = $this->_params['modal']['id'];
        
        $BR = $this->recordacademico->getBuscarRecord($this->_params['modal']['id']);//codigo propietario  
        $BDE = $this->recordacademico->getBuscarDatosEstudiante($this->_params['modal']['id']);
        $BMR = $this->recordacademico->getBuscarMateriasRaspadas($BDE[0]['fk_escuela'],$BDE[0]['fk_usuario'],$BDE[0]['fk_periodo'],$BR);
        $UIAPP = $this->recordacademico->getUltimoIndiceAcademicoPorPeriodo($BDE[0]['fk_usuario']);
        $IA = $this->recordacademico->getIndiceindiceAcademico($BDE[0]['fk_usuario']);  
        $BM = $this->recordacademico->getBuscarMateria($this->_params['modal']['id']);
            
            if(($BMR[0]['calificacion']>0 && $BMR[0]['calificacion']<10)&&($UIAPP < 11)&&($IA[0]['fn_xrxx_estudiante_iia'] < 11) || $BM == '1701'){ // se verifica la calificacion de la materia si es mayor a 0 y menor a 11
                $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $this->FormTitle_Modificar);
                $this->SwapBytes_Crud_Form->enableElements(false);
             }else{
                $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $this->FormTitle_Modificar);
                $this->SwapBytes_Crud_Form->enableElements(false);
                $this->SwapBytes_Crud_Form->enableElement('estado', true);  
                $this->SwapBytes_Crud_Form->enableElement('calificacion', true);
             }

        $this->SwapBytes_Crud_Form->getAddOrEditLoad();
    }

    public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $dataRow = $this->recordacademico->getList_record($this->_params['modal']['id']);
            $dataRow['id'] = $this->_params['modal']['id'];
            $dataRow['calificacion'] = $this->_params['modal']['calificacion'];
            $dataRow['estado'] = $this->_params['modal']['estado'];

        $BR = $this->recordacademico->getBuscarRecord($this->_params['modal']['id']);//codigo propietario  
        $BDE = $this->recordacademico->getBuscarDatosEstudiante($this->_params['modal']['id']);
        $BMR = $this->recordacademico->getBuscarMateriasRaspadas($BDE[0]['fk_escuela'],$BDE[0]['fk_usuario'],$BDE[0]['fk_periodo'],$BR);
        $UIAPP = $this->recordacademico->getUltimoIndiceAcademicoPorPeriodo($BDE[0]['fk_usuario']);
        $IA = $this->recordacademico->getIndiceindiceAcademico($BDE[0]['fk_usuario']);  
        $this->logger->log("aj", Zend_Log::INFO);
        $BM = $this->recordacademico->getBuscarMateria($this->_params['modal']['id']);    
            if(($BMR[0]['calificacion']>0 && $BMR[0]['calificacion']<10)&&($UIAPP < 11)&&($IA[0]['fn_xrxx_estudiante_iia'] < 11) || $BM == '1701'){ // se verifica la calificacion de la materia si es mayor a 0 y menor a 11
            
            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow);
            $this->SwapBytes_Crud_Form->enableElements(false);


            }else{
            
            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow);
            $this->SwapBytes_Crud_Form->enableElements(false);
            $this->SwapBytes_Crud_Form->enableElement('calificacion', true);
            $this->SwapBytes_Crud_Form->enableElement('estado', true);
            $this->SwapBytes_Crud_Form->setWidthLeft('80px');
            $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
                
            }


        }
    }

    /**
     * Permite guardar el contenido de un determinado registro mediante una serie
     * de datos que fueron capturados por un formulario modal.
     */
    public function addoreditresponseAction() {
        // Obtenemos los parametros que se esperan recibir.
        $this->recordacademico->updateRow($this->_params['modal']['id'], null, null, null, $this->_params['modal']['estado'], $this->_params['modal']['calificacion']);
        $this->SwapBytes_Crud_Form->getAddOrEditEnd();
    }

    /**
     * Carga la ayuda del modulo, basicamente es una pagina html no dinamica que
     * contiene toda la información relevante del modulo.
     */
    public function helpAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $this->render();
    }

    public function loadretirarsemestreAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $queryString = $this->_getParam('filters');
            $ci = $this->_getParam('buscar');
            $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);
            $filterPeriodo = $queryArray['Periodo'];
            $filterSede = $queryArray['Sede'];
            $filterEscuela = $queryArray['Escuela'];
            $filterPensum = $queryArray['Pensum'];
            $count = $this->recordacademico->retirarSemestre($ci, $filterPeriodo, $filterEscuela, $filterSede,$filterPensum);
            $html = "El estudiante de cédula <b>{$ci}</b> posee <b>{$count}</b> Materia(s) Inscrita(s) en el período {$filterPeriodo}.</br>Desea continuar con el RETIRO DEL SEMESTRE?";

            $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 420);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal', 580);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle('frmModal', 'Retiro de Semestre');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->open('frmModal');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript('boton = $(\'#frmModal\').parent().find(\'button:contains(\"Proceder\")\').length');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript('if(boton == 0){$(\'#frmModal\').parent().find(\'button:contains(\"Cancelar\")\').clone(true).appendTo(\'.ui-dialog-buttonset:visible\').find(\'span:first\').text(\'Proceder\')}');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript('$(\'#frmModal\').parent().find(\'button:contains(\"Proceder\")\').unbind(\'click\')');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript('$(\'#frmModal\').parent().find(\'button:contains(\"Proceder\")\').click( function (){$.getJSON(urlAjax + \"makeretirarsemestre/buscar/\" + encodeURIComponent($(\'#btnRetirar_Semestre\').val()) + \"/filters/\" + escape($(\'#tblFiltros\').find(\':input\').serialize()), function (d) {executeCmdsFromJSON(d);$(this).dialog(\'close\');})})');
   

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
    
    public function makeretirarsemestreAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $queryString = $this->_getParam('filters');
            $ci = $this->_getParam('buscar');
            $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);
            $filterPeriodo = $queryArray['Periodo'];
            $filterSede = $queryArray['Sede'];
            $filterEscuela = $queryArray['Escuela'];
            $filterPensum = $queryArray['Pensum'];
            $count = $this->recordacademico->retirarSemestre($ci, $filterPeriodo, $filterEscuela, $filterSede, $filterPensum ,true);

            // $json[] = $this->SwapBytes_Jquery->getJSON('list', null, array('buscar' => $this->SwapBytes_Jquery->getValEncodedUri('txtBuscar'),
            //                         'filters' => $this->SwapBytes_Jquery->serializeForm(),
            //                         'page' => $Rows['page'],
            //                     ));
            $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript('$.getJSON(urlAjax + \"list/buscar/\" + encodeURIComponent($(\'#btnRetirar_Semestre\').val()) + \"/filters/\" + escape($(\'#tblFiltros\').find(\':input\').serialize()), function (d) {executeCmdsFromJSON(d)});');
            $this->getResponse()->setBody(Zend_Json::encode($json));
            $this->SwapBytes_Crud_Form->getAddOrEditEnd();
        }
    }

}

