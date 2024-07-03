


<?php

class Consultas_TesisdirescController extends Zend_Controller_Action {
    
   public function init() {
        //instanciar clases
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Horarios');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Descargables');
        Zend_Loader::loadClass('Zend_Pdf'); 
        Zend_Loader::loadClass('Models_DbView_Materias');       


        //instanciar models
        $this->Usuarios       = new Models_DbTable_Usuarios();
        $this->Materias       = new Models_DbView_Materias();
        $this->pdf            = new Zend_Pdf();
        $this->Une_Filtros    = new Une_Filtros();
        $this->grupo          = new Models_DbTable_UsuariosGrupos();
  

        //se instancia request que es el que maneja los _params
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->Swapbytes_array          = new SwapBytes_Array();
        $this->CmcBytes_Filtros         = new CmcBytes_Filtros();

/*Filtros*/
      $this->Une_Filtros->setDisplay(true, true, true);
      $this->Une_Filtros->setRecursive(true, true, true);
      /*Botones de Acciones*/
      $this->SwapBytes_Crud_Action->setDisplay(true,false);
      $this->SwapBytes_Crud_Action->setEnable(true,false);
     

       $this->SwapBytes_Crud_Search->setDisplay(false);
       $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
      
      
       $customFilters = array(
        array(
          'id' => 'materia',
          'name' => 'selMateria',
          'label' => 'Materia',
          'recursive' => true,
          
        )    
      );

 $this->Une_Filtros->addCustom($customFilters);
 
    }   
    

    public function indexAction() {

      $this->view->title   = 'Consultas \ Tesis';
      $this->view->filters = $this->Une_Filtros;
      $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
      $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
      $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
      $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
      $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
      $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();


    }
      


  public function periodoAction() {
        $this->Une_Filtros->getAction();
    }

  public function sedeAction() {
    $this->Une_Filtros->getAction(array());
  
  }
  public function escuelaAction() {
    $this->Une_Filtros->getAction(array());
  
  }

  public function materiaAction() {

     if ($this->_request->isXmlHttpRequest()) {
      $json = array();
      $this->SwapBytes_Ajax->setHeader();
      $this->escuelas = $this->_getParam('escuela');

      $materias = $this->Materias->materiastesis($this->escuelas);
      $this->SwapBytes_Ajax_Action->fillSelect($materias);
    }
    
  } 

 

   function preDispatch() {
      if (!Zend_Auth::getInstance()->hasIdentity()) {
        $this->_helper->redirector('index', 'login', 'default');
      }
      if (!$this->grupo->haveAccessToModule()) {
        $this->_helper->redirector('accesserror', 'profile', 'default');
      }
    }

    public function listAction(){
          if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $params  = $this->Une_Filtros->getParams();
          
            $periodo = $params['periodo'];
            $sede = $params['sede'];
            $escuela = $params['escuela'];
            $materia = $params['materia'];           

             $data = $this->Usuarios->tesisalumnosdes($periodo,$sede,$escuela,$materia);
           
            if (isset($data) == false) {// validar que el campo cedula no este vacio
                  $alertV = "no hay registros no aprobados por el momento";
                  $HTML = $this->SwapBytes_Html_Message->alert($alertV);
                  $json[]= "$('#datosEstudiante').hide();";        
                  $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                  $this->getResponse()->setBody(Zend_Json::encode($json));
                }
            else {
               $property_table = array(   'class'  => 'tableData',
                                       'width'  => '1050px',
                                       'column' => 'disponible');

            $property_column = array(array('name' => 'nombre',
                                               'column'   => 'nombre',
                                               'width'    => '130px',
                                               'rows'     => array('style' => 'text-align:left')),
                                        array('name' => 'apellido',
                                               'column'   => 'apellido',
                                               'width'    => '130px',
                                               'rows'     => array('style' => 'text-align:left')),
                                         array('name' => 'correo',
                                               'column'   => 'correo',
                                               'width'    => '130px',
                                               'rows'     => array('style' => 'text-align:left')),
                                         array('name' => 'titulo',
                                               'column'   => 'titulo',
                                               'width'    => '250px',
                                               'rows'     => array('style' => 'text-align:left')),
                                         array('name' => 'estado tesis',
                                               'column'   => 'estadotesis',
                                               'width'    => '130px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name' => 'nombre tutor',
                                               'column'   => 'nombre_tutor',
                                               'width'    => '150px',
                                               'rows'     => array('style' => 'text-align:center')),
                                         array('name' => 'estado tutor',
                                               'column'   => 'tutor',
                                               'width'    => '130px',
                                               'rows'     => array('style' => 'text-align:center')),
                                        );
              
            // Generamos la lista.
            $HTML   = $this->SwapBytes_Crud_List->fill($property_table, $data, $property_column);
            $json[] = $this->SwapBytes_Jquery->setHtml('tblCantidad', $HTML);

                     
           $this->getResponse()->setBody(Zend_Json::encode($json)); 
                }
              }
          }     
  
}

            /*    if ($ci == NULL) {// validar que el campo cedula no este vacio
                  $alertV = "El campo cedula esta vacio.";
                  $HTML = $this->SwapBytes_Html_Message->alert($alertV);
                  $json[]= "$('#datosEstudiante').hide();";        
                  $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                  $this->getResponse()->setBody(Zend_Json::encode($json));
*/
?>
  
