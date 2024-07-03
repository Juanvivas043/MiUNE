<?php

class Reports_InscripciontesisController extends Zend_Controller_Action {
    
    public function init() {
        
        Zend_Loader::loadClass('Models_DbTable_Inscripciontesis');
        Zend_Loader::loadClass('Models_DbTable_Profit');
        Zend_Loader::loadClass('Models_DbTable_Certificadocompetencia');
        $this->tesisgrado           = new Models_DbTable_Inscripciontesis();
        $this->profit               = new Models_DbTable_Profit();
        $this->Certificadocompentencia = new Models_DbTable_Certificadocompetencia();  
        
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax->setView($this->view);
        $this->CmcBytes_Profit = new CmcBytes_Profit();
        
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->logger = Zend_Registry::get('logger');
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
    
    public function indexAction() {
        $this->view->title = "Reportes \ Inscripcion Tesis de Grado II";
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
    }
    
    public function validar($ci){
        $cont = 0;
        $per = "0123456789";
        for ($i = 0; $i<strlen($ci);$i++){
            for($j = 0; $j<strlen($per);$j++){
                if($ci[$i]== $per[$j]){
                    $cont = $cont + 1;

                }
            }
        }
        if($cont == strlen($ci)){
            return true;  
        }
        return false;
    }
    
    public function inscribirAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
           
            $json2 = array();
            $HTML = "";
            $HTML1 = "";
            $cont = 0;
            $ci= $this->_getParam('ci');
            if($this->validar($ci) && $ci != ""){
                $datos = $this->Certificadocompentencia->getUsuarios($ci);
                if($datos[0]['nombre']!= null){
                    $pensum = $this->tesisgrado->getPensum($ci);
                    $cumple = $this->verificarRequisitos($ci, $pensum[0]["nombre"]);
                }
            }else{
                $HTML1 .=   "<td style='color:green'><br><h3 style='font-size: 16px; color:green;'>Los datos de la cédula no coinciden.</h3></td>"; 
                $cont++;
            }
            if($cumple){
                $this->InscribirTesis($ci);
                $HTML .=   "<td style='color:green'><br><h3 style='font-size: 16px; color:green;'>Inscripción realizada con éxito.</h3></td>";
            }else{
                $HTML1 .=   "<td style='color:red'><br><h3 style='font-size: 16px; color:red;'>Los datos de la cédula no coinciden.</h3></td>";
            }
            if($cumple){
                $json2[] = '$("#divSolvencias").hide()';
                $json2[] = '$("#mensaje").html("' . $HTML . '")';
            }else{
                $json2[] = '$("#mensaje").html("' . $HTML1 . '")';
                $cont++;
            }
            if($cont > 0){
                $json2[] = '$("#datosEstudiante").hide()';
                $json2[] = '$("#divSolvencias").hide()';
                $json2[] = '$("#imprimir").hide()';
            }
            $this->getResponse()->setBody(Zend_Json::encode($json2));
        }    
    }
    
    public function descargarAction(){
        $ci= $this->_getParam('ci');
        if($this->validar($ci)){    
        $fecha = date("Y-m-d");
        $materia = "Tesis de grado II";
        $periodo = $this->tesisgrado->getUltimoPeriodoVigente($fecha);
     
        $inscrito = $this->tesisgrado->isMateriaInscrita($ci,$materia,$periodo);
            if ($inscrito){
                    $config = Zend_Registry::get('config');
                    $dbname = $config->database->params->dbname;
                    $dbuser = $config->database->params->username;
                    $dbpass = $config->database->params->password;
                    $dbhost = $config->database->params->host;
                    $report = APPLICATION_PATH . '/modules/transactions/templates/inscripciontesis2/ReporteTesisdeGrado2.jasper';
                    $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.png';
                    $filename    = 'Inscripciontesis';
                    $filetype    = 'pdf';

                    $params = "'cedula=integer:{$ci}|logo=string:{$imagen}|asignatura=integer:{$inscrito[0]['asignatura']}|periodo=integer:{$periodo[0]['pk_periodo']}'";
                    $cmd    = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D pgsql -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                    //echo $cmd;exit;

                    Zend_Layout::getMvcInstance()->disableLayout();
                    Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
                    Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );

                    $outstream = exec($cmd); //exec ejecuta el java indicado por la ruta $cmd
                    echo base64_decode($outstream);
            }else{
               $message = '<div><H3 align="center" class="alert" style="color:red">Usted no esta Inscrito para cursar la asignatura </H3></div>';
               echo($message);
            }
        }  
    }
    
    public function verificarRequisitos($ci,$pensum){
        $pagoArticulo = $this->verificarPagoArticulo($ci,$pensum);
        $solvenciaAcademica = $this->verificarSolvenciaAcademica($ci,$pensum);
        $inscripcionTesis = $this->verificarInscripcionTesis($ci);
        if(!$solvenciaAcademica[0]['resultado'] && $pagoArticulo && $inscripcionTesis[0]["resultado"]){
            $return = true;
        }else{
            $return = false;
        }
        return $return;
    }
    
    public function verificarSolvenciaAcademica($ci,$pensum){
        $solvencia = $this->tesisgrado->getSolvenciaAcademica($ci,$pensum);
        return $solvencia;
    }
    
    public function verificarInscripcionTesis($ci){
        $verificar = $this->tesisgrado->verificarInscripcion($ci);
        return $verificar;
    }
    
    public function verificarPagoArticulo($cedula,$pensum){
        if($pensum == "1997"){
            $solven = $this->profit->VerificarPagoTesis($cedula,'TRABGRADOII97');
        }else if($pensum == "2012"){
            $solven = $this->profit->VerificarPagoTesis($cedula,'TRABGRADOII12');
        }
        $solvenciaAdministrativa = strlen($solven[0]);
        if($solvenciaAdministrativa>0){
            $solvencia = true;
        }else{
            $solvencia = false;
        }
        return $solvencia;
    }
    
    public function busquedaAction(){
        $this->SwapBytes_Ajax->setHeader(); 
        $ci= $this->_getParam('ci');
        $json = array();
        $HTML = "";
        $HTML0 = "";
        
        $HTML1 = "";
        if($this->validar($ci) && $ci != ""){
            $datos = $this->Certificadocompentencia->getUsuarios($ci);
            if($datos[0]['nombre']!= null){
                $contadorImprimir = 0;
                $pensum = $this->tesisgrado->getPensum($ci);
                $pagoArticulo = $this->verificarPagoArticulo($ci,$pensum[0]["nombre"]);
                $solvenciaAcademica = $this->verificarSolvenciaAcademica($ci,$pensum[0]["nombre"]);
                $inscripcionTesis = $this->verificarInscripcionTesis($ci);
                $cumple = $this->verificarRequisitos($ci, $pensum[0]["nombre"]);
                $HTML .=    "<br>".
                            "<table class='tabledata' border='1px' align='center'  cellpadding='0' cellspacing='0' font-size= '8px'>".
                            "<tr>".
                            "<td> Nombres:".
                            "</td>".
                            '<td>'.$datos[0]['nombre'].'.</td>'.
                            "</tr>".
                            "<tr>".
                            "<td> Apellidos:".
                            "</td>".
                            '<td>'.$datos[0]['apellido'].'.</td>'.
                            "</tr>".
                            "<tr>".
                            "<td> Pensum:".
                            "</td>".
                            '<td>'.$pensum[0]["nombre"].'.</td>'.
                            "</tr>".
                            "</table>";

                $HTML0 .=   "<br>".
                            "<table class='tabledata' border='1px' align='center'  cellpadding='0' cellspacing='0' font-size= '8px'>". 
                            "<tr>";
                if(!$solvenciaAcademica[0]['resultado']){
                    $HTML0 .=   "<td> Solvencia Academica: </td>".
                                "<td style='color:green'> Solvente. </td>";
                }else{
                    $HTML0 .=   "<td> Solvencia Academica: </td>".
                                "<td style='color:red'> No está solvente. </td>";
                }
                $HTML0 .=   '</tr>'.
                            "<tr>";
                if($pagoArticulo){
                    $HTML0 .=   "<td> Pago del Artículo: </td>".
                                "<td style='color:green'> Solvente. </td>";
                }else{
                    $HTML0 .=   "<td> Pago del Artículo: </td>".
                                "<td style='color:red'> Falta pago del artículo. </td>";
                }
                $HTML0 .=   '</tr>'.
                            "<tr>";
                if($inscripcionTesis[0]["resultado"]){
                    $HTML0 .=   "<td> Estado: </td>".
                                "<td style='color:green'> No ha inscrito Tesis II. </td>";
                }else{
                    $HTML0 .=   "<td> Estado: </td>".
                                "<td style='color:red'> Tesis II inscrita o aprobada. </td>";
                    $contadorImprimir++;
                }
                $HTML0 .=   "</tr>".
                            "</table>";
                $HTML1 .=   "<table>".
                            "<tr>";
               // $cumple = true;
                if(!$cumple){
                    if($contadorImprimir>0){
                        $HTML1 .=   "<td style='color:red'><br><h3 style='font-size: 16px; color:red;'>Este estudiante ya inscribió o curso la materia.</h3></td>";
                        $json[] = '$("#inscribir").hide()';
                        $json[] = '$("#imprimir").show()';
                        $json[] = '$("#divSolvencias").hide()';
                    }else{
                        if($pensum[0]["nombre"]=='1997'){
                            $HTML1 .=   "<td style='color:red'><br><h3 style='font-size: 16px; color:red;'> No cumple con los requisitos para inscribir Tesis de Grado II.</h3></td>";
                        }else{
                            $HTML1 .=   "<td style='color:red'><br><h3 style='font-size: 16px; color:red;'> No cumple con los requisitos para inscribir Trabajo de Grado II.</h3></td>";
                        }   
                        $json[] = '$("#datosEstudiante").show()';
                        $json[] = '$("#divSolvencias").show()';
                    }
                }else{
                    if($pensum[0]["nombre"]=='1997'){
                        $HTML1 .=   "<td style='color:green'><br><h3 style='font-size: 16px; color:green;'> Cumple con los requisitos para inscribir Tesis de Grado II.</h3></td>";
                    }else{
                        $HTML1 .=   "<td style='color:green'><br><h3 style='font-size: 16px; color:green;'> Cumple con los requisitos para inscribir Trabajo de Grado II.</h3></td>";
                    } 
                    $json[] = '$("#inscribir").show()';
                    $json[] = '$("#imprimir").hide()';
                    $json[] = '$("#datosEstudiante").show()';
                    $json[] = '$("#divSolvencias").show()';
                }
                $HTML1 .=   "</tr>".
                            "</table>";
            }else{
                $HTML1 .=   "<br>".
                            "<table>".
                            "<tr>". 
                            "<td style='color:red'>".
                            "<p align='center' style='font-size: 16px;'>".
                            "<b>No existen registros asociados a ese número de cédula.</b>".
                            "</p>".
                            "</td>".
                            "</tr>".
                            "</table>";
                $json[] = '$("#inscribir").hide()';
                $json[] = '$("#imprimir").hide()';
            }
        }else{
            $HTML1 .=   "<br>".
                        "<table>".
                        "<tr>".
                        "<td style='color:red'>".
                        "<p align='center' style='font-size: 16px;'>".
                        "<b>Formato de cédula incorrecto.</b>".
                        "</p>".
                        "</td>".
                        "</tr>".
                        "</table>";
            $json[] = '$("#inscribir").hide()';
            $json[] = '$("#imprimir").hide()';
        }
            
        $json[] = '$("#datosEstudiante").html("' . $HTML . '")';
        $json[] = '$("#divSolvencias").html("' . $HTML0 . '")';
        $json[] = '$("#mensaje").html("' . $HTML1 . '")';

        $this->getResponse()->setBody(Zend_Json::encode($json));
    }
    public function InscribirTesis($cedula){
        $fecha = date("Y-m-d");
        $periodo = $this->tesisgrado->getUltimoPeriodoVigente($fecha);
         
        $inscrito = $this->tesisgrado->BuscarInscripcionenPeriodoActual($cedula,$periodo);
        
        if($inscrito){
            $inscripcion = $inscrito;
        }else{
            //$dataEstudiante = $this->tesisgrado->getDataEstudiante($ci);
            //$inscripcion = $this->tesisgrado->CrearInscripcion($cedula,$periodo,$numeropago,$sede);
        }
            //$this->tesisgrado->CrearTesisEnInscripcion($inscripcion,$asignacion);
    }
}

?>
