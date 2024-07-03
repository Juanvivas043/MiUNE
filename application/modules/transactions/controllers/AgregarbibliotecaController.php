<?php
/**
 * @todo Ocultar el boton "Ocultar" del formulario Ver.
 * @todo Ocultar el boton "Eliminar" del formulario Eliminar, solo cuando no se
 *       permita.
 */
class Transactions_AgregarbibliotecaController extends Zend_Controller_Action {

    private $Title                = 'Agregar Bibloteca';

    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_BibliotecaAgregar');
        Zend_Loader::loadClass('Forms_AgregarBiblioteca');
        
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
        $this->session = new Zend_Session_Namespace('session');
        $this->id = new Zend_Session_Namespace('id');
        
       
         // Mandamos a crear el formulario para ser utilizado mediante el AJAX.
          $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
          //$this->view->form = new Forms_AgregarBiblioteca();
          
//var_dump( $this->view->form );
         $this->view->form = $this->NuevoForm();
          
        // $this->SwapBytes_Form->set($this->view->form);
       //  $this->view->form = $this->SwapBytes_Form->get();
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
            //$this->estudiante->setSearch($searchData);
              $paginatorCount = $this->agregar->getSQLCount();  
              $rows           = $this->agregar->getlibro($itemPerPage, $pageNumber);

            // Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                           'width' => '800px');

            $columns = array(array('column'  => 'pk_libro',
                                   'primary' => true,
                                   'hide'    => true),
                
                             array('name'    => 'COTAs',
                                   'width'   => '70px',
                                   'column'  => 'cota',
                                   'rows'    => array('style' => 'text-align:right')),
                             array('name'    => 'Titulo',
                                   'width'   => '200px',
                                   'column'  => 'titulo',
                                   'rows'    => array('style' => 'text-align:center')),
                
                             array('name'    => 'Autor Principal',
                                   'width'   => '200px',
                                   'column'  => 'autor',
                                   'rows'    => array('style' => 'text-align:center')),
                    
                             array('name'    => 'Editorial',
                                   'width'   => '200px',
                                   'column'  => 'editorial',
                                   'rows'    => array('style' => 'text-align:center')),
                       );
            
               // Generamos la lista.
            $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'VUD');
            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
            
   
    }

  public function existsAction() {
        
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
           
                
               $this->getResponse()->setBody(Zend_Json::encode($json));
        }
  }

  public function readonly($Status){
      
                            $this->SwapBytes_Form->readOnlyElement('cota'                   , $Status);
                            $this->SwapBytes_Form->readOnlyElement('titulo'                 , $Status);
                            $this->SwapBytes_Form->readOnlyElement('fk_autorprincipal'      , $Status);
                            $this->SwapBytes_Form->readOnlyElement('fk_autorotro'           , $Status);
                            $this->SwapBytes_Form->readOnlyElement('fk_editorial'           , $Status);
                            $this->SwapBytes_Form->readOnlyElement('fk_materia'             , $Status);
                            $this->SwapBytes_Form->readOnlyElement('fk_pais'                , $Status);
                            $this->SwapBytes_Form->readOnlyElement('ciudad'                 , $Status);
                            $this->SwapBytes_Form->readOnlyElement('ano'                    , $Status);
                            $this->SwapBytes_Form->readOnlyElement('pagina'                 , $Status);
                            $this->SwapBytes_Form->readOnlyElement('nota'                   , $Status);
                            $this->SwapBytes_Form->readOnlyElement('ejemplar'               , $Status);
                            $this->SwapBytes_Form->readOnlyElement('coleccion'              , $Status);
                            $this->SwapBytes_Form->readOnlyElement('volumen'                , $Status);
                            $this->SwapBytes_Form->readOnlyElement('ciudad'                 , $Status);
                            $this->SwapBytes_Form->readOnlyElement('numero'                 , $Status);
      
  }
  
  public function EditorialAction(){// lo cambie de autos a Editorial
       if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
           
           $queryString = $this->_getParam('data');
           $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);
           $json        = array();
           
           $dataRow = $this->agregar->getEditorial($queryArray['editorial']); // buscamos los datos de los libros si existen
           
              }
      
  }
  
  public function cpaisAction(){
       if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
              
           $dataRow = $this->agregar->getciudad($this->_getParam('pais')); 
           $this->SwapBytes_Ajax_Action->fillSelect($dataRow);
           
              }
      
  }
 
  public function addoreditloadAction() {

        $this->id->int = 0;
        
    
      if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {//edit
             $dataRowx       = $this->agregar->getRow($this->_params['modal']['id']); 
             $dataRow = $this->transformarNull($dataRowx);
             // hay q ver si tenemos mas autores y materias
             //$this->SwapBytes_Crud_Form->setProperties($this->view->form,$dataRow[0],'Agregar Libro');
              
         }else{
              $this->SwapBytes_Crud_Form->setProperties($this->view->form,$dataRow[0],'Agregar Libro');
         }
       
        
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
    
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
            // FALTA ACTUALIZAR E INSERTAR LOS AUTORES,MATERIAS
			// Obtenemos los parametros que se esperan recibir.
			$dataRow                  = $this->_params['modal'];
			$id                       = (int)$dataRow['id'];
                        //var_dump($dataRow);
			if(is_numeric($id) && $id > 0) { // update
                                
                          /*       $this->agregar->updatelibro($id,$dataRow['cota'],$dataRow['titulo'],$dataRow['edicion'],$dataRow['fk_pais'],
                                                            $dataRow['ciudad'],$dataRow['fk_editorial'],$dataRow['ano'],$dataRow['pagina'],
                                                            $dataRow['volumen'],$dataRow['ejemplar'],$dataRow['nota'],$dataRow['coleccion'],
                                                            $dataRow['numero']); */
                            
			} else{ // insert

                         /*$this->agregar->InsertLibro($dataRow['cota'],$dataRow['titulo'],$dataRow['edicion'],$dataRow['fk_pais'],
                                                            $dataRow['ciudad'],$dataRow['fk_editorial'],$dataRow['ano'],$dataRow['pagina'],
                                                            $dataRow['volumen'],$dataRow['ejemplar'],$dataRow['nota'],$dataRow['coleccion'],
                                                            $dataRow['numero']);  
                         $pk =   $this->agregar->getPk_libro(); 
                         $this->agregar->InsertAutores($pk[0]['pk_libro'],$dataRow['fk_autorprincipal'],'true');
                             */   
			}
                        
      
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
	    
            $message = 'Seguro desea elmimnar esto?:';
	    $permit = true;
            $dataRowx       = $this->agregar->getRow($this->_params['modal']['id']);
            $dataRowx = $this->transformarNull($dataRowx);
            $dataRow['id'] = (int)$this->_params['modal']['id'];
            $dataRow['cota'] = $dataRowx[0]['cota'];
            $dataRow['titulo'] = $dataRowx[0]['titulo'];
            $dataRow['autor_principal'] = $dataRowx[0]['autor'];
            $dataRow['otro_autor'] = $dataRowx[0]['autor']; // falta terminar
            $dataRow['editorial']  = $dataRowx[0]['editorial'];;
            $dataRow['materia'] = $dataRowx[0]['materia']; // falta terminar
            $dataRow['fk_pais'] = $dataRowx[0]['fk_pais']; 
            $this->view->form->fk_pais->addMultiOption(1,$dataRow['fk_pais']);
            $dataRow['ciudad'] = $dataRowx[0]['ciudad']; 
            $dataRow['fk_ciudad'] = $dataRowx[0]['fk_ciudad']; 
            $this->view->form->ciudad->addMultiOption((int)$dataRow['fk_ciudad'],$dataRow['ciudad']);
            $dataRow['ano'] = $dataRowx[0]['ano']; 
            $this->view->form->ano->addMultiOption(1,$dataRowx['ano']);
            $dataRow['pagina'] = $dataRowx[0]['pagina'];
            $dataRow['nota'] = $dataRowx[0]['nota'];
            $dataRow['ejemplar'] = $dataRowx[0]['ejemplar'];
            $dataRow['volumen'] = $dataRowx[0]['volumen'];
            $dataRow['coleccion'] = $dataRowx[0]['coleccion'];
            $dataRow['numero'] = $dataRowx[0]['numero']; 

            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Libro', $message);
            $this->SwapBytes_Crud_Form->getDeleteLoad($permit,$msg);
        
           
        }
    }

  public function viewAction() {
     // $this->detalleAction();
        // Obtenemos los parametros que se esperan recibir.
       
        
           $dataRow          = $this->agregar->getRow($this->_params['modal']['id']);
      //  var_dump($dataRow);
          $dataRow  = $this->transformarNull($dataRow);
          $dataRow['cota'] = $dataRow[0]['cota'];
          $dataRow['titulo'] = $dataRow[0]['titulo'];
          $dataRow['autor_principal'] = $dataRow[0]['autor'];
          $dataRow['otro_autor'] = $dataRow[0]['autor']; // falta terminar
          $dataRow['editorial']  = $dataRow[0]['editorial'];;
          $dataRow['materia'] = $dataRow[0]['materia']; // falta terminar
          $dataRow['fk_pais'] = $dataRow[0]['fk_pais']; 
          $this->view->form->fk_pais->addMultiOption(1,$dataRow['fk_pais']);
          $dataRow['ciudad'] = $dataRow[0]['ciudad']; 
          $this->view->form->ciudad->addMultiOption(1,$dataRow['ciudad']);
          $dataRow['ano'] = $dataRow[0]['ano']; 
          $this->view->form->ano->addMultiOption(1,$dataRow['ano']);
          $dataRow['pagina'] = $dataRow[0]['pagina'];
          $dataRow['nota'] = $dataRow[0]['nota'];
          $dataRow['ejemplar'] = $dataRow[0]['ejemplar'];
          $dataRow['volumen'] = $dataRow[0]['volumen'];
          $dataRow['coleccion'] = $dataRow[0]['coleccion'];
          $dataRow['numero'] = $dataRow[0]['numero'];
          
          
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Usuario');
        $this->SwapBytes_Crud_Form->getView(); 
       
    }
  
  public function helpAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $this->render();
    }
    
  private function _fillSelectsRecursive($RowData) {

       if ($this->_params['modal']['id'] <> 0 && is_array($RowData) && count($RowData) > 0) {
	  // Modificar
           $pais = $this->agregar->getciudad($this->_params['modal']['fk_pais']);    
	} else if ($this->_params['modal']['id'] == 0) {
	  // Agregar
	  //$institucion = (!empty($this->_params['modal']['fk_institucion'])) ? $this->_params['modal']['fk_institucion'] : $this->_params['default']['institucion'];

          $pais = $this->agregar->getciudad($this->_params['modal']['fk_pais']);   
	}

	$this->SwapBytes_Form->fillSelectBox('ciudad', $pais  , 'pk_atributo', 'ciudad');

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
          
           if ($RowData[0]['nota']=== 'NULL'){
               $RowData[0]['nota'] = '';
          }
          
            if ($RowData[0]['edicion']=== 'NULL'){
               $RowData[0]['edicion'] = '';
          }
         
         return $RowData;
  }

 /* public function agregarautorprincipalAction(){
        
      if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $this->data->array = array();
            $arreglo = array();
            $cantidad = $this->cantidad->int;
            $cantidad = $cantidad + 1;
            $this->cantidad->int = (int)$cantidad;
            
            $total = $cantidad - 16;
            
            $queryString = $this->_getParam('data');
            $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);
            
            $x = 'fk_autorprincipal';
             
            for($i = 0; $i < $total ;$i++){
             $x .= $i;   
             $this->view->form->addElement(new Zend_Form_Element_Select($x));
             $this->SwapBytes_Form->fillSelectBox($x,  $this->agregar->getAutorlibro()  , 'pk_atributo', 'autor');
             $arreglo[$x] = $this->view->form->getElement($x)->getValue();
            }
            
            
            array_push($queryArray, $arreglo);
           
            array_push($this->data->array, $queryArray); // una forma, la otra seria pasarle el arreglo por parametro al load
         
            $this->addoreditloadAction($total); 
           
             
                     
        }                   
    
  }
  */
  public function crearForm($json){
      $text = ""; 
      if (is_array($json)) {
            foreach ($json as $mjson){ 
                $text .= $mjson;
            }
       return $text;     
       }
       return $json;
  }

 public function NuevoForm(){
       $html  = $this->generarHTML();
        
        
        $agregar_autor = "$('#agregar').click(function(){";
        $agregar_autor.= "$.getJSON(urlAjax+'nuevoautor/', function(data){executeCmdsFromJSON(data)}) })";
   
        
        $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
        $json[] = $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 550);
        $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 580);
        $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle('frmModal', "Agregar Libro");
        $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript($agregar_autor);
        $json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter('frmModal');
        $json[] = $this->SwapBytes_Jquery_Ui_Form->open('frmModal'); 
        $form = $this->crearForm($json);
        $this->SwapBytes_Crud_Form->setJson($json); 
        return $form; 
  }
  
  public function generarHTML(){
   $Autor     = $this->agregar->getAutorlibro();
   $Editorial = $this->agregar->getEditorial();
   $Materia   = $this->agregar->getMateria();
   $Pais      = $this->agregar->getpais();
   $Ciudad    = $this->agregar->getciudad($Pais[0]['pk_atributo']);
   $rango  = 600;
   $actual = date ('Y');
   $inicio = $actual - $rango;

                  $html = '<form id ="libros" method="post" enctype="application x-www-form-urlencoded" action ="" >';
                  $html .='<dl class="zend_form">';
                  $html .='<input type="hidden" name="id" value="" id="id" />';
                 
                  //cota falta el evento
                  $html .='<dt id ="cota-label"><label for="cota" class="required">Cota:</label></dt>';
                  $html .='<dd id = "cota-element"><input type="text" name="cota" id="cota" value="" size="10" maxlength="30"/>';
                  //titulo
                  $html .='<dt id ="titulo-label"><label for="titulo" class="required">Titulo:</label></dt>';
                  $html .='<dd id = "titulo-element"><input type="text" name="titulo" id="titulo" value="" size="10" maxlength="30"/>'; 
                  //autor
                  $html .='<dt id ="fk_autor-label"><label for="fk_autor" class="optional">Autor:</label></dt>';
                  $html .='<dd id="fk_autor-element"><select name="fk_autor" id="fk_autor" style="width:200px">';
                  foreach ($Autor as $mAutor){ 
                      $html .= "<option value={$mAutor[pk_atributo]}>{$mAutor[autor]}</option>"; 
                  }
                  $html .='</select></dd>';
                  $html .='<dd id="fk_principal-element"><select name="fk_principal" id="fk_principal" style="width:100px">';
                  $html .= "<option value=1>Principal</option>"; 
                  $html .= "<option value=2>Otro</option>";
                  $html .='</select></dd>';
                  $html .='<dd id="agregar-element"><button id="agregar" type="button" name="agregar">+</button>';
                  $html .='</dd>';
                  $html .='<div id="autores"></div>'; // para agregar los otros autores
                  $html .='</select></dd>';
                  //editorial
                  $html .='<dt id ="fk_editorial-label"><label for="fk_editorial" class="optional">Editorial:</label></dt>';
                  $html .='<dd id="fk_editorial-element"><select name="fk_editorial" id="fk_editorial" style="width:200px">';
                  foreach ($Editorial as $mEditorial){ 
                      $html .= "<option value={$mEditorial[pk_atributo]}>{$mEditorial[editorial]}</option>"; 
                  }
                  $html .='</select></dd>';
                  //materia
                  $html .='<dt id ="fk_materia-label"><label for="fk_materia" class="optional">Materia:</label></dt>';
                  $html .='<dd id="fk_materia-element"><select name="fk_materia" id="fk_materia" style="width:200px">';
                  foreach ($Materia as $mMateria){ 
                      $html .= "<option value={$mMateria[pk_atributo]}>{$mMateria[materia]}</option>"; 
                  }
                  $html .='</select></dd>';
                  $html .='<dd id="agregarm-element"><button id="agregarm" type="button" name="agregarm">+</button>';
                  $html .='</dd>';
                  $html .='<div id="materias"></div>'; // para agregar los otros autores
                  $html .='</select></dd>';
                  //Pais falta el evento
                  $html .='<dt id ="fk_pais-label"><label for="fk_pais" class="optional">Pais:</label></dt>';
                  $html .='<dd id="fk_pais-element"><select name="fk_pais" id="fk_pais" style="width:200px">';
                  foreach ($Pais as $mPais){ 
                      $html .= "<option value={$mPais[pk_atributo]}>{$mPais[pais]}</option>"; 
                  }
                  $html .='</select></dd>';
                  //Ciudad fala el evento
                  $html .='<dt id ="fk_ciudad-label"><label for="fk_ciudad" class="optional">Ciudad:</label></dt>';
                  $html .='<dd id="fk_ciudad-element"><select name="fk_ciudad" id="fk_ciudad" style="width:200px">';
                  foreach ($Ciudad as $mCiudad){ 
                      $html .= "<option value={$mCiudad[pk_atributo]}>{$mCiudad[ciudad]}</option>"; 
                  }
                  $html .='</select></dd>';
                  //Ano
                  $html .='<dt id ="fk_ano-label"><label for="fk_ano" class="optional">Año:</label></dt>';
                  $html .='<dd id="fk_ano-element"><select name="fk_ano" id="fk_ano" style="width:200px">';
                  $html .= "<option value=NA>N/A</option>"; 
                 for($x = $actual ; $x>$inicio; $x--){
                      $html .= "<option value={$x}>{$x}</option>"; 
                  }
                  $html .='</select></dd>';
                  //Pagina
                  $html .='<dt id ="pagina-label"><label for="pagina" class="optional">Pagina:</label></dt>';
                  $html .='<dd id = "pagina-element"><input type="text" name="pagina" id="pagina" value="" size="10" maxlength="30"/>'; 
                   //Nota
                  $html .='<dt id ="nota-label"><label for="nota" class="optional">Nota:</label></dt>';
                  $html .='<dd id = "nota-element"><input type="text" name="nota" id="nota" value="" size="10" maxlength="30"/>'; 
                 //Ejemplar
                  $html .='<dt id ="ejemplar-label"><label for="ejemplar" class="optional">Ejemplar:</label></dt>';
                  $html .='<dd id ="ejemplar-element"><input type="text" name="ejemplar" id="ejemplar" value="" size="10" maxlength="30"/>'; 
                  //Volumen
                  $html .='<dt id ="volumen-label"><label for="volumen" class="optional">Volumen:</label></dt>';
                  $html .='<dd id ="volumen-element"><input type="text" name="volumen" id="volumen" value="" size="10" maxlength="30"/>'; 
                  //Coleccion
                  $html .='<dt id ="coleccion-label"><label for="coleccion" class="optional">Coleccion:</label></dt>';
                  $html .='<dd id ="ejemplar-coleccion"><input type="text" name="coleccion" id="coleccion" value="" size="10" maxlength="30"/>'; 
                  //Numero
                  $html .='<dt id ="numero-label"><label for="numero" class="optional">Numero:</label></dt>';
                  $html .='<dd id ="numero-element"><input type="text" name="numero" id="numero" value="" size="10" maxlength="30"/>'; 
                  
                  
                  $html .='</dl>';
                  $html .='</form>';
                 

   
  return $html;                
 }
  
  
  public function nuevoautorAction(){
         
            $this->SwapBytes_Ajax->setHeader();
            $Autor     = $this->agregar->getAutorlibro();

            $id = $this->id->int;
            $id = $id + 1;
            $this->id->int = (int)$id;
           
           // $autor     = 'Autor'.$id;
           /* $tipo      = 'TipoAutor'.$id;
            $eliminar  = 'EliminarAutor'.$id;
            $tbl_autor = 'tbl_autor'.$id; */
           
                       $txt  = 'eliminar'.$id;
                       $eliminar_autor = "$('#$txt').click(function(){";
                       $eliminar_autor.= "$.getJSON(urlAjax+'eliminarautor/id/".$id."', function(data){executeCmdsFromJSON(data)}) })"; 
                     
                  $html  ="<dt id ='fk_autor-label$id'><label for='fk_autor' class='optional'>Autor:</label></dt>";
                  $html .="<dd id='fk_autor-element$id'><select name='fk_autor' id='fk_autor' style='width:150px'>";
                  foreach ($Autor as $mAutor){ 
                      $html .= "<option value={$mAutor[pk_atributo]}>{$mAutor[autor]}</option>"; 
                  }
                  $html .='</select></dd>';
                  $html .="<dd id='fk_principal$id-element'><select name='fk_principal' id='fk_principal' style='width:100px'>";
                  $html .= "<option value=1>Principal</option>"; 
                  $html .= "<option value=2>Otro</option>";
                  $html .='</select></dd>';
                  $html .="<dd id='eliminar-element$id'><button id='eliminar$id' type='button' name='eliminar'>-</button>";
                  $html .='</dd>'; 
                   /*    
                 /*    
               
                  $html  ="<table id='$tbl_autor'>";
                  $html .="<tr>";
                  $html .="<td><p>Autor : </p></td>";
                  $html .="<td>";
                  $html .= "<select id='$autor'>";
                  foreach ($Autor as $mAutor){ 
                      $html .= "<option value='$mAutor[pk_atributo]'>{$mAutor[autor]}</option>"; 
                  }
                  $html .= "</select>";
                  $html .="</td>";
                  
                   //Tipo de autor
                  $html .= "<td>";
                  $html .= "<select id='$tipo'>";
                  $html .= "<option value='1'>Principal</option>"; 
                  $html .= "<option value='2'>Otro</option>"; 
                  $html .= '</select>';
                  $html .= '</td>';
                  
                  //boton de eliminar autor
                  $html .= "<td>";
                  $html .= "<input id='$eliminar' type='button' value='-' ></input>";
                  $html .= "</td>";
                  
                  $html .="</tr>";
                  $html .= "</table>";
                  
         */
          $json[] = '$("#autores").append("'.$html.'")';
          
          $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript($eliminar_autor); 
          
          
         
          $this->getResponse()->setBody(Zend_Json::encode($json));
    }

  public function eliminarautorAction(){
      if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
           $id = $this->_getParam('id');
         /*  $tbl_autor = 'tbl_autor'.$id; // tabla a borrar
           $json[] = "$('#$tbl_autor').hide()"; */
           $h_label     = 'fk_autor-label'.$id;
           $h_select    = 'fk_autor-element'.$id;
           $h_principal = 'fk_principal'.$id.'-element';
           $h_button    = 'eliminar-element'.$id;
           $json[]      = "$('#$h_label').hide()";
           $json[]      = "$('#$h_select').hide()";
           $json[]      = "$('#$h_principal').hide()";
           $json[]      = "$('#$h_button').hide()";
      }
      $this->getResponse()->setBody(Zend_Json::encode($json));
  } 

  
  
}

