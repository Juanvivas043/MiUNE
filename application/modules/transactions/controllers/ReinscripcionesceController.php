<?php
class Transactions_ReinscripcionesceController extends Zend_Controller_Action {
    private $Title  = 'Reinscripciones \ Carga de reinscripcion';
        public function init() {
        /* Initialize action controller AQUI */
            Zend_Loader::loadClass('Models_DbTable_Reinscripciones');
            Zend_Loader::loadClass('Models_DbTable_Profit');
            Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
            Zend_Loader::loadClass('Models_DbTable_Usuarios');
	    Zend_Loader::loadClass('Models_DbTable_Asignaturas');
            Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
            Zend_Loader::loadClass('Models_DbTable_Horarios');
            Zend_Loader::loadClass('Models_DbTable_Periodos');
            Zend_Loader::loadClass('Une_Filtros');
            Zend_Loader::loadClass('Une_Sweetalert');
            
            $this->reportes              = new CmcBytes_GenerarReportes();
            $this->grupo                 = new Models_DbTable_UsuariosGrupos();
            $this->usuarios              = new Models_DbTable_Usuarios();
	    $this->asignaturas           = new Models_DbTable_Asignaturas();
            $this->reinscripciones       = new Models_DbTable_Reinscripciones();
            $this->profit                = new Models_DbTable_Profit();
            $this->RecordAcademico       = new Models_DbTable_Recordsacademicos();
            $this->horario               = new Models_DbTable_Horarios;
            $this->periodos              = new Models_DbTable_Periodos();
            $this->authSpace             = new Zend_Session_Namespace('Zend_Auth');
            $this->sweetalert            = new Une_Sweetalert();

            $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();

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
            $this->UCU = array();

            $this->SwapBytes_Crud_Search->setDisplay(true);
            $this->date = $this->periodos->getInicio(($this->periodos->getPeriodoActual()))." 00:00:00.000";
            $this->redirect = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['CONTEXT_PREFIX'].'/inicio';
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
            $this->view->filters          = $this->Une_Filtros;
            $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
            $this->view->sweetalert       = $this->sweetalert;
            $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
            $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
            $this->view->SwapBytes_Ajax   = new SwapBytes_Ajax();
            $this->view->SwapBytes_Ajax->setView($this->view);
            $this->acceso->pk = 145; //pk_acceso de Reinscriciones
            
        }   
            public function cvAction(){

                $this->SwapBytes_Ajax->setHeader();
                $cv = $this->_getParam('clave',0);
                if($cv== '35504352' or $cv == '2018' or $cv == '1935gr'){
                    $json[] = " if(!$('#msg').is(':visible')){
                                   $('#btnInscribir').addClass('btnVisible').removeClass('btnUnvisible').attr('disabled', false);
                                }else{
                                    swal({ title: 'Error', text: 'No es posible inscribir al estudiante con unidades de crédito insuficientes', type: 'error'});
                                }";
                }else{
                     $json[] = $this->sweetalert->setBasicAlert("error","Error","Clave invalida por favor ingresela nuevamente");
                }

                echo Zend_Json::encode($json);
            }
        
/*---------*/public function asistidaAction(){
            
            $this->view->title                    = $this->Title;
            $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
            $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
            $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
            $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
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
                    $c = 0;
                    $ci = $this->_getParam('cedula',0);
                    if(!is_numeric($ci)  or empty($ci)){
                        $c=1;
                        $json[] = '$("#informacion").hide()';
                        $json[] = $this->sweetalert->setBasicAlert("error","Error","Ingrese una cédula que solo contenga caracteres numéricos, no puede estar vacía ");
                        $this->getResponse()->setBody(Zend_Json::encode($json));
                    }else{
                        $this->user = $this->usuarios->getPerson($ci);
                        if($this->user == NULL){
                            $c=2;  
                            $json[] = $this->sweetalert->setBasicAlert("error","Error","La cédula no esta asociada a ningún usuario, por favor ingrese una nueva cédula");
                            $json[] = '$("#informacion").hide()';
                            $this->getResponse()->setBody(Zend_Json::encode($json));
                        }
                    }
                    if($c==0){
                        $this->grupos = $this->reinscripciones->getGrupos($ci);
                        foreach ($this->grupos as $key => $value) {
                            if($value['fk_grupo'] == 855){
                                $estudiante = 1;
                            }
                        }
                        if($estudiante != 1){
                            $c = 3;  
                        }
                        if($c != 3){
                            $json[] = "$('#informacion').show();";
                            $aSede = $this->reinscripciones->sedeUltimo($ci);
                            //Busca la escuela del estudiante.
                            $aEscuela = $this->reinscripciones->escuelaEstudiante($ci);
                            //Busca ultmimo periodo
                            $aPeriodo = $this->reinscripciones->ultimoperiodo();
                            //Busca creditos adicionales
                     
			    $aUcadicio      = $this->profit->getUc($ci,$aPeriodo[0]['pk_periodo'], $this->date);
                    	    if(!isset($aUcadicio)){
                        	$aUcadicio = $this->reinscripciones->uca($ci,$aPeriodo[0]['pk_periodo'],$aEscuela[0]['pk_atributo'])[0]['ucadicionales'];
                        	if(!isset($aUcadicio)){
                        		$aUcadicio = 0;
                        	}
                    	    }else{
                                $aUcadicio = (int)$aUcadicio;
                            }
                            
                            //Busca datos de estudiante que se reinscribira
                            $aDatos = $this->reinscripciones->datosEstudiante($ci);
                            //Busca inscripcion del estudiante
                            $aInscripcion = $this->reinscripciones->buscarInscripcion($ci,$aPeriodo[0]['pk_periodo']);
                            //var_dump($aInscripcion);die;
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
                            if($aUltimoCursado == NULL){
                                $aUltimoCursado = $this->reinscripciones->sedePrimerPeriodo($ci);
                            }
                            //Chequea si eres pensum viejo o nuevo
                            $aCheck = $this->reinscripciones->checkNuevoPensum($aUltimoCursado[0]['fk_periodo'], $aSede[0]['fk_estructura'], $ci);
                            //total del indice acumula en todo los periodo
                            $aIia = $this->reinscripciones->IndiceAcademicoAcumuladoEscuela($ci, $aEscuela[0]['pk_atributo'], $aUltimoCursado[0]['fk_periodo'], $aCheck[0]['codigopropietario']);
                            // Busca el indice del periodo anterior                  
                            $aIap = $this->reinscripciones->indicePeriodo($ci,$aUltimoCursado[0]['fk_periodo'], $aEscuela[0]['pk_atributo']);
                            //Busca el pensum actual
                            $aPensumEst = $this->reinscripciones->getPensumNew($ci, $aUltimoCursado[0]['fk_periodo']);
                           //Indice acumulado y del periodo
                            $indiceAcumulado = $aIia[0]['fn_xrxx_estudiante_iia_escuela_periodo_articulado'];
                            $indicePeriodo = $aIap[0]['iiap']; 
                            $otroDia = false;
                            $activado = false;//Desactivar para que salga el mensaje de pira
                            $countins = $this->reinscripciones->countins($ci,$aEscuela[0]['pk_atributo'],$aPeriodo[0]['pk_periodo'],$aSede[0]["fk_estructura"]);
                            if($countins[0]['count']>1){
                                $json[] = '$("#informacion").hide()';
                                $json[] = $this->sweetalert->setBasicAlert("error","error","Posee más de una inscripción, debe dirigirse a la coordinación de sistemas para realizar su inscripción");
                                $json[] .= "$('.confirm').click(function(){window.location.replace('$this->redirect')});";
                            }else{
                                
                                $ultPeri = $this->reinscripciones->ultimoperiodo();
                                $aPensumEst = $this->reinscripciones->getPensumNew($ci, $aUltimoCursado[0]['fk_periodo']);              
                                $aRetiroDef = $this->reinscripciones->RetiroDefinitivo($ci, $aSede[0]['fk_periodo']);
                                $aDatosProfit = $this->profit->VerificarPagoReins($ci, $aPeriodo[0]['pk_periodo'], $aSede[0]['fk_estructura']);                    
                                $aPago = $aDatosProfit[0][$aDatosProfit[1]['NROPAGO'][0]]['value'];  
                                //$aPago = 12345;  
                                $aDatosUcProfit = $this->profit->VerificarPagoReins($ci, $aPeriodo[0]['pk_periodo'], $aSede[0]['fk_estructura'], 'UC');
                                //var_dump($aDatosUcProfit);die;
                                //$aDatosUcProfit = 0;

                                //$aUcadicioProfit = 0;
                                $count = count($aDatosUcProfit[1]['NROPAGO'])-1;
                                //$count = count(12345)-1;

                                for($i=0; $i<=$count; $i++){
                                    $aPeriodo = $this->reinscripciones->ultimoperiodo();
                                    //$llamarUc = $aDatosUcProfit[0][$aDatosUcProfit[1]['TOTAL_ART'][$i]]['value'];  
                                    $llamarUc = 0;
                                    $llamarUc = intval($llamarUc);
                                    $aUcadicioProfit += $llamarUc;  
                                     
                                    
                                }
                            
                                if($aUcadicioProfit >= 6){
                                    $aPago = $aDatosUcProfit[0][$aDatosUcProfit[1]['NROPAGO'][0]]['value'];
                                    //$aPago = 12345;
                                    
                                }
                                
                                if($aUcadicio<$aUcadicioProfit){
                                    $aUcadicio = $aUcadicioProfit;
                                    
                                }

                                //var_dump($aUcadicio[0]['ucadicionales'] )die;
                                
                                $this->datosInsc->pago = $aPago;
                                $this->datosInsc->uc = $aUcadicio;
                                
                                if($aRetiroDef[0]['cant_pr'] >= 2){
                                    $json[] = '$("#infoEstudiante").hide()';
                                    $json[] = '$("#erromsg").html("<center><label style=\'color:red; font-size:24px;\'>El estudiante se encuentra en situaci&oacute;n de <br><b>RETIRO DEFINITIVO</b></label></center>")';
                                }else{   
                                    /*si el estudiante no se encuentra en retiro definitivo entonces se buscan los datos del estudiante*/
                                    $json[] = '$("#periodoinfo").html("'.$aPeriodo[0]['pk_periodo'].'")';
                                    $json[] = '$("#ciinfo").html("'.$ci.'")';
                                    $json[] = '$("#nombreinfo").html("'.$aDatos[0]['nombre'].' '.$aDatos[0]['apellido'].'")';
                                    $json[] = '$("#cainfo").html("'.$aUcadicio.'")';
                                    $json[] = '$("#sedeinfo").html("'.$aSede[0]['nombre'].'")';
                                    $json[] = '$("#escuelainfo").html("'.$aEscuela[0]['valor'].'")';
                                    $json[] = '$("#pensuminfo").html("'.$aPensumEst[0]['name_pensum'].'")'; 

                                    $dia = date("d-m-y");
                                    $datosEstudiante .= "<b>Usuario: ".$aDatos[0]['nombre'].' '.$aDatos[0]['apellido']."</b>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
                                    $datosEstudiante .= "<b>Sede: ".$aSede[0]['nombre']."</b>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
                                    $datosEstudiante .= "<b>Escuela: ".$aEscuela[0]['valor']."</b>&nbsp;&nbsp;&nbsp;|&nbsp;";
                                    $datosEstudiante .= "<b>Dia: ".$dia."</b>";
                                    
                                    $json[] = '$("#user_name").width(800)';
                                    $json[] = '$("#user_name").html("'.$datosEstudiante.'")';
                                    
                                    
                                    if($aInscripcion[0]['numeropago'] != '' && $aPago == '' && !$otroDia){//Si tiene pago en OMICRON y en PROFIT NO
                                       $json[] = '$("#numcompinfo").html("'.$aInscripcion[0]['numeropago'].'")'; 
                                       $json[] = '$("#btn").html("<center>Pago hecho solo en OMICRON<br><input id=btnValidar class=\"button-material ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\" type=submit value=Aceptar name=btnValidar></center>");
                                           $("#btnValidar").click(function() {
                                            $("#informacion").hide();
                                            $("#footer").fadeIn();
                                            $(".prob").hide();
                                            $("#datosEstudiante").show();
                                            $("#tblMaterias").show();
                                            $(".bar").css("display","block");   
                                            $("select").attr("disabled", false);
                                            inforeinscripciones();
                                            });';
                                    }else if (!empty($aPago) && empty($aInscripcion[0]['numeropago']) && !$otroDia){//Si tiene pago en PROFIT y NO en OMICRON    
                                       $json[] = '$("#numcompinfo").html("'.$aPago.'")'; 
                                       
                                       $json[] = '$("#btn").html("<center>Pago hecho solo en OMICRON<br><input id=btnValidar class=\"button-material ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\" type=submit value=Aceptar name=btnValidar></center>");
                                           $("#btnValidar").click(function() {
                                            $("#informacion").hide();
                                            $("#footer").fadeIn();
                                            $(".prob").hide();
                                            $("#datosEstudiante").show();
                                            $("#tblMaterias").show();
                                            $(".bar").css("display","block");   
                                            $("select").attr("disabled", false);
                                            inforeinscripciones();
                                            });';
                                    }else if (!empty($aPago) && !empty($aInscripcion[0]['numeropago']) && !$otroDia){//Si tiene pago en PROFIT y en OMICRON 
                                       $json[] = '$("#numcompinfo").html("'.$aInscripcion[0]['numeropago'].'")';
                                       $json[] = '$("#btn").html("<center>Pago hecho solo en OMICRON<br><input id=btnValidar class=\"button-material ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\" type=submit value=Aceptar name=btnValidar></center>");
                                           $("#btnValidar").click(function() {
                                            $("#informacion").hide();
                                            $("#footer").fadeIn();
                                            $(".prob").hide();
                                            $("#datosEstudiante").show();
                                            $("#tblMaterias").show();
                                            $(".bar").css("display","block");   
                                            $("select").attr("disabled", false);
                                            inforeinscripciones();
                                            });';
                                    }else if(empty($aPago) && empty($aInscripcion[0]['numeropago'])){//Si NO tiene pago en PROFIT y en OMICRON 
                                       $json[] = '$("#numcompinfo").html("<label style=color:red;>No tiene Pago Registrado</label>")'; 
                                    }else{
                                       $json[] = '$("#numcompinfo").html("'.$aInscripcion[0]['numeropago'].'")';
                                    }

                            //Indice de Estado del estudiante, por ahora el estado de probatorio no existe, se realiza la validacion del 50% de las materias aplazadas

                                    $cuadro       = 'Cuadro de honor.';
                                    $probatorio   = 'Probatorio.';// pira
                                    $nuevoingreso = 'Nuevo Ingreso.';
                                    $regular      = 'Regular.';  


                                    if($indicePeriodo >= 16) {

                                         $json[] = '$("#estadoes").html("'.$cuadro.'")';
                          
                                    }
                                    /*elseif($indicePeriodo >= 0 && $indiceAcumulado < 11) {
                                        
                                        $json[] = '$("#estadoes").html("'.$probatorio.'")';
                                        //estado relacionado a pira
                                    }*/
                                    elseif($indicePeriodo == 0 && $indiceAcumulado==0) {

                                       $json[] = '$("#estadoes").html("'.$nuevoingreso.'")';


                                    }else {

                                      $json[] = '$("#estadoes").html("'.$regular.'")';

                                    }     
                                    /* datos de informacion del estudiante*/
                                    $json[] = '$("#nombrere").html("'.$aDatos[0]['nombre'].' '.$aDatos[0]['apellido'].'")';
                                    $json[] = '$("#sedere").html("'.$aSede[0]['nombre'].'")';
                                    $json[] = '$("#escuelainfore").html("'.$aEscuela[0]['valor'].'")';
                                    $json[] = '$("#indeceacu").html("'.$indiceAcumulado.'")';
                                    $json[] = '$("#indicepasa").html("'.$indicePeriodo.'")';
                                    $json[] = '$("#uca").html("'.$UCA.'")'; 
                                    $json[] = '$("#creditoa").html("'.$aUcadicio.'")';
                                    $json[] = '$("#periodore").html("'.$aPeriodo[0]['pk_periodo'].'")';
                                    $json[] = '$("#materiasfs").html("'.$asignaturasFaltantes.'")';
                                    $json[] = '$("#pensumre").html("'.$aPensumEst[0]['name_pensum'].'")'; 
                                      
                                            if($aInscripcion!= NULL){ //para validar que no tenga una inscripcion ya hecha en el periodo actual
                                                $json[]= "$('#mensajeprob').html('USTED YA POSEE UNA INSCRIPCIÓN PARA ESTE PERÍODO, INGRESE AL MÓDULO SI DESEA GENERAR UNA NUEVA INSCRIPCIÓN');
                                                          $('#mensajeprob').removeClass('disable').addClass('reins');
                                                              ";  
                                            }
                                        
                                }
                            }
                        }
             
                if($c==3){
                    $json[] = "$('#informacion').hide();";
                    $json[] = $this->sweetalert->setBasicAlert("error","Error","La cédula ingresada no pertenece a ningun estudiante");
                }
                if($c==0){    
                    $json[] = '$(tblbusqueda).hide();';
                }
                $this->getResponse()->setBody(Zend_Json::encode($json)); 
        }


        }
/*-----*/public function inforeinscripcionAction(){

            $this->SwapBytes_Ajax->setHeader();
            $ci =  $this->_getParam('cedula',0);
            $aDatos = $this->reinscripciones->datosEstudiante($ci);
            //$codigo = $this->_getParam('codigo',0);
            //$retsel = $this->_getParam('codigoret',0);
           //Busca la sede del estudiante.
                    $aSede          = $this->reinscripciones->sedeUltimo($ci);
                     //Busca la escuela del estudiante.
                    $aEscuela       = $this->reinscripciones->escuelaEstudiante($ci); 
                    $aPeriodo       = $this->reinscripciones->ultimoperiodo();
		    $aUcadicio      = $this->profit->getUc($ci,$aPeriodo[0]['pk_periodo'], $this->date);
                    if(!isset($aUcadicio)){
                        $aUcadicio = $this->reinscripciones->uca($ci,$aPeriodo[0]['pk_periodo'],$aEscuela[0]['pk_atributo'])[0]['ucadicionales'];
                        if(!isset($aUcadicio)){
                        $aUcadicio = 0;
                        }
                    }
                    
                    $aDatos         = $this->reinscripciones->datosEstudiante($ci);
                    //Busca ultmimo periodo cursado
                    $aInscripcion   = $this->reinscripciones->buscarInscripcion($ci,$aPeriodo[0]['pk_periodo']);
                     $aUltimoCursado = $this->reinscripciones->sedeUltimoPeriodo($ci);
                    if($aUltimoCursado == NULL){
                        $aUltimoCursado = $this->reinscripciones->sedePrimerPeriodo($ci);
                    }
                  
//                    $aPensum = $this->reinscripciones->ultimoPensumEscuela($aEscuela[0]['pk_atributo']);
//                    $aPensumEst = $this->reinscripciones->getPensum($ci, $aEscuela[0]['pk_atributo']);
                   
                    $aCheck  = $this->reinscripciones->checkNuevoPensum($aUltimoCursado[0]['fk_periodo'], $aSede[0]['fk_estructura'], $ci);
                    $aIia    = $this->reinscripciones->IndiceAcademicoAcumuladoEscuela($ci, $aEscuela[0]['pk_atributo'], $aUltimoCursado[0]['fk_periodo'], $aCheck[0]['codigopropietario']);
                    $aIap    = $this->reinscripciones->indicePeriodo($ci,$aSede[0]['fk_periodo']-1, $aEscuela[0]['pk_atributo']);
                    $aBiblio = $this->reinscripciones->checkBiblioteca($ci);

                    $count   = count($aBiblio);

                    $indiceAcumulado = $aIia[0]['fn_xrxx_estudiante_iia_escuela_periodo_articulado'];
                    $indicePeriodo   = $aIap[0]['iiap'];    
                    $otroDia         = false;
                    $activado        = true;//Desactivar para que salga el mensaje de pira                    


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
                    $mensaje .= "<td style=\"color: red; font-size:25; text-align:center;\";><b>Retiro
                        $ci = $ci;


                        if(!empty($ci)){
                        
                         //Busca la sede del estudiante.
                        $aSede = $this->reinscripciones->sedeUltimo($ci); Definitivo</b></td>";
                    $mensaje .= "</tr>";
                    $mensaje .= "<tr>";
                    $mensaje .= "<td style=\"color: red; font-size:25; text-align:center;\";><b>Indice Acumulado: $indiceAcumulado</b></td>";
                    $mensaje .= "</tr>";
                    $mensaje .= "<tr>";
                    $mensaje .= "<td style=\"color: red; font-size:25; text-align:center;\";><b>Indice Academico Per&iacute;odo: $indecePeriodo</b></td>";
                    $mensaje .= "</tr>";
                    $mensaje .= "</table></center>";
                    
                    $json[]   = "$(datosEstudiante).hide();" ;               
                    $json[]   = "$('#caja').html('$mensaje');";
                
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
                //$json[] = '$("#erromsg").html("<label style=\'color:red; font-size:24px;\'>Se debe inscribir el día 16/12/2012</label>")';
            }else{
                  
            
                if(!empty($aInscripcion[0]['numeropago']) && empty($aPago)){//Si tiene pago en OMICRON y en PROFIT NO
                    $this->reinscripciones->actualizarInscripcion($aInscripcion[0]['pk_inscripcion'],$aInscripcion[0]['numeropago'], 1621, $aUcadicio);
                }else if (!empty($aPago) && empty($aInscripcion[0]['numeropago'])){//Si tiene pago en PROFIT y no en OMICRON  
                    

                    if(empty($aUcadicio)){
                        $uca = 0;
                    }else{
                        $uca = $aUcadicio;
                    }
                    $this->reinscripciones->insertarInscripcion($pkusuariogrupo[0]['pk_usuariogrupo'], $aPago, $aPeriodo[0]['pk_periodo'], $aEscuela[0]['pk_atributo'], $aSede[0]['fk_estructura'], $uca, 1621,'false',$aPensumEst[0]['fk_pensum']);
                }else if (!empty($aPago) && !empty($aInscripcion[0]['numeropago'])){//Si tiene pago en PROFIT Y EN OMICRON
                    $this->reinscripciones->actualizarInscripcion($aInscripcion[0]['pk_inscripcion'],$aPago, 1621, $aUcadicio);
                 }else if(empty($aPago) && empty($aInscripcion[0]['numeropago'])){//Si no tiene pago en PROFIT y OMICRON
                   echo error;
                }

              }


            }


        //Donde se trae las materias que va a incribir un estudiante
            $data =  $this->data = $this->reinscripciones->MateriasInscripcion($ci,$aEscuela[0]['pk_atributo'],$aPeriodo[0]['pk_periodo']);
        //--------------------------------------------------------------------------
         //Contruccion de tabla de materias
           $HTML .= "<table align=center id = materia><tr><th class= \"thcod\" > Codigo Materia </th><th class= \"thmat\"> Materia</th><th class= \"thuc\"> U.C </th><th class= \"thsem\"> Semestre </th><th class= \"thtur\"> Turno </th><th class= \"thsec\"> Seccion </th><th class= \"thhor\"> Horario </th><th class= \"threq\"> Requisitos </th><th class= \"thcal\"> Calificacion </th><th class= \"thins\"> Inscritos </th></tr>";

          $new_turno      = array();
          $c              = 0;
           $prelacion_pasantia = array(11772,11939,12575,12027,12650,14036,12254,11930,12418,11771,12953,12956,14102,12955,11841,12419,12096,12957,12016,12176,12487,12568,12017,13145,12169,12569,11763,12249,13792,12095,12090,12328,12089,13291,11848,11938,12177,12026,12329,12168,12506,12410,14203,14204,12954,12958,12649,11840,12657,12574,11764,12658,11931,11849,14197,12255,12323,14198,12411,13866);
          $test = $this->asignaturas->validacionPasantia($ci,'13124,13275,13845,14087,13769,14012,13072,13070,13074,13073,13071,13069,13071,13074,13069,13070,13073');
          $UCA            = $this->RecordAcademico->getUCA($ci,$aEscuela[0]['pk_atributo']);
          $array_cod      = array(); //Se llena con los requisitos... sin el orden de las materias no sirve

          //AQUI SE CREA LA TABLA DE LA INSCRIPCION     
        foreach ($data as $key => $resultado) { 
            $this->mtria = $resultado['pk_asignatura']; 
            $array_cod[] = substr($resultado['codigopropietario'],4,8);     
            $prelacion[] = explode(",", (trim(($resultado['prelacion']),"{}\"")));
            $turnos      = $this->SwapBytes_String->arrayDbToArrayPHP($resultado["turno"]);
            $secciones   = $this->SwapBytes_String->arrayDbToArrayPHP($resultado["seccion"]);
            $semestres   = $this->SwapBytes_String->arrayDbToArrayPHP($resultado["semestre"]);
            $retirada    = $this->reinscripciones->matRetirada($ci,$resultado['codigopropietario'],$aPeriodo[0]['pk_periodo']);
            //es importante buscar si la materia esta retirada para chequear si es posible cursarla de forma simultanea
            $cod         = $resultado['codigopropietario'];
            if (!isset($semestres)/*in_array($resultado['pk_asignatura'], $prelacion_pasantia) && ($test["periodo"] === false || $test["periodo"] === NULL)*/) {
                 $HTML  .= "<tr id = \"{$resultado['codigopropietario']}\" class = \"gris disponible cod\">";
            }elseif(in_array($prelacion[$key][0], $array_cod) || trim(substr($resultado['uc'],0,3)) > $UCA || in_array($prelacion[$key][1], $array_cod)){
                 $HTML  .= "<tr id = \"{$resultado['codigopropietario']}\" class = \"gris disponible cod\">";
            }else{
                if($retirada == false){
                    $HTML .= "<tr id = \"{$resultado['codigopropietario']}\" class=\"disponible cod\" >";
                }else{
                    $rets .=  "'".$retirada."',";
                    $HTML .= "<tr id = \"{$resultado['codigopropietario']}\" class=\"ret disponible cod \">";
                }
            }
            $HTML     .= "<td id = \"{$resultado["pk_asignatura"]}\">".substr($resultado['codigopropietario'],4,8)."</td>";
            $HTML     .= "<td class=\"materia\"  id=\"".$resultado['codigopropietario']."\" >".$resultado['materia']."</td>";
            $HTML     .= "<td id=\"uc\" class=\"uc\">".$resultado['unidadcredito']."</td>";

            $disabled  = (count($semestres) > 1 ? "" : "disabled"); /*si tiene solo un semestre el filtro se deshabilita*/
            $HTML     .= "<td class=\"semestres\"><select  {$disabled} class=\"sem\">";
            foreach ($semestres as $key => $value) {
                $HTML .= "<option value=\"{$key}\">".$value."</option>";
            }
            $disabled  = (count($turnos) > 1 ? "" : "disabled");/*si tiene solo un turno el filtro se deshabilita*/
            $HTML     .= "<td class=\"turnos\" ><select {$disabled} class=\"tur\">";
            foreach ($turnos as $key => $value) {
                $HTML .= "<option value=\"{$key}\">".$value."</option>";
            }

            $HTML     .= "</select></td>";
            $disabled  = (count($secciones) == 1 ? "disabled" : "enabled");/*si tiene solo una seccion el filtro se deshabilita*/
            $HTML     .="<td class=\"secciones\"><select {$disabled}  class= \" {$disabled} {$resultado['codigopropietario']}\" >";
            foreach ($secciones as $key => $value) {
                $HTML .= "<option value=\"{$key}\">".$value."</option>";
            }
            $HTML     .= "</select></td>";
            if (!isset($semestres)) {
                $HTML     .= "<td id = \"horario\" class=\"hor\">NO OFRECIDA</td>";
            }else{
                $HTML     .= "<td id = \"horario{$resultado["pk_asignatura"]}\" class=\"hor\"></td>";
            }


            if (trim(($data[$c]['prelacion']),"{}\"") != "0000") {
             $HTML .= "<td id = \"prelacion{$resultado["pk_asignatura"]}\" class=\"prel\">".trim(($resultado['prelacion']),"{}\"")." ".$resultado['uc']."</td>";
             if(trim(substr($resultado['uc'],0,3),"")!= ''){
                if(trim(($resultado['prelacion']),"{}\"") == substr($data[$c]['codigopropietario'],4,8) || trim(substr($resultado['uc'],0,3)) > $UCA ) { //Antes de aca llenar el arreglo ... etc
                   
                       $json[] = "$('.disponible').find('tr').css('background-color','blue').removeClass('materiaSelect');";

                    } 
                   }
            }else{
                $HTML .= "<td id = \"prelacion{$resultado["pk_asignatura"]}\">".$resultado['uc']."</td>";
            }
            $HTML .= "<td id = \"calificacion{$resultado["pk_asignatura"]}\" class=\"cal\">".$resultado['calificacion']."</td>";
            $HTML .= "<td id = \"inscritos{$resultado["pk_asignatura"]}\" class=\"ins\"></td>";
            $HTML .= "</tr>"; 
  
            $turnos="";
            $c++;

        }
            $HTML .= "</table>";
            $rets  = substr($rets,0,strlen($rets)-1);
            $this->aptas = $this->reinscripciones->aptitudsimultaneos($aPensumEst[0]['fk_pensum'],$rets);
            /*aptitud de simultaneos revisa si hay alguna materia candidata a ser cursada en paralelo segun las materias retiradas que poseas*/
            //Enviamos la tabla a la vista por json
            
            $json[] .= $this->SwapBytes_Jquery->setHtml('tblMaterias', $HTML);
            //Clases para los click de la vista
            ////////////////////////////////////AQUII/////////////////////////////// 
             //asi se agrega el valor proveniente de la funcion con las secciones
            foreach ($this->aptas as $key => $value) {
                $id     = $this->aptas[$key]['codigopropietario'];/*se les agrega a las materias que prelan una materia que retiraste la clase apta de modo tal que se puedan seleccionar y tomar en cuenta por los funciones generadas en postAccepted()*/
                $valor  = $this->aptas[$key]['apta'];
                $json[] = "$('#{$id}').addClass('apta').attr('disabled', true);";
                //las materias aptas a simultaneos poseen una clase que las identifica
            }     
            /*
                en primer lugar, se evita que se aplique la seleccion de materias desde el click en los selectores de secciones o de turnos habiles
                el listener agrega la clase .materiaSelect a las clases disponibles y se lo quita a las que ya lo tienen. para evitar errores visuales, se genera un segundo listener, cada vez que se genera un cambio en el filtro de seleccion de turnos se refresca la seccion de horarios, estudiantes inscritos y se limpian los filtros de secciones al igual que cuando se remueve la clase .materiaSelect

                se monta un tercer listener una vez que se haga click en #modalhorario entonces se llamara a la vista la funcion modalhorario la que generara las solicitudes necesarias para generar el horario en el modal 
            */
            $json[] .= "$('.obs,.obstext').css('display', 'block');
                        $('td.secciones select').attr('disabled', true);
                        $('tr.disponible,tr.apta').click(function(event){
                            if($(this).hasClass('disponible')){
                                if ($(this).hasClass('materiaSelect') == false){
                                    $(this).addClass('materiaSelect');
                                    $('.tur').on('change', function(){
                                        $(this).closest('tr').removeClass('coincidente');
                                        $(this).closest('tr').find('.enabled').empty();
                                        $(this).closest('tr').find('.hor').html('');
                                        $(this).closest('tr').find('.ins').html(''); 
                                    });
                                }else{  
                                    var target = $( event.target );
                                    if ( target.is( 'select, option' )== false ) {                        
                                        $(this).removeClass('materiaSelect'); 
                                        $(this).removeClass('coincidente');
                                        $(this).find('.hor').html('');
                                        $(this).find('.ins').html('');                       
                                    }
                                }
                            }           
                        });";
            $json[] = "postAccepted();
                        $('#modalHorario').on('click',function(){
                            modalhorario();
                        });"; 

        
            $this->getResponse()->setBody(Zend_Json::encode($json));    

          }
            // funcion que trae las secciones
/*-----*/public function seccionesAction(){
            $this->SwapBytes_Ajax->setHeader();
            $this->cedula = $this->_getParam('cedula',0);
            $this->per    = $this->periodos->getMasNuevo();
            $this->usedePeriodo  = $usedePeriodo = $this->reinscripciones->ultimoperiodo();
            $this->uSede  = $uSede = $this->reinscripciones->sedeUltimo($this->cedula);
            if($this->usedePeriodo == NULL){
                $this->usedePeriodo  = $usedePeriodo = $this->reinscripciones->sedePrimerPeriodo($this->cedula);
            }
            $this->pens   = $this->reinscripciones->getPensumNew($this->cedula, $usedePeriodo[0]['pk_periodo']);
            $this->usedePeriodo  = $this->pens[0]['pk_estructura'];
            $this->cod    = $this->_getParam('cod1');
            $sem          = $this->_getParam('sem1');
            $tur          = $this->_getParam('tur1');

            if(isset($this->pens[0]['fk_pensum']) and isset($this->per) and isset($this->usedePeriodo) and isset($sem) and isset($tur) and isset($this->cod)) { 
                //$this->secc = $this->reinscripciones->getSecciones($this->pens[0]['fk_pensum'],$this->per,$this->usede,$sem,$tur,$this->cod);
                $this->secc = $this->reinscripciones->getSecciones($this->pens[0]['fk_pensum'],$this->per,$this->uSede[0]["fk_estructura"],$sem,$tur,$this->cod);
                $x          = sizeof($this->secc)+1;// para contar la cuantas secciones hay y poder agregarle una para la validacion del json
                foreach ($this->secc as $key => $value) {
                    /* busca las secciones de la materia donde indique el touch y si tiene mas de una seccion entonces se llena el filtro de las secciones
                        la primera condicion esta dada para que se agregue la seccion vacia, si no se agregara podria generar errores en los filtros ya que enviarian un valor nulo
                        la segunda condicion, revisa si el row tiene .materiaSelect y el filtro de secciones de dicha materia tiene la clase enabled
                        si es verdadero entonces se habilita la seleccion de secciones, adentro de dicha condicion, si el numeo de secciones es menor que todas las secciones posibles para el turno mas la seccion nula entonces se agregara una nueva opcion con su respectivo atributo

                        si la segunda condicion es falsa, entonces si la seleccion de secciones esta habilitada y la materia se le remueve el  .materiaSelect se deshabilita la seleccion de secciones y se vacia el filtro
                     */
                   $json[] = " if({$key}==0 && $('.{$this->cod} option').length < {$x}){
                                    $('.{$this->cod}').append(new Option('--', '--'));
                                }
                                
                                if ($('#{$this->cod}').hasClass('materiaSelect') && $('.{$this->cod}').hasClass('enabled')){
                                    $('.{$this->cod}').attr('disabled', false); 
                                    if($('.{$this->cod} option').length < {$x}){
                                        $('.{$this->cod}').append(new Option('{$value['atributo']}', '{$value['valor']}'));
                                    }
                                }else{
                                    if ($('.{$this->cod}').hasClass('enabled') && $('#{$this->cod}').hasClass('prelada') == false){
                                        $('.{$this->cod}').attr('disabled', true); 
                                        $('.{$this->cod}').empty();
                                }
                              }";
                }   

                echo Zend_Json::encode($json);        
            }
        }
         
/*-----*/public function horariosinscritosAction(){
            $this->SwapBytes_Ajax->setHeader();
            //Data
            $ci              = $this->_getParam('cedula',0);         
            $aEscuela        = $this->reinscripciones->escuelaEstudiante($ci);
            $aPeriodo        = $this->reinscripciones->ultimoperiodo();
            $aSede           = $this->reinscripciones->sedeUltimo($ci);
            $this->usede  = $usede = $this->reinscripciones->sedeUltimoPeriodo($ci);
            if($this->usede == NULL){
                $this->usede  = $usede = $this->reinscripciones->sedePrimerPeriodo($ci);
            }
            $this->pens   = $this->reinscripciones->getPensumNew($ci, $usede[0]['fk_periodo']);
            //Request Ajax
            $asignatura      = $this->reinscripciones->getpk($id);
            $codigo          = $this->_getParam('codigo',0);
            $semestre        = $this->_getParam('semestre',0);
            $turno           = $this->_getParam('turno',0);
            $seccion         = $this->_getParam('seccion',0);
            $touch           = $this->_getParam('touch',0);
            

            $codigo_c        = NULL;
            $id              = $touch['codigo'];
            $contador        = count($codigo); //para el contador de materias seleccionadas, suponiendo que $codigo sea nulo, es decir que no hayan materias seleccionadas entonces el contador sera sero y sera enviado a la vista 
            if($codigo == null){
                $contador=0;
                $json[] = " $('#cantidadm').html('{$contador}');";
            }
            if($touch['seccion']!='--'){//para evitar que los hayan errores en las consultas
                //Coincidencia
                $coincidencia    = array();
                //se toman los datos de la materia seleccionada 
                if($codigo != null){
                    $horTouch =  $this->horario->horarioReinscripcion($aSede[0]['fk_estructura'],$aSede[0]['fk_periodo'],$touch['codigo'],$touch['seccion'],$touch['turno'],$touch['semestre']); 
                    $count    = count($codigo);
                    //se introducen los datos individuales de las materias seleccionadas en un array asociativo para luego poder compararlas
                    for ($j = 0; $j < $count; $j++) {
                        $horarios = $this->horario->horarioReinscripcion($aSede[0]['fk_estructura'],$aSede[0]['fk_periodo'],$codigo[$j],$seccion[$j],$turno[$j],$semestre[$j]);
                        foreach ($horarios as $key => $value) {
                            $tmp  =  array(
                                        "asignatura" => $value['fk_asignatura'],
                                        "dia"        => $value['fk_dia'],
                                        "hora"       => $value['horario'],
                                        "codigo"     => $codigo[$j],
                                    );
                            array_push($coincidencia,$tmp);              
                        }
                    } 
                }
                // var_dump($Coincidencia['asignatura']);
                //Asignatura.
                $asignatura = $this->reinscripciones->getAsignatura($touch['semestre'],$touch['turno'],$touch['seccion'],$touch['codigo'],$aPeriodo[0]['pk_periodo']);
                //Generar Horario
                if($codigo != null){
                    $horarios = $this->horario->horarioReinscripcion($aSede[0]['fk_estructura'],$aSede[0]['fk_periodo'],$touch['codigo'],$touch['seccion'],$touch['turno'],$touch['semestre']);
                }
                foreach ($horarios as $key => $value) {
                    $hora_definitiva .= '<p style="text-align: justified;">'.$value['dia']." ".substr($value['inicio'],0,5)." / ".substr($value['horafin'],0,5).'</p>';  
                }   
                //AQUI BUSCAMOS LOS INSCRITOS POR MATERIA
            
                
                
                //Insertar Horario , Inscritos ,unidades de credito
                //se revisa el codigo, el horario y el dia de la materia recien seleccionada con las materias ya seleccionadas previamente, si coincide alguna de las horas y asi mismo el dia en materias diferentes, coincide

                for ($j = 0; $j < $count; $j++) { /*se introducen los datos individuales de las materias seleccionadas en un array asociativo para luego poder compararlas*/
                        $horarios = $this->horario->horarioReinscripcion($aSede[0]['fk_estructura'],$aSede[0]['fk_periodo'],$codigo[$j],$seccion[$j],
                                                                         $turno[$j],$semestre[$j]);
                        $inscritomateria     = $this->reinscripciones->CantidadDeInscritosPorMateria($aPeriodo[0]['pk_periodo'],$aSede[0]['fk_estructura'],$codigo[$j],$semestre[$j],$seccion[$j], $turno[$j],$ci);
                        $cuposmaximosmateria = $this->reinscripciones->getCuposMax($aPeriodo[0]['pk_periodo'],$aSede[0]['fk_estructura'],$codigo[$j],$semestre[$j],$seccion[$j],$turno[$j]);
                        $this->cuposmaximosmateria = $cuposmaximosmateria[0]['max'];
                        $this->inscritomateria     = $inscritomateria[0]['inscritos'];
                        if($this->inscritomateria==NULL){
                            $this->inscritomateria = 0;
                        }
                        if($this->cuposmaximosmateria == NULL){
                            $this->cuposmaximosmateria = 0;
                        }
                        $hora_definitiva = NULL;
                        foreach ($horarios as $key => $value) {
                            $hora_definitiva .= '<p style="text-align: justified;">'.$value['dia']." ".substr($value['inicio'],0,5)." / ".substr($value['horafin'],0,5).'</p>';  
                        }   
                            $json[] = "if  ($('#{$codigo[$j]}').hasClass('materiaSelect')== true) {
                                    $('#{$codigo[$j]}').find('td.hor').html('{$hora_definitiva}');
                                    $('#{$codigo[$j]}').find('td.ins').html('{$this->inscritomateria} de {$this->cuposmaximosmateria}');
                                    if ({$this->inscritomateria} >= {$this->cuposmaximosmateria}) {
                                        $('#{$codigo[$j]}').find('td.ins').addClass('cuposmax');                                        
                                    }else{
                                        $('#{$codigo[$j]}').find('td.ins').removeClass('cuposmax');
                                    }
                                    $('#cantidadm').html('{$contador}');
                                    $('#{$codigo[$j]}').removeClass('coincidente'); 

                                }else{
                                    $('#{$codigo[$j]}').find('td.ins').html('');
                                    $('#{$codigo[$j]}').find('td.hor').html('');                       
                                    $('#cantidadm').html('{$contador}'); 
                                }";
                        
                    } 
                }else{
                    $json[]= "$('#{$id}').find('td.hor').html('');";
                    $json[]= "$('#{$id}').find('td.ins').html('');";
                }

                $ct = count($coincidencia);
                $toncho_coincidencia = array();
                for ($i = 0; $i < $ct; $i++) { 
                    for ($j = $i; $j < $ct ; $j++) { 
                        if(
                            $i != $j && // Diferent Element
                            $coincidencia[$i]["codigo"] != $coincidencia[$j]["codigo"] && //Diferent Signature
                            $coincidencia[$i]["dia"] == $coincidencia[$j]["dia"] && // Same Day
                            $coincidencia[$i]["hora"] == $coincidencia[$j]["hora"] // Same Hour
                        ){
                            $toncho_coincidencia[] = array((object) $coincidencia[$i], (object) $coincidencia[$j]);
                        }
                    }
                }

               if ($this->inscritomateria >= $this->cuposmaximosmateria) {
                    $json[] = "$('#btnInscribir').removeClass('0').addClass('1');
                                if($('#btnInscribir').hasClass('1')== true) {                                 
                                    $('#btnInscribir').addClass('btnUnvisible').removeClass('btnVisible');
                                }"; 
                }


                // Coincidence Color
                if(isset($toncho_coincidencia) && !is_null($toncho_coincidencia)){
                    foreach ($toncho_coincidencia as $key => $value) {
                        $first  = (object) $value[0];
                        $second = (object) $value[1];
                        $cod1   = $first->codigo;
                        $cod1_c = substr($first->codigo, 4, 8);
                        $cod2   = $second->codigo;
                        $cod2_c = substr($second->codigo, 4, 8);
                        $json[]   ="if ($('#{$cod1}').hasClass('materiaSelect') == true){
                                        $('#{$cod1}').find('td.hor').html('Coincide con: {$cod2_c}');
                                        $('#{$cod1}').addClass('coincidente');
                                        $('#cantidadm').html('{$contador}'); 

                                     }else{
                                        $('#cantidadm').html('{$contador}');
                                        $('#horario{$cod1}').html('');
                                        $('#inscritos{$cod1}').html('');      
                                     }
                                     if ($('#{$cod2}').hasClass('materiaSelect') == true){
                                        $('#{$cod2}').find('td.hor').html('Coincide con: {$cod1_c}');
                                        $('#{$cod2}').addClass('coincidente');
                                        $('#cantidadm').html('{$contador}'); 

                                     }else{
                                        $('#cantidadm').html('{$contador}');
                                        $('#horario{$cod2}').html('');
                                        $('#inscritos{$cod2}').html('');      
                                     }";
                    }
                }else{
            $json[]= "$('#{$id}').find('td.hor').html('');";
            $json[]= "$('#{$id}').find('td.ins').html('');";
         }

            echo Zend_Json::encode($json);
        
        } 
        public function simultaneosAction(){
            $this->SwapBytes_Ajax->setHeader();

            $ci           = $this->_getParam('cedula',0);
            $codigo         = $this->_getParam('codigo',0);
            $this->usede  = $usede = $this->reinscripciones->sedeUltimoPeriodo($ci);
            $this->pens   = $this->reinscripciones->getPensumNew($ci, $usede[0]['fk_periodo']);
            $lineamat     = array();
                foreach ($codigo as $key => $value) {
                    $noapr = $value;

                    while(true){
                        $this->linea = $this->reinscripciones->anterioresSimultaneos($this->pens[0]['fk_pensum'], $noapr);
                        if($this->linea[0]['codigopropietario'] == NULL or $this->linea[0]['codigopropietario'] == false){
                            break;
                        }else{
                        array_push($lineamat, $this->linea[0]['codigopropietario']);
                        $noapr = $this->linea[0]['codigopropietario'];
                        }
                    }
                    $noapr = $touch['codigo'];
                    while(true){
                        $this->linea = $this->reinscripciones->Simultaneos($ci,$this->pens[0]['fk_pensum'], "'".$noapr."'");
                        if($this->linea[0]['codigopropietario'] == NULL or $this->linea[0]['codigopropietario'] == false){
                            break;
                        }else{
                        array_push($lineamat, $this->linea[0]['codigopropietario']);
                        $noapr = $this->linea[0]['codigopropietario'];
                        }
                    }
                }

                    foreach ($lineamat as $key => $value) {
                        foreach ($codigo as $key => $value2) {
                            if($value == $value2){
                                $json[] = "$(clavetxt).addClass('3');
                                           $(clavetxt).val('');
                                           $('#clavetxt').attr('disabled', false); 
                                           $('#verclave').attr('disabled', false); ";
                                           break;
                        }
                    }

                }
            echo Zend_Json::encode($json);

        }



/*---*/public function unidadescreditoestudianteAction(){
            $this->SwapBytes_Ajax->setHeader();

            $ci             = $this->_getParam('cedula',0);         
            $aEscuela       = $this->reinscripciones->escuelaEstudiante($ci);
            $aPeriodo       = $this->reinscripciones->ultimoperiodo();
            $aSede          = $this->reinscripciones->sedeUltimo($ci);
             $aUltimoCursado = $this->reinscripciones->sedeUltimoPeriodo($ci);
            if($aUltimoCursado == NULL){
                $aUltimoCursado = $this->reinscripciones->sedePrimerPeriodo($ci);
            }
            //FIX lag con Profit
            /*
            $aUcadicio      = $this->profit->getUc($ci,$aPeriodo[0]['pk_periodo'], $this->date);
            if(!isset($aUcadicio)){
                $aUcadicio = $this->reinscripciones->uca($ci,$aPeriodo[0]['pk_periodo'],$aEscuela[0]['pk_atributo'])[0]['ucadicionales'];
                if(!isset($aUcadicio)){
                    $aUcadicio = 0;
                }
            }
            */
            $aUcadicio = $this->reinscripciones->uca($ci,$aPeriodo[0]['pk_periodo'],$aEscuela[0]['pk_atributo'])[0]['ucadicionales'];
            if(!isset($aUcadicio)){
                $aUcadicio = 0;
            }
            $semestre       = $this->_getParam('semestre',0);
            $creditos       = $this->_getParam('creditos',0);
            $touch          = $this->_getParam('touch',0);
            $codigo         = $this->_getParam('codigo',0);
            $cantidadm      = count($this->_getParam('cantidadm',0));
            $aPensumEst     = $this->reinscripciones->getPensumNew($ci, $aPeriodo[0]['pk_periodo']);
            $adic           = $aUcadicio;
            foreach ($codigo as $key => $value) {
                $materias .= "'".$value."',";
            }
            $materias = substr($materias,0,strlen($materias)-1);
            /*en base a la cantidad de materias seleccionadas se calcula las unidades de credito del semestre de ubicacion el cual servira de base para poder utilizar un numero determinado de unidades de credito*/
            $ubic    = $this->reinscripciones->SemUb($materias)[0]['valor'];
            if(isset($ci) and isset($aEscuela[0]['pk_atributo']) and isset($semestre[0]) and isset($aSede[0]['fk_estructura']) and isset($aSede[0]['fk_periodo']) and isset($aPensumEst[0]['codigopensum'])){
                $UCU = $this->UCU = $this->reinscripciones->unidadcreditoporsemestre($ci,$aEscuela[0]['pk_atributo'],$ubic,$aSede[0]['fk_estructura'],$aSede[0]['fk_periodo'], $aPensumEst[0]['codigopensum']);
                $ucu = $UCU[0]['uc'];
            }

            $credito = 0;
            foreach ($creditos as $key => $value) {
                $credito = $credito + $value;  
            }
            
            if($cantidadm > 0 and isset($ucu)){
                $json[] = "$('#UCU').html('$ucu');";
                if($adic==0){
                    $json[] = "$('#UCI').html('$credito de $ucu');";
                }else{
                    $json[] = "$('#UCI').html('$credito de $ucu + $adic');";
                }
            }
            else{
                $json[] = "$('#UCU').html('0')";
                $json[] = "$('#UCI').html('0 de 0')";
            }

            // Fix Mostrar Boton

            if($credito > $ucu+ $aUcadicio){ //si hay mas unidades de credito de las que posee entonces se deshabilita el boton de finalizar
                $json[] = "$('#msg').html('Has excedido tus créditos').show();";
                $json[] = "$('#btnInscribir').removeClass('0').addClass('1');";
            }
            else if($credito == 0 and $codigo==NULL){//si no tiene unidades de credito registradas entonces se deshabilita el boton de finalizar
                $json[] = "$('#btnInscribir').removeClass('0').addClass('1');";
            }
            else if ($credito >=0 and $credito<=$ucu+ $aUcadicio and $codigo!=NULL){// si se encuentra entre un rango mayor que 0 y menor que las unidades de credito que posee enmtonces se esconde el mensaje de alerta y se visualiza el boton de finalizar
                $json[] = "$('#msg').hide();";
                $json[] = "$('#btnInscribir').removeClass('1').addClass('0');";
            }

            echo Zend_Json::encode($json);        

        }

        public function coincidenciaAction(){
            $this->SwapBytes_Ajax->setHeader();
            //Data
            $cv              = $this->_getParam('clave',0); //clave de omision de coincidencias
            $ci              = $this->_getParam('cedula',0);           
            $aEscuela        = $this->reinscripciones->escuelaEstudiante($ci);
            $aPeriodo        = $this->reinscripciones->ultimoperiodo();
            $aSede           = $this->reinscripciones->sedeUltimo($ci);
            
            //Request Ajax
            $asignatura      = $this->reinscripciones->getpk($id);
            $codigo          = $this->_getParam('codigo',0);
            $semestre        = $this->_getParam('semestre',0);
            $turno           = $this->_getParam('turno',0);
            $seccion         = $this->_getParam('seccion',0);
            $codigo_c        = NULL;
            $coincidencia    = array();
            $lineamat        = array();
            $test = NULL;
            $touch           = $this->_getParam('touch',0); 
            if($codigo != null){
                $count  = count($codigo);
                /*se busca toda la linea de prelacion, siempre buscando la inmediata prelacion hasta que el query retorne nulo, posteriormente se comprueba si en los codigos seleccionados que se pasan por parametro desde la vista estan seleccionadas alguna de las materias de la linea, si consigue al menos una, entonces se agrega la clase 3 para indicar que se debe mostrar la seccion de la clave y ocultar el boton de finalizar*/
                
                    




                for ($j = 0; $j < $count; $j++) { 
                    $horarios = $this->horario->horarioReinscripcion($aSede[0]['fk_estructura'],$aSede[0]['fk_periodo'],$codigo[$j],$seccion[$j],
                                                                     $turno[$j],$semestre[$j]);
                    foreach ($horarios as $key => $value) {
                        $tmp =  array(
                                    "asignatura" => $value['fk_asignatura'],
                                    "dia"        => $value['fk_dia'],
                                    "hora"       => $value['horario'],
                                    "codigo"     => $codigo[$j]
                                );
                        array_push($coincidencia,$tmp);              
                    }
                } 
            }
            //si se consigue alguna coincidencia de horario se deshabilita el boton de finalizar, en caso contrario, se habilita suponiendo que este entre un rango de unidades de credito mayor que cero y menor o igual que las unidades de credito que posee
            foreach ($coincidencia as $i => $value) {
                foreach ($coincidencia as $j => $value) {
                    if (   $coincidencia[$i]['asignatura']!=$coincidencia[$j]['asignatura']&&$coincidencia[$i]['dia']==$coincidencia[$j]['dia']&&$coincidencia[$i]['hora']==$coincidencia[$j]['hora']){
                            $json[]   = "$('#btnInscribir').addClass('btnUnvisible');
                                         $(clavetxt).val('');
                                         $('#clavetxt').attr('disabled', false);
                                         $('#verclave').attr('disabled', false); "; 
                            $test = 1;
                            break 2;
                            //si se consigue una coincidencia entonces se deshabilita el boton de finalizar, sino, se realizar una compobracion para revisar si cumple con su rango de unidades de credito
                    }else{
                        $test = 0;
                    }
                }
            }
                if($test ==0){
                    $json[]   = "if($('#btnInscribir').hasClass('0')== true) {
                                    $('#btnInscribir').addClass('btnVisible').removeClass('btnUnvisible').attr('disabled', false);
                                    if(!$(clavetxt).hasClass('3')){
                                        $(clavetxt).val('');
                                        $(clavetxt).attr('disabled', true);
                                        $('#verclave').attr('disabled', true);
                                        $(clavetxt).removeClass('3');
                                    }else{
                                        $(clavetxt).val('');
                                        $('#clavetxt').attr('disabled', false);
                                        $('#verclave').attr('disabled', false);
                                        $('#btnInscribir').addClass('btnUnvisible').removeClass('btnVisible');
                                        $(clavetxt).removeClass('3');
                                    }
                                        
                                 }if($('#btnInscribir').hasClass('1')== true) {
                                    $('#btnInscribir').addClass('btnUnvisible').removeClass('btnVisible');
                                 }"; 
                }
            //}
// se revisa si las materias tienen la clase de cupos agotados, en caso de tener se deshabilita el boton de finalizar, si no, no hace nada.
           $json[] = "if ($('.materiaSelect').find('td.ins').hasClass('cuposmax')) {
                $('#btnInscribir').removeClass('0').addClass('1');
                            if($('#btnInscribir').hasClass('1')== true) {                                 
                                $('#btnInscribir').addClass('btnUnvisible').removeClass('btnVisible');
                            }
            }";
            echo Zend_Json::encode($json);

        }

/*----*/public function semestreubicacionAction(){
            // Semestre de ubicacion del estudiante segundo las materias seleccionadas /*********arreglar */////////
            $this->SwapBytes_Ajax->setHeader();
             
            $codigos = $this->_getParam('codigo');
            /*se utilizan todos los codigos de las materias, se envian en un array y se buscan las unidades de credito de cada semestre
              se suman y se toma en donde haya mayor unidad de credito, en caso de que dos semestres tengan iguales unidades de credito entonces se tomara el mayor*/
            foreach ($codigos as $key => $value) {
                $materias .= "'".$value."',";
            }
            $materias = substr($materias,0,strlen($materias)-1);
            $ubic    = $this->reinscripciones->SemUb($materias)[0]['numsem'];
            /*si existen materias seleccionadas ($materias == true) entonces se introduce el resultado del semestre de ubicacion en el cuadro de #ubicacion en caso contrario se pontra en 0*/
            if($materias == true){
                $json[]= "$('#ubicacion').html('{$ubic}°')";
            }else{
                $json[]= "$('#ubicacion').html('0°')";
            }

             echo Zend_Json::encode($json);

        }

/*----*/public function gethorasAction(){
            $this->SwapBytes_Ajax->setHeader();
           
            $codigo          = $this->_getParam('codigo',0);
            $semestre        = $this->_getParam('semestre',0);
            $turno           = $this->_getParam('turno',0);
            $seccion         = $this->_getParam('seccion',0);
            $ci              = $this->_getParam('cedula',0);          
            $aEscuela        = $this->reinscripciones->escuelaEstudiante($ci);
            $aSede           = $this->reinscripciones->sedeUltimo($ci);
            $aPeriodo        = $this->reinscripciones->ultimoperiodo();
            $coincidencia    = array();

            /*$json[] = "$('.lunes,.martes,.miercoles,.jueves,.viernes,.sabado').hide();
                        $('td.lunes,td.martes,td.miercoles,td.jueves,td.viernes,td.sabado').html('').removeClass('shadowhora,modalcon');";*/
            /*se utilizan los codigos, secciones y turnos, si la seccion es -- generaria un error en el query por ende se saltaria a la siguiente materia*/
            foreach ($codigo as $i => $value) {
                if($seccion[$i]!='--'){
                    $horarios = $this->horario->horarioReinscripcion($aSede[0]['fk_estructura'],$aSede[0]['fk_periodo'],$codigo[$i],$seccion[$i],
                    $turno[$i],$semestre[$i]);
                    foreach ($horarios as $key => $value) {
                        $dia    = '.'.$horarios[$key]['dia'];
                        $hora   =  '#'.$horarios[$key]['horario'];
                        /*si el dia ya esta visible entonces no hace nada, en caso contrario se habilita el dia*/
                        $json[] = "if($('{$dia}').is('visible')==false){ $('{$dia}').show(); }";
                        $aul    = $horarios[$key]['aula'];
                        $edf    = $horarios[$key]['edif'];
                        $mat    = $horarios[$key]['materia'];
                        $nota   = $horarios[$key]['nota'];
                        $pro    = $horarios[$key]['primer_nombre'].' '.$horarios[$key]['primer_apellido'];
                        $json[] = "$('{$hora}').find('{$dia}').html('<div><p>{$aul} - {$edf} {$mat} {$nota}</p> <p>Prof: {$pro}</p></div>');
                                $('{$hora}').find('{$dia}').addClass('shadowhora').removeClass('modalcon');";
                        $tmp =  array(
                                    "materia" => $value['materia'],
                                    "dia"        => $value['dia'],
                                    "horario"       => $value['horario'],
                                );
                        array_push($coincidencia,$tmp);        
                    }

                }
            }
            //para revisar si hay coincidencia de horario debe realizarse el mismo proceso que en la seccion de horarios 
            // por ende, se introduce una variable temporal($temp) en un array asociativo y se comparan los valores respectivos de materia dia y hora
            //si coinciden dia y hora siendo una materia diferente entonces se sobreescribira el horario y se introducira una coincidencia
            foreach ($coincidencia as $i => $value1) {
                foreach ($coincidencia as $j => $value2) {
                    if($value1['materia'] != $value2['materia']){
                        if (($value1['dia']==$value2['dia']&&$value1['horario']==$value2['horario'])){
                                $d       = '.'.$coincidencia[$i]['dia'];
                                $h       = '#'.$coincidencia[$i]['horario'];
                                $mat1    = $coincidencia[$i]['materia'];
                                $mat2    = $coincidencia[$j]['materia'];
                                $json[]  = "$('{$h}').find('{$d}').html('COINCIDENCIA DE {$mat1} CON {$mat2}').addClass('modalcon');"; 
                        }
                    }
                }
            }

            $json[] = "$('#horas td').hover(function() {
                           $('#horas th').eq($(this).index()).css('background-color', 'rgb(236,244,164)');
                        }, function() {
                           $('#horas th').eq($(this).index()).css('background-color', 'rgb(171,188,14)');
                       });";


            $this->getResponse()->setBody(Zend_Json::encode($json));    
               
        }  

/*----*/public function inscribirmateriasAction(){
            //Funcion de inscripcion agregar materias de estudiante al periodo correspondiente
            $this->SwapBytes_Ajax->setHeader();

            /***** Datos para la inscripcion****/
            $ci              = $this->_getParam('cedula',0);
            $ubic            = $this->_getParam('ubicacion',0);
            $aSede           = $this->reinscripciones->sedeUltimo($ci);
            $aPeriodo        = $this->reinscripciones->ultimoperiodo();
            $aEscuela        = $this->reinscripciones->escuelaEstudiante($ci);
            $aPensumEst      = $this->reinscripciones->getPensumNew($ci, $aPeriodo[0]['pk_periodo']);
            $pkusuariogrupo  = $this->reinscripciones->pkusuariogrupo($ci);
            $fk_inscripcion  = $this->reinscripciones->buscarInscripcion($ci,$aPeriodo[0]['pk_periodo']);
            $uPeriodo        = $this->reinscripciones->sedeUltimoPeriodoCursado($ci,$aEscuela[0]["pk_atributo"]);
            //var_dump($uPeriodo);die;            
            /****** Variables para mandar a la funcion insertarInscripcion******/
            $pk_usuariogrupo = $pkusuariogrupo[0]['pk_usuariogrupo'];
            $numeropago      = $fk_inscripcion[0]['numeropago'];
            $fk_periodo      = $aPeriodo[0]['pk_periodo'];
            $fk_atributo     = $aEscuela[0]['pk_atributo'];
            $sede            = $aSede[0]['fk_estructura'];
            $uca             = $aPensumEst[0]['ucadicionales'];
            $fk_tipo         = 1621;
            $fk_pensum       = $aPensumEst[0]['fk_pensum'];
            $fechahora       = date("Y-m-d h:i:s");
            $test            = 0;
            $matasigSCI      = $this->reinscripciones->matasigSCI($fk_periodo,$fk_pensum,$sede);


            //var_dump($matasigSCI);die;
            if(!isset($uPeriodo) && !empty($uPeriodo)){
                $VerificarSCI = $this->reinscripciones->verificarSCIPasadaEnUltimoPeriodo($ci,$sede,$uPeriodo[0]["periodo"]);
            //var_dump($VerificarSCI,empty($VerificarSCI));die;
            }
            if($fk_inscripcion == false or $fk_inscripcion == ''){
                $inscripcion = $this->reinscripciones->insertarInscripcion($pk_usuariogrupo,$numeropago,$fk_periodo,$fk_atributo,$sede,$uca,$fk_tipo,'false',$fk_pensum);
                $fk_inscripcion  = $this->reinscripciones->buscarInscripcion($ci,$aPeriodo[0]['pk_periodo']);
            }
            //fechahora       = $aInscripcion[0]['fechahora'];
            //var_dump($pk_usuariogrupo);die;
                    $pk_asignatura      = $this->_getParam('pk_asignatura',0);
                    $seccion            = $this->_getParam('seccion',0);
                    $turno              = $this->_getParam('turno',0);
                    $semestre           = $this->_getParam('semestre',0);
                    $periodo            = $this->_getParam('periodo',0);
                    $obs                = $this->_getParam('obs',0);

            foreach ($seccion as $key => $value) {
                if($value == '--'){
                    $json[] = "$('#msg').html('Alguna de las materias a inscribir no tiene una sección escogida');
                               $('#msg').show();";
                    $json[] = $this->sweetalert->setBasicAlert("info","Error","Alguna materia no tiene sección seleccionada.");
                    $test = 1;
                    break;
                }
            }

            if($test != 1){
                    /***** Datos para mandar al record ****/
                    $aPeriodo = $this->reinscripciones->ultimoperiodo();
                    $aInscripcion = $this->reinscripciones->buscarInscripcion($ci,$aPeriodo[0]['pk_periodo']);

                   //var_dump($periodo);die;
                    $count_asignatura   = count($pk_asignatura);
                    $count_seccion      = count($seccion);
                    $count_turno        = count($turno);
                    $count_semestre     = count($semestre);
                    $count_periodo      = count($periodo);
            // Same Dimension
            if(($count_asignatura == $count_seccion) and ($count_asignatura == $count_turno) and ($count_asignatura == $count_semestre) and ($count_asignatura == $count_periodo)){
                //Set Data
                    $data  = array();
                    for ($i = 0; $i < $count_asignatura; $i++) { 
                        $data[] = array(
                                        'asignatura' => $pk_asignatura[$i],
                                        'seccion'    => $seccion[$i],
                                        'turno'      => $turno[$i],
                                        'semestre'   => $semestre[$i],
                                        'periodo'    => $periodo[$i],
                                    );

                }
                
            $fk_asignacion = $this->reinscripciones->getAsignacion($data);
            $asignaciones = "";
            foreach ($fk_asignacion as $key => $value) {
                $asignaciones.= $fk_asignacion[$key]['pk_asignacion'].",";
            };
           $asignaciones  = substr($asignaciones,0, (strlen($asignaciones)-1));
           $this->matasig = $this->reinscripciones->getAsignacionMateria($asignaciones);
           $this->semubic = $this->reinscripciones->semestreUbic($fk_periodo,$sede,$ci,$aEscuela[0]['pk_atributo'],$asignaciones,$aPensumEst[0]['codigopensum']);
           if (!empty($VerificarSCI))
           {
               $this->matasig[count($this->matasig)]["fk_asignatura"]=$matasigSCI[0]["fk_asignatura"];
                $this->matasig[count($this->matasig)-1]["pk_asignacion"]=$matasigSCI[0]["pk_asignacion"];
                //var_dump($this->matasig);die;
           }

           $this->reinscripciones->actualizarSemestreUbic($this->semubic[0]['pk_atributo'],$fk_inscripcion[0]["pk_inscripcion"]);
           $this->reinscripciones->insertarRecordAcademico(864,$this->matasig,$fk_inscripcion[0]["pk_inscripcion"],$this->matasig,$ci,$aPeriodo[0]['pk_periodo']);
           $this->reinscripciones->agregarObservacion($obs,$aInscripcion[0]['pk_inscripcion']);
            $json[] = "window.print();";
            $json[] = $this->sweetalert->setBasicAlert("success","Inscripción Realizada","La inscripción se ha realizado correctamente");
            $json[] .= "$('.confirm').click(function(){window.location.replace('$this->redirect')});";

            }
                    
          }          
             echo Zend_Json::encode($json);

        }
    

/*----*/public function unidadesdecreditoporsemestre($ci,$escuela,$semestre,$sede,$periodo,$pensum){

            //Unidades de credito por semestre de ubicacion
           $this->UCU = $this->reinscripciones->unidadcreditoporsemestre($ci,$escuela,$semestre,$sede,$periodo,$pensum);
           
        }

        
/*----*/public function setData($ci,$escuela,$periodo){

            //Datos correspondientes al estudiante que se va reinscribir
            $this->data = $this->reinscripciones->MateriasInscripcion($ci,$escuela,$periodo);


        }

/*----*/public function getHorario($estructura,$periodo,$codigo){
         $this->SwapBytes_Ajax->setHeader();
            // Accion de traer el horario
            $ci = $this->ci->ciId;

            $this->horario = $this->horario->horarioReinscripcion($aSede[0]['fk_estructura'],$aSede[0]['fk_periodo'],$touch['codigo']);

    }
                                 
}
 //validar las materias seleccionadas enviadas desde la vista

             //verificar unidades de creditas check
             //Poner las misma funciones de validacion en otra funcion "validar"
             //Verificar coincidencia de materias almost

    //var materiasSeleccionadas = []; 
    //$('tbody tr .materiaSelect').each(function(){ materiasSeleccionadas.push(this.id);}); console.log(materiasSeleccionadas);

?>




            
