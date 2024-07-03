<?php
class Transactions_MisvacantesController extends Zend_Controller_Action {

  private $Title              = 'Transactions / Mis Vacantes';
  private $FormTitle_View     = "Ver Vacante";
  private $FormTitle_Eliminar = "Eliminar Vacante";

  public function init() {
    /* Initialize action controller here */
    Zend_Loader::loadClass('Models_DbTable_Usuarios');  
    Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
    Zend_Loader::loadClass('Models_DbTable_Instituciones');
    Zend_Loader::loadClass('Models_DbTable_Vacantes');
    Zend_Loader::loadClass('Models_DbTable_Postulaciones');
    Zend_Loader::loadClass('Models_DbTable_Solicitudesempleadores');  
    Zend_Loader::loadClass('Models_DbTable_Atributos');
    Zend_Loader::loadClass('Forms_Vacante');
    Zend_Loader::loadClass('Une_Filtros');

    $this->usuario                  = new Models_DbTable_Usuarios();
    $this->grupo                    = new Models_DbTable_UsuariosGrupos();
    $this->instituciones            = new Models_DbTable_Instituciones();
    $this->vacantes                 = new Models_DbTable_Vacantes();
    $this->postulaciones            = new Models_DbTable_Postulaciones();
    $this->solicitudempleador       = new Models_DbTable_Solicitudesempleadores();
    $this->atributos                = new Models_DbTable_Atributos();
    $this->Une_Filtros              = new Une_Filtros();
    $this->current_user             = new Zend_Session_Namespace('Zend_Auth');

    $this->Request                  = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->form               = new Forms_Vacante();

    $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
    $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
    $this->SwapBytes_Html           = new SwapBytes_Html();
    $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
    $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
    $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
    $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
    $this->SwapBytes_Form           = new SwapBytes_Form();
    $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
    $this->SwapBytes_Date           = new SwapBytes_Date();
    $this->SwapBytes_Uri            = new SwapBytes_Uri();
    $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
    $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
    $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
    $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
    $this->Swapbytes_array          = new SwapBytes_Array();
    $this->CmcBytes_Filtros         = new CmcBytes_Filtros();

    $this->Une_Filtros->setDisplay(false);
    $this->Une_Filtros->setRecursive(false);
    $this->SwapBytes_Crud_Action->setDisplay(true,true);
    $this->SwapBytes_Crud_Action->setEnable(true,true);
    $this->SwapBytes_Crud_Search->setDisplay(true);

    $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
    $customFilters = array(
      array(
        'id' => 'empresa',
        'name' => 'selEmpresa',
        'label' => 'Empresa',
        'recursive' => true,
      )
    );
    $this->Une_Filtros->addCustom($customFilters);
    $params = $this->Une_Filtros->getParams();

    $this->SwapBytes_Form->set($this->view->form);
    $this->SwapBytes_Form->fillSelectBox('fk_contrato',$this->atributos->getTipes(103,NULL),'pk_atributo','valor');
    $this->SwapBytes_Form->fillSelectBox('sexo',$this->atributos->getTipes(104,NULL),'pk_atributo','valor');
    $this->view->form = $this->SwapBytes_Form->get();
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
    $this->view->titulo                   = $this->Title;
    $this->view->filters                  = $this->Une_Filtros;
    $this->view->SwapBytes_Ajax           = $this->SwapBytes_Ajax;
    $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
    $this->view->SwapBytes_Jquery_Mask    = $this->SwapBytes_Jquery_Mask;
    $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
    $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
    $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
    $this->view->SwapBytes_Jquery_Ui_Form = $this->SwapBytes_Jquery_Ui_Form;
    $this->view->SwapBytes_Ajax->setView($this->view);
  }

  public function empresaAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $empresas = $this->instituciones->getEmpresaByEmpleador($this->current_user->userId);
      $this->SwapBytes_Ajax_Action->fillSelect($empresas);
    }
  } 

  public function listAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $pageNumber  = $this->_getParam('page', 1);
      $searchData  = $this->_getParam('buscar');
      $params      = $this->Une_Filtros->getParams(null,array('empresa','vacante','selDateDesde','selDateHasta'));
      $itemPerPage = 15;
      $pageRange   = 10;
      // Definimos los valores
      $this->vacantes->setSearch($searchData);
      $paginatorCount = $this->vacantes->getSQLCount($params['selDateDesde'],$params['selDateHasta'],$params['empresa']);
      $rows           = $this->vacantes->getMisVacantes($itemPerPage,$pageNumber,$params['empresa'],$params['selDateDesde'],$params['selDateHasta']);
      // Definimos las propiedades de la tabla.
      $table = array('class' => 'tableData',
                     'width' => '870px');
      $columns = array(array('column'  => 'pk_vacante',
                             'primary' => true,
                             'hide'    => true),
                       array('name'    => 'Empresa',
                             'width'   => '150px',
                             'column'  => 'empresa',
                             'rows'    => array('style' => 'text-align:left')),
                       array('name'    => 'Titulo',
                             'width'   => '250px',
                             'column'  => 'title',
                             'rows'    => array('style' => 'text-align:left')),
                       array('name'    => 'Contrato',
                             'width'   => '80px',
                             'column'  => 'contrato',
                             'rows'    => array('style' => 'text-align:center')),
                       array('name'    => 'Vacantes',
                             'width'   => '50px',
                             'column'  => 'vacantes',
                             'rows'    => array('style' => 'text-align:center')),
                       array('name'    => 'Genero',
                             'width'   => '70px',
                             'column'  => 'sexo',
                             'rows'    => array('style' => 'text-align:center')),
                       array('name'    => 'Edad Minima',
                             'width'   => '20px',
                             'column'  => 'edad',
                             'rows'    => array('style' => 'text-align:center')),
                       array('name'    => 'Fecha Publicacion',
                             'width'   => '65px',
                             'column'  => 'publicacion',
                             'rows'    => array('style' => 'text-align:center')),
                       array('name'    => 'Fecha Culminacion',
                             'width'   => '65px',
                             'column'  => 'culminacion',
                             'rows'    => array('style' => 'text-align:center')));
      $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'VUD');
      $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
      $this->getResponse()->setBody(Zend_Json::encode($json));
    }
  }

  private function getData($id) {
    $dataRow = $this->vacantes->getVacante($id);
    if(isset($dataRow)) {
      return $dataRow[0];
    }
  }

  public function addoreditloadAction() {
    // Obtenemos los parametros que se esperan recibir.
    $id = $this->_getParam('id',0);
    if(is_numeric($id) && $id > 0) {
        // Agregamos algunos valores al formulario.
        $dataRow         = $this->getData($id);
        $dataRow['page'] = $this->_getParam('page', 1);
        $enable = false;
        $title = $this->FormTitle_Modificar;
    } else {
        $enable = true;
        $title = $this->FormTitle_Agregar;
    }
    $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
    $this->SwapBytes_Crud_Form->enableElement('empresa', $enable);
    $this->SwapBytes_Crud_Form->setWidthLeft('130px');
    $this->SwapBytes_Crud_Form->setJson(array($this->SwapBytes_Jquery_Mask->datePicker('culminacion',$dataRow['culminacion'])));
    $this->SwapBytes_Crud_Form->getAddOrEditLoad();
  }

  public function addoreditconfirmAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $queryString        = $this->_getParam('data');
      $dataRow            = $this->SwapBytes_Uri->queryToArray($queryString);
      $vacante            = $this->vacantes->getVacante($dataRow['id_vacante']);
      $dataRow['empresa'] = $vacante[0]['empresa'];
      $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow);
      $this->SwapBytes_Crud_Form->setWidthLeft('130px');
      $this->SwapBytes_Crud_Form->setJson(array($this->SwapBytes_Jquery_Mask->datePicker('culminacion',$dataRow['culminacion'])));
      $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
    }
  }

  public function addoreditresponseAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $pageNumber                   = $this->_getParam('page', 1);
      $queryString                  = $this->_getParam('data');
      $dataRow                      = $this->SwapBytes_Uri->queryToArray($queryString);
      $vacante                      = $this->vacantes->getVacante($dataRow['id_vacante']);
      $id                           = $dataRow['id_vacante'];
      $dataRow['fk_sexo']           = $dataRow['sexo'];
      $dataRow['fecha_culminacion'] = $dataRow['culminacion'];
      unset($dataRow['id_vacante'],$dataRow['sexo'],$dataRow['culminacion']);
      // Existe el registro se actualiza.
      if(isset($id) && $id > 0) {
        $this->vacantes->updateRow($id,$dataRow);
      }
      $this->SwapBytes_Crud_Form->getAddOrEditEnd();
    }
  }

  public function deleteloadAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $pageNumber   = $this->_getParam('page',1);
      $id           = $this->_getParam('id',0);
      $dataRow      = $this->getData($id);
      $permit       = $this->_getDependencies();
      if($permit) {
        $message = 'No se puede eliminar la vacante por que<br>tiene dependencias en postulaciones y/o otros perfiles.';
      }
      // Agregamos los datos necesarios para los controles de tipo HIDDEN, los cuales son necesarios para poder eliminar el registro deseado.
      $dataRow['page']       = $pageNumber;
      $dataRow['id'] = intval($id);
      $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $this->FormTitle_Eliminar);
      $this->SwapBytes_Crud_Form->setWidthLeft('80px');
      $this->SwapBytes_Crud_Form->getDeleteLoad(!$permit);
    }
  }

  public function deletefinishAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $pageNumber = $this->_getParam('page',1);
      $id         = intval(str_replace("id_vacante=", "", $this->_getParam('data')));
      $permit = $this->_getDependencies();
      if(!$permit) {
        if(is_numeric($id) && $id > 0) {
          $this->vacantes->deleteRow($id);
        }
      }
      $this->SwapBytes_Crud_Form->setProperties($this->view->form);
      $this->SwapBytes_Crud_Form->getDeleteFinish();
    }
  }

  public function viewAction() {
    // Obtenemos los parametros que se esperan recibir.
    $id = $this->_getParam('id', 0);
    // Buscar los datos del registro seleccionado.
    $dataRow = $this->getData($id);
    $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $this->FormTitle_View);
    $this->SwapBytes_Crud_Form->setWidthLeft('130px');
    $this->SwapBytes_Crud_Form->getView();
  }

  private function _getDependencies() {
    $id_row = $this->_getParam('id'  , 0);
    $dataRow_postulaciones = $this->postulaciones->getCount($id_row);
    $this->dependencies['postulaciones'] = $dataRow_postulaciones;
    foreach($this->dependencies as $dependency) {
      if($dependency > 0) {
        return true;
      }
    }  
    return false;
  }

}
?>