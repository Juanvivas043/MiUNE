<?php

class Transactions_InscripciontesisController extends Zend_Controller_Action {

    public function init() {

        Zend_Loader::loadClass('Models_DbTable_Inscripciontesis');
        Zend_Loader::loadClass('Models_DbTable_Profit');
        Zend_Loader::loadClass('Models_DbTable_Certificadocompetencia');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        $this->tesisgrado           = new Models_DbTable_Inscripciontesis();
        $this->profit               = new Models_DbTable_Profit();
        $this->Certificadocompentencia = new Models_DbTable_Certificadocompetencia();
        $this->grupo = new Models_DbTable_UsuariosGrupos();

        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax->setView($this->view);
        $this->CmcBytes_Profit = new CmcBytes_Profit();
        $this->SwapBytes_Crud_List = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form = new SwapBytes_Crud_Form();
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
        $this->view->title = "Transacciones \ Inscripcion Trabajo de Grado II";
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Crud_Form = $this->SwapBytes_Crud_Form;
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
            $json = array();
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
                $HTML1 .=   "<td style='color:red'><br><h3 style='font-size: 16px; color:red;'>Los datos de la cédula no coinciden.</h3></td>";
                $cont++;
            }
            if($cumple){
                $fecha = date("Y-m-d");
                $periodo = $this->tesisgrado->getPeriodoVigente($fecha);
                $periodo = $periodo[0]["pk_periodo"];
                $verificarInscripcion = $this->tesisgrado->verificarInscripcionPeriodo($periodo,$ci);
                $fk_asignatura = $this->tesisgrado->getFkAsignatura($pensum[0]["nombre"],$pensum[0]["pk_pensum"]);
                if($verificarInscripcion[0]["count"] > 0){

                   $fk_inscripcion = $this->tesisgrado->getFkInscripcion($periodo,$ci);
                     //var_dump($fk_asignatura[0]["pk_asignatura"],$fk_inscripcion[0]["pk_inscripcion"]);die;

                    $this->tesisgrado->setRecordAcademico($fk_asignatura[0]["pk_asignatura"],$fk_inscripcion[0]["pk_inscripcion"]);

                    $HTML .=   "<td style='color:green'><br><h3 style='font-size: 16px; color:green;'>Inscripción realizada con éxito.</h3></td>";
                }else{

                    $usuariogrupo = $this->tesisgrado->getPkUsuarioGrupoEstudiante($ci);
                    $usuariogrupo = $usuariogrupo[0]["pk_usuariogrupo"];
                    $escuela = $this->tesisgrado->getUltimaEscuela($ci);
                    $escuela = $escuela[0]["fk_escuela"];
                    $escuela = (int)$escuela;
                    $estructura = $this->tesisgrado->getEstructuraUltimaInscripcion($ci);
                    $estructura = $estructura[0]["fk_estructura"];

                     if($pensum[0]["nombre"] == "1997" || $pensum[0]["nombre"] == "1992"){
                        $num = $this->profit->VerificarPagoCompetencia($ci, 'TRABGRADOII97');
                    }else{
                        $num = $this->profit->VerificarPagoCompetencia($ci, 'TRABGRADOII12');
                    }

                    if ($num == null){

                        $per  = $estructura == '8' ? $periodo - 86: $periodo;
                        $es = $estructura  == '7'?'LN':'CENTRO' ;

                            $num = $this->profit->VerificarPagoTesis($ci,'UC'.$es.$per);
                    }

                    $numero_pago = $num[0] ;

                    if($pensum[0]["nombre"]=="Vigente"){

                        $semestre = 9697;
                    }else{
                        $semestre = 884;
                    }
                    $pensum = $pensum[0]["pk_pensum"];


                    $fechahora = $fecha . " " . date("H") . ":" . date("i") . ":" . date("s");
                  //  var_dump($usuariogrupo,$numero_pago,$fechahora,$periodo,$escuela,$estructura,$semestre,$pensum);die;
                   $this->tesisgrado->setInscripcion($usuariogrupo,$numero_pago,$fechahora,$periodo,$escuela,$estructura,$semestre,$pensum);
                    $fk_inscripcion = $this->tesisgrado->getFkInscripcion($periodo,$ci);
                    //var_dump($fk_asignatura[0]["pk_asignatura"],$fk_inscripcion[0]["pk_inscripcion"]);die;
                   $this->tesisgrado->setRecordAcademico($fk_asignatura[0]["pk_asignatura"],$fk_inscripcion[0]["pk_inscripcion"]);
                    $HTML .=   "<td style='color:green'><br><h3 style='font-size: 16px; color:green;'>Inscripción realizada con éxito.</h3></td>";
                }
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

        if(ctype_digit($ci)&& $ci != ""){

        $fecha = date("Y-m-d");
        $pensum = $this->tesisgrado->getPensum($ci);

        if ($pensum[0]['nombre']=='Vigente' ){
            $materia  ="Trabajo de grado II";
        }else{
            $materia = "Tesis de grado II";
        }

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
                    $filename    = 'Inscripcion'.str_replace(' ', '', $materia);
                    $filetype    = 'pdf';

                    $params = "'cedula=integer:{$ci}|logo=string:{$imagen}|asignatura=integer:{$inscrito[0]["asignatura"]}|periodo=integer:{$periodo}'";
                    $cmd    = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D pgsql -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";

                    //echo $cmd;exit;

                    Zend_Layout::getMvcInstance()->disableLayout();
                    Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
                    Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );

                    $outstream = exec($cmd); //exec ejecuta el java indicado por la ruta $cmd
                    echo base64_decode($outstream);
            }else{
               $message = '<div><H3 align="center" class="alert" style="color:red">El estudiante no esta Inscrito o ya Aprobo la asignatura </H3></div>';
                //mensaje en modal
              echo $message;
             //$this->SwapBytes_Crud_Form->getDialog('Advertencia', $message);
            }
        }
    }

    public function verificarRequisitos($ci,$pensum){
        var_dump("hey");
        die();
        $pagoArticulo = $this->verificarPagoArticulo($ci,$pensum);
        $solvenciaAcademica = $this->verificarSolvenciaAcademica($ci,$pensum);
//        var_dump($solvenciaAcademica);die;
        $inscripcionTesis = $this->verificarInscripcionTesis($ci);
        $soloTesis= $this->tesisgrado->faltaSoloTesis($ci,$pensum);
        if(!$solvenciaAcademica[0]['resultado'] && $pagoArticulo && $inscripcionTesis[0]["resultado"] && $soloTesis){
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
        $verificar = $this->tesisgrado->verificarInscritaAprobada($ci);
        return $verificar;
    }

    public function verificarPagoArticulo($cedula,$pensum){
        $fecha = date("Y-m-d");
        $periodo = $this->tesisgrado->getPeriodoVigente($fecha);
        $periodo = $periodo[0]["pk_periodo"];
        $estructura = $this->tesisgrado->getEstructuraUltimaInscripcion($cedula);
        $estructura = $estructura[0]["fk_estructura"];
        $per  = $estructura == '8' ? $periodo - 86: $periodo;
        $es = $estructura  == '7'?'LN':'CENTRO' ;

        $uc = $this->profit->VerificarPagoTesis($cedula,'UC'.$es.$per);

        //var_dump($uc);die;

        if($pensum == "1997" || $pensum == '1992' ){
            $solven = $this->profit->VerificarPagoTesis($cedula,'TRABGRADOII97');
            $solven2 = intval($uc[2]) >= 6 ? true : false;
        }else if($pensum == "Vigente"){
            $solven = $this->profit->VerificarPagoTesis($cedula,'TRABGRADOII12');
           // var_dump($solven);die;
            $solven2 = intval($uc[2]) >= 5 ? true : false;
        }


        $solvenciaAdministrativa = 1;

        if($solvenciaAdministrativa>0 || $solven2){
            $solvencia = true;
        }else{
            $solvencia = false;
        }
        //var_dump($solvencia);die;
        return $solvencia;
    }

    public function busquedaAction(){
        $this->SwapBytes_Ajax->setHeader();
        $ci= $this->_getParam('ci');
        //Declaro modalidad y verifico que tenga P o T
        $modalidad = $this->tesisgrado->getModalidad($ci);
        if ($modalidad[0]["tipo"] == null)
            $modalidad[0]["tipo"] = 'Por definir.';
        $json = array();
        $HTML = "";
        $HTML0 = "";
        $HTML1 = "";
        $HTML2 ="";
        if($this->validar($ci) && $ci != ""){
            $datos = $this->Certificadocompentencia->getUsuarios($ci);
            if($datos[0]['nombre']!= null){
                $contadorImprimir = 0;
                $pensum = $this->tesisgrado->getPensum($ci);
                $pagoArticulo = $this->verificarPagoArticulo($ci,$pensum[0]["nombre"]);
                $solvenciaAcademica = $this->verificarSolvenciaAcademica($ci,$pensum[0]["nombre"]);

                $inscripcionTesis = $this->verificarInscripcionTesis($ci);
                $soloTesis= $this->tesisgrado->faltaSoloTesis($ci,$pensum[0]["nombre"]);
               // var_dump($soloTesis);exit;
                $cumple = $this->verificarRequisitos($ci, $pensum[0]["nombre"]);
                $HTML .=    "<br>".
                            "<table class='tabledata' border='1px' align='center'  cellpadding='0' cellspacing='0' font-size= '8px'>".
                            "<tr>".
                            "<td> Nombres:".
                            "</td>".
                            '<td>'.$datos[0]['nombre'].'</td>'.
                            "</tr>".
                            "<tr>".
                            "<td> Apellidos:".
                            "</td>".
                            '<td>'.$datos[0]['apellido'].'</td>'.
                            "</tr>".
                            "<tr>".
                            "<td> Pensum:".
                            "</td>".
                            '<td>'.$pensum[0]["nombre"].'</td>'.
                            "</tr>".
                            "<tr>".
                            "<td> Modalidad:".
                            "</td>".
                            '<td>'.$modalidad[0]["tipo"].'</td>'.
                            "</tr>".
                            "</table>";

                $HTML0 .=   "<br>".
                            "<table class='tabledata' border='1px' align='center'  cellpadding='0' cellspacing='0' font-size= '8px'>".
                            "<tr>";
                            //var_dump($solvenciaAcademica[0]['resultado']);die;
                if(!$solvenciaAcademica[0]['resultado'] && $soloTesis){
                    $HTML0 .=   "<td> Solvencia Academica: </td>".
                                "<td style='color:green'> Solvente. </td>";
                    $json[] = '$("#tblmaterias").html("")';
                    $json[] = '$("#materias").html("")';

                }else{
                    $HTML0 .=   "<td> Solvencia Academica: </td>".
                                "<td style='color:red'> No está solvente. </td>";
                        $materias =  $this->tesisgrado->getMaterias($ci, $pensum[0]["nombre"]);
                        $json[] = '$("#materias").html("Materias faltantes")';
                        $json[] =   $this->tbl_Materias($materias);

                }
                $HTML0 .=   '</tr>'.
                            "<tr>";
                if($pagoArticulo){
                    $HTML0 .=   "<td> Pago del Artículo: </td>".
                                "<td style='color:green'> Solvente. </td>";
                }else{
                    $HTML2 = "<b>Es posible que el Articulo facturado no sea el correcto para verificar dirijase al departamento de pagos.</b>";


                    $HTML0 .=   "<td> Pago del Artículo: </td>".
                                "<td style='color:red'> Falta pago del artículo .</td>";
                }
                $HTML0 .=   '</tr>'.
                            "<tr>";
                if($inscripcionTesis[0]["resultado"]){
                    $HTML0 .=   "<td> Estado: </td>".
                                "<td style='color:green'> No ha inscrito Trabajo de Grado II. </td>";
                }else{
                    $HTML0 .=   "<td> Estado: </td>".
                                "<td style='color:red'> Trabajo de Grado II inscrita o aprobada. </td>";
                    $contadorImprimir++;
                }
                $HTML0 .=   "</tr>".
                            "</table>";
                $HTML1 .=   "<table>".
                            "<tr>";
                if(!$cumple){
                    if($contadorImprimir>0){
                        $HTML1 .=   "<td style='color:red'><br><h3 style='font-size: 16px; color:red;'>Este estudiante ya inscribió o curso la materia.</h3></td>";
                        $json[] = '$("#inscribir").hide()';
                        $json[] = '$("#imprimir").show()';
                        $json[] = '$("#divSolvencias").hide()';
                        $json[] = '$("#tblmaterias").html("")';
                        $json[] = '$("#materias").html("")';
                    }else{
                        if($pensum[0]["nombre"]=='1997'){
                            $HTML1 .=   "<td style='color:red'><br><h3 style='font-size: 16px; color:red;'> No cumple con los requisitos para inscribir Tesis de Grado II.</h3></td>";
                        }else{
                            $HTML1 .=   "<td style='color:red'><br><h3 style='font-size: 16px; color:red;'> No cumple con los requisitos para inscribir Trabajo de Grado II.</h3></td>";
                        }

                        $json[] = '$("#datosEstudiante").show()';
                        $json[] = '$("#divSolvencias").show()';
                        $json[] = '$("#imprimir").hide()';
                        $json[] = '$("#inscribir").hide()';
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

          //var_dump($HTML2);die;
        $json[] = '$("#datosEstudiante").html("' . $HTML . '")';
        $json[] = '$("#divSolvencias").html("' . $HTML0 . '")';
        $json[] = '$("#mensaje").html("' . $HTML1. '")';
        $json[] = '$("#error").html("'.$HTML2.'")';
        $this->getResponse()->setBody(Zend_Json::encode($json));

    }


    public function tbl_Materias($materias){

       $ra_property_table = array('class' => 'tableData','width' => '600px','column' => 'disponible');

       $ra_property_column = array(

            array('name'     => 'Semestre',
                'column'   => 'semestre',
                'width'    => '70px',
                'rows'     => array('style' => 'text-align:center')),
            array('name'     => 'Materia',
                'column'   => 'materia',
                'td'    => '20px',
                'rows'     => array('style' => 'text-align:center')),
            array('name'     => 'Codigo Propietario',
                'column'   => 'codigopropietario',
                'td'    => '20px',
                'rows'     => array('style' => 'text-align:center'))

            );


        /*Creamos el html de la tabla*/
        $HTML = $this->SwapBytes_Crud_List->fill($ra_property_table, $materias, $ra_property_column);
            //var_dump($HTML);die;
        /*Creamos en el arreglo json para responder la peticion en el div tblRequisitos*/
        return $this->SwapBytes_Jquery->setHtml('tblmaterias', $HTML);
    }
}

?>
