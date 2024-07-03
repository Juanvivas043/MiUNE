<?php  
	class Consultas_EstadodecuentaController extends Zend_Controller_Action{

		private $Title = "Consultas / Estado de cuenta del estudiante";

	    public function init() {
	       Zend_Loader::loadClass('Une_Filtros');
	  	   Zend_Loader::loadClass('Models_DbTable_Grupos');
	       Zend_Loader::loadClass('Models_DbTable_Reiniciarpass');
	       Zend_Loader::loadClass('Models_DbTable_Profit');
	       Zend_Loader::loadClass('CmcBytes_Profit');
	       Zend_Loader::loadClass('Models_DbTable_Usuarios');
	       Zend_Loader::loadClass('Models_DbTable_Solventes');
	       Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
	       Zend_Loader::loadClass('Models_DbTable_Reinscripciones');
	       Zend_Loader::loadClass('Models_DbTable_Inscripciones');
	       Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
	       Zend_Loader::loadClass('Models_DbView_Escuelas'); 
	       Zend_Loader::loadClass('Models_DbView_Sedes');
	       Zend_Loader::loadClass('Models_DbTable_Periodos');
	       

	       $this->usuario 				= new Models_DbTable_Usuarios();
	       $this->grupo 				= new Models_DbTable_UsuariosGrupos();
	       $this->reinscripciones		   	= new Models_DbTable_Reinscripciones();
	       $this->inscripciones 		   	= new Models_DbTable_Inscripciones();
	       $this->recordacademico 		   	= new Models_DbTable_Recordsacademicos();
	       $this->profit            	   	= new Models_DbTable_Profit();
	       $this->escuelas 				= new Models_DbView_Escuelas();
	       $this->cedula 			   	= new Zend_Session_Namespace('Zend_Auth');
	       $this->sede 				= new Models_DbView_Sedes();
	       $this->CmcBytes_profit          		= new CmcBytes_Profit();
	       $this->filtros 				= new Une_Filtros();
	       $this->periodos 				= new Models_DbTable_Periodos();
	       $this->SwapBytes_Uri            		= new SwapBytes_Uri();
	       $this->SwapBytes_Ajax           		= new SwapBytes_Ajax();
	       $this->SwapBytes_Ajax_Html      		= new SwapBytes_Ajax_Html();
	       $this->SwapBytes_Ajax_Action   		= new SwapBytes_Ajax_Action();
	       $this->SwapBytes_Jquery         		= new SwapBytes_Jquery();
	       $this->SwapBytes_Jquery_Ui_Form 		= new SwapBytes_Jquery_Ui_Form();
	       $this->SwapBytes_Jquery_Mask    		= new SwapBytes_Jquery_Mask();
	       $this->SwapBytes_Crud_Action    		= new SwapBytes_Crud_Action();
	       $this->SwapBytes_Crud_List      		= new SwapBytes_Crud_List();
	       $this->SwapBytes_Crud_Form      		= new SwapBytes_Crud_Form();
	       $this->SwapBytes_Html           		= new SwapBytes_Html();
       	       $this->SwapBytes_Html_Message  		= new SwapBytes_Html_Message();
	       $this->SwapBytes_Crud_Search    		= new SwapBytes_Crud_Search();
	       
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
	       		$this->view->title      			= $this->Title;
	        	$this->view->SwapBytes_Jquery      	  	= $this->SwapBytes_Jquery;
			$this->view->SwapBytes_Crud_Action 	  	= $this->SwapBytes_Crud_Action;
			$this->view->SwapBytes_Crud_Form   	  	= $this->SwapBytes_Crud_Form;
			$this->view->SwapBytes_Crud_Search 	  	= $this->SwapBytes_Crud_Search;
	        	$this->view->SwapBytes_Ajax        	  	= new SwapBytes_Ajax();
	        	$this->view->SwapBytes_Jquery_Ui_Form 		= new SwapBytes_Jquery_Ui_Form();
	        	$this->SwapBytes_Ajax->setView($this->view);
		}

	        public function listAction(){
	        	if ($this->_request->isXmlHttpRequest()) {
		          	$this->SwapBytes_Ajax->setHeader();
		           	$ci = $_GET["cedula"]; //se recibe la cedula por GET (de la vista)
		           
		           	if ($ci == NULL) {// validar que el campo cedula no este vacio
		           		$alertV = "El campo cedula esta vacio";
		           		$HTML = $this->SwapBytes_Html_Message->alert($alertV);
		           		$json[]= "$('#datosEstudiante').hide();";        
		           		$json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
		           		$this->getResponse()->setBody(Zend_Json::encode($json));
		           	}else{
		           	   			if ($this->usuario->getUsuario($ci) == NULL) { // validar que el usuario exista
				           			$alert = "La cédula no existe en el sistema.";
				           			$HTML = $this->SwapBytes_Html_Message->alert($alert);
				           			$json[]= "$('#datosEstudiante').hide();";        
				           			$json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
				           			$this->getResponse()->setBody(Zend_Json::encode($json));
		           			}else{
				           	   	$this->user = $this->usuario->getUsuario($ci); // se obtienen los datos del estudiante mediante la funcion getUsuario()
					           	//se buscan los datos necesarios que seran mostrados en la vista
					           	$this->nombre = $this->user['primer_nombre'].' '.$this->user['segundo_nombre'];
					      		$this->apellido = $this->user['primer_apellido'].' '.$this->user['segundo_apellido'];
					      		$this->nombrep = $this->nombre.' '.$this->apellido;
					      			if ($this->inscripciones->getUltimaSede($ci) == FALSE) {// validar que sea estudiante
					      				$alertN = "El usuario que intenta consultar no es estudiante.";
					           			$HTML = $this->SwapBytes_Html_Message->alert($alertN);
					           			$json[]= "$('#datosEstudiante').hide();";        
					           			$json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
					           			$this->getResponse()->setBody(Zend_Json::encode($json));
					      			}else{
							      		$this->ultimaSede = $this->inscripciones->getUltimaSede($ci);
							      		$this->sedeN = $this->sede->getSedeName($this->ultimaSede);
							      		$this->ultimoperiodo = $this->recordacademico->getUltimoPeriodoInscrito($ci)[0]['periodo'];
							      		$this->ultimoperiodocursado = $this->recordacademico->getUltimoPeriodocursado($ci)[0]['fn_xrxx_reinscripcion_upc'];
							      		// Si el estudiante es nuevo ingreso el ultimoperiodo va a llegar NULL se lleva a 0 con un operador ternario para que no de error el query
							      		$this->ultimoperiodocursado = ($this->ultimoperiodocursado == NULL) ?  0 : $this->ultimoperiodocursado;
							      		$this->pensum = $this->inscripciones->getPensumInscripcion($ci,$this->ultimoperiodo)[0]['fk_pensum'];	
							          	$this->ultimaEscuela = $this->recordacademico->getUltimaEscuelaCursada($ci)[0]['pk_atributo'];
							          	$this->ultimaEscuelaN = $this->escuelas->getEscuelaName($this->ultimaEscuela);
							           	$this->iuc = $this->reinscripciones->indicePeriodo($ci,$this->ultimoperiodocursado,$this->ultimaEscuela,$this->pensum)[0]['iiap'];
							           	$this->usemubi = $this->recordacademico->getSemestreUbicacion($ci,$this->ultimaEscuela,$this->ultimoperiodocursado,$this->pensum)[0]['semestre'];
							          	$this->uca            = $this->recordacademico->getUnidadesDeCreditoAprobadas($ci,$this->pensum,$this->ultimaEscuela,$this->ultimaSede)[0]["uc"]; 
							          	$this->cursadas = $this->recordacademico->materiasCursadas($ci, $this->pensum)[0]['materias'];
							          	$this->porCursar = $this->recordacademico->materiasPorCursar($ci, $this->pensum)[0]['materias'];
							          	$this->pensumN = $this->recordacademico->getPensum($this->pensum)[0]['nombre'];
							          	$Pensumc = $this->recordacademico->getCodigopropietario($this->pensum);
										$iia = $this->recordacademico->getIAAEscuelaPensumArticulado($ci, $this->ultimaEscuela, $this->ultimoperiodocursado,$Pensumc);
										$this->periodoActual = $this->periodos->getPeriodoActual();

														
							          	// if para determinar el estado actual del estudiante (depende del ultimo indice acumulado)
							          	if($this->iuc >= 16) {
					          	      		$this->Estado = "Cuadro de honor";
					          	      		$json[] = 1;
					            		}elseif($this->iuc > 0 && $this->iuc < 11 && $iia > 0 && $iia<11) {
					               			$this->Estado = "P.I.R.A.";
					               			$json[] = 2;
					            		}elseif($this->iuc == 0) {
					                		$this->Estado = "Nuevo Ingreso o Cambio de Escuela";
					                		$json[] = 3;
					            		}else{
					                		$this->Estado = "Regular";
					                		$json[] = 4;
					            		};
					            		$ciNew = number_format($ci, 0, ',' ,'.'); // formato para la cedula "23.632.347"
					            		// se guardan los datos en un arreglo $json[]
							           	$json[]= "$('#datosEstudiante').show();"; 
							           	$json[]= "$('#est_nombre').html('$this->nombrep');";
							          	$json[]= "$('#est_ci').html('$ciNew');";
							          	$json[]= "$('#est_iuc').html('$this->iuc');";
							          	$json[]= "$('#est_usemubi').html('$this->usemubi');";
							          	$json[]= "$('#est_cursadas').html('$this->cursadas');";
							          	$json[]= "$('#est_porcursar').html('$this->porCursar');";
							          	$json[]= "$('#est_uca').html('$this->uca');";
							          	$json[]= "$('#est_pensumN').html('$this->pensumN');";
							          	$json[]= "$('#est_estado').html('$this->Estado');";
							          	$json[]= "$('#est_sede').html('$this->sedeN');";
							          	$json[]= "$('#est_escuela').html('$this->ultimaEscuelaN');";

							          	/////*******************Becados*******************\\\\\
										$estudiante_becado = $this->profit->getEstudiantesBecados($ci,$this->periodoActual)[0]['co_art'];
										if ($estudiante_becado != NULL) {
											$becado = $estudiante_becado;
											$json[] = "\"$text\" = $becado.concat($estudiante_becado);";
											$json[]= "if($('#est_becado').hasClass('textRed')){
													      $('#est_becado').removeClass('textRed');
													      $('#est_becado').addClass('textBlue');
													  }else{
													  	$('#est_becado').addClass('textBlue');
													  	}"; 
											$json[]= "$('#est_becado').html('$becado');";
											$estado_beca = $this->recordacademico->EstadoBeca($ci,$this->ultimoperiodo)[0]['estado'];
											if($estado_beca == 'SE MANTIENE'){
												$json[] = "$('#beca_estado').removeClass('textRed');
														   $('#beca_estado').addClass('textBlue');" ;
											}
											if ($estado_beca == 'COMPLETO') {
												$json[] ="$('#beca_estado').removeClass('textRed');
														  $('#beca_estado').addClass('textBlue');" ;
											}
											if ($estado_beca == 'SALE') {
												$json[] = "$('#beca_estado').removeClass('textBluee');
														   $('#beca_estado').addClass('textRed');";
											}
											if ($estado_beca == 'ENTRA EN PRUEBA') {
												$json[] = "$('#beca_estado').removeClass('textBluee');
														   $('#beca_estado').addClass('textRed');";
											}
										    $json[]= "$('#beca_estado').html('$estado_beca');";
											
										}else{
											$this->no_becado = "NO PERTENECE A ESTE PROGRAMA";
											$json[]= "if($('#est_becado').hasClass('textBlue')){
													      $('#est_becado').removeClass('textBlue');
													      $('#est_becado').addClass('textRed');
												  		}else{
												  			$('#est_becado').addClass('textRed');
												  		  }"; 
											$json[]= "$('#est_becado').html('$this->no_becado');";
											$estado_beca = "N/A";
											$json[]= "if($('#beca_estado').hasClass('textBlue')){
													      $('#beca_estado').removeClass('textBlue');
													      $('#beca_estado').addClass('textRed');
												  		}else{
												  			$('#beca_estado').addClass('textRed');
												  		  }"; 
											$json[]= "$('#beca_estado').html('$estado_beca');";

										}
						         	
									$this->profit->getDatosCuentaEstudiante($ci);//conexion con profit

							       	$texto = 'Total Estudiante';

								       	$saldo = $debe= $saldo = $total_haber = $total_debe = 0; // se inicializan todas las varuables a 0

								      	$rows           = $this->profit->getDatosCuentaEstudiante($ci);
								      	$rowsnew = array();
								      	//var_dump($rows);die;
								      	foreach ($rows as $key => $value){
								      		//Reicia la variable cada vuelta para evitar que tomen un valor no deseado
								      		unset($haber); 
								      		unset($debe);
								      		
								      		if ($value["Tipo"]== 1){
								      			$tipo="COBRO";
								      			$haber = number_format($value["MontoC"],2);
								      			$saldo = $saldo - $value["MontoC"];
								      			if($saldo <0.01 and $saldo >-0.01){ 
      												$saldo=0.00;
      											}
								      			$total_haber = $total_haber + $value["MontoC"];
								      		}
								      		elseif ($value["Tipo"] == 2){
								      			$tipo = "FACTURA";
								      			$debe =  number_format($value["MontoC"],2); 
								      			$saldo = $saldo + $value["MontoC"];
								      			if($saldo <0.01 and $saldo >-0.01){ 
      												$saldo=0.00;
								      			}
								      			$total_debe = $total_debe + $value["MontoC"];
								      		}
								      		elseif($value["Tipo"] == 3){
								      			$tipo = "NOTA DE CRÉDITO";
								      			$haber = number_format($value["MontoNeto"],2);
								      			$saldo = $saldo + $value["MontoC"];
								      			if($saldo <0.01 and $saldo >-0.01){ 
      												$saldo=0.00;
								      			}
								      			$total_haber = $total_haber + $value["MontoNeto"];				      			
								      		}
								      		elseif ($value["Tipo"]== 4) {
								      			$tipo="AJUSTE POSITIVO";
								      			$debe = number_format($value["MontoC"],2);
								      			$saldo = $saldo + $value["MontoC"];
								      			if($saldo <0.01 and $saldo >-0.01){ 
      												$saldo=0.00;
								      			}
								      			$total_debe = $total_debe + $value["MontoC"];
								      		}
								      		elseif($value["Tipo"] == 5){
								      			$tipo="COBRO";
								      			$debe =  number_format($value["MontoNeto"],2);
								      			$saldo = $saldo - $value["MontoC"];
								      			if($saldo <0.01 and $saldo >-0.01){ 
      												$saldo=0.00;
								      			}
								      			$total_debe = $total_debe + $value["MontoNeto"];
								      		};
								      		$numero = $value["NroPago"];
								      		$FechaEmision = $value["FechaEmision"];
								      		$VenFactCan = $value["VenFactCan"];
								      		//ordena el arreglo para solo usar las columnas que queremos
								      		$arraytemp = array("tipo" => $tipo,
								      							"numero" => $numero,
								      							"FechaEmision" => $FechaEmision,
								      							"VenFactCan" => $VenFactCan,
								      							"debe" => $debe,
								      							"haber" => $haber,
								      							"saldo" => number_format($saldo,2)
								      		      		);
								 			array_push($rowsnew, $arraytemp);
								      	}
								      		// ultima linea (totales)
								       	$saldoNew = number_format($saldo,2);
								      	$json[]= "$('#est_Saldo').html('$saldoNew');";
								      	array_push($rowsnew, array('FechaEmision' => $texto,
								      							   'debe'  => number_format($total_debe,2),
								      							   'haber' => number_format($total_haber,2),
								      							   'saldo' => number_format($saldo,2)));
								           // Definimos las propiedades de la tabla.
								        $table = array('class' => 'tableData',
								                       'width' => '750px');
								        $columns = array(array('name'    => 'tipo',
									                           'width'   => '135px',
									                           'column'  => 'tipo',
									                           'rows'    => array('style' => 'text-align:center', 'class' => 'tipo')),
									                     array('name'    => 'numero',
									                           'width'   => '70px',
									                           'column'  => 'numero',
									                     	   'rows'    => array('style' => 'text-align:center')),
									                     array('name'    => 'FechaEmision',
									                           'width'   => '107px',
									                           'column'  => 'FechaEmision',
									                           'rows'    => array('style' => 'text-align:center')),
									                     array('name'    => 'Ven. Fact. Can',
									                           'width'   => '100px',
									                           'column'  => 'VenFactCan',
									                           'rows'    => array('style' => 'text-align:center')),
									                     array('name'    => 'debe',
									                           'width'   => '100px',
									                           'column'  => 'debe'),
									                     array('name'    => 'haber',
									                           'width'   => '100px',
									                           'column'  => 'haber'),
									                     array('name'    => 'saldo',
									                           'width'   => '100px',
									                           'column'  => 'saldo',
									                     	   'rows'    => array('style' => 'text-align:center; font-weight:bold;')));
								            //pasos para mandar el json a la vista
								            $HTML = $this->SwapBytes_Crud_List->fill($table, $rowsnew, $columns);
								            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML); 
						       	 }
						             	$this->getResponse()->setBody(Zend_Json::encode($json));
						     }
					 }
				}
	        }
}
?>
