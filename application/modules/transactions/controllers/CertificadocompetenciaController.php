<?php

class Transactions_CertificadocompetenciaController extends Zend_Controller_Action {
    private $Title = "Transacciones / Certificado de competencia";
    
    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Certificadocompetencia');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('CmcBytes_Profit');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Profit');
        
        $this->Certificadocompentencia = new Models_DbTable_Certificadocompetencia();
        $this->periodos                = new Models_DbTable_Periodos();
        $this->inscripciones           = new Models_DbTable_Inscripciones();
        $this->recordacademico         = new Models_DbTable_Recordsacademicos();
        $this->estudiante              = new Models_DbTable_Usuarios();        
        $this->grupo = new Models_DbTable_UsuariosGrupos();
        $this->profit             = new Models_DbTable_Profit();
        
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        
        $this->CmcBytes_Profit = new CmcBytes_Profit();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        
        $this->logger = Zend_Registry::get('logger');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
    }
    
    function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if(!$this->grupo->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
                          
                                        
                                                        }
    }
    
    public function indexAction() {
        
	$this->view->title = $this->Title;
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
        if($cont == strlen($ci) ){
           return true;  
        }
        return false;
    }
    
    public function verificarSolvenciaAdministrativa($cedula){
        $solvenciaAdministrativa = $this->CmcBytes_Profit->getSolvente($cedula);
        //$becado = $this->CmcBytes_Profit->isBecado($cedula);
        return $solvenciaAdministrativa;
    }  
    
    public function verificarSolvenciaAcademica($ci){
        $solvenciaAcademica = $this->Certificadocompentencia->getResultado($ci);
        return $solvenciaAcademica;
    }
    
    public function verificarSolvenciaServicioComunitario($ci){
        $SolvenciaServCom = $this->Certificadocompentencia->checkSolvenciaServicioComunitario($ci);
        return $SolvenciaServCom;
    }
    
    public function verificarSolvenciaBiblioteca($ci){
        $Biblioteca = $this->Certificadocompentencia->checkBiblioteca($ci);
        $solvenciaBiblioteca = false;
        $cuenta = count($Biblioteca);
        //$arreglo = array();
        if($cuenta > 0){
            $solvenciaBiblioteca = false;
        }else{
            $solvenciaBiblioteca = true;
        }
        $arreglo = array('solvencia'=>$solvenciaBiblioteca,'cuenta'=>$cuenta);
        return $arreglo;
    }
    
    public function verificarPagoArticulo($cedula){
        
        
        $solven = $this->profit->VerificarPagoCompetencia($cedula,'CERTCOMPETENCIA');
        $solvenciaAdministrativa = strlen($solven[0]);
       
        
        if($solvenciaAdministrativa>0){
            $solvencia = true;
        }else{
            $solvencia = false;
        }
        
        return $solvencia;
    }
    
    public function verificar($ci){
        $solvencia = false;
        $solvenciaAdmin = $this->verificarSolvenciaAdministrativa($ci);
        $solvenciaPagoArt = $this->verificarPagoArticulo($ci);
        $solvenciaServicioComunitario = $this->verificarSolvenciaServicioComunitario($ci);
        $BibliotecaArreglo = $this->verificarSolvenciaBiblioteca($ci);
        $resultado = $this->verificarSolvenciaAcademica($ci);
        
        if ($BibliotecaArreglo['solvencia'] == true && $solvenciaAdmin == true && $resultado[0]['resultado'] == true && $solvenciaPagoArt == true){
            $solvencia = true;
        }else{
            $solvencia = false;
//            $solvencia = true;
        }
        
        $arreglo = array('bibliotecasolvencia'=>$BibliotecaArreglo['solvencia'],'bibliotecalibros'=>$BibliotecaArreglo['cuenta'],'administrativo'=>$solvenciaAdmin,'serviciocomunitario'=>$solvenciaServicioComunitario[0]['solvencia'],'academico'=>$resultado[0]['resultado'],'solvencia'=>$solvencia,'articulo'=>$solvenciaPagoArt);
        return $arreglo;
    }
    
    public function verificarSolicitudExistente($ci){
        $Verificar = $this->Certificadocompentencia->checkExistenciaSolicitud($ci);
        return $Verificar;
    }
    
    public function verificarExistenciaSolicitud($pk_usuariogrupo,$periodovigente,$numeropago,$fecha,$estructura){
        $Resultado = "";
        $datosUsuario = $this->Certificadocompentencia->getPkUsuarioGrupoSolicitud($pk_usuariogrupo,$periodovigente,$numeropago,$fecha,$estructura);
        if ($datosUsuario[0] != ""){
            $Resultado = true;
        }else{
            $Resultado = false;
        }
        $arreglo = array('pk_usuariogruposolicitud'=>$datosUsuario[0]['pk_usuariogruposolicitud'],'resultado'=>$Resultado);
        return $arreglo;
    }
    
    public function generarsolicitudAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
            $ci = $this->authSpace->userId;
              
            $verificacion = $this->verificarSolicitudExistente($ci);
            
            $num = $this->profit->VerificarPagoCompetencia($ci, 'CERTCOMPETENCIA');
            $numero_pago =$num[0] ;
            
            //verificar usuario.
            if ( $this->isSeguro($this->verificar($ci)) && $verificacion[0]['resultado'] == false){
                
                $datosUsuario = $this->Certificadocompentencia->getUsuarios($ci);
                $fecha = date('Y-m-d');
                $pk_usuariogrupo = $this->Certificadocompentencia->getPkUsuarioGrupo($ci);
                $periodoAcademico = $this->Certificadocompentencia->getPeriodoAcademicoVigente($fecha);
                $estructura = $datosUsuario[0]['estructura'];
                $verificarSolicitud = $this->verificarExistenciaSolicitud($pk_usuariogrupo[0]['pk_usuariogrupo'],$periodoAcademico[0]['pk_periodo'],$numero_pago,$fecha,$estructura);
                $escuela = $this->Certificadocompentencia->getUltimaEscuela($ci);

                if($verificarSolicitud[0]['resultado'] == false){
                    $this->Certificadocompentencia->setUsuariosGruposSolicitudes($pk_usuariogrupo[0]['pk_usuariogrupo'],$periodoAcademico[0]['pk_periodo'],$numero_pago,$fecha,$estructura);
                    $getPk_usuariogruposolicitud = $this->verificarExistenciaSolicitud($pk_usuariogrupo[0]['pk_usuariogrupo'],$periodoAcademico[0]['pk_periodo'],$numero_pago,$fecha,$estructura);
                    $this->Certificadocompentencia->setDocumentosSolicitados($getPk_usuariogruposolicitud['pk_usuariogruposolicitud'],$escuela[0]['fk_escuela']);
                }
                
            }
            
        }
        
    }
    
    public function descargarAction(){
           
            $ci = $this->authSpace->userId;
            $verificacion =$this->verificar($ci);
            if($this->isSeguro($verificacion)){
                $datadmin = $this->profit->VerificarPagoCompetencia($ci,'CERTCOMPETENCIA');

                $queryArray = array($ci);
                $config = Zend_Registry::get('config');

                $dbname = $config->database->params->dbname;
                $dbuser = $config->database->params->username;
                $dbpass = $config->database->params->password;
                $dbhost = $config->database->params->host;
                $report = APPLICATION_PATH . '/modules/reports/templates/certificadocompetencia/report1.jasper';
                $imagen = APPLICATION_PATH . '/modules/reports/templates/certificadocompetencia/logo.png';
                $filename    = 'CertificadoCompetencia';
                $filetype    = 'pdf';


                $params = "'cedula=integer:{$queryArray[0]}|Imagen=string:{$imagen}|numeropago=integer:{$datadmin[0]}|monto=string:{$datadmin[1]}'";


                $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D pgsql -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                //echo $cmd;exit;

                Zend_Layout::getMvcInstance()->disableLayout();
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );

                $outstream = exec($cmd); //exec ejecuta un programa externo indicado por la ruta $cmd
    //            echo $outstream;

                echo base64_decode($outstream);
            
            }else{
                 $message = '<H3 align="center" class="alert" style="color:red">Usted no esta calificado para obtener el Certificado de Competencia </H3>';
                 echo($message);
                
            }
           
        
    }
    
    public function isSeguro($check){
        
        $seguro = true;
        
        foreach($check as $key=>$val){
            
            if($key != 'bibliotecalibros'){
                if($val == false){
                    $seguro=false;
                
                }
           }else{
               if($val!=0)
               $seguro=false;
           }
        }
      
        return $seguro;
        
    }
    
    public function verificarAction() {
        
        if ($this->_request->isXmlHttpRequest()) {
            
        $this->SwapBytes_Ajax->setHeader();
        
        $json = array();
        $json2 = "";
        $HTML0 = "";
        $HTML1 = "";
        $HTML2 = "";
        $leyenda = false;
       $ci = $this->authSpace->userId;
//    $ci ='18941308';
        
        if ($this->validar($ci) && $ci != "") {
            
            $datos = $this->Certificadocompentencia->getUsuarios($ci);
            $materiasAprobadas = $this->Certificadocompentencia->getMateriasAprobadas($ci);
            $materiasFaltantes = $this->Certificadocompentencia->getMateriasFaltantes($ci);
            $codigoPropietario = $this->Certificadocompentencia->getCodigoPropietario($ci);
            $solvencia = $this->verificar($ci);
            
            $verificacion = $this->verificarSolicitudExistente($ci);
            
            if($datos[0]['nombre']!= null){

                if($codigoPropietario[0]['codpro'] == 8){
                    
                    $json[] = '$("#fotoDiv").show()';
                
                    $json2 .="<table class='tabledata' border='1px' id ='tbl_usuariosdato' align='center'  cellpadding='0' cellspacing='0' font-size= '8px'>". 
                            '<tr>'.
                                '<th> Nombre</th>'.
                              '<td>'.$datos[0]['nombre'].'.</td>'.
                              '</tr>'.  
                              '<tr>'.
                            '<th> Apellidos</th>'.
                              '<td> '.$datos[0]['apellido'].'.</td>'.
                              '</tr>'.
                              '<tr>'.
                            '<th> Escuela</th>'.
                              '<td> '.$datos[0]['escuela'].'.</td>'.
                              '</tr>'; 
                    
                    //Mensaje de estado de solvencia biblioteca
                      $json2 .=  '<th> Solvencia Con Biblioteca</th>'; 
                    if ($solvencia['bibliotecasolvencia'] == true){
                        $json2 .= 
                                  "<td style='color:green'> Solvente.</td>";
                                  
                    }else{
                        $json2 .= "<td style='color:red'> El estudiante posee libros {$solvencia['bibliotecalibros']} de la biblioteca en Tránsito o Mora.</td>";
                                  
                    }
                    
                    //Mensaje de estado de solvencia administrativa
                    $json2 .=  '<tr><th> Solvencia Administrativa</th>'; 
                   
                    if ($solvencia['administrativo'] == true){
                        $json2 .= 
                                  "<td style='color:green'> Solvente.</td>";
                                 
                    }else{
                        $json2 .= 
                                  "<td style='color:red'> No está solvente.</td>";
                                  
                    }
                    $json2 .= '</tr><tr><th> Solvencia Académica</th>';
                    //Mensaje de estado de solvencia académica
                    if ($solvencia['academico'] == true){
                        $json2 .= "<td style='color:green'> Solvente.</td>";
                                
                    }else{
                        $json2 .= "<td style='color:red'> No está solvente.</td>".
                                  "</tr>";
                    }
                    
                    //Mensaje de estado de solvencia servicio comunitario
                    $json2 .=  '<tr><th> Solvencia con Servicio Comunitario</th>'; 
                    if ($solvencia['serviciocomunitario'] == true){
                        $json2 .="<td style='color:green'> Solvente.</td>";
                                  
                    }else{
                        $json2 .= "<td style='color:red'> No está solvente.</td>".
                                  "</tr>";
                    }
                    
                    //Mensaje de estado de solvencia pago articulo
                   
                    $json2 .=  '<tr><th> Solvencia por Pago de Arancel</th>'; 
                    if ($solvencia['articulo'] == true){
                        $json2 .=  "<td style='color:green'> Solvente.</td>";
                                  
                    }else{
                        $json2 .= "<td style='color:red'> Falta pagar el arancel.</td>".
                                  "</tr>";
                    }
                     $json2 .= "</table>";
                   
                     if ($solvencia['solvencia'] == true){  
                        $json2 .= "<tr>".
                                  "<td style='color:green'><br><h3 style='font-size: 16px; color:green;'> Cumple con los requisitos para el certificado de competencia.</h3></td>".
                                  "</tr>";
                          //Descomentar al terminar el módulo.
                        if($verificacion[0]['resultado'] == false){
                            $json[] = '$("#generarsolicitud").show()';
                            $json[] = '$("#imprimir").hide()';
                        }else{
                            $json[] = '$("#imprimir").show()';
                            $json[] = '$("#generarsolicitud").hide()';
                        }
                        
                    }else{
                        if($codigoPropietario[0]['codpro'] == 8){
                            
                            
                            
                            if($solvencia['academico']&& $solvencia['serviciocomunitario']){
                             
                                $json2 .= "<tr>".
                                      "<td ><br><h3 style='font-size: 16px; color:green;'>Cumple con los requisitos Academicos.</h3></td>".
                                    "</tr>";
                                   $json2 .= "<tr>".
                                      "<td ><br><h3 style='font-size: 16px; color:red;'> Debe estar solvente con el resto de los requisitos.</h3></td>".
                                    "</tr>";
                                
                             
                            
                            }else{
                                      $json2 .= "<tr>".
                                      "<td ><br><h3 style='font-size: 16px; color:red;'>No cumple con los requisitos para el certificado de competencia.</h3></td>".
                                        "</tr>";
                            }
                            
                        }
                       
                    }
                    
                    if ($solvencia['solvencia'] == false && $solvencia['academico'] == false){
                        
                        $HTML0 .= "<tr>".
                                  "<td>".
                                  "<p align='center' style='font-size: 16px;'> <b>Aprobadas</b></p>".
                                  "</td>".
                                  "<td>".
                                  "<p align='center' style='font-size: 16px;'> <b>Faltantes</b> </p>".
                                  "</td>".    
                                  "</tr>".
                                  "<tr>".
                                  "<td>".
                                  "<div id='Aprobadas'>". 
                                  "<table class='tableData' id ='tbl_Aprobadas' align='center' border='1' cellpadding='0' cellspacing='0' font-size= '8px'>".
                                  "<tr>".
                                  "<td width='90px'align='center'>".
                                  "<p>Codigo </p>".
                                  "</td>".
                                  "<td width='300px'align='center'>".
                                  "<p>Asignatura </p>".     
                                  "</td>".
                                  "<td width='50px'align='center'>".
                                  "<p>Calificación </p>".
                                  "</td>".
                                  "</tr>";

                                  foreach ($materiasAprobadas as $matA){
                                        $HTML0 .= "<tr>".
                                                  "<td align='center'>".$matA['codigo']."</td>".
                                                  "<td align='center'>".$matA['materia']."</td>";
                                        if ($matA["nota"] == 0){
                                            $matA["nota"] = 'RA';
                                            $HTML0 .= "<td align='center'>".$matA['nota']."</td>".
                                                      "</tr>";
                                            $leyenda = true;
                                        }else{
                                            $HTML0 .= "<td align='center'>".$matA['nota']."</td>".
                                                      "</tr>";
                                        }
                                  }

                        $HTML0  .= "</table>".
                                   "<br>". 
                                   "</div>".
                                   "</td>".
                                   "<td>".
                                   "<div id='Faltantes'>".
                                   "<table class='tableData' id ='tbl_Faltantes' align='center' border='1' cellpadding='0' cellspacing='0' style='margin-left: 20px;'>".
                                   "<tr>".
                                   "<td width='90px'align='center'>".
                                   "<p>Codigo</p>".
                                   "</td>".
                                   "<td width='300px'align='center'>".
                                   "<p>Asignatura</p>".
                                   "</td>".
                                   "</tr>";

                                   foreach ($materiasFaltantes as $matF){
                                        $HTML0 .= "<tr>".
                                                  "<td align='center'>".$matF['codigo']."</td>".
                                                  "<td align='center'>".$matF['materia']."</td>".
                                                  "</tr>";
                                   }

                        $HTML0 .= "</table>".
                                  "<br>".  
                                  "</div>".                   
                                  "</td>".
                                  "</tr>";
                        
                        if ($leyenda == true){
                            $HTML1 .= "<p align='center' style='font-size: 12px;'><b>RA = Reconocimiento Académico</b></p>";    
                        }
                            
                    }elseif ($solvencia['solvencia'] == true OR ($solvencia['solvencia'] == false && $solvencia['academico'] == true)){

                        $HTML0 .= "<tr>".
                                  "<td>".
                                  "<p align='center' style='font-size: 16px;'> <b>Aprobadas</b></p>".
                                  "</td>".   
                                  "</tr>".
                                  "<tr>".
                                  "<td>".
                                  "<div id='Aprobadas'>".
                                  "<table class='tableData' id ='tbl_Aprobadas' align='center' border='1' cellpadding='0' cellspacing='0' font-size= '8px'>".

                                  "<tr>".
                                  "<td width='90px'align='center'>".
                                  "<p>Codigo </p>".
                                  "</td>".
                                  "<td width='300px'align='center'>".
                                  "<p>Asignatura </p>".     
                                  "</td>".
                                  "<td width='50px'align='center'>".
                                  "<p>Calificación </p>".
                                  "</td>".
                                  "</tr>";

                                  foreach ($materiasAprobadas as $matA){
                                        $HTML0 .= "<tr>".
                                                  "<td align='center'>".$matA['codigo']."</td>".
                                                  "<td align='center'>".$matA['materia']."</td>";
                                        if ($matA["nota"] == 0){
                                            $matA["nota"] = 'RA';
                                            $HTML0 .= "<td align='center'>".$matA['nota']."</td>".
                                                      "</tr>";
                                            $leyenda = true;
                                        }else{
                                            $HTML0 .= "<td align='center'>".$matA['nota']."</td>".
                                                      "</tr>";
                                        }
                                  }
                                  
                                  if ($leyenda == true){
                                      $HTML1 .= "<p align='center' style='font-size: 12px;'><b>RA = Reconocimiento Académico</b></p>";       
                                  }
                                  
                    }
                    
                }else{
                    
                    $json[] = '$("#fotoDiv").show()';
                
                    $json2 .= '<tr>'.
                              '<td>Nombre: '.$datos[0]['nombre'].'.</td>'.
                              '</tr>'.  
                              '<tr>'.
                              '<td>Apellido: '.$datos[0]['apellido'].'.</td>'.
                              '</tr>'.
                              '<tr>'.
                              '<td>Escuela: '.$datos[0]['escuela'].'.</td>'.
                              '</tr>';

                    $HTML0 .= "<tr>".
                              "<td>".
                              "<br>".
                              "<p align='center' style='font-size: 16px;'>".
                              "<b>Los estudiantes del pensum 1997 no pueden optar por el certificado de competencia.</b>".
                              "</p>".
                              "</td>";

                }
                
            }else{
                
                $json[] = '$("#fotoDiv").hide()';
                $json[] = '$("#generarsolicitud").hide()';
                $json[] = '$("#imprimir").hide()';
                
                $HTML0 .= "<tr>".
                          "<td style='color:red'>".
                          "<p align='center' style='font-size: 16px;'>".
                          "<b>No existen registros.</b>".
                          "</p>".
                          "</td>";

            }
            
        }else{
            
            $json[] = '$("#fotoDiv").hide()';
            
            $HTML0 .= "<tr>".
                      "<td style='color:red'>".
                      "<p align='center' style='font-size: 16px;'>".
                      "<b>Formato de cédula incorrecto.</b>".
                      "</p>".
                      "</td>";
            
        }

        $json[] = '$("#datosusuario").html("' . $json2 . '")';
        $json[] = '$("#tblcuadrado").html("' . $HTML0 .'")';
        $json[] = '$("#leyenda").html("' . $HTML1 .'")';
        $json[] = '$("#buttons").html("' . $HTML2 . '")';
        
        $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setWidthLeft('120px');
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();
        
        $this->getResponse()->setBody(Zend_Json::encode($json));

        }
            
    }
    
    public function photoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $id    = $this->_getParam('id', 0);
        $image = $this->estudiante->getPhoto($id);

        $this->getResponse()
             ->setHeader('Content-type', 'image/jpeg')
             ->setBody($image);
    }
    
}

//Andreas
?>
