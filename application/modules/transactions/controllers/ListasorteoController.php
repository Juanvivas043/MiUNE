<?php

/**
* User: Carlos Rivero Theoktisto 
* Date: 30/06/2017
* Time: 4:20 pm 
* @author: DDTI 
*/

class Transactions_ListasorteoController extends Zend_Controller_Action 
{

  /****************************  Funcion donde se inicializan las Librerias,Modelos,Filtros y Botones *******************************/
  public function init() 
  {

    Zend_Loader::loadClass('Une_Filtros');    
    Zend_Loader::loadClass('Models_DbView_Escuelas'); 
    Zend_Loader::loadClass('Models_DbTable_Usuariosvehiculossorteos');
    Zend_Loader::loadClass('Models_DbTable_Recordsacademicos'); 
    Zend_Loader::loadClass('Models_DbTable_Usuarios');


    $this->Usuarios                 = new Models_DbTable_Usuarios();
    $this->Usuariosvehiculos        = new Models_DbTable_Usuariosvehiculossorteos();
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

    				/*Filtros*/
    $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
    $this->_params['filters'] = $this->Une_Filtros->getParams();

    $this->Une_Filtros->setDisplay(true,false,false,false,false,false,true);
    $this->Une_Filtros->setRecursive(true, false,false,false,false,false,true);

     			/*Botones de Acciones*/
    $this->SwapBytes_Crud_Action->setDisplay(true,false);
    $this->SwapBytes_Crud_Action->setEnable(true,false);
    $this->SwapBytes_Crud_Search->setDisplay(false);

    $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));

    $customFilters = array(array(
                                  'id' => 'sorteo',
                                  'name' => 'selSorteo',
                                  'label' => 'Sorteo',
                                  'recursive' => true
                                ));
  $this->Une_Filtros->addCustom($customFilters);

  } 

  function preDispatch() 
  {
    /*if (!Zend_Auth::getInstance()->hasIdentity()) 
    {
      $this->_helper->redirector('index', 'login', 'default');
    }

    if (!$this->seguridad->haveAccessToModule()) 
    {
      $this->_helper->redirector('accesserror', 'profile', 'default');
    }*/
  }

  public function indexAction() 
  {
    $this->view->title = "Reportes \ Lista de Sorteo";
    $this->view->filters = $this->Une_Filtros;
    $this->view->module = $this->Request->getModuleName();
    $this->view->controller = $this->Request->getControllerName();
    $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
    $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
    $this->view->SwapBytes_Ajax->setView($this->view);
    $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
    $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search; 
    #$this->view->SwapBytes_Ajax->setView($this->view);            
  }

  /*************************************************** ACCIONES  ********************************************************************/
  public function sorteoAction()
  {
    if ($this->_request->isXmlHttpRequest()) 
    {
      $this->SwapBytes_Ajax->setHeader();
      $periodo = $this->_getParam('periodo');
      $sorteo  = $this->Usuariosvehiculos->getSorteo($periodo);
      $this->SwapBytes_Ajax_Action->fillSelect($sorteo);
    }          
  }

  public function periodoAction() 
  {
    $this->Une_Filtros->getAction(array());
  }

  public function turnoAction()
  {
    $this->Une_Filtros->getAction(array());
  }

  /************************************************** Generar Tabla *****************************************************************/

  public function listAction() 
  {
    if ($this->_request->isXmlHttpRequest()) 
    {
      $this->SwapBytes_Ajax->setHeader(); 
      $data = $this->_params['filters'];
      $periodo = $this->_params['redirect']['per'];
      $pk = $this->_params['redirect']['fk_ugs'];
      $rows = $this->Usuariosvehiculos->participantesSorteoNew($this->_params['filters']['selPeriodo'],$this->_params['filters']['selTurno'],$this->_params['filters']['selSorteo']);

      $json[]     = $this->SwapBytes_Jquery->setHtml('tableData', ''); 

      if(isset($rows) && count($rows) > 0) 
      {
        $property_table = array('class'  => 'tableData',
                                   'width'  => '900px',
                                   'column' => 'disponible');

        $property_column = array(   array( 'column' => 'usu_sorteo',
                                            'primary' => true,
                                            'hide' => true),
                                    array( 'name'     => 'N°',
                                           'width'    => '15px',
                                           'function' => 'rownum',
                                           'rows'     => array('style' => 'text-align:right')),
                                    array( 'name'     => 'Cédula',
                                           'column'   => 'cedula',
                                           'width'    => '60px',
                                           'rows'     => array('style' => 'text-align:center')),
                                    array( 'name'     => 'Nombre ',
                                           'column'   => 'nombre',
                                           'width'    => '130px',
                                           'rows'     => array('style' => 'text-align:center')),
                                    array( 'name'     => 'Apellido ',
                                           'column'   => 'apellido',
                                           'width'    => '130px',
                                           'rows'     => array('style' => 'text-align:center')),
                                    array( 'name'     => 'Escuela ',
                                           'column'   => 'escuela',
                                           'width'    => '120px',
                                           'rows'     => array('style' => 'text-align:center')),
                                    array( 'name'     => 'Pago ',
                                           'column'   => 'pago',
                                           'width'    => '60px',
                                           'rows'     => array('style' => 'text-align:center')),
                                    array( 'name'     => 'Carnet ',
                                           'column'   => 'carnet',
                                           'width'    => '60px',
                                           'rows'     => array('style' => 'text-align:center')),
                                    array( 'name'     => 'Estado ',
                                           'column'   => 'estado',
                                           'width'    => '60px',
                                           'rows'     => array('style' => 'text-align:center')), 
                                    );

        $other = Array(
                        Array( 'actionName' => 'activar',
                              'action' => 'activar(##pk##,$(this));return false;',
                              'label' => 'Activar',
                              'column' => 'activable',
                              'validate' => 'true',
                              'intrue' => 'Si',
                              'intruelabel' => ''),
                        Array( 'actionName' => 'desactivar',
                              'action' => 'desactivar(##pk##,$(this));return false;',
                              'label' => 'Desactivar',
                              'column' => 'estado',
                              'validate' => 'true',
                              'intrue' => 'Activo',
                              'intruelabel' => '')             
                      );

                            // Generamos la tabla.
        $HTML   = $this->SwapBytes_Crud_List->fill($property_table, $rows, $property_column,'O', $other);
        $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);                      
          //var_dump($json);die;
      }
      else
      {
        $HTML  = $this->SwapBytes_Html_Message->alert("No existen Usuarios Registrados en este Sorteo");
        $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
      }
      $this->getResponse()->setBody(Zend_Json::encode($json));
  	}                                  
  }
/******************************************************** Acciones *****************************************************************/
        //esto

  public function activarAction()
  {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
        $pk = $this->_getParam('pk');
        // $data['cactivo'] = 'true';
        // $emitido = $this->usuvehiculossorteos->updateRow($pk, $data);
        $this->Usuariosvehiculos->updateEstadoCarnetNew($pk, 'true');
    }
  }

  public function desactivarAction()
  {
    $this->SwapBytes_Ajax->setHeader();
      $pk = $this->_getParam('pk');
      $this->Usuariosvehiculos->updateEstadoCarnetNew($pk, 'false');
      // $emitido = $this->Usuariosvehiculos->updateRow($pk, $data);
  }
}
//buscar como mandar del index al controller 