


<?php

class Consultas_TesisalumnosController extends Zend_Controller_Action {
    
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
      $this->SwapBytes_Crud_Action->setDisplay(true,true);
      $this->SwapBytes_Crud_Action->setEnable(true,true);
     

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

 $this->SwapBytes_Crud_Action->AddCustum('<button type="button" id="btnValidar" >Guardar</button>');

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
    public function guardarAction(){
         $this->pktesis = $this->_getParam('valtesis');
         $this->pktesis = implode("','",$this->pktesis);
         $this->pktesis = str_replace('\'', '', $this->pktesis);
         $this->actual  = $this->Usuarios->checkplanilla($this->pktesis);

         return $this->actual;

          
    }

    public function listAction(){
          if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $params  = $this->Une_Filtros->getParams();
          
            $periodo = $params['periodo'];
            $sede    = $params['sede'];
            $escuela = $params['escuela'];
            $materia = $params['materia'];           
            $pktesis = array();

             $this->data  = $this->Usuarios->tesisalumnos($periodo,$sede,$escuela,$materia);
             $this->data2 = $this->Usuarios->cuentatesisalumnos($periodo,$sede,$escuela,$materia);

          
             //data envia la tabla con los datos de lo tesistas y tutores 
             //data2 envia las cuentas de las tesis y tutores bien sean aprobados o no
            
             $HTML .= "<table align=center id = tableData><tr><th> Nombre</th><th> Apellido</th><th> Correo </th><th> Titulo </th><th> Estado Tesis </th><th> Tutor </th><th> Estado Tutor </th><th> Planilla </th></tr>";

             foreach ($this->data as $key => $resultado) {

                  $HTML .= "<tr  class=\"px1120\" >";
                  
                  $HTML .= "<td class=\"px125\" ".$resultado['nombre']."\" >".$resultado['nombre']."</td>";
                  $HTML .= "<td class=\"px125\" ".$resultado['apellido']."\" >".$resultado['apellido']."</td>";
                  $HTML .= "<td class=\"px125\" ".$resultado['correo']."\" >".$resultado['correo']."</td>";
                  $HTML .= "<td class=\"px250\" ".$resultado['titulo']."\" >".$resultado['titulo']."</td>";
                  $HTML .= "<td class=\"px80\" ".$resultado['estadotesis']."\" >".$resultado['estadotesis']."</td>";
                  $HTML .= "<td class=\"px200\" id=\"".$resultado['nombre_tutor']."\" >".$resultado['nombre_tutor']."</td>";
                  $HTML .= "<td class=\"px80\" ".$resultado['tutor']."\" >".$resultado['tutor']."</td>";
                  if ($resultado['estado_planilla']== true) {
                        $HTML.= "<td class=\"px20\"><input type=\"checkbox\" name=\"chkEstudiante\" checked disabled></td>";
                  }else{
                        $HTML.= "<td class=\"px20 \"><input type=\"checkbox\" id=\"{$resultado['pk_usuario']}\" class=\" check\" value=\"{$resultado['pk_datotesis']}\" name=\"chkEstudiante\" ></td>";

                  }
                  
                  $HTML .= "</tr>"; 
              }                          
            // Generamos la lista.
                      

           
            $json[].= $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
          
            $json[] = '$("#totales1").html("'.$this->data2[0]['count'].'")';
            $json[] = '$("#totales2").html("'.$this->data2[1]['count'].'")';
            $json[] = '$("#totales3").html("'.$this->data2[2]['count'].'")';
            $json[] = '$("#totales4").html("'.$this->data2[3]['count'].'")';

           $this->getResponse()->setBody(Zend_Json::encode($json)); 

              }
          }     

  
}
 

?>
