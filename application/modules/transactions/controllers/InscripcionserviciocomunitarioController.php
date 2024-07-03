<?php
class Transactions_InscripcionserviciocomunitarioController extends Zend_Controller_Action {   
 

    private $Title   = 'Transacciones \ Lista de Preinscritos en Servicio Comunitario II';

public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Asignacionesproyectos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->asignacionesproyectos = new Models_DbTable_Asignacionesproyectos();       
        $this->usuarios = new Models_DbTable_Usuarios();
        $this->periodos = new Models_DbTable_Periodos();       
        $this->grupo = new Models_DbTable_UsuariosGrupos();       
        $this->filtros = new Une_Filtros();      
        $this->SwapBytes_Date = new SwapBytes_Date();
        $this->SwapBytes_Uri = new SwapBytes_Uri();
        $this->SwapBytes_Form = new SwapBytes_Form();
        $this->SwapBytes_Form_Agregar = new SwapBytes_Form();
        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search = new SwapBytes_Crud_Search();
        $this->SwapBytes_Html = new SwapBytes_Html();
        $this->SwapBytes_Html_Message = new SwapBytes_Html_Message();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask = new SwapBytes_Jquery_Mask();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();
        //var_dump($this->filtros);die;
        $this->SwapBytes_Ajax->setView($this->view);

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

       
              
        // $this->filtros->setDisplay(true, true, true, false, false, false, false, false, false);
        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);

        $this->SwapBytes_Crud_Action->setDisplay(true);
        $this->SwapBytes_Crud_Action->setEnable(true);
        //$this->SwapBytes_Crud_Action->addCustum('&nbsp;<select id="selEstado" name="selEstado" style="width:100px"></select>');
        
        $this->_params['modal'] = $this->SwapBytes_Crud_Form->getParams();
        
        $this->logger = Zend_Registry::get('logger');
         
        $this->tablas = Array(
                              'Periodo'  => Array('tbl_periodos',
                                                  null,
                                                  Array('pk_periodo',
                                                        'lpad(pk_periodo::text, 4, \'0\') || \', \' || to_char(fechainicio, \'MM-yyyy\') || \' / \' ||  to_char(fechafin, \'MM-yyyy\')'),
                                                  'DESC'),
            
                              'Sede'    => Array('vw_sedes',
                                                 null     ,
                                                 Array('pk_estructura',
                                                       'nombre'),
                                                 'DESC'),

                              'Escuela' => Array(Array('tbl_estructurasescuelas ee',
                                                       'vw_escuelas es'),
                                                 Array('ee.fk_atributo = es.pk_atributo',
                                                       'ee.fk_estructura = ##Sede##'),//'fk_estructura = 7','fk_estructura = ##sede##',
                                                 Array('ee.fk_atributo',
                                                       'es.escuela'),
                                                 'ASC'),
                              'Pensums' => Array(Array('tbl_pensums'),
                                                 Array('fk_escuela = ##Escuela##'),
                                                 Array('pk_pensum','nombre') ) );

        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));        $this->SwapBytes_Form_Agregar->set($this->view->form_agregar);
        $this->SwapBytes_Crud_Search->setDisplay(false); //quita la barra de busqueda
       //var_dump($this->tablas);die;
    }
    
    

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    function preDispatch() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if (!$this->grupo->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

    /**
     * Crea la estructura base de la pagina principal.
     */
    public function indexAction() {
        $this->view->filters = $this->filtros;
        $this->view->title = $this->Title;
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();

        $this->view->SwapBytes_Ajax = $this->SwapBytes_Ajax;
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Jquery_Ui_Form = $this->SwapBytes_Jquery_Ui_Form;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        
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
       
     
   
 public function listAction() {
	if ($this->_request->isXmlHttpRequest()) {
	  $this->SwapBytes_Ajax->setHeader();

    $rows = $this->asignacionesproyectos->getPreinscritos($this->_params['filters']['Periodo'],$this->_params['filters']['Sede'],$this->_params['filters']['Escuela'],$this->_params['filters']['Pensums']);
	  $inscritos = $this->asignacionesproyectos->getInscritos($this->_params['filters']['Sede'],$this->_params['filters']['Escuela'],$this->_params['filters']['Pensums'],$this->_params['filters']['Periodo']);
    

    if (!empty($inscritos)) {


    $table = array('class'=> 'tableData',
                           'width'=> '800px');
            $columns = array(array( 'column'   => 'pk_usuario',
                                    'primary' => true,
                                    'hide'     =>true),
                             array( 'name'     => '#',
                                    'width'    => '10px',
                                    'function' => 'rownum',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'C.I.',
                                    'column'   => 'pk_usuario',
                                    'width'    => '100px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Estudiante',
                                    'column'   => 'estudiante',
                                    'width'    => '250px',
                                    'rows'     => array('style' => 'text-align:center')),
                            array( 'name'     => 'Materia',
                                    'column'   => 'materia',
                                    'width'    => '200px',
                                    'rows'     => array('style' => 'text-align:center')),
                            array( 'name'     => 'Estado',
                                    'column'   => 'valor',
                                    'width'    => '200px',
                                    'rows'     => array('style' => 'text-align:center')),
                            );

    $HTML = $this->SwapBytes_Crud_List->fill($table, $inscritos, $columns);
    $json[] = $this->SwapBytes_Jquery->setHtml('tblData', $HTML);
    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnAsignar', true);
      
    }
    else{
	 if(!empty($rows)){

	 	$table = array('class'=> 'tableData',
                           'width'=> '800px');
            $columns = array(array( 'column'   => 'pk_usuario',
                                    'primary' => true,
                                    'hide'     =>true),
                             array( 'name'     => '#',
                                    'width'    => '10px',
                                    'function' => 'rownum',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'C.I.',
                                    'column'   => 'pk_usuario',
                                    'width'    => '100px',
                                    'rows'     => array('style' => 'text-align:center')),
                             array( 'name'     => 'Estudiante',
                                    'column'   => 'estudiante',
                                    'width'    => '250px',
                                    'rows'     => array('style' => 'text-align:center')),
                            array( 'name'     => 'Materia',
                                    'column'   => 'materia',
                                    'width'    => '200px',
                                    'rows'     => array('style' => 'text-align:center')),
                            array( 'name'     => 'Estado',
                                    'column'   => 'valor',
                                    'width'    => '100px',
                                    'rows'     => array('style' => 'text-align:center')),
                            );

	 	$HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
		$json[] = $this->SwapBytes_Jquery->setHtml('tblData', $HTML);
    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnAsignar', false);
	 }

		
		
	   else {
		$HTML = $this->SwapBytes_Html_Message->alert("No existen estudiantes preinscritos.");

		$json[] = $this->SwapBytes_Jquery->setHtml('tblData', $HTML);
	  }
}

	  $this->getResponse()->setBody(Zend_Json::encode($json));
	}

  }

    public function inscribir(){
        //inscribe a los alumnos preinscritos en el último período.
        $json=array();

        $arraygrupo = $this->asignacionesproyectos->getNoInscripcion($this->_params['filters']['Periodo'],$this->_params['filters']['Sede'],$this->_params['filters']['Escuela'],$this->_params['filters']['Pensums']);
        
        if(empty($arraygrupo)){
            return 0;
        }
        else{
         $last_periodo = $this->periodos->getMasNuevo(); //ultimo período creado
         $this->asignacionesproyectos->inscribirInscripcion($arraygrupo,$last_periodo,$this->_params['filters']['Sede'],$this->_params['filters']['Escuela'],$this->_params['filters']['Pensums']);
        }

    }
 
   public function asignarAction() {
            $this->SwapBytes_Ajax->setHeader();
            $this->inscribir();//lamo a mi funcion inscribir
            $json=array();            

            $array = $this->asignacionesproyectos->getLastInscripcion($this->_params['filters']['Periodo'],$this->_params['filters']['Sede'],$this->_params['filters']['Escuela']);
            
            $asignatura = $this->asignacionesproyectos->getPkServicioII($this->_params['filters']['Escuela'],$this->_params['filters']['Pensums']);
            $length = sizeof($asignatura);
            if($length>1){
                //inscribir pasantia social I y pasantia social II
                
                $asignacionI = $this->asignacionesproyectos->getPkAsignacion($asignatura[0]['pk_asignatura'],$this->_params['filters']['Escuela'],$this->_params['filters']['Pensums'],$this->_params['filters']['Sede']);
                $asignacionII = $this->asignacionesproyectos->getPkAsignacion($asignatura[1]['pk_asignatura'],$this->_params['filters']['Escuela'],$this->_params['filters']['Pensums'],$this->_params['filters']['Sede']);
                $this->asignacionesproyectos->inscribirRecord($asignatura[0]['pk_asignatura'],$array,$asignacionI[0]['pk_asignacion']);
                $this->asignacionesproyectos->inscribirRecord($asignatura[1]['pk_asignatura'],$array,$asignacionII[0]['pk_asignacion']);
            }
            else{
                //inscribir servicio comunitario II o pasantia social I y II

                $asignacion = $this->asignacionesproyectos->getPkAsignacion($asignatura[0]['pk_asignatura'],$this->_params['filters']['Escuela'],$this->_params['filters']['Pensums'],$this->_params['filters']['Sede']);
                $this->asignacionesproyectos->inscribirRecord($asignatura[0]['pk_asignatura'],$array,$asignacion[0]['pk_asignacion']);
                
            }
            $json[]="$('#btnList').click()";         
            $this->getResponse()->setBody(Zend_Json::encode($json));
            


           
        
  }

}

