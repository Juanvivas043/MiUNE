<?php

class Transactions_AutoreslibrosController extends Zend_Controller_Action {

    private $Title                = 'Autores libros';
    

    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Autoreslibros');
        Zend_Loader::loadClass('Forms_Autoreslibros');
        $this->autor         = new Models_DbTable_Autoreslibros();
        $this->usuario       = new Models_DbTable_Usuarios();
        $this->grupo         = new Models_DbTable_UsuariosGrupos();
        $this->filtros       = new Une_Filtros();

        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->CmcBytes_profit          = new CmcBytes_Profit();        
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->session = new Zend_Session_Namespace('session');
        $this->id = new Zend_Session_Namespace('id');
         // Mandamos a crear el formulario para ser utilizado mediante el AJAX.
         
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->view->form = new Forms_Autoreslibros();
        $this->SwapBytes_Form->set($this->view->form);
        /*
         * Configuramos los botones.
         */
	$this->SwapBytes_Crud_Action->setDisplay(true, true, true);
	$this->SwapBytes_Crud_Action->setEnable(true, true, true);
        
       
           
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
              $this->autor->setSearch($searchData);
              $paginatorCount = $this->autor->getSQLCount();  
              $rows           = $this->autor->get_data($itemPerPage, $pageNumber);

            // Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                           'width' => '600px');

            $columns = array(array('column'  => 'pk_atributo',
                                   'primary' => true,
                                   'hide'    => true),
                
                             array('name'    => 'Autor',
                                   'width'   => '600px',
                                   'column'  => 'autor',
                                   'rows'    => array('style' => 'text-align:center')),
                            
                       );
            
               // Generamos la lista.
            $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'VUD');
            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
            
   
    }

    
    
    
    
  public function addoreditloadAction() {
     
      
        if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) { // update
            $dataRowx = $this->autor->get_dataRow($this->_params['modal']['id']);
            $dataRow['id'] = $dataRowx[0]['pk_atributo'];
            $dataRow['autor'] = $dataRowx[0]['autor'];
            $mensaje = 'Agregar Autor';
        }else{ // insertar
            $mensaje = 'Editar Autor';
        
        } 
      
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $mensaje);
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();
        
        
    }
    
  public function addoreditconfirmAction() {
     
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

                    $dataRow = $this->autor->get_Autor($this->_params['modal']['autor']); 
                   
                   if(!empty($dataRow)){ // existe 
                         $this->session->id = $this->_params['modal']['id'];
                        $html   = $this->SwapBytes_Html_Message->alert("ERROR El autor ya existe");
                        $html  .= $this->SwapBytes_Ajax->render($this->view->form);
                        $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                        $this->getResponse()->setBody(Zend_Json::encode($json));
                   }else{
                   $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
	      	   $this->SwapBytes_Crud_Form->getAddOrEditConfirm();  
                   }
                  
                   
			
		}
	}

  public function addoreditresponseAction() {
    
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
                        
			// Obtenemos los parametros que se esperan recibir.
			$dataRow                  = $this->_params['modal'];
			$id                       = (int)$dataRow['id'];
                        if(!empty($this->session->id)){
                            $id=(int)$this->session->id;
                            $this->session->id = "";
                            $this->id->unsetAll();
                            $this->session->unsetAll();
                            
                        }
			if(is_numeric($id) && $id > 0) { // update
                                 $this->autor->update($id,$dataRow['autor']);
                            
			} else{ // insert
                            $pk = $this->autor->get_pkAtributo();
                            $this->autor->insert($pk[0]['pk_atributo'],$dataRow['autor']);
			}
                        
      
        $this->getResponse()->setBody(Zend_Json::encode($json));
        $this->SwapBytes_Crud_Form->getAddOrEditEnd($data);
                       
		} 
    }  

  public function deletefinishAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
              $this->autor->deleteRow($this->_params['modal']['id']);

	$this->SwapBytes_Crud_Form->setProperties($this->view->form);
	$this->SwapBytes_Crud_Form->getDeleteFinish();
           
        }
    }

  public function deleteloadAction() {
      
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
	    $libros = $this->autor->get_librosAsociados($this->_params['modal']['id']);
            
	     if($libros[0]['count'] == 0){
            $message = 'Seguro desea elmimnar esto?:';
	    $permit = true;
            }else{
            $message = 'No se puede eliminar :';
	    $permit = false;    
            }
            
            $dataRowx = $this->autor->get_dataRow($this->_params['modal']['id']);
            $dataRow['id'] = $dataRowx[0]['pk_atributo'];
            $dataRow['autor'] = $dataRowx[0]['autor'];

            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Autor', $message);
            $this->SwapBytes_Crud_Form->getDeleteLoad($permit,$msg);
        
           
        }
    }

  public function viewAction() {
        // Obtenemos los parametros que se esperan recibir.

        $dataRowx = $this->autor->get_dataRow($this->_params['modal']['id']);
        $dataRow['id'] = $dataRowx[0]['pk_atributo'];
        $dataRow['autor'] = $dataRowx[0]['autor'];
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Autor');
        $this->SwapBytes_Crud_Form->getView(); 
       
    }
  
  public function helpAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $this->render();
    }
    
}

?>
