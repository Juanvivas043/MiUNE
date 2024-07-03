<?php
/**
 * Clase que contiene una serie de metodos que permiten la integracion con el
 * framework Angular JS
 *
 * @author Jerry Martinez / Enrique Reyes / Alton Bell-Smythe 
 */
class Transactions_PagocuotasController extends Zend_Controller_Action {

    private $_Title = 'Transacciones \ Pago de Cuotas En Linea';
    // VARIABLES QUE SE USARAN PARA DESARROLLO
    private $user_default = 24042410;
    private $isProduccion = true;
    /**
     * Inicializacion de variables e instancias importantes
     * @throws Zend_Exception
     */


    public function init(){
		Zend_Loader::loadClass('CmcBytes_Filtros');
		Zend_Loader::loadClass('Une_Filtros');
		Zend_Loader::loadClass('Une_Payment');
		Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
		Zend_Loader::loadClass('Models_DbTable_Profit');
		Zend_Loader::loadClass('Models_DbTable_Usuarios');
		Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
		Zend_Loader::loadClass('Models_DbTable_Transaccionespg');
		Zend_Loader::loadClass('Models_DbTable_Inscripciones');
		Zend_Loader::loadClass('Models_DbTable_Cuotas');
		Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
		Zend_Loader::loadClass('Models_DbTable_Periodos');

    $this->Payment                  = new Une_Payment($this->isProduccion);

    $this->filtros          		    = new Une_Filtros();
		$this->CmcBytes_Filtros 		    = new CmcBytes_Filtros();
		$this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
		$this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
		$this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
		$this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
		$this->SwapBytes_Ajax           = new SwapBytes_Ajax();
		$this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
		$this->SwapBytes_Jquery         = new SwapBytes_Jquery();
    $this->SwapBytes_Angular        = new SwapBytes_Angular();
		$this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
		$this->SwapBytes_Html_Message	  = new SwapBytes_Html_Message();
		$this->transaccionpg            = new Models_DbTable_Transaccionespg();

		$this->grupo                    = new Models_DbTable_UsuariosGrupos();
		$this->profit                   = new Models_DbTable_Profit();
		$this->CmcBytes_Profit          = new CmcBytes_Profit();
		$this->usuario                  = new Models_DbTable_Usuarios();
		$this->inscripcion              = new Models_DbTable_Inscripciones();
		$this->recordacademico          = new Models_DbTable_Recordsacademicos();
		$this->periodo                  = new Models_DbTable_Periodos();
		$this->usuariogrupo             = new Models_DbTable_UsuariosGrupos();
		$this->cuota                    = new Models_DbTable_Cuotas();
		$this->authSpace                = new Zend_Session_Namespace('Zend_Auth');
    	$this->preDispatch();
     

		if($this->_getParam('action') == "reinscripcion" OR $this->_getParam('action') == "getFactDetails" OR $this->_getParam('action') == "getNumeroFact"  ){	
	     		 $this->setDatosReinscripcion($this->produccion,$this->authSpace->userId);
	     		 $this->moduloActual = "Reinscripcion";
		}else{
	      		$this->setDatosCuotas($this->produccion,$this->authSpace->userId);
	      		$this->moduloActual = "Cuotas";
		}
		//var_dump($this->datos);die;
    }

    public function setProduccion($isProduccion){

        $this->isProduccion = $isProduccion;

    }
    public function getUserDefault(){

        if($this->isProduccion){
           return   $this->authSpace->userId;
         }else{
            return   $this->user_default;
         }         
   }

   	/*
	   *	 Guardando las variables necesarias para el pago de cuotas 
   	*/

    public function setDatosCuotas(){

        $cedula = $this->getUserDefault();
        $this->noAutorizado = true;
        $sedeEstructura = $this->inscripcion->getUltimaSedeInscripcion($cedula);
        $is_nuevo_ingreso = $this->recordacademico->isNuevoIngreso($cedula);
        $this->datos = $this->usuario->getEstudiantePago($cedula,$sedeEstructura[0]["fk_estructura"],$is_nuevo_ingreso);
       //var_dump($this->datos);die; 
        if($this->datos != false  && !$this->CmcBytes_Profit->isBecado($cedula)){

          $this->datos["monto_inscripcion"] =  $this->cuota->getMontoPrimeraCuota($this->datos["fk_periodo"],$sedeEstructura[0]["fk_estructura"],$is_nuevo_ingreso);

          $this->datos["ajuste_primera_cuota"] = $this->datos["monto_inscripcion"] - $this->datos["precio_cuota"];
          if($this->datos["cuotas_vencidas"] < 1)
            $this->datos["ajuste_primera_cuota"] = 0;

          // Usando la nueva lib para conectarse a profit.
          $this->datos["saldo_actual"] = $this->profit->getSaldoEstudiante($cedula);

          $this->datos["saldo_total_vencido"] = ($this->datos["precio_cuota"] * $this->datos["cuotas_vencidas"]) + $this->datos["ajuste_primera_cuota"];

          $this->datos["saldo_cancelado"] = ($this->datos["total_pagar"] - $this->datos["saldo_actual"]) ;
          $this->datos["cuotas_pagadas"] = (int)($this->datos["total_cuotas"]-($this->datos["saldo_actual"]/$this->datos["precio_cuota"]));
          $this->datos["cuotas_no_pagadas"] = $this->datos["total_cuotas"] - $this->datos["cuotas_pagadas"];
          $this->datos["cuotas_por_pagar"] = $this->datos["cuotas_vencidas"] - $this->datos["cuotas_pagadas"];
          $this->datos["cuotas_no_vencidas"] = $this->datos["total_cuotas"] - $this->datos["cuotas_vencidas"];
          if ($this->datos["cuotas_por_pagar"] < 0 )
              $this->datos["cuotas_por_pagar"] = 0;

          $this->datos["remanente"] = (float)($this->datos["saldo_cancelado"] - (($this->datos["cuotas_pagadas"] * $this->datos["precio_cuota"]) + $this->datos["ajuste_primera_cuota"]) );

          $this->datos["pago_cuotas_vencidas"] =   ($this->datos["cuotas_por_pagar"] * $this->datos["precio_cuota"]) - $this->datos["remanente"];
          $this->datos["ultima_cuota"] = $this->cuota->getNombreCuotaCorto($this->datos["cuotas_vencidas"]);

          $first_cuota_pagar = true;
          $e = 0;
          for($i = 1; $i <= $this->datos["total_cuotas"] ; $i++ ){
              $pagado = ($i <= $this->datos["cuotas_pagadas"]) ? true:false;
              if($i <= $this->datos["cuotas_pagadas"]){

                  if($i == 1){
                      $bs = $this->datos["monto_inscripcion"];
                  }else{
                      $bs = $this->datos["precio_cuota"];
                  }
              }else{
                  if($first_cuota_pagar){
                      $bs = $this->datos["remanente"] ;
                      $first_cuota_pagar = false;
                  }else{
                      $bs = 0.00;
                  }
                  $vencida = ($e < $this->datos["cuotas_por_pagar"]) ? true:false;
                  $e++;
              }
              // Etiquetas a cada cuota
              switch ($i) {
                  case 1: $nombre = "Inscripción + Seguro"; $short = "INS"; break;
                  case 2: $nombre = "Primera Cuota"; $short = "1DA";break;
                  case 3: $nombre = "Segunda Cuota"; $short = "2RA";break;
                  case 4: $nombre = "Tercera Cuota"; $short = "3TA";break;
                  case 5: $nombre = "Cuarta Cuota"; $short = "4TA";break;
              }

              if($i == 1){
                $porpagar = $this->datos["monto_inscripcion"] - $bs;
              }else{
                $porpagar = $this->datos["precio_cuota"] - $bs;
              }
              $this->datos["Cuotas"][] = array( "bspagado" => $bs,"pagado" => $pagado,"porpagar" => $porpagar , "nombre" => $nombre,"ordinal" => $short, "vencida" => $vencida );
          }
        }else{
          $this->noAutorizado = false;
        }
    }

    /*
	   *	 Guardando las variables necesarias para el pago de reinscripcion 
   	*/
   	
  public function setDatosReinscripcion(){
        $cedula = $this->getUserDefault();
      	$this->noAutorizado = true;
        $this->inscrito = false;
        $this->noConsecutivo = false;
        $sedeEstructura = $this->inscripcion->getUltimaSedeInscripcion($cedula);
        $is_nuevo_ingreso = $this->recordacademico->isNuevoIngreso($cedula);
        $ultimo_periodo = $this->recordacademico->getUltimoPeriodocursado($cedula)[0]["fn_xrxx_reinscripcion_upc"];
        $this->datos = $this->usuario->getEstudiantePago($cedula,$sedeEstructura[0]["fk_estructura"],$is_nuevo_ingreso);
        $nuevo_periodo = $this->periodo->getMasNuevo();
        $this->reinscrito = $this->profit->VerificarPagoReins($cedula, $nuevo_periodo, $sedeEstructura[0]["fk_estructura"]);
        $this->consecutivo = $nuevo_periodo - $ultimo_periodo;
	//var_dump($nuevo_periodo, $ultimo_periodo);die;
        if ($this->consecutivo == 1) {
           // var_dump($this->datos,$this->CmcBytes_Profit->isBecado($cedula),!isset($this->reinscrito));die;
          if (!isset($this->reinscrito)) {  
            //para omicron $this->datos != false, en local en vez $this->datos == false
	//var_dump($this->usuario->getEstudiantePago($cedula,$sedeEstructura[0]["fk_estructura"],$is_nuevo_ingreso));die;
            
	      if($this->datos == false  && !$this->profit->ComprobarBecado($cedula,$ultimo_periodo,$sedeEstructura[0]["fk_estructura"])){

              $this->datos = $this->usuario->getInfoGeneral($cedula,$ultimo_periodo)[0];
              //var_dump($this->datos["sed"]);
              if ($this->datos["sed"] == "Centro"){
                $sede = "Sede Centro";
              }else{
                $sede = "Los Naranjos";
              }
              $this->datos["ultimo_periodo"] = $ultimo_periodo;
              $this->datos["nuevo_periodo"] = $nuevo_periodo;
              //var_dump($this->datos["nuevo_periodo"],$this->datos["ultimo_periodo"]);die;
              $this->datos["saldo_actual"] = $this->profit->getSaldoEstudiante($cedula);
              $this->datos["articulo_reinscribir"] = $this->profit->getArticulosByPeriodo($this->datos["nuevo_periodo"],$sede);
              foreach ($this->datos["articulo_reinscribir"] as $key => $value) {
                $this->datos["monto_reinscripcion"] += $value["monto"];
              }
              $this->datos["primera_cuota"] =  $this->cuota->getMontoPrimeraCuota($this->datos["nuevo_periodo"],$sedeEstructura[0]["fk_estructura"],$is_nuevo_ingreso);
              $this->datos["precio_cuotas"] = (($this->datos["monto_reinscripcion"] - $this->datos["primera_cuota"])/4);
              $this->datos["primera_cuota-seguro"] = $this->datos["primera_cuota"]-$this->datos["articulo_reinscribir"][0]["monto"];
              $this->datos["primera_cuota-5%"] = (($this->datos["primera_cuota-seguro"]*95)/100);
              $this->datos["primera_cuota-5%+seguro"] = $this->datos["primera_cuota-5%"] + $this->datos["articulo_reinscribir"][0]["monto"];
              $this->datos["precio_cuotas-5%"] = (($this->datos["precio_cuotas"]*95)/100);
              $this->datos["monto-5%-seguro"]= (($this->datos["articulo_reinscribir"][1]["monto"]*95)/100);
              $this->datos["monto-5%"]= bcdiv((($this->datos["articulo_reinscribir"][1]["monto"]*95)/100) + $this->datos["articulo_reinscribir"][0]["monto"], 1, 2);
              $this->datos["facturas_pendientes"] = $this->profit->getFacturasPendientes($cedula);              
            	if(false){
            		$this->noAutorizado = false;
            	}
            }else{
              $this->noAutorizado = false;
            }
          }else{
            $this->inscrito = true;
            $this->noAutorizado = false;
          }
        }else{
          $this->noConsecutivo = true;
        }
        //var_dump($this->datos);die;
   	}

    public function preDispatch() {
     if (!Zend_Auth::getInstance()->hasIdentity()) {
      $this->_helper->redirector('index', 'login', 'default');
     }

     if (!$this->grupo->haveAccessToModule()) {
       $this->_helper->redirector('accesserror', 'profile', 'default');
     }
   }
   /**
     * Action donde se paga inscripcion
     **/

    public function indexAction(){

        $this->view->title                 = $this->_Title;
        $this->view->filters               = $this->filtros;
        $this->view->datos                 = $this->datos;
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Angular     = $this->SwapBytes_Angular;
        $this->SwapBytes_Ajax_Action       = new SwapBytes_Ajax_Action();
      	$this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
      	$this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
       	$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
	      $this->view->noAutorizado = $this->noAutorizado;
    }

     /**
     * Action donde se lista todas las transacciones de el usuario
     */
    public function verAction(){

        $this->SwapBytes_Crud_Action->setDisplay(true, true, false);
        $this->SwapBytes_Crud_Action->setEnable(true, true, false);

        $btnPagarCuotas = "<button id='btnPagarCuotas' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnPagarCuotas' role='button' aria-disabled='false'>Pagar Cuotas";

        $this->SwapBytes_Crud_Action->addCustum($btnPagarCuotas);

        $this->view->title      = $this->Title;
        $this->view->filters    = $this->filtros;

        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Angular     = $this->SwapBytes_Angular;
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->view->trigger = "$('#btnList').click();";
    }

     /**
     * Action donde se lista todas las transacciones de el usuario
     */
    public function reinscripcionAction(){

        $this->SwapBytes_Crud_Action->setDisplay(true, true, false);
        $this->SwapBytes_Crud_Action->setEnable(true, true, false);
        $this->SwapBytes_Crud_Action->addCustum($btnPagarCuotas);

        $this->view->title      = "Transacciones \ Pagos \ Reinscripcion";
        $this->view->filters    = $this->filtros;

        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Angular     = $this->SwapBytes_Angular;
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->view->datos 					       = $this->datos;
        $this->view->noAutorizado          = $this->noAutorizado;
        $this->view->inscrito              = $this->inscrito;
        $this->view->noConsecutivo         = $this->noConsecutivo;
        //var_dump($this->datos["monto-5%"]);die;

        //var_dump($this->datos);die;
    }

    /**
     *  Redireccionara la peticion AJAX con el numero de control que venga por POST
     */
    public function doAction(){
        $this->Payment->redireccionar($_POST["numerocontrol"]);
       die; 
    }


    /**
     *  Para saltarnos el puerto 8443 en omicron, este 8443 se ejecutara en 14.200
     * *  @param $_GET["cuotas"] cuotas selecionadas en la vista
     */
    public function getnumeroAction(){

        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            if($this->isProduccion){

                $response = $this->doPreRegistro();
            }else{
                $response = $this->Payment->conexNoSecure( "getnumerocontrol?cuotas_seleccionadas=".$_GET["cuotas_seleccionadas"]);
            }
            // VERIFICAMOS SI NOS PODEMOS CONECTARNOS AL COMERCIO
            if(!$this->Payment->checkConection($response)){

                $response = ["message" => "La operacion no esta disponible en este momento, por favor intentalo mas tarde.",
                            "conection" => false,
                            "title" => "Operacion no disponible",
                            ];
            }else{
                $response = ["numerocontrol"    => $response,
                             "conection"        => true];
            }
            echo json_encode($response);
        }
    }

    /**
     *  Para saltarnos el puerto 8443 en omicron, este 8443 se ejecutara en 14.200, Eventualmente.
     * *  @param $_GET["optionSelected"] cuotas selecionadas en la vista
     */
    public function getnumerofactAction(){
    	$this->SwapBytes_Ajax->setHeader();
    	$response["numeroControl"] = $this->doPreRegistro();   
          
        echo json_encode($response);
        
    }

    /**
     *  Se registrata la transaccion en BD y le dara a la peticion el numero de control
     * *  @param $_GET["numerocontrol"] numero de control seleccionado
     */
    public function getstatusAction(){

        echo $this->Payment->getStatus($_GET["numerocontrol"]);
        die;    }

    /**
     *  Se registrata la transaccion en BD y le dara a la peticion el numero de control
     *  *  @param $_GET["cuotas_seleccionadas"] el numero de cuotas seleccionadas en la vista
     */

    public function doPreRegistro(){
        // AGREGAMOS A LA BD
//        $monto = ($this->datos["cuotas_no_pagadas"] == 1 ? $this->datos["saldo_actual"] : $_GET["cuotas_seleccionadas"] * $this->datos["precio_cuota"] );
  
    	$usuariogrupo = $this->usuariogrupo->getEstudiante($this->getUserDefault());

    	 if($this->_getParam('action') == "getNumeroFact"){ // PAGO DE REINSCRIPCION
    	 		$descripcion = "PAGO DE ";
    	 		$transacciones_por_realizar = $this->datos["facturas_pendientes"];
    	 		if($_GET["optionSelected"] == "one")
    	 			$transacciones_por_realizar[] = ["saldo" => $this->datos["primera_cuota"],"observa" => "INSCRIPCIÓN PER {$this->datos["nuevo_periodo"]} + SEGURO"];
    	 		if($_GET["optionSelected"] == "all") 
    	 			$transacciones_por_realizar[] = ["saldo" => $this->datos["monto-5%"],"observa" => "INSCRIPCIÓN PER {$this->datos["nuevo_periodo"]} + SEGURO + CUOTAS"];
    	 		foreach ($transacciones_por_realizar as $key => $value) {
    	 			$data = array(
		                'fk_usuariogrupo' => $usuariogrupo,
		                'fk_periodo' => $this->datos["nuevo_periodo"],
		                'cantidad' => 1,
		                'monto' => $value["saldo"],
						        'montototal' => $value["saldo"],
		                'ip' => $_SERVER["REMOTE_ADDR"],
		                'fk_atributo' => 1,
		                'fk_tipo' => 20118,
		                'descripcion' => $value["observa"],
		                'fact_num' => $value["nro_doc"]
		            );
		            $monto +=	$value["saldo"];
		            // guardamos el registro que acabamos de agregar
		            $pk_transaccionpg = $this->transaccionpg->addRow($data);
		            $pk_transaccionpgs[] = $pk_transaccionpg;
    	 		}
    	 	$factura = $this->transaccionpg->getFacturaByPk($pk_transaccionpg);
    	 //	$numerocontrol = 9696969;
	        // consultamos el numero de control

	    	$numerocontrol = $this->Payment->preRegistro($monto,$factura);
  
    	 }else{// PAGO DE CUOTAS
    	 	$cuotas_remanente = (($this->datos["saldo_actual"] - (($this->datos["cuotas_no_pagadas"]-$_GET["cuotas_seleccionadas"])*$this->datos["precio_cuota"]))/ $this->datos["precio_cuota"]);
	      	$cuotas_temp = explode(".",(string)$cuotas_remanente);
	      	$cuotas_remanente = $_GET["cuotas_seleccionadas"] - $cuotas_temp[0];
	      	$remanente_porcentaje = "0.".$cuotas_temp[1];
	      	$monto = ($this->datos["precio_cuota"] * ($_GET["cuotas_seleccionadas"] - $cuotas_remanente) + ($this->datos["precio_cuota"]*$remanente_porcentaje));
	        $adicional = 0;
	        for ($i=0; $i < $_GET["cuotas_seleccionadas"]; $i++) {

	            $cuota_actual = $this->datos["Cuotas"][$this->datos["cuotas_pagadas"]+$adicional]["ordinal"];
	            if(!empty($cuotas_des)){
	                $un = "/";
	            }
	            $cuotas_des = $cuotas_des .$un. $cuota_actual;
	            $adicional += 1;
	        }
	        $descripcion = "PAGO DE PERIODO " . $this->datos["fk_periodo"] ." " .$cuotas_des. " CUOTA(S)";
	        $factura_pendiente = $this->profit->getFacturasPendientes($this->getUserDefault())[0];
	        $data = array(
                'fk_usuariogrupo' => $usuariogrupo,
                'fk_periodo' => $this->datos["fk_periodo"],
                'cantidad' => $_GET["cuotas_seleccionadas"],
                'monto' => $this->datos["precio_cuota"],
				'montototal' => $monto,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'fk_atributo' => 1,
                'fk_tipo' => 20119,
                'descripcion' => $descripcion,
                'fact_num' => $factura_pendiente["nro_doc"]
            );


	        // guardamos el registro que acabamos de agregar
	        $pk_transaccionpg = $this->transaccionpg->addRow($data);
	        //get num factura
	        $factura = $this->transaccionpg->getFacturaByPk($pk_transaccionpg);
	        // consultamos el numero de control
	        $numerocontrol = $this->Payment->preRegistro($monto,$factura);

    	 }

        
        if($this->Payment->checkConection($numerocontrol)){
            // update con el nuevo numero de control
            if(count($pk_transaccionpgs) > 0){
            	foreach ($pk_transaccionpgs as $key => $value) {
            		$result = $this->transaccionpg->updateNumeroControl($value,$numerocontrol);
            	}
            }else{
            	  $result = $this->transaccionpg->updateNumeroControl($pk_transaccionpg,$numerocontrol);
            }
        }
         return  $numerocontrol;
    }

    public function getnumerocontrolAction(){
        echo  $this->doPreRegistro();
       die;
    }

    /**
     *  List Action de las transacciones del usuario logeado, "historial".
     */

    public function listAction(){

        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            // Obtenemos los parametros necesarios que se esta pasando por POST, y
            // los valores necesarios de las variables que se utilizaran mas adelante.
            $pageNumber  = $this->_getParam('page', 1);
            $searchData  = $this->_getParam('buscar');
            $cedula     = $this->getUserDefault();

            $itemPerPage = 15;
            $pageRange   = 10;

            // Definimos los valores
            $rows           = $this->transaccionpg->getTransacciones($cedula);
            if(count($rows)){

                // Definimos las propiedades de la tabla.
                $table = array('class' => 'tableData',
                               'width' => '800px');

                $columns = array(array('column'  => 'numerocontrol',
                                       'primary' => true,
                                       'hide'    => true),
                                 array('name'    => 'Periodo',
                                       'column'  => 'fk_periodo',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'estado',
                                    'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'estado'),
                                 array('name'    => 'tipo',
                                        'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'tipo'),
                                 array('name'    => 'fecha',
                                      'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'fecha'),
                                 array('name'    => 'hora',
                                    'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'hora'),
                                 array('name'    => 'factura',
                                    'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'factura'),
                                 array('name'    => 'cantidad',
                                    'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'cantidad'),
                                 array('name'    => 'monto',
                                    'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'monto'),
                                 array('name'    => 'monto total',
                                    'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'monto_total')
                                 );

                $other = [
                    [
                        "actionName" => "Ver",
                        "action"    => "ver(##pk##)",
                        "label"     => "Ver"
                    ]
                ];

                // Generamos la lista.
                $HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns,'O',$other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }else{
                $HTML  = $this->SwapBytes_Html_Message->alert("No existens transacciones realizadas");
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
    /**
     *  Function usada para reclcular las cuotas desde la vista.
     *
     */
    public function calcularcuotasAction() {

		if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

        	if(isset($_POST['cuotas']))
            {
                if ($_POST['cuotas'] == -1){
                    $total = number_format($this->datos["saldo_actual"],2);
                    $_POST['cuotas'] = $this->datos["cuotas_no_pagadas"]  ;
                    $monto = $this->datos["precio_cuota"];
                }else{
                    $total = $_POST['cuotas'] * $this->datos["precio_cuota"];
                    if($total != 0){
                        $total = number_format( ($total - $this->datos["remanente"]),2);
                    }
                    $monto = $this->datos["precio_cuota"];

                }
            }
            // Ajustando Remanente

            $saldo_primera_cuota = number_format(($this->datos["precio_cuota"] - $this->datos["remanente"]),2) ;
            $otras_cuotas = number_format($this->datos["precio_cuota"],2);

            $response = array(
                'total' =>number_format($total,2),
                'cantidad' =>$_POST['cuotas'],
                'monto_indiv' => number_format($monto,2),
                'table_body' => $body,
                'pago_total' => $total,
			);
           $this->getResponse()->setBody(Zend_Json::encode($response));
        }
    }

     /**
     *  Function usada para reclcular la informacion para la vista de reinscripcion.
     */
    public function getfactdetailsAction() {

    	// Hay que buscar en profit cuanto se debe en la ultima factura y el periodo desde profit.
    	$cedula = $this->getUserDefault();
    	$response["total"] = 0.0;
    	if(count($this->datos["facturas_pendientes"]) > 0){        
    		foreach ($this->datos["facturas_pendientes"] as $key => $value) {
    			$response["details"][] = ["valor"	=>	$value["observa"] ,
    								"precio" 	=>	$value["saldo"],
    								"deuda" => true];
    			$response["total"] 	+= $value["saldo"];
    		}
    	}

  		if(!empty($_GET["optionSelected"]) && $_GET["optionSelected"] != "none" && $_GET["optionSelected"] == "one"){

  			$response["details"][] = ["valor"	=>	"INSCRIPCIÓN PER {$this->datos["nuevo_periodo"]} + SEGURO"  ,"precio" 	=> $this->datos["primera_cuota"],"deuda" => false];
  			$response["total"] += $this->datos["primera_cuota"];

  		}else if($_GET["optionSelected"] == "all"){        
          
          $response["details"][] = ["valor" =>  "COSTO PERÍODO {$this->datos["nuevo_periodo"]}"  ,"precio"   => $this->datos["articulo_reinscribir"][1]["monto"],"deuda" => false, "desc" => true];
      		$response["details"][] = ["valor"	=>	"SEGURO"  ,"precio" 	=> $this->datos["articulo_reinscribir"][0]["monto"],"deuda" => false, "desc" => true];
      		$response["details"][] = ["valor"	=>	"DESCUENTO"  ,"precio" 	=> ($this->datos["articulo_reinscribir"][1]["monto"]*(-0.05)),"deuda" => false, "desc" => true];
      		$response["total"] 	+= $this->datos["monto-5%"];
          
          //$response["total"] = bcdiv($response["total"], 1, 2);  //para cortar los decimales extra
      }
    	$response["monto_reinscripcion"] = $this->datos["monto_reinscripcion"]; 
      //var_dump($response);die;
	echo json_encode($response);
    	die;	
    }

    /**
     *  Action responsable de recibir la redireccion del Payment Gateway, debe venir una variable GET llamada control
     */
    public function finishAction(){      
      $cedula = $this->getUserDefault();
      $this->view->title                 = $this->_Title;
      $this->view->filters               = $this->filtros;
      $this->view->datos                 = $this->datos;
      $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
      $this->SwapBytes_Ajax_Action       = new SwapBytes_Ajax_Action();
      $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
      $this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
    	$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
      $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
      $this->view->SwapBytes_Ajax->setView($this->view);
      $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
      $this->view->dataUser              = $this->usuario->getUsuarioDataEscuela($this->authSpace->userId);
      $ultimo_periodo                    = $this->inscripcion->getUltimoPeriodoInscripcion($cedula); 
      $this->datos["2SEGURO"] 		 = $this->profit->getCostoSeguro();

        if($this->isProduccion){
            $result = $this->Payment->getStatus($_GET["control"]);
        }else{
            $result = $this->Payment->conexNoSecure("getstatus?numerocontrol=".$_GET["control"]);
        }
        //$result = $this->Payment->getStatus($_GET["control"]); // VERIFICAR SI EL USUARIO TIENE EL CONTROL ASCOCIADO

        $cedula     = $this->getUserDefault();


        if($this->Payment->checkConection($result)){
          
            $xml = simplexml_load_string($result);
            $xml->voucher = nl2br($xml->voucher);
            if(!empty($xml->lote) && !empty($xml->referencia))
              $lot_ref = $xml->lote . "-". $xml->referencia;

            if($xml->estado == 'R'){
                $xml->estado = "Rechazado";
                $xml->color = "red";
                $atributo = 2;
            }elseif ($xml->estado == 'A') {
                $xml->estado = "Aprobada";
                $xml->color = "green";
                $atributo = 3;

            }elseif ($xml->estado == 'P') {
                $xml->estado = "Pendiente";
                $xml->color = "orange";
                $atributo = 4;
            }else{
                $atributo = 1;
            }
        $completed = $this->transaccionpg->isCompleted($cedula,$_GET["control"],$atributo);
        if($completed && $atributo <> 1 )
          $this->transaccionpg->updateEstado($cedula,$_GET["control"],$atributo);

        $datos_trans_all = $this->transaccionpg->getTransaccionesByControl($_GET["control"]);
        foreach ($datos_trans_all as $key => $datos_trans) {
        	//SE GUARDA EL LOT Y REF
	        if(empty($datos_trans["lot_ref"]) && isset($lot_ref))
	          $this->transaccionpg->updateLotRef($_GET["control"],$lot_ref);
	      	// ** REGISTRO EN PROFIT DE LA OPERACION
	      	if(!$datos_trans["reg_profit"] && $atributo == 3){
	      		$facturas_pendientes  = $this->profit->getFacturasPendientes($cedula);
		      		/*
			  	 *  SE REALIZA EL COBRO POR CADA FACTURA PENDIENTE
			  	 */
 
			foreach ($facturas_pendientes as $key => $factura_pendiente){
	      			$sedeEstructura = $this->inscripcion->getUltimaSedeInscripcion($cedula);
				    $sede = ($sedeEstructura[0]["fk_estructura"] == 7 ? "Los Naranjos":"Sede Centro");
	      			// SE CHEQUEA SI EL MONTO QUE SE PRETENDE COBRAR NO SEA MAYOR AL MONTO DE LA ULTIMA FACTURA PENDIENTE.
				if($factura_pendiente["saldo"] >= $datos_trans["montototal"] && $datos_trans["fact_num"] == $factura_pendiente["nro_doc"]){
	      				 $we_can_do_this = true;
				// SE VERIFICA SI LA TRANS NO SE HA REGISTRADO EN PROFIT Y SE REGISTRA MIENTRAS "WE CAN DO THIS"
				        if(empty($datos_trans["cob_num"])  && isset($lot_ref) && $we_can_do_this &&  $xml->estado == "Aprobada"){          
						  $monto_total = $datos_trans["montototal"];
					  	  $num_fact = $factura_pendiente["nro_doc"];
				 	  	  //$cob_num = $this->profit->realizarCobroByFact('21290353','10.01',$sede,$lot_ref,$this->datos["fk_periodo"],'41272') 
                                         
					$cob_num = $this->profit->realizarCobroByFact($cedula,$monto_total,$sede,$lot_ref,$this->datos["fk_periodo"],$num_fact);
					
					  $this->transaccionpg->updateCobNumByPk($datos_trans["pk_transaccionpg"],$cob_num);
				          $datos_trans_temp = $this->transaccionpg->getTransaccion($datos_trans["pk_transaccionpg"]);
				        }
	      			}
	      		}// end foreach facturas pendientes
	      		/* 
			  	 *  SE CREA LA FACTURA Y LUEGO SU COBRO CORRESPONDIENTE
			  	 */
				
	      		if($datos_trans["fk_tipo"] == 20118  && $datos_trans["cob_num"] == NULL && $datos_trans["fact_num"] == NULL && count($this->profit->getFacturasPendientes($cedula)) == 0 ) {
	      		 
				$sedeEstructura = $this->inscripcion->getUltimaSedeInscripcion($cedula);
				$sede = ($sedeEstructura[0]["fk_estructura"] == 7 ? "Los Naranjos":"Sede Centro");
				$periodo_nuevo = $this->inscripcion->getPeriodoNuevoAInscribir($cedula)[0]["pk_periodo"];
				$mnt_trjt = (float) substr_replace($xml->monto,".",-2,0);
				
				$this->datos = $this->profit->getArticulosByPeriodo($periodo_nuevo,$sede);				
				$mnt_5_porc = (float) number_format((($this->datos[1]["monto"]*0.95)+$this->datos["2SEGURO"]),2,".","");	
				// Definimos Resultado segun Deuda
			        if(!is_null($datos_trans_all[0]["monto"])){
                                        $deuda = (float) $datos_trans_all[0]["monto"];
                                        $result = (float) number_format(($mnt_trjt - $mnt_5_porc - $deuda),2,".","");
                                }
                                else{
                                        $result = (float) number_format(($mnt_trjt - $mnt_5_porc),2,".","");
                                }

				if($result < 0.01 && $result > -0.01){
					$result = 0;
				}
                                // Definimos si existio 5% Descuento
                                if($result == 0){
                                        $desc = true;
                                }
                                else{
                                        $desc = false;
                                }
	      			$new_factura_num = $this->profit->realizarFactura($cedula,$sede,$periodo_nuevo,$desc);
	      			$this->transaccionpg->updateFacturaByPk($datos_trans["pk_transaccionpg"],$new_factura_num);
	      			$cob_num = $this->profit->realizarCobroByFact($cedula,$datos_trans["montototal"],$sede,$lot_ref,$periodo_nuevo,$new_factura_num);
	      			$this->transaccionpg->updateCobNumByPk($datos_trans["pk_transaccionpg"],$cob_num);
	      			$datos_trans_temp = $this->transaccionpg->getTransaccion($datos_trans["pk_transaccionpg"]);
				$this->cobro = $cob_num;
              //se registra el pago
             		}
		 	if(isset($new_factura_num)){
			      $this->datos["Sede"]               = $this->inscripcion->getUltimaSedeInscripcion($cedula,$ultimo_periodo);
              		      $this->datos["Periodo"]            = $this->inscripcion->getUltimoPeriodoInscripcion($cedula);
              		      $this->datos["Escuela"]            = $this->inscripcion->getUltimaEscuelapk($cedula);
             		      $this->datos["Pensum"]             = $this->inscripcion->getPensumInscripcion($cedula,$ultimo_periodo);
			      $this->datos["cobro"]		 =
           		      $fk_usuariousuario = $this->inscripcion->getFkUsuariogrupo($cedula);
	      		      $this->inscripcion->insertInscripcion($fk_usuariousuario,$this->cobro,$this->datos["Periodo"],$this->datos["Escuela"],$this->datos["Sede"][0]["fk_estructura"],0,$this->datos["Pensum"][0]["fk_pensum"]); 

			}
	      	} 
        } // end foreach transacciones
     
	$cob_num = $datos_trans["cob_num"];
        $datos_trans = $datos_trans_temp;
        $xml->monto = substr_replace($xml->monto,".", -2, 0);
        $this->view->info = $xml;
	$this->view->cob_num = $cob_num;
        $this->view->info->cob_num 	= $datos_trans["cob_num"];
        $this->view->info->date 	= $datos_trans["fechahora_formated"];
        $this->view->transactionInfo = $this->transaccionpg->getInfoByControl($_GET["control"])[0];
        $this->view->transactionDate = $this->view->transactionInfo[0]["fechahora"];
        $this->view->transactionDescription = $this->view->transactionInfo[0]["descripcion"];
        $this->view->transactionAmount = $this->view->transactionInfo[0]["cantidad"];
        $this->view->connection = true;
        /*elseif(!$we_can_do_this){
          $this->view->message = "La operacion se realizo con exito, pero debes comunicarte con el departamento de pagos para solucionar una regularidad en tu estado de cuenta";
        }*/
        }else{
            $this->view->connection = false;
            $this->view->reloadOn = true;
            $this->view->message = "En este momento no podemos procesar su solicitud, por favor intente mas tarde.";
        }
    }

}
