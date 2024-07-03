<?php

class Consultas_ListadocurriculumsController extends Zend_Controller_Action 
{

    public function init() 
    {
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Usuariosarchivos');
        
        $this->filtros                  = new Une_Filtros();
        $this->usuarios                 = new Models_DbTable_Usuarios();
        $this->grupo                    = new Models_DbTable_UsuariosGrupos();
        $this->archivos                 = new Models_DbTable_Usuariosarchivos();

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
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->Swapbytes_array          = new SwapBytes_Array();
        

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        
        $this->filtros->setDisplay(false);
        $this->filtros->setDisabled(false);
        $this->filtros->setRecursive(false);
        $this->SwapBytes_Crud_Action->setDisplay(true,true);
        $this->SwapBytes_Crud_Action->setEnable(true);
        $this->SwapBytes_Crud_Search->setDisplay(true); 

        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
      function preDispatch() {
          if (!Zend_Auth::getInstance()->hasIdentity()) 
          {
              $this->_helper->redirector('index', 'login', 'default');
          }

          if (!$this->grupo->haveAccessToModule()) 
          {
              $this->_helper->redirector('accesserror', 'profile', 'default');
          }
      }

    public function indexAction() 
    {
        $this->view->title                  = "Consultas / Curriculums";
        $this->view->filters                = $this->filtros;
        $this->view->module                 = $this->Request->getModuleName();
        $this->view->controller             = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery       = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax         = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Crud_Action  = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search  = $this->SwapBytes_Crud_Search;
    }

    public function listAction() 
    {
      if ($this->_request->isXmlHttpRequest()) 
      {
        $this->SwapBytes_Ajax->setHeader();
        $json           = array();
        $itemPerPage    = 15;
        $pageRange      = 10;

        $search         = $this->_params['filters']["txtBuscar"];
        $pageNumber     = $this->_getParam('page', 1);
        $searchData     = $this->_getParam('txtBuscar');
        
        $this->archivos->setSearch($search);
        $paginatorCount = $this->archivos->getSQLCount();
        $rows           = $this->archivos->getCurriculums($itemPerPage, $pageNumber); 

        // Definimos las propiedades de la tabla.
        $table = array('class'  => 'tableData',
                       'width'  => '700px',
                       'column' => 'disponible');
        $columns = array(array('column'  => 'pk_usuarioarchivo',
                               'primary' => true,
                               'hide'    => true),
                         array('name'     => '#',
                               'width'    => '20px',
                               'function' => 'rownum',
                               'rows'     => array('style' => 'text-align:center')
                               ),
                         array('name'     => 'C.I.',
                               'column'   => 'cedula',
                               'width'    => '80px',
                               'rows'     => array('style' => 'text-align:center')
                               ),
                         array('name'     => 'Nombre',
                               'column'   => 'nombre',
                               'width'    => '225px',
                               'rows'     => array('style' => 'text-align:center')
                               ),
                         array('name'     => 'Apellido',
                               'column'   => 'apellido',
                               'width'    => '225px',
                               'rows'     => array('style' => 'text-align:center')
                               ),
                         array('name'     => 'Fecha',
                               'column'   => 'fecha',
                               'width'    => '90px',
                               'rows'     => array('style' => 'text-align:center')
                               )
                         );
        // Generamos la lista.
        $HTML   = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount,'V');
        $json[] = $this->SwapBytes_Jquery->setHtml('tblUsuarios', $HTML);
        $this->getResponse()->setBody(Zend_Json::encode($json));
      }
    }

    public function viewAction()
    {
      $this->SwapBytes_Ajax->setHeader();
      $id = $this->_getParam('id',0);
      if(is_numeric($id) and $id > 0)
      {
        $file   = $this->archivos->getCurriculumRow($id);
        $url    = $file['ruta'];
        $json[] = "$('#cv').attr('src','".$url."')";
        $json[] = "$('#btnCV').attr('href','".$url."')";
        $json[] = "$('#modalCV').modal('show')";
      }
      $this->getResponse()->setBody(Zend_Json::encode($json));
    }

}

?>
