<?php

//class Transactions_HorariosController extends SwapBytes_Controller_Action {
class Transactions_HorariosController extends Zend_Controller_Action {

  private $_title = 'Transacciones \ Horarios Académicos';

  public function init() {
	/* Initialize action controller here */
	Zend_Loader::loadClass('Une_Filtros');
	Zend_Loader::loadClass('Models_DbTable_Asignaciones');
	Zend_Loader::loadClass('Models_DbTable_Asignaturas');
	Zend_Loader::loadClass('Models_DbTable_Clases');
	Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
	Zend_Loader::loadClass('Models_DbTable_Horarios');
	Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
	Zend_Loader::loadClass('Models_DbTable_Referencias');
	Zend_Loader::loadClass('Models_DbView_Dias');
	Zend_Loader::loadClass('Models_DbView_Semestres');
	Zend_Loader::loadClass('Models_DbView_Secciones');
	Zend_Loader::loadClass('Models_DbView_Estructuras');
	Zend_Loader::loadClass('Models_DbView_Docentes');
	Zend_Loader::loadClass('Forms_Horario');

	$this->asignaciones = new Models_DbTable_Asignaciones();
	$this->asignaturas = new Models_DbTable_Asignaturas();
	$this->clases = new Models_DbTable_Clases();
	$this->grupo = new Models_DbTable_UsuariosGrupos();
	$this->horarios = new Models_DbTable_Horarios();
	$this->recordsacademicos = new Models_DbTable_Recordsacademicos();
	$this->referencias = new Models_DbTable_Referencias();
	$this->vw_semestres = new Models_DbView_Semestres();
	$this->vw_secciones = new Models_DbView_Secciones();
	$this->vw_estructuras = new Models_DbView_Estructuras();
	$this->vw_dias = new Models_DbView_Dias();
	$this->vw_docentes = new Models_DbView_Docentes();
	$this->filtros = new Une_Filtros();

	$this->SwapBytes_Ajax = new SwapBytes_Ajax();
	$this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
	$this->SwapBytes_Crud_Action = new SwapBytes_Crud_Action();
	$this->SwapBytes_Crud_List = new SwapBytes_Crud_List();
	$this->SwapBytes_Crud_Form = new SwapBytes_Crud_Form();
	$this->SwapBytes_Crud_Search = new SwapBytes_Crud_Search();
	$this->SwapBytes_Date = new SwapBytes_Date();
	$this->SwapBytes_Form = new SwapBytes_Form();
	$this->SwapBytes_Html_Message = new SwapBytes_Html_Message();
	$this->SwapBytes_Uri = new SwapBytes_Uri();
	$this->SwapBytes_Jquery = new SwapBytes_Jquery();
	$this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
   

	/*
	 * Obtiene los parametros de los filtros y del modal.
	 */
	$this->_params['filters'] = $this->filtros->getParams();
	$this->_params['modal'] = $this->SwapBytes_Crud_Form->getParams();

	/*
	 * Configuramos los filtros.
	 */
	$this->authSpace = new Zend_Session_Namespace('Zend_Auth');

	$this->filtros->setDisplay(true, true, true, true, true, false, true, true, false);
	$this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
	$this->filtros->setRecursive(false, true, true, true, false, false, false, false, false);
	$this->filtros->setType('seccion', FILTER_TYPE_SECCION_PADRES);

	$this->SwapBytes_Crud_Action->setDisplay(true, true, true);
	$this->SwapBytes_Crud_Action->setEnable(true, true, true);
	$this->SwapBytes_Crud_Search->setDisplay(false);
	/*
	 * Mandamos a crear el formulario para ser utilizado mediante el AJAX.
	 */
	$this->view->form = new Forms_Horario();

	// Valores que se definien por defecto al agregar?
	if (isset($this->_params['filters']['seccion']) && isset($this->_params['filters']['sede'])) {
	  $dias = $this->vw_dias->get();
	  $horarios = $this->horarios->getSelect();
	  $semestres = $this->vw_semestres->get();
	  $docentes = $this->vw_docentes->get();
	  $secciones = $this->vw_secciones->getHijos($this->_params['filters']['seccion']);
	  $edificios = $this->vw_estructuras->getEdificios($this->_params['filters']['sede']);

	  $this->SwapBytes_Form->set($this->view->form);
	  $this->SwapBytes_Form->fillSelectBox('fk_dia', $dias, 'pk_atributo', 'dia');
	  $this->SwapBytes_Form->fillSelectBox('fk_horario', $horarios, 'pk_horario', 'horario');
	  $this->SwapBytes_Form->fillSelectBox('fk_semestre', $semestres, 'pk_atributo', 'id');
	  $this->SwapBytes_Form->fillSelectBox('fk_usuariogrupo', $docentes, 'pk_usuariogrupo', 'docente');
	  $this->SwapBytes_Form->fillSelectBox('fk_seccion', $secciones, 'pk_atributo', 'valor');
	  $this->SwapBytes_Form->fillSelectBox('fk_edificio', $edificios, 'pk_edificio', 'edificio');
	  $this->view->form = $this->SwapBytes_Form->get();

	  // Definimos valores por defecto provenientes de la DB cuando
	  // estamos agregando, estos valores se definen segun los datos del filtro:
	  if ($this->_params['modal']['id'] == 0) {
		$this->_params['default']['edificio'] = $edificios[0]['pk_edificio'];
		$this->_params['default']['horario'] = $horarios[0]['pk_horario'];
		$this->_params['default']['dia'] = $dias[0]['pk_atributo'];
	  }
	}
  }

  public function preDispatch() {
	if (!Zend_Auth::getInstance()->hasIdentity()) {
	  $this->_helper->redirector('index', 'login', 'default');
	}

	if (!$this->grupo->haveAccessToModule()) {
	  $this->_helper->redirector('accesserror', 'profile', 'default');
	}
  }

  public function indexAction() {
	$this->view->title = $this->_title;
	$this->view->filters = $this->filtros;
	$this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
	$this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
	$this->view->SwapBytes_Crud_Form = $this->SwapBytes_Crud_Form;
	$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
	$this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
	$this->view->SwapBytes_Ajax->setView($this->view);
	$this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
  }

  public function sedeAction() {
	$this->filtros->getAction();
  }

  public function escuelaAction() {
	$this->filtros->getAction();
  }

  public function pensumAction() {
	$this->filtros->getAction();
  }

  public function horarioAction() {
	$dataRows = $this->horarios->getSelect($this->_params['filters']['turno']);
	$this->SwapBytes_Ajax_Action->fillSelect($dataRows);
  }

  public function materiaAction() {
	$dataRows = $this->asignaturas->getSelect($this->_params['filters']['pensum'], $this->_params['filters']['semestre']);
	$this->SwapBytes_Ajax_Action->fillSelect($dataRows);
  }

  public function aulaAction() {
	$dataRows = $this->vw_estructuras->getAulasOcupadas($this->_params['filters']['periodo'], $this->_getParam('horario'), $this->_getParam('dia'), $this->_getParam('edificio'));
	$this->SwapBytes_Ajax_Action->fillSelect($dataRows);
  }

  public function listAction() {
	if ($this->_request->isXmlHttpRequest()) {
	  $this->SwapBytes_Ajax->setHeader();

//			$json = $this->filtros->getJson();

	  $this->asignaciones->setData($this->_params['filters'], array('periodo', 'sede', 'escuela', 'pensum', 'semestre', 'turno', 'seccion', 'dia'));
	  $rows = $this->asignaciones->getRows();


	  if (isset($rows) && count($rows) > 0) {
		$table = array('class' => 'tableData',
			'zebra' => array('column' => 'dia',
				'colors' => array('odd' => 'A9D0F5',
					'even' => 'EFF5FB')));

		$columns = array(array('column' => 'pk_asignacion',
				'primary' => true,
				'hide' => true),
			array('name' => 'día',
				'width' => '70px',
				'column' => 'dia',
				'rows' => array('style' => 'text-align:left')),
			array('name' => 'Horario',
				'width' => '90px',
				'column' => 'horario',
				'rows' => array('style' => 'text-align:center')),
			array('name' => 'sec.',
				'width' => '30px',
				'column' => 'seccion',
				'rows' => array('style' => 'text-align:center')),
			array('name' => 'Materia',
				'width' => '300px',
				'column' => 'materia',
				'rows' => array('style' => 'text-align:left')),
			array('name' => 'Profesor',
				'width' => '300px',
				'column' => 'profesor'),
			array('name' => 'Edf.',
				'width' => '60px',
				'column' => 'edificio'),
			array('name' => 'Aula',
				'width' => '30px',
				'column' => 'aula',
				'rows' => array('style' => 'text-align:center')),
			array('name' => 'cupos',
				'width' => '50px',
				'column' => 'cupos',
            'rows' => array('style' => 'text-align:center')),
         array('name' => 'cupos max.',
				'width' => '50px',
				'column' => 'cupos_max',
				'rows' => array('style' => 'text-align:center'))
         );

		$HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VUD');
		$json[] = $this->SwapBytes_Jquery->setHtml('tblHorarios', $HTML);
		$json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkAsignacion');
	  } else {
		$HTML = $this->SwapBytes_Html_Message->alert("No existen horarios cargados.");

		$json[] = $this->SwapBytes_Jquery->setHtml('tblHorarios', $HTML);
	  }

	  $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnAdd', false);

	  $this->getResponse()->setBody(Zend_Json::encode($json));
	}
  }

  /**
   * Paso 1
   * aoel: AddOrEditLoad
   */
  public function addoreditloadAction() {
	  // $this->SwapBytes_Ajax->setHeader();
	$json = array();
	$dataRow = array();
	if (is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
	  // Editar
	  $title = 'Editar Horario';
	  $dataRow = $this->asignaciones->getRow($this->_params['modal']['id']);
     // var_dump($dataRow);
	  $dataRow['id'] = $this->_params['modal']['id'];
	  $dataRow['semestre'] = $dataRow['fk_semestre'];
	  $dataRow['asignatura'] = $dataRow['fk_asignatura'];

	  $this->SwapBytes_Form->enableElement('fk_semestre', false);
	  $this->SwapBytes_Form->enableElement('fk_asignatura', false);
	} else {
	  // Agregar
	  $title = 'Agregar Horario';
     $dataRow['semestre'] = $this->_params['filters']['semestre'];
     $dataRow['fk_semestre'] = $this->_params['filters']['semestre'];
     $dataRow['fk_semestre_alterado'] = $this->_params['filters']['semestre'];
	}

	$this->_fillSelectsRecursive($dataRow);

	$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
	$this->SwapBytes_Crud_Form->setJson($json);
	$this->SwapBytes_Crud_Form->setWidthLeft('80px');
	$this->SwapBytes_Crud_Form->getAddOrEditLoad(); 
  }

  /**
   * Paso 2
   * aoec: AddOrEditConfirm
   */
  public function addoreditconfirmAction() {
	if ($this->_request->isXmlHttpRequest()) {
	  $this->SwapBytes_Ajax->setHeader();

	  $message = '';

	  $coincideSalon = $this->asignaciones->getTodasCoincidenciaSalon($this->_params['filters']['periodo'], $this->_params['modal']['fk_dia'], $this->_params['modal']['fk_horario'], $this->_params['modal']['fk_estructura'], $this->_params['modal']['id']);
	  
	  $coincideHorario = $this->asignaciones->getCoincidenciaHorario($this->_params['filters']['periodo'], $this->_params['modal']['fk_dia'], $this->_params['modal']['fk_horario'], $this->_params['modal']['fk_usuariogrupo'], $this->_params['modal']['id']);

	  $TodascoincideHorario = $this->asignaciones->getTodasCoincidenciaHorario($this->_params['filters']['periodo'], $this->_params['modal']['fk_dia'], $this->_params['modal']['fk_horario'], $this->_params['modal']['fk_usuariogrupo'], $this->_params['modal']['id']);
	 
	  //echo sizeof($TodascoincideHorario);die;
	  if (!empty($coincideSalon)) {
		$message .= "- El salón se encuentra ocupado.<br>";
		$message .= "<table><tr><th>Per&iacute;odo</th><th>Sede</th><th>Pensum</th><th>Escuela</th><th>Semestre</th><th>Mater&iacute;a</th><th>Secci&oacute;n</th><th>Estructura</th><th>Aula</th><th>Profesor</th><th>Cupos</th></tr>";
		foreach ($coincideSalon as $key => $value) {
			$horario = $this->asignaciones->getRow($value["pk_asignacion"]);
			$message .= "<tr><td width=\"20px\" align=\"center\">{$horario['fk_periodo']}</td><td align=\"center\" width=\"100px\">{$horario['sede']}</td><td align=\"center\" width=\"80px\">{$horario['pen']}</td><td align=\"center\" width=\"250px\">{$horario['escuela']}</td><td align=\"center\" width=\"50px\">{$horario['semestre']}</td><td align=\"center\" width=\"100px\">{$horario['materia']}</td><td align=\"center\" width=\"20px\">{$horario['seccion']}</td><td align=\"center\" width=\"50px\">{$horario['edif']}</td><td align=\"center\" width=\"20px\">{$horario['aula']}</td><td align=\"center\" width=\"100px\">{$horario['prof']}</td><td align=\"center\" width=\"20px\">{$horario['cupos']}</td></tr>";
		}
		$message .= "</table>";

		

	  }
	  //var_dump($coincideHorario);die;
	  if (!empty($coincideHorario)) {
	  	$bodyTable = "";
	  	foreach ($TodascoincideHorario as $key => $value) {
		  	$horario = $this->asignaciones->getRow($value["pk_asignacion"]);
		 	$bodyTable .= "<tr><td width=\"20px\" align=\"center\">{$horario['fk_periodo']}</td><td align=\"center\" width=\"100px\">{$horario['sede']}</td><td align=\"center\" width=\"80px\">{$horario['pen']}</td><td align=\"center\" width=\"250px\">{$horario['escuela']}</td><td align=\"center\" width=\"50px\">{$horario['semestre']}</td><td align=\"center\" width=\"100px\">{$horario['materia']}</td><td align=\"center\" width=\"20px\">{$horario['seccion']}</td><td align=\"center\" width=\"50px\">{$horario['edif']}</td><td align=\"center\" width=\"20px\">{$horario['aula']}</td><td align=\"center\" width=\"100px\">{$horario['prof']}</td><td align=\"center\" width=\"20px\">{$horario['cupos']}</td></tr>";
		 
		 }

		$horario = $this->asignaciones->getRow($coincideHorario);
		//var_dump($horario);die;
		if ($horario['fk_dia'] == $this->_params['modal']['fk_dia'] &&
				$horario['fk_horario'] == $this->_params['modal']['fk_horario'] &&
				$horario['fk_estructura'] == $this->_params['modal']['fk_estructura'] &&
				$horario['fk_usuariogrupo'] == $this->_params['modal']['fk_usuariogrupo']) { // Fusion
		
		  $message = "Desea fuciónar la asignatura actual con la siguiente:<br>";
		  $message .= "<table><tr><th>Per&iacute;odo</th><th>Sede</th><th>Pensum</th><th>Escuela</th><th>Semestre</th><th>Mater&iacute;a</th><th>Secci&oacute;n</th><th>Estructura</th><th>Aula</th><th>Profesor</th><th>Cupos</th></tr>";
		  $message .= $bodyTable;
		  $message .= "</table>";


		  $this->SwapBytes_Crud_Form->getDialog('¿Desea realizar fución de horario?', $message, swYesNo);
		}

		if(sizeof($TodascoincideHorario)>1 && empty($coincideSalon) && !empty($TodascoincideHorario)){
			
			//el sizeof me indica si la asignacion esta fucionada y cuantas fuciones posee
			//porque me dice el tamaño del vector $TodascoincideHorario si posee una no esta fucionada
			//si posee mas de 1 esta fucionada

			$message = "Desea mover todas las asignaturas :";
	  		$message .= "<table><tr><th>Per&iacute;odo</th><th>Sede</th><th>Pensum</th><th>Escuela</th><th>Semestre</th><th>Mater&iacute;a</th><th>Secci&oacute;n</th><th>Estructura</th><th>Aula</th><th>Profesor</th><th>Cupos</th></tr>";
	  		$bodyTable = "";

	  		foreach ($TodascoincideHorario as $key => $value) {
		  		$horario = $this->asignaciones->getRow($value["pk_asignacion"]);
		  		//var_dump($horario);die;
		 		$bodyTable .= "<tr><td width=\"20px\" align=\"center\">{$horario['fk_periodo']}</td><td align=\"center\" width=\"100px\">{$horario['sede']}</td><td align=\"center\" width=\"80px\">{$horario['pen']}</td><td align=\"center\" width=\"250px\">{$horario['escuela']}</td><td align=\"center\" width=\"50px\">{$horario['semestre']}</td><td align=\"center\" width=\"100px\">{$horario['materia']}</td><td align=\"center\" width=\"20px\">{$horario['seccion']}</td><td align=\"center\" width=\"50px\">{$horario['edif']}</td><td align=\"center\" width=\"20px\">{$horario['aula']}</td><td align=\"center\" width=\"100px\">{$horario['prof']}</td><td align=\"center\" width=\"20px\">{$horario['cupos']}</td></tr>";
		 
		 }

			
			
		    $message .= $bodyTable;
		    $message .= "</table>";

		    $this->SwapBytes_Crud_Form->getDialog('¿Desea mover las Asignaturas a otro salon?', $message, swYesNo);die;
			
			
			
		}
		

		else { // Profesor ocupado
			
			//var_dump($coincideSalon);die;
		  $message .= "- El profesor tiene otro horario asignado.<br>";
		  $message .= "<table><tr><th>Per&iacute;odo</th><th>Sede</th><th>Pensum</th><th>Escuela</th><th>Semestre</th><th>Mater&iacute;a</th><th>Secci&oacute;n</th><th>Estructura</th><th>Aula</th><th>Profesor</th><th>Cupos</th></tr>";
		  $message .= $bodyTable;
		  $message .= "</table>";
		  $this->SwapBytes_Crud_Form->getDialog('No se puede realizar esta accion', $message);
		}
	  }



	  elseif(!empty($coincideSalon)){ // Salon ocupado
		  $this->SwapBytes_Crud_Form->getDialog('No se puede realizar esta accion', $message);
	  }



	  if ($this->_params['modal']['id'] > 0) {
		$this->SwapBytes_Form->enableElement('fk_semestre', false);
		$this->SwapBytes_Form->enableElement('fk_asignatura', false);
	  }

	  //echo "pasa";die;
	  $this->_params['modal']['fk_semestre'] = $this->_params['modal']['semestre'];
	  $this->_params['modal']['fk_asignatura'] = $this->_params['modal']['asignatura'];

	  $this->_fillSelectsRecursive($this->_params['modal']);
	  //echo "pasa";die;
	  $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
	  $this->SwapBytes_Crud_Form->setWidthLeft('800px');
	  $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
	}
  }

  /**
   * Paso 3
   * aoer: AddOrEditResponce
   */
  public function addoreditresponseAction() {
	if ($this->_request->isXmlHttpRequest()) {
	  $this->SwapBytes_Ajax->setHeader();
		
	  $id = $this->_params['modal']['id'];
	  $this->_params['modal']['fk_periodo'] = $this->_params['filters']['periodo'];
	  // $this->_params['modal']['fk_semestre_alterado'] = $this->_params['modal']['fk_semestre'];
	  $this->_params['modal']['fk_semestre_alterado'] = $this->_params['filters']['semestre'];
	  $this->_params['modal']['fk_semestre'] = $this->_params['filters']['semestre'];
	  $this->_params['modal']['fk_turno'] = $this->_params['filters']['turno'];
	  $this->_params['modal']['fk_turno_alterado'] = $this->_params['filters']['turno'];
//			$this->_params['modal']['fk_asignatura']        = $this->_params['modal']['asignatura'];
//			$this->_params['modal']['fk_asignacion']        = null;
	  $this->_params['modal']['fk_edificio'] = null;
	  $this->_params['modal']['filters'] = null;
	  $this->_params['modal']['asignatura'] = null;
	  $this->_params['modal']['semestre'] = null;
	  $this->_params['modal']['id'] = null;

	  // En caso de que tenga fusiones, se cambiaran los cupos y cupos max de todas las asignaturas relacionadas.
	  // var_dump($this->_params['filters'] , $this->_params['modal'] );die;
	  // En caso de que tenga fusiones, se cambiaran las aulas de todas las asignaciones fusionadas a la seleccionada
	  // var_dump($this->_params['modal']['fk_estructura']);die;
	  $CoincidenciasCupos = $this->asignaciones->getTodasCoincidenciaHorarioCupos($id);
	  
	  $TodascoincideHorario = $this->asignaciones->getTodasCoincidenciaHorario($this->_params['filters']['periodo'], $this->_params['modal']['fk_dia'], $this->_params['modal']['fk_horario'], $this->_params['modal']['fk_usuariogrupo'], $this->_params['modal']['id']);
	  
	  

	  if (sizeof($TodascoincideHorario)>1){
	  	
	  	$aula_cambiar = $this->_params['modal']['fk_estructura'];
	    $this->asignaciones->updateAulasByArray($TodascoincideHorario,$aula_cambiar);
	   }

	  if (count($CoincidenciasCupos)> 0 ){
	  	$CuposDiferentes = false;
		$CupoMenor = $CoincidenciasCupos[0]["cupos"];
		$CupoMax = $CoincidenciasCupos[0]["cupos"];
		//var_dump($CupoMax);die;
		
		
		   
		for ($i = 0; $i < intval(count($CoincidenciasCupos)) ; $i++) {

			    $pk_asignaciones_cambiar[] = $CoincidenciasCupos[$i]["pk_asignacion"];
			    //var_dump($pk_asignaciones_cambiar);die;
			    // Buscamos el Cupo menor
			    if ($CupoMenor > $CoincidenciasCupos[$i]["cupos"])
			    	$CupoMenor = $CoincidenciasCupos[$i]["cupos"];
			    // Buscamos el Cupo maximo
			    if ($CupoMax > $CoincidenciasCupos[$i]["cupos_max"])
			    	$CupoMax = $CoincidenciasCupos[$i]["cupos_max"];
				// Verificamos en caso de que alguno de los cupos sea diferente
				
				if(!empty($CoincidenciasCupos[$i+1]["cupos"])){
					if ($CoincidenciasCupos[$i]["cupos"] != $CoincidenciasCupos[$i+1]["cupos"]  ){
				    	$CuposDiferentes = true;
				    }	
				}
		}
	  }
	  	
		

	  
	  if (is_numeric($id) && $id > 0) {
		$this->asignaciones->updateRow($id, $this->_params['modal']);
		if (count($CoincidenciasCupos)> 0 ){
			if($CuposDiferentes){
				$this->asignaciones->updateCuposByArray($pk_asignaciones_cambiar,$CupoMenor,$CupoMax);
				
			}else{
				$this->asignaciones->updateCuposByArray($pk_asignaciones_cambiar,$this->_params['modal']['cupos'],$this->_params['modal']['cupos_max']);
			}
		}
		
	  } else {
		$this->asignaciones->addRow($this->_params['modal']);
	  }

	  $this->SwapBytes_Crud_Form->getAddOrEditEnd();
	}
  }

  public function viewAction() {

   
	$dataRow = $this->asignaciones->getRow($this->_params['modal']['id']);
	$materias = $this->asignaturas->getSelect($dataRow['fk_pensum'], $dataRow['fk_semestre']);
	$aulas = $this->vw_estructuras->getAulas($dataRow['fk_edificio']);

	$this->SwapBytes_Form->fillSelectBox('fk_asignatura', $materias, 'pk_asignatura', 'materia');
	$this->SwapBytes_Form->fillSelectBox('fk_estructura', $aulas, 'pk_aula', 'aula');

	$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Horario');
	$this->SwapBytes_Crud_Form->setWidthLeft('80px');
	$this->SwapBytes_Crud_Form->getView();
  }

  public function deleteloadAction() {
	$message = '';
	$permit = true;
	$dataRow = $this->asignaciones->getRow($this->_params['modal']['id']);
	$dataRow['id'] = $this->_params['modal']['id'];

	$inscritos = $this->recordsacademicos->getCountByAsignacion($dataRow['pk_asignacion']);
	$clases = $this->clases->getCountByAsignacion($dataRow['pk_asignacion']);
	$referencias = $this->referencias->getCountByAsignacion($dataRow['pk_asignacion']);

	if ($inscritos > 0 || $clases > 0 || $referencias > 0) {
	  $message .= "<b>No se podra eliminar la asignación por que existen datos relacionados:</b><br>";
	  $permit = false;
	}

	$message .= ( $inscritos > 0) ? "- Estudiantes inscritos.<br>" : '';
	$message .= ( $clases > 0) ? "- Clases en el cronograma de activiades.<br>" : '';
	$message .= ( $referencias > 0) ? "- Referencias en el cronograma de activiades.<br>" : '';

	$materias = $this->asignaturas->getSelect($dataRow['fk_pensum'], $dataRow['fk_semestre']);
	$aulas = $this->vw_estructuras->getAulas($dataRow['fk_edificio']);

	$this->SwapBytes_Form->fillSelectBox('fk_asignatura', $materias, 'pk_asignatura', 'materia');
	$this->SwapBytes_Form->fillSelectBox('fk_estructura', $aulas, 'pk_aula', 'aula');

	$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Horario', $message);
	$this->SwapBytes_Crud_Form->setWidthLeft('80px');
	$this->SwapBytes_Crud_Form->getDeleteLoad($permit);
  }

  public function deletefinishAction() {
	$this->asignaciones->deleteRows($this->_params['modal']['id']);

	$this->SwapBytes_Crud_Form->setProperties($this->view->form);
	$this->SwapBytes_Crud_Form->getDeleteFinish();
  }

  /**
   * Llena los filtros que son recursivos del formulario.
   *
   * @param array $RowData Arreglo de los datos que se estan enviando, provenientes
   *                       de la Base de Datos cuando se esta editando o del
   *                       formulario cuando se esta validando.
   */
  private function _fillSelectsRecursive($RowData) {
	if ($this->_params['modal']['id'] <> 0 && is_array($RowData) && count($RowData) > 0) {
	  // Modificar
	  $materias = $this->asignaturas->getSelect($this->_params['filters']['pensum'], $RowData['fk_semestre']);
	  $aulas = $this->vw_estructuras->getAulasOcupadas($this->_params['filters']['periodo'], $RowData['fk_horario'], $RowData['fk_dia'], $RowData['fk_edificio']);
	} else if ($this->_params['modal']['id'] == 0) {
	  // Agregar
	  $horario = (!empty($this->_params['modal']['fk_horario'])) ? $this->_params['modal']['fk_horario'] : $this->_params['default']['horario'];
	  $dia = (!empty($this->_params['modal']['dia'])) ? $this->_params['modal']['dia'] : $this->_params['default']['dia'];
	  $edificio = (!empty($this->_params['modal']['fk_edificio'])) ? $this->_params['modal']['fk_edificio'] : $this->_params['default']['edificio'];

	  $materias = $this->asignaturas->getSelect($this->_params['filters']['pensum'], $this->_params['filters']['semestre']);
	  $aulas = $this->vw_estructuras->getAulasOcupadas($this->_params['filters']['periodo'], $horario, $dia, $edificio);
	}

	$this->SwapBytes_Form->fillSelectBox('fk_asignatura', $materias, 'pk_asignatura', 'materia');
	$this->SwapBytes_Form->fillSelectBox('fk_estructura', $aulas, 'pk_aula', 'aula');
  }

}
