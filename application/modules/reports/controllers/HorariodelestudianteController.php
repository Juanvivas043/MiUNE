<?php 
//Jhonwil Abraham
class Reports_HorariodelestudianteController extends Zend_Controller_Action {


	 public function init() {
	 	//instanciar clases
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Horarios');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Descargables');
        Zend_Loader::loadClass('Zend_Pdf'); 
        Zend_Loader::loadClass('Une_Filtros');       


        //instanciar models
        $this->Une_Filtros  = new Une_Filtros();
        $this->Usuarios 	= new Models_DbTable_Usuarios();
        $this->grupo    	= new Models_DbTable_UsuariosGrupos();
        $this->Record 		= new Models_DbTable_Recordsacademicos();
        $this->Inscripcion = new Models_DbTable_Inscripciones();
        $this->Horario 		= new Models_DbTable_Horarios();
        $this->Periodo 		= new Models_DbTable_Periodos();
        $this->Descargables = new Models_DbTable_Descargables();
        $this->pdf       	= new Zend_Pdf();

 		//se instancia request que es el que maneja los _params
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action 	= new SwapBytes_Ajax_Action();
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
        $this->Swapbytes_array 			= new SwapBytes_Array();

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');

        $this->Une_Filtros->setDisplay(true,false);
        $this->Une_Filtros->setRecursive(false, false);
        $this->SwapBytes_Crud_Search->setDisplay(true);
        $this->SwapBytes_Crud_Action->setDisplay(true,false);
        $this->SwapBytes_Crud_Action->setEnable(true,false);
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
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
        $this->view->title            = 'Reportes \ Horario del estudiante';
        $this->view->usr = $this->Usuarios->getProfile($this->authSpace->userId);  // Datos del Usuario
        $this->fetchGroups();
        $this->view->filters = $this->Une_Filtros;
        $this->view->horario = $this->HorarioEstudiante($cedula, NULL);
        $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->view->SwapBytes_Ajax->setView($this->view);
            
    }

    public function fetchGroups(){
        $this->groups = $this->grupo->getGrupos($this->authSpace->userId);
        return $this->groups;
    }



    public function periodoAction() {
        $this->Une_Filtros->getAction();
        $params  = $this->Une_Filtros->getParams();
    }

    public function listAction(){
          if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $params  = $this->Une_Filtros->getParams();
            $allParams = $this->_getAllParams();
            $params ['cedula'] = $allParams['buscar'];
            $html = $this->HorarioEstudiante($params['cedula'],$params['periodo']);
            $json[] = $this->SwapBytes_Jquery->setHtml('tblHorarios',addslashes($html));
            $this->getResponse()->setBody(Zend_Json::encode($json));
          }
    }



	public function HorarioEstudiante($cedula,$periodo){
         
            $ci = $cedula;

            if (!is_numeric($ci)){
                $HTML = "<div class='alert'><center><p style='margin-left:20px;'>Introduzca solo Cedulas por favor.</p></center></div>";
                return $HTML;
            }
            $validate_user = $this->Usuarios->getEstudianteUG($ci);
            if ($validate_user==null) {
                $HTML = "<div class='alert'><center><p style='margin-left:20px;'>El Usuario no esxiste, Porfavor intentelo de nuevo.</p></center></div>";

                return $HTML;
            }
            
            $this->result = $this->Horario->getHorarioPersona($ci,$periodo, 'est', $this->Inscripcion->getUltimaSede($ci));

            $dias = array();
            $midia = 0;
            $clases = array();
            $times = array();
            $datosest = $this->Usuarios->getInfoGeneralUsuario($ci);
            $sedeest = $this->Usuarios->getUsuarioSede($ci);
            $sedeupper = strtoupper($sedeest[0]['nombre']);
            foreach ($this->result as $hora) {
                if(isset($hora['materia'])){
                    $notset = false;
                    break;
                }else{
                    $notset = true;
                }
            }
            if($notset == true) {
                $HTML = "<div class='alert'><center><p style='margin-left:20px;'> Usted no Tiene materias inscritas este periodo</p></center></div>";
            }else {
                $HTML =  "<div id='datosest'><div class='sign'>SEDE: {$sedeupper} </div>".
                "<div> PERIODO LECTIVO: {$this->Periodo->getPeriodoActual()}</div>".
                "<div>ESTUDIANTE: ".$datosest[0]["apellido"]." , ". $datosest[0]["nombre"]."</div></div><div><table class='horario widget-contenido'> ".
                "<tr>" .
                "<th class ='horas'>Horas</th>";
                foreach ($this->result as $hora) {
                   if (($dia != $hora['pk_atributo']) && (isset($hora['materia']))) {
                        $dia = $hora['pk_atributo'];
                        array_push($dias, $hora['pk_atributo']);
                        $HTML .= "<th>" . $hora['dia'] . "</th>";
                    }
                    if (!isset($turno)) {
                        if (isset($hora['turnoreal'])) {
                            $turno = $hora['turnoreal'];
                        }
                    }
                    if (($hora['fk_atributo'] != $turno)) {
                        if (!in_array($hora['horainicio'], $times) && $hora['horainicio'] != null) {
                            array_push($times, $hora['horainicio']);
                        }
                    } else {
                        if ($hora['fk_atributo'] == $turno) {
                            if (!in_array($hora['horainicio'], $times)) {
                                array_push($times, $hora['horainicio']);
                            }
                        }
                    }
                }
            }
            sort($times);
                $HTML .= "</tr>";
                foreach ($times as $time) {
                   $HTML .= "<tr><td class='time'><b>" . date("g:i a", strtotime($time)) . "</b></td>";   
                    foreach ($dias as $dia) {
                        $cont=0;
                        foreach ($this->result as $hora) {
                            if (($hora['fk_atributo'] == $turno) && (isset($hora['materia'])) && ($hora['horainicio'] == $time) && ($hora['pk_atributo'] == $dia)) {
                                if(strpos($hora['materia'], ',')==true){
                                    $coin = TRUE;
                                    $coincidencia = explode(',',$hora['materia']);
                                    $HTML .= "<td class='coincidencias'>";
                                    for ($i = 0; $i<count($coincidencia);$i++){
                                        if ($i == 0) {
                                            $HTML .= "<b>" . $coincidencia[$i] . "</b><br /><b id='coincidecon'>COINCIDE CON:</b><br />";
                                        }else {
                                        $HTML .= "<b>" . $coincidencia[$i] . "</b><br />";
                                        }
                                    }
                                    $HTML .= "</td>";
                                }else {
                                   $HTML .= "<td class='clases'><div>" . $hora['materia'] . "<div><div>" . $hora['lugar'] . "</div><div>" . $hora['prof'] . "</div></td>";
                                }
                            }elseif(($hora['fk_atributo'] != $turno) && (isset($hora['materia'])) && ($hora['horainicio'] == $time) && ($hora['pk_atributo'] == $dia)){
                                if(strpos($hora['materia'], ',')==true){
                                    $coin = TRUE;
                                    $coincidencia = explode(',',$hora['materia']);
                                    $HTML .= "<td class='coincidencias'>";
                                    for ($i = 0; $i<count($coincidencia);$i++){
                                        if ($i == 0) {
                                            $HTML .= "<b>" . $coincidencia[$i] . "</b><br /><b id='coincidecon'>COINCIDE CON:</b><br />";
                                        }else {
                                        $HTML .= "<b>" . $coincidencia[$i] . "</b><br />";
                                        }
                                    }
                                    $HTML .= "</td>";
                                }else {
                                   $HTML .= "<td class='clases'><div>" . $hora['materia'] . "<div><div>" . $hora['lugar'] . "</div><div>" . $hora['prof'] . "</div></td>";
                                }
                            }elseif (($time == $hora['horainicio']) && ($dia == $hora['pk_atributo'])) {
                                       $HTML .= "<td class='stripe'>&nbsp;</td>";
                                }
                        }
                    }
                    $HTML .= "</tr>";
                }
                $HTML .= "</table>";
                $HTML .= "</div>";
           return $HTML;

           $json[] = "$('#tblHorarios .clases ,.stripe').hover(function() {
                          $('#tblHorarios th').eq($(this).index()).css('background-color', 'rgb(236,244,164)');
                            }, function() {
                          $('#tblHorarios th').eq($(this).index()).css('background-color', 'rgb(171,188,14)');
                            });

                      });";
                     

    }

}

?>