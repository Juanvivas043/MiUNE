<?php

class InicioController extends Zend_Controller_Action {

    
    private $Title = 'Transacciones \ Inicio';

    public function init() {
        //instanciar clases
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Horarios');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbView_Calendarios');
        Zend_Loader::loadClass('Models_DbTable_Descargables');
        Zend_Loader::loadClass('Models_DbTable_Documentossolicitados');
        Zend_Loader::loadClass('Models_DbTable_Usuariosvehiculossorteos');
        Zend_Loader::loadClass('Models_DbTable_Sorteo');
        Zend_Loader::loadClass('Models_DbTable_Inscripcionespasantias');
        Zend_Loader::loadClass('Zend_Pdf'); 
        //instanciar models
        $this->Usuarios = new Models_DbTable_Usuarios();
        $this->grupo    = new Models_DbTable_UsuariosGrupos();
        $this->Inscripcion = new Models_DbTable_Inscripciones();
        $this->Record = new Models_DbTable_Recordsacademicos();
        $this->Horario = new Models_DbTable_Horarios();
        $this->Periodo = new Models_DbTable_Periodos();
        $this->Calendarios = new Models_DbView_Calendarios();
        $this->Descargables = new Models_DbTable_Descargables();
        $this->documetos = new Models_DbTable_Documentossolicitados();
        $this->uvs       = new Models_DbTable_Usuariosvehiculossorteos();
        $this->inscripcionpasantias = new Models_DbTable_Inscripcionespasantias();
        $this->sorteos   = new Models_DbTable_Sorteo();
        $this->pdf       = new Zend_Pdf();
        //se instancia request que es el que maneja los _params
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
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
        $this->Swapbytes_array = new SwapBytes_Array();
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
    }

    function preDispatch() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if (!$this->grupo->haveAccessToModule()) {
            //$this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

    public function datosPasantias(){  

        $this->pasantias = $this->Record->resumenAcademico($this->authSpace->userId, $this->Inscripcion->getUPI($this->authSpace->userId));



        //$this->academicas = $this->Record->resumenAcademico($this->authSpace->userId, $this->Inscripcion->getUPI($this->authSpace->userId));
        $this->pasantia = "<p class='ui-corner-all subTitbg'>" .
                          "<span style='position:relative;'>
                             <span class='subTit'>Datos Academicos: </span>
                          </span>
                          </p>
                          <p class='perfcont'>
                            <b>Escuela: </b>" . $this->pasantias[0]["escuela"] .
                         "</p>
                          <p class='perfcont'>
                            <b>Unidades de Credito Aprobadas: </b>" . $this->pasantias[0]["uca"] .
                         "</p>
                          <p class='perfcont'>
                            <b>Indice Academico Acumulado: </b>" . $this->pasantias[0]["indice"] .
                         "</p>
                          <p class='perfcont'>
                            <b>Indice Academico Periodo Anterior: </b>" . $this->pasantias[0]["indiceant"] .
                         "</p>";
        if(isset($this->pasantias[0]["sociala"])){
            $this->pasantia .= "<p class='perfcont'><b>Pasantia Social: </b>Aprobada</p>";
        }else if(isset($this->pasantias[0]["sociali"])){
            $this->pasantia .= "<p class='perfcont'><b>Pasantia Social: </b>Inscrita</p>";
        }else if(isset($this->pasantias[0]["socialinscribible"])){
            $this->pasantia .= "<p class='perfcont'><b>Pasantia Social: </b>Puede Pre-Inscribirse</p>";
        }else{
            $this->pasantia .= "<p class='perfcont'><b>Pasantia Social: </b>No cumple con los requisitos</p>";
        }
        if(isset($this->pasantias[0]["proa"])){
            $this->pasantia .= "<p class='perfcont'><b>Pasantia Profesional: </b>Aprobada</p>";
        }else if(isset($this->pasantias[0]["proi"])){
            $this->pasantia .= "<p class='perfcont'><b>Pasantia Profesional: </b>Inscrita</p>";
        }else if(isset($this->pasantias[0]["proinscribible"])){
            $this->pasantia .= "<p class='perfcont'><b>Pasantia Profesional: </b>Puede Pre-Inscribirse</p>";
        }else{
            $this->pasantia .= "<p class='perfcont'><b>Pasantia Profesional: </b>No cumple con los requisitos</p>";
        }
        return $this->pasantia;
    }

    public function datosTesis(){
        
        $this->tesistas = $this->Usuarios->aprobaciontesis($this->Periodo->getUltimo(),$this->authSpace->userId);
        if( $this->tesistas[0]["pk_usuario"]== '' || $this->tesistas[0]["pk_usuario"]==null){
            $this->tesistas[0]['estadotesis']='No tiene tesis asignadas';
            $this->tesistas[0]["tutor"]='No aprobada';
            $this->tesista = "<p class='ui-corner-all subTitbg'>".
                          "<span style='position:relative;'>
                             <span class='subTit'> Tesis Aprobadas: </span>
                          </span>
                          </p>
                          <p class='perfcont red cont'>
                            <b id='esttesis'>Estado Tesis: </b>".$this->tesistas[0]['estadotesis']. 
                         "</p>
                          <p class='perfcont red cont'>
                            <b id='esttutor'>Estado Tutoría: </b>".$this->tesistas[0]["tutor"].
                         "</p>";  
        }else{
            if(($this->tesistas[0]["estadotesis"]== 'Aprobado') && ($this->tesistas[0]["tutor"]== 'Aprobado')){
                $this->tesista = "<p class='ui-corner-all subTitbg'>".
                          "<span style='position:relative;'>
                             <span class='subTit'> Tesis Aprobadas: </span>
                          </span>
                          </p>
                          <p class='perfcont green cont'>
                            <b id='esttesis'>Estado Tesis: </b>".$this->tesistas[0]['estadotesis']. 
                         "</p>
                          <p class='perfcont green cont'>
                            <b id='esttutor'>Estado Tutoría: </b>".$this->tesistas[0]["tutor"].
                         "</p>";   

            }

            else if(($this->tesistas[0]["estadotesis"]== 'Aprobado') && ($this->tesistas[0]["tutor"]!= 'Aprobado')){
                $this->tesista = "<p class='ui-corner-all subTitbg'>".
                          "<span style='position:relative;'>
                             <span class='subTit'> Tesis Aprobadas: </span>
                          </span>
                          </p>
                          <p class='perfcont green cont'>
                            <b id='esttesis'>Estado Tesis: </b>".$this->tesistas[0]['estadotesis']. 
                         "</p>
                          <p class='perfcont red cont'>
                            <b id='esttutor'>Estado Tutoría: </b>".$this->tesistas[0]["tutor"].
                         "</p>"; 
            }

            else if($this->tesistas[0]["estadotesis"] != 'Aprobado') {
                 $this->tesista = "<p class='ui-corner-all subTitbg'>".
                          "<span style='position:relative;'>
                             <span class='subTit'> Tesis Aprobadas: </span>
                          </span>
                          </p>
                          <p class='perfcont red cont'>
                            <b id='esttesis'>Estado Tesis: </b>".$this->tesistas[0]['estadotesis']. 
                         "</p>
                          <p class='perfcont red cont'>
                            <b id='esttutor'>Estado Tutoría: </b>".$this->tesistas[0]["tutor"].
                         "</p>";       
            }
        }
       
        return $this->tesista;     
    }

    public function setPasantias(){

      $this->pasantiasProf = $this->inscripcionpasantias->getEstudianteTutoresProfesional($this->Periodo->getUltimo(),$this->authSpace->userId);
   
      //var_dump($this->pasantiasProf);die;
      if ($this->pasantiasProf != null) {
        
        $HTML = "<p class='ui-corner-all subTitbg'>" .
                          "<span style='position:relative;'>
                             <span class='subTit'> Práctica Profesional: </span>
                        
                          </span>
                          </p>";
                          if($this->pasantiasProf[0]["academico"]== 'No Posee Tutor Académico'){
                            $HTML .= " <p class='perfcont red cont'>

                              <b>Tutor Académico: </b>" . $this->pasantiasProf[0]["academico"] .
                           "</p>";
                          }else{
                              $HTML .= " <p class='perfcont green cont'>

                                <b>Tutor Académico: </b>" . $this->pasantiasProf[0]["academico"] .
                             "</p>";
                          }
                          if($this->pasantiasProf[0]["institucional"]== 'No Posee Tutor Institucional'){
                            $HTML .= " <p class='perfcont red cont'>

                              <b>Tutor Institucional: </b>" . $this->pasantiasProf[0]["institucional"] .
                           "</p>";
                          }else{
                              $HTML .= " <p class='perfcont green cont'>

                                <b>Tutor Institucional: </b>" . $this->pasantiasProf[0]["institucional"] .
                             "</p>";
                          };

      }
      return $HTML;
    }

    public function setServicioComunitario(){

      $this->servComun = $this->inscripcionpasantias->getEstudianteTutorComunitario($this->authSpace->userId,$this->Periodo->getUltimo());

      if ($this->servComun != null) {
        
        $HTML = "<p class='ui-corner-all subTitbg'>" .
                          "<span style='position:relative;'>
                             <span class='subTit'> Servicio Comunitario: </span>
                        
                          </span>
                          </p>";
                          if($this->servComun[0]["tutoracademico"] == 'No Posee Tutor Académico'){
                            $HTML .= " <p class='perfcont red cont'>

                              <b>Tutor Académico: </b>" . $this->servComun[0]["tutoracademico"] .
                           "</p>";
                          }else{
                              $HTML .= " <p class='perfcont green cont'>

                                <b>Tutor Académico: </b>" . $this->servComun[0]["tutoracademico"] .
                             "</p>";
                          }
                          if($this->servComun[0]["tutorinstitucion"] == 'No Posee Tutor Institucional'){
                            $HTML .= " <p class='perfcont red cont'>

                              <b>Tutor Institucional: </b>" . $this->servComun[0]["tutorinstitucion"] .
                           "</p>";
                          }else{
                              $HTML .= " <p class='perfcont green cont'>

                                <b>Tutor Institucional: </b>" . $this->servComun[0]["tutorinstitucion"] .
                             "</p>";
                          };

      }
      return $HTML;
    }

    public function datosTutor(){
        
        $this->tutores = $this->Usuarios->revisionTutor($this->authSpace->userId);
       $qlq=  $this->tutores[0]["planilla"];
        if($this->tutores[0]["planilla"] == 'f' or $this->tutores[0]["planilla"] == false or $this->tutores[0]["planilla"] == NULL ){
            $this->tutores[0]["planilla"]="<p class='perfcont red cont'>
                                              <b>Estado Planilla: </b>".'Recaudos NO entregados. Dirigirse a la coordinacion de trabajo especial de grado'.
                                          "</p>" ;
        }else{
            $this->tutores[0]["planilla"]="<p class='perfcont green cont'>
                                              <b>Estado Planilla: </b>".'Sus recaudos ya han sido entregados'. 
                                          "</p>" ;
        }

        if( $this->tutores[0]["pk_usuario"]== '' || $this->tutores[0]["estado_tesis"]==null){
            $this->tutores[0]["estado_tesis"]= 'Tesis no aprobada o asignada';
            $this->tutores[0]["nombre_tutor"]= 'No hay un tutor asignado';
            $this->tutores[0]["tutor"]= 'Tutor no aprobado';

            $this->tutor = 
                        "<p class='ui-corner-all subTitbg'>" .
                          "<span style='position:relative;'>
                             <span class='subTit'> Aprobacion Tesis: </span>
                        
                          </span>
                          </p>
                          <p class='perfcont red cont'>

                            <b>Titulo Tesis: </b>" . $this->tutores[0]["estado_tesis"] .
                         "</p>
                          <p class='perfcont red cont' >
                            <b>Nombre del Tutor:  </b> <span id='nomtut'>" . $this->tutores[0]["nombre_tutor"] .
                         "</span></p>
                          <p class='perfcont red cont'>
                            <b>Estado Tutoría: </b>" . $this->tutores[0]["tutor"] .
                         "</p>".$this->tutores[0]["planilla"];  
        }else{
           if(($this->tutores[0]["estado_tesis"]== 'Aprobado') && ($this->tutores[0]["tutor"]== 'Aprobado')){
                    $this->tutor = 
                        "<p class='ui-corner-all subTitbg'>" .
                          "<span style='position:relative;'>
                             <span class='subTit'> Aprobacion Tesis: </span>
                        
                          </span>
                          </p>
                          <p class='perfcont green cont'>

                            <b>Titulo Tesis: </b>" . $this->tutores[0]["estado_tesis"] .
                         "</p>
                          <p class='perfcont green cont' >
                            <b>Nombre del Tutor: </b> <span id='nomtut'>" . $this->tutores[0]["nombre_tutor"] .
                         "</span></p>
                          <p class='perfcont green cont'>
                            <b>Estado Tutoría: </b>" . $this->tutores[0]["tutor"] .
                         "</p>".$this->tutores[0]["planilla"];

            }

            else if(($this->tutores[0]["estado_tesis"]== 'Aprobado') && ($this->tutores[0]["tutor"]!= 'Aprobado')){
                    $this->tutor = 
                        "<p class='ui-corner-all subTitbg'>" .
                          "<span style='position:relative;'>
                             <span class='subTit'> Aprobacion Tesis: </span>
                        
                          </span>
                          </p>
                          <p class='perfcont green cont'>

                            <b>Titulo Tesis: </b>" . $this->tutores[0]["estado_tesis"] .
                         "</p>
                          <p class='perfcont red cont' >
                            <b>Nombre del Tutor:  </b> <span id='nomtut'>" . $this->tutores[0]["nombre_tutor"] .
                         "</span></p>
                          <p class='perfcont red cont'>
                            <b>Estado Tutoría: </b>" . $this->tutores[0]["tutor"] .
                         "</p>".$this->tutores[0]["planilla"];
            }

            else if($this->tutores[0]["estado_tesis"] != 'Aprobado') {
                     $this->tutor = 
                        "<p class='ui-corner-all subTitbg'>" .
                          "<span style='position:relative;'>
                             <span class='subTit'> Aprobacion Tesis: </span>
                        
                          </span>
                          </p>
                          <p class='perfcont red cont'>

                            <b>Titulo Tesis: </b>" . $this->tutores[0]["estado_tesis"] .
                         "</p>
                          <p class='perfcont red cont' >
                            <b>Nombre del Tutor: </b> <span id='nomtut'>" . $this->tutores[0]["nombre_tutor"] .
                         "</span></p>
                          <p class='perfcont red cont'>
                            <b>Estado Tutoría: </b>" . $this->tutores[0]["tutor"] .
                         "</p>".$this->tutores[0]["planilla"];   
            }
        }
                    
        return $this->tutor;     
    }

    public function fetchGroups(){
        $this->groups = $this->grupo->getGrupos($this->authSpace->userId);
        return $this->groups;
    }

    public function genAlerta($txt,$dat,$id) {
        $result  ="<div id='{$id}' class='ui-state-error ui-corner-all'>" .
                  "<p id='fonts'>" .
                  "<span style='float:left; height:32px;'><span class='ui-icon ui-icon-alert' style='float: left; margin-right: 0.3em;'></span></span>" .
                  $txt . ". <b>Finaliza (". $dat .")</b>".
                  "</p>" .
                  "</div>";
        return $result;
    }

    public function genResaltar($txt,$dat,$id) {
        if ($dat != 0) {
            $result = "<div id='{$id}' class='ui-state-highlight ui-corner-all'>" .
                  "<p id='fonts'>" .
                  "<span style='float:left; height:32px;'><span class='ui-icon ui-icon-alert' style='float: left; margin-right: 0.3em;'></span></span>" .
                  $txt . ". <b>Finaliza (". $dat .")</b>".
                  "</p>" .
                  "</div>";
        }else {
            $result = "<div id='{$id}' class='ui-state-highlight ui-corner-all'>" .
                  "<p id='fonts'>" .
                  "<span style='float:left; height:32px;'><span class='ui-icon ui-icon-alert' style='float: left; margin-right: 0.3em;'></span></span><b>" .
                  $txt .
                  "</b></p>" .
                  "</div>";
        }
        return $result;
    }

    public function getHorario() {
        $ci = $this->authSpace->userId;

        $resultado = $this->Horario->getHorarioEstudianteProfesor($ci, $this->Periodo->getUltimo(), $this->Inscripcion->getUltimaSede($ci));
      
        $dias = array();
        $midia = 0;
        $clases = array();
        $times = array();
        foreach ($resultado as $hora) {
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
            foreach ($resultado as $hora) {
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
                    foreach ($resultado as $hora) {
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
                                if(isset($hora["prof"]) || $hora["prof"] != null){
                                    $HTML .= "<td class='clases'><b>" . $hora['materia'] . "</b><br />" . $hora['lugar'] . "<br /><b>" . $hora['prof'] . "</b></td>";
                                }else{
                                    $HTML .= "<td class='profesor'><b>" . $hora['materia'] . "</b><br />" . $hora['lugar'] . "<br /><b>" . $hora['prof'] . "</b></td>";
                                }
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

       
    public function getCal(){
        $hoy = date('d-m-Y');
        foreach($this->groups as $i => $grp){
            if($i == 0){
                $this->grpArray .= $grp['pk_grupo'];
            }else{
                $this->grpArray .= ',' . $grp['pk_grupo'];
            }
        }
        //$this->Calendarios->getCalendario($this->grpArray, $this->Periodo->getUltimo());
        $calendarios = $this->Calendarios->getCalendarioPeriodo($this->grpArray, $this->Periodo->getUltimo());
        $HTML = "<div id='tasks' class='ui-widget-content ui-corner-all' style='border: 1px solid gray;'>" .
            "<h2 class='ui-widget-header-une' style='z-index:-1000;'>Cronograma de Actividades</h2>";
        $cont = 0;
        $id = 'act';
        if (isset($calendarios)) {
            foreach ($calendarios as $calen) {
                if ($cont > 0) {
                    if ($calen['fechai'] == $calendarios[$cont - 1]['fechai']) {
                        if ($calen['restante'] <= 7) {
                            $HTML .= $this->genAlerta($calen['actividad'], $calen['fechaf'], $id);
                        } else {
                            $HTML .= $this->genResaltar($calen['actividad'], $calen['fechaf'], $id);
                        }
                    } else {
                        if($calen['actual'] >= 0){
                            $HTML .= "<div id='fechas'><p>Actividades que iniciaron el: " . $calen['fechai'] . "</p></div>";
                        }else{
                            $HTML .= "<div id='fechas'><p>Actividades que inician el: " . $calen['fechai'] . "</p></div>";
                        }
                        if ($calen['restante'] <= 7) {
                            $HTML .= $this->genAlerta($calen['actividad'], $calen['fechaf'], $id);
                        } else {
                            $HTML .= $this->genResaltar($calen['actividad'], $calen['fechaf'], $id);
                        }
                    }
                } else {
                    if($calen['actual'] >= 0){
                            $HTML .= "<div id='fechas'><p>Actividades que iniciaron el: " . $calen['fechai'] . "</p></div>";
                        }else{
                            $HTML .= "<div id='fechas'><p>Actividades que inician el: " . $calen['fechai'] . "</p></div>";
                        }
                    if ($calen['restante'] <= 7) {
                        $HTML .= $this->genAlerta($calen['actividad'], $calen['fechaf'], $id);
                    } else {
                        $HTML .= $this->genResaltar($calen['actividad'], $calen['fechaf'], $id);
                    }
                }
                $cont++;
            }
            $HTML .= "<center><a href='#' id='btnDescargar' style='color:blue;'>Ver Calendario Completo</a></center></div>";
        } else {
            $HTML .= "<center><p style='margin-left: 20px;'>No Existen Actividades Pendientes<p></center></div>";
        }
        return $HTML;
    }

    public function getEstacionamiento(){
        //isParticipantes
        //getSorteos
        //isSelected
        $link = Zend_Controller_Front::getInstance()->getBaseUrl() . '/transactions/inscripcionsorteo';
        $ci = $this->authSpace->userId;
        $data['periodo'] = $this->Periodo->getUltimo();
        $participa = $this->uvs->isParticipantes($ci, $data['periodo']); 
        $estado = $this->uvs->isSelected($ci);
        $sorteo = $this->sorteos->getCurrentSorteos($data);
        $sorteoactivo = $this->sorteos->getActive($data['periodo']);
        $HTML = "<div id='right-p3' class='ui-widget-content ui-corner-all' style='border: 1px solid gray;'>" .
                "<h2 class='ui-widget-header-une' style='z-index:-1000;'>Estacionamiento</h2>";
        if(isset($sorteo[0])){
            $HTML .= "<table align='center' style='text-align: center; margin-left: auto; margin-right: auto; margin-top: 35px;'>";
            if(!isset($participa[0])){ //no participa
                if(!isset($sorteoactivo[0])){//no hay sorteo activo
                    $HTML .="<tr><td>No existen sorteos de puestos de estacionamiento en este momento</td></tr>";
                }else{
                    $HTML .="<tr><td>Estan abiertas las Inscripciones para el sorteo de puestos de estacionamiento. registrese <a href={$link} style='color:blue;'>Aquí</a></td></tr>";   
                }
            }
            else{
                if($estado[0]['estado'] == 'Inscrito'){
                    
                    $HTML .= "<tr><td>Usted esta Inscrito en el sorteo <b>{$estado[0]['descripcion']}</b> en el turno <b>{$estado[0]['valor']}</b></td></tr>";//var_dump($estado);
                }elseif($estado[0]['estado'] == 'Seleccionado'){
                    $HTML .= "<tr><td>Usted ha sido Selecionado en el sorteo <b>{$estado[0]['descripcion']}</b> en el turno <b>{$estado[0]['valor']}</b></td></tr>";
                }
            }
        }
        else{
            $HTML .= "<table align='center' style='text-align: center; margin-left: auto; margin-right: auto; margin-top: 35px;'>";
            $HTML .="<tr><td>No existen sorteos de puestos de estacionamiento en este momento</td></tr>";
        }
        $HTML .= "</table></div>";
        return $HTML;
    }

    public function getCuotas() {
        $docs = $this->Calendarios->getAllCuotas($this->Periodo->getUltimo());
        $HTML = "<div id='right-p' class='ui-widget-content ui-corner-all' style='border: 1px solid gray;'>" .
                "<h2 class='ui-widget-header-une' style='z-index:-1000;'>Cuotas</h2>";
        //var_dump($docs);
        $HTML .= "<table align='center' width='180px' style='margin-right:auto;margin-left:auto;'>";
        if (isset($docs)) {
            foreach ($docs as $dc) {
                $alert = $dc['restante'] >= 1 ? "<span style='color:green;font-size:14px;text-align:center;'>{$dc['fechai']}</span>" : "<span style='color:red;font-size:14px;font-weight:bold;'>Vencida</span>";
                $HTML .= "<tr><td style='font-size:14px;'>{$dc['actividad']}</td><td style='text-align:center;'>$alert</td></tr>";
            }
        }
        $HTML .= "</table></div>";
        return $HTML;
    }

    public function getDescargables() {
        $ci = $this->authSpace->userId;
        if ($this->Swapbytes_array->in_array_recursivo(855,$this->groups) == TRUE){
            $HTML = "<div class='ui-widget-content ui-corner-all' style='border: 1px solid gray; margin-top: 5px;float: left;margin-left: 6px;width: 200px;overflow-y: scroll;height: 175px;'>" .
            "<h2 class='ui-widget-header-une' style='z-index:-1000; text-align: center; font: 14px verdana, arial, helvetica, sans-serif;margin: 0;padding: 0.2em;font-weight: bold;'>Descargables</h2>";
            $HTML .= "<table align='center' width='180px' style='margin-right:auto;margin-left:auto;'>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;' id='calificacionesParciales' class='click' > Instructivo Final de Calificaciones Parciales </label></td></tr>";                
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;'>&nbsp</label></td></tr>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;' id='manualEst' class='click' >Manual para inscripci&oacute;n de vehiculo</label></td></tr>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;'>&nbsp</label></td></tr>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;' id='manual' class='click' >Manual para Presentaci&oacute;n de la Prueba Diagn&oacute;stica</label></td></tr>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;'>&nbsp</label></td></tr>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;' id='autoevaluacion' class='click' >Manual Autoevaluaci&oacute;n Pr&aacute;ctica Profesional</label></td></tr>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;'>&nbsp</label></td></tr>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;' id='servicio' class='click' >Manual Preinscripci&oacute;n Proyecto Servicio Comunitario</label></td></tr>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;'>&nbsp</label></td></tr>"; 
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;' id='solicitudgrado' class='click' >Manual Solicitud de Grado</label></td></tr>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;'>&nbsp</label></td></tr>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;' id='tesis' class='click' >Instructivo para Estudiantes de Trabajo de Grado</label></td></tr>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;'>&nbsp</label></td></tr>"; 
            $HTML .= "</table></div>";
        }
        else if ($this->Swapbytes_array->in_array_recursivo(854,$this->groups) == TRUE){
            $HTML = "<div id='right-p' class='ui-widget-content ui-corner-all' style='border: 1px solid gray;'>" .
            "<h2 class='ui-widget-header-une' style='z-index:-1000;'>Descargables</h2>";
            $HTML .= "<table align='center' width='180px' style='margin-right:auto;margin-left:auto;'>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;' id='instructivoEA' class='click' >Instructivo de Evaluaci&oacute;n Acad&eacute;mica Pr&aacute;cticas Profesionales</label></td></tr>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;'>&nbsp</label></td></tr>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;' id='instructivoTesis' class='click' >Instructivo de Aprobaci&oacute;n de T&iacute;tulo y Tutor Trabajo de Grado</label></td></tr>";
            $HTML .= "<tr><td align='center' style='font-size:14px;'><label style='color:blue; font-family: Arial;'>&nbsp</label></td></tr>";
            $HTML .= "</table></div>";
        }
        return $HTML;
    }

    public function descargargablesAction(){
        $id = $this->_getParam('id');
        $filetype    = 'pdf';
        $filename = $id;
        $planilla = APPLICATION_PATH . '/modules/default/uploads/';
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
        //readfile($planilla.$filename.'.pdf');
        if($id == 'manual'){
            readfile("descargables/documentos/Manual-para-Presentacion-de-la-Prueba-Diagnostica.pdf");
        }else if($id == 'instructivoNar'){
            readfile("descargables/documentos/INSTRUCTIVO-INSCRIPCION-ENERO-ABRIL2013-NARANJOS.pdf");
        }else if($id == 'instructivoCen'){
            readfile("descargables/documentos/INSTRUCTIVO-INSCRIPCION-ENERO-ABRIL2013-NARANJOS.pdf");
        }else if($id == 'manualEst'){
            readfile("descargables/documentos/Manual-Estacionamiento.pdf");
        }else if ($id == 'autoevaluacion'){
            readfile("descargables/documentos/MANUAL-AUTOEVALUACION-PASANTIA.pdf");
        }else if ($id == 'servicio'){
            readfile("descargables/documentos/MANUAL-PREINSCRIPCION-PROYECTO.pdf");
        }else if ($id == 'solicitudgrado'){
            readfile("descargables/documentos/MANUAL-SOLICITUD-GRADO.pdf");
        }else if ($id == 'tesis'){
            readfile("descargables/documentos/MANUAL-TESIS-ESTUDIANTE.pdf");
        }else if ($id == 'instructivoEA'){
            readfile("descargables/documentos/MANUAL-EVALUACION-ACADEMICA.pdf");
        }else if ($id == 'instructivoTesis'){
            readfile("descargables/documentos/MANUAL-TESIS-DIRECTOR-DE-ESCUELA.pdf");
        }else if ($id == "calificacionesParciales"){
            readfile("descargables/documentos/calificacionesParciales.pdf");
        }   
    }

    public function getDocuments(){
        $ci = $this->authSpace->userId;
        $documentos = $this->documetos->getEstatusSolicitudes($ci);
        $HTML = "<div id='tasks2' class='ui-widget-content ui-corner-all' style='border: 1px solid gray;'>" .
                 "<h2 class='ui-widget-header-une' style='z-index:-1000;'>Solicitudes de Documentos</h2>";
        if(isset($documentos[0])){
            $HTML .= "<table width='400px' style='font-size: 14px; margin-left: auto; margin-right: auto;'>";
            $HTML .="<th>Solicitud</th><th>Documento</th><th>Estado</th>";
            foreach($documentos as $doc){
                $span = $doc['estado'] == 'Listo para Retirar' ? "<span style='color:green;font-weight:bold;'>{$doc['estado']}</span>" : "<span style='font-weight:bold;'>{$doc['estado']}</span>";
                $HTML .= "<tr><td align='center'>{$doc['pk_usuariogruposolicitud']}</td><td align='left'>{$doc['docum']}</td><td align='center'>$span</td></tr>";
            }
             $HTML .= "</table>";
        }else{
            $HTML .= "<table style='font-size: 14px; margin-left: auto; margin-right: auto; vertical-align: middle; margin-top: 28px;'><tr><td style='font-size:14px;'>No existen solicitudes pendientes.</td></tr></table>";
        }
        $HTML .= "</div>";
        return $HTML;
    }

    public function descargarAction() {
        //$Params = $this->_getParam('data');
        $Params = $this->SwapBytes_Uri->queryToArray($Params);
        $config = Zend_Registry::get('config');
        $per = $this->Periodo->getUltimo();
        $dbname = $config->database->params->dbname;
        $dbuser = $config->database->params->username;
        $dbpass = $config->database->params->password;
        $dbhost = $config->database->params->host;
        $report = APPLICATION_PATH . '/modules/reports/templates/Calendario/calendario.jasper';
        $subreport = APPLICATION_PATH . '/modules/reports/templates/Calendario/';
        $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
        $filename    = 'CalendarioAcademico';
        $filetype    = 'pdf';
        $params      = "'Periodo=string:{$per}|SUBREPORT_DIR=string:{$subreport}|ruta_imagen=string:{$imagen}'";
        $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
        //local -Djava.awt.headless=true
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype }");
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
        //echo $cmd;
        $outstream = exec($cmd);
        echo base64_decode($outstream);
    }


    public function getFree() {
        //$free = $this->Inicio->getFeriados($this->grps, $this->periodoactual);
        $free = $this->Calendarios->getFeriados($this->grpArray, $this->Periodo->getUltimo());
        $HTML = "<div id='right-p2' class='ui-widget-content ui-corner-all' style='border: 1px solid gray;'>" .
                "<h2 class='ui-widget-header-une' style='z-index:-1000;'>D&iacute;as libres</h2>";
        if (isset($free)) {
            foreach ($free as $frees) {
                $HTML .= "<div id='freecont'><b>" . $frees['fechai'] . " al " . $frees['fechafin'] . ":</b><br /> " . $frees['actividad'] . "</div>";
            }
            $HTML .= "</div>";
        } 
        else {
            $HTML .= "<p style='margin-left: 12px;'>No hay mas dias libres en el período</p></div>";
        }
        return $HTML;
    }

    public function setMessage($id){
        switch ($id) {
            case 1:
                //Ya no se puede pagar usando tarjetas de credito
                /* 
                $HTML = '<div id="msg" class="message">
                    <p>Ya puedes pagar tus cuotas con una Tarjeta de Credito entrando a "Transacciones" > "Pagos" > "Cuotas" </p>
                </div>';
                */

                break;
            case 2:
                $HTML = '<div id="msg" class="message">
                    <p>Tu solicitud esta siendo procesada en este momento por el departamente de Practica Profesional</p>
                </div>';
                break;

        }
        return $HTML;
    }

    public function setMessageTesis($id){
        switch ($id) {
            case 1:
                $HTML = '<div id="msg2" class="message2">
                    <p>Revisa el estado de tu tesis entrando a "Transacciones" > "Trabajo de grado" > "Datos Tesis Estudiante" </p>
                </div>';
                

                break;
            case 2:
                $HTML = '<div id="msg2" class="message2">
                   <p>Revisa el estado de los tesistas entrando a "Consultas" > "Revision Tesistas" </p>
                </div>';
                break;
                
        }
        return $HTML;
    }

    public function insmattesis(){
        $this->matesis =  $this->Inscripcion->insmateriastesis($this->Inscripcion->getUltimaEscuelapk($this->authSpace->userId), $this->authSpace->userId,$this->Periodo->getUltimo());

        return $this->matesis;
    }

    public function indexAction() {

        $this->view->usr = $this->Usuarios->getProfile($this->authSpace->userId);  // Datos del Usuario
        $this->fetchGroups();

        

      /*  foreach ($this->fetchGroups() as $value) {
            if($value == 19976){
                $this->view->tesista = $this->datosTesis();
            }
        }*/
        
         
        if(!$this->view->usr[0]['actualizado']){
            $this->getResponse()->setRedirect($this->getRequest()->getBaseUrl().'/profile/actualizaciondatos');        
        }   
            
        $this->view->horario = $this->getHorario();
        if ($this->Swapbytes_array->in_array_recursivo(855,$this->groups) == TRUE){
             $this->view->setPasantias = $this->setPasantias();
             $this->view->servComun    = $this->setServicioComunitario();
            
            if($this->Periodo->getUltimo() != $this->Inscripcion->getUPI($this->authSpace->userId) && $this->Swapbytes_array->in_array_recursivo(854,$this->groups) == TRUE){
                $this->view->actividades = $this->getCal();
                $this->view->feriados = $this->getFree();
                $this->view->pasantias = $this->datosPasantias();
                $this->view->message = $this->setMessage(1);

                if($this->matesis[0]["count"]>0){
                    $this->view->tutores = $this->datosTutor();
                    $this->view->message2 = $this->setMessageTesis(1);
                }


            }else{
                $this->view->pasantias = $this->datosPasantias();
                $this->view->actividades = $this->getCal();
                $this->view->feriados = $this->getFree();
                $this->view->cuotas = $this->getCuotas();
                $this->view->Descargables = $this->getDescargables();
                $this->view->documentos = $this->getDocuments();
                $this->view->estacionamiento = $this->getEstacionamiento();
                $this->view->message = $this->setMessage(1);
                $this->view->matesis = $this->insmattesis();

                if($this->matesis[0]["count"]>0){
                    $this->view->tutores = $this->datosTutor();
                    $this->view->message2 = $this->setMessageTesis(1);
                }
            }
        }else if($this->Swapbytes_array->in_array_recursivo(854,$this->groups) == TRUE){
            $this->view->actividades = $this->getCal();
            $this->view->feriados = $this->getFree();
            $this->view->Descargables = $this->getDescargables();
        }else if($this->Swapbytes_array->in_array_recursivo(20111,$this->groups) == TRUE){
            $this->view->message = $this->setMessage(2);
        }

        if ($this->Swapbytes_array->in_array_recursivo(19976,$this->groups) == TRUE){
            $this->view->tesistas = $this->datosTesis();
            $this->view->message2 = $this->setMessageTesis(2);
        }    

        $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->view->SwapBytes_Ajax->setView($this->view);
    }



}
?>
