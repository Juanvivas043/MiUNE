<?php 

  class Reports_PormateriaController extends Zend_Controller_Action {

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
        Zend_Loader::loadClass('Models_DbView_Materias');       


        //instanciar models
        $this->Usuarios       = new Models_DbTable_Usuarios();
        $this->grupo          = new Models_DbTable_UsuariosGrupos();
        $this->Record         = new Models_DbTable_Recordsacademicos();
        $this->Inscripcion    = new Models_DbTable_Inscripciones();
        $this->Horario        = new Models_DbTable_Horarios();
        $this->atributos      = new Models_DbTable_Atributos();
        $this->inscripciones  = new Models_DbTable_Inscripciones();
        $this->Periodo        = new Models_DbTable_Periodos();
        $this->Descargables   = new Models_DbTable_Descargables();
        $this->pdf            = new Zend_Pdf();
        $this->Une_Filtros    = new Une_Filtros();
        $this->Materias       = new Models_DbView_Materias();

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
      $this->Une_Filtros->setDisplay(true, true);
      $this->Une_Filtros->setRecursive(true, true);
      /*Botones de Acciones*/
      $this->SwapBytes_Crud_Action->setDisplay(true,false);
      $this->SwapBytes_Crud_Action->setEnable(true,false);
      $this->SwapBytes_Crud_Action->addCustum("<button id=\"btnImprimir\" class=\"ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\"
                                                        name=\"btnImprimir\" role=\"button\" aria-disabled=\"false\">Imprimir Horario</button>");
      $this->SwapBytes_Crud_Action->addJavaScript("$('#btnImprimir').click(function(){window.print()});");

       $this->SwapBytes_Crud_Search->setDisplay(false);
       $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
      
      
       $customFilters = array(
        array(
          'id' => 'materia',
          'name' => 'selMateria',
          'label' => 'Materia',
          'recursive' => true,
          
        )    
      );

 $this->Une_Filtros->addCustom($customFilters);
 
    }   
    

    public function indexAction() {

      $this->view->title   = 'Reportes \ Horarios por materia';
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

  public function materiaAction() {
   
    if ($this->_request->isXmlHttpRequest()) {
      $json = array();
      $this->SwapBytes_Ajax->setHeader();
      $params  = $this->Une_Filtros->getParams();
      $materias = $this->Materias->getMaterias($params['periodo'], $params['sede']);
      $this->SwapBytes_Ajax_Action->fillSelect($materias);
    }
  } 

 

   function preDispatch() {
      if (!Zend_Auth::getInstance()->hasIdentity()) {
        $this->_helper->redirector('index', 'login', 'default');
      }
      if (!$this->grupo->haveAccessToModule()) {
        $this->_helper->redirector('accesserror', 'profile', 'default');
      }
    }

    public function listAction(){
          if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $params  = $this->Une_Filtros->getParams();
            $html = $this->horarioMateria($params['periodo'], $params['sede'], $params['materia']);
            $json[] = $this->SwapBytes_Jquery->setHtml('tblHorarios',addslashes($html));
            $this->getResponse()->setBody(Zend_Json::encode($json));


          }
    }

    public function horarioMateria($periodo,$sede,$materia){

          $this->result = $this->Horario->horarioPorMateria($periodo,$sede,$materia);
          //si no existe horario
          if(empty($this->result)){
            $HTML .= '<table class="empty"><tr><td>Esta Materia No posee ningun Horario inscrito</td></tr>';
          }
          //si existe horario
          else {
            //control de dias y horas
            $days = array();
            $hours = array();
            $count = 0;
            foreach ($this->result as $hora) {
              $days[] = $hora['dia'];
              $hours[] = $hora['inicio'];
            }
            $days = array_unique($days);
            $hours = array_unique($hours);
            sort($hours);
            //primera fila de la tabla
            $HTML .= '<table class="horario widget-contenido"><tr><th>Horas</th>';
            foreach($days as $day){
              $HTML .= "<th class='dia'>".$day."</th>";
            }
            $HTML .= "</tr>";
            //demas filas de la tabla
            foreach ($hours as $hour) {
              $HTML .= "<tr>";
              switch($hour){
                case '07:00:00':
                  $time = "7:00 am";
                  break;
                case '08:40:00':
                  $time = "8:40 am"; 
                  break;
                case '10:15:00':
                  $time = "10:15 am";
                  break;
                case '12:00:00':
                  $time = "12:00 pm"; 
                  break;
                case '12:45:00':
                  $time = "12:45 pm"; 
                  break;
                case '13:35:00':
                  $time = "1:35 pm"; 
                  break;
                case '14:10:00':
                  $time = "2:10 pm"; 
                  break;
                case '14:20:00':
                  $time = "2:20 pm";
                  break;
                case '15:10:00':
                  $time = "3:10 pm";
                  break;
                case '15:55:00':
                  $time = "3:55 pm"; 
                  break;
                case '16:40:00':
                  $time = "4:40 pm"; 
                  break;
                  case '17:00:00':
                  $time = "5:00 pm"; 
                  break;
                case '17:30:00':
                  $time = "5:30 pm";
                  break;
                case '18:30:00':
                  $time = "6:30 pm"; 
                  break;
                case '19:00:00':
                  $time = "7:00 pm"; 
                  break;
              }
              $HTML .= '<td class="time">'.$time.'</td>';
              foreach ($days as $day) {
                $HTML .= '<td class="space">';
                foreach ($this->result as $hora) {
                  if($hora['inicio'] == $hour){
                    if($hora['dia'] == $day){
                      $HTML .= '<div class="clases">';
                      if($hora['sede'] == 7){
                        $HTML .= "LN";
                      }
                      elseif($hora['sede'] == 8){
                        $HTML .= "CN";
                      }
                        $codigo_materia = $hora['codigo'].$hora['periodo'].$hora['seccion'];
                        $prof = $hora['primer_apellido']." ".$hora['primer_nombre'];
                        $place = $hora['edificio']." - ".$hora['salon'];
                      $HTML .= $codigo_materia." - ".$hora['materia']."<br>".$prof."<br>".$place."<br><br>";
                      $HTML .= '</div>';
                      $count++;
                    }
                  }
                }
                $HTML .= "</td>";
              }
              $HTML .= "</tr>";
            }
          }
          $HTML .= "</table>";
          if(!empty($days) and !empty($hours)){
            $HTML .= '<div class="total">';
            $HTML .= "Total de Horas: ".$count;
            $HTML .= "</div>";
          }
          return $HTML;
    }   

}