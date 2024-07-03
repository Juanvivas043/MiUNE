<?php
class Consultas_ListadopostulacionesController extends Zend_Controller_Action {

  private $Title          = 'Reports / Listado Postulaciones';
  private $FormTitle_View = "Ver Postulado";

  public function init() {
    /* Initialize action controller here */
    Zend_Loader::loadClass('Models_DbTable_Usuarios');  
    Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
    Zend_Loader::loadClass('Models_DbTable_Instituciones');
    Zend_Loader::loadClass('Models_DbTable_Vacantes');
    Zend_Loader::loadClass('Models_DbTable_Postulaciones');
    Zend_Loader::loadClass('Forms_Postulacion');
    Zend_Loader::loadClass('Une_Filtros');

    $this->usuario                  = new Models_DbTable_Usuarios();
    $this->grupo                    = new Models_DbTable_UsuariosGrupos();
    $this->instituciones            = new Models_DbTable_Instituciones();
    $this->vacantes                 = new Models_DbTable_Vacantes();
    $this->postulaciones            = new Models_DbTable_Postulaciones();
    $this->Une_Filtros              = new Une_Filtros();
    $this->current_user             = new Zend_Session_Namespace('Zend_Auth');

    $this->Request                  = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->form               = new Forms_Postulacion();

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
      ),
      array(
        'id' => 'vacante',
        'name' => 'selVacante',
        'label' => 'Vacante',
        'recursive' => true,
      )
    );
    $this->Une_Filtros->addCustom($customFilters);
    $params = $this->Une_Filtros->getParams();
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

  public function vacanteAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $params   = $this->Une_Filtros->getParams(null,array('empresa'));
      $vacantes = $this->vacantes->getVacantesByEmpresa($params['empresa']);
      $this->SwapBytes_Ajax_Action->fillSelect($vacantes);
    }
  } 

  public function listAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $pageNumber  = $this->_getParam('page', 1);
      $searchData  = $this->_getParam('buscar');
      $itemPerPage = 15;
      $pageRange   = 10;
      $params      = $this->Une_Filtros->getParams(null,array('empresa','vacante','selDateDesde','selDateHasta'));
      // Definimos los valores de la Busqueda
      $this->vacantes->setSearch($searchData);
      // Arreglamos fechas
      if(!empty($params["selDateDesde"]) or !empty($params["selDateHasta"])) {
        if(empty($params["selDateDesde"])) {
          $params["selDateDesde"] = date("Y-m-d");
        }
        if(empty($params["selDateHasta"])) {
          $params["selDateHasta"] = date("Y-m-d");
        }
        if($params["selDateDesde"] > $params["selDateHasta"]) {
          $params["selDateDesde"] = $params["selDateHasta"];
        }
      }
      $paginatorCount = $this->postulaciones->getSQLCount($params['empresa'],$params['vacante'],$params['selDateDesde'],$params['selDateHasta']);
      $rows           = $this->postulaciones->getPostulaciones($itemPerPage, $pageNumber,$params['empresa'],$params['vacante'],$params['selDateDesde'],$params['selDateHasta']);
      // Definimos las propiedades de la tabla.
      $table = array('class' => 'tableData',
                     'width' => '1000px');
      $columns = array(array('column'  => 'pk_postulacion',
                             'primary' => true,
                             'hide'    => true),
                       array('name'    => 'Cedula',
                             'width'   => '70px',
                             'column'  => 'cedula',
                             'rows'    => array('style' => 'text-align:center')),
                       array('name'    => 'Postulante',
                             'width'   => '300px',
                             'column'  => 'postulado',
                             'rows'    => array('style' => 'text-align:center')),
                       array('name'    => 'Correo',
                             'width'   => '250px',
                             'column'  => 'correo',
                             'rows'    => array('style' => 'text-align:center')),
                       array('name'    => 'Escuela',
                             'width'   => '250px',
                             'column'  => 'escuela',
                             'rows'    => array('style' => 'text-align:center')),
                       array('name'    => 'Fecha Postulacion',
                             'width'   => '65px',
                             'column'  => 'fecha_postulacion',
                             'rows'    => array('style' => 'text-align:center')));
      $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'V');
      $json[] = $this->SwapBytes_Jquery->setHtml('tblPostulaciones', $HTML);
      $this->getResponse()->setBody(Zend_Json::encode($json));
    }
  }

  private function getData($id) {
    $dataRow = $this->postulaciones->getPostulacion($id);
    if(isset($dataRow)) {
      return $dataRow[0];
    }
  }

  public function viewAction() {
    $id      = $this->_getParam('id', 0);
    $dataRow = $this->getData($id);
    $cv_path = $dataRow['cv'];
    //Debe apuntar a controller
    $url     = $_SERVER['HTTP_HOST'].substr($cv_path,2);
    $content = "<div><iframe id=\"content\" src=\"http://$url\" style=\"width:80em; height:50em;\" frameborder=\"0\"></iframe></div>";
    $this->SwapBytes_Crud_Form->setProperties($this->view->form,$dataRow,$this->FormTitle_View);
    $this->SwapBytes_Crud_Form->setJson(
      array(
        "$(\"#correo\").removeAttr('disabled'); $(\"#telefono\").removeAttr('disabled'); $(\"#celular\").removeAttr('disabled'); $(\"#cv_btn\").removeAttr('disabled'); ",
        "$(\"#frmModal\").dialog({ 
            buttons: [
              { 
                text: \"Ver Curriculum Vitae\",
                \"class\": \"btn_cv\",
                click: function(){
                  $(\"#frmDialog\").dialog(\"open\");
                  $(\"#frmDialog\").dialog(\"option\",\"hide\", { effect: \"clip\", duration: 1000 } );
                  $(\"#frmDialog\").dialog({ 
                    buttons: { 
                      \"Descargar\": function(){ 
                        if(!$(\".invalid\").length){ 
                          window.open(urlAjax + 'document?p=$id');
                        }
                      },
                      \"Abrir\": function(){ 
                        if(!$(\".invalid\").length){ 
                          window.open('$cv_path');
                        }
                      },
                      \"Cancelar\": function(){ 
                        if(!$(\".invalid\").length){ 
                          $(\"#frmDialog\").dialog(\"close\"); 
                        }
                      }
                    }
                  });
                  $(\"#frmDialog\").html('$content');
                  $(\"#frmDialog\").dialog({title: \"Curriculum Vitae\"});
                  $(\"#frmDialog\").dialog('option','position','center');
                }
              },
              {
                text: \"Cancelar\", 
                click: function(){ 
                  if(!$(\".invalid\").length){ $(\"#frmModal\").dialog(\"close\"); }
                }
              }
            ]
          });",
        $this->SwapBytes_Jquery_Mask->phone('telefono'),
        $this->SwapBytes_Jquery_Mask->phone('celular')
        )
      );
    $this->SwapBytes_Crud_Form->setWidthLeft('130px');
    $this->SwapBytes_Crud_Form->getView();
  }

  public function documentAction(){
    $id      = $_GET['p'];
    $dataRow = $this->getData($id);
    $cv      = substr($dataRow['cv'], 3);
    $ext     = substr($cv,strpos($cv,".") + 1);
    $name    = $dataRow['cedula']."_".strtoupper(str_replace(" ","_",$dataRow['postulado']));
    switch ($ext) {
      case 'pdf':
        $app  = "application";
        break;
      case 'jpeg':
        $app  = "image";
        break;
      default:
        $app  = "application";
        break;
    }
    // ruta del cv 
    header("Content-type:$app/$ext");
    // It will be called downloaded.pdf
    header("Content-Disposition:attachment;filename=$name.$ext");
    // The PDF source is in original.pdf
    readfile($cv);
    die;    
  }

}
?>