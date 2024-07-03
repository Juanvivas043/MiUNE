<?php
class Transactions_InstitucionesController extends Zend_Controller_Action {   
 
    private $_Title             = 'Transacciones \ Lista de Instituciones';
    private $FormAlert_Exist    = 'El RIF ya se encuentra utilizado por otra Institucion, verifique que el RIF este correctamente escrito';
    private $Connection_Failed  = 'Error al establecer Conexión con el servidor del Seniat, por favor intente mas tarde';
    private $No_Exist           = 'Error el RIF escrito no existe, por favor verifique que este escrito correctamente';
    private $used_rif           = false;

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Une_Seniat');
        Zend_Loader::loadClass('Models_DbTable_Instituciones');
        Zend_Loader::loadClass('Models_DbView_Tiposinstituciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Forms_Institucion');
         
        $this->Instituciones            = new Models_DbTable_Instituciones();
        $this->vw_tiposinstituciones    = new Models_DbView_Tiposinstituciones();
        $this->grupo                    = new Models_DbTable_UsuariosGrupos();
        $this->filtros                  = new Une_Filtros();
        $this->seniat                   = new Une_Seniat();
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
        $this->authSpace                = new Zend_Session_Namespace('Zend_Auth');
        // Obtiene los parametros del modal.
		$this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();           
        // BOTONES DE ACCIONES        
        $this->SwapBytes_Crud_Action->setDisplay(true, true, true, true, false, false);
	    $this->SwapBytes_Crud_Action->setEnable(true, true, true, true, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(true);  
        // FORM  
        $this->view->form = new Forms_Institucion();
        $this->SwapBytes_Form->set($this->view->form);
        $this->SwapBytes_Form->fillSelectBox('fk_tipopasantia',  $this->vw_tiposinstituciones->getTipos(8222)  , 'pk_atributo', 'institucion');
        $this->SwapBytes_Form->fillSelectBox('tipo_rif',$this->seniat->array_rif, NULL, NULL);
        $this->view->form = $this->SwapBytes_Form->get();
    }

    function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
        if (!$this->grupo->haveAccessToModule()) {
    	  $this->_helper->redirector('accesserror', 'profile', 'default');
    	}    
    }
    
    // Crea la estructura base de la pagina principal.
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

    // Verifcamos RIF y enviamos a la vista
    public function getrifAction(){
        if(!$this->used_rif){
            $json_array = array();
            if($this->_request->isXmlHttpRequest()){
                $this->SwapBytes_Ajax->setHeader();
                //Consultamos informacion del RIF
                $data = $this->seniat->getRifInformation($_GET['rif'],$_GET['tipo_rif']);
                if(!empty($data)){
                    $json_array[] = true;
                    $json_array[] = $data['rif'];
                    $json_array[] = $data['razonsocial'];
                    echo Zend_Json::encode($json_array);
                }
                else {
                    $json_array[] = false; $json_array[] = null;  $json_array[] = null;
                    echo Zend_Json::encode($json_array);
                }
            }
        }
    }

    //Verifica que el RIF no este siendo utilizado por otra Institucion
    public function existsAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json        = array();
            $Status      = true;
            $html        = '';
            $queryString = $this->_getParam('data');
            //$id_row      = $this->_getParam('id',0);
            $id_row      = $this->_params['modal']['id'];
            $type_rif    = $this->_params['modal']['tipo_rif'];
            $rif         = $this->seniat->setRif($this->_params['modal']['rif'],$this->_params['modal']['tipo_rif']);
            if(!empty($rif)) {
                $dataRow        = $this->Instituciones->getRif($rif);
                $dataRow        = $dataRow[0];
                // Verificamos si la institucion existe, al ser asi evitamos que 
                // se pueda agregar, de lo contrario lo permitimos.
                if(empty($dataRow)) {
                    $Status         = true;
                    $this->used_rif = false;
                    $json[]         = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                    $json[]         = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                } 
                else if(!empty($dataRow) and $dataRow['fk_tipopasantia'] == 8222) {
                    $dataRow['tipo_rif'] = substr($dataRow['rif'],0,1);
                    $dataRow['rif']      = str_replace($this->seniat->array_rif,'',$dataRow['rif']);
                    $Status              = false;
                    $this->used_rif      = true;
                    $html                = $this->SwapBytes_Html_Message->alert($this->FormAlert_Exist);
                    $json[]              = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                    $json[]              = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                }
                // Creamos el frmModal con los datos necesarios.
                $this->SwapBytes_Form->set($this->view->form);
                // Cargamos los Datos del Formulario
                if(!empty($dataRow) and $dataRow['fk_tipopasantia'] == 8222){ 
                    $dataRow['id']  = $id_row;
                    //Si existe Institucion lleno los datos de la misma
                    $this->view->form->populate($dataRow);
                    $this->used_rif = true;
                }
                else {
                    //Si no existe pero estoy editando una institucion (cargo informacion previa)
                    if($id_row > 0){
                        $row = $this->Instituciones->getRow($id_row);
                        $row['tipo_rif'] = substr($row['rif'],0,1);
                        $row['rif']      = str_replace($this->seniat->array_rif,'',$rif);
                        $row['id']       = $id_row;
                        $this->view->form->populate($row);
                    }
                    //Si no existe blanqueo los campos, menos el RIF
                    else {
                        $data = array();
                        $data['id']             = $id_row;
                        $data['tipo_rif']       = $type_rif;
                        $data['pk_institucion'] = null;
                        $data['nombre']         = null;
                        $data['direccion']      = null;
                        $data['telefono']       = null;
                        $data['telefono2']      = null;
                        $data['fk_tipopasantia']= null;
                        $data['rif']            = str_replace($this->seniat->array_rif,'',$rif);
                        $data['razonsocial']    = null;
                        $this->view->form->setDefaults($data);
                    }
                    $this->used_rif = false;
                }
                // Definimos el acceso a los controles del frmModal.
                $this->SwapBytes_Form->enableElement('nombre'          , $Status);
                $this->SwapBytes_Form->enableElement('fk_tipopasantia' , $Status);
                $this->SwapBytes_Form->enableElement('direccion'       , $Status);
                $this->SwapBytes_Form->enableElement('telefono'        , $Status);
                $this->SwapBytes_Form->enableElement('telefono2'       , $Status);

                $this->view->form = $this->SwapBytes_Form->get();

                // Preparamos el frmModal para ser enviado por AJAX.
                $html  .= $this->SwapBytes_Ajax->render($this->view->form);
                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono2');
                $this->getResponse()->setBody(Zend_Json::encode($json));
            } 
        }
    }
    
    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $searchData  = $this->_getParam('buscar');
            $json   = array();
            $this->Instituciones->setSearch($searchData);           
         
            $rows = $this->Instituciones->getListaInstituciones();

            if(isset($rows) && count($rows) > 0) {
                // Definimos las propiedades de la tabla.
                $table = array('class' => 'tableData',
                               'width' => '1100px');
                $columns = array(array('column'  => 'pk_institucion',
                                       'primary' => true,
                                       'hide'    => true),
                                 array('name'    => array('control' => array('tag'  => 'input',
                                                                             'type' => 'checkbox',
                                                                             'name' => 'chkSelectDeselect')),
                                       'column'  => 'nc',
                                       'width'   => '20px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'control' => array('tag'   => 'input',
                                                          'type'  => 'checkbox',
                                                          'name'  => 'chkInstitucion',
                                                          'value' => '##pk_institucion##')),
                                 array('name'    => 'Nombre',
                                       'width'   => '200px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'nombre'),
                                 array('name'    => 'R.I.F',
                                       'width'   => '100px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'rif'),
                                 array('name'    => 'Razon Social',
                                       'width'   => '200px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'razonsocial'),
                                 array('name'    => 'Direccion',
                                       'width'   => '400px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'direccion'));
                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VU');       
       
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkInstitucion');
            } 
            else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen Instituciones cargadas.");
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }    

    public function addoreditloadAction() {
        if ($this->_request->isXmlHttpRequest()){
            if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {               
                $title = 'Editar Institucion';
        		$dataRow                    = $this->Instituciones->getRow($this->_params['modal']['id']);
                $dataRow['id']              = $this->_params['modal']['id'];
        		$dataRow['pk_institucion']  = $this->_params['modal']['id'];
                $dataRow['fk_tipopasantia'] = $dataRow['fk_tipopasantia'];
                $dataRow['nombre']          = strtoupper($dataRow['nombre']);
                //Armamos el RIF correctamente
                $dataRow['rif']             = str_replace($this->seniat->array_rif,'',$dataRow['rif']);
            }
            else {           
                $title = 'Agregar Institucion'; 
        	}              
            $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
            $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono2');
            //Registramos los datos
            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
        	$this->SwapBytes_Crud_Form->setJson($json);
        	$this->SwapBytes_Crud_Form->setWidthLeft('90px');
        	$this->SwapBytes_Crud_Form->getAddOrEditLoad();
        }    
    }
   
    // hacemos las validaciones a editar o agregar       
    public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
            $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono2');
            //Segun el RIF del modal, consultamos
            //Arreglamos el RIF y Traemos la Razon Social correspondiente del mismo (se ignora la del modal)
            $data_dump['id']                = $this->_params['modal']['id'];
            $data_dump['fk_tipopasantia']   = $this->_params['modal']['fk_tipopasantia'];
            $data_dump['nombre']            = strtoupper($this->_params['modal']['nombre']);
            $data_dump['direccion']         = $this->_params['modal']['direccion'];
            $data_dump['telefono']          = $this->_params['modal']['telefono'];
            $data_dump['telefono2']         = $this->_params['modal']['telefono2'];
            $data_dump['rif']               = $this->seniat->setRif($this->_params['modal']['rif'],$this->_params['modal']['tipo_rif']);
            //Ingresamos los Datos
			$this->SwapBytes_Crud_Form->setJson($json);
			$this->SwapBytes_Crud_Form->setProperties($this->view->form, $data_dump);
			$this->SwapBytes_Crud_Form->getAddOrEditConfirm();
		}
	}

    // Editamos o Agregamos        
    public function addoreditresponseAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            // Obtenemos los parametros que se esperan recibir
            $dataRow                = $this->_params['modal'];
            $id                     = $dataRow['id'];
            $dataRow['id']          = null;
            if(!empty($dataRow)){
                //Llenamos Data
                $data_dump['fk_tipopasantia']   = $dataRow['fk_tipopasantia'];
                $data_dump['nombre']            = strtoupper($dataRow['nombre']);
                $data_dump['direccion']         = $dataRow['direccion'];
                $data_dump['telefono']          = $dataRow['telefono'];
                $data_dump['telefono2']         = $dataRow['telefono2'];
                // Consultamos informacion del RIF
                $data = $this->seniat->getRifInformation($this->_params['modal']['rif'],$this->_params['modal']['tipo_rif']);
                if(!empty($data)) { 
                    $data_dump['rif']          = $data['rif'];
                    $data_dump['razonsocial']  = $data['razonsocial'];
                    $success                   = true;  
                } else { /*Se debera cambiar para el rif sea requisito*/$success = true; }
                if($success){
                    // La data obtenida del servidor del seniat fue exitosa
                    if(is_numeric($id) && $id > 0) {
                        $this->Instituciones->updateRow($id,$data_dump);
                    }
                    else {
                        $this->Instituciones->addRow($data_dump);            
                    }
                    // Cierro modal
                    $this->SwapBytes_Crud_Form->getAddOrEditEnd();
                }
                else {
                    // Notificacion de error de conexion
                    $html  = $this->SwapBytes_Html_Message->alert($this->Connection_Failed);
                    // Estado de visibilidad de los botones
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                    // Setea la notifiacion
                    $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                    $this->getResponse()->setBody(Zend_Json::encode($json));
                    // Creamos el frmModal con los datos necesarios.
                    $this->SwapBytes_Form->set($this->view->form);
                    // Cargamos los Datos del Formulario
                    $this->view->form->populate($data_dump);
                    $Status = true;
                    // Setea el estado de los input
                    $this->SwapBytes_Form->enableElement('nombre'          , $Status);
                    $this->SwapBytes_Form->enableElement('fk_tipopasantia' , $Status);
                    $this->SwapBytes_Form->enableElement('direccion'       , $Status);
                    $this->SwapBytes_Form->enableElement('telefono'        , $Status);
                    $this->SwapBytes_Form->enableElement('telefono2'       , $Status);
                    $this->view->form = $this->SwapBytes_Form->get();
                    // Preparamos el frmModal para ser enviado por AJAX.
                    $html  .= $this->SwapBytes_Ajax->render($this->view->form);
                    $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
                    $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
                    $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono2');
                    $this->getResponse()->setBody(Zend_Json::encode($json));
                }
            }
		}
    }         

    // cargamos Formulario ver
    public function viewAction() {       
        $dataRow = $this->Instituciones->getRow($this->_params['modal']['id']);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Institucion');
        $this->SwapBytes_Crud_Form->getView();
    }
  
    // Eliminamos datos seleccionados    
    public function deleteloadAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
			
            $Params = $this->_params['modal'];

			if(isset($Params['chkInstitucion'])) {
				if(is_array($Params['chkInstitucion'])) {
					foreach($Params['chkInstitucion'] as $institucion) {
						$this->Instituciones->deleteRow($institucion);
					}
				} 
                else {
					$this->Instituciones->deleteRow($Params['chkInstitucion']);
				}
				$this->SwapBytes_Crud_Form->getDeleteFinish();
			}
		}
    } 
}
