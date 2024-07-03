<?php
/**
 * @todo Ocultar el boton "Ocultar" del formulario Ver.
 * @todo Ocultar el boton "Eliminar" del formulario Eliminar, solo cuando no se
 *       permita.
 */
class Transactions_AgregarlibrosController extends Zend_Controller_Action {

    private $Title                = 'Agregar Libros';

    public function init() {
            /* Initialize action controller here */
            Zend_Loader::loadClass('Une_Filtros');
            Zend_Loader::loadClass('Models_DbTable_Usuarios');
            Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
            Zend_Loader::loadClass('Models_DbTable_BibliotecaAgregar');
            Zend_Loader::loadClass('Forms_Agregarv2');
//            Zend_Loader::loadClass('Forms_Guardarautor');
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
            $this->CmcBytes_Filtros         = new CmcBytes_Filtros();

            $this->Request = Zend_Controller_Front::getInstance()->getRequest();

         // Mandamos a crear el formulario para ser utilizado mediante el AJAX.
          $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
          $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
          
         $this->view->form = new Forms_Agregarv2();
         $this->SwapBytes_Form->set($this->view->form);
         $this->view->form = $this->SwapBytes_Form->get();
        
        $this->SwapBytes_Crud_Action->setDisplay(true, true, true ,false,true,true);
        $this->SwapBytes_Crud_Action->setEnable(true, true, false,false,false,false);
        
      
         
        $this->view->form->fk_principal->addMultiOption('1', 'principal');
        $this->view->form->fk_principal->addMultiOption('2', 'otro');
         
        // $this->SwapBytes_Form->fillSelectBox('fk_pais',  $this->agregar->getpais()  , 'pk_atributo', 'pais'); // llenado del pais
        $this->copiar = new Zend_Session_Namespace("copiar");
        
        $rango  = 600;
        $actual = date ('Y');
        $inicio = $actual - $rango;
        $this->view->form->fk_ano->addMultiOption(0, 's.a');
        for($x = $actual ; $x>$inicio; $x--){
            $this->view->form->fk_ano->addMultiOption($x, $x);

        }
        
        
         $this->tablas = Array(
                                  
                              'Sede'    => Array('vw_sedes',
                                                 null     ,
                                                 Array('pk_estructura',
                                                       'nombre'),
                                                 'DESC'),
        );
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

  public function filterAction(){
            $this->SwapBytes_Ajax->setHeader(); 
            $select = $this->_getParam('select');
            $values = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));

            if(!$select || !$values){
                $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,null,1,null);
            }else{
                $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,$values,null,$select);
            }            
            $this->getResponse()->setBody(Zend_Json::encode($json));
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
    }
    
  public function existsAction() {
        
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $queryString = $this->_getParam('data');
            $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);

            $verificarlibro = $this->agregar->getlibro_cotas($queryArray['cota'],$queryArray['fk_sede']);



            if(!empty($verificarlibro)){//cota ya existente
              $libro = $this->agregar->getlibro_cota($queryArray['cota'],$queryArray['fk_sede']);
              
            }else{

              $libro = $this->agregar->getlibro_cota($queryArray['cota']);
              

            }
            
            

            
            if($libro != NULL){ // el libro existe
               
           $libro          = $this->transformarNull($libro);
              
           $autor          = $this->agregar->getRowAutor($libro[0]['pk_libro']);
           $materia        =  $this->agregar->getRowmateria($libro[0]['pk_libro']);
            
            // recreamos el formulario
            $cantidad_autores = count($autor);
            $cantidad_materia = count($materia);
            
            $pos_materia = 19 + ($cantidad_autores - 1)*3;
            $pos_autor = 8;


            
            $this->crearCampo($cantidad_autores -1 , 'autor', $pos_autor); // creamos el autor
            $this->crearCampo($cantidad_materia -1 , 'materia', $pos_materia); // creamos la materias
           
           // llenamos el formulario.
       
            //autores
            $pos = -1;
            
            foreach ($autor as $au){
                if($pos < 0){
                     $dataRow['fk_autor']     = $au['autor'];
                     $dataRow['fk_principal'] = $au['principal'];
                }else{
                    $dataRow['fk_autor'.$pos] = $au['autor'];
                    $dataRow['fk_principal'.$pos] = $au['principal'];
                }
                
                $pos++;
            }
            
            $posma = -1;
            
            foreach ($materia as $mat){
                if($posma < 0){
                     $dataRow['fk_materia']   = $mat['materia'];
                    
                }else{
                    $dataRow['fk_materia'.$posma] = $mat['materia'];
                    
                }
                
                $posma++;
            }


          $sede = $this->agregar->getsedeform($libro[0]['sede']);
          // $this->SwapBytes_Form->fillSelectBox('fk_sede',  $sede  , 'pk_estructura', 'sede');
          $dataRow['cota'] = $libro[0]['cota'];
          $dataRow['titulo'] = $libro[0]['titulo'];
          $dataRow['fk_editorial']  = $libro[0]['editorial'];
          $dataRow['edicion']  = $libro[0]['edicion'];
          $dataRow['fk_pais'] = $libro[0]['pais']; 
          // $this->view->form->fk_pais->addMultiOption(1,$dataRow['fk_pais']);
          $dataRow['fk_ciudad'] = $libro[0]['ciudad']; 
          // $dataRow['fk_ciudad'] = $libro[0]['fk_ciudad']; 
          // $this->view->form->fk_ciudad->addMultiOption($dataRow['fk_ciudad'],$dataRow['ciudad']);
          $dataRow['ano'] = $libro[0]['ano']; 
          
          $dataRow['pagina'] = $libro[0]['pagina'];
          $dataRow['nota'] = $libro[0]['nota'];
          $dataRow['ejemplar'] = $libro[0]['ejemplar'];
          $dataRow['volumen'] = $libro[0]['volumen'];
          $dataRow['coleccion'] = $libro[0]['coleccion'];
          $dataRow['numero'] = $libro[0]['numero'];
        
           $this->readonly('true');
           
           if($queryArray['fk_sede'] != $sede[0]['pk_estructura']){
              $html   = $this->SwapBytes_Html_Message->alert("Cota existente en: ".$sede[0]['sede'].". Desea agregarla de todos modos?");  
              $this->SwapBytes_Form->fillSelectBox('fk_sede',  $this->agregar->getsedeform($queryArray['fk_sede'])  , 'pk_estructura', 'sede');
              $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');

            }else{
              $html   = $this->SwapBytes_Html_Message->alert("Cota existente");  
              $this->SwapBytes_Form->fillSelectBox('fk_sede',  $this->agregar->getsedeform($sede[0]['pk_estructura'])  , 'pk_estructura', 'sede');
              $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
            }   
            

           if(isset($dataRow)) {
				
                    $this->view->form->populate($dataRow);
                }      
          
          
          
          $html  .= $this->SwapBytes_Ajax->render($this->view->form);
          $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
          $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
          $json[] = $this->SwapBytes_Jquery->setValSelectOption('fk_ano',$dataRow['ano']);
                 
            }
          
          
                
            $this->getResponse()->setBody(Zend_Json::encode($json));
           
			
        }
    }
  
  public function listAction() {
         // Verificamos si es una llamada de tipo AJAX.
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
           
            // Obtenemos los parametros necesarios que se esta pasando por POST, y
            // los valores necesarios de las variables que se utilizaran mas adelante.
            $pageNumber  = $this->_getParam('page', 1);
            $searchData  = $this->_getParam('buscar');      

            $searchData = str_replace("~", "/", $searchData); 

            //sale doble(DEPURAR)
            // P.O./367.7/C765ab



            $itemPerPage = 15;
            $pageRange   = 10;
            $filtro = $this->_getParam('filtro');
            $json[] = ' $("#mensaje").hide()';
            
              if($this->_params['filters']['Sede']!="" || $filtro != "" ){    
                  if($this->_params['filters']['Sede']!=""){
                     $filtro = $this->_params['filters']['Sede'];
                      
                  }
              $paginatorCount = $this->agregar->getSQLCount($filtro);

              
              if(!empty($searchData)){
                $libros = $this->agregar->getlibro($itemPerPage, $pageNumber,$filtro,$searchData);
              }else{
                $libros = $this->agregar->getlibro($itemPerPage, $pageNumber,$filtro,$searchData);  
              }
              
              $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnAdd', false);
              $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnCopy', false);
              $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnPaste', false);
              }else{
              $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnAdd', true);
              $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnCopy', true);
              $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnPaste', true);
              }
              $pk_libro           = $this->pk_libros($libros);
              if($libros!=NULL){
              $autor_principal  = $this->agregar->get_autor($pk_libro,'t');         
              $autor_otro  = $this->agregar->get_autor($pk_libro,'f'); 
              $rows = $this->transformar($libros,$autor_principal,$autor_otro);
              $rows = $this->transformarNull($rows);
              $rows = $this->buscar($rows, $searchData);
             
                if($searchData != ""){
                  $paginatorCount     = Count($rows);
                }
              
              }


            // Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                           'width' => '800px');

            $columns = array(array('column'  => 'pk_libro',
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
                                                      'name'  => 'chkLibros',
                                                      'value' => '##pk_libro##')),       
                
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
                
                                   
                       );
            
               // Generamos la lista.
              
            $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'VUD');
            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkLibros');
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
            
   
    }

  public function autoraddAction(){
     
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();
         
          $queryString = $this->_getParam('data');
          $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);
          //$buscar = explode("~", $queryArray['buscar_hide']);
          $cantidad_autores = $this->cont_element($queryArray, 'fk_principal');
          $cantidad_materia = $this->cont_element($queryArray, 'fk_materia');
          
          $pos_materia = 19 + ($cantidad_autores)*3;
          $pos_autor = 6;
          
          $this->crearCampo($cantidad_autores, 'autor', $pos_autor); // creamos el autor
          $this->crearCampo($cantidad_materia - 1, 'materia', $pos_materia); // re creamos las materias

           $this->addoreditloadAction($queryArray);
     }

  }
  
  public function materiaaddAction(){
       if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();
         
          $queryString = $this->_getParam('data');
          $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);
          $cantidad_autores = $this->cont_element($queryArray, 'fk_principal');
          $pos = 19 + ($cantidad_autores - 1)*3;
          
          $cantidad_materia = $this->cont_element($queryArray, 'fk_materia');

          $this->crearCampo($cantidad_autores - 1, 'autor',6);   // re-creamos los autores se coloca el -1 para no crear un nuevo autor
          $this->crearCampo($cantidad_materia, 'materia', $pos); // creamos la materias
    
          $this->addoreditloadAction($queryArray);
                                   
         
     }

  }
 
  public function crearCampo($cantidad , $campo ,$pos){
        
      if($campo == "autor"){
      for($i = -1; $i < $cantidad ;$i++){
          if($i == -1){
                foreach ($buscar as $valor){  
               // $this->SwapBytes_Form->fillSelectBox('fk_autor',  $this->agregar->getAutores($valor)  , 'pk_atributo', 'autor'); 
                }
           }else{
               
          $principal = 'fk_principal'.$i;   
          $this->view->form->addAutor($i,$pos);
        //  $this->SwapBytes_Form->fillSelectBox('fk_autor'.$i,  $this->agregar->getAutores()  , 'pk_atributo', 'autor'); // llenado de los autoresprincipal 
          $this->view->form->{$principal}->addMultiOption('1', 'Principal');
          $this->view->form->{$principal}->addMultiOption('2', 'Otro');
          
          foreach ($buscar as $valor){   
              //  $this->SwapBytes_Form->fillSelectBox('fk_autor'.$i,  $this->agregar->getAutores($valor)  , 'pk_atributo', 'autor'); 
                }
          $pos = $pos + 3;
           }
        }
      }else{
         for($i = -1; $i < $cantidad ;$i++){
           if($i == -1){
               
                foreach ($buscar as $valor){ 
                    if($valor != ""){
                //$this->SwapBytes_Form->fillSelectBox('fk_materia',  $this->agregar->getMateria($valor)  , 'pk_atributo', 'materia'); 
                    }
                }
           }else{
                $this->view->form->addMateria($i,$pos);
                //$this->SwapBytes_Form->fillSelectBox('fk_materia'.$i,  $this->agregar->getMateria()  , 'pk_atributo', 'materia');

                foreach ($buscar as $valor){  
                    if($valor != ""){
               // $this->SwapBytes_Form->fillSelectBox('fk_materia'.$i,  $this->agregar->getMateria($valor)  , 'pk_atributo', 'materia'); 
                    }
                }
                $pos = $pos + 2;
           }
        } 
         
          
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

  public function deletemateriaAction(){
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();
                $id = $this->_getParam('id');
           
                $materia = 'fk_materia'.$id;
                $butom = 'eliminar_materia'.$id;

                $json[] = "$('#$materia-element').remove()";
                $json[] = "$('#$materia-label').remove()";

                $json[] = "$('#$butom').remove()";

                $this->getResponse()->setBody(Zend_Json::encode($json));
            
         }
  }
 
  public function addoreditloadAction($queryArray=null) {
      if($queryArray!=NULL){
          $json= $this->llenar_form($queryArray);
          $this->SwapBytes_Crud_Form->setJson($json);
      }
      if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0 ) {

             $libro          = $this->agregar->getRowLibro($this->_params['modal']['id']);
             $libro          = $this->transformarNull($libro);
      
          if($queryArray==NULL){
                    
                    // data extra necesaria
                    $autor          = $this->agregar->getRowAutor($this->_params['modal']['id']);
                    $materia        = $this->agregar->getRowmateria($this->_params['modal']['id']);
                    
                    // recreamos el formulario
                    $cantidad_autores = count($autor);
                    $cantidad_materia = count($materia);

                    $pos_materia = 19 + ($cantidad_autores - 1)*3;
                    $pos_autor = 6;
                    $this->crearCampo($cantidad_autores -1 , 'autor', $pos_autor); // creamos el autor
                    $this->crearCampo($cantidad_materia -1 , 'materia', $pos_materia); // creamos la materias

                // llenamos el formulario.

                    //autores
                    $pos = -1;

                    foreach ($autor as $au){

                        if($au['autor'] == "N/A"){
                          $au['autor'] = "";
                        }



                        if($pos < 0){
                            $dataRow['fk_autor']     = $au['autor'];
                            $dataRow['fk_principal'] = $au['principal'];
                        }else{
                            $dataRow['fk_autor'.$pos] = $au['autor'];
                            $dataRow['fk_principal'.$pos] = $au['principal'];
                            
                        }

                        $pos++;
                    }

                    $pos = -1;

                    foreach ($materia as $mat){

                        if($mat['materia'] == "N/A"){
                          $mat['materia'] = "";
                        }

                        if($pos < 0){
                            $dataRow['fk_materia']   = $mat['materia'];

                        }else{
                            $dataRow['fk_materia'.$pos] = $mat['materia'];

                        }

                        $pos++;
                    }

                    
                $dataRow['buscar_hide'] =  $buscarx;   
                $dataRow['id'] = $this->_params['modal']['id']; 
                $dataRow['cota'] = $libro[0]['cota'];
                $dataRow['titulo'] = $libro[0]['titulo'];
                $dataRow['edicion'] = $libro[0]['edicion'];
                $dataRow['fk_editorial']  = $libro[0]['editorial'];
                $dataRow['fk_pais'] = $libro[0]['pais']; 
                // $this->view->form->fk_pais->addMultiOption(1,$dataRow['fk_pais']);
                // $dataRow['ciudad'] = $libro[0]['ciudad'];
                $dataRow['fk_ciudad'] = $libro[0]['ciudad'];
                // $this->view->form->fk_ciudad->addMultiOption($dataRow['fk_ciudad'],$dataRow['ciudad']);
                $dataRow['fk_ano'] = $libro[0]['ano']; 
                $this->view->form->fk_ano->addMultiOption(1,$dataRow['fk_ano']); // ver el NA
                $dataRow['pagina'] = $libro[0]['pagina']; 
                $dataRow['nota'] = $libro[0]['nota'];
                $dataRow['ejemplar'] = $libro[0]['ejemplar'];
                $dataRow['volumen'] = $libro[0]['volumen'];
                $dataRow['coleccion'] = $libro[0]['coleccion'];
                $dataRow['numero'] = $libro[0]['numero'];
                $dataRow['fk_sede'] = $libro[0]['fk_sede'];
                $this->view->form->fk_sede->addMultiOption($dataRow['fk_sede'],$libro[0]['sede']);
               // var_dump($dataRow);
                
              }else{
                  // mejorar
                $dataRow['ciudad'] = $libro[0]['ciudad'];
                $dataRow['fk_ciudad'] = $libro[0]['fk_ciudad'];
                // $this->view->form->fk_ciudad->addMultiOption($dataRow['fk_ciudad'],$dataRow['ciudad']);
                 $this->SwapBytes_Form->fillSelectBox('fk_sede',  $this->agregar->getsedeform($queryArray['fk_sede'])  , 'pk_estructura', 'sede');
                }

      }else{
           // $this->SwapBytes_Form->fillSelectBox('fk_ciudad',  $this->agregar->getciudad(2114)  , 'pk_atributo', 'ciudad');
           if($this->_params['filters']['Sede']!=""){
           $this->SwapBytes_Form->fillSelectBox('fk_sede',  $this->agregar->getsedeform($this->_params['filters']['Sede'])  , 'pk_estructura', 'sede');
           }else{
               if($queryArray['fk_sede'] != ""){
                    $this->SwapBytes_Form->fillSelectBox('fk_sede',  $this->agregar->getsedeform($queryArray['fk_sede'])  , 'pk_estructura', 'sede');
               }
           }
           
      }
      
     
      $this->SwapBytes_Crud_Form->setProperties($this->view->form,$dataRow,'Agregar Libro');

      $this->SwapBytes_Crud_Form->getAddOrEditLoad();
    }
    
  public function addoreditconfirmAction() {
     
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
                if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0 ) {
                 

                }else{

                        $cantidad_autores = $this->cont_element($this->_params['modal'], 'fk_principal');
                        $cantidad_materia = $this->cont_element($this->_params['modal'], 'fk_materia');
                        $pos_materia = 19 + ($cantidad_autores - 1)*3;
                        $pos_autor = 6;
                        $this->crearCampo($cantidad_autores - 1, 'autor', $pos_autor); // creamos el autor
                        $this->crearCampo($cantidad_materia - 1 , 'materia', $pos_materia); // creamos la materias*/ 
           
                }
                 // $this->SwapBytes_Form->fillSelectBox('fk_ciudad',  $this->agregar->getciudad(2114)  , 'pk_atributo', 'ciudad');
                 $this->SwapBytes_Form->fillSelectBox('fk_sede',  $this->agregar->getsedeform($this->_params['filters']['Sede'])  , 'pk_estructura', 'sede');
            
            

           
           

           // para el pais
           if(!empty($this->_params['modal']['fk_pais'])){
              $pais = $this->agregar->getbypais($this->_params['modal']['fk_pais']);
              $this->_params['modal']['fk_pais'] = $pais[0]['pk_atributo'];
           }else{
              $sinpais = $this->agregar->getsinpais();
              $this->_params['modal']['fk_pais'] = $sinpais[0]['pk_atributo'];
           }

           //para la ciudad
           if (!empty($this->_params['modal']['fk_ciudad'])) {
             $fk_ciudad = $this->agregar->getbyciudad($pais[0]['pk_atributo'],$this->_params['modal']['fk_ciudad']);
             $this->_params['modal']['fk_ciudad'] = $fk_ciudad[0]['pk_atributo'];
           }else{

              $sinciudad = $this->agregar->getsinciudad();
              $this->_params['modal']['fk_ciudad'] = $sinciudad[0]['pk_atributo'];
           }



           // $this->_fillSelectsRecursive($this->_params['modal']); // permite la recursividad del pais/ciudad 
           $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']); 
           $this->SwapBytes_Crud_Form->getAddOrEditConfirm();

            }   
	}

  public function addoreditresponseAction() {
    
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            

      // para el pais
           if(!empty($this->_params['modal']['fk_pais'])){
              $pais = $this->agregar->getbypais($this->_params['modal']['fk_pais']);
              $this->_params['modal']['fk_pais'] = $pais[0]['pk_atributo'];
           }else{
              $sinpais = $this->agregar->getsinpais();
              $this->_params['modal']['fk_pais'] = $sinpais[0]['pk_atributo'];
           }

           //para la ciudad
           if (!empty($this->_params['modal']['fk_ciudad'])) {
             $fk_ciudad = $this->agregar->getbyciudad($pais[0]['pk_atributo'],$this->_params['modal']['fk_ciudad']);
             $this->_params['modal']['fk_ciudad'] = $fk_ciudad[0]['pk_atributo'];
           }else{

              $sinciudad = $this->agregar->getsinciudad();
              $this->_params['modal']['fk_ciudad'] = $sinciudad[0]['pk_atributo'];
           }           
           
         
			// Obtenemos los parametros que se esperan recibir.
			$dataRow                  = $this->_params['modal'];


			$id                       = (int)$dataRow['id'];
                        $autor_array    = $this->crear_array($dataRow,'fk_autor');
                        $materia_array  = $this->crear_array($dataRow,'fk_materia');
                        $principal      = $this->crear_array($dataRow,'fk_principal');
                        $val = true;
                        $val = $this->get_existe($autor_array,'autor');
                        $val = $this->get_existe($materia_array,'materia');
                        $val = $this->get_existe($dataRow['fk_editorial'],'editorial');
                        
                        if($val){
                        if(is_numeric($id) && $id > 0) { // editar
                            
                            $pk_editorial = $this->agregar->getEditoriales($dataRow['fk_editorial']); 
                                // update del tbl_libro
                                   $this->agregar->updatelibro($id,$dataRow['cota'],$dataRow['titulo'],$dataRow['edicion'],$dataRow['fk_pais'],
                                                            $dataRow['fk_ciudad'], $pk_editorial[0]['pk_atributo'],$dataRow['fk_ano'],$dataRow['pagina'],
                                                            $dataRow['volumen'],$dataRow['ejemplar'],$dataRow['nota'],$dataRow['coleccion'],
                                                            $dataRow['numero'],$dataRow['fk_sede']);
                            
                            // borrar los autores
                            $this->agregar->deleteallAutores($id);
                            // agregar autores
                           for ($i = 0 ; $i < count($autor_array); $i++){
                                
                                if(empty($autor_array[$i])){//si viene vacio, usara el autor N/A
                                    $fk_autor= $this->agregar->getAutor("N/A");
                                }else{//sino, toma el autor
                                    $fk_autor= $this->agregar->getAutor($autor_array[$i]);
                                }



                                $this->agregar->InsertAutores($id,$fk_autor[0]['pk_atributo'],$principal[$i]);
                           }
                           
                           // borrar las materias
                            $this->agregar->deleteallMaterias($id);
                            // agregar materias
                           for ($i = 0 ; $i < count($materia_array); $i++){


                                if(empty($materia_array[$i])){//si viene vacio, usara la materia N/A

                                  $fk_materia= $this->agregar->getMaterias("N/A");  
                                }else{//sino, toma la materia
                                  $fk_materia= $this->agregar->getMaterias($materia_array[$i]);
                                }


                                $this->agregar->InsertMateria($id,$fk_materia[0]['pk_atributo']);
                           }
                          
                         
                        }else{// agregar
                           $pk_editorial = $this->agregar->getEditoriales($dataRow['fk_editorial']);   
                           // agrega el libro
                         $this->agregar->InsertLibro($dataRow['cota'],$dataRow['titulo'],$dataRow['edicion'],$dataRow['fk_pais'],
                                                            $dataRow['fk_ciudad'],$pk_editorial[0]['pk_atributo'],$dataRow['fk_ano'],$dataRow['pagina'],
                                                            $dataRow['volumen'],$dataRow['ejemplar'],$dataRow['nota'],$dataRow['coleccion'],
                                                            $dataRow['numero'],$dataRow['fk_sede']);  
                          
                       
                         $pk_libro =   $this->agregar->getPk_libro(); 
                         
                          // agregar autores
                          //busqueda de pk de autores
                          
                          for ($i = 0 ; $i<count($autor_array); $i++){

                            if(empty($autor_array[$i])){//si viene vacio, usara el autor N/A
                                $fk_autor= $this->agregar->getAutor("N/A");
                            }else{//sino, toma el autor
                                $fk_autor= $this->agregar->getAutor($autor_array[$i]);
                            }

                            $this->agregar->InsertAutores($pk_libro[0]['pk_libro'],$fk_autor[0]['pk_atributo'],$principal[$i]);
 
                          }
                          
                          // agregar materias
                          //busqueda de pk de materias
                           $materia_array  = $this->crear_array($dataRow,'fk_materia');
                           for ($i = 0 ; $i<count($materia_array); $i++){

                                if(empty($materia_array[$i])){//si viene vacio, usara la materia N/A
                                  $fk_materia= $this->agregar->getMaterias("N/A");  
                                }else{//sino, toma la materia
                                  $fk_materia= $this->agregar->getMaterias($materia_array[$i]);
                                }
                                
                                $this->agregar->InsertMateria($pk_libro[0]['pk_libro'],$fk_materia[0]['pk_atributo']);  
                            }
        } 
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
            $prestamo = $this->agregar->get_libro_prestado($this->_params['modal']['id']);
            if($prestamo[0]['pk_prestamo']!= ''){
                 $permit = false;
                 $message = "El libro esta prestado";
            }
            // data necesaria
            $libro          = $this->agregar->getRowLibro($this->_params['modal']['id']);
            $autor          = $this->agregar->getRowAutor($this->_params['modal']['id']);
            $materia        =  $this->agregar->getRowmateria($this->_params['modal']['id']);
            $libro          = $this->transformarNull($libro);
             
            // recreamos el formulario
            $cantidad_autores = count($autor);
            $cantidad_materia = count($materia);
            
            $pos_materia = 19 + ($cantidad_autores - 1)*3;
            $pos_autor = 6;
            $this->crearCampo($cantidad_autores -1 , 'autor', $pos_autor); // creamos el autor
            $this->crearCampo($cantidad_materia -1 , 'materia', $pos_materia); // creamos la materias
           
           // llenamos el formulario.
       
            //autores
            $pos = -1;
            
            foreach ($autor as $au){

              if($au['autor'] == "N/A"){
                $au['autor'] = "";
              }              


                if($pos < 0){
                     $dataRow['fk_autor']     = $au['autor'];
                     $dataRow['fk_principal'] = $au['principal'];
                }else{
                    $dataRow['fk_autor'.$pos] = $au['autor'];
                    $dataRow['fk_principal'.$pos] = $au['principal'];
                }
                
                $pos++;
            }
            
            $pos = -1;
            
            foreach ($materia as $mat){

              if($mat['materia'] == "N/A"){
                $mat['materia'] = "";
              }

              
                if($pos < 0){
                     $dataRow['fk_materia']   = $mat['materia'];
                    
                }else{
                    $dataRow['fk_materia'.$pos] = $mat['materia'];
                    
                }
                
                $pos++;
            }
         
      
          $dataRow['id'] = $this->_params['modal']['id'];  
          $dataRow['cota'] = $libro[0]['cota'];
          $dataRow['titulo'] = $libro[0]['titulo'];
           $dataRow['edicion'] = $libro[0]['edicion'];
          $dataRow['fk_editorial']  = $libro[0]['editorial'];;
          $dataRow['fk_pais'] = $libro[0]['pais']; 
          // $this->view->form->fk_pais->addMultiOption(1,$dataRow['fk_pais']);
          $dataRow['fk_ciudad'] = $libro[0]['ciudad']; 
          // $this->view->form->fk_ciudad->addMultiOption(1,$dataRow['ciudad']);
          $dataRow['fk_ano'] = $libro[0]['ano']; 
          $this->view->form->fk_ano->addMultiOption(1,$dataRow['ano']); // ver el NA
          $dataRow['pagina'] = $libro[0]['pagina'];
          $dataRow['nota'] = $libro[0]['nota'];
          $dataRow['ejemplar'] = $libro[0]['ejemplar'];
          $dataRow['volumen'] = $libro[0]['volumen'];
          $dataRow['coleccion'] = $libro[0]['coleccion'];
          $dataRow['numero'] = $libro[0]['numero'];
          $dataRow['fk_sede'] = $libro[0]['fk_sede'];
          $this->view->form->fk_sede->addMultiOption($dataRow['fk_sede'],$libro[0]['sede']);
            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Libro', $message);
            $this->SwapBytes_Crud_Form->getDeleteLoad($permit,$msg);
        
           
        }
    }

  public function viewAction() {
           
            // data necesaria
            $libro          = $this->agregar->getRowLibro($this->_params['modal']['id']);
            $autor          = $this->agregar->getRowAutor($this->_params['modal']['id']);
            $materia        = $this->agregar->getRowmateria($this->_params['modal']['id']);
            $libro          = $this->transformarNull($libro);
           
            // recreamos el formulario
            $cantidad_autores = count($autor);
            $cantidad_materia = count($materia);
            
            $pos_materia = 19 + ($cantidad_autores - 1)*3;
            $pos_autor = 6;
            $this->crearCampo($cantidad_autores -1 , 'autor', $pos_autor); // creamos el autor
            $this->crearCampo($cantidad_materia -1 , 'materia', $pos_materia); // creamos la materias
           
           // llenamos el formulario.
       
            //autores
            $pos = -1;
            
            foreach ($autor as $au){

                if($au['autor'] == "N/A"){
                  $au['autor'] = "";
                }

                if($pos < 0){
                     $dataRow['fk_autor']     = $au['autor'];
                     $dataRow['fk_principal'] = $au['principal'];
                }else{
                    $dataRow['fk_autor'.$pos] = $au['autor'];
                    $dataRow['fk_principal'.$pos] = $au['principal'];
                }
                
                $pos++;
            }
            
            $pos = -1;
            
            foreach ($materia as $mat){

                if($mat['materia'] == "N/A"){
                  $mat['materia'] = "";
                }

                if($pos < 0){
                     $dataRow['fk_materia']   = $mat['materia'];
                    
                }else{
                    $dataRow['fk_materia'.$pos] = $mat['materia'];
                    
                }
                
                $pos++;
            }
         
        
         // $dataRow  = $this->transformarNull($dataRow);
           
          $dataRow['cota'] = $libro[0]['cota'];
          $dataRow['titulo'] = $libro[0]['titulo'];
          $dataRow['fk_editorial']  = $libro[0]['editorial'];
           $dataRow['edicion'] = $libro[0]['edicion'];
          $dataRow['fk_pais'] = $libro[0]['pais']; 
          // $this->view->form->fk_pais->addMultiOption(1,$dataRow['fk_pais']);
          $dataRow['fk_ciudad'] = $libro[0]['ciudad']; 
          // $this->view->form->fk_ciudad->addMultiOption(1,$dataRow['ciudad']);
          $dataRow['fk_ano'] = $libro[0]['ano']; 
          $this->view->form->fk_ano->addMultiOption(1,$dataRow['ano']); // ver el NA
          $dataRow['pagina'] = $libro[0]['pagina'];
          $dataRow['nota'] = $libro[0]['nota'];
          $dataRow['ejemplar'] = $libro[0]['ejemplar'];
          $dataRow['volumen'] = $libro[0]['volumen'];
          $dataRow['coleccion'] = $libro[0]['coleccion'];
          $dataRow['numero'] = $libro[0]['numero'];
          $dataRow['fk_sede'] = $libro[0]['fk_sede'];
          $this->view->form->fk_sede->addMultiOption($dataRow['fk_sede'],$libro[0]['sede']);
          
          
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Libro');
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

	$this->SwapBytes_Form->fillSelectBox('fk_ciudad', $pais  , 'pk_atributo', 'ciudad');

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

  public function pk_libros($RowData){
     $pk_libro = '';
      foreach ($RowData as $data){
          $pk_libro .= $data['pk_libro'].',';
      }
      $pk_libro = trim($pk_libro, ',');
      return $pk_libro;
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

  public function llenar_form($arreglo){
    
     $datakey = array_keys($arreglo);
     $pos = 0;
     foreach ($arreglo as $valor){
        $json[] = "$('#$datakey[$pos]').val('$valor')";
        $pos++;
     }
     return $json;
    
  }

  public function cpaisAction(){
       if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
              
           $dataRow = $this->agregar->getciudad($this->_getParam('pais')); 
           $this->SwapBytes_Ajax_Action->fillSelect($dataRow);
           
              }
      
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
  
  public function readonly($Status){
      
                            $this->SwapBytes_Form->readOnlyElement('cota'                   , $Status);
                            $this->SwapBytes_Form->readOnlyElement('titulo'                 , $Status);
                            $this->SwapBytes_Form->readOnlyElement('fk_editorial'           , $Status);
                            $this->SwapBytes_Form->readOnlyElement('fk_pais'                , $Status);
                            $this->SwapBytes_Form->readOnlyElement('fk_ciudad'              , $Status);
                            $this->SwapBytes_Form->readOnlyElement('fk_ano'                    , $Status);
                            $this->SwapBytes_Form->readOnlyElement('pagina'                 , $Status);
                            $this->SwapBytes_Form->readOnlyElement('nota'                   , $Status);
                            $this->SwapBytes_Form->readOnlyElement('ejemplar'               , $Status);
                            $this->SwapBytes_Form->readOnlyElement('coleccion'              , $Status);
                            $this->SwapBytes_Form->readOnlyElement('volumen'                , $Status);
                            $this->SwapBytes_Form->readOnlyElement('numero'                 , $Status);
      
  }
  
  public function buscar($rows,$search){
     
      if($search != ''){
          $i = 0;
          foreach ($rows as $row){
              $v1 = strpos(strtolower($row['cota'])     , strtolower($search));
              $v2 = strpos(strtolower($row['titulo'])   , strtolower($search));
              $v3 = strpos(strtolower($row['editorial']), strtolower($search));
              $v4 = strpos(strtolower($row['autor_principal']), strtolower($search));
              $v5 = strpos(strtolower($row['autor_otro']), strtolower($search));
              if(is_numeric($v1) || is_numeric($v2) || is_numeric($v3) || is_numeric($v4) || is_numeric($v5)){
                    $rows[$i]['find']= true; 
                    
              }else{
                  unset($rows[$i]);
              }
                
               $i = $i + 1;
          }
          
 
    }
    return $rows;
  }
   
  public function buscarAction(){
         if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();
         
          $queryString = $this->_getParam('data');
          $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);
          $cantidad_autores   = $this->cont_element($queryArray, 'fk_autor');
          $cantidad_materia   = $this->cont_element($queryArray, 'fk_materia');
          
          $pos_materia = 8 + ($cantidad_autores)*3;
          $pos_autor = 8;
          
          $this->crearCampo($cantidad_autores -1 , 'autor', $pos_autor); // creamos el autor
          $this->crearCampo($cantidad_materia -1, 'materia', $pos_materia); // re creamos las materias
          
          $queryArray['buscar_hide'] = $queryArray['buscar_hide'].'~'. $queryArray['buscar'];
          $buscar = explode("~", $queryArray['buscar_hide']);
          
          for($i = -1 ; $i < $cantidad_autores - 1 ;$i++){
              if($i == -1){
                   foreach ($buscar as $b){
                  $this->SwapBytes_Form->fillSelectBox('fk_autor',  $this->agregar->getAutores($b)  , 'pk_atributo', 'autor'); 
                   }
              }else{
                   foreach ($buscar as $b){
                  $this->SwapBytes_Form->fillSelectBox('fk_autor'.$i,  $this->agregar->getAutores($b)  , 'pk_atributo', 'autor'); 
                   }
              }
              
          }
          
          
          for($i = -1 ; $i < $cantidad_materia - 1 ;$i++){
              if($i == -1){
                   foreach ($buscar as $b){
                  $this->SwapBytes_Form->fillSelectBox('fk_materia',  $this->agregar->getMateria($b)  , 'pk_atributo', 'materia'); // llenado de las materia
                   }
              }else{
                   foreach ($buscar as $b){
                  $this->SwapBytes_Form->fillSelectBox('fk_materia'.$i,  $this->agregar->getMateria($b)  , 'pk_atributo', 'materia'); // llenado de las materia
                   }
              }
              
          }
          
          $queryArray['buscar'] = '';
          $this->addoreditloadAction($queryArray);
                                   
         
     }

    }
    
  public function recrear_listbox($buscar,$tipo,$cantidad){
         if ($this->_request->isXmlHttpRequest()) {
             $this->SwapBytes_Ajax->setHeader();
             if($tipo == "autor"){
                 for($i = -1; $i < $cantidad-1 ;$i++){
                     if($i == -1){// primer autor
                      $this->SwapBytes_Form->fillSelectBox('fk_autor',  $this->agregar->getAutores()  , 'pk_atributo', 'autor');     
                       foreach ($buscar as $valor){ 
                           if($valor != ""){
                           $this->SwapBytes_Form->fillSelectBox('fk_autor',  $this->agregar->getAutores($valor{0})  , 'pk_atributo', 'autor'); 
                           }
                       }
                     }else{ // los otros autores
                         $this->SwapBytes_Form->fillSelectBox('fk_autor'.$i,  $this->agregar->getAutores()  , 'pk_atributo', 'autor'); 
                        foreach ($buscar as $valor){ 
                           if($valor != ""){
                           $this->SwapBytes_Form->fillSelectBox('fk_autor'.$i,  $this->agregar->getAutores($valor{0})  , 'pk_atributo', 'autor'); 
                           }
                        }
                    }
                 }
                 
             }else{
               /*for($i = -1; $i < $cantidad-1 ;$i++){
                     if($i == -1){
                         $this->SwapBytes_Form->fillSelectBox('fk_materia',  $this->agregar->getMateria()  , 'pk_atributo', 'materia'); 
                        foreach ($buscar as $valor){ 
                           if($valor != ""){
                           $this->SwapBytes_Form->fillSelectBox('fk_materia',  $this->agregar->getMateria($valor{0})  , 'pk_atributo', 'materia'); 
                           }
                        }
                     }else{
                          $this->SwapBytes_Form->fillSelectBox('fk_materia'.$i,  $this->agregar->getMateria($valor)  , 'pk_atributo', 'materia'); 
                        foreach ($buscar as $valor){ 
                           if($valor != ""){
                           $this->SwapBytes_Form->fillSelectBox('fk_materia'.$i,  $this->agregar->getMateria($valor{0})  , 'pk_atributo', 'materia'); 
                           }
                        }
                    }
                 } */ 
             }
          
         }
        
    }

  public function listbox($arreglo,$buscar){
      
      
      $cantidad_autores = $this->cont_element($arreglo, 'fk_principal');
      $cantidad_materia = $this->cont_element($arreglo, 'fk_materia');
      for ($i = -1 ; $i <$cantidad_autores -1 ;$i++){
          if($i == -1){
          $this->view->form->fk_autor->addMultiOption($arreglo['fk_autor'],$arreglo['fk_autor']);
          }else{
          $this->view->form->fk_autor->addMultiOption($arreglo['fk_autor'.$i],$arreglo['fk_autor'.$i]);    
          }
        
        }
        
     for ($i = -1 ; $i <$cantidad_materia -1 ;$i++){
          if($i == -1){
          $this->view->form->fk_materia->addMultiOption($arreglo['fk_materia'],$arreglo['fk_materia']);
          }else{
          $this->view->form->fk_materia->addMultiOption($arreglo['fk_materia'.$i],$arreglo['fk_materia'.$i]);    
          }
        
        }  
      
       
  }
  
  public function autoautorAction(){
      if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $id  = $this->_getParam('id');
      $data = $this->_getParam('data');
      $queryArray  = $this->SwapBytes_Uri->queryToArray($data);
      $autor = 'fk_autor'.$id;
      $db = $this->agregar->getAutores($queryArray[$autor]);
      $valores = "";
      foreach ($db as $valor){
          $valores .= "'".$valor['autor']."',";
      }
      $json[]= " $('#$autor').autocomplete({source:[$valores]});";
      $this->getResponse()->setBody(Zend_Json::encode($json));
         }
      
      
  }
  
  public function autoeditorialAction(){
      if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $data = $this->_getParam('data');
      $queryArray  = $this->SwapBytes_Uri->queryToArray($data);
      $editorial = 'fk_editorial';
      $db = $this->agregar->getEditorial($queryArray[$editorial]);
      $valores = "";
      foreach ($db as $valor){
          $valores .= "'".$valor['editorial']."',";
      }
      $json[]= " $('#$editorial').autocomplete({source:[$valores]});";
      $this->getResponse()->setBody(Zend_Json::encode($json));
         }
      
      
  }

  public function automateriaAction(){
      if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $id  = $this->_getParam('id');
      $data = $this->_getParam('data');
      $queryArray  = $this->SwapBytes_Uri->queryToArray($data);
      $materia = 'fk_materia'.$id;
      $db = $this->agregar->getMateria($queryArray[$materia]);
      $valores = "";
      foreach ($db as $valor){
          $valores .= "'".$valor['materia']."',";
      }
      $json[]= " $('#$materia').autocomplete({source:[$valores]});";
      $this->getResponse()->setBody(Zend_Json::encode($json));
         }
      
      
  }


  public function autopaisAction(){
      if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      // $id  = $this->_getParam('id');
      $data = $this->_getParam('data');
      $queryArray  = $this->SwapBytes_Uri->queryToArray($data);
      $pais = 'fk_pais';//.$id;
      $db = $this->agregar->getpais($queryArray[$pais]);
      $valores = "";
      foreach ($db as $valor){
          $valores .= "'".$valor['pais']."',";
      }
      $json[]= " $('#fk_pais').autocomplete({source:[$valores]});";
      $this->getResponse()->setBody(Zend_Json::encode($json));
         }
      
      
  }


  public function autociudadAction(){
      if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      // $id  = $this->_getParam('id');
      $data = $this->_getParam('data');

      
      $queryArray  = $this->SwapBytes_Uri->queryToArray($data);

      $pais = $this->agregar->getbypais($queryArray['fk_pais']);
      
      $db = $this->agregar->getciudad($pais[0]['pk_atributo']);
      $valores = "";
      foreach ($db as $valor){
          $valores .= "'".$valor['ciudad']."',";
      }
      $json[]= " $('#fk_ciudad').autocomplete({source:[$valores]});";
      $this->getResponse()->setBody(Zend_Json::encode($json));
         }
      
      
  }  


  
  public function copyAction(){
      if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $Params = $this->_params['modal'];
      $this->copiar->array = array();
      $sede = "";
      $i = 0;
      $json =array();
      
      $arreglo = array();
	if(isset($Params['chkLibros'])) {
            if(is_array($Params['chkLibros'])){
               $sedex = $this->agregar->getSede($Params['chkLibros'][0]);
                $sede = $sedex[0]['fk_sede'];
                array_push($arreglo, $sede);
                  foreach($Params['chkLibros'] as $libros) {
                    array_push($arreglo, $libros);
                    $i = $i +1;
		}
                $mensaje = 'cantidad de libros copiados '.$i;
                $json[] = '$("#mensaje").html("'.$mensaje.'")';
                $json[] = ' $("#mensaje").show()';
            }else{
               $sedex = $this->agregar->getSede($Params['chkLibros']);
               $sede = $sedex[0]['fk_sede'];
               array_push($arreglo, $sede);
               array_push($arreglo, $Params['chkLibros']);
               $mensaje = 'cantidad de libros copiados 1';
               $json[] = '$("#mensaje").html("'.$mensaje.'")';
               $json[] = ' $("#mensaje").show()';
            }
             array_push($this->copiar->array,$arreglo);
             
        }         
       $this->getResponse()->setBody(Zend_Json::encode($json));
    }
  
  }
  
  public function pasteAction(){
      if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $cant = 0;
      $libro = "";
      $pk_libro = "";
      $autor = "";
      $materia = "";
      if($this->copiar->array[0]!=NULL){
          if($this->copiar->array[0][0] != $this->_params['filters']['Sede']){
              $cant = count($this->copiar->array[0]);
              for( $i = 1 ; $i<$cant ; $i++){
                $libro  = $this->agregar->getRowLibro($this->copiar->array[0][$i]);
                
                //validar si la cota del libro ya existe
                
                
                $autor   =$this->agregar->getRowAutor($this->copiar->array[0][$i]);
                $materia =$this->agregar->getRowmateria($this->copiar->array[0][$i]);
                
                $this->agregar->InsertLibro($libro[0]['cota'],$libro[0]['titulo'],$libro[0]['edicion'],$libro[0]['fk_pais'],
                                            $libro[0]['fk_ciudad'],$libro[0]['fk_editorial'],$libro[0]['ano'],$libro[0]['pagina'],
                                            $libro[0]['volumen'], 1,$libro[0]['nota'],$libro[0]['coleccion'],$libro[0]['numero'],
                                            $this->_params['filters']['Sede']);
                $pk_libro = $this->agregar->getPk_libro();
                foreach ($autor as $newautor){
                 $this->agregar->InsertAutores($pk_libro[0]['pk_libro'],$newautor['fk_autor'],$newautor['principal']);
                }
               
                foreach ($materia as $newmateria){
                 $this->agregar->InsertMateria($pk_libro[0]['pk_libro'],$newmateria['fk_materiabiblioteca']);
                }
                
              }
            $this->copiar->array[0] = NULL;    
          }
      }
      $this->listAction();

  }
  }

  public function guardarautorAction(){
       if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
           $queryString = $this->_getParam('data');
           $id  = $this->_getParam('id');
           $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);

           if(!empty($queryArray['fk_autor']) && $queryArray['fk_autor'] != " "){
               $fk_autor = $this->agregar->getAutor($queryArray['fk_autor'.$id]);
               if($fk_autor[0]['pk_atributo']==""){
               $json2  =  "$.getJSON('agregarlibros/agregarautor/data/";
               $json2 .=   $queryArray['fk_autor'.$id];
               $json2 .="' ,function(data){executeCmdsFromJSON(data)}); ";
               
               $json[] = "var valor =false; if('{$queryArray['fk_autor'.$id]}' == $('#fk_autor$id').val()) valor = confirm('Desea agregar el autor {$queryArray['fk_autor'.$id]}');";
               $json[] = " if(valor){if('{$queryArray['fk_autor'.$id]}' == $('#fk_autor$id').val()){$json2}}";
               
               }

           }

           $this->getResponse()->setBody(Zend_Json::encode($json));
      }
  }
  
  public function guardareditorialAction(){
       if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
           $queryString = $this->_getParam('data');
           $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);


           if(!empty($queryArray['fk_editorial']) && $queryArray['fk_editorial'] != " "){
              $fk_editorial = $this->agregar->getEditoriales($queryArray['fk_editorial']);
               if($fk_editorial[0]['pk_atributo']==""){
               $json2  =  "$.getJSON('agregarlibros/agregareditorial/data/";
               $json2 .=   $queryArray['fk_editorial'];
               $json2 .="' ,function(data){executeCmdsFromJSON(data)}); ";
               
               $json[] = "var valor =false; if('{$queryArray['fk_editorial']}' == $('#fk_editorial').val()) valor = confirm('Desea agregar la editorial {$queryArray['fk_editorial']}');";
               $json[] = " if(valor){if('{$queryArray['fk_editorial']}' == $('#fk_editorial').val()){$json2}}";
               
               }

           }

           
           $this->getResponse()->setBody(Zend_Json::encode($json));


      }
  }
  
  public function guardarmateriaAction(){
       if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
           $queryString = $this->_getParam('data');
           $id  = $this->_getParam('id');
           $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);

           if(!empty($queryArray['fk_materia']) && $queryArray['fk_materia'] != " "){
               $fk_materia = $this->agregar->getMaterias($queryArray['fk_materia'.$id]);
               if($fk_materia[0]['pk_atributo']==""){
               $json2  =  "$.getJSON('agregarlibros/agregarmateria/data/";
               $json2 .=   $queryArray['fk_materia'.$id];
               $json2 .="' ,function(data){executeCmdsFromJSON(data)}); ";
               
               $json[] = "var valor =false; if('{$queryArray['fk_materia'.$id]}' == $('#fk_materia$id').val()) valor = confirm('Desea agregar la materia {$queryArray['fk_materia'.$id]}');";
               $json[] = " if(valor){if('{$queryArray['fk_materia'.$id]}' == $('#fk_materia$id').val()){$json2}}";
               
               }

           }

           $this->getResponse()->setBody(Zend_Json::encode($json));


      }
  }



  public function guardarpaisAction(){
       if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
           $queryString = $this->_getParam('data');
           // $id  = $this->_getParam('id');
           $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);

           if(!empty($queryArray['fk_pais']) && $queryArray['fk_pais'] != " "){
               $fk_pais= $this->agregar->getbypais($queryArray['fk_pais']);
               if($fk_pais[0]['pk_atributo']==""){
               $json2  =  "$.getJSON('agregarlibros/agregarpais/data/";
               $json2 .=   $queryArray['fk_pais'];
               $json2 .="' ,function(data){executeCmdsFromJSON(data)}); ";
               
               $json[] = "var valor =false; if('{$queryArray['fk_pais']}' == $('#fk_pais').val()) valor = confirm('Desea agregar el pais {$queryArray['fk_pais']}');";
               $json[] = " if(valor){if('{$queryArray['fk_pais']}' == $('#fk_pais').val()){$json2}}";
               
               }

           }

           $this->getResponse()->setBody(Zend_Json::encode($json));


      }
  }


  public function guardarciudadAction(){
       if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
           $queryString = $this->_getParam('data');
           // $id  = $this->_getParam('id');
           $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);

           if(!empty($queryArray['fk_ciudad']) && $queryArray['fk_ciudad'] != " "){

               $pais = $this->agregar->getbypais($queryArray['fk_pais']);

               $fk_ciudad = $this->agregar->getbyciudad($pais[0]['pk_atributo'],$queryArray['fk_ciudad']);


               if($fk_ciudad[0]['pk_atributo']==""){//cuando no encuentra la ciudad dentro de un pais dado

                  $ciudadotropais = $this->agregar->getciudadbynombre($queryArray['fk_ciudad']);

                  if ($ciudadotropais[0]['pk_atributo']!="") {

                    $mensaje = "Desea agregar el ciudad?. Existe similitud con:  " . $ciudadotropais[0]['pais'] . ", ";

                  }else{

                     $mensaje = " Desea agregar el ciudad?  ";
                  }


                   $json2  =  "$.getJSON('agregarlibros/agregarciudad/pais/";
                   $json2 .=   $pais[0]['pk_atributo'];
                   $json2 .= "/ciudad/";
                   $json2 .=  $queryArray['fk_ciudad'];
                   $json2 .="' ,function(data){executeCmdsFromJSON(data)}); ";
                   
                   $json[] = "var valor =false; if('{$queryArray['fk_ciudad']}' == $('#fk_ciudad').val()) valor = confirm('".$mensaje." {$queryArray['fk_ciudad']}');";
                   $json[] = " if(valor){if('{$queryArray['fk_ciudad']}' == $('#fk_ciudad').val()){$json2}}";
               
               }

           }

           $this->getResponse()->setBody(Zend_Json::encode($json));


      }
  }  

 
  public function agregarautorAction(){
        if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
           $queryString = $this->_getParam('data');
           $pk = $this->agregar->get_pkAtributo();
           $this->agregar->InsertnewAutores($pk[0]['pk_atributo'],$queryString);
           
           
        }
  }
  
  public function agregareditorialAction(){
        if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
           $queryString = $this->_getParam('data');
           $pk = $this->agregar->get_pkAtributo();
           $this->agregar->InsertnewEditorial($pk[0]['pk_atributo'],$queryString);
           
           
        }
  }
  
  public function agregarmateriaAction(){
        if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
           $queryString = $this->_getParam('data');
           $pk = $this->agregar->get_pkAtributo();
           $this->agregar->InsertnewMateria($pk[0]['pk_atributo'],$queryString);
           
           
        }
  }


  public function agregarpaisAction(){
        if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
           $queryString = $this->_getParam('data');
           $pk = $this->agregar->get_pkAtributo();
           $this->agregar->InsertnewPais($pk[0]['pk_atributo'],$queryString);
           
           
        }
  }


  public function agregarciudadAction(){
        if ($this->_request->isXmlHttpRequest()) {
           $this->SwapBytes_Ajax->setHeader();
           $pais = $this->_getParam('pais');
           $ciudad = $this->_getParam('ciudad');
           
           $pk = $this->agregar->get_pkAtributo();
           $this->agregar->Insertnewciudad($pk[0]['pk_atributo'],$ciudad,$pais);
           
           
        }
  }  

  
  public function get_existe($array , $valor){
      $modulo = "";
      if($valor == 'editorial'){
          $modulo =  'getEditoriales';
          $message = "<b>la editorial ";
      }
      // elseif($valor=='autor'){
      //     $modulo =  'getAutor';
      //     $message = "<b>el autor </b>";
      // }else if($valor == 'materia'){
      //     $modulo =  'getMaterias';
      //      $message = "<b>la materia </b>";
      // }
      
      for($i=0 ; $i<count($array);$i++){
          // if($valor != 'editorial'){
          //   $fk=$this->agregar->$modulo($array[$i]);
          //    if($fk[0]['pk_atributo'] == ""){
          //    $message .= "$array[$i] no fue guardado </b>";       
          //    $this->SwapBytes_Crud_Form->getDialog('No se puede Guardar ', $message);
          //    return false;
          //   }
          // }

        if($valor == 'editorial'){
            $fk=$this->agregar->$modulo($array);  
            if($fk[0]['pk_atributo'] == ""){
             $message .= "$array no fue guardada </b>";   
             $this->SwapBytes_Crud_Form->getDialog('No se puede Guardar ', $message);
             return false;
            }
          }
        
      }
      return true;
       
  }
}
 
