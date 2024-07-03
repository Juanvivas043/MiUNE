<?php

class Transactions_VertesistasController extends Zend_Controller_Action {

private $_Title   = 'Transacciones \ Ver Tesistas';
	

	public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios'); 
        Zend_Loader::loadClass('Models_DbTable_Pasantes'); 
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Tesis');
        Zend_Loader::loadClass('Models_DbTable_Materiasestados');
        Zend_Loader::loadClass('Models_DbView_Grupos');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Forms_OtroTesista');
        
        $this->Usuarios         = new Models_DbTable_Usuarios();
        $this->Pasantes         = new Models_DbTable_Pasantes();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->recordacademico  = new Models_DbTable_Recordsacademicos();
        $this->tesis           = new Models_DbTable_Tesis();
        $this->materiasestados = new Models_DbTable_Materiasestados();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->filtros          = new Une_Filtros();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();
        $this->vw_grupos        = new Models_DbView_Grupos();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->AuthSpace                = new Zend_Session_Namespace('Zend_Auth');  
        $this->view->form = new Forms_OtroTesista();
        $this->SwapBytes_Form->set($this->view->form);
        $this->view->form = $this->SwapBytes_Form->get();
        $this->tablas = Array(
                              'Periodo'  => Array('tbl_periodos',
                                                  null,
                                                  Array('pk_periodo',
                                                        'lpad(pk_periodo::text, 4, \'0\') || \', \' || to_char(fechainicio, \'MM-yyyy\') || \' / \' ||  to_char(fechafin, \'MM-yyyy\')'),
                                                  'DESC'),

                              'Sede'    => Array('vw_sedes',
                                                 null     ,
                                                 Array('pk_estructura',
                                                       'nombre'),
                                                 'DESC'),

                              'Escuela' => Array(Array('tbl_estructurasescuelas ee',
                                                       'vw_escuelas es'),
                                                 Array('ee.fk_atributo = es.pk_atributo',
                                                       'ee.fk_estructura = ##Sede##'),//'fk_estructura = 7','fk_estructura = ##sede##',
                                                 Array('ee.fk_atributo',
                                                       'es.escuela'),
                                                 'ASC'),

                              'Pensum'  => Array(Array('tbl_pensums'),
                                                  Array('fk_escuela = ##Escuela##'),
                                                  Array('pk_pensum',
                                                        'nombre'),
                                                  'ASC'),
                              'Materia'  => Array(Array('tbl_asignaturas 	asi', 
                              							'vw_materias 		vma'),
                                                  Array('asi.fk_materia = vma.pk_atributo',
                                                  		'asi.fk_pensum = ##Pensum##',
                                                  		'asi.fk_materia in (519,830,834,10621,9719,9723,9724)'),
                                                  Array('pk_atributo',
                                                        'materia'),
                                                  'ASC'));
    $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters')); 
    //      BOTONES DE ACCIONES          
    $this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);
    $this->SwapBytes_Crud_Action->setEnable(true, true, false, true, false, false);
    $this->SwapBytes_Crud_Search->setDisplay(true); 
	}

  function preDispatch() {
    if(!Zend_Auth::getInstance()->hasIdentity()) {
      $this->_helper->redirector('index', 'login', 'default'); 
    }
    if (!$this->grupo->haveAccessToModule()) {
      $this->_helper->redirector('accesserror', 'profile', 'default');
    }    
  }

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

  public function listAction(){
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $datos = $this->_getAllParams();
      $filtro = $this->SwapBytes_Uri->queryToArray($datos['filters']);
      if(!empty($filtro['Materia'])){
        $rows = $this->tesis->getTesistasByMateria($filtro['Periodo'],$filtro['Sede'],$filtro['Escuela'],$filtro['Pensum'],$filtro['Materia'],$filtro['txtBuscar']);  
      }
      if(isset($rows) && count($rows) > 0) {
  			$table = array('class' => 'tableData',
                             'width' => '600px');
        $columns = array(array('column'  => 'pk_usuario',
                               'primary' => true,
                               'hide'    => true),
                         array('name'    => 'C.I.',
                                   'width'   => '70px',
                                   'column'  => 'pk_usuario',
                                   'rows'    => array('style' => 'text-align:center')),
                         array('name'    => 'Nombre',
                               'width'   => '200px',
                               'rows'    => array('style' => 'text-align:center'),
                               'column'  => 'nombre'),
                         array('name'    => 'Apellido',
                               'width'   => '200px',
                               'rows'    => array('style' => 'text-align:center'),
                               'column'  => 'apellido') 
                      );
        $HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'V',$other);
        $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
      }else{
        $HTML = $this->SwapBytes_Html_Message->alert("No Existen Tesistas Inscritos");
        $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
      }
      $this->getResponse()->setBody(Zend_Json::encode($json));        
    }
  }

  public function viewAction() {
      $this->SwapBytes_Ajax->setHeader();
      
      $cod = $this->_getParam('id');

      $dataRow['pk_usuario']  = $cod;
      $dataRow = $this->Usuarios->getRow($cod);
      $dataRow['sexo']            = $this->SwapBytes_Form->setValueToBoolean($dataRow['sexo']);
      $dataRow['nacionalidad']    = $this->SwapBytes_Form->setValueToBoolean($dataRow['nacionalidad']);
      $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);

      $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
      $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
      $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
      $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$dataRow['pk_usuario']}'");

      $this->SwapBytes_Crud_Form->setJson($json); 
      $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Tesista');
      $this->SwapBytes_Crud_Form->getView();
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

}