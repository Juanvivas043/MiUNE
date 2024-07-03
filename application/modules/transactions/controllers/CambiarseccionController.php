<?php

//class Transactions_HorariosController extends SwapBytes_Controller_Action {
class Transactions_CambiarseccionController extends Zend_Controller_Action {

  private $_title = 'Transacciones \ Cambio de Sección DDTI';

  public function init() {
    /* Initialize action controller here */
      Zend_Loader::loadClass('Une_Filtros');
      Zend_Loader::loadClass('Models_DbTable_Periodos');
      Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
      Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
      Zend_Loader::loadClass('Models_DbTable_Horarios');
      //instanciar models
      $this->Une_Filtros   = new Une_Filtros();
      $this->periodos      = new Models_DbTable_Periodos();
      $this->grupo         = new Models_DbTable_UsuariosGrupos();
      $this->records       = new Models_DbTable_Recordsacademicos();
      $this->Horario       = new Models_DbTable_Horarios();
  
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

      $this->Une_Filtros->setDisplay(true, true, true, true, true, true, true, true, false);
      $this->Une_Filtros->setRecursive(true, true, true, true, true, true, true, true, false);
      $this->Une_Filtros->setDisabled(false);
     
      /*Botones de Acciones*/
      $this->SwapBytes_Crud_Action->setDisplay(true,true);
      $this->SwapBytes_Crud_Action->setEnable(true,true);
      $this->SwapBytes_Crud_Search->setDisplay(false);
      $this->SwapBytes_Crud_Action->addCustum("<button id=\"btnCambiar\" class=\"ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\" disabled
                                                          name=\"btnCambiar\" role=\"button\" aria-disabled=\"false\">Cambiar</button>");
      $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
      
  }   
    
  public function indexAction(){

      $this->view->title   = $this->_title;
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
      $this->Une_Filtros->getAction(array('periodo', 'sede', 'escuela'));
  }

  public function semestreAction() {
      $this->Une_Filtros->getAction(array('periodo', 'sede', 'escuela', 'pensum'));
  }

  public function materiaAction() {
      $this->Une_Filtros->getAction(array('periodo', 'sede', 'escuela', 'pensum','semestre')); 
  } 

  public function turnoAction() {
      $this->Une_Filtros->getAction(array('periodo', 'sede', 'escuela', 'pensum','semestre', 'materia')); 
  } 

  public function seccionAction() {
      $this->Une_Filtros->getAction(array('periodo', 'sede', 'escuela', 'pensum','semestre', 'materia','turno')); 
  } 

  public function preDispatch() {
    if (!Zend_Auth::getInstance()->hasIdentity()) {
      $this->_helper->redirector('index', 'login', 'default');
    }

    if (!$this->grupo->haveAccessToModule()) {
      $this->_helper->redirector('accesserror', 'profile', 'default');
    }
  }

  public function listAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();

      $HtmlObjectName  = 'pk_recordacademico';
      $HtmlObjectName2 = 'pk_asignacion';
      $this->inscritos = $this->records->getEstudiantesPorSeccion($this->_params['filters']['selPeriodo'], 
                                                                  $this->_params['filters']['selSede'],
                                                                  $this->_params['filters']['selEscuela'], 
                                                                  $this->_params['filters']['selPensum'],
                                                                  $this->_params['filters']['selSemestre'],
                                                                  $this->_params['filters']['selMateria'],
                                                                  $this->_params['filters']['selTurno'],
                                                                  $this->_params['filters']['selSeccion']
                                                                 );
      //var_dump($this->inscritos);die;

      if (!empty($this->inscritos)) {

        $ra_property_table = array('class'  => 'tableData',
                                   'width'  => '950px',
                                   'column' => 'disponible');

        $ra_property_column = array( array('column'   => 'pk_recordacademico',
                                           'primary'  => true,
                                           'hide'     => true),
                                     array('name'     => '#',
                                           'width'    => '20px',
                                           'function' => 'rownum',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'C.I.',
                                           'column'   => 'ci',
                                           'width'    => '60px',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'Apellidos',
                                           'column'   => 'apellido',
                                           'width'    => '225px',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'Nombres',
                                           'column'   => 'nombre',
                                           'width'    => '225px',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'código',
                                           'column'   => 'codigopropietario',
                                           'width'    => '225px',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'materia',
                                           'column'   => 'materia',
                                           'width'    => '225px',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'coincidencia',
                                           'column'   => 'NULL',
                                           'width'    => '225px',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => array('control'  => array('tag'        => 'input',
                                                                                   'type'       => 'checkbox',
                                                                                   'name'       => 'chkSelectDeselect',
                                                                                   'id'         => 'chkboxMaster',
                                                                                   'value'      => "1")),
                                                               'width'    => '30px',
                                                               'column'   => $HtmlObjectName,
                                                               'rows'     => array('style'      => 'text-align:center'),
                                                               'control'  => array('tag'        => 'input',
                                                                                   'type'       => 'checkbox',
                                                                                   'name'       => 'chkEstudiante',
                                                                                   'class'      => 'chkEstudiante',
                                                                                   'value'      => "##{$HtmlObjectName}##")),
                                     array('name'     => array('control'  => array('tag'        => 'select',
                                                                                   'disabled'   => 'true',
                                                                                   'class'      => 'selMaster select',
                                                                                   'name'       => 'selSeccion')),
                                                               'width'    => '100px',
                                                               'column'   => $HtmlObjectName2,
                                                               'rows'     => array('style'      => 'text-align:center'),
                                                               'control'  => array('tag'        => 'select',
                                                                                   'disabled'   => 'true',
                                                                                   'name'       => 'selSeccion',
                                                                                   'class'      => 'selEsbirro select'))
                                     );

              $HTML   = $this->SwapBytes_Crud_List->fill($ra_property_table, $this->inscritos, $ra_property_column);
              $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
              $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkEstudiante');
              $json[] = "$('.tableData').find('td:nth-child(2)').addClass('cedula');
                         $('.tableData').find('td:nth-child(7)').addClass('coincidencia');
                         $('.selEsbirro,.selMaster').empty();
                         $('input:checkbox.chkEstudiante').change(function(){
                           $(this).closest('tr').find('.coincidencia').html('');
                           if($(this).closest('tr').find('.select').hasClass('.dis') && !$(this).is(':checked')){
                              $(this).closest('tr').find('.select').attr('disabled', true).removeClass('.dis');
                           }else{
                              $(this).closest('tr').find('.select').attr('disabled', false).addClass('.dis');
                           }
                         });    
                  ";
              $json[] = "$('#btnCambiar').attr('disabled',true);
                         $('#btnCambiar').addClass('ui-state-disabled')";
              
      }else{

        $HTML =  $this->SwapBytes_Html_Message->alert("No existen Estudiantes en esta Sección.");
        $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);

      }
            

      $this->getResponse()->setBody(Zend_Json::encode($json));
    }
    $json[] = "llenar();";
    $this->getResponse()->setBody(Zend_Json::encode($json));
  }

  public function llenarAction(){
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();

      $periodo = $this->_getParam('periodo',0);
      $materia = $this->_getParam('materia',0);
      $pensum  = $this->_getParam('pensum',0);
      $sede    = $this->_getParam('sede',0);
      $seccion = $this->_getParam('seccion',0);

      $this->secciones = $this->records->getAllSecciones($periodo, $materia, $pensum, $sede, NULL);
      foreach ($this->secciones as $key => $value) {

        $json[] = "$('.selMaster').append(new Option('{$value['valor']}','{$value['fk_seccion']}'));
                   $('.selEsbirro').append(new Option('{$value['valor']}','{$value['fk_seccion']}'));";
      }

      foreach ($this->secciones as $key => $value) {
        if ($value['fk_seccion']==$seccion) {
            $json[] = "$('.selEsbirro').val('".$value['fk_seccion']."');
                       $('.selMaster').val('".$value['fk_seccion']."');
                      ";
        }
      }

        $json[] = "$('input:checkbox#chkboxMaster').change(function(){
                          $('.coincidencia').html('');
                           if ($('#chkboxMaster').is(':checked')) {
                               $('.selEsbirro').attr('disabled', true).removeClass('.dis');
                               $('.selMaster').attr('disabled', false).removeClass('.dis');
                               $('.chkEstudiante').attr('disabled', true);
                           }else{
                               $('.selMaster').attr('disabled', true).removeClass('.dis');
                               $('.chkEstudiante').attr('disabled', false).attr('checked',false);
                               
                           }
                         });";
        $json[] = "$('.select').change(function(){
                      coincidencia();
                   });
                   $('.selMaster').change(function(){
                      coincidenciamaster();
                   });";
    }

      $this->getResponse()->setBody(Zend_Json::encode($json));
  } 
   public function coincidenciaAction(){
      if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();

        $periodo = $this->_getParam('periodo',0);
        $materia = $this->_getParam('materia',0);
        $pensum  = $this->_getParam('pensum',0);
        $sede    = $this->_getParam('sede',0);
        $seccionMaster = $this->_getParam('seccion',0);
        $cedulas = $this->_getParam('cedulas',0);
        $secciones= $this->_getParam('secciones',0);
        $control = 0;
        foreach ($cedulas as $key => $value) {
            $this->result = $this->Horario->horarioestudiante($periodo,$value,$sede);
            $this->materia = $this->Horario->horarioseccion($secciones[$key],$periodo,$materia,$pensum);
            $control = 0;
              foreach ($this->result as $key2 => $value2) {
                foreach ($this->materia as $key3 => $value3) {
                  if($seccionMaster == $secciones[$key]){
                    $json[] = "$('.{$cedulas[$key]}').find('.coincidencia').html('Misma Seccion Inscrita').css('color','blue');";
                    break 2;
                  }else{
                      if ($seccionMaster != $secciones[$key] && $this->result[$key2]['hora'] == $this->materia[$key3]['horainicio'] && $this->materia[$key3]['fk_dia'] == $this->result[$key2]['diaint']){
                       $json[] = "$('.{$cedulas[$key]}').find('.coincidencia').html('Existe Coincidencia').css('color','red');";
                       $control = 1; 
                       break 2;
                    }
                  }
                  
                }
            }
          if($control ==0){
            $json[] = "$('.{$cedulas[$key]}').find('.coincidencia').html('No Existe Coincidencia').css('color','green');";
          }
        }

        $json[] = "$('#btnCambiar').removeAttr('disabled');
                   $('#btnCambiar').removeClass('ui-state-disabled')";
        $this->getResponse()->setBody(Zend_Json::encode($json));
    } 
  }

  public function coincidenciamasterAction(){
      if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();

        $periodo = $this->_getParam('periodo',0);
        $materia = $this->_getParam('materia',0);
        $pensum  = $this->_getParam('pensum',0);
        $sede    = $this->_getParam('sede',0);
        $seccionMaster = $this->_getParam('seccion',0);
        $cedulas = $this->_getParam('cedulas',0);
        $secciones= $this->_getParam('secciones',0);
        $control = 0;
        $json[]  = "$('.select').val('$secciones[0]');coincidencia();";
        

        
        $this->getResponse()->setBody(Zend_Json::encode($json));
    } 
  }

  public function cambiarAction(){
      if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();

        $arreglo = $this->_getParam('arreglo',0);
        $periodo = $this->_getParam('periodo',0);
        $materia = $this->_getParam('materia',0);
        $pensum  = $this->_getParam('pensum',0);
        $sede    = $this->_getParam('sede',0);

        if (empty($arreglo)) {

          $HTML =  $this->SwapBytes_Html_Message->alert("No existen alumnos seleccionados.");
          $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);

        }else{

          foreach ($arreglo as $key => $value) {
            if ($value[0] != "undefined") {
   
              $this->fk_asignacion = $this->records->getAllSecciones($periodo, $materia, $pensum, $sede, $value[1])[0]["pk_asignacion"];            
              $result = $this->records->updateSeccion($value[0], $this->fk_asignacion);

            }
          }
          if (isset($result)) {

             $json[] = "$('#btnCambiar').attr('disabled','disabled');
                        $('#btnCambiar').addClass('ui-state-disabled');";
             $HTML =  $this->SwapBytes_Html_Message->alert("Cambio realizado con éxito.");
             $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
             
          }
        }
        $this->getResponse()->setBody(Zend_Json::encode($json));
    } 
  }
}
