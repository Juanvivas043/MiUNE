<?php
class Transactions_TiposhorariospasantiasController extends Zend_Controller_Action {   

    private $Title   = 'Transacciones \ Lista de Tipos de Horarios para Pasantias';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Instituciones');
        Zend_Loader::loadClass('Models_DbView_Tiposinstituciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Atributostipos');
        Zend_Loader::loadClass('Forms_Tipohorario');
        
        
        
        $this->atributos              = new Models_DbTable_Atributos();
        $this->atributostipos         = new Models_DbTable_Atributostipos();
        $this->grupo                  = new Models_DbTable_UsuariosGrupos();
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
           
     $this->view->form = new Forms_Tipohorario();

     $atributostipos= $this->atributostipos->getatributostipos();
     
    $this->SwapBytes_Form->set($this->view->form); 
    $this->SwapBytes_Form->fillSelectBox('fk_atributotipo', $atributostipos  , 'pk_atributotipo', 'atributotipo');
    
    
    $this->view->form = $this->SwapBytes_Form->get();
    
    if ($this->_params['modal']['id'] == 0) {
		$this->_params['default']['atributotipo'] = $atributostipos[0]['pk_atributotipo'];
	  }
     
     $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
//     $this->_params['modal']['fk_atributotipo']= 37;
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
            $this->atributos->setSearch($searchData);           
            
            $rows = $this->atributos->getListaTipoHorarios();

            if(isset($rows) && count($rows) > 0) {
           

// Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                           'width' => '500px');

            $columns = array(array('column'  => 'pk_atributo',
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
                                                      'name'  => 'chkAtributo',
                                                      'value' => '##pk_atributo##')),
                             array('name'    => 'Horario',
                                   'width'   => '500px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'tipohorario'));


                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VU');       
       
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkAtributo');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen Tipos de Horario de Proyectos cargadas.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }    

public function addoreditloadAction() {
    if ($this->_request->isXmlHttpRequest()){
//        $this->_params['modal']['fk_atributotipo'] = 37;
    if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
         
        $title = 'Editar Horarios de Pasantias';
		$dataRow = $this->atributos->getRow($this->_params['modal']['id']);
               var_dump($this->_params['modal']['id']);
        } else {          
            
            $title = 'Agregar Horarios de Pasantias'; 
            
	}
        
        $dataRow['fk_atributotipo'] = 37;
        
//        var_dump($this->_params['modal']);
        $this->SwapBytes_Form->enableElement('fk_atributotipo', false);
          
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
		var_dump($dataRow);
			$dataRow                = $this->_params['modal'];
			$id                     = $dataRow['id'];
			$dataRow['id']                   = null;
                        

			if(is_numeric($id) && $id > 0) {
				$this->atributos->updateRow($id, $dataRow);
                        }else {
                                $this->atributos->addRow($this->_params['modal']);            
			}
			
			$this->SwapBytes_Crud_Form->getAddOrEditEnd();
		}
        }         

// cargamos Formulario ver
    public function viewAction() {
       
        $dataRow = $this->atributos->getRow($this->_params['modal']['id']);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Horario de Pasantía');
        $this->SwapBytes_Crud_Form->getView();

    }
  
    
// Eliminamos datos seleccionados    
  public function deleteloadAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
			
            $Params = $this->_params['modal'];

			if(isset($Params['chkAtributo'])) {
				if(is_array($Params['chkAtributo'])) {
					foreach($Params['chkAtributo'] as $atributo) {
						$this->atributos->deleteRow($atributo);
					}
				} else {
					$this->atributos->deleteRow($Params['chkAtributo']);
				}

				$this->SwapBytes_Crud_Form->getDeleteFinish();
			}
		}
    } 
    
}