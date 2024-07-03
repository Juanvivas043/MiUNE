<?php
class Transactions_InscripcionesproyectosController extends Zend_Controller_Action {

  private $_title = 'Transacciones \ Inscripcion de Proyectos';

  public function init() {
	/* Initialize action controller here */
	      Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Inscripcionespasantias');
        Zend_Loader::loadClass('Models_DbTable_Asignacionesproyectos');
        Zend_Loader::loadClass('Models_DbTable_Contactos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
	      Zend_Loader::loadClass('Forms_Inscripcionproyecto');

        $this->inscripcionespasantias   = new Models_DbTable_Inscripcionespasantias();
	      $this->asignacionesproyectos    = new Models_DbTable_Asignacionesproyectos();
        $this->contactos                = new Models_DbTable_Contactos();
        $this->usuariosgrupos           = new Models_DbTable_UsuariosGrupos();
	      $this->filtros                  = new Une_Filtros();
        $this->CmcBytes_Filtros         = new CmcBytes_Filtros();
        
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
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
                
        
        // key -> nombre del filtro
       // values -> array(tabla o vista, where,columnas, ordenamiento)        
        
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
            
                              'Proyecto'  => Array(Array('tbl_asignacionesproyectos ap',
                                                        'tbl_proyectos p'),
                                                  Array('p.pk_proyecto = ap.fk_proyecto',
                                                        'ap.fk_escuela = ##Escuela##',
                                                        'ap.fk_periodo = ##Periodo##'),
                                                  Array('pk_asignacionproyecto',
                                                        'p.nombre'),
                                                  'ASC'));
//        $x = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));
        
//	 * Obtiene los parametros de los filtros y del modal.
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));

	$this->_params['modal']     = $this->SwapBytes_Crud_Form->getParams();


//	 * Configuramos los filtros.
	$this->authSpace = new Zend_Session_Namespace('Zend_Auth');


	$this->SwapBytes_Crud_Action->setDisplay(true, true, true);
	$this->SwapBytes_Crud_Action->setEnable(true, true, true);
	$this->SwapBytes_Crud_Search->setDisplay(false);

//	 * Mandamos a crear el formulario para ser utilizado mediante el AJAX.

	$this->view->form = new Forms_Inscripcionproyecto();
    
   if (isset($this->_params['filters']['Periodo']) && $this->_params['filters']['Sede'] <> '' && $this->_params['filters']['Escuela'] <> '' && $this->_params['filters']['Proyecto'] <> ''){


        if ($this->_params['modal']['id'] == 0) { 
        $estudiantes            = $this->inscripcionespasantias->getEstudiantesInscritospasantias($this->_params['filters']['Periodo'],$this->_params['filters']['Escuela']);
        
        }
        $estudiantes            = $this->inscripcionespasantias->getEstudiantesInscritosProyecto($this->_params['filters']['Periodo'],$this->_params['filters']['Sede'],$this->_params['filters']['Escuela']);
        $tutoresinstituciones   = $this->contactos->get($this->_params['filters']['Proyecto']); 
	      $tutoresacademicos      = $this->usuariosgrupos->getTutoresAcademicos($this->_params['filters']['Periodo'],$this->_params['filters']['Escuela']);  
        
          $this->SwapBytes_Form->set($this->view->form);
          $this->SwapBytes_Form->fillSelectBox('fk_recordacademico', $estudiantes  , 'pk_recordacademico', 'estudiante');
          $this->SwapBytes_Form->fillSelectBox('fk_tutor_institucion', $tutoresinstituciones  , 'pk_contacto', 'tutor');
          $this->SwapBytes_Form->fillSelectBox('fk_tutor_academico', $tutoresacademicos  , 'pk_usuariogrupo', 'profesor');
	        $this->view->form = $this->SwapBytes_Form->get();

            
      } 
         
  }


  function preDispatch() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if (!$this->usuariosgrupos->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

  public function indexAction() {
        	$this->view->title                      = $this->_title;
        	$this->view->filters                    = $this->filtros;
        	$this->view->SwapBytes_Jquery           = $this->SwapBytes_Jquery;
        	$this->view->SwapBytes_Crud_Action      = $this->SwapBytes_Crud_Action;
        	$this->view->SwapBytes_Crud_Form        = $this->SwapBytes_Crud_Form;
        	$this->view->SwapBytes_Crud_Search      = $this->SwapBytes_Crud_Search;
        	$this->view->SwapBytes_Ajax             = new SwapBytes_Ajax();
        	$this->view->SwapBytes_Ajax->setView($this->view);
          $this->view->button_span                = 2;
        	$this->view->SwapBytes_Jquery_Ui_Form   = new SwapBytes_Jquery_Ui_Form();
        
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
    $json = array();

	  $rows = $this->inscripcionespasantias->getInscripcionesproyectos($this->_params['filters']['Periodo'], $this->_params['filters']['Sede'],$this->_params['filters']['Escuela'],$this->_params['filters']['Proyecto']);

	  if (!empty($rows)) {

              $table = array('class' => 'tableData',
                             'width' => '1230px');

		$columns = array(array('column' => 'pk_inscripcionpasantia',
				'primary' => true,
				'hide' => true),
			array('name' => 'Cedula',
				'width' => '80px',
				'column' => 'ci',
				'rows' => array('style' => 'text-align:center')),
			array('name' => 'Estudiante',
				'width' => '300px',
				'column' => 'estudiante',
				'rows' => array('style' => 'text-align:center')),
      array('name' => 'Proyecto',
            'width' => '300px',
            'column' => 'proyecto',
            'rows' => array('style' => 'text-align:center')),
      array('name' => 'Tutor Academico',
				'width' => '300px',
				'column' => 'tutoracademico',
				'rows' => array('style' => 'text-align:center')),
			array('name' => 'Tutor Institucional',
				'width' => '300px',
				'column' => 'tutorinstitucion',
				'rows' => array('style' => 'text-align:center')));

		$HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'UD');
		$json[] = $this->SwapBytes_Jquery->setHtml('tblPasantias', $HTML);
	  } 

    else {
		$HTML   = $this->SwapBytes_Html_Message->alert("No existen Estudiantes Inscritos en Proyectos en este periodo.");
		$json[] = $this->SwapBytes_Jquery->setHtml('tblPasantias', $HTML);
    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnAdd', false);

	  }
                
	  $this->getResponse()->setBody(Zend_Json::encode($json));
	}
  }

  public function addoreditloadAction() {
   	   if ($this->_request->isXmlHttpRequest()) {
               
	$json    = array();
	$dataRow = array();

        if (is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
// Editar
	        $title = 'Editar Inscripcion de Proyecto';
	        $dataRow                              = $this->inscripcionespasantias->getRow($this->_params['modal']['id']);
      	  $dataRow['id']                        = $this->_params['modal']['id'];
          $dataRow['pk_inscripcionpasantia']    = $this->_params['modal']['id'];
          //var_dump($dataRow['id']);die;
          $estudiantes = $this->inscripcionespasantias->getEstudiantesInscritosServicio($dataRow['id']);
          //var_dump($estudiantes);die;
          $this->SwapBytes_Form->fillSelectBox('fk_recordacademico', $estudiantes  , 'pk_recordacademico', 'estudiante');
          $this->SwapBytes_Form->enableElement('fk_recordacademico', false);
	} else {
// Agregar
	  $title = 'Agregar Inscripcion de Proyectos';
	}
        
        
	$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
	$this->SwapBytes_Crud_Form->setJson($json);
	$this->SwapBytes_Crud_Form->setWidthLeft('120px');
	$this->SwapBytes_Crud_Form->getAddOrEditLoad(); 
  }
}

  public function addoreditconfirmAction() {
	if ($this->_request->isXmlHttpRequest()) {
      	  $this->SwapBytes_Ajax->setHeader();
      	  $message = '';      
          $proyectocupos = $this->inscripcionespasantias->getCuposfull($this->_params['filters']['Periodo'],$this->_params['filters']['Escuela'],$this->_params['filters']['Proyecto'],$this->_params['modal']['id']);
          
          $tutoresinstituciones   = $this->contactos->get($this->_params['filters']['Proyecto']);
          if ($this->_params['modal']['id'] > 0) {
		            $this->SwapBytes_Form->enableElement('fk_recordacademico', false);
	      }
          if (empty($tutoresinstituciones)){
                $message = "<b>Primero debe asignar un tutor antes de inscribir un estudiante a un proyecto</b>";
		            $this->SwapBytes_Crud_Form->getDialog('No se puede agregar o modificar', $message);
              
        }
          
	  $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
	  $this->SwapBytes_Crud_Form->setWidthLeft('120px');
	  $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
	}
  }

  public function addoreditresponseAction() {
	if ($this->_request->isXmlHttpRequest()) {
	  $this->SwapBytes_Ajax->setHeader();

          $dataRow = $this->_params['modal'];
          $proyectocupos = $this->inscripcionespasantias->getCuposfull($this->_params['filters']['Periodo'],$this->_params['filters']['Escuela'],$this->_params['filters']['Proyecto'],$this->_params['modal']['id']);
          
          $id = $dataRow['id'];
	        $dataRow['fk_asignacionproyecto'] = $this->_params['filters']['Proyecto'];
          $dataRow['pk_inscripcionpasantia'] = (isset($dataRow['pk_inscripcionpasantia']))? $dataRow['pk_inscripcionpasantia'] : $dataRow['id'];
          
          $this->_params['modal']['id'] = null;
          $dataRow['id']         = null;
         


	  if (is_numeric($id) && $id > 0) {
		$this->inscripcionespasantias->updateRow($id, $dataRow);
	  } 

    else {
          
        if ($proyectocupos == true) {
          
            $message = "<b>No se podra Inscribir en este proyecto puesto que no tiene Cupo</b>";
            $this->SwapBytes_Crud_Form->getDialog('No se puede agregar', $message);
          }

        else{ 

          $escuela = $this->_params['filters']['Escuela'];
          $pensum = $this->inscripcionespasantias->getPensumByRecord($dataRow['fk_recordacademico']);
          //var_dump($pensum[0]['fk_pensum']);die;

          $ci = $this->inscripcionespasantias->getUsuarioByRecord($dataRow['fk_recordacademico']);
          $lastIns = $this->inscripcionespasantias->getLastInscripcionUsuario($this->_params['filters']['Periodo'],$this->_params['filters']['Sede'],$this->_params['filters']['Escuela'],$ci[0]['pk_usuario']);
         

          if(empty($lastIns)){
            //si no tiene inscripcion en el ultimo periodo
           
            $this->inscripcionespasantias->insertInscripcionUsuario($ci[0]['pk_usuariogrupo'],$this->_params['filters']['Periodo'],$this->_params['filters']['Sede'],$this->_params['filters']['Escuela'],$pensum[0]['fk_pensum']);
            $pk_servicio = $this->asignacionesproyectos->getPkServicioII($escuela,$pensum[0]['fk_pensum']);
            $length = sizeof($pk_servicio);
            if ($length>1) {
              //inserta pasantia social 1 y pasantia social 2

              $inscripcion = $this->inscripcionespasantias->getLastInscripcionUsuario($this->_params['filters']['Periodo'],$this->_params['filters']['Sede'],$this->_params['filters']['Escuela'],$ci[0]['pk_usuario']);
              $asignacionI = $this->asignacionesproyectos->getPkAsignacion($pk_servicio[0]['pk_asignatura'],$escuela,$pensum[0]['fk_pensum'],$this->_params['filters']['Sede']);
              $asignacionII = $this->asignacionesproyectos->getPkAsignacion($pk_servicio[1]['pk_asignatura'],$escuela,$pensum[0]['fk_pensum'],$this->_params['filters']['Sede']);
             // $this->inscripcionespasantias->insertRecordAsignacion($pk_servicio[0]['pk_asignatura'],$inscripcion[0]['pk_inscripcion'],$asignacionI[0]['pk_asignacion']);
             // $this->inscripcionespasantias->insertRecordAsignacion($pk_servicio[1]['pk_asignatura'],$inscripcion[0]['pk_inscripcion'],$asignacionII[0]['pk_asignacion']); 
              $this->inscripcionespasantias->addRow($dataRow);
            }
            else{
              //inserta servicio comunitario 2 o "pasantia social I y II"(esto es una sola materia)

              $inscripcion = $this->inscripcionespasantias->getLastInscripcionUsuario($this->_params['filters']['Periodo'],$this->_params['filters']['Sede'],$this->_params['filters']['Escuela'],$ci[0]['pk_usuario']);
              $asignatura = $pk_servicio[0]['pk_asignatura'];
              $asignacion = $this->asignacionesproyectos->getPkAsignacion($asignatura,$escuela,$pensum[0]['fk_pensum'],$this->_params['filters']['Sede']);
             // $this->inscripcionespasantias->insertRecordAsignacion($asignatura,$inscripcion[0]['pk_inscripcion'],$asignacion[0]['pk_asignacion']);
              
              $this->inscripcionespasantias->addRow($dataRow);
          }
          }

          else{
          $pk_servicio = $this->asignacionesproyectos->getPkServicioII($escuela,$pensum[0]['fk_pensum']);
          $length = sizeof($pk_servicio);
          $inscripcion = $pensum[0]['pk_inscripcion'];
          if ($length>1) {

              $asignacionI = $this->asignacionesproyectos->getPkAsignacion($pk_servicio[0]['pk_asignatura'],$escuela,$pensum[0]['fk_pensum'],$this->_params['filters']['Sede']);
              $asignacionII = $this->asignacionesproyectos->getPkAsignacion($pk_servicio[1]['pk_asignatura'],$escuela,$pensum[0]['fk_pensum'],$this->_params['filters']['Sede']);
             // $this->inscripcionespasantias->insertRecordAsignacion($pk_servicio[0]['pk_asignatura'],$inscripcion,$asignacionI[0]['pk_asignacion']);
             // $this->inscripcionespasantias->insertRecordAsignacion($pk_servicio[1]['pk_asignatura'],$inscripcion,$asignacionII[0]['pk_asignacion']); 
              $this->inscripcionespasantias->addRow($dataRow);
          }
          else{
              $asignatura = $pk_servicio[0]['pk_asignatura'];
              $asignacion = $this->asignacionesproyectos->getPkAsignacion($asignatura,$escuela,$pensum[0]['fk_pensum'],$this->_params['filters']['Sede']);
             // $this->inscripcionespasantias->insertRecordAsignacion($asignatura,$inscripcion,$asignacion[0]['pk_asignacion']);
    		      $this->inscripcionespasantias->addRow($dataRow);
        }
        }
        }
	  }

	  $this->SwapBytes_Crud_Form->getAddOrEditEnd();
	}
  }

  public function deleteloadAction() {
	$message        = 'Esta seguro que desea eliminarle la inscripcion al estudiante?';
	$permit         = true;
	$dataRow        = $this->inscripcionespasantias->getRow($this->_params['modal']['id']);
	$dataRow['id']  = $this->_params['modal']['id'];

        $estudiantes = $this->inscripcionespasantias->getEstudiantesInscritospasantias($dataRow['fk_periodo'],$dataRow['escuela']); 
        $this->SwapBytes_Form->fillSelectBox('fk_recordacademico', $estudiantes  , 'pk_recordacademico', 'estudiante');
        
	$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Inscripcion de Proyecto', $message);
	$this->SwapBytes_Crud_Form->setWidthLeft('120px');
	$this->SwapBytes_Crud_Form->getDeleteLoad($permit);
  }

  public function deletefinishAction() {
	  $this->inscripcionespasantias->deleteRows($this->_params['modal']['id']);
    //var_dump($this->_params['modal']['id']);die;
    $this->SwapBytes_Crud_Form->setProperties($this->view->form);
	  $this->SwapBytes_Crud_Form->getDeleteFinish();
  }    
}
?>
