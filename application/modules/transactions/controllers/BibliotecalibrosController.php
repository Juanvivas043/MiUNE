<?php
/**
 * @todo Ocultar el boton "Ocultar" del formulario Ver.
 * @todo Ocultar el boton "Eliminar" del formulario Eliminar, solo cuando no se
 *       permita.
 */
class Transactions_BibliotecalibrosController extends Zend_Controller_Action {

    private $Title                = 'Agregar libros';
   

    public function init() {
            Zend_Loader::loadClass('Une_Filtros');
            Zend_Loader::loadClass('Models_DbTable_Usuarios');
            Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
            Zend_Loader::loadClass('Models_DbTable_BibliotecaAgregar');
            Zend_Loader::loadClass('Forms_Bibliotecalibros');

            $this->agregar       = new Models_DbTable_BibliotecaAgregar();
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
            
            $this->CmcBytes_Redirector          = new CmcBytes_Redirect();
            
            $this->redirect_session = new Zend_Session_Namespace('redirect_session');
            $this->_params['redirect'] = $this->redirect_session->params;
            $this->redirect_session->unsetAll(); 
            
            
            $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
            $this->view->form = new Forms_Bibliotecalibros();
            $this->SwapBytes_Form->set($this->view->form);
            
            $this->SwapBytes_Crud_Action->setDisplay(true, true, true);
	    $this->SwapBytes_Crud_Action->setEnable(true, true, true);
            
            $this->SwapBytes_Form->fillSelectBox('fk_editorial',  $this->agregar->getEditorial()  , 'pk_atributo', 'editorial'); // llenado de las editoriales
            $this->SwapBytes_Form->fillSelectBox('fk_pais',  $this->agregar->getpais()  , 'pk_atributo', 'pais'); // llenado del pais
        
      
            $rango  = 600;
            $actual = date ('Y');
            $inicio = $actual - $rango;
            $this->view->form->fk_ano->addMultiOption(0, 'N/A');
            for($x = $actual ; $x>$inicio; $x--){
                $this->view->form->fk_ano->addMultiOption($x, $x);

            }

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
              $paginatorCount     = $this->agregar->getSQLCount();  
              $libros             = $this->agregar->getlibro($itemPerPage, $pageNumber);
              $pk_libro           = $this->pk_libros($libros);
              if($libros!=NULL){
              $autor_principal  = $this->agregar->get_autor($pk_libro,'t');         
              $autor_otro  = $this->agregar->get_autor($pk_libro,'f'); 
              $rows = $this->transformar($libros,$autor_principal,$autor_otro);
              $rows = $this->transformarNull($rows);
              $rows = $this->buscar($rows, $searchData);
              
              
               if($searchData != ''){
                   $paginatorCount     = Count($rows);
               }
              
              }
             
            // Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                           'width' => '800px');

            $columns = array(array('column'  => 'pk_libro',
                                   'primary' => true,
                                   'hide'    => true),
                
                             array('name'    => 'COTA',
                                   'width'   => '70px',
                                   'column'  => 'cota',
                                   'rows'    => array('style' => 'text-align:right')),
                
                             array('name'    => 'Titulo',
                                   'width'   => '200px',
                                   'column'  => 'titulo',
                                   'rows'    => array('style' => 'text-align:center')),
                
                             array('name'    => 'Autor Principal',
                                   'width'   => '200px',
                                   'column'  => 'autor_principal',
                                   'rows'    => array('style' => 'text-align:center')),
                                   
                              array('name'    => 'Autor Otro',
                                   'width'   => '200px',
                                   'column'  => 'autor_otro',
                                   'rows'    => array('style' => 'text-align:center')),
                             
                    
                             array('name'    => 'Editorial',
                                   'width'   => '200px',
                                   'column'  => 'editorial',
                                   'rows'    => array('style' => 'text-align:center')),
                
                             array('name'    => 'Ciudad',
                                   'width'   => '200px',
                                   'column'  => 'ciudad',
                                   'rows'    => array('style' => 'text-align:center')),
                       );
                         
                      $other = array(
                      array('actionName' => '',
                            'action'     => 'autor(##pk##)'  ,
                            'label'      => 'Autor'),      
                             
                       array('actionName' => '',
                            'action'     => 'materia(##pk##)'  ,
                            'label'      => 'Materia'));

                
                  
               // Generamos la lista.
             //$HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VUDO', $other);             
            $HTML = $this->Paginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'VUDO',$other);
            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

  public function existsAction() {
        
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
            $json        = array();
            $html        = '';
            $queryString = $this->_getParam('data');
            $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);
            $dataRow = array();
            $libro = $this->agregar->getDataLibro($queryArray['cota']);
            $libro = $this->transformarNull($libro) ;    
            $cota = $libro[0]['cota'];
           
           
           
            
            if(!empty($cota)) {// validacion para saber si hay algun libro
               $dataRow['cota']          = $cota;
               $dataRow['titulo']        = $libro[0]['titulo'];
               $dataRow['fk_editorial']  = $libro[0]['fk_editorial'];
               $dataRow['fk_pais']       = $libro[0]['fk_pais'];
               $this->SwapBytes_Form->fillSelectBox('fk_ciudad',  $this->agregar->getciudad($dataRow['fk_pais'])  , 'pk_atributo', 'ciudad');
               $dataRow['fk_ciudad']        = $libro[0]['fk_ciudad'];
               $dataRow['fk_ano']        = $libro[0]['ano'];
               $dataRow['pagina']        = $libro[0]['pagina'];
               $dataRow['nota']          = $libro[0]['nota'];
               $dataRow['ejemplar']      = $libro[0]['ejemplar'];
               $dataRow['volumen']       = $libro[0]['volumen'];
               $dataRow['coleccion']     = $libro[0]['coleccion'];
               $dataRow['numero']        = $libro[0]['numero'];
                $this->SwapBytes_Form->set($this->view->form); //setamos el formulario

               if(isset($dataRow)) {
                  	
                    $this->view->form->populate($dataRow);
                } 
                   
                // Definimos el acceso a los controles del frmModal.
               
               
                $this->view->form = $this->SwapBytes_Form->get();

                // Preparamos el frmModal para ser enviado por AJAX.
                $html  .= $this->SwapBytes_Ajax->render($this->view->form);

                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
              

                  $this->SwapBytes_Crud_Form->setJson($json);//new
                
                $this->getResponse()->setBody(Zend_Json::encode($json));
           }
			
        }
    }
  
  private function getData($id) {
        $dataRow = $this->agregar->getRowLibro($id);
        return $dataRow; 
    }
  
  public function addoreditloadAction() {
       
        if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
                $libro = $this->getData($this->_params['modal']['id']);
                $libro = $this->transformarNull($libro) ; 
                
                $dataRow['id'] = $this->_params['modal']['id']; 
                $dataRow['cota'] = $libro[0]['cota'];
                $dataRow['titulo'] = $libro[0]['titulo'];
                $dataRow['fk_editorial']  = $libro[0]['fk_editorial'];
                $dataRow['fk_pais'] = $libro[0]['fk_pais']; 
                $this->SwapBytes_Form->fillSelectBox('fk_ciudad',  $this->agregar->getciudad($dataRow['fk_pais'])  , 'pk_atributo', 'ciudad');
                $dataRow['ciudad'] = $libro[0]['ciudad'];
                $dataRow['fk_ciudad'] = $libro[0]['fk_ciudad'];
                $dataRow['fk_ano'] = $libro[0]['ano']; 
                $dataRow['edicion']  = $libro[0]['edicion'];
                $dataRow['pagina'] = $libro[0]['pagina']; 
                $dataRow['nota'] = $libro[0]['nota'];
                $dataRow['ejemplar'] = $libro[0]['ejemplar'];
                $dataRow['volumen'] = $libro[0]['volumen'];
                $dataRow['coleccion'] = $libro[0]['coleccion'];
                $dataRow['numero'] = $libro[0]['numero'];
                

        }else{
            
            $this->SwapBytes_Form->fillSelectBox('fk_ciudad',  $this->agregar->getciudad(2114)  , 'pk_atributo', 'ciudad');
        } 
        
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Agregar Libros');
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();
        
        
    }
    
  public function addoreditconfirmAction() {
     
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
                      
            $this->_fillSelectsRecursive($this->_params['modal']); // permite la recursividad del pais/ciudad  
            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
            $this->SwapBytes_Crud_Form->getAddOrEditConfirm(); 
                        
			
		}
	}

  public function addoreditresponseAction() {
    
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
           
			// Obtenemos los parametros que se esperan recibir.
			$dataRow                  = $this->_params['modal'];
                        $id                       = $this->_params['modal']['id'];
			
			if(is_numeric($id) && $id > 0) {
                            
		        $this->agregar->updatelibro($id,$dataRow['cota'],$dataRow['titulo'],$dataRow['edicion'],$dataRow['fk_pais'],
                                                            $dataRow['fk_ciudad'],$dataRow['fk_editorial'],$dataRow['fk_ano'],$dataRow['pagina'],
                                                            $dataRow['volumen'],$dataRow['ejemplar'],$dataRow['nota'],$dataRow['coleccion'],
                                                            $dataRow['numero']);
			} else{
                             
			$this->agregar->InsertLibro($dataRow['cota'],$dataRow['titulo'],$dataRow['edicion'],$dataRow['fk_pais'],
                                                            $dataRow['fk_ciudad'],$dataRow['fk_editorial'],$dataRow['fk_ano'],$dataRow['pagina'],
                                                            $dataRow['volumen'],$dataRow['ejemplar'],$dataRow['nota'],$dataRow['coleccion'],
                                                            $dataRow['numero']);  
			}
                        
                        

                        //$idn = $this->prestamo->getUltimopk();
                        
                        
                       /* $data = array( 'module'=>'transactions',
                                       'controller'=>'prestamoart',
                                       'params'=>array('id' => $idn[0]['pk_prestamo'],
                                       'set' => 'false'));*/
 
        
        
        $this->getResponse()->setBody(Zend_Json::encode($json));
                        
                        
                        
			$this->SwapBytes_Crud_Form->getAddOrEditEnd($data);
                        
		}
    }  

  public function deletefinishAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
           
          
            $this->agregar->deleteRow($this->_params['modal']['id']);
        


	$this->SwapBytes_Crud_Form->setProperties($this->view->form);
	$this->SwapBytes_Crud_Form->getDeleteFinish();
           
        }
    }

  public function deleteloadAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $this->SwapBytes_Ajax->setHeader();
	   
            //$cantidad = $this->prestamo->contArticulo(($this->_params['modal']['id']));

                $message = 'Seguro desea elmimnar esto?:';
                $permit = true;

                $libro = $this->getData($this->_params['modal']['id']);
                $libro = $this->transformarNull($libro) ;      
                
                $dataRow['id'] = $this->_params['modal']['id']; 
                $dataRow['cota'] = $libro[0]['cota'];
                $dataRow['titulo'] = $libro[0]['titulo'];
                $dataRow['fk_editorial']  = $libro[0]['fk_editorial'];
                $dataRow['fk_pais'] = $libro[0]['fk_pais']; 
                $this->SwapBytes_Form->fillSelectBox('fk_ciudad',  $this->agregar->getciudad($dataRow['fk_pais'])  , 'pk_atributo', 'ciudad');
                $dataRow['ciudad'] = $libro[0]['ciudad'];
                $dataRow['fk_ciudad'] = $libro[0]['fk_ciudad'];
                $dataRow['fk_ano'] = $libro[0]['ano']; 
                $dataRow['edicion']  = $libro[0]['edicion'];
                $dataRow['pagina'] = $libro[0]['pagina']; 
                $dataRow['nota'] = $libro[0]['nota'];
                $dataRow['ejemplar'] = $libro[0]['ejemplar'];
                $dataRow['volumen'] = $libro[0]['volumen'];
                $dataRow['coleccion'] = $libro[0]['coleccion'];
                $dataRow['numero'] = $libro[0]['numero'];
                
	$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Acividad', $message);
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->getDeleteLoad($permit,$msg);
        
           
        }
    }

  public function viewAction() {
     // $this->detalleAction();
        // Obtenemos los parametros que se esperan recibir.
       
        $libro = $this->getData($this->_params['modal']['id']);
        $libro = $this->transformarNull($libro) ;      
                
                $dataRow['id'] = $this->_params['modal']['id']; 
                $dataRow['cota'] = $libro[0]['cota'];
                $dataRow['titulo'] = $libro[0]['titulo'];
                $dataRow['fk_editorial']  = $libro[0]['fk_editorial'];
                $dataRow['fk_pais'] = $libro[0]['fk_pais']; 
                $this->SwapBytes_Form->fillSelectBox('fk_ciudad',  $this->agregar->getciudad($dataRow['fk_pais'])  , 'pk_atributo', 'ciudad');
                $dataRow['ciudad'] = $libro[0]['ciudad'];
                $dataRow['fk_ciudad'] = $libro[0]['fk_ciudad'];
                $dataRow['fk_ano'] = $libro[0]['ano']; 
                $dataRow['edicion']  = $libro[0]['edicion'];
                $dataRow['pagina'] = $libro[0]['pagina']; 
                $dataRow['nota'] = $libro[0]['nota'];
                $dataRow['ejemplar'] = $libro[0]['ejemplar'];
                $dataRow['volumen'] = $libro[0]['volumen'];
                $dataRow['coleccion'] = $libro[0]['coleccion'];
                $dataRow['numero'] = $libro[0]['numero'];
        
       
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver libro');
        $this->SwapBytes_Crud_Form->getView();
       
    }

 public function autorAction(){
        
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();
         
      $id = $this->_getParam('pk');
      $data = array( 'module'=>'transactions',
                     'controller'=>'Agregarautores',
                      'params'=>array('id' => $id,
                                      'set' => 'true'));
 
        
        $json[] = $this->CmcBytes_Redirector->getRedirect($data);
        $this->getResponse()->setBody(Zend_Json::encode($json));
    }
    }
 
 public function materiaAction() {
     
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();
          
      $id = $this->_getParam('pk');
      $data = array( 'module'=>'transactions',
                     'controller'=>'agregarmaterias',
                      'params'=>array('id' => $id,
                                      'set' => 'true'));
 
        
        $json[] = $this->CmcBytes_Redirector->getRedirect($data);
        $this->getResponse()->setBody(Zend_Json::encode($json));
    }
  }
  
  private function transformar($libro,$principal,$otro){
         
      $cant_libros    = count($libro);
      $cant_principal = count($principal);
      $cant_otro     = count($otro);
      //var_dump($libro);
      for ($i = 0 ; $i < $cant_libros; $i++ ){
          for($j = 0; $j < $cant_principal; $j++ ){
              if($libro[$i]['pk_libro']==$principal[$j]['pk_libro']){
                  $libro[$i]['autor_principal'] = $principal[$j]['autor'];
              }
          }
          for($j = 0; $j < $cant_otro; $j++ ){
              if($libro[$i]['pk_libro']==$otro[$j]['pk_libro']){
                  $libro[$i]['autor_otro'] = $otro[$j]['autor'];
              }
          }
          
      }
        return  $libro;
          
         
  }

  private function transformarNull($RowData){
          if ($RowData[0]['ejemplar']=== 'NULL'){
               $RowData[0]['ejemplar'] = '';
          }
         
          if ($RowData[0]['volumen']=== 'NULL'){
               $RowData[0]['volumen'] = '';
          }
          
          if ($RowData[0]['coleccion']=== 'NULL'){
               $RowData[0]['coleccion'] = '';
          }
          
          if ($RowData[0]['edicion']=== 'NULL'){
               $RowData[0]['edicion'] = '';
          }
          
           if ($RowData[0]['nota']=== 'NULL'){
               $RowData[0]['nota'] = '';
          }
          if ($RowData[0]['pagina']=== 'NULL'){
               $RowData[0]['pagina'] = '';
          }
         
         return $RowData;
  }
  
  public function mejorar_data($rows){
      
      $cantidad = count($rows);
     
      for ($i = 0 ; $i < $cantidad ; $i++){
          
      $rows[$i]['autor_principal'] = str_replace("}"," ", $rows[$i]['autor_principal']);
      $rows[$i]['autor_principal'] = str_replace("{"," ", $rows[$i]['autor_principal']);
      $rows[$i]['autor_otro']      = str_replace("}"," ", $rows[$i]['autor_otro']);
      $rows[$i]['autor_otro']      = str_replace("{"," ", $rows[$i]['autor_otro']);
      
      
      }
            
      return $rows;      
  }

  public function buscar($rows,$search){
     
      if($search !== ''){
          $i = 0;
          foreach ($rows as $row){
              $v1 = strpos(strtolower($row['cota'])     , strtolower($search));
              $v2 = strpos(strtolower($row['titulo'])   , strtolower($search));
              $v3 = strpos(strtolower($row['editorial']), strtolower($search));
              $v4 = strpos(strtolower($row['autor_principal']), strtolower($search));
              $v5 = strpos(strtolower($row['autor_otro']), strtolower($search));
              $v6 = strpos(strtolower($row['ciudad']), strtolower($search));
              if(is_numeric($v1) || is_numeric($v2) || is_numeric($v3) || is_numeric($v4) || is_numeric($v5)|| is_numeric($v6)){
                    $rows[$i]['find']= true; 
                    
              }else{
                  unset($rows[$i]);
              }
                
               $i = $i + 1;
          }
          
 
    }
    return $rows;
  }
  
  public function pk_libros($RowData){
     $pk_libro = '';
      foreach ($RowData as $data){
          $pk_libro .= $data['pk_libro'].',';
      }
      $pk_libro = trim($pk_libro, ',');
      return $pk_libro;
  }

  public function cpaisAction(){
       if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
              
           $dataRow = $this->agregar->getciudad($this->_getParam('pais')); 
           $this->SwapBytes_Ajax_Action->fillSelect($dataRow);
           
              }
      
  }
  
  private function _fillSelectsRecursive($RowData) {

       if ($this->_params['modal']['id'] <> 0 && is_array($RowData) && count($RowData) > 0) {
	  // Modificar
           $pais = $this->agregar->getciudad($this->_params['modal']['fk_pais']);    
	} else if ($this->_params['modal']['id'] == 0) {
	
          $pais = $this->agregar->getciudad($this->_params['modal']['fk_pais']);   
	}

	$this->SwapBytes_Form->fillSelectBox('fk_ciudad', $pais  , 'pk_atributo', 'ciudad');

  }  
  
  public function Paginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $count, $actions = null, $other = null) {
        $HTML = '';
        
        if(is_array($rows) && count($rows) > 0) {
            $HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, $actions,$other);

            // Asigna la lista y el paginador a la vista.
            if(isset($itemPerPage) && isset($pageNumber) && isset($pageRange) && isset($count)) {
                $paginator  = new Zend_Paginator(new Zend_Paginator_Adapter_Null($count));
                $paginator->setItemCountPerPage($itemPerPage)
                          ->setCurrentPageNumber($pageNumber)
                          ->setPageRange($pageRange);

                $HTML .= $paginator;
            }

            $HTML  = str_replace("\n", "", $HTML);
        } else {
            // Envia un mensaje por que no consigue registros.
            $width = (isset($table['width']))? $table['width'] : '300px';
            $HTML  = '<div class="alert" style="text-align:center;width:' . $width . '">No existen registros.</div>';
        }

        return $HTML;
    }

    
}
