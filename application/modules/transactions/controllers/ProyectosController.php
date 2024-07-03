<?php
class Transactions_ProyectosController extends Zend_Controller_Action {   

    private $_Title   = 'Transacciones \ Lista de Proyectos';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Proyectos');
        Zend_Loader::loadClass('Models_DbTable_Instituciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Forms_Proyecto');
        
        $this->Proyectos          = new Models_DbTable_Proyectos();
        $this->Instituciones      = new Models_DbTable_Instituciones();
        $this->grupo = new Models_DbTable_UsuariosGrupos();
        $this->filtros            = new Une_Filtros();

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

//	 * Obtiene los parametros del modal.
		$this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();        
                
//      * Configuramos los botones.
              
                
                $this->SwapBytes_Crud_Action->setDisplay(true, true, true, true, false, false);
		$this->SwapBytes_Crud_Action->setEnable(true, true, true, true, false, false);
                $this->SwapBytes_Crud_Search->setDisplay(true);  
        
//    * Mandamos a crear el formulario para ser utilizado mediante el AJAX.        
    
             $this->view->form = new Forms_Proyecto();

//    * Valores que se definen por defecto al agregar
    $instituciones = $this->Instituciones->getinstitucionesproyectos();             

    $this->SwapBytes_Form->set($this->view->form);
    $this->SwapBytes_Form->fillSelectBox('fk_institucion',  $instituciones , 'pk_institucion', 'nombre');
    $this->view->form = $this->SwapBytes_Form->get();
    
    if ($this->_params['modal']['id'] == 0) {
		$this->_params['default']['institucion'] = $instituciones[0]['pk_institucion'];
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

    
    

   public function listAction() {
        
     if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $searchData  = $this->_getParam('buscar');

            $this->Proyectos->setSearch($searchData);           
         
            $rows = $this->Proyectos->getProyectos();

            if(isset($rows) && count($rows) > 0) {
           

// Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                           'width' => '1000px');

            $columns = array(array('column'  => 'pk_proyecto',
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
                                                      'name'  => 'chkProyecto',
                                                      'value' => '##pk_proyecto##')),
                             array('name'    => 'Nombre del Proyecto',
                                   'width'   => '300px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'nombre'),
                             array('name'    => 'Institucion',
                                   'width'   => '300px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'institucion'),      
                             array('name'    => 'Descripcion',
                                   'width'   => '500px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'descripcion'));


                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'U');       
       
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkProyecto');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen Proyectos cargados.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));
            
        }
    }    

public function addoreditloadAction() {
	if ($this->_request->isXmlHttpRequest()){
	if (is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {

          $title = 'Editar Proyecto';
	  $dataRow = $this->Proyectos->getRow($this->_params['modal']['id']);
          $dataRow['id'] = $this->_params['modal']['id'];
	  $dataRow['pk_proyecto'] = $this->_params['modal']['id'];
//          $this->SwapBytes_Form->enableElement('institucion', false);        
          
          
        } else {          
             $title = 'Agregar Proyecto'; 
	}


        
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
	$this->SwapBytes_Crud_Form->setJson($json);
	$this->SwapBytes_Crud_Form->setWidthLeft('70px');
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
				$this->Proyectos->updateRow($id, $dataRow);
                        }else {
                                $this->Proyectos->addRow($this->_params['modal']);            
			}
			
			$this->SwapBytes_Crud_Form->getAddOrEditEnd();
		}
        }         

// cargamos Formulario ver
    public function viewAction() {
       
        $dataRow = $this->Proyectos->getRow($this->_params['modal']['id']);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Proyecto');
        $this->SwapBytes_Crud_Form->getView();
        
    }
  
    
// Eliminamos datos seleccionados    
  public function deleteloadAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
			
            $Params = $this->_params['modal'];

			if(isset($Params['chkProyecto'])) {
				if(is_array($Params['chkProyecto'])) {
					foreach($Params['chkProyecto'] as $proyecto) {
						$this->Proyectos->deleteRow($proyecto);
					}
				} else {
					$this->Proyectos->deleteRow($Params['chkProyecto']);
				}

				$this->SwapBytes_Crud_Form->getDeleteFinish();
			}
		}
    }
  
    
    
}
