<?php 

  class Reports_RendimientoacademicoController extends Zend_Controller_Action {

    public function init() {
        //instanciar clases
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Horarios');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Descargables');
        Zend_Loader::loadClass('Zend_Pdf');        


        //instanciar models
        $this->Usuarios         = new Models_DbTable_Usuarios();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->Record           = new Models_DbTable_Recordsacademicos();
        $this->Inscripcion      = new Models_DbTable_Inscripciones();
        $this->Horario          = new Models_DbTable_Horarios();
        $this->atributos        = new Models_DbTable_Atributos();
        $this->inscripciones    = new Models_DbTable_Inscripciones();
        $this->Periodo          = new Models_DbTable_Periodos();
        $this->Descargables     = new Models_DbTable_Descargables();
        $this->pdf              = new Zend_Pdf();
        $this->Une_Filtros      = new Une_Filtros();

        //se instancia request que es el que maneja los _params
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
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->Swapbytes_array          = new SwapBytes_Array();
        $this->CmcBytes_Filtros         = new CmcBytes_Filtros();

      /*Filtros*/
      $this->Une_Filtros->setDisplay(true,true,true);
      $this->Une_Filtros->setRecursive(true,true,true);
      /*Botones de Acciones*/
      $this->SwapBytes_Crud_Action->setDisplay(true,false);
      $this->SwapBytes_Crud_Action->setEnable(true,false);
      $this->SwapBytes_Crud_Search->setDisplay(false);

      $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
      $this->_params['checkbox'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('checkbox'));


       $customFilters = array(
        array(
          'id' => 'tipo',
          'name' => 'selTipo',
          'label' => 'Tipo',
          'recursive' => true
          
        )    
      );

 $this->Une_Filtros->addCustom($customFilters);

}


   function preDispatch() {
      if (!Zend_Auth::getInstance()->hasIdentity()) {
        $this->_helper->redirector('index', 'login', 'default');
      }
      if (!$this->grupo->haveAccessToModule()) {
        $this->_helper->redirector('accesserror', 'profile', 'default');
      }
    }


    public function indexAction() {

      $this->view->title   = 'Reportes \ Rendimiento Academico';
      $this->view->filters = $this->Une_Filtros;
      $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
      $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
      $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
      $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
      $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
      $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();

    }

    public function periodoAction() {
        $this->Une_Filtros->getAction();
    }

  public function sedeAction() {
    $this->Une_Filtros->getAction(array());
  
  }

    public function escuelaAction() {
    $this->Une_Filtros->getAction(array());
  
  }

    public function pensumAction() {
    $this->Une_Filtros->getAction(array());
  
  }

  public function tipoAction() {
   
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $rendimiento = $this->atributos->getRendimiento();
      $this->SwapBytes_Ajax_Action->fillSelect($rendimiento);
      
    }
  }

    public function listAction(){
            if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json = array();

            $searchCheck = $this->_getParam('checkbox');
            //var_dump($searchCheck);die;
            
            // Definimos las propiedades de la tabla.
            $table = array('class'  => 'tableData',
                           'width'  => '1200px',
                           'column' => 'disponible');

            $table1 = array(array('column'   => 'pk_usuario',
                                   'primary'  => true,
                                   'hide'     => true
                                   ),
                             array('name'     => '#',
                                   'width'    => '20px',
                                   'function' => 'rownum',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'C.I.',
                                   'column'   => 'pk_usuario',
                                   'width'    => '150px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Apellidos',
                                   'column'   => 'apellido',
                                   'width'    => '225px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Nombres',
                                   'column'   => 'nombre',
                                   'width'    => '225px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Indice Periodo real',
                                   'column'   => 'iap',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Indice Acumulado real',
                                   'column'   => 'iaa',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   )); 

            $table2 = array(array('column'   => 'pk_usuario',
                                   'primary'  => true,
                                   'hide'     => true
                                   ),
                             array('name'     => '#',
                                   'width'    => '20px',
                                   'function' => 'rownum',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Codigo Propietario',
                                   'column'   => 'codigopropietario',
                                   'width'    => '150px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Semestre',
                                   'column'   => 'semestre',
                                   'width'    => '225px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Materia',
                                   'column'   => 'materia',
                                   'width'    => '225px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Seccion',
                                   'column'   => 'pensum',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Pensum',
                                   'column'   => 'seccion',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Profesor',
                                   'column'   => 'nombre',
                                   'width'    => '475px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Ins.T.',
                                   'column'   => 'total',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Apro.',
                                   'column'   => 'aprobados',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Apro%.',
                                   'column'   => 'poraprobados',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Reti.',
                                   'column'   => 'retirados',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Reti%.',
                                   'column'   => 'porretirados',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Aplaz.',
                                   'column'   => 'aplazados',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Aplaz%',
                                   'column'   => 'poraplazados',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Aplaz.',
                                   'column'   => 'aplazados',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Calif.Pro.',
                                   'column'   => 'califpro',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ));

               $table3  = array(array('column'   => 'pk_usuario',
                                   'primary'  => true,
                                   'hide'     => true
                                   ),
                             array('name'     => '#',
                                   'width'    => '20px',
                                   'function' => 'rownum',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Codigo Propietario',
                                   'column'   => 'codigopropietario',
                                   'width'    => '150px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Semestre',
                                   'column'   => 'semestre',
                                   'width'    => '225px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Materia',
                                   'column'   => 'materia',
                                   'width'    => '225px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Pensum',
                                   'column'   => 'seccion',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Ins.T.',
                                   'column'   => 'total',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Apro.',
                                   'column'   => 'aprobados',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Apro%.',
                                   'column'   => 'poraprobados',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Reti.',
                                   'column'   => 'retirados',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Reti%.',
                                   'column'   => 'porretirados',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Aplaz.',
                                   'column'   => 'aplazados',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Aplaz%',
                                   'column'   => 'poraplazados',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Aplaz.',
                                   'column'   => 'aplazados',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Calif.Pro.',
                                   'column'   => 'califpro',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ));

            if ($this->_params['filters']["selTipo"]=="20105") {      // Cuadro De Honor

                $rows = $this->Record->getCuadroHonor($this->_params['filters']["selPeriodo"],$this->_params['filters']["selSede"],$this->_params['filters']['selEscuela']);
                $columns =$table1;

            }elseif ($this->_params['filters']["selTipo"]=="20106") { // Regulares

                $rows = $this->Record->getRegulares($this->_params['filters']["selPeriodo"],$this->_params['filters']["selSede"],$this->_params['filters']['selEscuela']);
                $columns =$table1;

            }elseif ($this->_params['filters']["selTipo"]=="20107") { //P.I.R.A

                $rows = $this->Record->getPeriodoPrueba($this->_params['filters']["selPeriodo"],$this->_params['filters']["selSede"],$this->_params['filters']['selEscuela']); 
                $columns =$table1;

            }elseif ($this->_params['filters']["selTipo"]=="20108") { //Retiro Definitivo

                $rows = $this->Record->getRetiroDefinitivo($this->_params['filters']["selPeriodo"],$this->_params['filters']["selSede"],$this->_params['filters']['selEscuela']); 
                $columns =$table1;

            }elseif($this->_params['filters']["selTipo"]=="20109"){ //Por Materia
                
                if($searchCheck == "filtro=todos"){

                  $rows = $this->Record->getPorMateriaTodos($this->_params['filters']["selPeriodo"],$this->_params['filters']["selSede"],$this->_params['filters']['selEscuela']); 
                  $columns =$table3;

                }elseif($searchCheck == "filtro=aplaz"){ //30% de Aplazados por materia

                $rows = $this->Record->getPorMateriaPorcentaje($this->_params['filters']["selPeriodo"],$this->_params['filters']["selSede"],$this->_params['filters']['selEscuela']); 
                $columns =$table3;

                }

              }elseif ($this->_params['filters']["selTipo"]=="20110") { //Por Materia y Profesor

                 if($searchCheck == "filtro=todos"){

                $rows = $this->Record->getPorMateriaProfesorTodos($this->_params['filters']["selPeriodo"],$this->_params['filters']["selSede"],$this->_params['filters']['selEscuela']); 
                $columns =$table2;

              }elseif($searchCheck == "filtro=aplaz"){ //30% de Aplazados por materia y profesor

                $rows = $this->Record->getPorMateriaProfesorPorcentaje($this->_params['filters']["selPeriodo"],$this->_params['filters']["selSede"],$this->_params['filters']['selEscuela']); 
                $columns =$table2;

                }
            }          

            // Generamos la lista dependiendo del tipo

            $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
            $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
          

        }

      }
    }