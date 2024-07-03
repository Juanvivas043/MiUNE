<?php

class Reports_BibliotecaestadisticasController extends Zend_Controller_Action {
      
private $Title = 'Biblioteca Estadistca';
      
        public function init() {
         
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_BibliotecaEstadistica');
        
        $this->bestadistica             = new Models_DbTable_BibliotecaEstadistica();
        $this->seguridad                    = new Models_DbTable_UsuariosGrupos();
	$this->filtros                  = new Une_Filtros();
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
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        
          
       
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        //Filtros//

       // $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
       // $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
       // $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);
	
        $grupo[0]['valor']   = '-1';
        $grupo[0]['display'] = 'Todos';
        $grupo[1]['valor']   = '855';
        $grupo[1]['display'] = 'Estudiante';
        $grupo[2]['valor']   = '854';
        $grupo[2]['display'] = 'Docente';
        $grupo[3]['valor']   = '1745';
        $grupo[3]['display'] = 'Administrativo';
        $this->grupo =  $grupo;
        
        
        $iten[0]['valor']='1';
        $iten[0]['display']='Libro ';
        $iten[1]['valor']='2';
        $iten[1]['display']='Tesis ';
        $this->iten =  $iten;
        
        $estado[0]['valor']='-1';
        $estado[0]['display']='Todos ';
        $estado[1]['valor']='8242';
        $estado[1]['display']='Prestamo ';
        $estado[2]['valor']='8243';
        $estado[2]['display']='Devuelto ';
        $estado[3]['valor']='8244';
        $estado[3]['display']='Mora ';
        $this->estado =  $estado;
        
  
        $this->tablas = Array(
                                  
                          /*    'Grupo'         => Array('vw_grupos',
                                                  Array('pk_atributo in(855,854,1745)'),
                                                  Array('pk_atributo','grupo'),
                                                  'DESC')*/
                             
                           /*   'Estado'          => Array('tbl_atributos',
                                                   Array('fk_atributotipo = 39'),
                                                   Array('pk_atributo','valor'),
                                                  'DESC') */
            
                             
            
            );
        
        
        
        //Botones//
        $this->SwapBytes_Crud_Action->setDisplay(true, true);
        $this->SwapBytes_Crud_Action->setEnable(true, true);
        
     
        
        //Formulario//
    
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
     
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));

        }

    function preDispatch() {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
                $this->_helper->redirector('index', 'login', 'default');
            }

            if (!$this->seguridad->haveAccessToModule()) {
                $this->_helper->redirector('accesserror', 'profile', 'default');
            }
        }

    public function filterAction(){
            $this->SwapBytes_Ajax->setHeader(); 
            $select = $this->_getParam('select');
            $values = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));

            if(!$select || !$values){
                 
                
                $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,null,1,null);
                $json[] = $this->CmcBytes_Filtros->addCustom('Grupo',$this->grupo);
                $json[] = $this->CmcBytes_Filtros->addCustom('Tipo'  ,$this->iten);
                $json[] = $this->CmcBytes_Filtros->addCustom('Estado',$this->estado);
                
                            
                $html1 = "<td><input type='text' name='fecha_ini' id='fecha_ini'></td ";
                $html2 = "<td><input type='text' name='fecha_fin' id='fecha_fin'></td ";
                 
                $json[] = '$("#select_filters").children().children().first().append("<td>Fecha Inicial</td>")';
                $json[] = "$('#select_filters').children().children().last().append(\"" . $html1 . "\");";
                $json[] = '$("#select_filters").children().children().first().append("<td>Fecha Final</td>")';
                $json[] = "$('#select_filters').children().children().last().append(\"" . $html2 . "\");";
                $json[] = '$("#fecha_ini").datepicker()'; 
                $json[] = '$("#fecha_fin").datepicker()'; 
                //$json[] = '$("#Estado").append(<option value="8242">Prestamo</option>)';
              
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

            $data = $this->_params['filters'];
            
            
            if($data['Grupo'] !=NULL && $data['Estado']!= NULL  && $data['fecha_ini']!=NULL && $data['fecha_fin']!=NULL){
                $rows           = $this->bestadistica->getData($data['Grupo'],$data['Estado'], $data['Tipo'],$data['fecha_ini'],$data['fecha_fin']);
            }
            $table = array('class' => 'tableData',
                           'width' => '800px');

            $columns = array(
                            array('name'  => 'Administracion',
                                   'column'  => 'administracion',
                                   'width'   => '50px', 
                                   'rows'    => array('style' => 'text-align:center')),
                                
                            array('name'    => 'Computacion',
                                   'width'   => '50px',
                                   'column'  => 'computacion',
                                   'rows'    => array('style' => 'text-align:center')),
                
                             array('name'    => 'Diseño',
                                   'width'   => '50px',
                                   'column'  => 'diseno',
                                   'rows'    => array('style' => 'text-align:center')),
                                   
                             array('name'    => 'Civil',
                                   'width'   => '50px',
                                   'column'  => 'civil',
                                   'rows'    => array('style' => 'text-align:center')),
                
                             array('name'    => 'Electronica',
                                   'width'   => '50px',
                                   'column'  => 'electronica',
                                   'rows'    => array('style' => 'text-align:center')),
                
                             array('name'    => 'Turismo',
                                   'width'   => '50px',
                                   'column'  => 'turismo',
                                   'rows'    => array('style' => 'text-align:center')),
                
                             array('name'    => 'N/A',
                                   'width'   => '50px',
                                   'column'  => 'na',
                                   'rows'    => array('style' => 'text-align:center')),
                
                            array('name'    => 'Total',
                                   'width'   => '50px',
                                   'column'  => 'total',
                                   'rows'    => array('style' => 'text-align:center')),       
                );

            $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount);
            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
   
  /*  public function descargarAction() {
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
    
 */
}