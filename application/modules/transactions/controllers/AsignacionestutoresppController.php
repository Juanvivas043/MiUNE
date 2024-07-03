<?php
class Transactions_AsignacionestutoresppController extends Zend_Controller_Action {   

    private $Title   = 'Transacciones \ Asignar Tutores Empresariales';

    private $fk_grupo     = 8237;
    
    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Contactos');
        Zend_Loader::loadClass('Models_DbTable_Instituciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Forms_Asignaciontutorpp');
        
        $this->Contactos              = new Models_DbTable_Contactos();
        $this->Instituciones          = new Models_DbTable_Instituciones();
        $this->Usuariosgrupos         = new Models_DbTable_UsuariosGrupos();
        $this->filtros                = new Une_Filtros();

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
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
  
//      BOTONES DE ACCIONES
                
        $this->SwapBytes_Crud_Action->setDisplay(true, true, true, true, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, true, true, true, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(true);  
        
//    FORM 
           
    $this->view->form = new Forms_Asignaciontutorpp();
    
    $this->SwapBytes_Form->set($this->view->form);
    $this->SwapBytes_Form->fillSelectBox('fk_institucion',  $this->Instituciones->getempresas()  , 'pk_institucion', 'nombre');
    $this->SwapBytes_Form->fillSelectBox('fk_usuariogrupo',  $this->Usuariosgrupos->getusuariostutorespp($this->fk_grupo)  , 'pk_usuariogrupo', 'nombre');
    
    $this->view->form = $this->SwapBytes_Form->get();
  
   
     //prueba
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->filtros->getParams();

}

  function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
        if (!$this->Usuariosgrupos->haveAccessToModule()) {
             $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }
    
//     * Crea la estructura base de la pagina principal.
  
    public function indexAction() {
            
            $this->view->title   = $this->Title;
            $this->view->filters = $this->filtros;
            $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
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

            $searchData  = $this->_getParam('buscar');
            $json   = array();
     
            $this->Contactos->setSearch($searchData);           
            $rows = $this->Contactos->getTutoresEmpresariales();
            
            if(isset($rows) && count($rows) > 0) {
           

// Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                           'width' => '800px');

            $columns = array(array('column'  => 'pk_contacto',
                                   'primary' => true,
                                   'hide'    => true),
                             array('name'    => array('control' => array('tag'        => 'input',
                                                                         'type'       => 'checkbox',
                                                                         'name'       => 'chkSelectDeselect')),
                                   'column'  => 'nc',
                                   'width'   => '20px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'control' => array('tag'   => 'input',
                                                      'type'  => 'checkbox',
                                                      'name'  => 'chkTutorpp',
                                                      'value' => '##pk_contacto##')),
                             array('name'    => 'Cédula Tutor',
                                   'width'   => '100',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'cedula'),
                             array('name'    => 'Nombre',
                                   'width'   => '300px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'nombre_tutor'),
                             array('name'    => 'Empresa',
                                   'width'   => '300px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'institucion'));


                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VU');       
       
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkTutorpp');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen Tutores cargados.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }    

public function addoreditloadAction() {
//    $dataRow = array();
    if ($this->_request->isXmlHttpRequest()){

        if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {

        
        $title = 'Editar Tutor Empresarial Asignado';
			$dataRow                    = $this->Contactos->getRow($this->_params['modal']['id']);
			$dataRow['pk_contacto']     = $this->_params['modal']['id'];
                        $dataRow['id'] = $this->_params['modal']['id'];
 
 
	
        } else {          
        
            $title = 'Asignar Tutor Empresarial';

	}
        
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();
  }    
}
   
    

// hacemos las validaciones a editar o agregar       
       public function addoreditconfirmAction() {
        
           if ($this->_request->isXmlHttpRequest()) {
            
               $this->SwapBytes_Ajax->setHeader();

               
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
			$dataRow['id']      = null;

			if(is_numeric($id) && $id > 0) {
				$this->Contactos->updateRow($id, $dataRow);
                        }else {
                                $this->Contactos->addRow($this->_params['modal']);            
			}
			
			$this->SwapBytes_Crud_Form->getAddOrEditEnd();
		}
        }         

// cargamos Formulario ver
    public function viewAction() {
       
        $dataRow = $this->Contactos->getRow($this->_params['modal']['id']);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Tutor Empresarial Asignado');
        $this->SwapBytes_Crud_Form->getView();

    }
  
    
// Eliminamos datos seleccionados    
  public function deleteloadAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
			
            $Params = $this->_params['modal'];

			if(isset($Params['chkTutorpp'])) {
				if(is_array($Params['chkTutorpp'])) {
					foreach($Params['chkTutorpp'] as $tutorpp) {
						$this->Contactos->deleteRow($tutorpp);
					}
				} else {
					$this->Contactos->deleteRow($Params['chkTutorpp']);
				}

				$this->SwapBytes_Crud_Form->getDeleteFinish();
			}
		}
    }
}
   