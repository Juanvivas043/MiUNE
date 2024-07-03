<?php

class Consultas_NominaprofesoresController extends Zend_Controller_Action {
    public function init() {
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Horarios');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        

        $this->filtros         = new Une_Filtros();
        $this->seguridad       = new Models_DbTable_UsuariosGrupos();
        $this->Usuarios        = new Models_DbTable_Usuarios();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->Horario         = new Models_DbTable_Horarios();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->sedes           = new Models_DbTable_Estructuras();
        $this->escuelas        = new Models_DbTable_EstructurasEscuelas();
        $this->RecordAcademico = new Models_DbTable_Recordsacademicos();

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

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        
        $this->filtros->setDisplay(true, true, true, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, true, true, false, false, false, false, false, false);
        $this->filtros->setRecursive(false, true, true, false, false, false, false, false, false);
        $this->SwapBytes_Crud_Action->setDisplay(true, false, false, false, false, false);
        $this->SwapBytes_Crud_Action->setEnable(true, true, false, true, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(false); 

        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
        // Se añade el nuevo Filtro
        $customFilters =  array(array(
                                  'id'=> 'tipo',
                                  'name'=>'selTipo',
                                  'recursive'=> true,
                                  'label'=>'Tipo'));
        $this->filtros->addCustom($customFilters);

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
        $this->view->title                  = "Reportes \ Nomina de Profesores";
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
    public function sedeAction() {
        $this->filtros->getAction(array('periodo'), null);
    }

    public function escuelaAction() {
        $dataRows = $this->escuelas->getSelect($this->Request->getParam('sede'));
        array_unshift($dataRows, array("pk_atributo"=>"0","escuela"=>"Todas"));
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }
    public function tipoAction() {
      if ($this->_request->isXmlHttpRequest()) {
        $json = array();
        $this->SwapBytes_Ajax->setHeader();
        $tipo = $this->Usuarios->getTipoNominaProfesores();
        $this->SwapBytes_Ajax_Action->fillSelect($tipo);

      }
    }

    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json = array();
            if($this->_params['filters']["selEscuela"] == "0"){//verificamos si el valor del filtro es 'Todas'

                $valorescuela="11,12,13,14,15,16";  //le asignamos el valor de todas las escuelas

            }else{

                $valorescuela =$this->_params['filters']["selEscuela"];//sino le dejamos su valor

            }
            $PeriodoAnterior = $this->_params['filters']["selPeriodo"] - 1 ; //Obtiene el periodo anterior 
            $HtmlObjectName = 'pk_usuario';
            if ($this->_params['filters']["selTipo"]=="20093") {      // Nuevo Ingreso

                $rows = $this->Usuarios->ListadoNuevoIngresoDetalle($PeriodoAnterior,$this->_params['filters']["selPeriodo"],$this->_params['filters']["selSede"],$valorescuela);

            }elseif ($this->_params['filters']["selTipo"]=="20095") { //Retirados

                $rows = $this->Usuarios->getDocentesRetirados($PeriodoAnterior,$this->_params['filters']["selPeriodo"],$this->_params['filters']["selSede"],$valorescuela);

            }elseif ($this->_params['filters']["selTipo"]=="20094") { //Regulares

                $rows = $this->Usuarios->ListadoRegularesDetalle($this->_params['filters']["selPeriodo"],$this->_params['filters']["selSede"],$valorescuela); 

            }
            // Definimos las propiedades de la tabla.
            $table = array('class'  => 'tableData',
                           'width'  => '1000px',
                           'column' => 'disponible');
            $totalprof = end($rows)['num'];
            $columns = array(array('column'   => 'pk_asignatura',
                                   'primary'  => true,
                                   'hide'     => true
                                   ),
                             array('name'     => '#',
                                   'width'    => '20px',
                                   'column'   => 'num',
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
                             array('name'     => 'Correo',
                                   'column'   => 'correo',
                                   'width'    => '200px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),                             
                             array('name'     => 'Horas Academicas',
                                   'column'   => 'Horas',
                                   'width'    => '100px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Horario',
                                   'column'   => 'Horario',
                                   'width'    => '350px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Curso',
                                   'column'   => 'curso',
                                   'width'    => '75px',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Materias',
                                   'column'   => 'materia',
                                   'width'    => '500px',
                                   'rows'     => array('style' => 'text-align:center')
                                   )
                             );

            // Generamos la lista.
            $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
            $json[] = $this->SwapBytes_Jquery->setHtml('tblProfesores', $HTML);
            $total = '<div style="font-size: 20px;padding: 15px;">Total de Profesores: <div style="font-weight: bolder;" >'. $totalprof .'</div></div>';
            $json[] = $this->SwapBytes_Jquery->setHtml('totalprofesores', $total);
            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }

}
