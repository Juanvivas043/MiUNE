<?php

class Consultas_ListadocambioescuelaController extends Zend_Controller_Action {
    public function init() {
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        
        $this->filtros         = new Une_Filtros();
        $this->seguridad       = new Models_DbTable_UsuariosGrupos();
        $this->Usuarios        = new Models_DbTable_Usuarios();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->atributos       = new Models_DbTable_Atributos();

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
        
        $this->filtros->setDisplay(true);
        $this->filtros->setDisabled(false);
        $this->filtros->setRecursive(false);
        $this->SwapBytes_Crud_Action->setDisplay(true,false);
        $this->SwapBytes_Crud_Action->setEnable(true);
        $this->SwapBytes_Crud_Search->setDisplay(false); 

        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
       

      }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
     function preDispatch() {
         if (!Zend_Auth::getInstance()->hasIdentity()) {
             $this->_helper->redirector('index', 'login', 'default');
         }

         if (!$this->seguridad->haveAccessToModule()) {
             $this->_helper->redirector('accesserror', 'profile', 'default');
         }
     }

    public function indexAction() {
        $this->view->title                  = "Consultas / Cambio de Escuela";
        $this->view->filters                = $this->filtros;
        $this->view->module                 = $this->Request->getModuleName();
        $this->view->controller             = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery       = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax         = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Crud_Action  = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search  = $this->SwapBytes_Crud_Search;
    }

    public function periodoAction(){
        $this->filtros->getAction();
    }

    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json = array();
            //var_dump($this->_params['filters']["selPeriodo"]);die;
            $PeriodoActual = $this->_params['filters']["selPeriodo"] ; //Obtiene el periodo
            $HtmlObjectName = 'pk_usuario';
            $rows = $this->Usuarios->getCambioEscuela($PeriodoActual); 

            }
            
            // Definimos las propiedades de la tabla.
            $table = array('class'  => 'tableData',
                           'width'  => '1000px',
                           'column' => 'disponible');

            $columns = array(array('name'     => '#',
                                   'width'    => '20px',
                                   'function' => 'rownum',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'C.I.',
                                   'column'   => 'pk_usuario',
                                   'width'    => '100px',
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
                             array('name'     => 'Sem Ubicacion',
                                   'column'   => 'sem_ubic',
                                   'width'    => '100px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Escuela Anterior',
                                   'column'   => 'esc1',
                                   'width'    => '370px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Escuela Actual',
                                   'column'   => 'esc2',
                                   'width'    => '370px',
                                   'rows'     => array('style' => 'text-align:center')
                                   )
                             );

            // Generamos la lista.
            $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
            $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }

