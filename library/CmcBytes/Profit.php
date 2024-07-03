<?php

class CmcBytes_Profit {

	public function __construct() {

		Zend_Loader::loadClass('Models_DbTable_Profit');
		Zend_Loader::loadClass('Models_DbTable_Cuotas');
		Zend_Loader::loadClass('Models_DbTable_Usuarios');
		Zend_Loader::loadClass('Models_DbTable_Periodos');
		Zend_Loader::loadClass('Models_DbTable_Inscripciones');
		$this->Request = Zend_Controller_Front::getInstance()->getRequest();
		$this->SwapBytes_Uri            = new SwapBytes_Uri();
		$this->moduleName     = $this->Request->getModuleName();
		$this->controllerName = $this->Request->getControllerName();
		$this->logger = Zend_Registry::get('logger');
		$this->profit = new Models_DbTable_Profit();
		$this->cuotas = new Models_DbTable_Cuotas();
		$this->estudiante = new Models_DbTable_Usuarios();
		$this->periodo = new Models_DbTable_Periodos();
		$this->inscripcion = new Models_DbTable_Inscripciones();
	}


	public function getSolvente($cedula){

		$periodo = $this->periodo->getUltimo();
		$sede = $this->inscripcion->getUltimaSede($cedula);
		$is_becado = $this->profit->ComprobarBecado($cedula, $periodo, $sede);
		//$npago = $this->inscripcion->getPagoPeriodo($cedula,$periodo);
		//$saldo_profit = $this->profit->VerEstadoDeCuentaEstudiante($cedula,$npago);
		$sedeEstructura = $this->inscripcion->getUltimaSedeInscripcion($cedula);
		$saldo_profit = $this->profit->VerSaldoEstudiante($cedula);
		$nuevoingreso = $this->isNuevoIngreso($cedula);
		if (!$is_becado) {
					
			$saldo_solvente = $this->cuotas->getMontoNoVencido($periodo,$sedeEstructura[0]["fk_estructura"],$nuevoingreso);
			
		} else {
			if ($is_becado == $this->cuotas->getCostoPer($periodo,$sedeEstructura[0]["fk_estructura"],$nuevoingreso)/2) {
				$saldo_solvente = $this->cuotas->getMontoNoVencido($periodo,$sedeEstructura[0]["fk_estructura"],$nuevoingreso);
			} else {
				return true;
			}
		}
			
		$this->logger->log($npago,ZEND_LOG::ALERT);
		$this->logger->log($saldo_profit,ZEND_LOG::INFO);
		$this->logger->log($saldo_solvente,ZEND_LOG::ALERT);
		if ( (string) $saldo_profit < $saldo_solvente || (string) $saldo_profit == $saldo_solvente) {
			return true;
		} else {
			return false;
		}
	}

	public function isNuevoIngreso($cedula){
		$nuevoingreso = $this->estudiante->checkNuevoIngreso($cedula);
		return $nuevoingreso;
	}
	public function isBecado($cedula){

		$periodo = $this->periodo->getUltimo();
		$sede = $this->inscripcion->getUltimaSede($cedula);
		$is_becado = $this->profit->ComprobarBecado($cedula, $periodo, $sede);	
		if ($is_becado) {
			return true;
		} else {
			return false;
		}
	}

	public function getArticulosPago($cedula,$pago){

		$articulos = $this->profit->VerArticulosPago($cedula, $pago);
		$fecha = $this->profit->VerFechaArticulo($cedula, $pago);
		$up = $this->periodo->getUltimo();
		if (isset($fecha)) {
			$valid = $this->periodo->checkDateInPeriodo($fecha, $up);
		} else {
			return false;
		}
		if ($valid['valid']==true) {
			return $articulos;
		} else {
			return false;
		}  
	}

	public function getEstacionamiento($cedula,$per){

		$fecha = $this->periodo->getInicio($per);
		$articulo = $this->profit->VerEstacioanmientoPago($cedula, $fecha);
		return $articulo;
	}

	public function checkPagoSemCompleto($cedula){

		$saldo_profit = $this->profit->VerSaldoEstudiante($cedula);
		if ($saldo_profit <= 0) {
			return true;
		}else{
			return false;
		}
	}

	public function getRecibosPagos($cedula){

		$recibos =  $this->profit->verReciboDePago($cedula);
		return $recibos;
	}

	public function getReciboEspecifico($cedula,$recibo){

		$especifico = $this->profit->verReciboDePagoEspecifico($cedula, $recibo);
		return $especifico;
	}

	public function getFechaPagoNomina(){
		return $this->profit->getFechadePagoNomina();
	}

	public function getDatosPersonal($fecha_inic, $fecha_emis) {
		return $this->profit->getDatosNomina($fecha_inic, $fecha_emis);
	}
}
?>
