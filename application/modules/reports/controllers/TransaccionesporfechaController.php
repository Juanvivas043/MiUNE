<?php

class Reports_TransaccionesporfechaController extends Zend_Controller_Action {  

    public function init() {
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Transaccionespg');

        $this->usuarios                 = new Models_DbTable_Usuarios();
        $this->grupo                    = new Models_DbTable_UsuariosGrupos();
        $this->transacciones            = new Models_DbTable_Transaccionespg();
		$this->filtros                  = new Une_Filtros();

        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->CmcBytes_Filtros         = new CmcBytes_Filtros();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        /*
         * Configuramos los botones.
         */
	    
        
        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);
        $this->tablas = ['Sede'    => ['vw_sedes',
                                                 null     ,
                                                 Array('pk_estructura',
                                                       'nombre'),
                                                 'DESC']];
        $this->SwapBytes_Crud_Action->setDisplay(true, true, false);
        $this->SwapBytes_Crud_Action->setEnable(true, true, true);
        $this->_params['filters']   = $this->filtros->getParams();

    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    
    function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if(!$this->grupo->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

      public function filterAction(){
            $this->SwapBytes_Ajax->setHeader(); 
            $select = $this->_getParam('select');
            $values = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));

            if(!$select || !$values){
                $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,null,1,null);
            }else{
                $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,$values,null,$select);
            }
            $json[] = "$('select#Sede option').first().html('Todas')";
            $this->getResponse()->setBody(Zend_Json::encode($json));
    }
    
    /**
     * Crea la estructura base de la pagina principal.
     */
    public function indexAction() {
        $this->view->title      = $this->Title;
        $this->view->filters    = $this->filtros;
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
	    $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
	    $this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
	    $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
    }

    /**
     * Lista el contenido y las acciones pertinentes de una tabla determinada de
     * forma paginada.
     */
    public function listAction() {
    // Verificamos si es una llamada de tipo AJAX.
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            //Recibo valores de la vista
            $fechaInicio = $_GET["selDateDesde"];
            $fechaFin    = $_GET["selDateHasta"];
            //No se selecciono una fecha


            // Definimos los valores
             $queryString = $this->_getParam('filters');
            $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);
            $sede = $queryArray['Sede'];
            if ($sede == '') {
              $sede = '7, 8';
            }else{
              $sede = $sede;
            }
            $this->transacciones->setSearch($searchData);
            $saldo = 0;

           
            $rows           = $this->transacciones->getTransaccionesByDate($sede, $fechaInicio, $fechaFin);
            //var_dump($rows);die;
              $rowsnew = array();
              // Definimos las propiedades de la tabla.
              $table = array('class' => 'tableData');
              $columns = array(array('name'    => '# Factura',
                                     'column'  => 'factura',
                                     'rows'    => array('style' => 'text-align:center')),
                               array('name'    => 'C.I.',
                                     'column'  => 'pk_usuario',
                                     'rows'    => array('style' => 'text-align:center')),
                               array('name'    => 'Nombres',
                                     'column'  => 'nombre'),
                               array('name'    => 'Apellidos',
				     'width'   => '175px',
                                     'column'  => 'apellido'),
                               array('name'    => 'Sede',
                                     'column'  => 'sede',
                                     'rows'    => array('style' => 'text-align:center')),
                               array('name'    => 'Tipo trans.',
                                     'column'  => 'tipo',
                                     'rows'    => array('style' => 'text-align:center')),
                               array('name'    => '# Cobro',
                                     'column'  => 'cobro',
                                     'rows'    => array('style' => 'text-align:center')),
                                array('name'    => 'lot-ref',
                                     'column'  => 'lote',
                                     'rows'    => array('style' => 'text-align:center')),
                               array('name'    => 'Fecha',
                                     'column'  => 'dia',
                                     'rows'    => array('style' => 'text-align:center')),
                               array('name'    => 'HORA',
                                     'column'  => 'hora',
                                     'rows'    => array('style' => 'text-align:center')),
                               array('name'    => 'Cantidad',
                                     'column'  => 'cantidad',
                                     'rows'    => array('style' => 'text-align:center')),
                                array('name'    => 'Monto',
                                     'column'  => 'monto',
                                     'rows'    => array('style' => 'text-align:center')),
                               array('name'    => 'Monto Total',
                                     'column'  => 'montototal',
                                     'rows'    => array('style' => 'text-align:center')),
                                array('name'    => 'TOTAL ACUM.',
                                     'column'  => 'dif',
                                     'rows'    => array('style' => 'text-align:center')));
              // Generamos la lista.
              foreach ($rows as $key => $value) {             
                  $saldo = $value['montototal'] + $saldo;
                  $rows[$key]['monto'] =  number_format($value['monto'],2);
                  $rows[$key]['montototal'] =  number_format($value['montototal'],2);
              }
        
              if (count($rows) > 0) {
                array_push($rows, array('dif' => number_format($saldo,2)));
              
                $HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
              
             }else{     
                 $alert = "No existen registros en las fechas seleccionadas.";
                 $HTML = $this->SwapBytes_Html_Message->alert($alert);       
                 $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
              }
              $json[] = "$('.tableData td').each(function(){
                            if ($(this).text() == '' && $(this).parent().find('td').first().text() != ''){
                              $(this).parent().addClass('Error')
                            }
                         })";
                $this->getResponse()->setBody(Zend_Json::encode($json));
        

         
}
    
  }

    /**
     * Carga la ayuda del modulo, basicamente es una pagina html no dinamica que
     * contiene toda la informaci‚àö‚â•n relevante del modulo.
     */
    public function helpAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $this->render();
    }


}
