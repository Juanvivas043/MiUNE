<?php
	/**
	* @author Enrique Reyes / 24-08-2016 / 11:31 am.
	**/
class Transactions_RegistrodepagosController extends Zend_Controller_Action {

	private $Title = 'Transacciones \ Registro de pagos DDTI';

	public function init(){
		Zend_Loader::loadClass('Une_Filtros');
		Zend_Loader::loadClass('Models_DbTable_Profit');
		Zend_Loader::loadClass('Models_DbTable_Usuarios');
		Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
		Zend_Loader::loadClass('Models_DbTable_Periodos');
		Zend_Loader::loadClass('Forms_Registrarpago');
		Zend_Loader::loadClass('Models_DbView_Escuelas');
		Zend_Loader::loadClass('Models_DbView_Sedes');
		Zend_Loader::loadClass('Models_DbTable_Pensums');
		Zend_Loader::loadClass('Models_DbTable_Inscripciones');
		
		$this->Filtros          		= new Une_Filtros();
		$this->Periodo 					= new Models_DbTable_Periodos();
		$this->Inscripciones 			= new Models_DbTable_Inscripciones();
		$this->Escuelas 				= new Models_DbView_Escuelas();
		$this->Sedes 					= new Models_DbView_Sedes();
		$this->Pensums 					= new Models_DbTable_Pensums();
		$this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
		$this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
		$this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
		$this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
		$this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
		$this->SwapBytes_Ajax           = new SwapBytes_Ajax();
		$this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
		$this->SwapBytes_Jquery         = new SwapBytes_Jquery();
    	$this->SwapBytes_Angular        = new SwapBytes_Angular();
		$this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
		$this->SwapBytes_Uri 			= new SwapBytes_Uri();
   		$this->profit                   = new Models_DbTable_Profit();
		$this->CmcBytes_Profit          = new CmcBytes_Profit();
		$this->CmcBytes_Filtros			= new CmcBytes_Filtros();

		$this->usuarios 				= new Models_DbTable_Usuarios();
		$this->usuariosgrupos 			= new Models_DbTable_UsuariosGrupos();

		$this->form           			= new Forms_Registrarpago();

		$this->controller = Zend_Controller_Front::getInstance();
		$this->Request = Zend_Controller_Front::getInstance()->getRequest();
		/**Filtros**/
		 $this->Filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->Filtros->setDisabled(true, false, false, false, false, false, false, false, false);
        $this->Filtros->setRecursive(false, false, false, false, false, false, false, false, false);

		$this->SwapBytes_Crud_Search->setDisplay(false);
		$this->SwapBytes_Crud_Action->setDisplay(true,false);
       	$this->ci = $this->_getParam('cedula');
       	$this->periodo  = $this->_getParam('periodo');

       	$this->SwapBytes_Jquery->endLine(true);
	}

  	function preDispatch() {
	        if(!Zend_Auth::getInstance()->hasIdentity()) {
	            $this->_helper->redirector('index', 'login', 'default');
	        }

	        if(!$this->usuariosgrupos->haveAccessToModule()) {
	            $this->_helper->redirector('accesserror', 'profile', 'default');
	        }
	}

	public function indexAction() {
		$this->view->title                 		= $this->Title;
        $this->view->filters               		= $this->Filtros;
        $this->view->SwapBytes_Jquery      		= $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Angular     		= $this->SwapBytes_Angular;
        $this->SwapBytes_Ajax_Action       		= $this->SwapBytes_Ajax_Action;
      	$this->view->SwapBytes_Crud_Action 		= $this->SwapBytes_Crud_Action;
      	$this->view->SwapBytes_Crud_Form   		= $this->SwapBytes_Crud_Form;
       	$this->view->SwapBytes_Crud_Search 		= $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Jquery_Ui_Form 	= $this->SwapBytes_Jquery_Ui_Form;
		$this->view->SwapBytes_Ajax        		= $this->SwapBytes_Ajax;
		$this->view->form 						= $this->form;
		$periodos 								= $this->Periodo->getSelect();
  	     		
		$this->SwapBytes_Crud_Form->setProperties($this->view->form, [], 'Enrique');

        $this->SwapBytes_Crud_Form->fillSelectBox('periodo', $periodos, 'pk_periodo', 'nombre'); 
        
		$this->view->SwapBytes_Ajax->setView($this->view);
    }
    public function verificarAction(){
    	if ($this->_request->isXmlHttpRequest()) {
    		$this->SwapBytes_Ajax->setHeader(); 
    		///reiniciar valores
    		$json = $this->clearForm();

  		   	$nombreEstudiante = $this->usuarios->getUsuario($this->ci)['primer_nombre'].' '.$this->usuarios->getUsuario($this->ci)['segundo_nombre'].' '. $this->usuarios->getUsuario($this->ci)['primer_apellido'].' '.$this->usuarios->getUsuario($this->ci)['segundo_apellido'] ;
		   	if (empty($this->ci)){
		   		$json[]= "$('#cedula').removeClass('valid novalid').addClass('invalid');
		   					  $('#nombreEstudiante').val('');";
		   	}else{
		   		if($this->usuarios->getUsuario($this->ci) == NULL){
		   			$json[]= "$('#cedula').removeClass('valid novalid').addClass('invalid');
		   					  $('#nombreEstudiante').val('');";
		   		}elseif($this->usuariosgrupos->getEstudiante($this->ci)){
		   			
		   			//////////////////Si el estudiante existe\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

		   			///////////// se habilian los filtros e inputs\\\\\\\\\\\\\\\\\
		   			$json[] = $this->SwapBytes_Jquery->removeAttr('sede', 'disabled');
					$json[] = $this->SwapBytes_Jquery->removeAttr('escuela', 'disabled');
					$json[] = $this->SwapBytes_Jquery->removeAttr('pensum', 'disabled');
					$json[] = $this->SwapBytes_Jquery->removeAttr('numPago', 'disabled');
					$json[] = $this->SwapBytes_Jquery->removeAttr('UCA', 'disabled');
    		
		   			
	   				$numCob = $this->profit->getCobNum($this->ci,$this->periodo);//consulta a profit para saber si existe el pago en profit
	   				$datosInscripcion = $this->Inscripciones->getInscripcion($this->ci,$this->periodo)[0];//consulta a MiUNE para saber si existe una inscripcion o pago
		   			
		   			if ($datosInscripcion != NULL) {
		   				////////////// Si el pago existe en la base de datos de MiUNE\\\\\\\\\\\\\\\\\\\\\\\\\\\
		   			
		   			///////////////Se hacen llamados a la base de datos con lo que se necesita\\\\\\\\\\\\\\\\\\\
		   				$sede = $this->Inscripciones->getInscripcion($this->ci,$this->periodo)[0]['fk_estructura'];
		   				$numeropago = $this->Inscripciones->getInscripcion($this->ci,$this->periodo)[0]['numeropago'];
    					$escuela = $this->Inscripciones->getInscripcion($this->ci,$this->periodo)[0]['fk_atributo'];
    					$UCA = $this->Inscripciones->getInscripcion($this->ci,$this->periodo)[0]['ucadicionales'];
    					$pensum = $this->Inscripciones->getInscripcion($this->ci,$this->periodo)[0]['fk_pensum'];


    					$json[] = $this->SwapBytes_Jquery->fillSelectByArray('sede',$this->Sedes->get(),'pk_estructura','nombre');
		  				$json[] = $this->SwapBytes_Jquery->fillSelectByArray('escuela',$this->Escuelas->getEscuelasBySede($sede),'pk_atributo','escuela');
		   				$json[] = $this->SwapBytes_Jquery->fillSelectByArray('pensum', $this->Pensums->getPensums($escuela), 'pk_pensum', 'nombre');

		   				/////////////// Se agregan los valores de la base de datos a los imputs (#pago y UCA)\\\\\\\\\\\
		   				$json[] = $this->SwapBytes_Jquery->setVal(numPago, $numeropago);
		   				$json[] = $this->SwapBytes_Jquery->setVal(UCA, $UCA);
		   				////////////// Se hace el focus para los selects\\\\\\\\\\\\\\\\\\\\\
		   				$json[] = $this->SwapBytes_Jquery->setVal(sede, $sede);
		   				$json[] = $this->SwapBytes_Jquery->setVal(escuela, $escuela);
		   				$json[] = $this->SwapBytes_Jquery->setVal(pensum, $pensum);
		   				//////////////// Se habilitan los botones (Modificar y Eliminar)\\\\\\\\\\\\\\\\\\\\\\\\\

		   				$json[] = $this->SwapBytes_Jquery->removeAttr('Modificar', 'disabled');
			   			$json[] = $this->SwapBytes_Jquery->removeAttr('Eliminar', 'disabled');
			   			$json[] = "$('#Modificar').removeClass('disabled');
					  			   $('#Eliminar').removeClass('disabled');";


   					}
   					
   					elseif (isset($numCob) && count($datosInscripcion) == NULL) {
   						
   						////////////////Si el estudiante tiene pago en profit entra aca\\\\\\\\\\\\\
   						
   						//////////////llamados a MiUNE para tomar la ultima inscripciones\\\\\\\\\\\\
    					$this->sede = $this->Inscripciones->getUiltimaInfoEstudiante($this->ci)[0]['pk_estructura'];
    					$this->pensum = $this->Inscripciones->getUiltimaInfoEstudiante($this->ci)[0]['pk_pensum'];
    					$this->escuela = $this->Inscripciones->getUiltimaInfoEstudiante($this->ci)[0]['pk_atributo'];

    					////////////// se llenan los filtos con los datos de la ultima inscripcion \\\\\\\\\\\\\\\\	
		   				$json[] = $this->SwapBytes_Jquery->fillSelectByArray('sede',$this->Sedes->get(),'pk_estructura','nombre');
		  				$json[] = $this->SwapBytes_Jquery->fillSelectByArray('escuela',$this->Escuelas->getEscuelasBySede($this->sede),'pk_atributo','escuela');
		   				$json[] = $this->SwapBytes_Jquery->fillSelectByArray('pensum', $this->Pensums->getPensums($this->escuela), 'pk_pensum', 'nombre');

			   			////////////// Se hace el focus para los selects\\\\\\\\\\\\\\\\\\\\\
		   				$json[] = $this->SwapBytes_Jquery->setVal(sede, $this->sede);
		   				$json[] = $this->SwapBytes_Jquery->setVal(escuela, $this->escuela);
		   				$json[] = $this->SwapBytes_Jquery->setVal(pensum, $pensum);

		   				$fechaInicio = $this->Periodo->getInicio($this->periodo);
		   				// se modifica el formato de la fecha para que este como la de profit 
		   				$date = new DateTime($fechaInicio);
		   				$fecha = date($fechaInicio, "M j Y");
		   				$getUC = $this->profit->getUc($this->ci,$this->periodo,$fecha);
						$getUC = (int)$getUC;
		   				if(isset($getUC)){
			   					$json[] = $this->SwapBytes_Jquery->setVal('UCA', $getUC);
			   				}	
			   				$json[] = $this->SwapBytes_Jquery->setVal('numPago', $numCob);
			   				///////// Se habilita el boton de agregar para registrar el pago en tbl_inscripciones\\\\\\\
			   				$json[] = $this->SwapBytes_Jquery->removeAttr('Agregar', 'disabled');
			   				$json[] = "$('#Agregar').removeClass('disabled');";

   						
   					}else{
		   				//No hay pago registrado
		   				$this->sede = $this->Inscripciones->getUiltimaInfoEstudiante($this->ci)[0]['pk_estructura'];
    					$this->pensum = $this->Inscripciones->getUiltimaInfoEstudiante($this->ci)[0]['pk_pensum'];
    					$this->escuela = $this->Inscripciones->getUiltimaInfoEstudiante($this->ci)[0]['pk_atributo'];

		   				
			   			$json[] = $this->SwapBytes_Jquery->setVal(sede, $this->sede);
		   				$json[] = $this->SwapBytes_Jquery->setVal(escuela, $this->escuela);
		   				$json[] = $this->SwapBytes_Jquery->setVal(pensum, $pensum);

		   			$json[] = $this->SwapBytes_Jquery->fillSelectByArray('sede',$this->Sedes->get(),'pk_estructura','nombre');
		
		   			$json[] = $this->SwapBytes_Jquery->fillSelectByArray('escuela',$this->Escuelas->getEscuelasBySede($this->sede),'pk_atributo','escuela');
		   			$json[] = $this->SwapBytes_Jquery->fillSelectByArray('pensum', $this->Pensums->getPensums($this->escuela), 'pk_pensum', 'nombre');
		   			
		   			$json[] = $this->SwapBytes_Jquery->setVal(sede, $this->sede);
		   			$json[] = $this->SwapBytes_Jquery->setVal(escuela, $this->escuela);
		   			$json[] = $this->SwapBytes_Jquery->setVal(pensum, $pensum);

		   				$json[] = $this->SwapBytes_Jquery->removeAttr('Agregar', 'disabled');
		   				$json[] = "$('#Agregar').removeClass('disabled');";
		   				
		   			}
		   			//////////// se agrega el nombre del estudiante \\\\\\\\\\\\\\\\\
		   			$json[]= "$('#cedula').removeClass('invalid novalid').addClass('valid');
		   					  $('#nombreEstudiante').val('$nombreEstudiante');";
		   		}else{
		   			$json[]= "$('#cedula').removeClass('invalid valid').addClass('novalid');
		   					  $('#nombreEstudiante').val('');";
		   		}
		   	}


		$this->getResponse()->setBody(Zend_Json::encode($json));


		       	
    	}
	}
	public function filtoescuelaAction(){
		if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json[] = $this->SwapBytes_Jquery->setHtml('escuela', '');
          	$params  = $this->_getParam('sede');


			$json[] = $this->SwapBytes_Jquery->fillSelectByArray('escuela',$this->Escuelas->getEscuelasBySede($params),'pk_atributo','escuela');
			$this->getResponse()->setBody(Zend_Json::encode($json));
		}
	}
	public function filtopensumAction(){
		if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
    		$json[] = $this->SwapBytes_Jquery->setHtml('pensum', '');
      		$params  = $this->_getParam('escuela');
   
			$json[] = $this->SwapBytes_Jquery->fillSelectByArray('pensum', $this->Pensums->getPensums($params), 'pk_pensum', 'nombre');
			$this->getResponse()->setBody(Zend_Json::encode($json));
		}
	}
	public function agregarAction(){
		if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $datos = $this->_getAllParams();
            if ($datos['numeropago'] != 0) {
            

            	$fk_usuariogrupo = $this->Inscripciones->getFkUsuariogrupo($datos['cedula']);

          		$this->Inscripciones->insertInscripcion($fk_usuariogrupo,$datos['numeropago'],$datos['periodo'],$datos['escuela'],$datos['sede'],$datos['UCA'],$datos['pensum']);

          		$json = $this->clearForm();
       		}else{
       	 		$json[] = "$('#numPago').addClass('invalid');";

      		 }
           $this->getResponse()->setBody(Zend_Json::encode($json));
       }
        
	}
	public function updateAction(){
		if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $datos = $this->_getAllParams();

           $this->Inscripciones->updateInscripcion($datos['cedula'],$datos['periodo'],$datos['numeropago'],$datos['UCA'],$datos['sede'],$datos['escuela'],$datos['pensum']);

           $json = $this->clearForm();
           $this->getResponse()->setBody(Zend_Json::encode($json));
        }
	}
	public function deleteAction(){
		if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $datos = $this->_getAllParams();

           $this->Inscripciones->deleteInscripcion($datos['cedula'],$datos['periodo']);

           $json = $this->clearForm();
           $this->getResponse()->setBody(Zend_Json::encode($json));
        }
	}
	// Funcion que se encarga de reiniciar todos los campos luego de alguna modificacion (agregar,modificar,eliminar, o cambiar la cedula)
	function clearForm(){
			$json[] = $this->SwapBytes_Jquery->setVal('UCA', 0);
    		$json[] = $this->SwapBytes_Jquery->setVal('numPago', 0);
    		$json[] = $this->SwapBytes_Jquery->setAttr(Agregar, disabled, true);
    		$json[] = $this->SwapBytes_Jquery->setAttr(Modificar, disabled, true);
		   	$json[] = $this->SwapBytes_Jquery->setAttr(Eliminar, disabled,true);
    		$json[] = $this->SwapBytes_Jquery->setHtml('sede', '');
    		$json[] = $this->SwapBytes_Jquery->setHtml('escuela', '');
    		$json[] = $this->SwapBytes_Jquery->setHtml('pensum', '');
    		$json[] = $this->SwapBytes_Jquery->setAttr(sede, disabled, true);
			$json[] = $this->SwapBytes_Jquery->setAttr(escuela,disabled, true);
			$json[] = $this->SwapBytes_Jquery->setAttr(pensum, disabled, true);
			$json[] = $this->SwapBytes_Jquery->setAttr(numPago, disabled, true);
			$json[] = $this->SwapBytes_Jquery->setAttr(UCA, disabled, true);
			$json[] = $this->SwapBytes_Jquery->setVal('nombreEstudiante', '');
			$json[] = "$('#Agregar').addClass('disabled');
					   $('#Modificar').addClass('disabled');
					   $('#Eliminar').addClass('disabled');
					   $('#numPago').removeClass('invalid');";

		return $json;
	}
}
