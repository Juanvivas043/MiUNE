<?php 

class Consultas_MihorarioController extends Zend_Controller_Action {

	private $Title = 'Consultas \ Mi Horario';

	 public function init() {
	 	//instanciar clases
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Horarios');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Descargables');
        Zend_Loader::loadClass('Zend_Pdf');        


        //instanciar models
        $this->Usuarios 	= new Models_DbTable_Usuarios();
        $this->grupo    	= new Models_DbTable_UsuariosGrupos();
        $this->Record 		= new Models_DbTable_Recordsacademicos();
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
	 }

         function preDispatch() {
             if (!Zend_Auth::getInstance()->hasIdentity()) {
                 $this->_helper->redirector('index', 'login', 'default');
             }

             if (!$this->grupo->haveAccessToModule()) {
                 $this->_helper->redirector('accesserror', 'profile', 'default');
             }
         }	 



	public function HorarioEst(){
            $ci = $this->authSpace->userId;
            //$this->result = $this->Horario->getHorarioPersona($ci, $this->Periodo->getUltimo() , $quien, $this->Inscripcion->getUltimaSede($ci));

            $this->result = $this->Horario->getHorarioPersona($ci, $this->Periodo->getUltimo() , 'est', $this->Inscripcion->getUltimaSede($ci));

            $dias = array();
            $midia = 0;
            $clases = array();
            $times = array();
            
            foreach ($this->result as $hora) {

                if(isset($hora['materia'])){

                    $notset = false;
                    break;
                }else{
                    $notset = true;
                }
            }

            if($notset == true) {

                $HTML = "<div><br><center><p style='margin-left:20px;'> Usted no Tiene materias inscritas este periodo</p></center></div>";
            }else {

                $HTML =  "<div><table class='horario widget-contenido'>" .
                "<tr>" .
                "<th>Horas</th>";

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



                    if (($hora['fk_atributo'] != $turno) && (isset($hora['materia']))) {



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
                                            $HTML .= "<b>" . $coincidencia[$i] . "</b><br /><b>COINCIDE CON:</b><br />";
                                        }else {
                                        $HTML .= "<b>" . $coincidencia[$i] . "</b><br />";
                                        }

                                    }
                                    $HTML .= "</td>";

                                }else {

                                    $HTML .= "<td class='clases'><b>" . $hora['materia'] . "</b><br />" . $hora['lugar'] . "<br /><b>" . $hora['prof'] . "</b></td>";

                                }

                            }elseif(($hora['fk_atributo'] != $turno) && (isset($hora['materia'])) && ($hora['horainicio'] == $time) && ($hora['pk_atributo'] == $dia)){

                                if(strpos($hora['materia'], ',')==true){
                                    $coin = TRUE;
                                    $coincidencia = explode(',',$hora['materia']);

                                    $HTML .= "<td class='coincidencias'>";
                                    for ($i = 0; $i<count($coincidencia);$i++){

                                        if ($i == 0) {
                                            $HTML .= "<b>" . $coincidencia[$i] . "</b><br /><b>COINCIDE CON:</b><br />";
                                        }else {
                                        $HTML .= "<b>" . $coincidencia[$i] . "</b><br />";
                                        }

                                    }
                                    $HTML .= "</td>";

                                }else {

                                    $HTML .= "<td class='clases'><b>" . $hora['materia'] . "</b><br />" . $hora['lugar'] . "<br /><b>" . $hora['prof'] . "</b></td>";

                                }


                            }elseif (($time == $hora['horainicio']) && ($dia == $hora['pk_atributo'])) {

                                       $HTML .= "<td class='stripe'>&nbsp;</td>";
                                }

                        }

                    }

                    $HTML .= "</tr>";
                }
                $HTML .= "</table>";
                if($coin == TRUE){
                    $HTML .= "<div align='center'>Usted presenta <b>Coincidencia(s)</b> de Horario, para solventar esto debe dirigirse a su direccion de escuela.</div></div>";
                }else{

                 $HTML .= "</div>";
                }



            }

           return $HTML;
        }






        public function HorarioProf(){

            $ci = $this->authSpace->userId;
            //$this->result = $this->Horario->getHorarioPersona($ci, $this->Periodo->getUltimo() , prof, $this->Inscripcion->getUltimaSede($ci));

            $this->result = $this->Horario->getHorarioPersona($ci, $this->Periodo->getUltimo() , 'prof', $this->Inscripcion->getUltimaSede($ci));

            $dias = array();
            $midia = 0;
            $clases = array();
            $times = array();
            $coin = array();

            foreach ($horarios as $hora) {

                if(isset($hora['materia'])){

                    $notset = false;
                    break;
                }else{
                    $notset = true;
                }
            }

            if($notset == true) {


                 $HTML = "<div class='widget-contenido'><br><center><p style='margin-left:20px;'> Usted no Tiene materias Asignadas este periodo</p></center></div>";

            }else {

            $HTML = "<div class='widget-contenido'><table class='horario'>" .
                    "<tr>" .
                    "<th>Horas</th>";
                    
            foreach ($this->result as $hora) {

                if (($dia != $hora['pk_atributo']) && (isset($hora['materia']))) {

                    $dia = $hora['pk_atributo'];
                    array_push($dias, $hora['pk_atributo']);

                    $HTML .= "<th>" . $hora['dia'] . "</th>";
                }

                if (isset($hora['materia'])) {


                    if (!in_array($hora['horainicio'], $times)) {

                        array_push($times, $hora['horainicio']);
                    }
                }
            }



            sort($times);
            $HTML .= "</tr>";

            foreach ($times as $time) {

                $HTML .= "<tr><td class='time'><b>" . date("g:i a", strtotime($time)) . "</b></td>";

                foreach ($dias as $dia) {


                    foreach ($this->result as $hora) {



                        if(($hora['pk_atributo'] == $dia)&&(isset($hora['materia'])&&($time == $hora['horainicio']))) {

                            if(strpos($hora['materia'], ',')==true){

                                $fuciones = explode(',',$hora['materia']);
                                $HTML .= "<td class='fucion'>";
                                for ($i = 0; $i<count($fuciones);$i++){

                                    $HTML .= "<b>" . $fuciones[$i] . "</b><br />";

                                }
                                $HTML .= $hora[3] . "<br /></td>";
                            }else {

                                $HTML .= "<td class='clases'><b>" . $hora['materia'] . "</b><br />" . $hora['lugar'] . "<br /></td>";
                            }



                        }elseif(($hora['pk_atributo'] == $dia)&&($time == $hora['horainicio'])){

                            $HTML .= "<td class='stripe'>&nbsp;</td>";

                        }

                    }

                }

                $HTML .= "</tr>";
            }
            $HTML .= "</table></div>";
    //echo $HTML;
            }
            return $HTML;


        }


}

 ?>