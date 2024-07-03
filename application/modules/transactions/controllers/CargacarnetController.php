<?php
class Transactions_CargacarnetController extends Zend_Controller_Action{
    private $Title = "Carga de Carnets";

    public function init() {
        
           $this->SwapBytes_Ajax = new SwapBytes_Ajax();
           $this->SwapBytes_Ajax->setView($this->view);
           Zend_Loader::loadClass('Models_DbTable_Grupos');
           Zend_Loader::loadClass('Models_DbTable_Cargacarnets');
           Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
           Zend_Loader::loadClass('Une_Filtros');

           $this->grupos = new Models_DbTable_Grupos();
           $this->CargaCarnets = new Models_DbTable_CargaCarnets();
           $this->filtros         = new Une_Filtros();
           $this->grupo = new Models_DbTable_UsuariosGrupos();//
           $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
           $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
           $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
           $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
           $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
           $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
           $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
           $this->SwapBytes_Uri            = new SwapBytes_Uri();
           $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
           $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
           $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
           $this->CmcBytes_Filtros = new CmcBytes_Filtros();
           
           $this->Request = Zend_Controller_Front::getInstance()->getRequest();

           $this->SwapBytes_Crud_Search->setDisplay(false);
           $this->SwapBytes_Crud_Action->setDisplay(true, true, true);
           $this->SwapBytes_Crud_Action->setEnable(true, true, true);
           
           
                   $this->tablas = Array('Periodo'  => Array('tbl_periodos',
                                                  null,
                                                  Array('pk_periodo',
                                                        'lpad(pk_periodo::text, 4, \'0\') || \', \' || to_char(fechainicio, \'MM-yyyy\') || \' / \' ||  to_char(fechafin, \'MM-yyyy\')'),
                                                  'DESC'),
                                         'Afinidad'  => Array('vw_pandaid_afinidades',
                                                   null,
                                                              Array('pk_afinidad',
                                                                    'nombre'),
                                                              'DESC'));
           
           $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
        
    }
    
        
    
    
      public function preDispatch() {
           if (!Zend_Auth::getInstance()->hasIdentity()) {
              $this->_helper->redirector('index', 'login', 'default');
            }

            if (!$this->grupo->haveAccessToModule()) {
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
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
      
      public function periodoAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
              
              $Periodos = $this->CargaCarnets->getPeriodos();
             
              $opt = "";
              foreach ($Periodos as $per) {
                 $opt .= "<option value='{$per['pk_periodo']}'>{$per['pk_periodo']}</option>";
              };

              $opt = addslashes($opt);

              $json[] = $this->SwapBytes_Jquery->setAttr('selPeriodo', 'disabled', 'false');
              $json[] = $this->SwapBytes_Jquery->setHtml('selPeriodo', $opt);
              $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerate', 'disabled', 'true');

              $this->getResponse()->setBody(Zend_Json::encode($json));
        }
      }
      
      public function afinidadAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
              
              $Afinidades = $this->CargaCarnets->getAfinidades();
              
              $opt = "";
              foreach ($Afinidades as $afi) {
                 $opt .= "<option value='{$afi['pk_afinidad']}'>{$afi['nombre']}</option>";
              };

              $opt = addslashes($opt);

              $json[] = $this->SwapBytes_Jquery->setAttr('selAfinidad', 'disabled', 'false');
              $json[] = $this->SwapBytes_Jquery->setHtml('selAfinidad', $opt);
              $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerate', 'disabled', 'true');

              $this->getResponse()->setBody(Zend_Json::encode($json));
        }
      }
      
        public function listAction() {
       
    // Verificamos si es una llamada de tipo AJAX.
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
            $periodo= $this->_params['filters']['Periodo'];
            $afinidad= $this->_params['filters']['Afinidad'];
            
            $tipo = '';
            
            if($afinidad == 10654){ //Docente

                $rows = $this->CargaCarnets->getDocentesafinidad($periodo);
                $tipo = 'Docentes';

            }else if($afinidad == 1437){ //Administrativo

                $rows = $this->CargaCarnets->getAdministrativosafinidad($periodo);
                $tipo = 'Administrativos';
                
            }else if($afinidad == 1436){ //Estudiante

                $rows = $this->CargaCarnets->getEstudiantesafinidad($periodo);
                $tipo = 'Estudiantes';
                
            }
            
             
            if(!empty($rows)){
               
                $count = 0;
                foreach($rows as $r){
                   
                   if ($r['estado'] == "POR CARGAR") { //cuenta usuarios por cargar
                   $count += 1;   
                   
                   }
                    
                }
                
                
                $json[] = "$('#contador').html('<div style=border:1px solid black><b style=font-size:50px;>{$count}</b></div> {$tipo} por cargar');";
                $json[] = "$('#contador').show()";
                
                $table = array('class' => 'tableData',
                               'width' => '800px');

                $columns = array(
                                 array('name'    => 'Cedula',
                                       'width'   => '80px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'pk_usuario'),

                                 array('name'    => 'Nombre',
                                       'width'   => '150px',
                                       'column'  => 'nombre'),

                                 array('name'    => 'Apellido',
                                       'width'   => '150px',
                                       'column'  => 'apellido'),
                                 
                                 array('name'    => 'Estado',
                                       'width'   => '150px',
                                       'column'  => 'estado') 
                    );
                 $other = array(
                          array('actionName' => '',
                                'action'     => 'detalle(##pk##)'  ,
                                'label'      => 'Detalle'));

                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
            
            }else{
               
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen Usuario por cargar.");
                $json[] = "$('#contador').hide()";
            }
            // Generamos la lista.
            
            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
      
      
      public function addoreditloadAction() {
          $this->SwapBytes_Ajax->setHeader(); 
          
          $periodo= $this->_params['filters']['Periodo'];
          $afinidad= $this->_params['filters']['Afinidad'];
          
          if($afinidad == 10654){ //Docente

                $rows = $this->CargaCarnets->setDocentes($periodo,$afinidad);

          }else if($afinidad == 1437){ //Administrativo

                $rows = $this->CargaCarnets->setAdministrativos($periodo,$afinidad);

          }else if($afinidad == 1436){ //Estudiante

                $rows = $this->CargaCarnets->setEstudiantes($periodo,$afinidad);

          }
          
          $this->listAction();
        
      }

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
}

?>
