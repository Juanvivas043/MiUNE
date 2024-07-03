<?php

/**
 * User: Carlos Rivero Theoktisto 
 * Date: 19/05/2017
 * Time: 9:20 am 
 * @author kioskito
 * @author: DDTI 
 */

class Reports_PlanarticulacionController extends Zend_Controller_Action {

    	/*Funcion donde se inicializan las librerias*/
    public function init() {
        Zend_Loader::loadClass('Une_Filtros');    
        Zend_Loader::loadClass('Models_DbView_Escuelas'); 
        Zend_Loader::loadClass('Models_DbTable_Articulaciones'); 
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbView_Sedes');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');

        $this->Usuarios         		    = new Models_DbTable_Usuarios();
        $this->Une_Filtros              = new Une_Filtros();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();

        $this->Request 					        = Zend_Controller_Front::getInstance()->getRequest();

        $this->grupo                    = new Models_DbTable_UsuariosGrupos();

        $this->escuela                 	= new Models_DbView_Escuelas(); 
        $this->sede                		  = new Models_DbView_Sedes(); 
        $this->articulacion            	= new Models_DbTable_Articulaciones();

        				/*Filtros*/
          $this->_params['filters'] = $this->Une_Filtros->getParams();
	      $this->Une_Filtros->setDisplay(false, true,true);
	      $this->Une_Filtros->setRecursive(false, true,true);

         			/*Botones de Acciones*/
        $this->SwapBytes_Crud_Action->setDisplay(true,false);
        $this->SwapBytes_Crud_Action->setEnable(true,false);
        $this->SwapBytes_Crud_Search->setDisplay(false);
    } 

	function preDispatch() 
	{
         if (!Zend_Auth::getInstance()->hasIdentity()) {
             $this->_helper->redirector('index', 'login', 'default');
         }

         if (!$this->grupo->haveAccessToModule()) {
             $this->_helper->redirector('accesserror', 'profile', 'default');
         }
    }

	public function sedeAction() 
	{
	    $this->Une_Filtros->getAction(array());
	}

	public function escuelaAction() 
	{
	    $this->Une_Filtros->getAction(array('sede'));
	}

    public function indexAction() 
    {
        $this->view->title = "Reportes \ Plan de Articulación";
        $this->view->filters = $this->Une_Filtros;
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;             
    }

    public function listAction() 
    {
        if ($this->_request->isXmlHttpRequest()) 
        {
            $this->SwapBytes_Ajax->setHeader(); 

            $data = $this->articulacion->getPlanArticulacion($this->_params['filters']['sede'],$this->_params['filters']['escuela']);
           // var_dump($data);die;
            $json[]     = $this->SwapBytes_Jquery->setHtml('tableData', ''); 

            if(isset($data) && count($data) > 0) 
            {
                $property_table = array('class'  => 'tableData',
                                           'width'  => '900px',
                                           'column' => 'disponible');

                $property_column = array(array('name'     => 'Código ',
                                                   'column'   => 'codigopropietario',
                                                   'width'    => '60px',
                                                   'rows'     => array('style' => 'text-align:center')),
                                             array('name'     => ' Asignatura  2012',
                                                   'column'   => 'materia',
                                                   'width'    => '185px',
                                                   'rows'     => array('style' => 'text-align:center')),
                                             array('name'     => 'U.C',
                                                   'column'   => 'uc',
                                                   'width'    => '60px',
                                                   'rows'     => array('style' => 'text-align:center')),
                                            array('name'     => 'Código ',
                                                   'column'   => 'cod_1997',
                                                   'width'    => '60px',
                                                   'rows'     => array('style' => 'text-align:center')),
                                             array('name'     => ' Asignatura  1997',
                                                   'column'   => 'materia_1997',
                                                   'width'    => '185px',
                                                   'rows'     => array('style' => 'text-align:center')),
                                             array('name'     => 'U.C',
                                                   'column'   => 'uc_1997',
                                                   'width'    => '60px',
                                                   'rows'     => array('style' => 'text-align:center')),
                                        );
                // Generamos la lista.
                $HTML   = $this->SwapBytes_Crud_List->fill($property_table, $data, $property_column);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);                      
                //var_dump($json);die;
            }
        	$this->getResponse()->setBody(Zend_Json::encode($json));
    	}                                  
    }
}
