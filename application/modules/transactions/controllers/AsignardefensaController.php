<?php

class Transactions_AsignardefensaController extends Zend_Controller_Action {

	private $_Title   = 'Transacciones \ Asignar Defensa'; 
	private $FormTitle_Info = 'Asignar Evaluadores';

	public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios'); 
        Zend_Loader::loadClass('Models_DbTable_Pasantes'); 
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Tesis');
        Zend_Loader::loadClass('Models_DbTable_Materiasestados');
        Zend_Loader::loadClass('Models_DbView_Grupos');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Forms_Observaciones');
        
        
        $this->Usuarios         = new Models_DbTable_Usuarios();
        $this->Pasantes         = new Models_DbTable_Pasantes();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->recordacademico  = new Models_DbTable_Recordsacademicos();
        $this->tesis           = new Models_DbTable_Tesis();
        $this->materiasestados = new Models_DbTable_Materiasestados();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->filtros          = new Une_Filtros();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();
        $this->vw_grupos        = new Models_DbView_Grupos();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->AuthSpace                = new Zend_Session_Namespace('Zend_Auth');
        $this->CmcBytes_Redirector          = new CmcBytes_Redirect();  

        $this->view->form = new Forms_Observaciones();
        $this->SwapBytes_Form->set($this->view->form);
        $this->view->form = $this->SwapBytes_Form->get();

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
                                                       'ee.fk_estructura = ##Sede##'),
                                                 Array('ee.fk_atributo',
                                                       'es.escuela'),
                                                 'ASC'),
                                'Estado'  => Array('tbl_atributos',
                                                 array('fk_atributotipo = '.$this->tesis->getAtributotipoEvaluadores()),
                                                 array('pk_atributo',
                                                       'valor')

                                                )
                                                            );
            

    $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters')); 

//      BOTONES DE ACCIONES
                
    $this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);
    $this->SwapBytes_Crud_Action->setEnable(true, true, false, true, false, false);
    $this->SwapBytes_Crud_Search->setDisplay(true); 
   

   //Recibimos los filtros desde DefensatesisControllers.php para poder llenarlos cuando presionen el boton "Regresar" 
    $this->redirect_session = new Zend_Session_Namespace('redirect_session');  
    $this->backParams['redirect-filters'] = $this->redirect_session->params;

	}

function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
       if (!$this->grupo->haveAccessToModule()) {
           $this->_helper->redirector('accesserror', 'profile', 'default');
         }    
    }


    public function indexAction() {

        $this->view->title                 = $this->_Title;
        $this->view->filters               = $this->filtros;
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
        $this->SwapBytes_Ajax_Action       = new SwapBytes_Ajax_Action();
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();

    } 


    public function filterAction()
    {

              $this->SwapBytes_Ajax->setHeader(); 
              $select = $this->_getParam('select');
              $values = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));

              if(!$select || !$values){

                 $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,null,1,null);


              }else{

                  $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,$values,null,$select);
              }            


            if(!isset($_SESSION['filterBack']))
            {
              // QUIZAS DEBERIAS USAR MEMCACHE :(, YA QUE CADA PETICION SETEA TODA LA CLASE... SI, ESTOY SEGURO, POR EJEMPLO EL 
              // VARIABLE SE VUELVE A 3 COMO LO SETEAMOS AL PRINCIPIO DE LA CLASE 
              $_SESSION['counter'] += 1;

                $json[] = "$('#Periodo').val('".$this->backParams['redirect-filters']['periodo']."')";
                $json[] = "$('#Periodo').trigger('change')";
                $json[] = "$('#Sede').val('".$this->backParams['redirect-filters']['sede']."')";
                $json[] = "$('#Escuela').val('".$this->backParams['redirect-filters']['escuela']."')";
                $json[] = "$('#Estado').val('".$this->backParams['redirect-filters']['estado']."')";
                $json[] = "$('#Periodo').live('change', function(e){ if(e.originalEvent == undefined){ $('#Periodo').unbind('change'); } })";

                $json[] = "$('#btnList').trigger('click')";

                if($_SESSION['counter'] >= 4)
                {
                  $_SESSION['filterBack'] = 2;
                }

            }
              $this->getResponse()->setBody(Zend_Json::encode($json));
            
    }



    public function listAction(){
           if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();

                $datos = $this->_getAllParams();
                $filtro = $this->SwapBytes_Uri->queryToArray($datos['filters']);
                $asignado = $this->tesis->getEstadoEvaluadorAsignado();

                if($filtro['Estado'] == $asignado){//tipo 1 para editar, 0 para agregar
                  $mod = 'Asignado';
                  $label = "editar";
                  
                }else{
                  $mod = 'No Asignado';
                  $label = "asignar";
                }
                
                $rows = $this->tesis->getTesisConDefensa($filtro['Escuela'],$filtro['Periodo'],$filtro['txtBuscar'],$mod,null,$filtro['Sede']);
                
                if(isset($rows) && count($rows) > 0) {

                        $table = array('class' => 'tableData',
                               'width' => '1000px');

                        $columns = array(array('column'  => 'pk_datotesis',
                                               'primary' => true,
                                               'hide'    => true),
                                         array('name'    => 'Cedula(s)',
                                                   'width'   => '70px',
                                                   'column'  => 'cedula',
                                                   'rows'    => array('style' => 'text-align:center')),
                                         array('name'    => 'Autor(es)',
                                               'width'   => '200px',
                                               'rows'    => array('style' => 'text-align:center'),
                                               'column'  => 'autor'),
                                         array('name'    => 'Titulo',
                                               'width'   => '400px',
                                               'rows'    => array('style' => 'text-align:center'),
                                               'column'  => 'titulo'),
                                         array('name'    => 'Tutor',
                                               'width'   => '200px',
                                               'rows'    => array('style' => 'text-align:center'),
                                               'column'  => 'tutor'),
                                         );

                        $other = array(
                          array('actionName' => '',
                                'action'     => 'asignar(##pk##)'  ,
                                'label'      => $label,
                                'column' => 'acciones')
                          
                                );

                        $HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'O',$other);
                        $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                        $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkDatoTesis');

            }else{

                    $HTML = $this->SwapBytes_Html_Message->alert("No Existen Tesis Cargadas");

                    $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));        

        }

    }


  public function asignarAction() {
     
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();
      $cod = $this->_getParam('cod');
      $periodo = $this->_getParam('periodo');
      $sede = $this->_getParam('sede');
      $escuela = $this->_getParam('escuela');

      $datos = $this->_getAllParams();
      $estado = $this->SwapBytes_Uri->queryToArray($datos['filters']);

      $data = array( 'module'=>'transactions',
                     'controller'=>'defensatesis',
                      'params'=>array('cod' => $cod,
                                      'periodo' => $periodo,
                                      'sede' => $sede,
                                      'escuela' => $escuela,
                                      'estado' => $estado['Estado'],
                                      'set' => 'true')
              );
 
        $json[] = $this->CmcBytes_Redirector->getRedirect($data);

        $this->getResponse()->setBody(Zend_Json::encode($json));
    }
  }    

}