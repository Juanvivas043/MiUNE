<?php  
	class Consultas_BecadossistemaController extends Zend_Controller_Action{
		public function init() {

	      	Zend_Loader::loadClass('Models_DbTable_Usuarios');
	      	Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
	      	Zend_Loader::loadClass('Models_DbTable_Profit');
	        Zend_Loader::loadClass('CmcBytes_Profit');
	        Zend_Loader::loadClass('Une_Filtros');
	        Zend_Loader::loadClass('Models_DbView_Escuelas'); 
        	Zend_Loader::loadClass('Models_DbView_Sedes');
        	Zend_Loader::loadClass('Models_DbTable_Periodos'); 
        	Zend_Loader::loadClass('Models_DbView_Escuelas');
			Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');

			$this->usuarios        = new Models_DbTable_Usuarios();
			$this->grupo           = new Models_DbTable_UsuariosGrupos();
	        $this->profit          = new Models_DbTable_Profit();
	        $this->Une_Filtros     = new Une_Filtros();
        	$this->sede            = new Models_DbView_Sedes();
	        $this->escuela         = new Models_DbView_Escuelas();
	        $this->periodos        = new Models_DbTable_Periodos();
	        $this->escuela         = new Models_DbView_Escuelas();
	        $this->escuelas        = new Models_DbTable_EstructurasEscuelas();

	        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
	        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
	        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
	        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
	        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
	        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
	        $this->SwapBytes_Uri            = new SwapBytes_Uri();
	        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
	        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
	        $this->CmcBytes_profit          = new CmcBytes_Profit();
	        $this->CmcBytes_Filtros         = new CmcBytes_Filtros();
	        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();

	        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
	        /*Filtros*/
	        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
	        $this->Une_Filtros->setDisplay(true, true,true,false,false);
	        $this->Une_Filtros->setRecursive(true, true,true,false,false);
	        /*Botones de Acciones*/
	        $this->SwapBytes_Crud_Action->setDisplay(true,true);
	        $this->SwapBytes_Crud_Action->setEnable(true,true);
	        $this->SwapBytes_Crud_Search->setDisplay(false);
    	}

	    function preDispatch() {
	       if (!Zend_Auth::getInstance()->hasIdentity()) {
	           $this->_helper->redirector('index', 'login', 'default');
	       }
	       
	       if (!$this->grupo->haveAccessToModule()) {
	           $this->_helper->redirector('accesserror', 'profile', 'default');
	       }
	    }

	    //Acciones referidas al index
	    public function indexAction() {
	        $this->view->title = "Consultas \ Becados Sistema";
	        $this->view->filters = $this->Une_Filtros;
	        $this->view->module = $this->Request->getModuleName();
	        $this->view->controller = $this->Request->getControllerName();
	        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
	        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
	        $this->view->SwapBytes_Ajax->setView($this->view);
	        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        	$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
	    }

	    public function periodoAction(){
	    	if ($this->_request->isXmlHttpRequest()) {
	            $json = array();
	            $this->SwapBytes_Ajax->setHeader();
	            $params  = $this->Une_Filtros->getParams();
	            $periodosBecados = $this->periodos->periodosBecados();
	            $this->SwapBytes_Ajax_Action->fillSelect($periodosBecados);
	        }
	    }

	    public function sedeAction() {
	    	$dataSedes = $this->sede->getSedes();
	        array_unshift($dataSedes, array("pk_atributo"=>"0","sede"=>"Todas")); //se anade el campo de "todas" para seleccionar todas las sedes
	        $this->SwapBytes_Ajax_Action->fillSelect($dataSedes);        
	    }

    	public function escuelaAction() {
	        if ($this->Request->getParam('sede')==0) {
	          $dataRows = $this->escuela->getEscuelas();
	          array_unshift($dataRows, array("pk_atributo"=>"0","escuela"=>"Todas")); //se anade el campo de "todas" para seleccionar todas las escuelas
	          $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
	        }else{
	          $dataRows = $this->escuelas->getSelect($this->Request->getParam('sede'));
	          array_unshift($dataRows, array("pk_atributo"=>"0","escuela"=>"Todas")); //se anade el campo de "todas" para seleccionar todas las escuelas
	          $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
	        }
	    }

	    public function listAction(){
	    	if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();            
            $periodo        = $this->_params['filters']['selPeriodo'];
            $sede           = $this->_params['filters']['selSede'];
            $escuela        = $this->_params['filters']['selEscuela'];    
            $cedulas = $this->profit->getBecaEstudiantes($periodo);            
            foreach ($cedulas as $key => $value) {
            	$trim[$key]["co_cli"]=rtrim($value["co_cli"]);
            	$trim[$key]["co_art"]=rtrim($value["co_art"]);
            }
            foreach ($cedulas as $key => $value) {
            	$stringtrim = $stringtrim.rtrim($value["co_cli"]).',';
            }

            $cedulas_string = rtrim($stringtrim,',');     

            $data = $this->usuarios->getBecadosSistema($periodo,$cedulas_string,$sede,$escuela);

			foreach ($data as $key1 => $value1) {
				foreach ($trim as $key2 => $value2) {

					if ($value1["pk_usuario"]==$value2["co_cli"]) {
						($data[$key1]["co_art"]= $value2["co_art"]);				

					}
				}
			}//var_dump($data);die;
                if(isset($data) && count($data) > 0) {
	              $property_table = array('class'  => 'tableData',
	                                         'width'  => '900px',
	                                         'column' => 'disponible');

	              $property_column = array(array('name'     => 'C.I',
	                                                 'column'   => 'pk_usuario',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                           array('name'     => 'nombre',
	                                                 'column'   => 'nombre',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                           array('name'     => 'apellido',
	                                                 'column'   => 'apellido',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                           array('name'     => '1',
	                                                 'column'   => 'indice_periodo_anterior',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                           array('name'     => '2',
	                                                 'column'   => 'asig_reprobadas_periodo_anterior',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                           array('name'     => '3',
	                                                 'column'   => 'asig_inscritas_menos_retiradas_periodo_anterior',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                           array('name'     => '4',
	                                                 'column'   => 'asig_retiradas_periodo_anterior',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                           array('name'     => '5',
	                                                 'column'   => 'promedio_aprobadas_periodo_anterior',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                           array('name'     => '6',
	                                                 'column'   => 'indice_acumulado',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                           array('name'     => '7',
	                                                 'column'   => 'reprobadas_cursantes',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                          array('name'     => '8',
	                                                 'column'   => 'asig_inscritas_menos_retiradas_periodo_actual',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                          array('name'     => '9',
	                                                 'column'   => 'asig_retiradas_periodo_actual',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                          array('name'     => '10',
	                                                 'column'   => 'promedio_aprobadas_periodo_actual',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                          array('name'     => '11',
	                                                 'column'   => 'todas_cargadas',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                          array('name'     => '12',
	                                                 'column'   => 'uc_aprob_mas_inscritas',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                          array('name'     => '13',
	                                                 'column'   => 'uc_carrera',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                          array('name'     => '14',
	                                                 'column'   => 'sede',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                          array('name'     => '15',
	                                                 'column'   => 'escuela',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                          array('name'     => '16',
	                                                 'column'   => 'semestre_academico',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                          array('name'     => '17',
	                                                 'column'   => 'uc_inscritas_periodo_actual',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                          array('name'     => '18',
	                                                 'column'   => 'uc_periodo_ubicacion',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                          array('name'     => '19',
	                                                 'column'   => 'estado',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                          array('name'     => '20',
	                                                 'column'   => 'razon',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                          array('name'     => '21',
	                                                 'column'   => 'co_art',
	                                                 'rows'     => array('style' => 'text-align:center')),
	                                           );
	              // Generamos la lista.
	              $HTML   = $this->SwapBytes_Crud_List->fill($property_table, $data, $property_column);
	              $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);    
	            }
	            else {
	              $HTML =  $this->SwapBytes_Html_Message->alert("No existen Registros.");
	              $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);   
	            }
	            $this->getResponse()->setBody(Zend_Json::encode($json));
        	}
	    }
	}
?>