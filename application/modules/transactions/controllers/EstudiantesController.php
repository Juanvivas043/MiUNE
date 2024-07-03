<?php
/**
 * @todo Ocultar el boton "Ocultar" del formulario Ver.
 * @todo Ocultar el boton "Eliminar" del formulario Eliminar, solo cuando no se
 *       permita.
 */
class Transactions_EstudiantesController extends Zend_Controller_Action {
    /*
     * Contiene en un arreglo la existencia de las posibles dependencias de
     * registros que provienen de otras tablas, para evitar un desastre en la
     * eliminacion o actualizacion en cascada.
     */
    private $dependencies = array();
    /*
     * Codigo de la clave foranea que define el grupo del nuevo usuario, en este
     * caso esta definido como grupo de Estudiantes.
     */
    private $fk_grupo     = 855;
    /*
     * Mensajes a mostrar para el usuario:
     */
    private $Title                = 'Transacciones \ Lista de estudiantes';
    private $FormTitle_Agregar    = 'Agregar nuevo estudiante';
    private $FormTitle_Modificar  = 'Modificar datos del estudiante';
    private $FormTitle_Eliminar   = 'Eliminar datos del estudiante';
    private $FormTitle_Detalle    = 'Ver los datos del estudiante';
    private $FormTitle_Info       = 'Información del estudiante';
    private $FormAlert_Agregar    = 'El usuario ya existe en el sistema como estudiante,<br>porfavor coloque otro número de cédula de identidad para poder ingresarlo.';
    private $FormAlert_Actualizar = 'El usuario ya existe en el sistema, ¿Desea agregarlo como estudiante?';

    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Forms_Estudiantes');

        $this->estudiante      = new Models_DbTable_Usuarios();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->recordacademico = new Models_DbTable_Recordsacademicos();
		$this->filtros         = new Une_Filtros();

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

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        /*
         * Mandamos a crear el formulario para ser utilizado mediante el AJAX.
         */
        $this->view->form = new Forms_Estudiantes();

        /*
         * Configuramos los botones.
         */
		$this->SwapBytes_Crud_Action->setDisplay(true, true, true);
		$this->SwapBytes_Crud_Action->setEnable(true, true, true);

                $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if(!$this->grupo->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

    /**
     * Crea la estructura base de la pagina principal.
     */
    public function indexAction() {
        $this->view->title      = $this->Title;
        $this->view->filters    = $this->filtros;
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
		$this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
		$this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
		$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
    }

    /**
     * Lista el contenido y las acciones pertinentes de una tabla determinada de
     * forma paginada.
     */
    public function listAction() {
    // Verificamos si es una llamada de tipo AJAX.
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            // Obtenemos los parametros necesarios que se esta pasando por POST, y
            // los valores necesarios de las variables que se utilizaran mas adelante.
            $pageNumber  = $this->_getParam('page', 1);
            $searchData  = $this->_getParam('buscar');
            $itemPerPage = 15;
            $pageRange   = 10;

            // Definimos los valores
            $this->estudiante->setSearch($searchData);
            $paginatorCount = $this->estudiante->getSQLCount();
            $rows           = $this->estudiante->getEstudiantes($itemPerPage, $pageNumber);
            // Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                           'width' => '800px');

            $columns = array(array('column'  => 'pk_usuario',
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
                                   'column'  => 'apellido'));

            foreach($rows AS $key => $row){

                $rows[$key]['apellido'] = str_replace("'",  "\'", $row['apellido']);

            }

            // Generamos la lista.
            $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'VUDI');
            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    /**
     * Obtiene los datos del registro y le define el formato valido para ser
     * mostrado en el formulario.
     *
     * @param int $id
     * @return array
     */
    private function getData($id) {
        $dataRow = $this->estudiante->getRow($id);

        if(isset($dataRow)) {
            $dataRow['id']              = $id;
            $dataRow['sexo']            = $this->SwapBytes_Form->setValueToBoolean($dataRow['sexo']);
            $dataRow['nacionalidad']    = $this->SwapBytes_Form->setValueToBoolean($dataRow['nacionalidad']);
            $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);
            $dataRow['actualizado']     = $this->SwapBytes_Form->setValueToCheck($dataRow['actualizado']);
            return $dataRow;
        }
    }

    /**
     * Verifica si el estudiante a agregar existe para mostrar los datos en el
     * formulario.
     */
    public function existsAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json        = array();
            $Status      = true;
            $html        = '';
            $queryString = $this->_getParam('data');
            $id_row      = $this->_getParam('id', 0);
            $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);
            $pk_usuario  = $queryArray['pk_usuario'];

            if(is_numeric($pk_usuario) && !empty($pk_usuario)) {
                // Buscamos si el nuevo usuario tiene un grupo asignado de estudiante.
                $estudiante = $this->grupo->getCount($pk_usuario, ' AND fk_grupo = ' . $this->fk_grupo);
                $usuario    = $this->estudiante->getCount($pk_usuario);
                $dataRow    = $this->estudiante->getRow($pk_usuario);



                // Verificamos si el estudiante existe, al ser asi evitamos que
                // se pueda agregar, de lo contrario lo permitimos.
                if($estudiante == 1 && $usuario == 1) {
                    $Status = false;
                    $html   = $this->SwapBytes_Html_Message->alert($this->FormAlert_Agregar);
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                } else if($estudiante == 0 && $usuario == 0) {
                    $json[] = var_dump($dataRow);
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                } else if($estudiante == 0 && $usuario == 1) {
                    $dataRow['id'] = $pk_usuario;
                    $html   = $this->SwapBytes_Html_Message->alert($this->FormAlert_Actualizar);
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                }

                // Creamos el frmModal con los datos necesarios.
                //$dataRow['pk_usuario'] = $pk_usuario;

                $this->SwapBytes_Form->set($this->view->form);

                if(isset($dataRow)) {
					$dataRow['nacionalidad'] = (empty($dataRow['nacionalidad']))? 'f' : 't';
					$dataRow['sexo']         = (empty($dataRow['sexo']))?         'f' : 't';

                    $this->view->form->populate($dataRow);
                }

                // Definimos el acceso a los controles del frmModal.
                $this->SwapBytes_Form->enableElement('nacionalidad'   , $Status);
                $this->SwapBytes_Form->enableElement('sexo'           , $Status);
                $this->SwapBytes_Form->enableElement('primer_nombre'  , $Status);
                $this->SwapBytes_Form->enableElement('segundo_nombre' , $Status);
                $this->SwapBytes_Form->enableElement('primer_apellido', $Status);
                $this->SwapBytes_Form->enableElement('segundo_apellido', $Status);
                $this->SwapBytes_Form->enableElement('direccion'      , $Status);
                $this->SwapBytes_Form->enableElement('correo'         , $Status);
                $this->SwapBytes_Form->enableElement('fechanacimiento', $Status);
                $this->SwapBytes_Form->enableElement('telefono'       , $Status);

                $this->view->form = $this->SwapBytes_Form->get();

                // Preparamos el frmModal para ser enviado por AJAX.
                $html  .= $this->SwapBytes_Ajax->render($this->view->form);

                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
                $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
				$json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$pk_usuario}'");

                $this->getResponse()->setBody(Zend_Json::encode($json));
            } else {
				$html  .= $this->SwapBytes_Ajax->render($this->view->form);

                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
                $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');

				$this->getResponse()->setBody(Zend_Json::encode($json));
			}
        }
    }

	/**
	 * Obtiene la foto de un usuario desde la Base de Datos.
	 */
	public function photoAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$id    = $this->_getParam('id', 0);
		$image = $this->estudiante->getPhoto($id);

		$this->getResponse()
		     ->setHeader('Content-type', 'image/jpeg')
		     ->setBody($image);
	}

    /**
     * Permite construir y mostrar el formulario de tipo modal, este puede ser
     * utilizado para la acción agregar o modificar.  No deja seguir al siguiente
	 * action "addoreditconfirmAction" hasta que la validación sea correcta.
     */
    public function addoreditloadAction() {
        // Obtenemos los parametros que se esperan recibir.
        $id_row     = $this->_getParam('id'  , 0);

        if(is_numeric($id_row) && $id_row > 0) {
            // Agregamos algunos valores al formulario.
            $dataRow         = $this->getData($id_row);



            $dataRow['page'] = $this->_getParam('page', 1);

            $enable = false;
            $title = $this->FormTitle_Modificar;
        } else {
            $enable = true;
            $title = $this->FormTitle_Agregar;
        }

        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
        $this->SwapBytes_Crud_Form->enableElement('pk_usuario', $enable);
		$this->SwapBytes_Crud_Form->setWidthLeft('130px');
        $this->SwapBytes_Crud_Form->setJson(array($this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$id_row}'"),
				     							  $this->SwapBytes_Jquery_Mask->date('fechanacimiento'),
                                                  $this->SwapBytes_Jquery_Mask->phone('telefono')));
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();

    }

	/**
	 * Valida los datos capturados por el formulario de tipo modal. No deja seguir
	 * al siguiente action "addoreditresponseAction" hasta que la validación sea
	 * correcta.
	 */
	public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $queryString   = $this->_getParam('data');
            $dataRow       = $this->SwapBytes_Uri->queryToArray($queryString);
			$dataRow['pk_usuario'] = (isset($dataRow['pk_usuario']))? $dataRow['pk_usuario'] : $dataRow['id'];

			$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow);
			$this->SwapBytes_Crud_Form->setWidthLeft('130px');
			$this->SwapBytes_Crud_Form->setJson(array($this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$dataRow['pk_usuario']}'"),
					                                  $this->SwapBytes_Jquery_Mask->date('fechanacimiento'),
													  $this->SwapBytes_Jquery_Mask->phone('telefono')));
			$this->SwapBytes_Crud_Form->getAddOrEditConfirm();
		}
	}

    /**
     * Permite guardar el contenido de un determinado registro mediante una serie
     * de datos que fueron capturados por un formulario modal.
     */
    public function addoreditresponseAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $pageNumber                 = $this->_getParam('page', 1);
            $queryString                = $this->_getParam('data');
            $dataRow                    = $this->SwapBytes_Uri->queryToArray($queryString);


            $dataRow['pk_usuario']      = (isset($dataRow['pk_usuario']))? $dataRow['pk_usuario'] : $dataRow['id'];
      			$id                         = $dataRow['id'];
      			$grupo                      = $this->grupo->getCount($id, ' AND fk_grupo = ' . $this->fk_grupo);
      			$usuario                    = $this->estudiante->getCount($dataRow['pk_usuario']);
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
                        }else{
                            $dataRow['actualizado'] = 'f';
                        }

                        $dataRow['telefono'] = str_replace('.','',str_replace(')','',str_replace('(','',$dataRow['telefono'])));



			// Existe el registro se actualiza.
			if($grupo >= 1 && $usuario == 1 && isset($id) && $id > 0) {
				$this->estudiante->updateRow($id, $dataRow);
			// No existe el registro, se agreha.
			} else if($grupo == 0 && $usuario == 0 && empty($id)) {
      			$dataRow['passwordhash']    = md5($dataRow['pk_usuario']);
				$this->estudiante->addRow($dataRow);
				$this->grupo->addRow($dataRow['pk_usuario'], $this->fk_grupo);
			} else if($grupo == 0 && $usuario == 1 && isset($id)) {
				$this->grupo->addRow($id, $this->fk_grupo);
			}

			$this->SwapBytes_Crud_Form->getAddOrEditEnd();
        }
    }

    /**
     * Elimina de forma definitiva en registro determinado.
     */
    public function deletefinishAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            /*
             * Verificamos que no tenga ninguna depedencia para eliminar al
             * estudiante, en caso contrario, y solo en el caso de que el
             * estudiante NO tenga nada realacionada a lo academico se le
             * elimina unicamente el perfil de estudiante.
             */
            if(!$this->_getDependencies()) {
            // Obtenemos los parametros que se esperan recibir.
                $pageNumber = $this->_getParam('page', 1);
                $pk_usuario = $this->_getParam('id'  , 0);

                // Eliminamos el registro seleccionado.
                $this->grupo->deleteRow(null, $pk_usuario, $this->fk_grupo);
                $this->estudiante->deleteRow($pk_usuario);
            } else if($this->dependencies['grupo'] > 1 && $this->dependencies['recordacademico'] == 0) {
                // Eliminar perfil del estudiante.
                $this->grupo->deleteRow(null, $pk_usuario, $this->fk_grupo);
            }

			$this->SwapBytes_Crud_Form->setProperties($this->view->form);
			$this->SwapBytes_Crud_Form->getDeleteFinish();
        }
    }

    /**
     * Muestra un mensaje de confirmaci√≥n para poder eliminar un registro
     * determinado.
     */
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

    /**
     * Verifica si existen dependencias en otras tablas, con el fin de poder
     * eliminar un registro correctamente sin necesidad de aplicar la eliminacion
     * por cascada.
     *
     * @return boolean
     */
    private function _getDependencies() {
        if(!is_numeric($dataRow_grupo)) {
            $pk_usuario = $this->_getParam('id'  , 0);

            $dataRow_grupo           = $this->grupo->getCount($pk_usuario, 'AND fk_grupo <> ' . $this->fk_grupo);
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

    /**
     * Muestra mediante un formulario modal con todos los objetos del mismo
     * bloqueados, con el fin de poder visualizar todos los datos importantes
     * del registro seleccionado.
     */
    public function viewAction() {
        // Obtenemos los parametros que se esperan recibir.
        $id = $this->_getParam('id', 0);

        // Buscar los datos del registro seleccionado.
        $dataRow = $this->getData($id);

        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $this->FormTitle_Detalle);
		$this->SwapBytes_Crud_Form->setWidthLeft('130px');
        $this->SwapBytes_Crud_Form->setJson(array($this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$id}'")));
        $this->SwapBytes_Crud_Form->getView();
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
            $pk_usuario = $this->_getParam('id', 0);

            // Buscar los datos del registro seleccionado.
            $dataRow    = $this->getData($pk_usuario);

            $Info = $this->recordacademico->getInformacionGeneral($pk_usuario);

            if(isset($Info) && is_array($Info) && count($Info) > 0) {
                $properties = array('width' => '550',
                                    'align' => 'center');

                $styles = array(array('style' => 'text-align:right;font-size:14px;font-weight:bold'),
                                array('style' => 'text-align:left;font-size:14px'));

                $data[] = array('Sede:'                               , $Info['sede']);
                $data[] = array('Escuela Inscrita:'                   , $Info['escuela']);
                $data[] = array('Pensum:'                             , $Info['pensum']);
                $data[] = array('Período Académico Inscrito:'         , "{$Info['upi_fecha']} ({$Info['upi']})");
                $data[] = array('Ultimo Período Académico Inscrito:'  , "{$Info['upc_fecha']} ({$Info['upc']})");
                $data[] = array('Semestre de Ubicación:'              , $Info['su']);
                $data[] = array('Indice Académico del Ultimo Período:', $Info['iap_upc']);
                $data[] = array('Unidades de Credito Aprobadas:'      , $Info['uca']);
                $data[] = array('Indice Académico Acumulado:'         , $Info['iaa']);
                $data[] = array('Estado del estudiante:'              , $Info['estado']);

                $html  = $this->SwapBytes_Html->table($properties, $data, $styles);
            } else {
                $html = "El estudiante no tiene informaci&oacute;n acad&eacute;mica registrada.";
            }

            // Envia los datos al modal.
            $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 320);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 580);
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
     * Carga la ayuda del modulo, basicamente es una pagina html no dinamica que
     * contiene toda la informaci‚àö‚â•n relevante del modulo.
     */
    public function helpAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $this->render();
    }
}
