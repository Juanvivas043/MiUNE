<?php
/**
 * @todo Ocultar el boton "Ocultar" del formulario Ver.
 * @todo Ocultar el boton "Eliminar" del formulario Eliminar, solo cuando no se
 *       permita.
 */
class Transactions_PrestamoController extends Zend_Controller_Action {

    private $Title                = 'Prestamos Bibloteca';
    private $FormTitle_Agregar    = 'Agregar nuevo Prestamo';
    
    private $Prestamoval   = 8242;//8223;
    private $Moraval       = 8244;//8224;
    private $Devueltoval   = 8243;//8225;
    
   
    
    private $FormAlert_Agregar    = 'Usuario no permitido';
    private $FormAlert_Solvente   = 'No esta Solvente administrativamente ';
   // private $FormAlert_Supension  = 'Esta suspendido por :';
    private $FormAlert_Estado     = 'Tiene una ficha aun no solvente';

    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        //Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        //Zend_Loader::loadClass('Forms_Estudiantes');
        
        Zend_Loader::loadClass('Models_DbTable_Prestamo');//base de datos
        Zend_Loader::loadClass('Forms_Prestamo');
        
        $this->prestamo        = new Models_DbTable_Prestamo();
        
        $this->usuario      = new Models_DbTable_Usuarios();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->periodo = new Models_DbTable_Periodos();
        //$this->recordacademico = new Models_DbTable_Recordsacademicos();
		$this->filtros         = new Une_Filtros();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();
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
       // $this->CmcBytes_profit          = new CmcBytes_Profit();        
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->session = new Zend_Session_Namespace('session');
        $this->grupo_id = new Zend_Session_Namespace('grupo_id');
        $this->val = new Zend_Session_Namespace('val');
        $this->CmcBytes_Redirector          = new CmcBytes_Redirect();
        
        
        
         // Mandamos a crear el formulario para ser utilizado mediante el AJAX.
         
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->view->form = new Forms_Prestamo();
        $this->SwapBytes_Form->set($this->view->form);

         $this->tablas = Array(
                                  
                              'Sede'    => Array('vw_sedes',
                                                 null     ,
                                                 Array('pk_estructura',
                                                       'nombre'),
                                                 'DESC'),
        );
            

    $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters')); 





        /*
         * Configuramos los botones.
         */
$this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);        
	$this->SwapBytes_Crud_Action->setDisplay(true, true, true);
	$this->SwapBytes_Crud_Action->setEnable(true, true, true);
        //nuevo..
        $this->SwapBytes_Crud_Action->addCustum('&nbsp;<select id="selEstado" name="selEstado" style="width:150px">
                                                <option value = 1 select>Mora , Transito y Vacio </option>
                                                <option value = 2 select>Solvente</option>
                                                <option value = 3 select>Todos</option>
                                                </select>');
        

         
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
    
  public function estadoAction(){
         if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $val = $this->_getParam('val');
            $sed = $this->_getParam('sede');
            $this->session->val = $val;
            $this->listAction($sed);
            }
    }

  public function listAction() { //Tenia antes el parametro $sed
       
    // Verificamos si es una llamada de tipo AJAX.
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $val = $this->session->val;
           // $pageNumber  = $this->_getParam('page', 1);
            $searchData  = $this->_getParam('buscar');
            $sede = $this->_params['filters']['Sede'];

            /*
            if(empty($sede)){       COMENTADO PARA LA ACTUALIZACION A PHP 7.2 junto con su parametro
                $sede = $sed;
            }
            */
            
	    $this->update_estadoArt();
            
           // $itemPerPage = 15;
           // $pageRange   = 10;
           
            // Definimos los valores
            $this->prestamo->setSearch($searchData);

            //$paginatorCount = $this->prestamo->getSQLCount();
            $rows           = $this->prestamo->getListUsuarioprestamo($val,$sede);
            
            //var_dump($rows);
            //$rows           = $this->transformRows($rows);
            $this->val->unsetAll();
           
            
            
            $table = array('class' => 'tableData',
                           'width' => '800px');

            $columns = array(array('name'  => 'Solicitud',
                                   'column'  => 'solicitud',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'width'   => '30px',
                                   'primary' => true,
                                   'hide'   => true ),
                
                             
                             array('name'    => 'Cedula',
                                   'width'   => '80px',
                                   'column'  => 'pk_usuario'),
                
                             array('name'    => 'Nombre',
                                   'width'   => '150px',
                                   'column'  => 'nombre'),
                
                             array('name'    => 'Apellido',
                                   'width'   => '150px',
                                   'column'  => 'apellido'),
                
                
                             array('name'    => 'Perfil',
                                   'width'   => '100px',
                                   'column'  => 'perfil'),
                
                             array('name'    => 'Fecha Solicitud',
                                   'width'   => '80px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'fecha_prestamo'),
                
                          //   array('name'    => 'Cantidad',
                          //         'width'   => '10px',
                          //         'rows'    => array('style' => 'text-align:center'),
                          //         'column'  => 'numeroart'),
                
                             array('name'    => 'Cota',
                                   'width'   => '80px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'cota'),
                
                            array( 'name'    => 'Estado',
                                   'width'   => '70px',
                                   'column'  => 'estado')
                
                           
                );
             $other = array(
                      array('actionName' => '',
                            'action'     => 'detalle(##pk##)'  ,
                            'label'      => 'Detalle'));

                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VODI', $other);
            // Generamos la lista.
           
            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
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
            $pk_usuario  = $queryArray['pk_usuario'];  // tomamos el pk_usuario.
            
          
            //$id_row      = $this->_getParam('id', 0);
           
           
            
            if(is_numeric($pk_usuario) && !empty($pk_usuario)) {// validacion para saber si escribiste un numero
                $estado     = $this->valestado($pk_usuario);//pos inicicial arriba de este if
                $solvente   =  true; //$this->CmcBytes_profit->getSolvente($pk_usuario);//pos inicicial arriba de este if
               // $dataRow    =  $this->usuario->getRow($pk_usuario); 
                
                $dataRow    = $this->prestamo->getDataUsuario($pk_usuario,$this->_params['modal']['fk_sede']);

                $dataRow[0]['fk_sede'] = $this->_params['modal']['fk_sede'];
                //var_dump($dataRow);
                $perfiles   = $this->prestamo->getperfil($pk_usuario);
                $suspendido = $this->suspencion($pk_usuario);//nuevo 

                // $sedes = $this->prestamo->getSedes();
                // $this->SwapBytes_Form->fillSelectBox('fk_sede', $sedes, 'pk_estructura', 'nombre');

                if($suspendido<=0){
 
                    if($estado != "mora" && $estado != "prestamo"){
                    
                        if(true){ // aqui es solvente

                         if($dataRow[0]['nombre'] != "" && $perfiles[0]['perfil'] != "") {
                            $Status = false;
                            $this->SwapBytes_Form->fillSelectBox('fk_grupo',  $this->prestamo->getperfil($pk_usuario)  , 'pk_usuariogrupo', 'perfil');
                             
                             $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                             $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                    
                        }else{
                             $Status = true;
                             $html   = $this->SwapBytes_Html_Message->alert($this->FormAlert_Agregar);
                             $this->SwapBytes_Form->fillSelectBox('fk_grupo',  $this->prestamo->getperfil($pk_usuario)  , 'pk_usuariogrupo', 'perfil');
                             $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                             $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');    
                             }
                    }else{
                                
                    $Status = true;
                    $html   = $this->SwapBytes_Html_Message->alert($this->FormAlert_Solvente);
                    $this->SwapBytes_Form->fillSelectBox('fk_grupo',  $this->prestamo->getperfil($pk_usuario)  , 'pk_usuariogrupo', 'perfil');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                         }
                    
               }else{
                    //$Status = true;
                    //$html   = $this->SwapBytes_Html_Message->alert($this->FormAlert_Solvente);
                    //$this->SwapBytes_Form->fillSelectBox('fk_grupo',  $this->prestamo->getperfil($pk_usuario)  , 'pk_usuariogrupo', 'perfil');
                    //$json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                    //$json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                          $Status = true;
                          $html   = $this->SwapBytes_Html_Message->alert($this->FormAlert_Estado);
                          $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                          $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                } 
                    }else{
                    $Status = true;
                    $html   = $this->SwapBytes_Html_Message->alert("Quedan ".$suspendido." dias de suspensión");
                    $this->SwapBytes_Form->fillSelectBox('fk_grupo',  $this->prestamo->getperfil($pk_usuario)  , 'pk_usuariogrupo', 'perfil');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                    }
                // Creamos el frmModal con los datos necesarios.
              

                $this->SwapBytes_Form->set($this->view->form); //setamos el formulario

               if(isset($dataRow[0])) {
				
                    $this->view->form->populate($dataRow[0]);
                } 
                   
                // Definimos el acceso a los controles del frmModal.
               
                $this->SwapBytes_Form->readOnlyElement('nombre'         , true);
                $this->SwapBytes_Form->readOnlyElement('apellido'       , true);
                $this->SwapBytes_Form->readOnlyElement('direccion'      , true);
                $this->SwapBytes_Form->readOnlyElement('escuela'        , true);
                $this->SwapBytes_Form->readOnlyElement('telefono'       , true);
                $this->SwapBytes_Form->readOnlyElement('telefono_movil' , true);
                $this->SwapBytes_Form->readOnlyElement('correo'         , true);
                $this->SwapBytes_Form->readOnlyElement('fk_grupo'       , $Status);
                $this->view->form = $this->SwapBytes_Form->get();

                // Preparamos el frmModal para ser enviado por AJAX.
                $html  .= $this->SwapBytes_Ajax->render($this->view->form);

                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('Modal', 'Aceptar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('Modal', 'Eliminar');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
		$json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$pk_usuario}'");

                  $this->SwapBytes_Crud_Form->setJson($json);//new
                
                $this->getResponse()->setBody(Zend_Json::encode($json));
           }
			
        }
    }
  
  private function getData($id) {
        $dataRow = $this->prestamo->getview($id);

        
            return $dataRow;
        
    }
  
  public function photoAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$id    = $this->_getParam('id', 0);
		$image = $this->usuario->getPhoto($id);

		$this->getResponse()
		     ->setHeader('Content-type', 'image/jpeg')
		     ->setBody($image);
	}
 
  public function addoreditloadAction() {
       
        if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
          
			$dataRow       = $this->prestamo->getRow($this->_params['modal']['id']);
			$dataRow['id'] = $this->_params['modal']['id'];
                  
		$dataRow['fecha_devolucion'] = $this->SwapBytes_Date->convertToForm($dataRow['fecha_devolucion']);
                
                
		$json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fecha_devolucion');

        }else{

            $dataRow['fk_sede'] = $this->_params['filters']['Sede'];
        }



        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ficha Prestamo');

        // $sedes = $this->prestamo->getSedes();
        // $this->SwapBytes_Crud_Form->fillSelectBox('fk_sede', $sedes, 'pk_estructura', 'nombre');


        $this->SwapBytes_Crud_Form->getAddOrEditLoad();
        
        
    }
    
  public function addoreditconfirmAction() {
     
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $this->session->grupo_id = $this->_params['modal']['fk_grupo'];
            $this->_params['modal']['fk_grupo']="";
            
           
                        if($this->session->grupo_id !=""){ 
                         
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

            $sede = $dataRow['fk_sede'];
            

			$id                       = $dataRow['id'];
                        $dataRow['fk_usuariogrupo']= $this->session->grupo_id;
			                  $dataRow['id']                = null;
                        $dataRow['fk_grupo']          = null;
                        $dataRow['pk_usuario']        = null;
                        $dataRow['nombre']            = null;
                        $dataRow['apellido']          = null;
                        $dataRow['escuela']           = null;
                        $dataRow['direccion']         = null;
                        $dataRow['telefono']          = null;
                        $dataRow['telefono_movil']    = null;
                        $dataRow['correo']            = null;
                        $dataRow['fk_sede']            = null;
                        //$dataRow['fecha_prestamo']     = date('d-m-Y');
			                 $dataRow['fecha_prestamo']     = date('Y-m-d');

            
			if(is_numeric($id) && $id > 0) {
                            
				$this->prestamo->updateRow($id, $dataRow);
			} else{
                   
				$this->prestamo->addRow($dataRow);
			}
                        
                        

                        $idn = $this->prestamo->getUltimopk();
                        
                        
                        $data = array( 'module'=>'transactions',
                                       'controller'=>'prestamoart',
                                       'params'=>array('id' => $idn[0]['pk_prestamo'],
                                        'sede' => $sede,
                                       'set' => 'false'));
 
        
        //$json[] = $this->CmcBytes_Redirector->getRedirect($data);
        $this->getResponse()->setBody(Zend_Json::encode($json));
                        
                        
                        
			$this->SwapBytes_Crud_Form->getAddOrEditEnd($data);
                        
		}
    }  

  public function deletefinishAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
           
          
            $this->prestamo->deleteRow($this->_params['modal']['id']);
        


	$this->SwapBytes_Crud_Form->setProperties($this->view->form);
	$this->SwapBytes_Crud_Form->getDeleteFinish();
           
        }
    }

  public function deleteloadAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $sedes = $this->prestamo->getSedes();
	   
            $cantidad = $this->prestamo->contArticulo(($this->_params['modal']['id']));
           
            if ($cantidad[0]['count'] > 0){
                $message = 'No se puede Eliminar:';
                $permit = false;
            }else{    
            
            $message = 'Seguro desea eliminar esto?:';
	    $permit = true;
            }

            $dataRow          = $this->getData($this->_params['modal']['id']);
            $dataRow['id'] = $this->_params['modal']['id'];
            $dataRow['pk_usuario'] = $dataRow[0]['pk_usuario'];
            $dataRow['nombre'] = $dataRow[0]['nombre'];
            $dataRow['apellido'] = $dataRow[0]['apellido'];
            $dataRow['direccion'] = $dataRow[0]['direccion'];
            $dataRow['escuela']           = $dataRow[0]['escuela'];
            $dataRow['fk_grupo'] = $dataRow[0]['perfil'];
            $dataRow['telefono_movil']    = $dataRow[0]['telefono_movil'];
            $dataRow['telefono'] = $dataRow[0]['telefono'];
            $dataRow['correo'] = $dataRow[0]['correo'];
            $dataRow['fk_grupo'] = $dataRow[0]['perfil'];

            $dataRow['fk_sede'] = $dataRow[0]['estructura'];


            
            
            $this->view->form->fk_grupo->addMultiOption(1,$dataRow['fk_grupo']);
            
            
         
         $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
        $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$dataRow['pk_usuario']}'");
	       $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Acividad', $message);

        //  // para incluir la sede
        // $this->SwapBytes_Crud_Form->fillSelectBox('fk_sede', $sedes, 'pk_estructura', 'nombre');
        // if(empty($dataRow['fk_sede'])){//por si acaso la sede del prestamo viene vacia
        //     $dataRow['fk_sede'] = 7;
        // }

        // $json[] = '$("#fk_sede option[value="+'.$dataRow['fk_sede'].'+"]").attr("selected",true)';



        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->getDeleteLoad($permit,$msg);
        
           
        }
    }

  public function viewAction() {
     // $this->detalleAction();
        // Obtenemos los parametros que se esperan recibir.
       $sedes = $this->prestamo->getSedes();
        
        $dataRow          = $this->getData($this->_params['modal']['id']);
        // var_dump($dataRow);die;
        $dataRow['pk_usuario'] = $dataRow[0]['pk_usuario'];
        $dataRow['nombre'] = $dataRow[0]['nombre'];
        $dataRow['apellido'] = $dataRow[0]['apellido'];
        $dataRow['direccion'] = $dataRow[0]['direccion'];
        $dataRow['escuela']           = $dataRow[0]['escuela'];
        $dataRow['fk_grupo'] = $dataRow[0]['perfil'];
        $dataRow['telefono'] = $dataRow[0]['telefono'];
        $dataRow['telefono_movil']    = $dataRow[0]['telefono_movil'];
        $dataRow['correo'] = $dataRow[0]['correo'];
        $dataRow['fk_grupo'] = $dataRow[0]['perfil'];
        
        $dataRow['fk_sede'] = $dataRow[0]['estructura'];


        
        

        

        $this->view->form->fk_grupo->addMultiOption(1,$dataRow['fk_grupo']);
        $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
        $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$dataRow['pk_usuario']}'");
        

        //$dataRow['fecha_devolucion'] = $this->SwapBytes_Date->convertToForm($dataRow['fecha_devolucion']);
        //$this->SwapBytes_Form->fillSelectBox('fk_grupo',$dataRow,$dataRow[0]['perfil'],$dataRow[0]['perfil']);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Usuario');

        // $this->SwapBytes_Crud_Form->fillSelectBox('fk_sede', $sedes, 'pk_estructura', 'nombre');

        // if(empty($dataRow['fk_sede'])){//por si acaso la sede del prestamo viene vacia
        //     $dataRow['fk_sede'] = 7;
        // }

        // $json[] = '$("#fk_sede option[value="+'.$dataRow['fk_sede'].'+"]").attr("selected",true)';

        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->getView();
       
    }

  public function detalleAction() {
     
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();
      $id = $this->_getParam('pk');
      $sede = $this->_getParam('sede');
      $data = array( 'module'=>'transactions',
                     'controller'=>'prestamoart',
                      'params'=>array('id' => $id,
                                        'sede'=>$sede,
                                      'set' => 'true'));
 
        
        $json[] = $this->CmcBytes_Redirector->getRedirect($data);
        $this->getResponse()->setBody(Zend_Json::encode($json));
    }
  }
  
  private function update_estadoArt(){
     if ($this->_request->isXmlHttpRequest()) {
           
       // $fecha = date("d/m/Y");//m/d/Y
         $fecha = date('m/d/Y')  ;    
        $this->prestamo->updateEstado($fecha);
        
         }
 } 
 
  private function update_estadoPres($rows){
     if ($this->_request->isXmlHttpRequest()) {
          $mora = false; 
          $transito = false;
          $solvente = false;
          $x = 0;
          
         foreach ($rows as $row){
          
          $estado= $this->prestamo->getestadoArticulo($row['solicitud']);
          
          if(count($estado)>0){
          for($i = 0 ; $i< count($estado); $i++){
              
          if($estado[$i]['fk_asignacion']==$this->Moraval){
              $mora = true;
              $rows[$x]['estado']="Mora";
          }
         
           if($estado[$i]['fk_asignacion']==$this->Prestamoval && !$mora){
              $transito = true;
              $rows[$x]['estado']="Transito";
          }
           if($estado[$i]['fk_asignacion']==$this->Devueltoval && !$mora && !$transito){
              $solvente = false;
              $rows[$x]['estado']="Solvente";
          }
           
          }
          }else{
              $rows[$x]['estado']="Vacio";
          }
          $estado = "";
          $mora = false;
          $transito = false;
          $solvente = false;
          $x = $x + 1; 
         }
        
         
         return $rows;
         }
 }
  
  private function valestado($pk_usuario){
      
     if(is_numeric($pk_usuario) && !empty($pk_usuario)) {
        $mora = $this->prestamo->getMORA($pk_usuario);  
        $prestamo = $this->prestamo->getPrestamo($pk_usuario);
        $devuelto = $this->prestamo->getDevuelto($pk_usuario);
        if(count($mora)>0){
          return "mora"  ;//mora
        }
        if(count($prestamo)>0){
          return "prestamo";//Transito
        }
        if(count($devuelto)>0){
          return "devuelto";//solvente
        }
        
      }
      
     
  }
 
  private function mensaje_rojo(){
          $json[] = $this->SwapBytes_Jquery_Ui_Form->displayError($msg, $id);
          return $json;
      }
  
  private function suspencion($pk_usuario){
      
      $info = $this->prestamo->dias_suspencion(); 
      foreach ($info as $datos){
           if((int)$datos['pk_usuario'] == $pk_usuario){
               return $datos['total'];
           }      
      }
      return 0;
  }

  
  public function infoAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
           
            $json = array();
            $quien = "";
            $horario = "";
            $usuario          = $this->getData($this->_params['modal']['id']);
            $periodo          = $this->periodo->getUltimo();
            
            
            if($usuario[0]['perfil']==="Estudiante"){
                $quien = "est";
                
                $horario = $this->prestamo->getHorarioPersona($usuario[0]['pk_usuario'], $periodo, $quien, $usuario[0]['estructura']);
                $html    = $this->horario_estudiante($horario);

               
                
            }
            if($usuario[0]['perfil']==="Docente"){
                $quien = "prof";
                $horario=$this->prestamo->getHorarioPersona($usuario[0]['pk_usuario'], $periodo, $quien, $usuario[0]['estructura']);
                $html    = $this->horario_profesor($horario); //PRUEBA
                
               
                 // $html = '<table id=tbl_hora border="1"  style="text-align:center"';
                //  $html .= '<tr>';
                //  $html .= '<td>';
                //  $html .= '<th>No tiene horario asignado </th>';
                //  $html .= '</td>';
               //   $html .= '</tr>';
               //   $html .= '</table>';
            }
            if($usuario[0]['perfil']==="Administrativo"){
                  $html = '<table id=tbl_hora border="1"  style="text-align:center"';
                  $html .= '<tr>';
                  $html .= '<td>';
                  $html .= '<th>No tiene horario asignado </th>';
                  $html .= '</td>';
                  $html .= '</tr>';
                  $html .= '</table>';
            }
                      
            // Envia los datos al modal.
            $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 320);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 580);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle('frmModal', "Horario");
            $json[] = $this->SwapBytes_Jquery_Ui_Form->open('frmModal');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
            
            
            
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
  
  
  public function helpAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $this->render();
    }
    
  public function transformRows($rows){
       if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader(); 
            
            $nrows = array();
            $cantidad = count($rows);
            for ($i = 0 ; $i < $cantidad ; $i++){
               
                $nrows[$i]['solicitud']      = $rows[$i]['solicitud'];
                $nrows[$i]['pk_usuario']     = $rows[$i]['pk_usuario'];
                $nrows[$i]['nombre']         = $rows[$i]['nombre'];
                $nrows[$i]['apellido']       = $rows[$i]['apellido'];
                $nrows[$i]['perfil']         = $rows[$i]['perfil'];
                $nrows[$i]['correo']         = $rows[$i]['correo'];
                $nrows[$i]['estado']         = $rows[$i]['estado'];
                $nrows[$i]['fecha_prestamo'] = $rows[$i]['fecha_prestamo'];
                $nrows[$i]['cota'] = str_replace(","," , ",$rows[$i]['cota']);
                $nrows[$i]['cota'] = str_replace("}"," ", $nrows[$i]['cota']);
                $nrows[$i]['cota'] = str_replace("{"," ", $nrows[$i]['cota']);
                $nrows[$i]['cota'] = str_replace('"'," ", $nrows[$i]['cota']);
                $nrows[$i]['cota'] = str_replace('NULL'," ", $nrows[$i]['cota']); 
                $nrows[$i]['numeroart']      = $rows[$i]['numeroart'];
                $nrows[$i]['orden']          = $rows[$i]['orden'];
                
            }
            
            return $nrows;
            }
  }
  
  public function horario_estudiante($horario){ // despues
        
      $times     = array();
      $dias       = array();
      
           $HTML = '<table id=tbl_horarios border="1"  style="text-align:center"';
           $HTML .= '<tr>';
           

                   $HTML .= '<th>Hora</th>';

                    // Capturas de los dias..  
                   foreach ($horario as $mdia){
                       if(!in_array( $mdia['dia'], $dias)){//valido que no existan dias repetidos
                          
                          array_push($dias, $mdia['dia']);
                          
                          $HTML .= "<th>" . $mdia['dia'] . "</th>";
                       }
                       
                   }
                   
                  // Captura de las horas   
                  foreach($horario as $mhora){
                       if (!in_array($mhora['hora'], $times)) {
                           array_push($times, $mhora['hora']); // times tiene todas las horas
                       }
                  }
                  sort($times);

           $HTML .= '</tr>';
          
           foreach ($times as $time ){
                $HTML .= '<tr>';
                $HTML .= '<td>';
                $HTML .= '<p>'.date("g:i a",strtotime($time)).'</p>';
                $HTML .= '</td>'; 
                
                foreach($dias as $dia){
                    $HTML .= '<td>';
                    $entro = false;
                     foreach($horario as $mhorario){
                         if($dia == $mhorario['dia'] && $time == $mhorario['hora'] ){
                              $HTML .= '<b>'.$mhorario['materia'].'</b>';
                              $HTML .='<br></br>';
                              $HTML .= '<b>'.$mhorario['lugar'].'</b>';
                              $entro = true;
                         }
                         
                     }
                     if (!$entro){
                      
                          $HTML .= '<div class="stripe"></div>';
                     }
                     $HTML .= '</td>';
                }
                $HTML .= '</tr>';
               
           }
           
           $HTML .= '</table>';
              
           return $HTML;

  }
  
  public function horario_profesor($horario){//
      
      $times = array();
      $dias  = array();
      //var_dump($horario);
     
      $HTML = '<table id=tbl_horariosp border="1"  style="text-align:center"';
      $HTML .= '<tr>';
      $HTML .= '<th>Hora</th>';
      
      foreach ($horario as $mdia){
                       if(!in_array( $mdia['dia'], $dias)){//valido que no existan dias repetidos
                          
                          array_push($dias, $mdia['dia']);
                          
                          $HTML .= "<th>" . $mdia['dia'] . "</th>";
                       }
                       
                   }
                
        foreach($horario as $mhora){
            if($mhora['lugar']!=""){
                
                       if (!in_array($mhora['horainicio'], $times)) {
                           array_push($times, $mhora['horainicio']); // times tiene todas las horas
                       }
                  }
                  
               }
                        sort($times);     
                         
                   $HTML .= '</tr>';
                   
                     
               foreach ($times as $time ){
                $HTML .= '<tr>';
                $HTML .= '<td>';
                $HTML .= '<p>'.date("g:i a",strtotime($time)).'</p>';
                $HTML .= '</td>'; 
               
                 
                foreach($dias as $dia){
                    $HTML .= '<td style="height:60px;">';
                    $entro = false;
                     foreach($horario as $mhorario){
                         if($dia == $mhorario['dia'] && $time == $mhorario['horainicio']){
                              $HTML .= '<div>'.$mhorario['lugar'].'</div>';
                              $entro = true;
                         }
                         
                     }
                     if (!$entro){
                      
                          $HTML .= '<div class="stripe"></div>';
                     }
                     $HTML .= '</td>';
                }
                $HTML .= '</tr>';
               
           }
          
                   
           $HTML .= '</table>';
              
           return $HTML;    
                  
     
      
  }
  
}
