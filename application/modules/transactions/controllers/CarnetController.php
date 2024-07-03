<?php
/**
 * @todo Ocultar el boton "Ocultar" del formulario Ver.
 * @todo Ocultar el boton "Eliminar" del formulario Eliminar, solo cuando no se
 *       permita.
 */
class Transactions_CarnetController extends Zend_Controller_Action {
    /*
     * Contiene en un arreglo la existencia de las posibles dependencias de
     * registros que provienen de otras tablas, para evitar un desastre en la
     * eliminacion o actualizacion en cascada.
     */
    private $dependencies = array();
    /*
     * Mensajes a mostrar para el usuario:
     */
    private $Title                = 'Transacciones \ Carnet';
    private $FormTitle_Modificar  = 'Modificar datos del carnet';
    private $FormTitle_Detalle    = 'Ver los datos del carnet';
    
    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Carnets');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Forms_Carnet');

        $this->carnets                  = new Models_DbTable_Carnets();
        $this->grupo                    = new Models_DbTable_UsuariosGrupos();
	$this->filtros                  = new Une_Filtros();
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

        $this->view->form = new Forms_Carnet();
        $this->SwapBytes_Form->set($this->view->form);

        /*
         * Configuramos los botones.
         */
	$this->SwapBytes_Crud_Action->setDisplay(true, true, false);
	$this->SwapBytes_Crud_Action->setEnable(true, true, false);
                
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
        
        $this->view->title                 = $this->Title;
        $this->view->filters               = $this->filtros;
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
            $this->carnets->setSearch($searchData);
            $paginatorCount = $this->carnets->getSQLCount();
            $rows           = $this->carnets->getUsuariosCarnets($itemPerPage, $pageNumber);
 
            // Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                           'width' => '800px');
            
            $columns = array(array('column'  => 'pk_usuario',
                                   'primary' => false,
                                   'hide'    => true),
                            array('column'  => 'pk_carnet',
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
                             array('name'    => 'Emisiones',
                                   'width'   => '100px',
                                   'column'  => 'emisiones'),
                             array('name'    => 'Autorización',
                                   'width'   => '120px',
                                   'column'  => 'autorizacion'),
                             array('name'    => 'Afinidad',
                                   'width'   => '120px',
                                   'column'  => 'afinidad'));
                        
            
            foreach($rows AS $key => $row){
                
                $rows[$key]['apellido'] = str_replace("'",  "\'", $row['apellido']);
                
            }

            // Generamos la lista.
            $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'VU');
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
        
        $dataRow = $this->carnets->getRow($id);

        if(isset($dataRow)) {
            
            $emision = $this->carnets->getUsuariosEmision($id);
            $autorizacion = $this->carnets->getAfinidad($id);

            $dataRow['id']              = $id;
            $dataRow['fk_emision']      = $emision[0]['valor'];
            $dataRow['fk_afinidad']     = $autorizacion[0]['afinidad'];
            $dataRow['fk_autorizacion'] = $autorizacion[0]['valor'];
            return $dataRow;
        }
        
    }



	/**
	 * Obtiene la foto de un usuario desde la Base de Datos.
	 */
	public function photoAction() {
            
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$id    = $this->_getParam('id', 0);
		$image = $this->carnets->getPhoto($id);

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
        
        $carnets = $this->carnets->getEmisiones($id_row);
        $autorizaciones = $this->carnets->getAutorizaciones($id_row);
        $autorizacion = $this->carnets->getAfinidad($id_row);


        $this->SwapBytes_Form->fillSelectBox('fk_emision', $carnets, 'pk_emision', 'nombre');
        $this->SwapBytes_Form->fillSelectBox('fk_autorizacion', $autorizaciones, 'pk_atributo', 'autorizacion');
        $this->SwapBytes_Form->fillSelectBox('fk_afinidad', $autorizacion, 'fk_afinidad', 'afinidad');
        
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
        $this->SwapBytes_Crud_Form->enableElement('pk_usuario', $enable);
		$this->SwapBytes_Crud_Form->setWidthLeft('130px');
                
        $this->SwapBytes_Crud_Form->setJson(array($this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$id}'"),
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
            
        $info = $this->carnets->getRow($dataRow['id']);
        $dataRow['pk_usuario'] = $info["pk_usuario"];

		// $dataRow['pk_usuario'] = (isset($dataRow['pk_usuario']))? $dataRow['pk_usuario'] : $dataRow['id'];
        // var_dump($dataRow['pk_usuario']);exit;
		$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow);
		$this->SwapBytes_Crud_Form->setWidthLeft('130px');
                $carnets = $this->carnets->getEmisiones($dataRow['id']);
                $autorizaciones = $this->carnets->getAutorizaciones($dataRow['id']);
                $autorizacion = $this->carnets->getAfinidad($dataRow['id']);
                
                $this->SwapBytes_Form->fillSelectBox('fk_emision', $carnets, 'pk_emision', 'nombre');
                $this->SwapBytes_Form->fillSelectBox('fk_autorizacion', $autorizaciones, 'pk_atributo', 'autorizacion');
                $this->SwapBytes_Form->fillSelectBox('fk_afinidad', $autorizacion, 'fk_afinidad', 'afinidad');
                        
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

            $emision = $this->carnets->getUsuariosEmision($dataRow['id']);

            $this->carnets->setEmision($dataRow['id'], $dataRow['fk_emision']);
            $this->carnets->setAutorizaciones($dataRow['id'], $dataRow['fk_autorizacion']);               
			
	    $this->SwapBytes_Crud_Form->getAddOrEditEnd();
            
        }
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


        $this->view->form->fk_emision->addMultiOption(1,$dataRow['fk_emision']);
        $this->view->form->fk_autorizacion->addMultiOption(1,$dataRow['fk_autorizacion']);
        $this->view->form->fk_afinidad->addMultiOption(1,$dataRow['fk_afinidad']);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $this->FormTitle_Detalle);
	$this->SwapBytes_Crud_Form->setWidthLeft('130px');
        $this->SwapBytes_Crud_Form->setJson(array($this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$id}'")));
        $this->SwapBytes_Crud_Form->getView();
        
    }

}