<?php

class Transactions_AprobartutorController extends Zend_Controller_Action {
	
	private $_Title   = 'Transacciones \ Aprobar Tutor';
	private $FormTitle_Observacion = 'Agregar Observacion';
    private $maxtesisporperiodo = 3;
    private $maxtutorperiodo = 3;

    public function init() {

        Zend_Loader::loadClass('Une_Filtros'); 
        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios'); 
        Zend_Loader::loadClass('Models_DbTable_Pasantes'); 
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Tesis');
        Zend_Loader::loadClass('Models_DbTable_Materiasestados');
        Zend_Loader::loadClass('Models_DbView_Grupos');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Forms_Tutortesis');
        Zend_Loader::loadClass('Forms_Observaciones');
        
        
        $this->Usuarios         = new Models_DbTable_Usuarios();
        $this->Pasantes         = new Models_DbTable_Pasantes();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->recordacademico  = new Models_DbTable_Recordsacademicos();
        $this->tesis           = new Models_DbTable_Tesis();
        $this->materiasestados = new Models_DbTable_Materiasestados();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->filtros          = new Une_Filtros();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();
        $this->vw_grupos        = new Models_DbView_Grupos();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->AuthSpace                = new Zend_Session_Namespace('Zend_Auth');  

        $this->view->form = new Forms_Tutortesis();
        $this->view->form_observaciones = new Forms_Observaciones();

        $this->SwapBytes_Form->set($this->view->form);

        $this->view->form = $this->SwapBytes_Form->get();


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
                                                       'ee.fk_estructura = ##Sede##'),
                                                 Array('ee.fk_atributo',
                                                       'es.escuela'),
                                                 'ASC'),

                              'Estado'  => Array('tbl_atributos',
                                                 array('fk_atributotipo ='.$this->tesis->getAtributotipoTutores()), 
                                                 array('pk_atributo',
                                                       'valor')),
                              'Tipo'=> Array('tbl_atributos',
                                                 array('fk_atributotipo ='.$this->tesis->getAtributotipoCondicionTutor()), 
                                                 array('pk_atributo',
                                                       'valor'))
                                                            );
            

    $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters')); 

//      BOTONES DE ACCIONES
                
    $this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);
    $this->SwapBytes_Crud_Action->setEnable(true, true, false, true, false, false);
    $this->SwapBytes_Crud_Search->setDisplay(true); 
    $this->SwapBytes_Crud_Action->addCustum('&nbsp;<select id="selEstado" name="selEstado" style="width:100px"></select>');


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
    
    public function estadoAction() {
    
    $dataRows = $this->atributos->getTipes($this->tesis->getAtributotipoTutores(),array(8267,8268,8270,8271,14146));
    
    $this->SwapBytes_Ajax_Action->fillSelect($dataRows, "Estado");
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


    public function listAction(){
           if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();

                $datos = $this->_getAllParams();
                $filtro = $this->SwapBytes_Uri->queryToArray($datos['filters']);
                $newrows = array();
                
                $normal = $this->tesis->getCondicionTutorPuede();
                $casoespecial = $this->tesis->getCondicionTutorNoPuede();
                $periodo = $this->tesis->getPeriodoActual();

                $rows = $this->tesis->getTutoresPeriodo($filtro['Periodo'],$filtro['Sede'],$filtro['Escuela'],$filtro['Estado'],$filtro['txtBuscar']);

                foreach ($rows as $row) {

                    $pk_usuario = $row['pk_usuario']; 


                    $grupoestudiante = $this->tesis->getUsuariogrupo($pk_usuario,855);
                    $grupoestudiante = $grupoestudiante[0];

                    
                    $dataRow    = $this->tesis->getUsuarioTutorDatos($pk_usuario);
                    $dataRow = $dataRow[0];
                    $autoridad = $this->tesis->getGrupoAutoridad();
                    $grupoautoridad = $this->tesis->getUsuariogrupo($pk_usuario,$autoridad);
                    $TutorCount = $this->tesis->getTutorNumeroTesis($pk_usuario,$periodo);


                    
                    

                    if(!empty($grupoautoridad)){//si es autoridad
                        $row['condicion'] = 'Es autoridad';
                        $puede = false;
                    }else{//no es autoridad

                        if($TutorCount >= $this->maxtutorperiodo){//tiene mas de 3 tesis por periodo
                            $row['condicion'] = 'Tiene  3 o mas tesis asociadas';
                            $puede = false;
                        }else{

                            if(!empty($grupoestudiante)){//eres estudiante
                                $datosestudiante = $this->tesis->getDatosEstudiante($pk_usuario);

                                

                                $UCA = $this->tesis->getUCA($datosestudiante[0]['usuario'],$datosestudiante[0]['escuela'],$datosestudiante[0]['pensum'],$datosestudiante[0]['periodo']);
                                
                                $EquiUCA = $this->recordacademico->getUCACEscuelaEquivPensum($datosestudiante[0]['usuario'], $datosestudiante[0]['escuela'],$datosestudiante[0]['pensum']);

                                $totalUCA = $UCA + $EquiUCA;

                                if($datosestudiante[0]['pensum'] == 8){//es del pensum 2012
                                    switch ((int)$totalUCA) {
                                        case 180:$graduado = true; break;//administracion 
                                        case 185:$graduado = true; break;//computacion 
                                        case 189:$graduado = true; break;//diseño 
                                        case 197:$graduado = true; break;//civil 
                                        case 199:$graduado = true; break;//electronica 
                                        case 193:$graduado = true; break;//turismo 
                                        default: $graduado = false;break;//indica que no esta graduado aun
                                    }

                                }elseif($datosestudiante[0]['pensum'] == 7){// es del pensum 1997

                                    switch ((int)$totalUCA) {                      
                                        case 175:$graduado = true; break;//pensum 1997
                                        default: $graduado = false;break;//indica que no esta graduado aun
                                    }

                                }elseif($datosestudiante[0]['pensum'] == 6){//es del pensum 1992

                                    switch ((int)$totalUCA) {
                                        case 176:$graduado = true; break;//pensum 1992 administracion - diseño - civil - electronica - turismo
                                        case 175:$graduado = true; break;//pensum 1992 computacion
                                        default: $graduado = false;break;//indica que no esta graduado aun
                                    }

                                }else{//esto quiere decir que no se ha podido calcular bien sus UCA
                                    $graduado = false;
                                }



                                if($graduado == true){//si lo esta, verifico si tiene mas de 2 años graduado

                                    $tiempo_graduado = $this->tesis->getTiempoGraduado($datosestudiante[0]['usuario']);


                                    if($tiempo_graduado >= 2){//tiene al menos 2 años de graduado

                                        $row['condicion'] = 'Tiene +2 años de graduado';
                                        $puede = true;

                                    }else{//no tiene el tiempo requerido
                                        $row['condicion'] = 'No tiene 2 años de graduado';
                                        $puede = false;
                                    }


                                }else{//si no esta graduado, no cumple
                                    $row['condicion'] = 'No esta graduado';
                                    $puede = false;
                                }

                            }else{//no eres estudiante

                                if(!empty($dataRow)){//tienes perfil docente, administrativo o tutor

                                    $grupoadministrativo = $this->tesis->getUsuariogrupo($pk_usuario,1745);
                                    $grupodocente = $this->tesis->getUsuariogrupo($pk_usuario,854);

                                    if(!empty($grupodocente)){//verificamos si es administrativo

                                        $row['condicion'] = 'Es docente';
                                        $puede = true;

                                        
                                    }elseif(!empty($grupoadministrativo)){
                                        
                                        $row['condicion'] = 'Es administrativo'; 
                                        $puede = true;

                                    }else{
                                        $row['condicion'] = 'Ha sido tutor';
                                        $puede = true;
                                    }


                                }else{//eres un tutor externo

                                    $row['condicion'] = 'Tutor Externo';
                                    $puede = true;
                                    
                                }
                        }
                    }

            }


                    if($filtro['Tipo'] == $normal){
                        if($puede == true){
                            $newrows[] = $row;
                        }
                    }else{
                        if($puede == false){
                            $newrows[] = $row;
                        }
                    }
                    
                }

                natcasesort($newrows);
                
                if(isset($newrows) && count($newrows) > 0) {

                        $table = array('class' => 'tableData',
                               'width' => '1000px');

                        $columns = array(array('column'  => 'pk_tutortesis',
                                               'primary' => true,
                                               'hide'    => true),
                                         array('name' => array('control' => array('tag' => 'input',
                                                        'type' => 'checkbox',
                                                        'name' => 'chkSelectDeselect')),
                                                'column' => 'action',
                                                'width' => '30px',
                                                'rows' => array('style' => 'text-align:center'),
                                                'control' => array('tag' => 'input',
                                                    'type' => 'checkbox',
                                                    'name' => 'chkTutorTesis',
                                                    'id' => 'chkTutorTesis',
                                                    'value' => '##pk_tutortesis##')),
                                         array('name'    => 'Cedula(s)',
                                                   'width'   => '70px',
                                                   'column'  => 'pk_usuario',
                                                   'rows'    => array('style' => 'text-align:center')),
                                         array('name'    => 'Tutor',
	                                   			'width'   => '200px',
	                                   			'rows'    => array('style' => 'text-align:center'),
	                                   			'column'  => 'nombretutor'),
			                             array('name'    => 'Tesista(s)',
			                                   'width'   => '200px',
			                                   'rows'    => array('style' => 'text-align:center'),
			                                   'column'  => 'tesistas'),
                                         array('name'    => 'Titulo',
                                               'width'   => '300px',
                                               'rows'    => array('style' => 'text-align:center'),
                                               'column'  => 'titulo'),
                                         array('name'    => 'Condicion',
                                               'width'   => '100px',
                                               'rows'    => array('style' => 'text-align:center'),
                                               'column'  => 'condicion')

                        );

                          $other = array(
                          array('actionName' => '',
                                'action'     => 'observaciones(##pk##)'  ,
                                'label'      => 'observaciones',
                                'column' => 'acciones')
                          
                          );
                       

                        $HTML = $this->SwapBytes_Crud_List->fill($table, $newrows, $columns, 'VO',$other);
                        $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                        $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkTutorTesis');

            }else{

                    $HTML = $this->SwapBytes_Html_Message->alert("No Existen Tutores Cargados");

                    $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));        

        }

    }

    public function cambiarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json = array();
            $Data = $this->_getParam('data');

            $Rows = $this->SwapBytes_Uri->queryToArray($Data);


            $nopuede = false;
            $implicados = array();
            
            if(isset($Rows['chkTutorTesis'])){

                if (is_array($Rows['chkTutorTesis'])) {

                    foreach($Rows['chkTutorTesis'] as $tutor){

                    	$cedula = $this->tesis->getTutorByPk($tutor);


                    	$count = $this->tesis->getTutorNumeroTesis($cedula,$Rows['Periodo']);

                    	$pk = $tutor;

                    	if($count < $this->maxtesisporperiodo){
	                    	
	                        $up_data = $Rows['selEstado'];
	                        $update = $this->tesis->updateEstadoTutor($pk, $up_data);

                    	}else{

                    		// if($Rows['selEstado'] == $this->tesis->getEstadoTutorAprobado()){

	                    	// 	$nopuede = true;
	                    	// 	$datostutor = $this->tesis->getTutorDatos($pk);

	                    	// 	array_push($implicados, array(
	                    	// 		'cedula'=>$datostutor[0]['pk_usuario'],
	                    	// 		'nombre'=>$datostutor[0]['nombre'],
	                    	// 		'titulo'=>$datostutor[0]['titulo'])
	                    	// 	);
                    		// }else{

	                    		$up_data = $Rows['selEstado'];
	                    		$update = $this->tesis->updateEstadoTutor($pk, $up_data);
                    		// }
                    	}

                        
                    }

                }else{

                    $pk = $Rows['chkTutorTesis'];
                    $cedula = $this->tesis->getTutorByPk($pk);

                    $count = $this->tesis->getTutorNumeroTesis($cedula,$Rows['Periodo']);

                    if($count < $this->maxtesisporperiodo){
                    	$up_data = $Rows['selEstado'];
                    	$update = $this->tesis->updateEstadoTutor($pk, $up_data);
                    }else{
                    	
                    	// if($Rows['selEstado'] == $this->tesis->getEstadoTutorAprobado()){
                    	// 	$nopuede = true;
	                    // 	$datostutor = $this->tesis->getTutorDatos($pk);

                    	// 	array_push($implicados, array(
                    	// 		'cedula'=>$datostutor[0]['pk_usuario'],
                    	// 		'nombre'=>$datostutor[0]['nombre'],
                    	// 		'titulo'=>$datostutor[0]['titulo'])
                    	// 	);

                    	// }else{
                    		$up_data = $Rows['selEstado'];
                    		$update = $this->tesis->updateEstadoTutor($pk, $up_data);
                    	// }
                    }

                    
                }
                
            }

            //esto muestra las tesis a  las que no se le asignaron tutor
            // if($nopuede == true){
            //     $msg = "<div style =\"width: 400px; height:auto;overflow:auto;\">A las siguientes tesis no se le pudieron asignar tutor debido que el mismo cumple con la cantidad máxima por Periodo: <br>";
            //     foreach ($implicados as $key => $value) {
            //     	 $msg .= "<br>". $value['cedula']. " ".$value['nombre'].": <b>".$value['titulo']. "</b><br>";
            //     }
            //     $msg .= "</div>";
                
            //     $json[] = $this->SwapBytes_Jquery->getJSON('list', null, array('buscar' => $this->SwapBytes_Jquery->getValEncodedUri('txtBuscar'),
            //                                 'filters' => $this->SwapBytes_Jquery->serializeForm()

            //                             ));
            //     $json[] = $this->SwapBytes_Jquery->setValSelectOption('selEstado', 0);

            //     $this->SwapBytes_Crud_Form->setJson($json);

            //     $this->SwapBytes_Crud_Form->getDialog('Advertencia', $msg, swOkOnly);


            // }else{
	            $json[] = $this->SwapBytes_Jquery->getJSON('list', null, array('buscar' => $this->SwapBytes_Jquery->getValEncodedUri('txtBuscar'),
	                                        'filters' => $this->SwapBytes_Jquery->serializeForm()

	                                    ));
	            $json[] = $this->SwapBytes_Jquery->setValSelectOption('selEstado', 0);
	            $this->getResponse()->setBody(Zend_Json::encode($json));
            // }

            

        }
    }

    public function addoreditloadAction() {
        // Obtenemos los parametros que se esperan recibir.
       if ($this->_request->isXmlHttpRequest()) {

            
            $this->SwapBytes_Ajax->setHeader();

            $datos = $this->_getAllParams();

            $queryString = $this->_getParam('filters');
            $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);
            $dataRow = $dataRow[0];
            $observaciones = $this->tesis->getTutorObservacion($datos['cod']);
            
            //con esto uso el mismo formulario de observaciones para lo que necesite, que no tienda a confundir
            $dataRow['pk_pasotesis'] = $datos['cod'];

            if(!empty($observaciones[0]['observaciones'])){

               $dataRow['observaciones'] = $observaciones[0]['observaciones'];

            }

            $this->SwapBytes_Crud_Form->setProperties($this->view->form_observaciones, $dataRow, $this->FormTitle_Observacion);
            $this->SwapBytes_Crud_Form->enableElements(true);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
            //para evitar que presionen enter
            $json[] = "$('#frmModal').keypress(function(e){   
                        if(e.which == 13){
                          return false;
                        }
                      });";
            $this->SwapBytes_Crud_Form->setJson($json);

            $this->SwapBytes_Crud_Form->getAddOrEditLoad();

       }
    }

    public function addoreditconfirmAction() {
        
       if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            

            $queryString = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));
            
            $datos = $this->SwapBytes_Uri->queryToArray($this->_getParam('data'));

            if(!empty($datos['pk_pasotesis'])){
                $this->tesis->updateObservacionesTutor($datos['pk_pasotesis'],$datos['observaciones']);
                
            }

            $this->SwapBytes_Crud_Form->getAddOrEditEnd(); 
       }
    }


    public function viewAction() {
        $this->SwapBytes_Ajax->setHeader();
        
        
        $cod = $this->tesis->getTutorByPk($this->_getParam('id'));
        $tutorinterno = $this->tesis->getTutorInterno();
        $AllTutor = $this->tesis->getAllTutor($this->_getParam('id'));
        
        $tipotutores = $this->tesis->getTutoresTipos($AllTutor[0]['fk_tipo']);

        $dataRow['pk_usuario']  = $cod;
        $dataRow = $this->Usuarios->getRow($cod);
        $dataRow['sexo']            = $this->SwapBytes_Form->setValueToBoolean($dataRow['sexo']);
        $dataRow['nacionalidad']    = $this->SwapBytes_Form->setValueToBoolean($dataRow['nacionalidad']);
        $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);
        $dataRow['titulo_academico'] = $titulo_academico[0]['titulo_academico'];

        if($AllTutor[0]['fk_tipo'] == $tutorinterno){
            $json[] = "$('#titulo_academico-label').hide();";
            $json[] = "$('#titulo_academico-element').hide();";
            
        }else{

            $titulo_academico = $this->tesis->getTutorTituloAcademicoByPk($this->_getParam('id'));
            $dataRow['titulo_academico'] = $titulo_academico[0]['titulo_academico'];
            $json[] = "$('#titulo_academico-label').show();";
            $json[] = "$('#titulo_academico-element').show();";
        }

         $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
         $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
         $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
         $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$dataRow['pk_usuario']}'");

        $json[] = $this->SwapBytes_Jquery->setValSelectOption('fk_tipo',$AllTutor[0]['fk_tipo']);

        $this->SwapBytes_Crud_Form->setJson($json); 
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Tutor');
        $this->SwapBytes_Crud_Form->fillSelectBox('fk_tipo', $tipotutores, 'pk_atributo', 'valor');
        $this->SwapBytes_Crud_Form->getView();
 

    }


    public function photoAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$id    = $this->_getParam('id', 0);
		$image = $this->Usuarios->getPhoto($id);

		$this->getResponse()
		     ->setHeader('Content-type', 'image/jpeg')
		     ->setBody($image);
	} 



}