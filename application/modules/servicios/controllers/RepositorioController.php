<?php
class Transactions_RepositorioController extends Zend_Controller_Action {


    private $_Title   = 'Transacciones \ Lista de Usuarios de MiUNE';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Profit');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Usuariosdatos');
        Zend_Loader::loadClass('Models_DbView_Grupos');
        Zend_Loader::loadClass('Models_DbView_Sedes');
        Zend_Loader::loadClass('Forms_Nuevoingreso');
        Zend_Loader::loadClass('Models_DbView_Escuelas');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Pensums');
        Zend_Loader::loadClass('Models_DbTable_Cargacarnets');


        $this->Usuarios         = new Models_DbTable_Usuarios();
        $this->usuariosdatos    = new Models_DbTable_Usuariosdatos();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->recordacademico  = new Models_DbTable_Recordsacademicos();
        $this->filtros          = new Une_Filtros();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();
        $this->vw_grupos        = new Models_DbView_Grupos();
        $this->vw_escuelas      = new Models_DbView_Escuelas();
        $this->profit           = new Models_DbTable_Profit();
        $this->inscripciones    = new Models_DbTable_Inscripciones();
        $this->vw_sedes         = new Models_DbView_Sedes();
        $this->periodos         = new Models_DbTable_Periodos();
        $this->atributo         = new Models_DbTable_Atributos();
        $this->inscripciones    = new Models_DbTable_Inscripciones();
        $this->pensum           = new Models_DbTable_Pensums();
        $this->CargaCarnets     = new Models_DbTable_CargaCarnets();

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
	$this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->AuthSpace                = new Zend_Session_Namespace('Zend_Auth');
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();

        $this->tablas = Array('periodo'  => Array('tbl_periodos',
                                                  null,
                                                  Array('pk_periodo',
                                                        'lpad(pk_periodo::text, 4, \'0\') || \', \' || to_char(fechainicio, \'MM-yyyy\') || \' / \' ||  to_char(fechafin, \'MM-yyyy\')'),
                                                  'DESC'),
                               'sede'    => Array('vw_sedes',
                                                  null,
                                                  Array('pk_estructura',
                                                        'nombre'),
                                                  ),
                               'escuela' => Array('vw_escuelas',
                                                 'pk_atributo <> 920',
                                                 Array('pk_atributo',
                                                       'escuela'),
                                                 '1 ASC')
                                );

        // SELECT * FROM  fn_xrxx_perfil_principal_usuario(19022630) AS (pk_grupo bigint , grupo character varying)

                $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));



        //	 * Obtiene los parametros del modal.
		$this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();



//      BOTONES DE ACCIONES

        $this->SwapBytes_Crud_Action->setDisplay(true, true, true, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, true, true, true, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(true);

//    FORM

     $this->view->form = new Forms_Nuevoingreso();

            $this->SwapBytes_Form->set($this->view->form);
//    $this->SwapBytes_Form->fillSelectBox('fk_grupo',  $this->vw_grupos->getGrupoespecifico($this->fk_grupo)  , 'pk_atributo', 'grupo');
     $this->view->form = $this->SwapBytes_Form->get();

     $this->SwapBytes_Form->fillSelectBox('fk_tipocolegio', $this->atributo->getTipes(74, null) , 'pk_atributo', 'valor');
     //$this->SwapBytes_Form->fillSelectBox('fk_colegio', $this->atributo->getTipes(72, null) , 'pk_atributo', 'valor');
     $this->SwapBytes_Form->fillSelectBox('fk_tipodeingreso', $this->atributo->getTipes(73, null) , 'pk_atributo', 'valor');
    // $this->SwapBytes_Form->fillSelectBox('fk_escuela', $this->vw_escuelas->getEscuelas() , 'pk_atributo', 'escuela');
    // $this->SwapBytes_Form->fillSelectBox('fk_sede', $this->vw_sedes->getSedes() , 'pk_estructura', 'nombre');


}

  // function preDispatch() {
  //       if(!Zend_Auth::getInstance()->hasIdentity()) {
  //           $this->_helper->redirector('index', 'login', 'default');
  //       }
  //       if (!$this->grupo->haveAccessToModule()) {
  //     	  $this->_helper->redirector('accesserror', 'profile', 'default');
  //     	}
  //   }

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

   public function listAction() {

     if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $pageNumber  = $this->_getParam('page', 1);
            $searchData  = $this->_getParam('buscar');
            $itemPerPage = 15;
            $pageRange   = 10;
            $json   = array();
            $this->Usuarios->setSearch($searchData);
            $this->Usuarios->setData(array('Perfil' => 855), array('Perfil'));
            $periodo = $this->_params['filters']['periodo'];
            $escuela = $this->_params['filters']['escuela'];
            $sede = $this->_params['filters']['sede'];

            $paginatorCount = $this->Usuarios->totalusuariosNI($periodo, $escuela, $sede);
            $rows = $this->Usuarios->getUsuariosNI($itemPerPage, $pageNumber, $periodo, $escuela, $sede);


            $this->usuariosdatos;
            if(isset($rows) && count($rows) > 0) {


// Definimos las propiedades de la tabla.
            $table = array('class' => 'tableData',
                           'width' => '600px');

            $columns = array(array('column'  => 'pk_usuario',
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
                                                      'name'  => 'chkUsuario',
                                                      'value' => '##pk_usuario##')),
                             array('name'    => 'C.I.',
                                       'width'   => '70px',
                                       'column'  => 'ci',
                                       'rows'    => array('style' => 'text-align:center')),
                             array('name'    => 'Nombre',
                                   'width'   => '200px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'nombre'),
                             array('name'    => 'Apellido',
                                   'width'   => '200px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'apellido'),
                             array('name'    => 'Indice',
                                   'width'   => '200px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'column'  => 'promedio')

                );


                $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'VUD');

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkUsuario');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen Usuario cargados.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

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
            $queryStringper = $this->_getParam('other');
            $queryArrayper  = $this->SwapBytes_Uri->queryToArray($queryStringper);
            $pk_usuario  = $queryArray['pk_usuario'];
            $grupo  = $queryArray['perfil'];
            $periodo = $queryArrayper['periodo'];
            

            

            if(is_numeric($pk_usuario) && !empty($pk_usuario)) {
                // Buscamos si el nuevo usuario tiene un grupo asignado.
                $usuariogrupo = $this->grupo->getCount($pk_usuario, ' AND fk_grupo =' . 855);
                $usuario    = $this->Usuarios->getCount($pk_usuario);
                $dataRow    = $this->Usuarios->getRow($pk_usuario);



                $dataRow['nacionalidad']    = $this->SwapBytes_Form->setValueToBoolean($dataRow['nacionalidad']);
                $dataRow['actualizado']    = $this->SwapBytes_Form->setValueToBoolean($dataRow['actualizado']);
                $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);
                $dataRow['colegio'] = $this->usuariosdatos->getcolegio($pk_usuario);
                $dataRow['fk_tipodeingreso'] = $this->usuariosdatos->getTipoIngreso($pk_usuario);
                $dataRow['indice'] = $this->usuariosdatos->getPromedio($pk_usuario);
                $dataRow['pago'] = $this->profit->VerificarPago($pk_usuario, $periodo);
                $dataRow['pk_usuario'] = $pk_usuario;

                // Verificamos si el Usuario existe, al ser asi evitamos que
                // se pueda agregar, de lo contrario lo permitimos.
                if($usuariogrupo == 1 && $usuario == 1) {
                    $Status = true;
                    
                    if(!isset($dataRow['pago'])){

			$pago_alterno = $this->inscripciones->getPagoPeriodo($pk_usuario, $periodo);

			if(!isset($pago_alterno) && $pago_alterno == false){
				$nopagoperiodo = true;
			}else{
				$html = $this->SwapBytes_Html_Message->alert('El pago del alumno fue registrado en el sistema manualmente');
				$dataRow['pago'] = $pago_alterno;
			}

                    }
                    $html   = $this->SwapBytes_Html_Message->alert('Este Numero de Cedula ya se encuentra en el sistema y ya tiene el perfil asignado.');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                } else if($usuariogrupo == 0 && $usuario == 0) {
                    $info = $this->SeparateNames($pk_usuario);
                    $otherinfo = $this->profit->InfoEstudiante($pk_usuario);

                    $dataRow['primer_nombre'] = $info[0];
                    $dataRow['segundo_nombre'] = $info[1];
                    $dataRow['primer_apellido'] = $info[2];
                    $dataRow['segundo_apellido'] = $info[3];
                    $dataRow['correo'] = trim($otherinfo['email']);
                    $dataRow['direccion'] = trim($otherinfo['direccion']);
                    
                    
                    if(!isset($dataRow['pago'])){
                        $message = 'El usuario de cedula: ' . $pk_usuario . ' no tiene pago en el sistema';
                        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                        $html = $this->SwapBytes_Html_Message->alert($message);
                        $nopago = true;
                        
                    }else{

                        $Status = false;
                        $json[] = $dataRow;
                        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');

                    }

                    
                } else if($usuariogrupo == 0 && $usuario == 1) {
                    $Status = true;
                    $dataRow['id'] = $pk_usuario;
                    $html   = $this->SwapBytes_Html_Message->alert('El usuario ya existe en el sistema, ¿Desea agregarlo al perfil?');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                }

                 // Creamos el frmModal con los datos necesarios.

                $this->SwapBytes_Form->set($this->view->form);

                if(isset($dataRow)) {
        					$dataRow['nacionalidad'] = (empty($dataRow['nacionalidad']))? 't' : 'f';
        					$dataRow['sexo']         = (empty($dataRow['sexo']))?         'f' : 't';
                  $this->view->form->populate($dataRow);
                }

                // Definimos el acceso a los controles del frmModal.
                $this->SwapBytes_Form->readOnlyElement('nacionalidad'    , $Status);
                $this->SwapBytes_Form->readOnlyElement('sexo'            , $Status);
                $this->SwapBytes_Form->readOnlyElement('primer_nombre'   , $Status);
                $this->SwapBytes_Form->readOnlyElement('segundo_nombre'  , $Status);
                $this->SwapBytes_Form->readOnlyElement('primer_apellido' , $Status);
                $this->SwapBytes_Form->readOnlyElement('segundo_apellido', $Status);
                $this->SwapBytes_Form->readOnlyElement('direccion'       , $Status);
                $this->SwapBytes_Form->readOnlyElement('correo'          , $Status);
                $this->SwapBytes_Form->readOnlyElement('fechanacimiento' , $Status);
                $this->SwapBytes_Form->readOnlyElement('telefono'        , $Status);
                $this->SwapBytes_Form->readOnlyElement('telefono_movil'  , $Status);

                $this->view->form = $this->SwapBytes_Form->get();

                // Preparamos el frmModal para ser enviado por AJAX.
                $html  .= $this->SwapBytes_Ajax->render($this->view->form);

                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
                $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
		$json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$pk_usuario}'");

                if($nopago == true){
                
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->hideAllInputs('frmModal');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter('frmModal');
                    //$this->SwapBytes_Crud_Form->getDialog('Error registrando el pago del estudiante', $message, swOkOnly);
                }else if($nopagoperiodo == true){
                    $json[] = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>No tiene pago en el periodo.</span>');";
                }else{
                    $json[] = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>Pago registrado número: ". $dataRow['pago'] ."</span>');";
                }

                $this->getResponse()->setBody(Zend_Json::encode($json));

                
            } else {

                $html  .= $this->SwapBytes_Ajax->render($this->view->form);

                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
                $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');

                if($nopago == true){
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->hideAllInputs('frmModal');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter('frmModal');
                }else if($nopagoperiodo == true){
                    $json[] = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>No tiene pago en el periodo.</span>');";
                }else{
                    $json[] = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>Pago registrado número: ". $dataRow['pago'] ."</span>');";
                }


		$this->getResponse()->setBody(Zend_Json::encode($json));

                

            }
        }
    }


public function photoAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$id    = $this->_getParam('id', 0);
		$image = $this->Usuarios->getPhoto($id);

		$this->getResponse()
		     ->setHeader('Content-type', 'image/jpeg')
		     ->setBody($image);
	}

public function addoreditloadAction() {
    if ($this->_request->isXmlHttpRequest()){
    if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
        
    if ($this->grupo->getCount($this->_params['modal']['id']) > 1) {

                $this->SwapBytes_Form->readOnlyElement('nacionalidad'    , True);
                $this->SwapBytes_Form->readOnlyElement('sexo'            , True);
                $this->SwapBytes_Form->readOnlyElement('primer_nombre'   , True);
                $this->SwapBytes_Form->readOnlyElement('segundo_nombre'  , True);
                $this->SwapBytes_Form->readOnlyElement('primer_apellido' , True);
                $this->SwapBytes_Form->readOnlyElement('segundo_apellido', True);
                $this->SwapBytes_Form->readOnlyElement('direccion'       , True);
                $this->SwapBytes_Form->readOnlyElement('correo'          , True);
                $this->SwapBytes_Form->readOnlyElement('fechanacimiento' , True);
                $this->SwapBytes_Form->readOnlyElement('telefono'        , True);
                $this->SwapBytes_Form->readOnlyElement('telefono_movil'  , True);    
                $this->SwapBytes_Form->readOnlyElement('perfil', True);
    }

        $title = 'Editar Usuario';
                        $periodo = $this->_params['filters']['periodo'];


			$dataRow = $this->Usuarios->getRow($this->_params['modal']['id']);
                        if($dataRow['primer_nombre'] == ''|| !isset($dataRow['primer_nombre'])){
                            $this->Usuarios->splitnames($this->_params['modal']['id']);
                            $dataRow = $this->Usuarios->getRow($this->_params['modal']['id']);
                        }
                        
                        $dataRow['id'] = $this->_params['modal']['id'];
			$dataRow['pk_usuario']  = $this->_params['modal']['id'];
                        $dataRow['perfil'] = $this->_params['filters']['Perfil'];
                        $dataRow['sexo']            = $this->SwapBytes_Form->setValueToBoolean($dataRow['sexo']);
                        $dataRow['actualizado']    = $this->SwapBytes_Form->setValueToBoolean($dataRow['actualizado']);
                        $dataRow['nacionalidad']    = $this->SwapBytes_Form->setValueToBoolean($dataRow['nacionalidad']);
                        $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);
                        //$dataRow['fk_escuela'] = $this->inscripciones->getUltimaEscuelapk($this->_params['modal']['id']);
                        $dataRow['pago'] = $this->profit->VerificarPago($this->_params['modal']['id'], $periodo);
                        
                        $dataRow['fk_tipocolegio'] = $this->usuariosdatos->gettipocolegio($dataRow['pk_usuario']);
                        $dataRow['colegio'] = $this->usuariosdatos->getcolegio($dataRow['pk_usuario']);
                        $dataRow['fk_tipodeingreso'] = $this->usuariosdatos->getTipoIngreso($dataRow['pk_usuario']);
                        $dataRow['indice'] = $this->usuariosdatos->getPromedio($dataRow['pk_usuario']);
                        $json[] = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>Pago registrado número: ". $dataRow['pago'] ."</span>');";

        } else {
            
            $title = 'Agregar Usuario';
            

            $dataRow['actualizado']    = $this->SwapBytes_Form->setValueToBoolean('t');
            $dataRow['nacionalidad']    = $this->SwapBytes_Form->setValueToBoolean('f');
	}


         $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
         $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
         $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
         $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$dataRow['pk_usuario']}'");
         $json[] = "$('#indice-element').append('<span style=\'margin-left: 10px; color: red;\'>(ej. 14.5)</span>');";
         $json[] = "$('#fechanacimiento-element').append('<span style=\'margin-left: 10px; color: red;\'>(ej. 01/22/2012)</span>');";

         $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
	$this->SwapBytes_Crud_Form->setJson($json);
	$this->SwapBytes_Crud_Form->setWidthLeft('120px');
	$this->SwapBytes_Crud_Form->getAddOrEditLoad();
  }
}



// hacemos las validaciones a editar o agregar
  public function addoreditconfirmAction() {

   if ($this->_request->isXmlHttpRequest()) {
     $this->SwapBytes_Ajax->setHeader();

     $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
     $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
     $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');

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

      $periodo = $this->_params['filters']['periodo'];
      $escuela = $this->_params['filters']['escuela'];
      $sede = $this->_params['filters']['sede'];

      $dataRow['pk_usuario']      = (isset($dataRow['pk_usuario']))? $dataRow['pk_usuario'] : $dataRow['id'];
      $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToDataBase($dataRow['fechanacimiento']);
      $usuario                    = $this->Usuarios->getCount($dataRow['pk_usuario']);
      $gruposcount                = $this->grupo->getCount($id, ' AND fk_grupo = ' . 855);
      $dataRow['nombre']          = $dataRow['primer_nombre'] . ' '. $dataRow['segundo_nombre'];
      $dataRow['apellido']        = $dataRow['primer_apellido'] . ' '. $dataRow['segundo_apellido'];

      if(empty($dataRow['segundo_nombre'])){
        $dataRow['segundo_nombre'] = ' ';
      }

      if(empty($dataRow['segundo_apellido'])){ 
        $dataRow['segundo_apellido'] = ' ';    
      } 

      $dataRow['passwordhash'] = md5($dataRow['pk_usuario']);

      $dataRow['id'] = null;
      $dataRow['perfil']   = null;

      $this->_params['modal']['perfil']        = null;
      $this->_params['modal']['fechanacimiento'] = $dataRow['fechanacimiento'];

      $usuariosdatos['fk_usuario'] = $dataRow['pk_usuario'];
      $usuariosdatos['promedio'] = str_replace(",",".",$dataRow['indice']);
      $usuariosdatos['colegio'] = $dataRow['colegio'];
      $usuariosdatos['fk_tipocolegio'] = $dataRow['fk_tipocolegio'];
      $usuariosdatos['fk_tipodeingreso'] = $dataRow['fk_tipodeingreso'];

      $dataRow['indice'] = null;
      $dataRow['fk_tipocolegio'] = null;
      $dataRow['colegio'] = null;
      $dataRow['fk_tipodeingreso'] = null;

      $inscripciondatos['numeropago'] = $dataRow['pago'];
      $inscripciondatos['fk_periodo'] = $periodo;
      $inscripciondatos['fk_atributo'] = $escuela;
      $inscripciondatos['fk_estructura'] = $sede;

      $dataRow['pago'] = null;

      $dataRow['telefono'] = str_replace('.','',str_replace(')','',str_replace('(','',$dataRow['telefono'])));
      $dataRow['telefono_movil'] = str_replace('.','',str_replace(')','',str_replace('(','',$dataRow['telefono_movil'])));


      if($gruposcount >= 1 && $usuario == 1 && isset($id) && $id > 0) {
        $this->Usuarios->updateRow($id, $dataRow);

      } else if($gruposcount == 0 && $usuario == 0 && empty($id)) {
				$this->Usuarios->addRow($dataRow); //add usuario
        $this->Usuarios->namestocomplete($dataRow['pk_usuario']);
				$this->grupo->addRow($dataRow['pk_usuario'], 855); //add grupo

			} else if($gruposcount == 0 && $usuario == 1 && isset($id)) {
        $this->Usuarios->namestocomplete($dataRow['pk_usuario']);
        $this->grupo->addRow($id, 855);
      }

      $datos_exist = $this->usuariosdatos->getUserData($dataRow['pk_usuario']);

      if(!isset($datos_exist[0]['pk_usuariodato'])){
        $this->usuariosdatos->insertar($usuariosdatos);
      }else{
        $this->usuariosdatos->updateUser($usuariosdatos);
      }

      $inscripciondatos['fk_usuariogrupo'] = $this->grupo->getpkgrupoEstudiante($dataRow['pk_usuario']);

      $lastinscripcion = $this->inscripciones->getInscripcionPeriodo($dataRow['pk_usuario'],$periodo);

      if ($lastinscripcion == true){
        $inscripciondatos['pk_inscripcion'] = $lastinscripcion;
        $inscripciondatos['fk_pensum'] = $this->pensum->getPensumDeEscuela($inscripciondatos['fk_atributo']);
        $this->inscripciones->updateEscuelaSedePensum($inscripciondatos);

      }else{
        $inscripciondatos['fk_pensum'] = $this->pensum->getPensumDeEscuela($inscripciondatos['fk_atributo']);
        $this->inscripciones->addRow($inscripciondatos['fk_usuariogrupo'], $periodo, $inscripciondatos['numeropago'], 0, $inscripciondatos['fk_atributo'], $inscripciondatos['fk_estructura'], 873, null,$inscripciondatos['fk_pensum']);

      }

      $solicitud = $this->CargaCarnets->getEstudiantesCarnet($dataRow['pk_usuario']);
      if($solicitud[0]['cuenta'] == 0){
        $this->CargaCarnets->setEstudiantesCarnet($dataRow['pk_usuario']);//Se le inserta el carnet al estudiante Nuevo Ingreso. 
      }

      $this->SwapBytes_Crud_Form->getAddOrEditEnd();
    }
  }

    // cargamos Formulario ver
  public function viewAction() {

    $dataRow = $this->Usuarios->getRow($this->_params['modal']['id']);

    if($dataRow['primer_nombre'] == ''|| !isset($dataRow['primer_nombre'])){
      $this->Usuarios->splitnames($this->_params['modal']['id']);
      $dataRow = $this->Usuarios->getRow($this->_params['modal']['id']);

    }

    $dataRow['pk_usuario']  = $this->_params['modal']['id'];
    $dataRow['perfil']  = $this->_params['filters']['Perfil'] ;
    $dataRow['sexo']            = $this->SwapBytes_Form->setValueToBoolean($dataRow['sexo']);
    $dataRow['actualizado']    = $this->SwapBytes_Form->setValueToBoolean($dataRow['actualizado']);
    $dataRow['nacionalidad']    = $this->SwapBytes_Form->setValueToBoolean($dataRow['nacionalidad']);
    $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);

    $dataRow['colegio'] = $this->usuariosdatos->getcolegio($dataRow['pk_usuario']);
    $dataRow['fk_tipodeingreso'] = $this->usuariosdatos->getTipoIngreso($dataRow['pk_usuario']);
    $dataRow['indice'] = $this->usuariosdatos->getPromedio($dataRow['pk_usuario']);

    $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
    $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
    $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
    $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$dataRow['pk_usuario']}'");

    $this->SwapBytes_Crud_Form->setJson($json);
    $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Usuario');
    $this->SwapBytes_Crud_Form->getView();

  }


  public function deleteloadAction() {
    $message = '¿Esta seguro que desea Eliminar este Usuario?';
    $permit = true;

    $hasrecord = $this->recordacademico->getCount($this->_params['modal']['id']);
    $dataRow = $this->Usuarios->getRow($this->_params['modal']['id']);

    if($hasrecord > 0){

      $message = 'Error al intentar eliminar usuario.';
      $msg = 'El usuario de Ci: ' . $this->_params['modal']['id'] . ' no puede ser eliminado ya que posee record academico o materias inscritas.';
      $json[]   = "$('.message').html('". $msg  ."')";
      $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
      $json[] = $this->SwapBytes_Jquery_Ui_Form->hideAllInputs('frmModal');
      $json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter('frmModal');

    }else{

      $dataRow['id'] = $this->_params['modal']['id'];
      $dataRow['colegio'] = $this->usuariosdatos->getcolegio($dataRow['pk_usuario']);
      $dataRow['fk_tipodeingreso'] = $this->usuariosdatos->getTipoIngreso($dataRow['pk_usuario']);
      $dataRow['indice'] = $this->usuariosdatos->getPromedio($dataRow['pk_usuario']);
    }
    $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Tutor', $message);
    $this->SwapBytes_Crud_Form->setWidthLeft('120px');
    $this->SwapBytes_Crud_Form->setJson($json);
    $this->SwapBytes_Crud_Form->getDeleteLoad($permit);
  }

  public function deletefinishAction() {

    $pk_usuario = $this->_params['modal']['id'];
    $perfil = 855;

    $udpk = $this->usuariosdatos->getPkFromUser($pk_usuario);
    $this->usuariosdatos->deleteRow($udpk);
    $ipk = $this->inscripciones->getPkporCedula($pk_usuario);
    $this->inscripciones->deleteRow($ipk);
    $this->grupo->deleteRow(null, $pk_usuario, $perfil);
    $this->Usuarios->deleteRow($pk_usuario);

    $this->SwapBytes_Crud_Form->setProperties($this->view->form);
    $this->SwapBytes_Crud_Form->getDeleteFinish();
  }

  public function SeparateNames($ci){

    $info = $this->profit->InfoEstudiante($ci);
    $names = explode(" ", trim($info['nombre']));
    foreach ($names as $key => $nam) {

      if(strlen(trim($names[$key - 1])) < 4 && ($key > 0) && ($key != 3) && ($key != 4)){
        $nombres[$key - 1] .= " " . trim($nam);
      }else if(strlen(trim($names[$key - 1])) < 4 && ($key == 3) && strlen(trim($names[2])) < 4){
        $nombres[$key - 2] .= " " . trim($nam);
      }else if($key == 2 && strlen(trim($names[$key])) < 4) {
        $nombres [$key - 1] .= " " . trim($nam);
      }else if(strlen(trim($names[$key - 1])) < 4 && ($key == 4) && strlen(trim($names[3])) < 4){
        $nombres[$key - 2] .= " " . trim($nam);
      }else if($key == 3 && strlen(trim($names[$key])) < 4){
        $nombres [$key - 1] .= " " . trim($nam);
      }else{
        $nombres[$key] = $nam;
      }
    }
    return $nombres;
  }
}