<?php
class Transactions_PasantesController extends Zend_Controller_Action {   
 

    private $_Title   = 'Transacciones \ Lista de Pasantes de MiUNE';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios'); 
        Zend_Loader::loadClass('Models_DbTable_Pasantes'); 
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbView_Grupos');
        Zend_Loader::loadClass('Forms_Usuario');
        
        
        $this->Usuarios         = new Models_DbTable_Usuarios();
        $this->Pasantes         = new Models_DbTable_Pasantes();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->recordacademico  = new Models_DbTable_Recordsacademicos();
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
	$this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
	$this->SwapBytes_Uri            = new SwapBytes_Uri();
	$this->SwapBytes_Jquery         = new SwapBytes_Jquery();
	$this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->AuthSpace                = new Zend_Session_Namespace('Zend_Auth');  
        
        $this->filtros->setDisplay(true, true, true, true, false, false, false, false, false);
    $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
    $this->filtros->setRecursive(false, true, true, true, false, false, false, false, false);
            
                             
        
        // SELECT * FROM  fn_xrxx_perfil_principal_usuario(19022630) AS (pk_grupo bigint , grupo character varying) 

                $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
        
                
   
        //	 * Obtiene los parametros del modal.
		$this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();   
                
          
                
//      BOTONES DE ACCIONES
                
        $this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, true, false, true, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(true);  

//    FORM 
           
     $this->view->form = new Forms_Usuario();

            $this->SwapBytes_Form->set($this->view->form);
//    $this->SwapBytes_Form->fillSelectBox('fk_grupo',  $this->vw_grupos->getGrupoespecifico($this->fk_grupo)  , 'pk_atributo', 'grupo'); 
     $this->view->form = $this->SwapBytes_Form->get();
     //echo ($this->_params['filters']['Periodo']);
     if (isset($this->_params['filters']['Periodo'])){
   // $this->view->form->getElement('periodo')->setValue($this->_params['filters']['Periodo']) ;
    }
}

  function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
       if (!$this->grupo->haveAccessToModule()) {
    	  $this->_helper->redirector('accesserror', 'profile', 'default');
    	}    
    }
    
//     * Crea la estructura base de la pagina principal.
  
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
    
    public function sedeAction() {
    $this->filtros->getAction();
  }

  public function escuelaAction() {
    $this->filtros->getAction();
  }

  public function pensumAction() {
    $this->filtros->getAction();
  }

    
    
   public function listAction() {
        
     if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            //var_dump($this->_params['filters']);die;
            $pageNumber  = $this->_getParam('page', 1);
            $searchData  = $this->_getParam('buscar');
            $itemPerPage = 15;
            $pageRange   = 10;
            $json   = array();
            $queryString = $this->_getParam('filters');
            //var_dump($this->_params['filters']['selPeriodo']);die;
            
            $this->Pasantes->setSearch($searchData);
           //var_dump($this->Pasantes->setSearch($searchData));die;
              $paginatorCount = $this->Pasantes->totalpasantes($this->_params['filters']['selPeriodo'],$this->_params['filters']['selSede'],$this->_params['filters']['selEscuela'],$this->_params['filters']['selPensum']);
            //$rows = $this->Usuarios->getUsuarios($itemPerPage, $pageNumber);
              $rows = $this->Pasantes->getPasantes($this->_params['filters']['selPeriodo'],$this->_params['filters']['selSede'],$this->_params['filters']['selEscuela'],$this->_params['filters']['selPensum'],$itemPerPage, $pageNumber);
              //var_dump($rows);die;
              
              

            if(isset($rows) && count($rows) > 0) {
           

// Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                           'width' => '600px');

            $columns = array(array('column'  => 'pk_usuario',
                                   'primary' => true,
                                   'hide'    => true),
                             
                             array('name'    => 'C.I.',
                                       'width'   => '70px',
                                       'column'  => 'pk_usuario',
                                       'rows'    => array('style' => 'text-align:center')),
                             array('name'    => 'Apellido',
                                   'width'   => '200px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'apellido'),
                             array('name'    => 'Nombre',
                                   'width'   => '200px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'nombre')
                             );


                $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'V');
       
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkUsuario');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen Usuario cargados.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

           $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }
      

public function existsAction() {
    
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json        = array();
            $Status      = true;
            $html        = '';
            $queryString = $this->_getParam('data');
            
            $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);
            $pk_usuario  = $queryArray['pk_usuario'];
            $grupo  = $queryArray['perfil'];
        
            if(is_numeric($pk_usuario) && !empty($pk_usuario)) {
                // Buscamos si el nuevo usuario tiene un grupo asignado.
                $usuariogrupo = $this->grupo->getCount($pk_usuario, ' AND fk_grupo =' . $grupo);
                $usuario    = $this->Usuarios->getCount($pk_usuario);
                $dataRow    = $this->Usuarios->getRow($pk_usuario);
                
                $dataRow['nacionalidad']    = $this->SwapBytes_Form->setValueToBoolean($dataRow['nacionalidad']);
                $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);
                // Verificamos si el Usuario existe, al ser asi evitamos que
                // se pueda agregar, de lo contrario lo permitimos.
                if($usuariogrupo == 1 && $usuario == 1) {
                    $Status = true;
                    $html   = $this->SwapBytes_Html_Message->alert('Este Numero de Cedula ya se encuentra en el sistema y ya tiene el perfil asignado.');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                } else if($usuariogrupo == 0 && $usuario == 0) {
                    $json[] = var_dump($dataRow);
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                } else if($usuariogrupo == 0 && $usuario == 1) {
                    $Status = true;
                    $dataRow['id'] = $pk_usuario;
                    $html   = $this->SwapBytes_Html_Message->alert('El usuario ya existe en el sistema, ¿Desea agregarlo al perfil?');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                }

                 // Creamos el frmModal con los datos necesarios.
                //  $dataRow['pk_usuario'] = $pk_usuario;

                $this->SwapBytes_Form->set($this->view->form);

                if(isset($dataRow)) {
					$dataRow['nacionalidad'] = (empty($dataRow['nacionalidad']))? 't' : 'f';
					$dataRow['sexo']         = (empty($dataRow['sexo']))?         'f' : 't';
					
                    $this->view->form->populate($dataRow);
                }

                // Definimos el acceso a los controles del frmModal.
                $this->SwapBytes_Form->readOnlyElement('nacionalidad'    , $Status);
                $this->SwapBytes_Form->readOnlyElement('sexo'            , $Status);
                $this->SwapBytes_Form->readOnlyElement('primer_nombre'   , $Status);
                $this->SwapBytes_Form->readOnlyElement('segundo_nombre'  , $Status);
                $this->SwapBytes_Form->readOnlyElement('primer_apellido' , $Status);
                $this->SwapBytes_Form->readOnlyElement('segundo_apellido', $Status);
                $this->SwapBytes_Form->readOnlyElement('direccion'       , $Status);
                $this->SwapBytes_Form->readOnlyElement('correo'          , $Status);
                $this->SwapBytes_Form->readOnlyElement('fechanacimiento' , $Status);
                $this->SwapBytes_Form->readOnlyElement('telefono'        , $Status);
                $this->SwapBytes_Form->readOnlyElement('telefono_movil'  , $Status);

                $this->view->form = $this->SwapBytes_Form->get();

                // Preparamos el frmModal para ser enviado por AJAX.
                $html  .= $this->SwapBytes_Ajax->render($this->view->form);

                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
                $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
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
    
    
public function photoAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$id    = $this->_getParam('id', 0);
		$image = $this->Usuarios->getPhoto($id);

		$this->getResponse()
		     ->setHeader('Content-type', 'image/jpeg')
		     ->setBody($image);
	}    

public function addoreditloadAction() {
    if ($this->_request->isXmlHttpRequest()){ 
    if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
    if ($this->grupo->getCount($this->_params['modal']['id']) > 1) {
               
                $this->SwapBytes_Form->readOnlyElement('nacionalidad'    , True);
                $this->SwapBytes_Form->readOnlyElement('sexo'            , True);
                $this->SwapBytes_Form->readOnlyElement('primer_nombre'   , True);
                $this->SwapBytes_Form->readOnlyElement('segundo_nombre'  , True);
                $this->SwapBytes_Form->readOnlyElement('primer_apellido' , True);
                $this->SwapBytes_Form->readOnlyElement('segundo_apellido', True);
                $this->SwapBytes_Form->readOnlyElement('direccion'       , True);
                $this->SwapBytes_Form->readOnlyElement('correo'          , True);
                $this->SwapBytes_Form->readOnlyElement('fechanacimiento' , True);
                $this->SwapBytes_Form->readOnlyElement('telefono'        , True);
                $this->SwapBytes_Form->readOnlyElement('telefono_movil'  , True);
                $this->SwapBytes_Form->readOnlyElement('pk_usuario', True);
                $this->SwapBytes_Form->readOnlyElement('perfil', True);
    }                    
    
        $title = 'Editar Usuario';
        
			$dataRow = $this->Usuarios->getRow($this->_params['modal']['id']);
                        $dataRow['id'] = $this->_params['modal']['id'];
			$dataRow['pk_usuario']  = $this->_params['modal']['id'];
                        $dataRow['perfil'] = $dataRow['perfil'];
                        $dataRow['sexo']            = $this->SwapBytes_Form->setValueToBoolean($dataRow['sexo']);
                        $dataRow['nacionalidad']    = $this->SwapBytes_Form->setValueToBoolean($dataRow['nacionalidad']);
                        $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);
                        $this->SwapBytes_Form->readOnlyElement('pk_usuario', True);
        } else {          
            
            $title = 'Agregar Usuario';                      
	}

         $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
         $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
         $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
         $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$dataRow['pk_usuario']}'");
        
         $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
	$this->SwapBytes_Crud_Form->setJson($json);
	$this->SwapBytes_Crud_Form->setWidthLeft('120px');
	$this->SwapBytes_Crud_Form->getAddOrEditLoad();
  }    
}
   
    

// hacemos las validaciones a editar o agregar       
       public function addoreditconfirmAction() {
        
           if ($this->_request->isXmlHttpRequest()) {
//            var_dump($this->_params['modal']);
               $this->SwapBytes_Ajax->setHeader();
  
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
                $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');

			$this->SwapBytes_Crud_Form->setJson($json);
			$this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
			$this->SwapBytes_Crud_Form->getAddOrEditConfirm();
		}
	}

// Editamos o Agregamos        
        public function addoreditresponseAction() {
        
            if ($this->_request->isXmlHttpRequest()) {
                
                $this->SwapBytes_Ajax->setHeader();
			
// Obtenemos los parametros que se esperan recibir
		
			$dataRow            = $this->_params['modal'];
                        $id                 = $dataRow['id'];
                        $grupo              =  $dataRow['perfil'];
                        
                        $dataRow['pk_usuario']      = (isset($dataRow['pk_usuario']))? $dataRow['pk_usuario'] : $dataRow['id'];
                        $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToDataBase($dataRow['fechanacimiento']);
                        $usuario                    = $this->Usuarios->getCount($dataRow['pk_usuario']);
                        $gruposcount                = $this->grupo->getCount($id, ' AND fk_grupo = ' . $grupo);
                        $dataRow['nombre']          = $dataRow['primer_nombre'] . ' '. $dataRow['segundo_nombre'];
                        $dataRow['apellido']        = $dataRow['primer_apellido'] . ' '. $dataRow['segundo_apellido'];
                        $dataRow['passwordhash']    = md5($dataRow['pk_usuario']); 
                        
			$dataRow['id']         = null;
                        $dataRow['perfil']   = null;
//                        $this->_params['modal']['nombre']          = $dataRow['primer_nombre'] . ' '. $dataRow['segundo_nombre']; 
//                        $this->_params['modal']['apellido']        = $dataRow['primer_apellido'] . ' '. $dataRow['segundo_apellido']; 
//                        $this->_params['modal']['passwordhash']    = md5($dataRow['pk_usuario']); 
                        $this->_params['modal']['perfil']        = null;
                        $this->_params['modal']['fechanacimiento'] = $dataRow['fechanacimiento'];
                        
			if($gruposcount >= 1 && $usuario == 1 && isset($id) && $id > 0) {
				$this->Usuarios->updateRow($id, $dataRow);
			// No existe el registro, se agrega.
			} else if($gruposcount == 0 && $usuario == 0 && empty($id)) {
				$this->Usuarios->addRow($dataRow);
				$this->grupo->addRow($dataRow['pk_usuario'], $grupo);
			} else if($gruposcount == 0 && $usuario == 1 && isset($id)) {
				$this->grupo->addRow($id, $grupo);
			}
			
			$this->SwapBytes_Crud_Form->getAddOrEditEnd();
		}
        }         

// cargamos Formulario ver
    public function viewAction() {
       
        $dataRow = $this->Usuarios->getRow($this->_params['modal']['id']);
        $dataRow['pk_usuario']  = $this->_params['modal']['id'];
        $dataRow['perfil']  = $dataRow['perfil'] ;
        $dataRow['sexo']            = $this->SwapBytes_Form->setValueToBoolean($dataRow['sexo']);
        $dataRow['nacionalidad']    = $this->SwapBytes_Form->setValueToBoolean($dataRow['nacionalidad']);
        $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);
        
                 $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
         $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
         $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
         $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$dataRow['pk_usuario']}'");
        
        $this->SwapBytes_Crud_Form->setJson($json); 
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Usuario');
        $this->SwapBytes_Crud_Form->getView();

    }
  
    
     public function deleteloadAction() {
	$message = '¿Esta seguro que desea Eliminar este tutor?';
	$permit = true;

	$dataRow = $this->Usuarios->getRow($this->_params['modal']['id']);
	$dataRow['id'] = $this->_params['modal']['id'];
        $dataRow['perfil'] = $this->_params['Filters']['Perfil'];

	$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Tutor', $message);
	$this->SwapBytes_Crud_Form->setWidthLeft('120px');
	$this->SwapBytes_Crud_Form->getDeleteLoad($permit);
  }

  public function deletefinishAction() {
                
      $pk_usuario = $this->_params['modal']['id'];
      $perfil = $this->_params['filters']['Perfil'];
      
      $this->grupo->deleteRow(null, $pk_usuario, $perfil);

	$this->SwapBytes_Crud_Form->setProperties($this->view->form);
	$this->SwapBytes_Crud_Form->getDeleteFinish();
  } 
}