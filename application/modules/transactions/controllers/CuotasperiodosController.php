<?php
	/**
 	* User: Carlos Rivero Theoktisto 
 	* Date: fecha
 	* Time: hora 
 	* @author kioskito
	**/
class Transactions_CuotasperiodosController extends Zend_Controller_Action 
{

	private $Title = 'Transacciones \ Cuotas Periodo';

	public function init()
	{
		Zend_Loader::loadClass('Une_Filtros');
		Zend_Loader::loadClass('Forms_Cuotasperiodo');
		Zend_Loader::loadClass('Models_DbTable_Periodos');
		Zend_Loader::loadClass('Models_DbView_Sedes');
		Zend_Loader::loadClass('Models_DbTable_Cuotas');

		$this->Filtros          		= new Une_Filtros();
		$this->form           			= new Forms_Cuotasperiodo();

		$this->Periodo 					= new Models_DbTable_Periodos();
		$this->Sedes 					= new Models_DbView_Sedes();
		$this->Cuotas 					= new Models_DbTable_Cuotas();

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
		$this->CmcBytes_Filtros			= new CmcBytes_Filtros();

		
		$this->controller = Zend_Controller_Front::getInstance();
		$this->Request = Zend_Controller_Front::getInstance()->getRequest();


		/**Filtros**/
		$this->Filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->Filtros->setDisabled(true, false, false, false, false, false, false, false, false);
        $this->Filtros->setRecursive(false, false, false, false, false, false, false, false, false);

		$this->SwapBytes_Crud_Search->setDisplay(false);
		$this->SwapBytes_Crud_Action->setDisplay(true,false);

       	$this->periodo  = $this->_getParam('periodo');
       	$this->sede = $this->_getParam('sede');
       	$this->nuevoingreso = $this->_getParam('NuevoIngreso');
       	$this->montocuota = (float)$this->_getParam('montocuota');
       	$this->montocuotaNew = (float)$this->_getParam('montocuotaNew');
       	$this->montoinscri = (float)$this->_getParam('montoinscri');
       	$this->montoinscriNew = (float)$this->_getParam('montoinscriNew');

       	$this->SwapBytes_Jquery->endLine(true);
	}

  	/*function preDispatch() 
  	{
	        if(!Zend_Auth::getInstance()->hasIdentity()) 
	        {
	            $this->_helper->redirector('index', 'login', 'default');
	        }

	        if(!$this->usuariosgrupos->haveAccessToModule()) 
	        {
	            $this->_helper->redirector('index', 'inicio', 'default');
	        }
	}*/

	public function indexAction() 
	{
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
		$sede                                   = $this->Sedes->get();
		$this->nuevoingreso 					= $this->Cuotas->getNuevoIngreso();
  	

		$this->SwapBytes_Crud_Form->setProperties($this->view->form, [], 'Kioskito'); 

        $this->SwapBytes_Crud_Form->fillSelectBox('periodo', $periodos, 'pk_periodo', 'nombre'); 
        $this->SwapBytes_Crud_Form->fillSelectBox('sede', $sede, 'pk_estructura', 'nombre'); 
        $this->SwapBytes_Crud_Form->fillSelectBox('NuevoIngreso', $this->nuevoingreso, 'pk_nuevoingreso', 'nombre'); 
         

		$this->view->SwapBytes_Ajax->setView($this->view);
        }			

    public function verificarAction()
    {

    	if ($this->_request->isXmlHttpRequest()) 
    	{
    		$this->SwapBytes_Ajax->setHeader(); 

    		///reiniciar valores
    		$json = $this->clearForm(); 			
		  			///////////// se habilian los filtros e inputs\\\\\\\\\\\\\\\\\
			$this->fk_cuota = $this->Cuotas->getFkCuota();
			
			foreach ($this->fk_cuota as $key => $value) 
			{
				$fk_cuota.="".$this->fk_cuota[$key]['fk_cuota'].",";
			}
			$fk_cuota= trim($fk_cuota,',');
			//var_dump($this->nuevoingreso);die;
			//var_dump($fk_cuota);die;

				if($this->nuevoingreso == '0'){
						$nuevoingreso = 'True, False';

				};
				if($this->nuevoingreso == '1'){
						$nuevoingreso = 'True';
				};
				if ($this->nuevoingreso == '2'){
						$nuevoingreso = 'False';
				}; 		//var_dump($this->nuevoingreso);die; 
						   			   											
			$datosPeriodoCuotas = $this->Cuotas->getPeriodosCuotas($this->sede,$this->periodo,$nuevoingreso,$fk_cuota);
				   		//	var_dump($datosPeriodoCuotas);die;
			   			
//var_dump($datosPeriodoCuotas);die;
		   	if ($datosPeriodoCuotas != NULL)
		   	{		   			
		   			$json[] = $this->SwapBytes_Jquery->setAttr(NuevoIngreso, disabled, true);
		   			$json[] = $this->SwapBytes_Jquery->setAttr(periodo, disabled, true);
					$json[] = $this->SwapBytes_Jquery->setAttr(sede, disabled, true);

		   			///////////////Se hacen llamados a la base de datos con lo que se necesita\\\\\\\\\\\\\\\\\\\

		 			if ($this->nuevoingreso == 0) 
		 			{
	 					$json[] = $this->SwapBytes_Jquery->removeAttr('montoinscri', 'disabled');
						$json[] = $this->SwapBytes_Jquery->removeAttr('montocuota', 'disabled');
						$json[] = $this->SwapBytes_Jquery->removeAttr('montoinscriNew', 'disabled');
						$json[] = $this->SwapBytes_Jquery->removeAttr('montocuotaNew', 'disabled');

						///////////////////////////////////////////////////////////////////////////////////
						$montoinscri = $this->Cuotas->getCostoCuotas($this->sede,$this->periodo,'False',$fk_cuota)[0]['costo'];
						$montocuota = $this->Cuotas->getCostoCuotas($this->sede,$this->periodo,'False',$fk_cuota)[1]['costo'];
						$montoinscriNew = $this->Cuotas->getCostoCuotas($this->sede,$this->periodo,'True',$fk_cuota)[0]['costo'];
						$montocuotaNew = $this->Cuotas->getCostoCuotas($this->sede,$this->periodo,'True',$fk_cuota)[1]['costo'];
	   				
	   					/////////////// Se agregan los valores de la base de datos a los imputs (#montocuota y montoinscri)\\\\\\\\\\\
						$json[] = $this->SwapBytes_Jquery->setVal(montocuota, $montocuota);
		   				$json[] = $this->SwapBytes_Jquery->setVal(montoinscri, $montoinscri);
		   				$json[] = $this->SwapBytes_Jquery->setVal(montocuotaNew, $montocuotaNew);
		   				$json[] = $this->SwapBytes_Jquery->setVal(montoinscriNew, $montoinscriNew);	
		   						   					 
		 			}
					if ($this->nuevoingreso == 1) 
					{
						$json[] = $this->SwapBytes_Jquery->removeAttr('montoinscriNew', 'disabled');
						$json[] = $this->SwapBytes_Jquery->removeAttr('montocuotaNew', 'disabled');
						//var_dump($montoinscriNew,$montocuotaNew);die;

						///////////////////////////////////////////////////////////////////////////////////
						$montoinscriNew = $this->Cuotas->getCostoCuotas($this->sede,$this->periodo,'True',$fk_cuota)[0]['costo'];
						$montocuotaNew = $this->Cuotas->getCostoCuotas($this->sede,$this->periodo,'True',$fk_cuota)[1]['costo'];

	   					/////////////// Se agregan los valores de la base de datos a los imputs (#montocuota y montoinscri)\\\\\\\\\\\
		   				$json[] = $this->SwapBytes_Jquery->setVal(montocuotaNew, $montocuotaNew);
		   				$json[] = $this->SwapBytes_Jquery->setVal(montoinscriNew, $montoinscriNew);	
					}
					if ($this->nuevoingreso == 2) 
					{
	 					$json[] = $this->SwapBytes_Jquery->removeAttr('montoinscri', 'disabled');
						$json[] = $this->SwapBytes_Jquery->removeAttr('montocuota', 'disabled');

						///////////////////////////////////////////////////////////////////////////////////
						$montoinscri = $this->Cuotas->getCostoCuotas($this->sede,$this->periodo,'False',$fk_cuota)[0]['costo'];
						$montocuota = $this->Cuotas->getCostoCuotas($this->sede,$this->periodo,'False',$fk_cuota)[1]['costo'];

	   					/////////////// Se agregan los valores de la base de datos a los imputs (#montocuota y montoinscri)\\\\\\\\\\\
						$json[] = $this->SwapBytes_Jquery->setVal(montocuota, $montocuota);
		   				$json[] = $this->SwapBytes_Jquery->setVal(montoinscri, $montoinscri);		 		   										
					}
	   				//////////////// Se habilitan los botones (Modificar )\\\\\\\\\\\\\\\\\\\\\\\\\

	   				$json[] = $this->SwapBytes_Jquery->removeAttr('Modificar', 'disabled');
		   			$json[] = "$('#Modificar').removeClass('disabled');";
   			}	
   					
   			elseif ($datosPeriodoCuotas == NULL)
   			{						
		   			$json[] = $this->SwapBytes_Jquery->setAttr(NuevoIngreso, disabled, true);
		   			$json[] = $this->SwapBytes_Jquery->setAttr(periodo, disabled, true);
					$json[] = $this->SwapBytes_Jquery->setAttr(sede, disabled, true);

   				//var_dump($datosPeriodoCuotas);die;		
		 			if ($this->nuevoingreso == 0) 
		 			{
	 					$json[] = $this->SwapBytes_Jquery->removeAttr('montoinscri', 'disabled');
						$json[] = $this->SwapBytes_Jquery->removeAttr('montocuota', 'disabled');
						$json[] = $this->SwapBytes_Jquery->removeAttr('montoinscriNew', 'disabled');
						$json[] = $this->SwapBytes_Jquery->removeAttr('montocuotaNew', 'disabled');
						/////////////// Se agregan los valores de la base de datos a los imputs (#montocuota y montoinscri)\\\\\\\\\\\
						$json[] = $this->SwapBytes_Jquery->setVal('montocuota', 0);
		   				$json[] = $this->SwapBytes_Jquery->setVal('montoinscri', 0);
		   				$json[] = $this->SwapBytes_Jquery->setVal('montocuotaNew', 0);
		   				$json[] = $this->SwapBytes_Jquery->setVal('montoinscriNew', 0);	
			   			///////// Se habilita el boton de agregar para registrar el pago en tbl_inscripciones\\\\\\\
			   			$json[] = $this->SwapBytes_Jquery->removeAttr('Agregar', 'disabled');
			   			$json[] = "$('#Agregar').removeClass('disabled');";	
		 			}

					if ($this->nuevoingreso == 1) 
					{
						$json[] = $this->SwapBytes_Jquery->removeAttr('montoinscriNew', 'disabled');
						$json[] = $this->SwapBytes_Jquery->removeAttr('montocuotaNew', 'disabled');
	   					/////////////// Se agregan los valores de la base de datos a los imputs (#montocuota y montoinscri)\\\\\\\\\\\
		   				$json[] = $this->SwapBytes_Jquery->setVal('montocuotaNew', 0);
		   				$json[] = $this->SwapBytes_Jquery->setVal('montoinscriNew', 0);
			   			///////// Se habilita el boton de agregar para registrar el pago en tbl_inscripciones\\\\\\\
			   			$json[] = $this->SwapBytes_Jquery->removeAttr('Agregar', 'disabled');
			   			$json[] = "$('#Agregar').removeClass('disabled');";		   		
					}

					if ($this->nuevoingreso == 2) 
					{
	 					$json[] = $this->SwapBytes_Jquery->removeAttr('montoinscri', 'disabled');
						$json[] = $this->SwapBytes_Jquery->removeAttr('montocuota', 'disabled');
	   					/////////////// Se agregan los valores de la base de datos a los imputs (#montocuota y montoinscri)\\\\\\\\\\\
						$json[] = $this->SwapBytes_Jquery->setVal('montocuota', 0);
		   				$json[] = $this->SwapBytes_Jquery->setVal('montoinscri', 0);
		 		   			///////// Se habilita el boton de agregar para registrar el pago en tbl_inscripciones\\\\\\\
			   			$json[] = $this->SwapBytes_Jquery->removeAttr('Agregar', 'disabled');
			   			$json[] = "$('#Agregar').removeClass('disabled');";	
   					}
   			}
   			else
   			{
		   		$json[] = $this->SwapBytes_Jquery->fillSelectByArray('sede',$this->Sedes->get(),'pk_estructura','nombre');
		   		$json[] = $this->SwapBytes_Jquery->setVal(sede, $this->sede);
		
		   		$json[] = $this->SwapBytes_Jquery->setVal(periodo,$periodos);
		   		$json[] = $this->SwapBytes_Jquery->removeAttr('Agregar', 'disabled');
		   		$json[] = "$('#Agregar').removeClass('disabled');";		  
		   	}
		}
		   
		$this->getResponse()->setBody(Zend_Json::encode($json));
	}

	public function agregarAction()
	{

		if ($this->_request->isXmlHttpRequest()) 
		{
            $this->SwapBytes_Ajax->setHeader();

        		if($this->nuevoingreso == '0')
        		{
					$nuevoingreso = 'True, False';	
				};
				if($this->nuevoingreso == '1')
				{
					$nuevoingreso = 'True';
				};
				if ($this->nuevoingreso == '2')
				{
					$nuevoingreso = 'False';
				}; 		

			$this->montocuota=number_format($this->montocuota,2,".","");
			$this->montocuotaNew=number_format($this->montocuotaNew,2,".","");
			$this->montoinscri=number_format($this->montoinscri,2,".","");
			$this->montoinscriNew=number_format($this->montoinscriNew,2,".","");

	        if ($this->nuevoingreso == '0') 
	        {	   		
	        	$validacion=0;
	        	    if ($this->montocuota  == 0)
	          		{
	          			$json[] = "$('#montocuota').addClass('invalid');";
	          			$validacion=1;
	          		}
	          		if ($this->montocuotaNew == 0 )
	          		{
	          			$json[] = "$('#montocuotaNew').addClass('invalid');";
	          			$validacion=1;
	          		}
					if ($this->montoinscri == 0 )
	          		{
	          			$json[] = "$('#montoinscri').addClass('invalid');";
	          			$validacion=1;
	          		}
	          		if ($this->montoinscriNew == 0 )
	          		{
	          			$json[] = "$('#montoinscriNew').addClass('invalid');";
	          			$validacion=1;
	          		} 
		          		if ($validacion == 0) 
		          		{
		          			$this->Cuotas->insertPeriodosCuotasAll($this->periodo,$this->montocuota,$this->montocuotaNew,$this->montoinscri,$this->montoinscriNew,$this->sede);
		          		}
		          		else {
		          			$json[]= "alert(' No puede agregar ninguna cuota si el valor esta en 0 ')";
		          		}
	   		} 
	   		if ($this->nuevoingreso == '1') 
	   		{	 	    
	       	 	$validacion=0;
		       	 	

		          		if ($this->montocuotaNew == 0 )
		          		{
		          			$json[] = "$('#montocuotaNew').addClass('invalid');";
		          			$validacion=1;
		          		}
		          		if ($this->montoinscriNew == 0 )
		          		{
		          			
		          			$json[] = "$('#montoinscriNew').addClass('invalid');";
		          			$validacion=1;
		          		} 	

		        if ($validacion == 0) 
          		{   
          			$this->Cuotas->insertPeriodosCuotasTrue($this->periodo,$this->montocuotaNew,$this->montoinscriNew,$this->sede);    
          		}
          		else 
          		{
          			$json[]= "alert(' No puede agregar ninguna cuota si el valor esta en 0 ')";
          		}	
		    }          	

		    if ($this->nuevoingreso == '2') 
		    {   
		    	$validacion=0;
			        if ($this->montocuota  == 0)
	          		{
	          			$json[] = "$('#montocuota').addClass('invalid');";
	          			$validacion=1;
	          		}      
					if ($this->montoinscri == 0 )
	          		{
	          			$json[] = "$('#montoinscri').addClass('invalid');";
	          			$validacion=1;
	          		}

          		if ($validacion == 0) 
          		{   
          			$this->Cuotas->insertPeriodosCuotasFalse($this->periodo,$this->montocuota,$this->montoinscri,$this->sede);    
          		}
          		else 
          		{
          			$json[]= "alert(' No puede agregar ninguna cuota si el valor esta en 0 ')";
          		}
          	}          		
  		}
      		$json = $this->clearForm();
       		$this->getResponse()->setBody(Zend_Json::encode($json));
    }  
	

	public function updateAction()
	{
		if ($this->_request->isXmlHttpRequest()) 
		{
           $this->SwapBytes_Ajax->setHeader();
  			$this->periodo = $this->_getParam('periodo',0);
			$this->montocuotaNew = $this->_getParam('montocuotaNew');
			$this->montocuota = $this->_getParam('montocuota');
			$this->montoinscriNew = $this->_getParam('montoinscriNew');
			$this->montoinscri = $this->_getParam('montoinscri');
			$this->sede = $this->_getParam('sede');
			$this->nuevoingreso = $this->_getParam('NuevoIngreso');

			if ($this->nuevoingreso == 0) 
			{
				$this->Cuotas->updatePeriodosCuotas($this->periodo,$this->montocuota,$this->sede,$this->montoinscri); 
				$this->Cuotas->updatePeriodosCuotasNI($this->periodo,$this->montocuotaNew,$this->sede,$this->montoinscriNew); 
			}
			if ($this->nuevoingreso == 1) 
			{
				$this->Cuotas->updatePeriodosCuotasNI($this->periodo,$this->montocuotaNew,$this->sede,$this->montoinscriNew); 
			}
			if ($this->nuevoingreso == 2) 
			{
				$this->Cuotas->updatePeriodosCuotas($this->periodo,$this->montocuota,$this->sede,$this->montoinscri); 
			}
 			
             $json = $this->clearForm();
           	$this->getResponse()->setBody(Zend_Json::encode($json));
        }
	}

	public function reiniciarAction()
    {
    	if ($this->_request->isXmlHttpRequest()) 
    	{
    		$this->SwapBytes_Ajax->setHeader(); 
    		///reiniciar valores
    		$json = $this->clearForm(); 

            $this->getResponse()->setBody(Zend_Json::encode($json));    		
    	}
    }

	// Funcion que se encarga de reiniciar todos los campos luego de algun cambio (agregar,modificar)
	function clearForm()
	{
		$json[] = $this->SwapBytes_Jquery->setVal('montocuota', 0);
		$json[] = $this->SwapBytes_Jquery->setVal('montoinscri', 0);
		$json[] = $this->SwapBytes_Jquery->setVal('montocuotaNew', 0);
		$json[] = $this->SwapBytes_Jquery->setVal('montoinscriNew', 0);
		$json[] = $this->SwapBytes_Jquery->setAttr(Agregar, disabled, true);
		$json[] = $this->SwapBytes_Jquery->setAttr(Modificar, disabled, true);
		$json[] = $this->SwapBytes_Jquery->setAttr(montocuota, disabled, true);			
		$json[] = $this->SwapBytes_Jquery->setAttr(montoinscri, disabled, true);
		$json[] = $this->SwapBytes_Jquery->setAttr(montocuotaNew, disabled, true);			
		$json[] = $this->SwapBytes_Jquery->setAttr(montoinscriNew, disabled, true);		
		$json[] = "$('#Agregar').addClass('disabled');
				   $('#Modificar').addClass('disabled');
				   $('#montocuota').removeClass('invalid');
				   $('#sede').attr('disabled', false);
				   $('#periodo').attr('disabled', false);
				   $('#NuevoIngreso').attr('disabled', false);
				   $('#montocuota').removeClass('invalid');
				   $('#montocuotaNew').removeClass('invalid');
				   $('#montoinscri').removeClass('invalid');
				   $('#montoinscriNew').removeClass('invalid');";
		return $json;
	}
}
