<?php

class Consultas_CoincidenciahorarioController extends Zend_Controller_Action {
    public function init() {
      
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Horarios');

        $this->Une_Filtros     = new Une_Filtros();
        $this->seguridad       = new Models_DbTable_UsuariosGrupos();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->sedes           = new Models_DbTable_Estructuras();
        $this->escuelas        = new Models_DbTable_EstructurasEscuelas();
        $this->RecordAcademico = new Models_DbTable_Recordsacademicos();
        $this->periodo         = new Models_DbTable_Periodos();
        $this->horario         = new Models_DbTable_Horarios();


        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();


        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        /*Filtros*/
        $this->_params['filters'] = $this->Une_Filtros->getParams();

      $this->Une_Filtros->setDisplay(true, true,true);
      $this->Une_Filtros->setRecursive(true, true,true);
       /*Botones de Acciones*/
      $this->SwapBytes_Crud_Action->setDisplay(true,true);
      $this->SwapBytes_Crud_Action->setEnable(true,true);

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

    //Acciones referidas al index
    public function indexAction() {
        $this->view->title = "Consultas \ Coincidencia de Horarios";
        $this->view->filters = $this->Une_Filtros;
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;

    }

  
  public function periodoAction() {
    $this->Une_Filtros->getAction();
  }

  public function sedeAction() {
    $this->Une_Filtros->getAction(array('periodo'));
  }

  public function escuelaAction() {
    $this->Une_Filtros->getAction(array('periodo', 'sede'));
  }

    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json = array();
            $searchData  = $this->_getParam('buscar');
            $info = $searchData;

            $ra_data = $this->horario->getHorarioCoincidencia($info,$this->_params['filters']['periodo'],$this->_params['filters']['sede'],$this->_params['filters']['escuela']);
            
            // Definimos las propiedades de la tabla.
            $ra_property_table = array('class'  => 'tableData',
                                       'width'  => '890px',
                                       'column' => 'disponible');

            $ra_property_column = array(array('name'     => 'C.I.',
                                               'column'   => 'ci',
                                               'width'    => '60px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'Apellidos',
                                               'column'   => 'apellido',
                                               'width'    => '225px',
                                               'rows'     => array('style' => 'text-align:left')),
                                         array('name'     => 'Nombres',
                                               'column'   => 'nombre',
                                               'width'    => '225px',
                                               'rows'     => array('style' => 'text-align:left')),
                                         array('name'     => 'Semestre',
                                               'column'   => 'semestre_1',
                                               'width'    => '40px',
                                               'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Materia',
                                               'column'   => 'materia_1',
                                               'width'    => '300px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'Seccion',
                                               'column'   => 'seccion_1',
                                               'width'    => '40px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'C.C.Semestre',
                                               'column'   => 'semestre_2',
                                               'width'    => '40px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'C.C.Materia',
                                               'column'   => 'materia_2',
                                               'width'    => '300px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name'     => 'Seccion',
                                               'column'   => 'seccion_2',
                                               'width'    => '40px',
                                               'rows'     => array('style' => 'text-align:center')),);
            // Generamos la lista.
            $HTML   = $this->SwapBytes_Crud_List->fill($ra_property_table, $ra_data, $ra_property_column);

            $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
              }
        }

    
}

