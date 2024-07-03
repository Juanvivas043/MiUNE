<?php

class Transactions_PrestamoartController extends Zend_Controller_Action {


    private $Title = 'Prestamo Articulo';
    
    private $Prestamoval   = 8242;  //8223; omicron  // local   8242
    private $Moraval       = 8244;  //8224; omicron  // local   8244
    private $Devueltoval   = 8243;  //8225; omicron  // local   8243
    private $local =  false;         // omicron false // local = true
   
    public function init() {
         
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
       // Zend_Loader::loadClass('Forms_Estudiantes');
        
        Zend_Loader::loadClass('Models_DbTable_Prestamo');
        Zend_Loader::loadClass('Models_DbTable_BibliotecaAgregar');
        Zend_Loader::loadClass('Forms_Prestamoart');
        Zend_Loader::loadClass('Models_DbTable_Prestamoart');
        $this->prestamo        = new Models_DbTable_Prestamo();
        $this->prestamoart     = new Models_DbTable_Prestamoart();
        $this->agregar       = new Models_DbTable_BibliotecaAgregar();
        $this->estudiante      = new Models_DbTable_Usuarios();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->recordacademico = new Models_DbTable_Recordsacademicos();
		$this->filtros         = new Une_Filtros();

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
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        
        $this->aux = Array();
        //Filtros//

        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);
	

        //Botones//

        $this->SwapBytes_Crud_Action->setDisplay(true, true, true, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, true, true, false, false, false);
        
        $Regreso = "<button id='btnReturn' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnReturn' role='button' aria-disabled='false'>Regreso";
        $this->SwapBytes_Crud_Action->addCustum($Regreso);
        
        //Formulario//

        $this->view->form = new Forms_Prestamoart();

        $this->SwapBytes_Form->set($this->view->form);
        

        $this->view->form = $this->SwapBytes_Form->get();

    
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->filtros->getParams();
        $this->CmcBytes_Redirector2          = new CmcBytes_Redirect();
        $this->_params['redirect'] = $this->redirect_session->params;
        // $this->redirect_session->unsetAll();
        
        $this->view->form->tipo_prestamo->addMultiOption(1,Externo);
        $this->view->form->tipo_prestamo->addMultiOption(2,Interno);
       
        $this->SwapBytes_Form->fillSelectBox('fk_tipo_interno',  $this->prestamoart->getprestamointerno()  , 'pk_atributo', 'interno');
        }

    function preDispatch() {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
                #$this->_helper->redirector('index', 'login', 'default');
            }

            if (!$this->grupo->haveAccessToModule()) {
                #$this->_helper->redirector('accesserror', 'profile', 'default');
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
    
    public function existsAction() {
        
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
           
            $json        = array();
            $existe      = false;
            // $hide = false;
            $html        = '';
            $queryString = $this->_getParam('data');
            $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);
            $cota = $queryArray['cota'];
            $sede_prestamo = $this->_params['redirect']['sede'];

             if(!empty($cota)) {
                $libro  = $this->prestamoart->get_libro($cota);
                
                if($libro[0]['cota'] != ''){ // es libro

                  foreach ($libro as $value) {
                    if($value['fk_sede'] != $sede_prestamo){//intentas pedir un libro de una sede distinta
                      $sede = $this->agregar->getsedeform($value['fk_sede']);
                      $html   = $this->SwapBytes_Html_Message->alert("Este libro/tesis pertenece a la sede: ".$sede[0]['sede'] );
                      $hide = true;

                    }else{
                      $html = "";
                      $hide = false;
                      break;
                    }
                  }
                  
                $existe = true;
                $autor  = $this->agregar->get_autor($libro[0]['pk_libro'],'t');
                $fechas = $this->prestamo->getUsuarioinfo($this->session->id);
                $dataRow['fecha_solicitud'] = $this->SwapBytes_Date->convertToForm($fechas[0]['fecha_solicitud']);
                $dataRow['fk_libro'] = $libro[0]['titulo'];
                $dataRow['cota'] = $libro[0]['cota'];
                $dataRow['pagina'] = $libro[0]['pagina'];
                $dataRow['fk_autor'] = $autor[0]['autor'];
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                }else{ 
                 $tesis  = $this->prestamoart->get_tesis($cota); 
                 

                 foreach ($tesis as $value) {
                    if($value['fk_sede'] != $sede_prestamo){//intentas pedir un libro de una sede distinta
                      $sede = $this->agregar->getsedeform($value['fk_sede']);
                      $html   = $this->SwapBytes_Html_Message->alert("Este libro/tesis pertenece a la sede: ".$sede[0]['sede'] );
                      $hide = true;
                    }else{
                      $html = "";
                      $hide = false;
                      break;
                    }
                  }

                 if($tesis[0]['cota'] != ''){ // es tesis
                     $existe = true;
                     $autor  = $this->agregar->get_autor_tesis($tesis[0]['pk_tesis']);
                     
                     $fechas = $this->prestamo->getUsuarioinfo($this->session->id);
                     $dataRow['fecha_solicitud'] = $this->SwapBytes_Date->convertToForm($fechas[0]['fecha_solicitud']);
                     $dataRow['fk_libro'] = $tesis[0]['titulo'];
                     $dataRow['cota'] = $tesis[0]['cota'];
                     $dataRow['pagina'] = $tesis[0]['pagina'];
                     $dataRow['fk_autor'] = $autor[0]['autor'];
                     $dataRow['tipo_prestamo']  = 2;
                     $dataRow['fecha_estimada']  = $this->SwapBytes_Date->convertToForm($fechas[0]['fecha_solicitud']);
                     $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                     
                 }
                }
                
                if(!$existe){
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                $dataRow['cota'] = $cota;
                }
                
                
                $this->SwapBytes_Form->set($this->view->form); //setamos el formulario
                
               if(isset($dataRow)) {
                   	
                    $this->view->form->populate($dataRow);
                } 
                $this->SwapBytes_Form->readOnlyElement('fecha_solicitud', true);
                $this->SwapBytes_Form->readOnlyElement('pagina', true);
                $this->SwapBytes_Form->readOnlyElement('fk_autor', true);
                $this->SwapBytes_Form->readOnlyElement('fk_libro', true);
                 $this->view->form = $this->SwapBytes_Form->get();

                // Preparamos el frmModal para ser enviado por AJAX.
                $html  .= $this->SwapBytes_Ajax->render($this->view->form);
                
                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
               
                $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fecha_estimada');
                $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fecha_devolucion');
                
             }

             if($hide == true){
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
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
            $itemPerPage = 3;
            $pageRange   = 3;

            // Definimos los valores
            //$this->estudiante->setSearch($searchData);
            $this->prestamoart->setSearch($searchData);
            $paginatorCount = $this->prestamoart->getSQLCount();
            //$rows           = $this->estudiante->getEstudiantes($itemPerPage, $pageNumber); // falta
            $rows           = $this->prestamoart->getListArticuloprestamo($this->session->id);
            
            // Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                           'width' => '1000px');

            $columns = array(array('name'    => 'Cota / Titulo',
                                   'width'   => '100px',
                                   'column'  => 'cota'),

                            array('name'  => 'Comentario',
                                   'column'  => 'comentario',
                                   'width'   => '150px' ),
                                
                            array('name'    => 'Fecha Solicitud',
                                   'width'   => '100px',
                                   'column'  => 'fecha_solicitud'),
                
                            array('name'    => 'Fecha Estimada',
                                   'width'   => '100px',
                                   'column'  => 'fecha_devolucion'),
                                   
                            array('name'    => 'Fecha real devolucion',
                                   'width'   => '100px',
                                   'column'  => 'fecha_entrega'),
                
                             array('name'    => 'pk_prestamoarticulo',
                                   'width'   => '100px',
                                   'column'  => 'pk_prestamoarticulo',
                                   'primary' => true,
                                    'hide'   => true),

                             array('name'    => 'Hora Registro',
                                   'width'   => '150px',
                                   'column'  => 'hora'),

                             array('name'    => 'Hora Retorno',
                                   'width'   => '150px',
                                   'column'  => 'hora_retorno'),

                             array('name'    => 'Operador Registro',
                                   'width'   => '200px',
                                   'column'  => 'usuario_registro'),

                             array('name'    => 'Operador Retorno',
                                   'width'   => '200px',
                                   'column'  => 'usuario_retorno'),
                
                
                             array('name'    => 'Estado',
                                   'width'   => '100px',
                                   'column'  => 'estado')

                            
                
                
                );
             
           
             $other = array(
                      array('actionName'=> 'retornar',
                            'action'    =>  'myretornar(##pk##)',
                            'label'     => 'Retornar',
                            'column'    => 'acciones', 
                            'validate' => 'true',
                            'intrue' => '',
                            'intruelabel' => ''
                           ),
                array('actionName' => '',
                            'action'     => 'myprestar(##pk##)'  ,
                            'label'      => 'Entregar',
                            'column'    => 'estado',
                            'validate' => 'true',
                            'intrue' => 'Espera',
                            'intruelabel' => '')
                 
                 );
          
            $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VDUO', $other);
            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            
             
            if( (int)$this->validarcantidad() >= 3 || !$this->cerrar_ficha() ){
           
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnAdd', true);
                
            }else{
                 $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnAdd', false);
            } 
            
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function addoreditloadAction() {
	                     
        $fecha = date('m/d/Y');
        $fechas = $this->prestamo->getUsuarioinfo($this->session->id);
        $fecha_solicitud = $fechas[0]['fecha_solicitud'];
        

        
        if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {//edit
                        $tipo_prestamo = $this->prestamoart->gettipoprestamo($this->_params['modal']['id']);
                        $dataRow       = $this->prestamoart->getRow($this->_params['modal']['id']);
			$dataRow['id'] = $this->_params['modal']['id'];
                        $dataRow['fecha_solicitud'] = $this->SwapBytes_Date->convertToForm($fechas[0]['fecha_solicitud']);
                        $fecha_estimada  = $dataRow['fecha_devolucion'];
                        $dataRow['fecha_estimada'] = $this->SwapBytes_Date->convertToForm($fecha_estimada);
                        $fecha_entrega = $dataRow['fecha_entrega'];
                        $dataRow['fecha_devolucion'] = $this->SwapBytes_Date->convertToForm($fecha_entrega);
                        
                        $libro  = $this->prestamoart->get_libro($dataRow['cota']);
                        if($libro[0]['cota']!=""){ // es un libro
                            $autor  = $this->agregar->get_autor($libro[0]['pk_libro'],'t');
                            $dataRow['fk_libro'] = $libro[0]['titulo'];
                            $dataRow['cota'] = $libro[0]['cota'];
                            $dataRow['pagina'] = $libro[0]['pagina'];
                            $dataRow['fk_autor'] = $autor[0]['autor'];
                        }else{
                             $tesis  = $this->prestamoart->get_tesis($dataRow['cota']);   
                                if($tesis[0]['cota'] != ''){ // es tesis
                                $autor  = $this->agregar->get_autor_tesis($tesis[0]['pk_tesis']);
                                $fechas = $this->prestamo->getUsuarioinfo($this->session->id);
                                $dataRow['fecha_solicitud'] = $this->SwapBytes_Date->convertToForm($fechas[0]['fecha_solicitud']);
                                $dataRow['fk_libro'] = $tesis[0]['titulo'];
                                $dataRow['cota'] = $tesis[0]['cota'];
                                $dataRow['pagina'] = $tesis[0]['pagina'];
                                $dataRow['fk_autor'] = $autor[0]['autor'];
                                }
                        }
                        
                        if($tipo_prestamo[0]['fk_tipo_interno']==NULL){
                        $this->view->form->tipo_prestamo->addMultiOption(1,Externo);
                        $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('fk_tipo_interno-label',
                                                                    'fk_tipo_interno-element'));
       
        
                        }else{
                        $this->view->form->tipo_prestamo->addMultiOption(1,Interno); 
                        }
                        
                        
		}else{//agregar
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('fk_tipo_interno-label',
                                                                    'fk_tipo_interno-element'));  
                $dataRow['fecha_hide'] = $fecha;
                $dataRow['fecha_solicitud'] = $this->SwapBytes_Date->convertToForm($fechas[0]['fecha_solicitud']);
                $dataRow['fecha_estimada'] = $this->SwapBytes_Date->convertToForm($fecha_estimada);
                
               if( $fechas[0]['grupo'] == "Docente" || $fechas[0]['grupo'] == "Administrativo"  )
                         {
                                $dataRow['fecha_estimada'] = $this->suma_fechas( $dataRow['fecha_solicitud'], 3);
              
                         }else{
                     
                                $dataRow['fecha_estimada'] = $this->suma_fechas( $dataRow['fecha_solicitud'], 2);  
                                
                         }
                
                                $dataRow['fecha_devolucion'] = $this->SwapBytes_Date->convertToForm($dataRow['fecha_devolucion']);
          
          }   
    
        $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fecha_estimada');
        $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fecha_devolucion');
        
        $json[] = $this->SwapBytes_Jquery->getJSON('list',
                            array('page' => $this->_request->getParam('page', 1)),
                            array('buscar' => $this->SwapBytes_Jquery->getValEncodedUri('txtBuscar'),
                                'filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros')));
        
        $this->SwapBytes_Form->readOnlyElement('fecha_solicitud', true);
        $this->SwapBytes_Form->readOnlyElement('pagina', true);
        $this->SwapBytes_Form->readOnlyElement('fk_autor', true);
        $this->SwapBytes_Form->readOnlyElement('fk_libro', true);
        
        $this->SwapBytes_Crud_Form->setJson($json);
        
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Agregar Articulo');
     
        
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();
       
    }

    public function addoreditconfirmAction() {
            
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            if($this->_params['modal']['id']!=0){ // update  
                $status  = false;
            }else{  // nuevo
                $status = true;
            }
               
            
		       $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fecha_devolucion');
                       $this->SwapBytes_Form->readOnlyElement('fecha_solicitud', true);

		       $this->SwapBytes_Crud_Form->setJson($json);
                       $validar = "valido"; 
                       $validar= $this->validarfecha($this->_params['modal']);
                       
                       //                true                                           true                  true
                        if( ($validar != "invalido"  && $this->cerrar_ficha() ) && (!$status || (int)$this->validarcantidad()<3) ){
                           
                           $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
			   $this->SwapBytes_Crud_Form->getAddOrEditConfirm(); 
                        }else{
                            if($validar == "invalido"){
                            $message = "<b>Debido que la fecha estimada es menor a la fecha solicitud</b>";
		            $this->SwapBytes_Crud_Form->getDialog('No se puede Guardar ', $message);
                            }
                            
                            if((int)$this->validarcantidad()>=3 ){ 
                            $message = "<b>Debido que tienen mas de 3 articulos </b>";
		            $this->SwapBytes_Crud_Form->getDialog('No se puede Guardar ', $message);
                            }
                        }

            	
		}
        
	}

    public function addoreditresponseAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $sede = $this->_params['redirect']['sede'];
            $updatefecha = "algo";
            $cedula = $this->authSpace->userId;
            $usuariogrupo = $this->prestamoart->getUsuariogrupo($cedula);


            $dataRow                  = $this->_params['modal'];
            $id                       = $dataRow['id'];

            $dataRow['fecha_entrega']     = $this->SwapBytes_Date->convertToDataBase($dataRow['fecha_devolucion']);
            $dataRow['fecha_devolucion']  = $this->SwapBytes_Date->convertToDataBase($dataRow['fecha_estimada']);
            $dataRow['id']                = null;
            $dataRow['fecha_hide']        = null;
            $dataRow['fecha_solicitud']   = null;
            $dataRow['fecha_estimada']    = null;     
            $dataRow['fk_libro']          = null;
            $dataRow['fk_autor']          = null;
            $dataRow['pagina']          = null;
            $dataRow['fk_prestamo']       = $this->session->id;

            if($id == 0 || $dataRow['fecha_entrega']==NULL){
                $dataRow['fk_asignacion'] = $this->Prestamoval;// en prestamo
                $updatefecha = NULL;
            }
            if($dataRow['fecha_entrega']!=NULL){
                $dataRow['fk_asignacion'] = $this->Devueltoval;//devuelto 
            }

            //valido el tipo de prestamo externo interno
            if($dataRow['tipo_prestamo']=='1'){
                $dataRow['fk_tipo_interno']  = null;
            }

            $dataRow['tipo_prestamo']     = null;
            $dataRow['fk_sede']  = $sede;

            //agregar datos de auditoria (hora, que usuario registro)
            $dataRow['hora'] = date("G:H:s a");
            $dataRow['usuario_registro'] = $usuariogrupo;

            

            if(is_numeric($id) && $id > 0) {   
                $this->prestamoart->updateRow($id, $dataRow);
                if($updatefecha==NULL){       
                    $this->prestamoart->miupdate($id);
                }
            } else{
                $this->prestamoart->addRow($dataRow); 
            }

            if((int)$this->validarcantidad()<3 &&  $this->cerrar_ficha()){
                $this->addoreditloadAction();  
            }else{
                $this->SwapBytes_Crud_Form->getAddOrEditEnd();
            }
        }
           
         
		
                
    }

    public function viewAction() {
        if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();
        $fechas = $this->prestamo->getUsuarioinfo($this->session->id);
        $fecha_solicitud = $fechas[0]['fecha_solicitud'];
        $tipo_prestamo = $this->prestamoart->gettipoprestamo($this->_params['modal']['id']);
        
        $dataRow          = $this->prestamoart->getRow($this->_params['modal']['id']);
        $libro  = $this->prestamoart->get_libro($dataRow['cota']);
        if($libro[0]['cota']!=""){ // es un libro
            $autor  = $this->agregar->get_autor($libro[0]['pk_libro'],'t');
            $dataRow['fk_libro'] = $libro[0]['titulo'];
            $dataRow['cota'] = $libro[0]['cota'];
            $dataRow['pagina'] = $libro[0]['pagina'];
            $dataRow['fk_autor'] = $autor[0]['autor'];
        }else{
                             $tesis  = $this->prestamoart->get_tesis($dataRow['cota']);   
                                if($tesis[0]['cota'] != ''){ // es tesis
                                $autor  = $this->agregar->get_autor_tesis($tesis[0]['pk_tesis']);
                                $fechas = $this->prestamo->getUsuarioinfo($this->session->id);
                                $dataRow['fecha_solicitud'] = $this->SwapBytes_Date->convertToForm($fechas[0]['fecha_solicitud']);
                                $dataRow['fk_libro'] = $tesis[0]['titulo'];
                                $dataRow['cota'] = $tesis[0]['cota'];
                                $dataRow['pagina'] = $tesis[0]['pagina'];
                                $dataRow['fk_autor'] = $autor[0]['autor'];
                                }
                        }
        
        $dataRow['fecha_estimada'] = $this->SwapBytes_Date->convertToForm($dataRow['fecha_devolucion']);
        $dataRow['fecha_devolucion'] = $this->SwapBytes_Date->convertToForm($dataRow['fecha_entrega']);
        $dataRow['fecha_solicitud'] = $this->SwapBytes_Date->convertToForm($fecha_solicitud);
        if($tipo_prestamo[0]['fk_tipo_interno']==NULL){
         $this->view->form->tipo_prestamo->addMultiOption(1,Externo);
         $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('fk_tipo_interno-label',
                                                                    'fk_tipo_interno-element'));
       
        }else{
         $this->view->form->tipo_prestamo->addMultiOption(1,Interno); 
         
        }
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver articulo');
        $this->SwapBytes_Crud_Form->getView();
       
    }
    }
    
    public function deleteloadAction() {
        $this->SwapBytes_Ajax->setHeader();
        
	$message = 'Seguro desea elmimnar esto?:';
	$permit = true;
        $params = $this->_params['modal'];

        $fechas = $this->prestamo->getUsuarioinfo($this->session->id);
        $tipo_prestamo = $this->prestamoart->gettipoprestamo($this->_params['modal']['id']);
        $fecha_solicitud = $fechas[0]['fecha_solicitud'];
        
        
        $dataRow          = $this->prestamoart->getRow($this->_params['modal']['id']);
        $dataRow['id'] = $this->_params['modal']['id'];
        $dataRow['fecha_estimada'] = $this->SwapBytes_Date->convertToForm($dataRow['fecha_devolucion']);
        $dataRow['fecha_devolucion'] = $this->SwapBytes_Date->convertToForm($dataRow['fecha_entrega']);
        $dataRow['fecha_solicitud'] = $this->SwapBytes_Date->convertToForm($fecha_solicitud);
       
         $libro  = $this->prestamoart->get_libro($dataRow['cota']);
                        if($libro[0]['cota']!=""){ // es un libro
                            $autor  = $this->agregar->get_autor($libro[0]['pk_libro'],'t');
                            $dataRow['fk_libro'] = $libro[0]['titulo'];
                            $dataRow['cota'] = $libro[0]['cota'];
                            $dataRow['pagina'] = $libro[0]['pagina'];
                            $dataRow['fk_autor'] = $autor[0]['autor'];
                        }else{
                             $tesis  = $this->prestamoart->get_tesis($dataRow['cota']);   
                                if($tesis[0]['cota'] != ''){ // es tesis
                                $autor  = $this->agregar->get_autor_tesis($tesis[0]['pk_tesis']);
                                $fechas = $this->prestamo->getUsuarioinfo($this->session->id);
                                $dataRow['fecha_solicitud'] = $this->SwapBytes_Date->convertToForm($fechas[0]['fecha_solicitud']);
                                $dataRow['fk_libro'] = $tesis[0]['titulo'];
                                $dataRow['cota'] = $tesis[0]['cota'];
                                $dataRow['pagina'] = $tesis[0]['pagina'];
                                $dataRow['fk_autor'] = $autor[0]['autor'];
                                }
                        }
      
        if($dataRow['fecha_entrega']!=""){
            $message = 'No se puede eliminar porque ya tiene una fecha en devolucion';
            $permit = false;
        }    
        
        if($tipo_prestamo[0]['fk_tipo_interno']==NULL){
         $this->view->form->tipo_prestamo->addMultiOption(1,Externo);
         $json[] = $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('fk_tipo_interno-label',
                                                                    'fk_tipo_interno-element'));
       
        
        }else{
         $this->view->form->tipo_prestamo->addMultiOption(1,Interno); 
         
        }
         
	$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Acividad', $message);
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->getDeleteLoad($permit,$msg);
        
  }

    public function deletefinishAction() {
          
        $params = $this->_params['modal'];
        
            $this->prestamoart->deleteRow($this->_params['modal']['id']);
        


	$this->SwapBytes_Crud_Form->setProperties($this->view->form); 
	$this->SwapBytes_Crud_Form->getDeleteFinish();


  }

    public function retornarAction() {
      if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();
        $pk = $this->_getParam('id', 0);
        $cedula = $this->authSpace->userId;
        $usuariogrupo = $this->prestamoart->getUsuariogrupo($cedula);
        $fecha = date("Y-m-d");
        $hora_retorno = date("G:H:s a");

        $this->prestamoart->retorno($pk,$fecha,$usuariogrupo,$hora_retorno);
        $this->listAction();
      }
    }
   
    public function prestarAction(){
         if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
        $pk = $this->_getParam('id', 0);
           
        $this->prestamoart->prestamo($pk);
        $this->listAction();
         }
    }
    
    private function validarfecha($data){
        
        $hide = explode ("/", $data['fecha_solicitud']); //0 dia 1 mes 2 año
        //var_dump($hide);
        $entrega = explode ("/", $data['fecha_estimada']); //0 dia 1 mes 2 año
        
        if($hide[2] > $entrega[2] ){ //año es mayor
         
         return "invalido";
        }
         if($hide[2] == $entrega[2] && $hide[1] > $entrega[1]){ //mes es mayor
            
         return "invalido";
        }
        if($hide[2] == $entrega[2] && $hide[1] == $entrega[1] &&  $hide[0] > $entrega[0]){ //dia mayor
            
         return "invalido";
        }
        
        return "valido";
    }
    
    public function informacionAction(){
         $this->SwapBytes_Ajax->setHeader();
         $usuario = $this->prestamo->getUsuarioinfo($this->session->id);
         $json[] = '$("#s_usuariotxt").html("'.$usuario[0]['pk_prestamo'].'")';
         $json[] = '$("#c_usuariotxt").html("'.$usuario[0]['ci'].'")';
         $json[] = '$("#n_usuariotxt").html("'.$usuario[0]['nombre'].'")';
         $json[] = '$("#a_usuariotxt").html("'.$usuario[0]['apellido'].'")';
         $json[] = '$("#g_usuariotxt").html("'.$usuario[0]['grupo'].'")';
         $this->getResponse()->setBody(Zend_Json::encode($json));
        
    }

    public function listarAction(){
        $this->listAction();
        
    }
    
    public function agregarAction(){
       // $this->SwapBytes_Ajax->setHeader();
        if((int)$this->validarcantidad()<3 &&  $this->cerrar_ficha()){

        $this->addoreditloadAction();
        }
    }

    public function regresoAction(){
        
         if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
       
        //$fecha = date("d/m/Y");// posible cambio
        $fecha = date("m/d/Y");
        $this->prestamo->updateEstado($fecha); 
        $this->prestamo->getListUsuarioprestamo(NULL);
       
     
      $data = array( 'module'=>'transactions',
                     'controller'=>'prestamo',
                     'params'=>array('id' => 1,
                                      'set' => 'true'));
        $this->session->unsetAll();
        
        $json[] = $this->CmcBytes_Redirector2->getRedirect($data);
        $this->getResponse()->setBody(Zend_Json::encode($json));
        }
       
    }

    private function suma_fechas($fecha,$ndias){
            

      if (preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/([0-9][0-9]){1,2}/",$fecha))
            

              list($dia,$mes,$año)=  explode("/", $fecha);
              //list($mes,$dia,$año)=split("/", $fecha);

      if (preg_match("/[0-9]{1,2}-[0-9]{1,2}-([0-9][0-9]){1,2}/",$fecha))
            

              list($dia,$mes,$año)=explode("-",$fecha);
        $nueva = mktime(0,0,0, $mes,$dia,$año) + $ndias * 24 * 60 * 60;
          //        list($mes,$dia,$año)=split("-",$fecha);
        //$nueva = mktime(0,0,0, $dia,$mes,$año) + $ndias * 24 * 60 * 60;
        $nuevafecha=date("d/m/Y",$nueva);
          //$nuevafecha=date("m/d/Y",$nueva); 

      return ($nuevafecha);  
            

}

    private function validarcantidad(){
        $row=$this->prestamoart->getCountarticulo($this->session->id);
        return $row;
    }
    
    private function cerrar_ficha(){
        $this->SwapBytes_Ajax->setHeader();
        $fecha = date("Y-m-d");
        $usuario = $this->prestamo->getUsuarioinfo($this->session->id);
        if ($fecha == $usuario[0]['fecha_solicitud'])
        {
            return TRUE;
        }else{
            return TRUE; // cambie
        }
    }
}

?>
