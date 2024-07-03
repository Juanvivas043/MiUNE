<?php
//Abraham estuvo aqui
class Reports_PoraulaController extends Zend_Controller_Action {

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
        Zend_Loader::loadClass('Models_DbView_Estructuras');   


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
        $this->Estructuras    = new Models_DbView_Estructuras();

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
        $this->SwapBytes_String         = new SwapBytes_String();

	  /*Filtros*/
      $this->Une_Filtros->setDisplay(true,true);
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
          'id' => 'edificio',
          'name' => 'selEdificio',
          'label' => 'Edificio',
          'recursive' => true
          
        )      
      );
       $customFilters2 = array(
        array(
          'id' => 'aula',
          'name' => 'selAula',
          'label' => 'Aula',
          'recursive' => true
          
        )      
      );

 		$this->Une_Filtros->addCustom($customFilters);
 		$this->Une_Filtros->addCustom($customFilters2);	
 
    }   
    

    public function indexAction() {

      $this->view->title   = 'Reportes \ Horarios por aula';
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

  

 	public function edificioAction(){
 		$json = array();
 		$this->SwapBytes_Ajax->setHeader();
 		$params  = $this->Une_Filtros->getParams();
 		$edificios = $this->Estructuras->getEdificios($params['sede']);
 		$this->SwapBytes_Ajax_Action->fillSelect($edificios);
 	}
 	public function aulaAction(){
 		$json = array();
 		$this->SwapBytes_Ajax->setHeader();
 		$params  = $this->Une_Filtros->getParams(null,array('edificio'));
 		$aulas = $this->Estructuras->getAulas($params['edificio']);
 		$this->SwapBytes_Ajax_Action->fillSelect($aulas);

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
            $params  = $this->Une_Filtros->getParams(null,array('edificio','aula'));
            $html = $this->horarioAula($params['periodo'], $params['sede'], $params['aula']);
            $json[] = $this->SwapBytes_Jquery->setHtml('tblHorarios',addslashes($html));
            $this->getResponse()->setBody(Zend_Json::encode($json));
          }
    }


    public function horarioAula($periodo,$sede,$aula){
    	
      $sedeaulaPrint = $this->Horario->getSedeAula($sede,$aula);
      $periodoPrint  = $this->Periodo->monthPeriodo($this->Une_Filtros->getParams()["periodo"]);
      $this->horas   = $this->Horario->getAllHoras();
      $this->dias    = $this->Horario->getAllDias();

      $HTML =  "<div id='encabezados'><div class='sign'>Sede: {$sedeaulaPrint[0]['sede']} </div>".
        "<div class='sign'> Periodo Académico: {$this->Une_Filtros->getParams()["periodo"]} / {$periodoPrint}</div>".
        "<div class='sign'> Edificio:{$sedeaulaPrint[0]['edificio']}</div>".
        "<div class='sign'> Aula:{$sedeaulaPrint[0]['aula']}</div>".
        "<div class='clear'></div>".
        "</div>";
      $HTML .= "<table id='tblHorarios'><tr><th>Horas</th><th>Lunes</th><th>Martes</th><th>Miercoles</th><th>Jueves</th><th>Viernes</th><th>Sabado</th></tr>";
      foreach ($this->horas as $key => $value) {
        /*Se buscan todas las horas, luego todos los dias en un query el primer foreach esta encargado de los rows(horas) y el segundo esta encargado de los td(dias) enviando como parametro la hora y el dia al query, si posee mas de dos resultados entonces indica que hay mas de una clase en la seccion y se agrega la clase de coincidencias, si posee uno se agrega la clase 'clases' y si no posee ninguna hora asociada se agrega la clase stripe*/
          
          $HTML.= "<tr><td class='h'>".substr($value['horainicio'], 0, -3)." - ".substr($value['horafin'], 0,-3)."</td>";

          foreach ($this->dias as $key2 => $value2) {
              $this->result  = $this->Horario->getHorarioAula($periodo,$sede,$aula,$value['horainicio'],$value2['id']);
              if(count($this->result)>=2){
                $HTML.= "<td class='coincidencias'>";
              }else if(count($this->result)==1){
                $HTML.= "<td class='clases'>";
              }else if(count($this->result)==0){
                $HTML.= "<td class='stripe'>";
              }
                foreach ($this->result as $key3 => $value3) {
                  $exc = $value3['inscritos']>$value3['cupos_max'] ? "class='fusion'" : "";
                  $HTML.= $value3['codigo'].$value3['id'].$value3['seccion']." - ".$value3['materia']."<br>"."<p $exc>inscritos: ".$value3['inscritos']." de ".$value3['cupos_max']."</p>";
                }
            
              $HTML.= "</td>";
          }
          $HTML.="</tr>";
      }
      $HTML .= "</table>";
   return $HTML;

	}
}


/*


        $HTML = "<div class='alert'><center><p style='margin-left:20px;'> Usted no tiene materias para dar clases este periodo</p></center></div>";
    }else {
        $HTML =  "<div id='encabezados'><div class='sign'>Sede: {$sedeaulaPrint[0]['sede']} </div>".
        "<div class='sign'> Periodo Academico: {$this->Une_Filtros->getParams()["periodo"]} / {$periodoPrint}</div>".
        "<div class='sign'> Edificio:{$sedeaulaPrint[0]['edificio']}</div>".
        "<div class='sign'> Aula:{$sedeaulaPrint[0]['aula']}</div>".
        "<div class='clear'></div>".
        "</div>".
        "<table class='horario widget-contenido'>" .
        "<tr>" .
        "<th class='day top-hour'>Horas</th>";
        foreach ($this->result as $hora) {
            if (($dia != $hora['pk_atributo']) ) {
                $dia = $hora['pk_atributo'];
                array_push($dias, $hora['pk_atributo']);
                $HTML .= '<th class="day">' . $hora['dia'] ."</th>";
            }
            if (!isset($turno)) {
                if (isset($hora['turnoreal'])) {
                    $turno = $hora['turnoreal'];
                }
            }
            if (($hora['fk_atributo'] != $turno) ) {
                if (!in_array($hora['horainicio'], $times) && $hora['horainicio'] != null) {
                    array_push($times, $hora['horainicio']);
                }
            } else {
                if ($hora['fk_atributo'] == $turno) {
                    if (!in_array($hora['horainicio'], $timesStart)) {
                        array_push($timesStart, $hora['horainicio']);
                    }
                    if (!in_array($hora['horafin'],$timesEnd)){
                      array_push($timesEnd, $hora['horafin']);
                    }
                }
            }
        }
    }

    sort($timesStart,$timesEnd);
    
    $HTML .= "</tr>";
        foreach (array_combine($timesStart,$timesEnd) as $timeStart=>$timeEnd) {
          //date("g:i a", strtotime($timeStart)) -> asi se usa con el am / pm
           $HTML .= "<tr><td class='time'><b><p>" . date("g:i", strtotime($timeStart)) ."-". date("g:i a", strtotime($timeEnd)) . "</p></b></td>"; 

            foreach ($dias as $dia) {
                $cont=0;
              
                foreach ($this->result as $hora) {
                      
                      if (($hora['fk_atributo'] == $turno) && (strlen($hora['materia'])>0)  && ($hora['horainicio'] == $timeStart) && ($hora['pk_atributo'] == $dia)  )
                      {
                          if(strpos($hora['materia'], ',')){
                              $fusiones = explode(',',$hora['materia']);
                              $fusion=array_unique($fusiones);
                              $fusionSecciones=explode(',',$hora['seccion']);
                              $HTML .= "<td class='coincidencias'>";
                             
                              $HTML .="<div class='tdContenido'>";                        
                              foreach ($fusion as $valor) {
                                $HTML .= "<p><b>" .$this->SwapBytes_String->strBrief($valor,40 )."</b></p>";
                              }
                              foreach ($fusionSecciones as $fusionSeccion) {
                                $HTML .= "<p><b>" . $fusionSeccion."</b></p>";
                              }
                              if (($hora['cantidadinscritos'] > $hora['cupomax']) && ($hora['cantidadinscritos']>0) && ($hora['cupomax']>0))
                                  $HTML.="<div class='fusion'><b><u>Inscritos:</u> ".$hora['cantidadinscritos']." de ".$hora['cupomax']."</b></div></td>";
                              elseif (($hora['cantidadinscritos']>0) && ($hora['cupomax']>0))
                                $HTML.="<b><u>Inscritos:</u> ".$hora['cantidadinscritos']." de ".$hora['cupomax']."</b></td>";
                              $HTML.="</div>";

                          }else {
                             $HTML .= "<td class='clases'><div class='tdContenido'>" . $this->SwapBytes_String->strBrief($hora['materia'],40 )."<p>". $hora['seccion']." de ".$hora['cupomax'] ."</p></div>";
                                                                                                                                                                                                                              
                          }
                    }elseif (($hora['horainicio'] == $timeStart) && ($dia == $hora['pk_atributo'])) {
                            $HTML .= "<td class='stripe'>&nbsp;</td>";
                        }
                }
            }
            $HTML .= "</tr>";
        }
        $HTML .= "</table>";
        $HTML .= "<div class='clear'><br></div>";
        $HTML .= "</div>";

*/