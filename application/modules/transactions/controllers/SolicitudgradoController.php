<?php

class Transactions_SolicitudgradoController extends Zend_Controller_Action {

    private $Title = "Transacciones / Solicitud de Revision de Expediente";
    private $Tsuperior = 19759;

	public function init() {
        Zend_Loader::loadClass('Models_DbTable_Solicitudgrado');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Profit');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('CmcBytes_Profit');
        Zend_Loader::loadClass('Forms_Solicitudgradoplanilla');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        $this->Solicitudgrado   = new Models_DbTable_Solicitudgrado();
        $this->estudiante       = new Models_DbTable_Usuarios();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->atributo         = new Models_DbTable_Atributos();
        $this->profit           = new Models_DbTable_Profit();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->CmcBytes_Profit = new CmcBytes_Profit();
        $this->SwapBytes_Crud_List = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->view->form = new Forms_Solicitudgradoplanilla();
        $this->SwapBytes_Form->set($this->view->form);
        $this->view->form = $this->SwapBytes_Form->get();
        $this->Request      = Zend_Controller_Front::getInstance()->getRequest();
        $this->logger       = Zend_Registry::get('logger');
        $this->authSpace    = new Zend_Session_Namespace('Zend_Auth');
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
        $this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
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

   public function verificarSolvenciaAcademica($cedula) {
		return $this->Solicitudgrado->getSolvenciaAcademica($cedula);
   }

    public function verificarSolvenciaTesis($cedula) {
        return $this->Solicitudgrado->getSolvenciaTesis($cedula);
    }

    public function verificarSolvenciaAdministrativa($cedula, $solicitud){
    //Solo verifica si esta solvente NO se verifica especificamente el pago del arancel
    if (!$solicitud) return $this->CmcBytes_Profit->getSolvente($cedula);
    else return $this->profit->verSaldoEstudiante($cedula) <= 0;
    //return true; //averia profit
}

        public function verificar($cedula) {
            $solvencia = false;
            $solvenciaAcademica = $this->verificarSolvenciaAcademica($cedula);
            $solicitud = $this->verificarExistenciaSolicitud($cedula);
            $solvenciaTesis = $this->verificarSolvenciaTesis($cedula);
            $solvenciaAdministrativa = $this->verificarSolvenciaAdministrativa($cedula, $solicitud);
            if ($solvenciaAcademica == true && $solvenciaTesis[0]['solvenciatesis'] == true) {
                $solvencia = true;
            } else {
                $solvencia = false; //DESCOMENTAR
            }
            $arreglo = array('administrativo'=>$solvenciaAdministrativa,'academico'=>$solvenciaAcademica,'tesis'=>$solvenciaTesis[0]['solvenciatesis'],'solvencia'=>$solvencia);
            return $arreglo;
        }

        public function verificarExistenciaSolicitud($cedula) {
            $fecha = date('Y-m-d');
            $periodo = $this->Solicitudgrado->getUltimoPeriodoVigente($fecha);
			$solicitud = $this->Solicitudgrado->getUltimaSolicitudDeGrado($cedula);
            $verificarSolicitud = $this->Solicitudgrado->getUltimaSolicitudDeGrado ($cedula);
            return $verificarSolicitud;
        }

        public function generarsolicitudAction() {

            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();
                $ci = $this->authSpace->userId;
                $fecha = date('Y-m-d');
                $verificacion = $this->verificarExistenciaSolicitud($ci);
				$egresado = $this->Solicitudgrado->isEgresado($this->authSpace->userId);
                if (!$verificacion && !$egresado) {
                    $this->Solicitudgrado->setUsuariosGruposSolicitudes($ci,$fecha);
                    $this->Solicitudgrado->setDocumentosSolicitados($ci);
                    $json[] =  $this->TableRequisitos($ci);
                    $this->getResponse()->setBody(Zend_Json::encode($json));

                }
            }
        }

        public function addoreditloadAction() {

            if ($this->_request->isXmlHttpRequest()) {
            $json = array();
            $ci = $this->authSpace->userId;
            $dataRow = $this->Solicitudgrado->getUsuarioData($ci);
            $dataRow['trabajo'] = $dataRow['empresa'] == NULL? false:true;
            $title = 'Datos Adicionales para planilla de Solicitud de Grado';
            //var_dump($dataRow);die;
             $this->view->form->toogleRequired($dataRow['trabajo']);
             $this->SwapBytes_Form->enableElement('cargo',$dataRow['trabajo']);
             $this->SwapBytes_Form->enableElement('teloficina',$dataRow['trabajo']);
             $this->SwapBytes_Form->enableElement('empresa',$dataRow['trabajo']);
			 //var_dump($datarow);die;
			$this->SwapBytes_Form->fillSelectBox('pais', $this->Solicitudgrado->getSelectCiudades($dataRow['pais']) , 'pk_atributo', 'valor');
			$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
			$this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->setWidthLeft('120px');
            $this->SwapBytes_Crud_Form->getAddOrEditLoad();
            }
        }

        public function addoreditconfirmAction() {

            if ($this->_request->isXmlHttpRequest()) {
            $json = array();
            $this->SwapBytes_Ajax->setHeader();
            $datarow = $this->_params['modal'];
            $datarow['trabajo'] = !$datarow['trabajo'] == '0' ? true : false;
            //var_dump($req);die;
              $this->view->form->toogleRequired($datarow['trabajo']);
              $this->SwapBytes_Form->enableElement('empresa',$datarow['trabajo']);
              $this->SwapBytes_Form->enableElement('cargo',$datarow['trabajo']);
              $this->SwapBytes_Form->enableElement('teloficina',$datarow['trabajo']);
           // var_dump($datarow);
             $this->SwapBytes_Form->fillSelectBox('pais', $this->Solicitudgrado->getSelectCiudades($datarow['pais']) , 'pk_atributo', 'valor');
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $datarow);
            $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
            }
        }

        public function addoreditresponseAction() {
            if ($this->_request->isXmlHttpRequest()) {
				$this->SwapBytes_Ajax->setHeader();
				$json[] = 'window.location.href = urlAjax + "descargar/"';
				$datarow = $this->_params['modal'];
				//var_dump($datarow);die;
				$ci = $this->authSpace->userId;
				$verificarSolicitud = $this->verificarExistenciaSolicitud($ci);
				$documento = $verificarSolicitud[0]['documentoid'];
				$SolvenciaDocumentos = $this->Solicitudgrado->getSolvenciaDocumentos($documento);
				$solvencia = $this->verificarSolvenciaAdministrativa($ci, NULL);
				$solvnciaAcademico = $this->Solicitudgrado->getSolvenciaAcademica($ci,false);
				$egresado = $this->Solicitudgrado->isEgresado($this->authSpace->userId);
				if(empty($SolvenciaDocumentos) && $solvencia && $solvnciaAcademico && !$egresado) {
					$this->Solicitudgrado->setUsuariosDatos($ci,$datarow);
					$this->getResponse()->setBody(Zend_Json::encode($json));
					$this->SwapBytes_Crud_Form->setJson($json);
					$this->SwapBytes_Crud_Form->getAddOrEditEnd();
				}
            }
        }

        public function descargarAction() {
            $ci = $this->authSpace->userId;
            $verificarSolicitud = $this->verificarExistenciaSolicitud($ci);
            $documento = $verificarSolicitud[0]['documentoid'];
            $SolvenciaDocumentos = $this->Solicitudgrado->getSolvenciaDocumentos($documento);
            $solvencia = $this->verificarSolvenciaAdministrativa($ci, $verificarSolicitud);
            $solvnciaAcademico = $this->Solicitudgrado->getSolvenciaAcademica($ci,false);
            $egresado = $this->Solicitudgrado->isEgresado($this->authSpace->userId);
            if(empty($SolvenciaDocumentos) && $solvencia && $solvnciaAcademico && !$egresado) {
               // $datadmin = $this->profit->VerificarPagoCompetenc $solvencia = true; //ELIMINARia($ci,'CERTCOMPETENCIA');
                $config = Zend_Registry::get('config');
                $dbname = $config->database->params->dbname;
                $dbuser = $config->database->params->username;
                $dbpass = $config->database->params->password;
                $dbhost = $config->database->params->host;
                $report = APPLICATION_PATH . '/modules/transactions/templates/solicitudgrado/solicitudgrado.jasper';
                $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.png';
                $filename    = 'Solicitud_de_Grado';
                $filetype    = 'pdf';
                $params = "'cedula=integer:{$ci}|Imagen=string:{$imagen}'";
                $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D pgsql -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                //echo $cmd;exit;
                Zend_Layout::getMvcInstance()->disableLayout();
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
                Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
                $outstream = exec($cmd); //exec ejecuta un programa externo indicado por la ruta $cmd
    //            echo $outstream;
                echo base64_decode($outstream);
                //una vez se imprimio el documento se debe cambiar el estado de documento a aprobado
			$this->Solicitudgrado->setDocumentoEstado($documento,'14145');
            } else {
               $message = '<H3 align="center" class="alert" style="color:red">Usted no esta solvente con la entrega de documentos </H3>';
                //mensaje en modal
                echo( $message);
           }
       }

       public function verificarAction() {

           if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $ci = $this->authSpace->userId;
            $json   = array();
            $HTML0  = "";
            $HTML2  = "";
            if ($this->validar($ci) && $ci != "") {
                $datos = $this->Solicitudgrado->getUsuarios($ci);
                $solvencia = $this->verificar($ci);
                $verificacion = $this->verificarExistenciaSolicitud($ci);
                if($datos[0]['nombre']!= null) {
                    $json[] = '$("#fotoDiv").show()';
                    $HTML0 .=   "<table class='tabledata' border='1px' id ='tbl_usuariosdato' align='center'  cellpadding='0' cellspacing='0' font-size= '8px'>".
                    '<tr>'.
                    '<th> Nombre</th>'.
                    '<td>'.$datos[0]['nombre'].'.</td>'.
                    '</tr>'.
                    '<tr>'.
                    '<th> Apellidos</th>'.
                    '<td>'.$datos[0]['apellido'].'.</td>'.
                    '</tr>'.
                    '<tr>'.
                    '<th> Escuela</th>'.
                    '<td> '.$datos[0]['escuela'].'.</td>'.
                    '</tr>'.
                    '</table>';
                    $HTML1 ='';
                    $HTML1 .=   "<table class='tabledata' border='1px' id ='tbl_usuariosdato' align='center'  cellpadding='0' cellspacing='0' font-size= '8px'>".
                    '<tr>';
                        //Mensaje de estado de solvencia Solvencia Con Tesis de Grado II
                    $HTML1 .=   '<th> Tesis de Grado II (Inscrita o Aprobada)</th>';
                    if ($solvencia['tesis'] == true){
                        $HTML1 .= "<td style='color:green'> Solvente.</td>";
                    }else{
                        $HTML1 .= "<td style='color:red'> No está solvente.</td></tr>";
                    }
                        //Mensaje de estado de solvencia administrativa
                    $HTML1 .=  '<tr><th> Solvencia Administrativa</th>';

                    if ($solvencia['administrativo'] == true){
                        $HTML1 .= "<td style='color:green'> Solvente.</td>";
                    }else{
                        $HTML1 .= "<td style='color:red'> No está solvente.</td>";
                    }
                    $HTML1 .= '</tr><tr><th> Solvencia Académica</th>';
                    $verificarSolicitud = $this->verificarExistenciaSolicitud($ci);
                    $existe = empty($verificarSolicitud) == true ?:false;
                    $solvent = $this->Solicitudgrado->getSolvenciaAcademica($ci,true/*$existe*/);/*se dano el $existe y no sabia que hacia */
                    $solvent = $solvent[0]['resul'];
                        //Mensaje de estado de solvencia académica
                    if ($solvent == true) {
                        $HTML1 .= "<td style='color:green'> Solvente.</td>";
                    } else {
                        $HTML1 .= "<td style='color:red'> No está solvente.</td>".
                        '</tr>'.
                        '</table>';
                    }
                    $documento = $verificarSolicitud[0]['documentoid'];
                    if ($verificacion) {
                        $json[] =  $this->TableRequisitos($documento);
                    }
                    if ($solvencia['solvencia'] == true){
                        if(!$verificacion) {
							$egresado = $this->Solicitudgrado->isEgresado($this->authSpace->userId);
							if ($egresado) {
                               $json[] = '$("#solvenciadoc").html("<br><h3 style=\'font-size: 16px; color:red;\'>Usted es egresado de la Universidad por lo tanto no puede generar la solicitud</h3>")';
							} else {
                        $HTML2 .= "<tr>".
                        "<td style='color:green'><br><h3 style='font-size: 16px; color:green;'> Cumple con los requisitos para la solicitud de Revision de documentos.</h3></td>".
                        "</tr>";
                            	$json[] = '$("#generarsolicitud").show()';
							}
                            $json[] = '$("#imprimir").hide()';
                        } else {
                            $json[] = '$("#generarsolicitud").hide()';
                            $SolvenciaDocumentos = $this->Solicitudgrado->getSolvenciaDocumentos($documento);
                            $solvenciaAdministrativa = $this->verificarSolvenciaAdministrativa($ci, $verificacion);
                            $solvnciaAcademico = $this->Solicitudgrado->getSolvenciaAcademica($ci,false);
                            $solvnciaAcademico = $solvnciaAcademico[0]['resul'];
                            if (empty($SolvenciaDocumentos) && $solvenciaAdministrativa && $solvnciaAcademico) {
                               $json[] = '$("#imprimir").show()';
                           }
                           else {
                               $json[] = '$("#solvenciadoc").html("<br><h3 style=\'font-size: 16px; color:red;\'>Para Completar la solicitud de grado debe consignar todos los recaudos, estar solvente con todas sus cuotas  y tener todas sus materias aprobadas</h3>")';
                           }
                       }
                   } else {
                    $HTML2 .= "<tr>".
                    "<td ><br><h3 style='font-size: 16px; color:red;'> No cumple con los requisitos para la solicitud de Revisión de documentos</h3></td>".
                    "</tr>";
                }
            } else {
                $json[] = '$("#fotoDiv").hide()';
                $json[] = '$("#generarsolicitud").hide()';
                $json[] = '$("#imprimir").hide()';
            }
        } else {
            $json[] = '$("#fotoDiv").hide()';
        }
        $json[] = '$("#datosusuario").html("' . $HTML0 .'")';
        $json[] = '$("#datossolvencia").html("' . $HTML1 .'")';
        $json[] = '$("#mensajesolvencia").html("' . $HTML2 .'")';
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

public function TableRequisitos($documento){

    $ra_property_table = array('class' => 'tableData','width' => '600px','column' => 'disponible');
            //var_dump($documento);die;
    $Requisitos  = array();
    $red = $this->Solicitudgrado->getMoraoPorRevisar($documento);
    for ($x=1; $x<=8; $x++) {
        $consulta = $this->Solicitudgrado->getRequisitos($documento,$x, NULL);
        $Requisitos[$x] = $consulta[0];
        if($Requisitos[$x]['estado']) {
            $Requisitos[$x]['estado'] = '<p style="font-size: 12px; color:green;"> Solvente </p>';
        }else{
            $Requisitos[$x]['estado'] = '<p style="font-size: 12px; color:red;"> '.$red.' </p>';
        }
    }
    $tecnico = $this->Solicitudgrado->getValorEstadoSolicitud($documento,$this->Tsuperior);
            if ($tecnico){      //$var==TRUE ? 'TRUE' : 'FALSE';
                //traer todos los requisitos de tsu
            $consulta['exist'] = $this->Solicitudgrado->getTecnicoReq($documento);
            $consulta['faltantes'] = $this->Solicitudgrado->getRequisitosTecfaltante($documento);
                //var_dump($consulta);die;
            $i = 0;
            foreach ($consulta['exist'] as $req ) {
                $Requisitos[$x+$i]['tipo'] = 'TSU';
                $Requisitos[$x+$i]['Requisito'] =  $req['valor'];
                $color = $req['estado'] == 'Solvente'? 'green':'red';
                $Requisitos[$x+$i]['estado'] = '<p style="font-size: 12px; color:'.$color.';"> '.$req["estado"].' </p>';
                $i++;
            }
            foreach ($consulta['faltantes'] as $req ) {
                $Requisitos[$x+$i]['tipo'] = 'TSU';
                $Requisitos[$x+$i]['Requisito'] =  $req['valor'];
                $Requisitos[$x+$i]['estado'] = '<p style="font-size: 12px; color:red;"> '.$red.' </p>';
                $i++;
            }
        }
        /*Definimos las propiedades de las columnas */
        $ra_property_column = array(

            array('name'     => 'Estado',
                'column'   => 'estado',
                'width'    => '70px',
                'rows'     => array('style' => 'text-align:center')),
            array('name'     => 'Requisito',
                'column'   => 'Requisito',
                'td'    => '20px',
                'rows'     => array('style' => 'text-align:center')),
            array('name'     => 'Entrega',
                'column'   => 'tipo',
                'td'    => '20px',
                'rows'     => array('style' => 'text-align:center'))

            );
        /*Creamos el html de la tabla*/
        $HTML = $this->SwapBytes_Crud_List->fill($ra_property_table, $Requisitos, $ra_property_column);
            //var_dump($HTML);die;
        /*Creamos en el arreglo json para responder la peticion en el div tblRequisitos*/
        return $this->SwapBytes_Jquery->setHtml('tblRequisitos', $HTML);
    }
}

?>
