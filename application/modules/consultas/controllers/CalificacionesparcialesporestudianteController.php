          <?php

class Consultas_CalificacionesparcialesporestudianteController extends Zend_Controller_Action {

    private $Title = 'Consultas \ Calificaciones Parciales del estudiante';
    private $FormTitle_Detalle = 'Ver los datos de la materia inscrita del estudiante';
    private $FormTitle_Modificar = 'Modifica los datos de la materia inscrita del estudiante';
    private $FormTitle_Info = 'Informaci&oacute;n';

    public function init(){
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Asignaturas');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Une_Helpers_CustomFunctionsForCalificacionesParciales');
        Zend_Loader::loadClass('Une_Cde_transactions_CalificacionesParciales');
        Zend_Loader::loadClass('Une_Helpers_ModalMessage');
        $this->usuarios               = new Models_DbTable_Usuarios();
        $this->asignaturas            = new Models_DbTable_Asignaturas();
        $this->inscripciones          = new Models_DbTable_Inscripciones();
        $this->filtros                = new Une_Filtros();
        $this->custom                 = new Une_Helpers_CustomFunctionsForCalificacionesParciales();
        $this->message                = new Une_Helpers_ModalMessage();
        $this->SwapBytes_Ajax         = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html    = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action  = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action  = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List    = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form    = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search  = new SwapBytes_Crud_Search();
        $this->SwapBytes_Jquery       = new SwapBytes_Jquery();
        $this->SwapBytes_Html_Message = new SwapBytes_Html_Message();
        $this->SwapBytes_Ajax_Action  = new SwapBytes_Ajax_Action();
        $this->seguridad              = new Models_DBTable_UsuariosGrupos();
        $this->Request                = Zend_Controller_Front::getInstance()->getRequest();
        $this->AuthSpace              = new Zend_Session_Namespace('Zend_Auth');
        $this->user                   = new Zend_Session_Namespace('user');
        $this->_params['filters'] = $this->filtros->getParams();//Obtiene los parametros del filtro
        $this->filtros->setDisplay(true, true, true, true);
        $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
        $this->filtros->setRecursive(true, true, true, true);
        $this->SwapBytes_Crud_Action->setDisplay(true, true);
        $this->SwapBytes_Crud_Action->setEnable(true, true);
        $this->SwapBytes_Crud_Search->setDisplay(false);

    }

    function preDispatch() {
         if (!Zend_Auth::getInstance()->hasIdentity()) {
             $this->_helper->redirector('index', 'login', 'default');
         }

         if (!$this->seguridad->haveAccessToModule()) {
             $this->_helper->redirector('accesserror', 'profile', 'default');
         }
    }

    public function indexAction() {
      $this->view->title = "Consultas \ Calificaciones Parciales del estudiante";
      $this->view->filters    = $this->filtros;
      $this->view->module     = $this->Request->getModuleName();
      $this->view->controller = $this->Request->getControllerName();
      $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
      $this->view->SwapBytes_Crud_Form = $this->SwapBytes_Crud_Form;
      $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
      $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
      $this->view->SwapBytes_Ajax->setView($this->view);
      $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
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
            $HTML = "";
            //Traemos los parametros de los filtros
            $this->_params['filters'] = $this->filtros->getParams();
            $periodo = $this->_params['filters']['periodo'];
            $pensum = $this->_params['filters']['pensum'];
            $escuela = $this->_params['filters']['escuela'];
            $sede = $this->_params['filters']['sede'];
            $paramArray = array('periodo' => $periodo, 'pensum' => $pensum, 'escuela' => $escuela, 'sede' => $sede);

            //Esta funcion retorna los regimenes historicos del estudiante
            $regimenesHistoricosEstudiante = $this->asignaturas->getRegimenHistoricoEstudiante($periodo, $escuela, $pensum, $this->user->id);

            if(!empty($regimenesHistoricosEstudiante)){

              foreach ($regimenesHistoricosEstudiante as $key => $regimen) {

                  $RecordPorRegimen = $this->asignaturas->getRecordAcademicoParcialEstudiante($periodo, $regimen['pk_regimen_historico'], $sede, $pensum, $escuela, $this->user->id);

                  $notas_array = $this->custom->convertirArrayNotas($RecordPorRegimen, $paramArray);





                  // Cambio para no considerar inasistencias
                  $notas_array = $this->custom->convertirArrayNotas($RecordPorRegimen, $paramArray);
                  foreach ($notas_array as $key => $value) {
                    // Update
                    if( ( isset($value['T.INS']) AND isset($value['T.A']) ) OR 
                        ( isset($value['E.L1']) AND isset($value['E.L2']) ) OR
                        ( isset($value['T.E']) AND isset($value['T.A']) AND isset($value['AUTO']) ) ){
                      $notas_array[$key]['c.f']['nota'] = (int) round($value['acum']['nota'], 0);
                    }
                    else{
                      $notas_array[$key]['t.inasist']['condition']['limite'] = (float) 100;
                      $notas_array[$key]['c.f']['nota'] = (int) round($value['acum']['nota'], 0);
                    }
                  }
                  // Cambio para no considerar inasistencias







                  $columns = $this->construirColumnsArray($notas_array, $RecordPorRegimen);                  
                  $table = array('class' => 'tableData',
                             'width' => '800px');

                  $other = array(
                      array(
                          'actionName' => 'RecordAcademicoParcial',
                          'label' => 'RecordAcademicoParcial')
                      );
                  
                  $HTML .= $this->SwapBytes_Crud_List->fillMultiTable($table, $notas_array, $columns, $actions = null, $otheractions = null);
                  $HTML .= "<br>";
              } 
              
              $json[] = $this->SwapBytes_Jquery->setHtml('tblCalificaciones', $HTML);
              $json[] = "$('[data=\"Retirada\"]').each(function(){ var parent = $(this).parent(\"tr\");parent.addClass('retirada'); });";

            }else{
              $HTML = $this->SwapBytes_Html_Message->alert("No posee record");
              $json[] = $this->SwapBytes_Jquery->setHtml('tblCalificaciones', $HTML);
            }  

            
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }

    }

    public function periodoAction(){
        $this->custom->fillSelect(array('usuario' => $this->user->id,'action' => 'periodo'));
    } 

    public function sedeAction(){
      $this->custom->fillSelect(array('usuario' => $this->user->id,'action' => 'sede'));
    } 

    public function escuelaAction(){
      $this->custom->fillSelect(array('usuario' => $this->user->id,'action' => 'escuela', 'periodo' => $this->_params['filters']['periodo']));
    } 

    public function pensumAction(){
      $this->custom->fillSelect(array('usuario' => $this->user->id,'action' => 'pensum', 'escuela' => $this->_params['filters']['escuela']));
    } 

    public function getestudianteAction(){
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();

          $this->user->id = $this->_getParam('id');

          if(!is_numeric($this->user->id)){
            $json[] = $this->message->quickAlert('Solo caracteres numericos', 1500);
          }else{

            $estudiante = $this->usuarios->getPerson($this->user->id);

            if(empty($estudiante)) { 

              $json[] = $this->message->quickAlert("No existe el estudiante", 1500); 

            }else{

              $json[] = $this->returnDataStudent($estudiante);
              $json[] = "$('#datosEstudiante, #filtros').fadeIn()";
              $json[] = $this->filtros->getJavaScript('tblCalificaciones');

            }
          }

          $this->getResponse()->setBody(Zend_Json::encode($json));
      }
    }

    
    private function construirColumnsArray($Notas, $propiedades){

      $masterArray = array();
      $conditions = array();
      $arrayParaColumnas = reset($Notas);

      $columnName = array_keys($arrayParaColumnas);
      $columnName = $this->custom->moverUltimoItemDePrimero($columnName);
      $columnName = $this->custom->moverUltimoItemDePrimero($columnName);
      $columnName = $this->custom->moverUltimoItemDePrimero($columnName);

      $materias = array_values($Notas);

      foreach ($columnName as $key => $column) {
        
        $conditions = array();
        $control = array();
        $class = "";

        if(is_array($arrayParaColumnas[$column])) {
          $isEvaluable = (Bool) $arrayParaColumnas[$column][0]["evaluable"];
          $isTotal = (Bool) $arrayParaColumnas[$column][0]["total"];
          
          if(!$isEvaluable && is_bool($isEvaluable)){
            $class .= 'noevaluable ';
          }
          if($isTotal){
            $class .= ' total ';
          }

        }
        if($key == 0){
         $columna = array('column'  => $column,
                          'name' => $column,
                          'primary' => true,
                          'hide'    => true);
        }else{

          $conditions = array_merge($conditions, array('style' => 'text-align:center;', 'class' => $class));
          $columna = array('name'    => $column,
                         'column'  => $column,
                         'width' => '20px',
                         'rows'    => $conditions);
        }

        array_push($masterArray, $columna);
      }
    return $masterArray;
  }
 
  private function returnDataStudent($datos){
    $cedula = $datos[0]['pk_usuario'];
    $nombre = $datos[0]['nombre'];
    $primer_apellido = $datos[0]['primer_apellido'];
    $segundo_apellido = $datos[0]['segundo_apellido'];
    $apellido = $primer_apellido . ' ' . $segundo_apellido;

    return "$('#datoCedula').val('$cedula'); $('#datoNombre').val('$nombre');$('#datoApellido').val('$apellido');";

  }

}
