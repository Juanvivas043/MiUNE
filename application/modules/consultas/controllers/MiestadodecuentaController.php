<?php
	
class Consultas_MiestadodecuentaController extends Zend_Controller_Action{

    private $Title = "Consultas / Mi estado de cuenta";

    public function init() {
                    
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

       $this->usuario 				   = new Models_DbTable_Usuarios();
       $this->grupo 				   = new Models_DbTable_UsuariosGrupos();
       $this->reinscripciones		   = new Models_DbTable_Reinscripciones();
       $this->inscripciones 		   = new Models_DbTable_Inscripciones();
       $this->recordacademico 		   = new Models_DbTable_Recordsacademicos();
       $this->profit            	   = new Models_DbTable_Profit();
       $this->sede 					   = new Models_DbView_Sedes();
       $this->escuelas 				   = new Models_DbView_Escuelas();
       $this->periodos 				   = new Models_DbTable_Periodos();
       $this->current_user 			   = new Zend_Session_Namespace('Zend_Auth');
       $this->CmcBytes_profit          = new CmcBytes_Profit();
       $this->SwapBytes_Uri            = new SwapBytes_Uri();
       $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
	   $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
	   $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
       $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
       $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
       $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
       $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
       $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
       $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
	   $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
	   
       $this->Request = Zend_Controller_Front::getInstance()->getRequest();
	    
	   $this->cedulas                  = new Zend_Session_Namespace('Zend_Auth');
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
    	// Data
    	$this->user 							= $this->usuario->getUsuario($this->current_user->userId);
     	$this->ci 								= $this->user['pk_usuario'];
       	$this->nombre 							= $this->user['primer_nombre'].' '.$this->user['segundo_nombre'];
      	$this->apellido 						= $this->user['primer_apellido'].' '.$this->user['segundo_apellido'];
      	$this->user 							= $this->usuario->getUsuario($this->current_user->userId);
        $this->ultimaSede 						= $this->inscripciones->getUltimaSede($this->ci);      
        $this->sedeN 							= $this->sede->getSedeName($this->ultimaSede);
        $this->ultimoperiodo 					= $this->recordacademico->getUltimoPeriodoInscrito($this->ci)[0]['periodo'];
        $this->ultimoperiodocursado 			= $this->recordacademico->getUltimoPeriodocursado($this->ci)[0]['fn_xrxx_reinscripcion_upc'];
        // Si el estudiante es nuevo ingreso el ultimoperiodo va a llegar NULL se lleva a 0 con un operador ternario para que no de error el query
        $this->ultimoperiodocursado 			= ($this->ultimoperiodocursado == NULL) ?  0 : $this->ultimoperiodocursado;
        $this->pensum 							= $this->inscripciones->getPensumInscripcion($this->ci,$this->ultimoperiodo)[0]['fk_pensum'];	
      	$this->ultimaEscuela 					= $this->recordacademico->getUltimaEscuelaCursada($this->ci)[0]['pk_atributo'];
      	$this->ultimaEscuelaN 					= $this->escuelas->getEscuelaName($this->ultimaEscuela);
       	$this->iuc 								= $this->reinscripciones->indicePeriodo($this->ci,$this->ultimoperiodocursado,$this->ultimaEscuela,$this->pensum)[0]['iiap'];
       	$this->usemubi 							= $this->recordacademico->getSemestreUbicacion($this->ci,$this->ultimaEscuela,$this->ultimoperiodocursado,$this->pensum)[0]['semestre'];
      	$this->uca            = $this->recordacademico->getUnidadesDeCreditoAprobadas($this->ci,$this->pensum,$this->ultimaEscuela,$this->ultimaSede)[0]["uc"];
      	$this->cursadas 						= $this->recordacademico->materiasCursadas($this->ci, $this->pensum)[0]['materias'];
      	$this->porCursar 						= $this->recordacademico->materiasPorCursar($this->ci, $this->pensum)[0]['materias'];
      	$this->pensumN 							= $this->recordacademico->getPensum($this->pensum)[0]['nombre'];
		$this->periodoActual 					= $this->periodos->getPeriodoActual();
      	$Pensumc 								= $this->recordacademico->getCodigopropietario($this->pensum);
		$iia 									= $this->recordacademico->getIAAEscuelaPensumArticulado($this->ci, $this->ultimaEscuela, $this->ultimoperiodocursado,$Pensumc);
		if($this->iuc >= 16) {
      		$this->Estado 	= "Cuadro de honor";
      		$this->class 	= "textBlue";
		}elseif($this->iuc > 0 && $this->iuc < 11 && $iia > 0 && $iia < 11) {
   			$this->Estado 	= "P.I.R.A.";
   			$this->class 	= "textRed";
		}elseif($this->iuc == 0) {
    		$this->Estado 	= "Nuevo Ingreso o Cambio de Escuela";
    		$this->class 	= "textAzul";
		}else{
    		$this->Estado 	= "Regular";
    		$this->class 	= "textGray";
		};
		// Profit
		$profit 								= $this->profit->getDatosCuentaEstudiante($this->current_user->userId);
		$rowsnew 								= array();
      	foreach ($profit as $value){
      		if ($value["Tipo"]== 1){
      			$saldo = $saldo - $value["MontoC"];
      		}
      		elseif ($value["Tipo"] == 2){
      			$saldo = $saldo + $value["MontoC"];
      		}
      		elseif($value["Tipo"] == 3){
      			$saldo = $saldo + $value["MontoC"];
      		}
      		elseif ($value["Tipo"]== 4) {
      			$saldo = $saldo + $value["MontoC"];
      		}
      		elseif($value["Tipo"] == 5){
      			$saldo = $saldo - $value["MontoC"];
      		};
      		if($saldo <0.01 and $saldo >-0.01){ 
      			$saldo=0.00;
      		}
      		$arraytemp = array("saldo" => number_format($saldo,2));
 			array_push($rowsnew, $arraytemp);
      	}
      	$this->saldo 							= $rowsnew[count($rowsnew) - 1]['saldo'];
       	// View
       	$this->view->title      				= $this->Title;
       	$this->view->ci 						= number_format($this->ci, 0, ',' ,'.');
       	$this->view->nombre 					= $this->nombre.' '.$this->apellido;
       	$this->view->iuc      					= $this->iuc;
       	$this->view->usemubi      				= $this->usemubi;
       	$this->view->cursadas      				= $this->cursadas;
       	$this->view->porCursar      			= $this->porCursar;
       	$this->view->uca      					= $this->uca;
       	$this->view->pensumN      				= $this->pensumN;
       	$this->view->Estado      				= $this->Estado;
       	$this->view->class      				= $this->class;
       	$this->view->sedeN      				= $this->sedeN;
       	$this->view->ultimaEscuelaN      		= $this->ultimaEscuelaN;
       	$this->view->saldo 						= $this->saldo;
        $this->view->filters    				= $this->filtros;
        $this->view->SwapBytes_Jquery      		= $this->SwapBytes_Jquery;
		$this->view->SwapBytes_Crud_Action 		= $this->SwapBytes_Crud_Action;
		$this->view->SwapBytes_Crud_Form   		= $this->SwapBytes_Crud_Form;
		$this->view->SwapBytes_Crud_Search		= $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        		= new SwapBytes_Ajax();
        $this->view->SwapBytes_Jquery_Ui_Form 	= new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Ajax->setView($this->view);
	}


       public function tablaAction(){
       	$texto = 'Total Estudiante';
       	if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
	       	$saldo = $debe= $saldo = $total_haber = $total_debe = 0;
	     	$ci = $this->current_user->userId;
	      	$rows           = $this->profit->getDatosCuentaEstudiante($ci);
	      	$rowsnew = array();
	      	foreach ($rows as $key => $value){
	      		unset($haber);
	      		unset($debe);
	      		
	      		if ($value["Tipo"]== 1){
	      			$tipo="COBRO";
	      			$haber = number_format($value["MontoC"],2);
	      			$saldo = $saldo - $value["MontoC"];
	      			if(($saldo <0.01) and ($saldo >-0.01)){ 
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
		                     array('name'    => 'VenFactCan',
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
	            // Generamos la lista.
	            
	            $HTML = $this->SwapBytes_Crud_List->fill($table, $rowsnew, $columns);
	            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
	            $this->getResponse()->setBody(Zend_Json::encode($json));
            }
      }
}

?>