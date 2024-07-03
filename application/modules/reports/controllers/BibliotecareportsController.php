<?php

class Reports_BibliotecareportsController extends Zend_Controller_Action {


    private $Title = 'Reportes Biblioteca';
    
    //private $Prestamoval   = 8242;//8223;
    //private $Moraval       = 8244;//8224;
    //private $Devueltoval   = 8243;//8225;
    
   
    public function init() {
         
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        //Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        //Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        //Zend_Loader::loadClass('Models_DbTable_Prestamo');
        //Zend_Loader::loadClass('Forms_Prestamoart');
        //Zend_Loader::loadClass('Models_DbTable_Prestamoart');
        
        Zend_Loader::loadClass('Models_DbTable_Bibliotecareportes');
        $this->breporte        = new Models_DbTable_Bibliotecareportes();
        
        //$this->prestamo        = new Models_DbTable_Prestamo();
        //$this->prestamoart     = new Models_DbTable_Prestamoart();
       
        //$this->estudiante      = new Models_DbTable_Usuarios();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        //$this->recordacademico = new Models_DbTable_Recordsacademicos();
		$this->filtros         = new Une_Filtros();
        $this->CmcBytes_Filtros         = new CmcBytes_Filtros();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Ui = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        //$this->session = new Zend_Session_Namespace('session');
        //$this->redirect_session = new Zend_Session_Namespace('redirect_session');
        
        $this->aux = Array();
        //Filtros//

        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);
	
  
        //Filtros Pocho
          $this->tablas = Array(
                              'Estado'  => Array('tbl_atributos',
                                                  Array('fk_atributotipo = 5'),
                                                  Array('pk_atributo','valor'),
                                                  'DESC'),
            
                             

                              'Acceso' => Array(Array('tbl_accesosgrupos ac',
                                                       'tbl_accesos a'),
                                                 Array('ac.fk_acceso = a.pk_acceso',
                                                       'ac.fk_grupo = ##Grupo##',
                                                       'ac.visibility = true',
                                                        'a.fk_acceso is NULL'),
                                                 Array('a.pk_acceso',
                                                       'a.nombre'),
                                                 'ASC'),
            
                             'Sub-Acceso' => Array(Array('tbl_accesosgrupos ac',
                                                       'tbl_accesos a'),
                                                 Array('ac.fk_acceso = a.pk_acceso',
                                                       'ac.fk_grupo = ##Grupo##',
                                                       'a.fk_acceso = ##Acceso##',
                                                       'ac.visibility = true'),
                                                 Array('a.pk_acceso',
                                                       'a.nombre'),
                                                 'ASC')
            
            );
        
        
        
        //Botones//

        $this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, true, false, false, false, false);
        
     
        
        //Formulario//
    
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        //$this->_params['filters'] = $this->filtros->getParams();

        }

    function preDispatch() {
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
        
    public function listAction() {
    // Verificamos si es una llamada de tipo AJAX.
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            // Obtenemos los parametros necesarios que se esta pasando por POST, y
            // los valores necesarios de las variables que se utilizaran mas adelante.
           
             $HtmlObjectName = 'pk_prestamo';
            
            $pageNumber  = $this->_getParam('page', 1);
            $searchData  = $this->_getParam('buscar');
            $itemPerPage = 3;
            $pageRange   = 3;

            $rows           = $this->breporte->getlistreport();
            $rows = $this->tranformarow($rows);
            
            $table = array('class' => 'tableData',
                           'width' => '800px');

            $columns = array(array('name'    => 'Solicitud',
                                   'width'   => '25px',
                                   'column'  => 'solicitud',
                                   'primary' => true),

                            array('name'  => 'Cedula',
                                   'column'  => 'pk_usuario',
                                   'width'   => '100px' ),
                                
                            array('name'    => 'Nombre',
                                   'width'   => '80px',
                                   'column'  => 'nombre'),
                
                            array('name'    => 'Apellido',
                                   'width'   => '80px',
                                   'column'  => 'apellido'),
                                   
                            array('name'    => 'Perfil',
                                   'width'   => '80px',
                                   'column'  => 'perfil'),
                
                             
                             array('name'    => 'Estado',
                                   'width'   => '100px',
                                   'column'  => 'estado'),
                
                             array('name'    => 'Libro',
                                   'width'   => '80px',
                                   'column'  => 'cota'),
                
                            array('name'     => array('control' => array('tag'        => 'input',
                                                                                      'type'       => 'checkbox',
                                                                                      'checked'    => 'checked',
                                                                                      'name'       => 'chkSelectDeselect')),
                                               'width'    => '30px',
                                               'column'   => $HtmlObjectName,
                                               'rows'     => array('style'      => 'text-align:center'),
                                               'control'  => array('tag'        => 'input',
                                                                   'type'       => 'checkbox',
                                                                   'checked'    => 'checked',
                                                                   'name'       => 'chkLibros',
                                                                   'value'      => "##{$HtmlObjectName}##")),
                );

            $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);  
            $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkLibros');
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
   
    private function validarfecha($data){
        
        $hide = explode ("/", $data['fecha_solicitud']); //0 dia 1 mes 2 año
        //var_dump($hide);
        $entrega = explode ("/", $data['fecha_estimada']); //0 dia 1 mes 2 año
        
        if($hide[2] > $entrega[2] ){ //año es mayor
         
         return "invalido";
        }
         if($hide[2] == $entrega[2] && $hide[1] > $entrega[1]){ //mes es mayor
            
         return "invalido";
        }
        if($hide[2] == $entrega[2] && $hide[1] == $entrega[1] &&  $hide[0] > $entrega[0]){ //dia mayor
            
         return "invalido";
        }
        
        return "valido";
    }
    
    private function tranformarow($rows){
         if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $nrows = array();
            $repe = array();
            $cantidad = count($rows);
            $cont = 0;
            //var_dump($rows);
          for ($i = 0 ; $i<$cantidad ;$i++){
              
               for ($j = 0 ; $j<$cantidad ; $j++){
                    
                  if ($rows[$i]['solicitud'] == $rows[$j]['solicitud'] ){
                   
                    $repe[$cont]['solicitud']            = $rows[$i]['solicitud'];
                    $repe[$cont]['pk_usuario']           = $rows[$i]['pk_usuario'];
                    $repe[$cont]['nombre']               = $rows[$i]['nombre'];
                    $repe[$cont]['apellido']             = $rows[$i]['apellido'];
                    $repe[$cont]['fecha_prestamo']       = $rows[$i]['fecha_prestamo'];
                    $repe[$cont]['perfil']               = $rows[$i]['perfil'];
                    $repe[$cont]['estado']               = $rows[$i]['estado'];
                    $repe[$cont]['cota']                 = $rows[$j]['cota']." , ".$repe[$cont]['cota'];
                    
                  }
                 
                 
               }
               $cont ++;
               
          }
       
         $nrows=$this->elimina_duplicados($repe, 'solicitud');
         }
         //var_dump($nrows);
         return $nrows;
    }
    
    public function elimina_duplicados($array, $campo){
  
  foreach ($array as $sub)
  {
    $cmp[] = $sub[$campo];
  }
  $unique = array_unique($cmp);
  foreach ($unique as $k => $campo)
  {
    $resultado[] = $array[$k];
  }
  return $resultado;
}


   public function descargarAction() {
        $Params = $this->_getParam('data');
        $Params = $this->SwapBytes_Uri->queryToArray($Params);
        $config = Zend_Registry::get('config');

		$dbname = $config->database->params->dbname;
		$dbuser = $config->database->params->username;
		$dbpass = $config->database->params->password;
		$dbhost = $config->database->params->host;
		$report = APPLICATION_PATH . '/modules/reports/templates/Listadoestudiantes/ListadoEstudiantesSeleccionBien.jrxml';
		$filename    = 'listablanca';
		$filetype    = strtolower($Params['rdbFormat']);
      if(!is_array($Params['chkEstudiante'])){
         $Estudiantes = $Params['chkEstudiante'];
      }else{
         $Estudiantes = implode(',', $Params['chkEstudiante']);
      }
		$params      = "'Sede=string:{$Params['selSede']}|Periodo=string:{$Params['selPeriodo']}|Escuela=string:{$Params['selEscuela']}|Semestre=string:{$Params['selSemestre']}|Materia=string:{$Params['selMateria']}|Seccion=string:{$Params['selSeccion']}|CIs=string:{$Estudiantes}'";
		$cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmd.jar -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";

        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
		
        $outstream = exec($cmd);
        echo base64_decode($outstream);
    }
}