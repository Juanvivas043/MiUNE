<?php

class Transactions_AgregarautoresController extends Zend_Controller_Action {


    private $Title = 'Agregar Autor';

    
   
    public function init() {
         
        /* Initialize action controller here */
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        //Zend_Loader::loadClass('Models_DbTable_Agregarlibros');
        Zend_Loader::loadClass('Models_DbTable_BibliotecaAgregar');
        Zend_Loader::loadClass('Forms_Agregarautores');
        Zend_Loader::loadClass('Models_DbTable_Agregarautores');
        Zend_Loader::loadClass('Une_Filtros');
        
        $this->agregar       = new Models_DbTable_BibliotecaAgregar();
        $this->autores       = new Models_DbTable_Agregarautores();
        $this->usuario       = new Models_DbTable_Usuarios();
        $this->grupo         = new Models_DbTable_UsuariosGrupos();
        $this->filtros       = new Une_Filtros(); 
        
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
        $this->SwapBytes_Jquery_Ui = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->session = new Zend_Session_Namespace('session');
        $this->redirect_session = new Zend_Session_Namespace('redirect_session');
        
      

        $this->SwapBytes_Crud_Action->setDisplay(true, true, true, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, true, true, false, false, false);
        
        $Regreso = "<button id='btnReturn' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnReturn' role='button' aria-disabled='false'>Regreso";
        $this->SwapBytes_Crud_Action->addCustum($Regreso);
        
        //Formulario//

        $this->view->form = new Forms_Agregarautores();

        $this->SwapBytes_Form->set($this->view->form);
        

        $this->view->form = $this->SwapBytes_Form->get();

    
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        //$this->_params['filters'] = $this->filtros->getParams();
        $this->CmcBytes_Redirector2          = new CmcBytes_Redirect();
        $this->_params['redirect'] = $this->redirect_session->params;
        $this->redirect_session->unsetAll(); 
        
        $this->view->form->fk_principal->addMultiOption(1,Principal);
        $this->view->form->fk_principal->addMultiOption(2,Otro); 
        $this->SwapBytes_Form->fillSelectBox('fk_autor',  $this->agregar->getAutorlibro()  , 'pk_atributo', 'autor'); // llenado de los autores
        }

    function preDispatch() {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
                $this->_helper->redirector('index', 'login', 'default');
            }

            if (!$this->grupo->haveAccessToModule()) {
                $this->_helper->redirector('accesserror', 'profile', 'default');
            } 
        }

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
        
   
        if($this->_params['redirect']['set'] == true){
            $this->session->id = $this->_params['redirect']['id'];
            $this->listAction();
            
        } 
        
       
    }
        
    public function nuevoAction(){
          if ($this->_request->isXmlHttpRequest()) {
              $this->SwapBytes_Ajax->setHeader();
              $fecha = date('m/d/Y');
              $fechas = $this->prestamo->getUsuarioinfo($this->session->id);

              $id = $this->_params['modal']['id'];
              
              if($id == "1"){//externo
              $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('fk_tipo_interno-label',
                                                                    'fk_tipo_interno-element'));
              
              if( $fechas[0]['grupo'] == "Docente" || $fechas[0]['grupo'] == "Administrativo"  )
                         {
                                $fecha_estimada = $this->suma_fechas($this->SwapBytes_Date->convertToForm($fechas[0]['fecha_solicitud']), 3);
                                $json[] = $this->SwapBytes_Jquery->setVal('fecha_estimada', $fecha_estimada) ;
                         }else{
                     
                                $fecha_estimada = $this->suma_fechas( $this->SwapBytes_Date->convertToForm($fechas[0]['fecha_solicitud']), 2);
                                $json[] = $this->SwapBytes_Jquery->setVal('fecha_estimada',$fecha_estimada ) ;
                         }
              
              }else{//interno
                 
              $json[] = $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('fk_tipo_interno-label',
                                                                    'fk_tipo_interno-element')); 
              
              $json[] = $this->SwapBytes_Jquery->setVal('fecha_estimada', $this->SwapBytes_Date->convertToForm($fechas[0]['fecha_solicitud'])) ;  
                
              }
            $this->getResponse()->setBody(Zend_Json::encode($json));
              
          }  
          //return $id;
    }		
        
    public function listarAction(){
      $this->listAction();
    }  
    
    public function listAction() {
    // Verificamos si es una llamada de tipo AJAX.
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            // Obtenemos los parametros necesarios que se esta pasando por POST, y
            // los valores necesarios de las variables que se utilizaran mas adelante.

            $pageNumber  = $this->_getParam('page', 1);
            $searchData  = $this->_getParam('buscar');
            $itemPerPage = 3;
            $pageRange   = 3;
           
            $rows           = $this->autores->getAutor($this->session->id);
          
            // Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                           'width' => '600px');

            $columns = array(array('name'    => 'Autor',
                                   'width'   => '250px',
                                   'column'  => 'autor',
                                   'rows'    => array('style' => 'text-align:center')
                                    ),

                            array('name'  => 'Tipo de Autor',
                                   'column'  => 'tipo',
                                   'width'   => '150px',
                                    'rows'    => array('style' => 'text-align:center')),
                                   
                             array('name'    => 'fk_autor',
                                   'width'   => '100px',
                                   'column'  => 'fk_autor',
                                   //'primary' => true,
                                   'hide'   => true),
                
                           array('name'    => 'pk_autorlibro',
                                   'width'   => '100px',
                                   'column'  => 'pk_autorlibro',
                                   'hide'   => true,
                                   'primary' => true),
        
                );

            $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'VUD');
            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function autoraddAction(){
     
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();
         
          $queryString = $this->_getParam('data');
          $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);
          $cantidad_autores = $this->cont_element($queryArray, 'fk_principal');
          $pos_autor = 4;
          
          $this->crearCampo($cantidad_autores, $pos_autor); // creamos el autor

           $this->addoreditloadAction($queryArray);
     }

  }
    
    public function deleteautorAction(){
         if ($this->_request->isXmlHttpRequest()) {
             $this->SwapBytes_Ajax->setHeader();
             $id = $this->_getParam('id');
           
             $autor = 'fk_autor'.$id;
             $principal = 'fk_principal'.$id;
             $butom = 'eliminar_autor'.$id;
           
             $json[] = "$('#$autor-element').remove()";
             $json[] = "$('#$autor-label').remove()";
             
             $json[] = "$('#$principal-element').remove()";
             $json[] ="$('#$principal-label').remove()";
             
             
             $json[] = "$('#$butom').remove()";
             
             $this->getResponse()->setBody(Zend_Json::encode($json));
            
         }
  }
    
    public function crearCampo($cantidad ,$pos){
        
      for($i = 0; $i < $cantidad ;$i++){
          $principal = 'fk_principal'.$i;   
          $this->view->form->addAutor($i,$pos);
          $this->SwapBytes_Form->fillSelectBox('fk_autor'.$i,  $this->agregar->getAutorlibro()  , 'pk_atributo', 'autor'); // llenado de los autoresprincipal 
          $this->view->form->{$principal}->addMultiOption('1', 'Principal');
          $this->view->form->{$principal}->addMultiOption('2', 'Otro');
          $pos = $pos + 3;
        }
      
  }
  
    public function addoreditloadAction($queryArray=null) {
	              
         if($queryArray!=NULL){
             
          $json = $this->llenar_form($queryArray);
          $this->SwapBytes_Crud_Form->setJson($json);
      }
      
        if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {//edit
          
          $json[] = "$('#agregar_autor').hide()";  // ocultar el boton
          $dataRowx       = $this->autores->getRow($this->_params['modal']['id']); 
          $dataRow['id'] = (int)$this->_params['modal']['id'];
          $dataRow['fk_autor'] = $dataRowx[0]['fk_autor'];
          $dataRow['autor']    = $dataRowx[0]['autor'];
          $dataRow['pk_autorlibro']    = $dataRowx[0]['pk_autorlibro'];
          if($dataRowx[0]['principal']==true){
              $dataRow['fk_principal'] = "1";
          }else{
              $dataRow['fk_principal'] = "2";
          }
            
	}else{//agregar
                
          
        }   
        
        
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Agregar Autor');
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();
       
    }

    public function addoreditconfirmAction() {
       
         if ($this->_request->isXmlHttpRequest()) {
             $this->SwapBytes_Ajax->setHeader();
             $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
	     $this->SwapBytes_Crud_Form->getAddOrEditConfirm();  
             }
		
	
   }

    public function addoreditresponseAction() {
        if ($this->_request->isXmlHttpRequest()){
            $this->SwapBytes_Ajax->setHeader();
          
            $dataRow                  = $this->_params['modal'];
            $id                       = (int)$this->_params['modal']['id'];
	    $pk_libro                 = (int)$this->session->id;
            $cont = 0;
           
            $autor     = $this->crear_array($dataRow,'fk_autor');
            $principal = $this->crear_array($dataRow,'fk_principal');
          
  
            if(is_numeric($id) && $id > 0) { // update
                       
                        $this->autores->update($dataRow['pk_autorlibro'],$dataRow['fk_autor'],$dataRow['fk_principal']);
                        
                        
			} else{ // insert
                        
                          // agregar autores
                         foreach($autor as $valor){   
                            $this->autores->InsertAutores($pk_libro ,$valor,$principal[$cont]);
                            $cont ++;    
                         }
                       
                          
			}
                        
      
            $this->getResponse()->setBody(Zend_Json::encode($json));
            $this->SwapBytes_Crud_Form->getAddOrEditEnd($data);
            
            }
            
           
         
		
                
    }

    public function viewAction() {
        if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();
        
          $dataRowx       = $this->autores->getRow($this->_params['modal']['id']); 
           
          $dataRow['id'] = (int)$this->_params['modal']['id'];
          $dataRow['fk_autor'] = $dataRowx[0]['fk_autor'];
          $dataRow['autor']    = $dataRowx[0]['autor'];
          if($dataRowx[0]['principal']==true){
              $dataRow['fk_principal'] = "1";
          }else{
              $dataRow['fk_principal'] = "2";
          }
        
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver autor');
        $this->SwapBytes_Crud_Form->getView();
       
    }
    }
    
    public function deleteloadAction() {
        $this->SwapBytes_Ajax->setHeader();
        
	$message = 'Seguro desea elmimnar esto?:';
	$permit = true;
        $dataRowx       = $this->autores->getRow($this->_params['modal']['id']);  
       
          $dataRow['fk_autor'] = $dataRowx[0]['fk_autor'];
          $dataRow['autor']    = $dataRowx[0]['autor'];
          $dataRow['pk_autorlibro'] = $dataRowx[0]['pk_autorlibro'];
          
          if($dataRowx[0]['principal']==true){
              $dataRow['fk_principal'] = "1";
          }else{
              $dataRow['fk_principal'] = "2";
          }
        
	$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Autor', $message);
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->getDeleteLoad($permit,$msg);
        
  }

    public function deletefinishAction() {
        
        $this->autores->deleteRow($this->_params['modal']['pk_autorlibro']);
	$this->SwapBytes_Crud_Form->setProperties($this->view->form);
	$this->SwapBytes_Crud_Form->getDeleteFinish();


  }
 
    public function regresoAction(){
        
         if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
       
            $data = array( 'module'=>'transactions',
                     'controller'=>'bibliotecalibros',
                     'params'=>array('id' => 1,
                                      'set' => 'true'));
        $this->session->unsetAll();
        
        $json[] = $this->CmcBytes_Redirector2->getRedirect($data);
        $this->getResponse()->setBody(Zend_Json::encode($json));
        }
       
    }
 
    public function llenar_form($arreglo){
     $datakey = array_keys($arreglo);
     $pos = 0;
    
     foreach ($arreglo as $valor){
        $json[] = "$('#$datakey[$pos]').val('$valor')";
        $pos++;
     }
     return $json;
    
  }
   
    public function cont_element($arreglo , $campo){
   
      $datakey = array_keys($arreglo);
      $cant = 0;
      foreach ($datakey as $key){
                 
              $cadena = preg_replace('/[0-9]/', '', $key);
              if($cadena == $campo){
                  $cant++;
              }
             
      }
      return $cant;
             
  }

    public function crear_array($data,$campo){
      $arreglo = array();
      $pos     = array();
      $datakey = array_keys($data);
      $i = 0; $j = 0;
      
      foreach ($datakey as $key){    
          $cadena = preg_replace('/[0-9]/', '', $key);  
          if($cadena == $campo){
              array_push($pos, $i);
            }
          $i = $i + 1;
      }
      
     foreach ($pos as $lugar){  
      $j = 0;   
            
            foreach ($data as $valor){ 
                        if($j === $lugar){         
                            array_push($arreglo, $valor);
                        }
                $j++;     
            }
         
          
      }
  
      
    return $arreglo;
  }
    
    public function informacionAction(){
         $this->SwapBytes_Ajax->setHeader();
        
         $libro = $this->autores->get_Info($this->session->id);
         $json[] = '$("#cotatxt").html("'.$libro[0]['cota'].'")';
         $json[] = '$("#titulotxt").html("'.$libro[0]['titulo'].'")';
         $json[] = '$("#editorialtxt").html("'.$libro[0]['editorial'].'")';
         $this->getResponse()->setBody(Zend_Json::encode($json));
        
    }
}



