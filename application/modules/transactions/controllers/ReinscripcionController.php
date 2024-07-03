<?php
class Transactions_ReinscripcionController extends Zend_Controller_Action {
    private $Title  = 'Reinscripcion \ Carga de reinscripcion';
    
    
        public function init() {
        /* Initialize action controller AQUI */
            Zend_Loader::loadClass('Models_DbTable_Reinscripciones');
            Zend_Loader::loadClass('Models_DbTable_Profit');
            Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
            Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
            Zend_Loader::loadClass('Models_DbTable_Horarios');
            
            $this->reportes              = new CmcBytes_GenerarReportes();
            $this->grupo                 = new Models_DbTable_UsuariosGrupos();
            $this->reinscripciones       = new Models_DbTable_Reinscripciones();
            $this->profit                = new Models_DbTable_Profit();
            $this->RecordAcademico       = new Models_DbTable_Recordsacademicos();
            $this->horario               = new Models_DbTable_Horarios;
            $this->authSpace             = new Zend_Session_Namespace('Zend_Auth');
            
            $this->view->SwapBytes_Ajax     = new SwapBytes_Ajax();
            $this->SwapBytes_Form           = new SwapBytes_Form();
            $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
            $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
            $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
            $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
            $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
            $this->SwapBytes_String         = new SwapBytes_String();

            $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
            $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
            $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
            $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();

            $this->logger = Zend_Registry::get('logger');
            $this->namespace = new Zend_Session_Namespace("arrayTable");
            $this->datosInsc = new Zend_Session_Namespace("datos");
            $this->ci = new Zend_Session_Namespace("ciId");
            $this->acceso = new Zend_Session_Namespace("acceso");
            $this->view->SwapBytes_Ajax->setView($this->view);
            $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;

            
            $this->_params['modal'] = $this->SwapBytes_Crud_Form->getParams();
            $this->SwapBytes_Form->set($this->view->form);
            $this->data = array();
        }   
        
        function preDispatch() {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
                $this->_helper->redirector('index', 'login', 'default');
            }

            if (!$this->grupo->haveAccessToModule()) {
                $this->_helper->redirector('accesserror', 'profile', 'default');
            }
        }
   
/*---------*/public function indexAction() {
            
            $this->view->title            = $this->Title;
            $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
            $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
            $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
            $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
            $this->view->SwapBytes_Ajax->setView($this->view);
            $this->acceso->pk = 145; //pk_acceso de Reinscriciones
            
        }
        
/*---------*/public function asistidaAction(){
            
            $this->view->title            = $this->Title;
            $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
            $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
            $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
            $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
            $this->view->SwapBytes_Ajax->setView($this->view);
            $this->acceso->pk = 219; //pk_acceso de Reinscripciones casos especiales
            
        }
        
 /*--------*/public function accesoAction(){
            $this->SwapBytes_Ajax->setHeader();
            
            $acceso = $this->acceso->pk;

            if ($acceso == 145){
                
                $this->ci->ciId = $this->authSpace->userId;
                $this->getResponse()->setBody($acceso); 
                
                
            }else if ($acceso == 219){
                
                $HTML .= "<label>C.I: </label>";
                $HTML .= "<input id=txtBuscar type=text style=width:120px name=txtBuscar></input>"; 
                $HTML .= "<button id=btnCi type=button>Buscar</button>"; 
                
                $json[] = "$('#buscarCi').html('".$HTML."')";
                
                $json[] = "$('#buscarCi').show()";
                
                $this->getResponse()->setBody(Zend_Json::encode($json));
                
            }
               
        }
        
       
        
/*----------*/public function infoestudianteAction(){
            
            $this->SwapBytes_Ajax->setHeader();
                    
                    $ci = $this->ci->ciId;


                    if(!empty($ci)){
                    
                     //Busca la sede del estudiante.
                    $aSede = $this->reinscripciones->sedeUltimo($ci);
                    //Busca la escuela del estudiante.
                    $aEscuela = $this->reinscripciones->escuelaEstudiante($ci);
                    //$Escuela = $this->RecordAcademico->getNombreEscuela($ci, $aEscuela[0]['pk_atributo']);

                    //Busca creditos adicionales
                    $aUcadicio = $this->reinscripciones->UnnidadesCreditoAdicionales($ci,$aSede[0]['fk_periodo']);
                    //Busca datos de estudiante que se reinscribira
                    $aDatos = $this->reinscripciones->datosEstudiante($ci);
                    //Busca ultmimo periodo cursado
                    $aPeriodo = $this->reinscripciones->ultimoperiodo();
                    //Busca inscripcion del estudiante
                    $aInscripcion = $this->reinscripciones->buscarInscripcion($ci,$aPeriodo[0]['pk_periodo']);

      //$aPensum = $this->reinscripciones->ultimoPensumEscuela($aEscuela[0]['pk_atributo']);
      //$aPensumEst = $this->reinscripciones->getPensum($ci, $aEscuela[0]['pk_atributo']);

                    //Materias cursadas por el estudiante                    
                    $asignaturasCursadas =  $this->reinscripciones->totalAsignaturasAprobadas($ci,$aEscuela[0]['pk_atributo']);
                    //Todas las materia de la carrera que esta el estudiante
                    $asignaturasTotales =   $this->reinscripciones->totalAsignaturas($ci);
                    //Materias que le faltan al estudiante
                    $asignaturasFaltantes = $asignaturasTotales[0]['total'] - $asignaturasCursadas[0]['total'];
                    //Busca los creditos aprobados de la carrera
                    $UCA = $this->RecordAcademico->getUCA($ci,$aEscuela[0]['pk_atributo']);
                    //Busca en cual fue la ultima sede donde curso el estudiante
                    $aUltimoCursado = $this->reinscripciones->sedeUltimoPeriodo($ci);
                    //Chequea si eres pensum viejo o nuevo
                    $aCheck = $this->reinscripciones->checkNuevoPensum($aUltimoCursado[0]['fk_periodo'], $aSede[0]['fk_estructura'], $ci);
                    //total del indice acumula en todo los periodo
                    $aIia = $this->reinscripciones->IndiceAcademicoAcumuladoEscuela($ci, $aEscuela[0]['pk_atributo'], $aUltimoCursado[0]['fk_periodo'], $aCheck[0]['codigopropietario']);
                    // Busca el indice del periodo anterior
                   
                    $aIap = $this->reinscripciones->indicePeriodo($ci,$aSede[0]['fk_periodo']-1, $aEscuela[0]['pk_atributo']);
                    //Busca el pensum actual
                    $aPensumEst = $this->reinscripciones->getPensumNew($ci, $aUltimoCursado[0]['fk_periodo']);

                    
                    $indiceAcumulado = $aIia[0]['fn_xrxx_estudiante_iia_escuela_periodo_articulado'];
                    $indicePeriodo = $aIap[0]['iiap']; 
                    $otroDia = false;
                    $activado = true;//Desactivar para que salga el mensaje de pira
                    
                    if($indiceAcumulado < 11 && $indecePeriodo < 11 && $activado == false){
                        
                        $mensaje = '<div style=\"color: red; font-size:30; text-align:center;\"><H3>El estudiante tiene un &Iacute;ndice Acumulado inferior a 11pts.</br> Debe asistir al proceso el d&iacute;a 31 de Agosto del 2012.</H3></div>';
                        
                        $json[] = '$("#btnValidar").attr("disabled","disabled")';
                        $json[] = '$("#otroDia").html("'.$mensaje.'")';

                        
                        $otroDia = true;//Poner en TRUE SI no es el dia.
            
                    }
                    
                    $ultPeri = $this->reinscripciones->ultimoperiodo();
                    $aPensumEst = $this->reinscripciones->getPensumNew($ci, $aUltimoCursado[0]['fk_periodo']);              
                    
                    $aRetiroDef = $this->reinscripciones->RetiroDefinitivo($ci, $aSede[0]['fk_periodo']);
                    $aDatosProfit = $this->profit->VerificarPagoReins($ci, $aPeriodo[0]['pk_periodo'], $aSede[0]['fk_estructura']);

                    
                    $aPago = $aDatosProfit[0][$aDatosProfit[1]['NROPAGO'][0]]['value'];  
//                    $aPago = 12345;  
                    $aDatosUcProfit = $this->profit->VerificarPagoReins($ci, $aPeriodo[0]['pk_periodo'], $aSede[0]['fk_estructura'], 'UC');
//                    $aDatosUcProfit = 0;

//                    $aUcadicioProfit = 0;
                    $count = count($aDatosUcProfit[1]['NROPAGO'])-1;
//                    $count = count(12345)-1;

                    for($i=0; $i<=$count; $i++){
                        
//                        $llamarUc = $aDatosUcProfit[0][$aDatosUcProfit[1]['TOTAL_ART'][$i]]['value'];  
                        $llamarUc = 0;
                        $llamarUc = intval($llamarUc);
                        $aUcadicioProfit += $llamarUc;  
                         
                        
                    }
                    
                    if($aUcadicioProfit >= 6){
                        $aPago = $aDatosUcProfit[0][$aDatosUcProfit[1]['NROPAGO'][0]]['value'];
//                        $aPago = 12345;
                        
                    }
                    
                    if($aUcadicio[0]['ucadicionales']<$aUcadicioProfit){
                        $aUcadicio[0]['ucadicionales'] = $aUcadicioProfit;
                        
                    }
                    
                    $this->datosInsc->pago = $aPago;
                    $this->datosInsc->uc = $aUcadicio[0]['ucadicionales'];
                    
                if($aRetiroDef[0]['cant_pr'] >= 2){
                    $json[] = '$("#infoEstudiante").hide()';
                    $json[] = '$("#erromsg").html("<center><label style=\'color:red; font-size:24px;\'>El estudiante se encuentra en situaci&oacute;n de <br><b>RETIRO DEFINITIVO</b></label></center>")';
                }else{   
                $json[] = '$("#periodoinfo").html("'.$aPeriodo[0]['pk_periodo'].'")';
                $json[] = '$("#ciinfo").html("'.$ci.'")';
                $json[] = '$("#nombreinfo").html("'.$aDatos[0]['nombre'].' '.$aDatos[0]['apellido'].'")';
                $json[] = '$("#cainfo").html("'.$aUcadicio[0]['ucadicionales'].'")';
                $json[] = '$("#sedeinfo").html("'.$aSede[0]['nombre'].'")';
                $json[] = '$("#escuelainfo").html("'.$aEscuela[0]['valor'].'")';
                $json[] = '$("#pensuminfo").html("'.$aPensumEst[0]['name_pensum'].'")'; 

                
                $datosEstudiante .= "<b>Usuario: ".$aDatos[0]['nombre'].' '.$aDatos[0]['apellido']."</b>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
                $datosEstudiante .= "<b>Sede: ".$aSede[0]['nombre']."</b>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
                $datosEstudiante .= "<b>Escuela: ".$aEscuela[0]['valor']."</b>";
                
                $json[] = '$("#user_name").width(800)';
                $json[] = '$("#user_name").html("'.$datosEstudiante.'")';
                
                
                    if($aInscripcion[0]['numeropago'] != '' && $aPago == '' && !$otroDia){//Si tiene pago en OMICRON y en PROFIT NO
                       $json[] = '$("#numcompinfo").html("'.$aInscripcion[0]['numeropago'].'")'; 
                       $json[] = '$("#btn").html("<center>Pago hecho solo en OMICRON<br><input id=btnValidar type=submit value=Aceptar name=btnValidar></center>")';
                    }else if (!empty($aPago) && empty($aInscripcion[0]['numeropago']) && !$otroDia){//Si tiene pago en PROFIT y no en OMICRON    
                       $json[] = '$("#numcompinfo").html("'.$aPago.'")'; 
                       
                       $json[] = '$("#btn").html("<center><input id=btnValidar class=\"button-material ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\" type=submit value=Aceptar name=btnValidar></center>")';
                    }else if (!empty($aPago) && !empty($aInscripcion[0]['numeropago']) && !$otroDia){//Si tiene pago en PROFIT y en OMICRON 
                       $json[] = '$("#numcompinfo").html("'.$aInscripcion[0]['numeropago'].'")';
                       $json[] = '$("#btn").html("<center><input id=btnValidar class=\"button-material ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\"  type=submit value=Aceptar name=btnValidar></center>")';
                    }else if(empty($aPago) && empty($aInscripcion[0]['numeropago'])){//Si no tiene pago en PROFIT y en OMICRON 
                       $json[] = '$("#numcompinfo").html("<label style=color:red;>No tiene Pago Registrado</label>")'; 
                    }else{
                       $json[] = '$("#numcompinfo").html("'.$aInscripcion[0]['numeropago'].'")';
                    }

           //Indice de Estado del estudiante

                  $cuadro = 'Cuadro de honor.';
                  $probatorio = 'Probatorio.';
                  $nuevoingreso = 'Nuevo Ingreso.';
                  $regular = 'Regular.';  


                  if($indicePeriodo >= 16) {

                         $json[] = '$("#estadoes").html("'.$cuadro.'")';
          
                      }
                    elseif($indicePeriodo >= 0 && $indiceAcumulado < 11) {
                        
                        $json[] = '$("#estadoes").html("'.$probatorio.'")';

                    }
                    elseif($indicePeriodo == 0) {

                       $json[] = '$("#estadoes").html("'.$nuevoingreso.'")';


                    }else {

                      $json[] = '$("#estadoes").html("'.$regular.'")';

                    }     

                  
                $json[] = '$("#nombrere").html("'.$aDatos[0]['nombre'].' '.$aDatos[0]['apellido'].'")';
                $json[] = '$("#sedere").html("'.$aSede[0]['nombre'].'")';
                $json[] = '$("#escuelainfore").html("'.$aEscuela[0]['valor'].'")';
                $json[] = '$("#indeceacu").html("'.$indiceAcumulado.'")';
                $json[] = '$("#indicepasa").html("'.$indicePeriodo.'")';
                $json[] = '$("#uca").html("'.$UCA.'")'; 
                $json[] = '$("#creditoa").html("'.$aUcadicio[0]['ucadicionales'].'")';
                $json[] = '$("#periodore").html("'.$aPeriodo[0]['pk_periodo'].'")';
                $json[] = '$("#materiasfs").html("'.$asignaturasFaltantes.'")';


                }
                $this->getResponse()->setBody(Zend_Json::encode($json)); 
        }


        }
  
/*-----------*/
          public function inforeinscripcionAction(){

            $this->SwapBytes_Ajax->setHeader();
            
            $ci = $this->ci->ciId;
            $aDatos = $this->reinscripciones->datosEstudiante($ci);
              
           //Busca la sede del estudiante.
                    $aSede = $this->reinscripciones->sedeUltimo($ci);
                     //Busca la escuela del estudiante.
                    $aEscuela = $this->reinscripciones->escuelaEstudiante($ci); 
                    $aUcadicio = $this->reinscripciones->UnnidadesCreditoAdicionales($ci,$aSede[0]['fk_periodo']);
                    $aDatos = $this->reinscripciones->datosEstudiante($ci);
                    //Busca ultmimo periodo cursado
                    $aPeriodo = $this->reinscripciones->ultimoperiodo();
                    $aInscripcion = $this->reinscripciones->buscarInscripcion($ci,$aPeriodo[0]['pk_periodo']);

//                    $aPensum = $this->reinscripciones->ultimoPensumEscuela($aEscuela[0]['pk_atributo']);
//                    $aPensumEst = $this->reinscripciones->getPensum($ci, $aEscuela[0]['pk_atributo']);
                   
                    $aUltimoCursado = $this->reinscripciones->sedeUltimoPeriodo($ci);
                    $aCheck = $this->reinscripciones->checkNuevoPensum($aUltimoCursado[0]['fk_periodo'], $aSede[0]['fk_estructura'], $ci);
                    $aIia = $this->reinscripciones->IndiceAcademicoAcumuladoEscuela($ci, $aEscuela[0]['pk_atributo'], $aUltimoCursado[0]['fk_periodo'], $aCheck[0]['codigopropietario']);
                    $aIap = $this->reinscripciones->indicePeriodo($ci,$aSede[0]['fk_periodo']-1, $aEscuela[0]['pk_atributo']);
                    $aBiblio = $this->reinscripciones->checkBiblioteca($ci);

                    $count = count($aBiblio);

                    $indiceAcumulado = $aIia[0]['fn_xrxx_estudiante_iia_escuela_periodo_articulado'];
                    $indicePeriodo = $aIap[0]['iiap'];    
                    $otroDia = false;
                    $activado = true;//Desactivar para que salga el mensaje de pira                    


                if($count > 0){

                $mensaje .= "<center><table>";
                $mensaje .= "<tr>";
                $mensaje .= "<td style=\"color: red; font-size:20\";><b>&nbsp;El estudiante posee libros {$count} de la bilioteca en Transito o Mora</b></td>";
                $mensaje .= "</tr>";
                $mensaje .= "<tr>";
                $mensaje .= "<td style=\"color: red; font-size:25; text-align:center;\";><b>Debe realizar la devolución para poder inscribirse</b></td>";
                $mensaje .= "</tr>";
                $mensaje .= "</table></center>";                             
                                 
                $json[] = "$('#caja').html('$mensaje');";
                             
            }else if($retDef[0]['count'] >= 1 && $indiceAcumulado < 11 && $indecePeriodo < 11){
                
                $mensaje .= "<center><table>";
                $mensaje .= "<tr>";
                $mensaje .= "<td style=\"color: red; font-size:20\";><b>EL estudiante se encuentra en situaci&oacute;n de</b></td>";
                $mensaje .= "</tr>";
                $mensaje .= "<tr>";
                $mensaje .= "<td style=\"color: red; font-size:25; text-align:center;\";><b>Retiro Definitivo</b></td>";
                $mensaje .= "</tr>";
                $mensaje .= "<tr>";
                $mensaje .= "<td style=\"color: red; font-size:25; text-align:center;\";><b>Indice Acumulado: $indiceAcumulado</b></td>";
                $mensaje .= "</tr>";
                $mensaje .= "<tr>";
                $mensaje .= "<td style=\"color: red; font-size:25; text-align:center;\";><b>Indice Academico Per&iacute;odo: $indecePeriodo</b></td>";
                $mensaje .= "</tr>";
                $mensaje .= "</table></center>";
                
                $json[] = "$(datosEstudiante).hide();" ;               
                $json[] = "$('#caja').html('$mensaje');";
                
            }else{
            
            $usuaripgrupo = $this->reinscripciones->datosEstudiante($ci);
            //Busca la sede del estudiante.
            $aSede = $this->reinscripciones->sedeUltimo($ci);
            //Busca la escuela del estudiante.  
            $aEscuela = $this->reinscripciones->escuelaEstudiante($ci);
            //Busca y ultimo periodo inscrito  
            $aPeriodo = $this->reinscripciones->ultimoperiodo();
            //Ultima inscripcion
            $aInscripcion = $this->reinscripciones->buscarInscripcion($ci,$aPeriodo[0]['pk_periodo']);
            
            $aPago = $this->profit->VerificarPago($ci, $aPeriodo[0]['pk_periodo'], $aSede[0]['fk_estructura']);
//            $aUcadicio = $this->reinscripciones->UnnidadesCreditoAdicionales($ci,$aPeriodo[0]['pk_periodo']);

            $pkusuariogrupo = $this->reinscripciones->pkusuariogrupo($ci);
            
            $sinPeriodo = $this->reinscripciones->SinPeriodosAnteriores($ci, $aPeriodo[0]['pk_periodo']);
            
            $aIia = $this->reinscripciones->indiceAcademico($ci, $aEscuela[0]['pk_atributo']);
            
            $ultPeri = $this->reinscripciones->ultimoperiodo();
            $aPensumEst = $this->reinscripciones->getPensumNew($ci, $aUltimoCursado[0]['fk_periodo']);
            
            
            $aUcadicio = 0;
            
            if(!empty($this->datosInsc->pago) && $this->datosInsc->pago > 0){
                $aPago = $this->datosInsc->pago;   
            }
            
            if(!empty($this->datosInsc->uc) && $this->datosInsc->uc > 0){
                $aUcadicio = $this->datosInsc->uc;
            }        
            
            if($aIia[0]['fn_xrxx_estudiante_iia_escuela_new']<11 && $aPeriodo[0]['date']<'2011-12-13' && (!empty($sinPeriodo))){
                $json[] = '$("#erromsg").html("<label style=\'color:red; font-size:24px;\'>Se debe inscribir el dia 12/12/2019</label>")';
            }else{
                  
            
                if(!empty($aInscripcion[0]['numeropago']) && empty($aPago)){//Si tiene pago en OMICRON y en PROFIT NO
                    $this->reinscripciones->actualizarInscripcion($aInscripcion[0]['pk_inscripcion'],$aInscripcion[0]['numeropago'], 1621, $aUcadicio);
                }else if (!empty($aPago) && empty($aInscripcion[0]['numeropago'])){//Si tiene pago en PROFIT y no en OMICRON  
                    

                    if(empty($aUcadicio)){
                        $uca = 0;
                    }else{
                        $uca = $aUcadicio;
                    }
                    $this->reinscripciones->insertarInscripcion($pkusuariogrupo[0]['pk_usuariogrupo'], $aPago, $aPeriodo[0]['pk_periodo'], $aEscuela[0]['pk_atributo'], $aSede[0]['fk_estructura'], $uca, 1621,$aPensumEst[0]['fk_pensum']);
                }else if (!empty($aPago) && !empty($aInscripcion[0]['numeropago'])){//Si tiene pago en PROFIT Y EN OMICRON
                    $this->reinscripciones->actualizarInscripcion($aInscripcion[0]['pk_inscripcion'],$aPago, 1621, $aUcadicio);
                 }else if(empty($aPago) && empty($aInscripcion[0]['numeropago'])){//Si no tiene pago en PROFIT y OMICRON
                   echo error;
                }

              }


            }


          //Donde se trae las materias que va a incribir un estudiante
            //0202
            $data =  $this->data = $this->reinscripciones->MateriasInscripcion($ci,$aEscuela[0]['pk_atributo'],$aPeriodo[0]['pk_periodo']);
                      //Contruccion de tabla de materias
           $HTML .= "<table align=center id = materia><tr><th> Codigo Materia </th><th> Materia</th><th> U.C </th><th> Semestre </th><th> Turno </th><th> Seccion </th><th> Horario </th><th> Requisitos </th><th> Calificacion </th><th> Inscritos </th></tr>";

          $new_turno = array();
          $c = 0;
          $prelacion_temp = array();
          $UCA = $this->RecordAcademico->getUCA($ci,$aEscuela[0]['pk_atributo']);
          $array_cod = array(); //Se llena con los requisitos... sin el orden de las materias no sirve
          //AQUI SE CREA LA TABLA DE LA INSCRIPCION
           
        

        foreach ($data as $key => $resultado) { 
            
                   
            $array_cod[] = substr($resultado['codigopropietario'],4,8);     
            $prelacion[] = split(",", (trim(($resultado['prelacion']),"{}\"")));
            $turnos = $this->SwapBytes_String->arrayDbToArrayPHP($resultado["turno"]);
            $secciones = $this->SwapBytes_String->arrayDbToArrayPHP($resultado["seccion"]);
            $semestres = $this->SwapBytes_String->arrayDbToArrayPHP($resultado["semestre"]);
           

            if(in_array($prelacion[$key][0], $array_cod) || trim(substr($resultado['uc'],0,3)) > $UCA || in_array($prelacion[$key][1], $array_cod)){
                 $HTML .= "<tr id = \"{$resultado['codigopropietario']}\" class = \"prelada\">";
            }else{
            $HTML .= "<tr id = \"{$resultado['codigopropietario']}\" class=\"disponible\" >";
            }
            $HTML .= "<td id = codigopro>".substr($resultado['codigopropietario'],4,8)."</td>";
            $HTML .= "<td class=\"materia\" id=\"".$resultado['codigopropietario']."\" >".$resultado['materia']."</td>";
            $HTML .= "<td id = uc>".$resultado['unidadcredito']."</td>";

            $disabled = (count($semestres) > 1 ? "" : "disabled");
            $HTML .= "<td class=\"semestres\"><select  {$disabled}>";
            foreach ($semestres as $key => $value) {
                $HTML .= "<option value=\"{$key}\">".$value."</option>";
            }
            $disabled = (count($turnos) > 1 ? "" : "disabled");
            $HTML .= "<td class=\"turnos\" ><select {$disabled}>";
            foreach ($turnos as $key => $value) {
                $HTML .= "<option value=\"{$key}\">".$value."</option>";
            }

            $HTML .= "</select></td>";
            $disabled = (count($secciones) > 1 ? "" : "disabled");
            $HTML .="<td class=\"secciones\"><select  {$disabled}>";
            foreach ($secciones as $key => $value) {
                $HTML .= "<option value=\"{$key}\">".$value."</option>";
            }
            $HTML .= "</select></td>";   
            $HTML .= "<td id = \"horario{$resultado["pk_asignatura"]}\" class></td>";


            if (trim(($data[$c]['prelacion']),"{}\"") != "0000") {
             $HTML .= "<td id = \"prelacion{$resultado["pk_asignatura"]}\">".trim(($resultado['prelacion']),"{}\"")." ".$resultado['uc']."</td>";
             if(trim(substr($resultado['uc'],0,3),"")!= ''){
                if(trim(($resultado['prelacion']),"{}\"") == substr($data[$c]['codigopropietario'],4,8) || trim(substr($resultado['uc'],0,3)) > $UCA ) { //Antes de aca llenar el arreglo ... etc
                   
                       $json[] = "$('.disponible').find('tr').css('background-color','blue').removeClass('materiaSelect');";

                       } 
                   }
            }else{
                $HTML .= "<td id = \"prelacion{$resultado["pk_asignatura"]}\">".$resultado['uc']."</td>";
            }
            $HTML .= "<td id = \"calificacion{$resultado["pk_asignatura"]}\">".$resultado['calificacion']."</td>";
            $HTML .= "<td id = \"inscritos{$resultado["pk_asignatura"]}\"></td>";
            $HTML .= "</tr>"; 
  
            $turnos="";
            $c++;

          
        }

            $HTML .= "</table>";
            
            //Enviamos la tabla a la vista por json
            $json[] .= $this->SwapBytes_Jquery->setHtml('tblMaterias', $HTML);
            //Clases para los click de la vista
            ////////////////////////////////////AQUII/////////////////////////////// 
            $json[] .= "$('tr.disponible').click(function(){
                            if ($(this).hasClass('materiaSelect') == false){
                                $(this).addClass('materiaSelect'); 
                            }else{
                                $(this).removeClass('materiaSelect');
                        }                      
                      });";
            $json[] = "postAcepted();";               
        
            $this->getResponse()->setBody(Zend_Json::encode($json));    

          }

/*-----*/public function horariosinscritosAction(){
            $this->SwapBytes_Ajax->setHeader();
            //Data
            $ci       = $this->ci->ciId;           
            $aEscuela = $this->reinscripciones->escuelaEstudiante($ci);
            $aPeriodo = $this->reinscripciones->ultimoperiodo();
            $data =  $this->data = $this->reinscripciones->MateriasInscripcion($ci,$aEscuela[0]['pk_atributo'],$aPeriodo[0]['pk_periodo']);
            $aSede = $this->reinscripciones->sedeUltimo($this->ci->ciId);
            //Request Ajax
            $codigo   = $this->_getParam('codigo',0);
            $semestre = $this->_getParam('semestre',0);
            $turno    = $this->_getParam('turno',0);
            $seccion  = $this->_getParam('seccion',0);
            $touch    = $this->_getParam('touch');
            //Coincidencia
            $coincidencia = array();
            //$array_cod = array("codigo"     => $codigo)
            $count    = count($codigo);
            for ($i = 0; $i < $count; $i++) { 
                $horarios = $this->horario->horarioReinscripcion($aSede[0]['fk_estructura'],$aSede[0]['fk_periodo'],$codigo[$i]);
                $cod = $codigo[$i];
                foreach ($horarios as $key => $value) {
                    $tmp =  array(
                                "asignatura" => $value['fk_asignatura'],
                                "dia"        => $value['fk_dia'],
                                "hora"       => $value['horario'],
                                "codigo"     => $codigo[$i]
                            );
                    array_push($coincidencia,$tmp);                 
                }

            }
           // var_dump($Coincidencia['asignatura']);
            //Asignatura
            $asignatura = $this->reinscripciones->getAsignatura($touch['semestre'],$touch['turno'],$touch['seccion'],$touch['codigo'],$aPeriodo[0]['pk_periodo']);
            //Generar Horario
            $horarios = $this->horario->horarioReinscripcion($aSede[0]['fk_estructura'],$aSede[0]['fk_periodo'],$touch['codigo']);
            foreach ($horarios as $key => $value) {
                $hora_definitiva .= '<p style="text-align: justified;">'.$value['dia']." ".substr($value['inicio'],0,5)." / ".substr($value['horafin'],0,5).'</p>';  
            }   
            //AQUI BUSCAMOS LOS INSCRITOS POR MATERIA
            $inscritomateria  = $this->reinscripciones->CantidadDeInscritosPorMateria($aPeriodo[0]['pk_periodo'],$aSede[0]['fk_estructura'], $ci,$touch['codigo'],$touch['semestre'],$touch['turno'],$touch['seccion']);
            $cuposmaximosmateria = $this->reinscripciones->getCuposMax($aPeriodo[0]['pk_periodo'],$aSede[0]['fk_estructura'],$touch['codigo'],$touch['semestre'],$touch['turno'],$touch['seccion']);
            if($inscritomateria[0][1] == 0){
                $inscritomateria = 0;
            }
            if($cuposmaximosmateria[0][1] == NULL){
                $cuposmaximosmateria = 0;
            }
            //Insertar Horario
            $id = $touch['codigo'];
            $json[] .= "if($('#{$id}').hasClass('materiaSelect') == true && $('#{$id}').hasClass('prelada') == false) {
                            $('#horario{$asignatura}').html('{$hora_definitiva}');
                            $('#inscritos{$asignatura}').html('$inscritomateria de $cuposmaximosmateria');
                        }else{
                            $('#horario{$asignatura}').html('');
                             $('#inscritos{$asignatura}').html('');
                        }";

            for ($i=0; $i < count($coincidencia) ; $i++) { 
                for ($j=0; $j < count($coincidencia) ; $j++) {
                    if ($coincidencia[$i]['asignatura'] != $coincidencia[$j]['asignatura']
                        && $coincidencia[$i]['dia'] == $coincidencia[$j]['dia']
                        && $coincidencia[$i]['hora'] == $coincidencia[$j]['hora']
                        /*&& $i != $j*/) {
                        var_dump($coincidencia[$i]['codigo'],$coincidencia[$j]['codigo']);

                        $codigo_c = substr($coincidencia[$j]['codigo'],4,8);


                        $json[] .= "if($('#{$id}').hasClass('materiaSelect') == true && $('#{$id}').hasClass('prelada') == false) {
                            $('#horario{$asignatura}').html('Coincide con: {$codigo_c}');
                            }else{
                            $('#horario{$asignatura}').html('');
                        }";

                        
                    } 
                }
            }

          
                
            echo Zend_Json::encode($json);
        
        } 

/*----*/public function verificarCoincidencia(){

           
        }

        
        public function setData($ci,$escuela,$periodo){

            $this->data = $this->reinscripciones->MateriasInscripcion($ci,$escuela,$periodo);

        }
                                    
}
?>
