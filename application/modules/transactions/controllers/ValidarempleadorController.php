<?php
class Transactions_ValidarempleadorController extends Zend_Controller_Action {

  private $Title                 = 'Transacciones \ Lista de Empleadores';
  private $FormTitle_Agregar     = 'Agregar nuevo empleador';
  private $FormTitle_Modificar   = 'Modificar datos del empleador';
  private $FormTitle_Eliminar    = 'Eliminar datos del empleador';
  private $FormTitle_Detalle     = 'Ver los datos del empleador';
  private $FormTitle_Info        = 'Información del empleador';
  private $FormAlert_Agregar     = 'El usuario ya existe en el sistema como empleador,<br>porfavor coloque otro número de cédula de identidad para poder ingresarlo.';
  private $FormAlert_Actualizar  = 'El usuario ya existe en el sistema, ¿Desea agregarlo como empleador?';
  private $fk_grupo_preempleador = 20111;
  private $fk_grupo_empleador    = 20120;

  public function init() {
    //instanciar clases
    Zend_Loader::loadClass('Une_Filtros');
    Zend_Loader::loadClass('Models_DbTable_Usuarios');
    Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
    Zend_Loader::loadClass('Models_DbTable_Instituciones');
    Zend_Loader::loadClass('Models_DbTable_Solicitudesempleadores');  
    Zend_Loader::loadClass('Models_DbTable_Atributos');    
    Zend_Loader::loadClass('Forms_Empleadores');
    Zend_Loader::loadClass('Une_Seniat');

    $this->SwapBytes_Date           = new SwapBytes_Date();
    $this->SwapBytes_Uri            = new SwapBytes_Uri();
    $this->SwapBytes_Form           = new SwapBytes_Form();
    $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
    $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
    $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
    $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
    $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
    $this->SwapBytes_Html           = new SwapBytes_Html();
    $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
    $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
    $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
    $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();

    $this->empleadores              = new Models_DbTable_Usuarios();
    $this->grupo                    = new Models_DbTable_UsuariosGrupos();
    $this->filtros                  = new Une_Filtros();
    $this->instituciones            = new Models_DbTable_Instituciones();
    $this->solicitudempleador       = new Models_DbTable_Solicitudesempleadores();
    $this->atributos                = new Models_DbTable_Atributos();

    $this->Request                  = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->form               = new Forms_Empleadores();

  	$this->SwapBytes_Crud_Action->setDisplay(true, true);
  	$this->SwapBytes_Crud_Action->setEnable(true, true);

    $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();

    // FORM  
    $this->view->form               = new Forms_Empleadores();
    $this->SwapBytes_Form->set($this->view->form);
    $this->SwapBytes_Form->fillSelectBox('fk_estado', $this->atributos->getTipes(92, NULL) , 'pk_atributo', 'valor');
    $this->view->form = $this->SwapBytes_Form->get();
  }
  
  function preDispatch() {
      if(!Zend_Auth::getInstance()->hasIdentity()) {
          $this->_helper->redirector('index', 'login', 'default');
      }

      if(!$this->grupo->haveAccessToModule()) {
          $this->_helper->redirector('accesserror', 'profile', 'default');
      }
  }
  
  public function indexAction() {
    $this->view->title                    = $this->Title;
    $this->view->filters                  = $this->filtros;
    $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
    $this->SwapBytes_Ajax_Action          = new SwapBytes_Ajax_Action();
		$this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
		$this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
		$this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
    $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
    $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
    $this->view->SwapBytes_Ajax->setView($this->view);
  }

  public function listAction() {
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();
          $pageNumber  = $this->_getParam('page', 1);
          $searchData  = $this->_getParam('buscar');
          $itemPerPage = 15;
          $pageRange   = 10;

          $this->empleadores->setSearch($searchData);
          $paginatorCount = $this->empleadores->getEmpleadoresCount();

          $rows           = $this->empleadores->getEmpleadores($itemPerPage, $pageNumber);
          // Definimos las propiedades de la tabla.
          $table = array('class' => 'tableData',
                         'width' => '770px');
          $columns = array(array('column'  => 'pk_solicitudempleador',
                                 'primary' => true,
                                 'hide'    => true),
                           array('name'    => 'C.I.',
                                 'width'   => '70px',
                                 'column'  => 'ci',
                                 'rows'    => array('style' => 'text-align:right')),
                           array('name'    => 'Nombre',
                                 'width'   => '200px',
                                 'column'  => 'nombre'),
                           array('name'    => 'Apellido',
                                 'width'   => '200px',
                                 'column'  => 'apellido'),
                           array('name'    => 'Institucion',
                                 'width'   => '200px',
                                 'rows'    => array('style' => 'text-align:center'),
                                 'column'  => 'institucion'),
                           array('name'    => 'Estado',
                                 'width'   => '100px',
                                 'rows'    => array('style' => 'text-align:center'),
                                 'column'  => 'estado'));
          foreach($rows AS $key => $row){
              $rows[$key]['apellido'] = str_replace("'",  "\'", $row['apellido']);
          }
          // Generamos la lista.
          $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'VU');
          $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
          $this->getResponse()->setBody(Zend_Json::encode($json));
      }
  }

  private function getData($id_row) {
    $dataSolicitud = $this->solicitudempleador->getRow($id_row);
    $id = $dataSolicitud['fk_usuario'];
    $dataRow = $this->empleadores->getRow($id);
    unset($dataRow['passwordhash'],$dataRow['passwordoehash']);
    $estado = $this->empleadores->getEmpleadorState($id_row);
    if(isset($dataRow)) {
        $dataRow['id']              = $id;
        $dataRow['sexo']            = $this->SwapBytes_Form->setValueToBoolean($dataRow['sexo']);
        $dataRow['nacionalidad']    = $this->SwapBytes_Form->setValueToBoolean($dataRow['nacionalidad']);
        $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);
        $dataRow['actualizado']     = $this->SwapBytes_Form->setValueToCheck($dataRow['actualizado']);
        $dataRow['fk_estado']       = $estado['fk_estado'];
        $dataRow['id_institucion']  = $estado['fk_institucion'];
        $dataRow['id_solicitud']    = $estado['pk_solicitudempleador'];
        return $dataRow;
    }
  }

  public function addoreditloadAction() {
    // Obtenemos los parametros que se esperan recibir.
    $id_row     = $this->_getParam('id', 0);
    if(is_numeric($id_row) && $id_row > 0) {
        // Agregamos algunos valores al formulario.
        $dataRow         = $this->getData($id_row);
        $dataRow['page'] = $this->_getParam('page', 1);
        $enable = false;
        $title = $this->FormTitle_Modificar;
    } 
    else {
        $enable = true;
        $title = $this->FormTitle_Agregar;
    }
    $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
    $this->SwapBytes_Crud_Form->enableElement('pk_usuario', $enable);
    $this->SwapBytes_Crud_Form->setWidthLeft('130px');
    $this->SwapBytes_Crud_Form->setJson(
      array(
		    $this->SwapBytes_Jquery_Mask->date('fechanacimiento'),
        $this->SwapBytes_Jquery_Mask->phone('telefono')
      )
    );
    $this->SwapBytes_Crud_Form->getAddOrEditLoad();
  }

	public function addoreditconfirmAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $queryString   = $this->_getParam('data');
      $dataRow       = $this->SwapBytes_Uri->queryToArray($queryString);
			$dataRow['pk_usuario'] = (isset($dataRow['pk_usuario'])) ? $dataRow['pk_usuario'] : $dataRow['id'];
			$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow);
			$this->SwapBytes_Crud_Form->setWidthLeft('130px');
			$this->SwapBytes_Crud_Form->setJson(
        array(
          $this->SwapBytes_Jquery_Mask->date('fechanacimiento'),
          $this->SwapBytes_Jquery_Mask->phone('telefono')
        )
      );
			$this->SwapBytes_Crud_Form->getAddOrEditConfirm();
		}
	}

  public function addoreditresponseAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $pageNumber                 = $this->_getParam('page', 1);
      $queryString                = $this->_getParam('data');
      $dataRow                    = $this->SwapBytes_Uri->queryToArray($queryString);
      $dataRow['pk_usuario']      = (isset($dataRow['pk_usuario'])) ? $dataRow['pk_usuario'] : $dataRow['id'];
			$id                         = $dataRow['id'];
			$grupo                      = $this->grupo->getCount($id, ' AND fk_grupo IN (' . $this->fk_grupo_preempleador . ',' . $this->fk_grupo_empleador . ')' );
			$usuario                    = $this->empleadores->getCount($dataRow['pk_usuario']);
			$dataRow['id']              = null;
			$dataRow['page']            = null;
			$dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToDataBase($dataRow['fechanacimiento']);
      $dataRow['nombre']          = $dataRow['primer_nombre'] . ' '. $dataRow['segundo_nombre'];
      $dataRow['apellido']        = $dataRow['primer_apellido'] . ' '. $dataRow['segundo_apellido'];
      if(empty($dataRow['segundo_nombre'])){
        $dataRow['segundo_nombre'] = ' ';
      }
      if(empty($dataRow['segundo_apellido'])){
        $dataRow['segundo_apellido'] = ' ';
      }
      //Si Actualizado es TRUE, crea un arreglo y con eso se identifica que esta actualizado,
      //si no crea un arreglo no entreara al foreach y por ende significa que no esta actualizado.
      $cont = 0;
      foreach ($dataRow['actualizado'] AS $data){
          $cont += 1;
      }
      if($cont == 2){
          $dataRow['actualizado'] = 't';
      }
      else{
          $dataRow['actualizado'] = 'f';
      }
      $dataRow['telefono'] = str_replace('.','',str_replace(')','',str_replace('(','',$dataRow['telefono'])));
      //Defino la Data de la Solicitud
      $dataSolicitud['fk_usuario']            = $dataRow['pk_usuario'];
      $dataSolicitud['fk_institucion']        = $dataRow['id_institucion'];
      $dataSolicitud['fk_estado']             = $dataRow['fk_estado'];
      $id_solicitud                           = $dataRow['id_solicitud'];
      //Limpio dataRow
      unset($dataRow['id'],$dataRow['id_institucion'],$dataRow['fk_estado'],$dataRow['id_solicitud']);
 			// Existe el registro se actualiza. (este debe existir siempre)
			if($grupo >= 1 && $usuario == 1 && isset($id) && $id > 0) {
        //Set Group
        $grupo_preempleador = $this->grupo->getCount($id, ' AND fk_grupo = ' . $this->fk_grupo_preempleador);
        $grupo_empleador = $this->grupo->getCount($id, ' AND fk_grupo = ' . $this->fk_grupo_empleador);
        switch ($dataSolicitud['fk_estado']) {
          case '19969':
            //Aprobado
            if($grupo_preempleador == 1){
              //Redefino Grupo
              $pk = $this->grupo->getPk($id,' AND fk_grupo = ' . $this->fk_grupo_preempleador);
              $this->grupo->updateRow($pk, $id, $this->fk_grupo_empleador);
            }
            break;
          default:
            //No Aprobado (19970) & Por Aprobar (19971)
            if($grupo_empleador == 1){
              $pk = $this->grupo->getPk($id,' AND fk_grupo = ' . $this->fk_grupo_empleador);
              $this->grupo->updateRow($pk, $id, $this->fk_grupo_preempleador);
            }
            break;
        }
				$this->empleadores->updateRow($id, $dataRow);
        $this->solicitudempleador->updateRow($id_solicitud,$dataSolicitud);
			}
			$this->SwapBytes_Crud_Form->getAddOrEditEnd();
    }
  }

  public function deletefinishAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      if(!$this->_getDependencies()) {
        // Obtenemos los parametros que se esperan recibir.
        $pageNumber = $this->_getParam('page', 1);
        $pk_usuario = $this->_getParam('id'  , 0);
        // Eliminamos el registro seleccionado.
        $this->grupo->deleteRow(null, $pk_usuario, $this->fk_grupo_preempleador);
        $this->empleadores->deleteRow($pk_usuario);
      } else if($this->dependencies['grupo'] > 1 && $this->dependencies['recordacademico'] == 0) {
        // Eliminar perfil del estudiante.
        $this->grupo->deleteRow(null, $pk_usuario, $this->fk_grupo_preempleador);
      }
			$this->SwapBytes_Crud_Form->setProperties($this->view->form);
			$this->SwapBytes_Crud_Form->getDeleteFinish();
    }
  }

  public function deleteloadAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      // Obtenemos los parametros que se esperan recibir.
      $pk_usuario = $this->_getParam('id', 0);
      $pageNumber = $this->_getParam('page', 1);
      // Buscar los datos del registro seleccionado.
      $dataRow    = $this->getData($pk_usuario);
			// Verificamos si se puede eliminar el registro.
			$permit     = $this->_getDependencies();
			if($permit) {
				$message = 'No se puede eliminar el estudiante por que<br>tiene dependencias en record académico y/o otros perfiles.';
			}
      // Agregamos los datos necesarios para los controles de tipo HIDDEN,
      // los cuales son necesarios para poder eliminar el registro deseado.
      $dataRow['id']   = $pk_usuario;
      $dataRow['page'] = $pageNumber;
			$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $this->FormTitle_Eliminar, $message);
			$this->SwapBytes_Crud_Form->setWidthLeft('80px');
			$this->SwapBytes_Crud_Form->getDeleteLoad(!$permit);
    }
  }

  private function _getDependencies() {
    if(!is_numeric($dataRow_grupo)) {
        $pk_usuario = $this->_getParam('id'  , 0);
        $dataRow_grupo           = $this->grupo->getCount($id, ' AND fk_grupo NOT IN (' . $this->fk_grupo_preempleador . ',' . $this->fk_grupo_empleador . ')' );
        $dataRow_recordacademico = $this->recordacademico->getCount($pk_usuario);
        $this->dependencies['grupo']           = $dataRow_grupo;
        $this->dependencies['recordacademico'] = $dataRow_recordacademico;
    }
    foreach($this->dependencies as $dependency) {
        if($dependency > 0) {
            return true;
        }
    }
    return false;
  }

  public function viewAction() {
    // Obtenemos los parametros que se esperan recibir.
    $id = $this->_getParam('id', 0);
    // Buscar los datos del registro seleccionado.
    $dataRow = $this->getData($id);
    $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $this->FormTitle_Detalle);
	  $this->SwapBytes_Crud_Form->setWidthLeft('130px');
    $this->SwapBytes_Crud_Form->getView();
  }

  public function helpAction() {
    Zend_Layout::getMvcInstance()->disableLayout();
    Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    $this->render();
  }

}
?>