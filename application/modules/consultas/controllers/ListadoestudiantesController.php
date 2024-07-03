<?php
/**
* Creado por: Ricardo Martos
* Octubre 2015
*
*/
class Consultas_ListadoestudiantesController extends Zend_Controller_Action {
    public function init() {
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Horarios');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbView_Materias'); 

        $this->Asignaciones    = new Models_DbTable_Asignaciones();
        $this->filtros         = new Une_Filtros();
        $this->seguridad       = new Models_DbTable_UsuariosGrupos();
        $this->Usuarios        = new Models_DbTable_Usuarios();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->Horario         = new Models_DbTable_Horarios();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->pensums         = new Models_DbTable_Pensums();
        $this->sedes           = new Models_DbTable_Estructuras();
        $this->escuelas        = new Models_DbTable_EstructurasEscuelas();
        $this->RecordAcademico = new Models_DbTable_Recordsacademicos();
        $this->Materias        = new Models_DbView_Materias();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->Swapbytes_array          = new SwapBytes_Array();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        
        $this->filtros->setDisplay(true, true, true, true, true, true);
        $this->filtros->setRecursive(true, true, true, true, true, true);
        $this->SwapBytes_Crud_Action->setDisplay(true, false);
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
        $this->view->title                  = "Consultas / Listado de Estudiantes";
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
        $this->filtros->getAction(array('periodo'));
    }

    public function escuelaAction() {
        $dataRows = $this->escuelas->getSelect($this->Request->getParam('sede'));
        array_unshift($dataRows, array("pk_atributo"=>"0","escuela"=>"Todas")); //se anade el campo de "todas" para seleccionar todas las escuelas
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    public function pensumAction() {
        $Params = $this->filtros->getParams('pensum');
        $pensums = $this->pensums->getAllPensums($Params["escuela"]);
        $this->SwapBytes_Ajax_Action->fillSelect($pensums);
    }

    public function semestreAction() {
        $this->filtros->getAction(array('periodo', 'sede'));

    }

    public function materiaAction() {
      if ($this->_request->isXmlHttpRequest()) {
      $json = array();
      $this->SwapBytes_Ajax->setHeader();
      $params  = $this->filtros->getParams();
      $materias = $this->Materias->getmateriascustom($params['semestre'],$params['periodo'], $params['sede'],$params['escuela']);
      $this->SwapBytes_Ajax_Action->fillSelect($materias);
      }
    }

    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json = array();
            $table = array('class'  => 'tableData',
                           'width'  => '1200px',
                           'column' => 'disponible');

            $tabla1 =  array(array('name'     => '#',
                                   'width'    => '20px',
                                   'function' => 'rownum',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'C.I.',
                                   'column'   => 'pk_usuario',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Apellidos',
                                   'column'   => 'apellido',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Nombres',
                                   'column'   => 'nombre',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Correo Electronico',
                                   'column'   => 'correo',
                                   'rows'     => array('style' => 'text-align:center')
                                   )
                             );
              $tabla2 =  array(array('name'     => '#',
                                   'width'    => '20px',
                                   'function' => 'rownum',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'C.I.',
                                   'column'   => 'pk_usuario',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Apellidos',
                                   'column'   => 'apellido',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Nombres',
                                   'column'   => 'nombre',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'Correo Electronico',
                                   'column'   => 'correo',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                             array('name'     => 'UCA',
                                   'column'   => 'uca',
                                   'rows'     => array('style' => 'text-align:center')
                                   )
                             );
              $tabla3 = array( array('name'     => '#',
                                   'width'    => '20px',
                                   'function' => 'rownum',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                                array('name'     => 'C.I.',
                                                     'column'   => 'pk_usuario',
                                                     'rows'     => array('style' => 'text-align:center')
                                ),
                                array('name'     => 'Apellidos',
                                   'column'   => 'apellido',
                                   'rows'     => array('style' => 'text-align:center')
                                ),
                                array('name'     => 'Nombres',
                                   'column'   => 'nombre',
                                   'rows'     => array('style' => 'text-align:center')
                                ),
                                array('name'     => 'U.C. Adicional',
                                   'column'   => 'ucadicionales',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                                array('name'     => 'Codigo Propietario',
                                   'column'   => 'codigopropietario1',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                                array('name'     => 'Materia',
                                   'column'   => 'materia1',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                                array('name'     => 'Codigo Propietario',
                                   'column'   => 'codigopropietario2',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                                array('name'     => 'Materia',
                                   'column'   => 'materia2',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                                array('name'     => 'U.C.',
                                   'column'   => 'unidadcredito',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                );
            
            $HtmlObjectName = 'pk_usuario';
            if($this->_params['filters']["selEscuela"] == "0"){//verificamos si el valor del filtro es 'Todas'

                $valorescuela="11,12,13,14,15,16";  //le asignamos el valor de todas las escuelas

            }else{

                $valorescuela =$this->_params['filters']["selEscuela"];//sino le dejamos su valor

            }
            if ($this->_params['filters']["selTipo"]=="20096") {      // Con Materias Reprobadas
                if(!isset($this->_params["filters"]["selMateria"]) || !isset($this->_params["filters"]["selSemestre"])){
                    $mensajeerror = 'Por favor revise los filtros, ningun filtro puede estar vacio.';
                }
                
                $rows = $this->Usuarios->materiasreprobadas($this->_params["filters"]["selPeriodo"],$this->_params["filters"]["selSede"],$valorescuela,$this->_params["filters"]["selSemestre"],$this->_params["filters"]["selMateria"]);
                
                $columns = $tabla1;

            }elseif ($this->_params['filters']["selTipo"]=="20097") { // Inscritos por Escuela

                $rows = $this->Usuarios->getinscritosescuela($this->_params["filters"]["selPeriodo"],$this->_params["filters"]["selSede"],$valorescuela,$this->_params["filters"]["selPensum"]);
                $columns = $tabla1;

            }elseif ($this->_params['filters']["selTipo"]=="20098") { // Inscritos por periodos

                $rows = $this->Usuarios->getinscritosperiodos($this->_params["filters"]["selPeriodo"],$this->_params["filters"]["selSede"]);
                $columns = $tabla1;

            }elseif ($this->_params['filters']["selTipo"]=="20099") { // Nuevo Ingreso

                $rows = $this->Usuarios->getnuevosingresos($this->_params["filters"]["selPeriodo"],$this->_params["filters"]["selSede"],$valorescuela);
                $columns = $tabla1;

            }elseif ($this->_params['filters']["selTipo"]=="20100") { // Nuevo Ingreso No Reinscritos

                $periodoanterior=$this->_params["filters"]["selPeriodo"] -1;
                $rows = $this->Usuarios->getnuevosingresosnoreinscritos($this->_params["filters"]["selPeriodo"],$this->_params["filters"]["selSede"], $periodoanterior);
                $columns = $tabla1;

            }elseif ($this->_params['filters']["selTipo"]=="20101") { // Por Semestre de Ubicacion
                if(!isset($this->_params["filters"]["selSemestre"])){
                    $mensajeerror = 'Por favor revise los filtros, ningun filtro puede estar vacio.';
                }
                switch ($this->_params["filters"]["selSemestre"]) { //cambiamos el valor del fk por el valor del semestre
                  case '873':
                    $semestreubicacion = 1;
                    break;
                  case '874':
                    $semestreubicacion = 2;
                    break;
                  case '875':
                    $semestreubicacion =3;
                    break;
                  case '876':
                    $semestreubicacion =4;
                    break;
                  case '878':
                    $semestreubicacion =5;
                    break;
                  case '879':
                    $semestreubicacion =6;
                    break;
                  case '881':
                    $semestreubicacion =7;
                    break;
                  case '882':
                    $semestreubicacion =8;
                    break;
                  case '883':
                    $semestreubicacion =9;
                    break;
                  case '884':
                    $semestreubicacion =10;
                    break;
                  case '9696':
                    $semestreubicacion =11;
                    break;
                  case '9697':
                    $semestreubicacion =12;
                    break;
                  case '872':
                    $semestreubicacion =0;
                    break;
                  default:
                    $semestreubicacion = 1;
                    break;
                }
                $rows = $this->Usuarios->getporsemestredeubicacion($this->_params["filters"]["selPeriodo"],$this->_params["filters"]["selSede"],$valorescuela,$semestreubicacion);
                $columns = $tabla1;


            }elseif ($this->_params['filters']["selTipo"]=="20102") { // Rango de UC Aprobadas
                
                $rows = $this->Usuarios->getrangodeUCaprobadas($this->_params["filters"]["selPeriodo"],$this->_params["filters"]["selSede"],$valorescuela,$this->_params["filters"]["selUCA"][1],$this->_params["filters"]["selUCAT"]);
                $columns = $tabla2;

            }elseif ($this->_params['filters']["selTipo"]=="20103") { // Repitientes
                if(!isset($this->_params["filters"]["selMateria"]) || !isset($this->_params["filters"]["selSemestre"])){
                    $mensajeerror = 'Por favor revise los filtros, ningun filtro puede estar vacio.';
                }
                $rows = $this->Usuarios->getrepitientes($this->_params["filters"]["selPeriodo"],$this->_params["filters"]["selSede"],$valorescuela,$this->_params["filters"]["selSemestre"],$this->_params["filters"]["selMateria"]);
                $columns = $tabla1;

            }elseif ($this->_params['filters']["selTipo"]=="20104") { // Sin Pasantias Social con UCA

                $rows = $this->Usuarios->getsinpasantiasocialconUCA($this->_params["filters"]["selPeriodo"],$this->_params["filters"]["selSede"],$valorescuela,$this->_params["filters"]["selUCA"][0]);
                $columns = $tabla2;

            }elseif ($this->_params['filters']["selTipo"]=="20105") { //Curso Simultaneo
                
                $rows = $this->RecordAcademico->getCursoSimultaneo($this->_params["filters"]["selPeriodo"],$this->_params["filters"]["selSede"],$this->_params["filters"]["selEscuela"]);

                $columns = $tabla3;
            }
           
            if(!isset($mensajeerror)){
                $mensajeerror='No existe ningun estudiante en esta condicion';
            }
            if(!count($rows)){
                $HTML = $this->SwapBytes_Html_Message->alert($mensajeerror);
                $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
            }else{
                
                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
                $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
            }
                $this->getResponse()->setBody(Zend_Json::encode($json));
            
        }
    }

}
