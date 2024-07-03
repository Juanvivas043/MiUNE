<?php

class Transactions_TemainvestigacionController extends Zend_Controller_Action {

private $_Title   = 'Transacciones \ Temas de investigacion';
	

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
        Zend_Loader::loadClass('Forms_Temainvestigacion');
        
        
        
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


        $this->view->form_tema = new Forms_Temainvestigacion();

        $this->SwapBytes_Form->set($this->view->form_tema);

        $this->view->form_tema = $this->SwapBytes_Form->get();


        // $this->tablas = Array(
                              
        //                       'Escuela' => Array(Array('tbl_estructurasescuelas ee',
        //                                                'vw_escuelas es'),
        //                                          Array('ee.fk_atributo = es.pk_atributo'),//'fk_estructura = 7','fk_estructura = ##sede##',
        //                                          Array('ee.fk_atributo',
        //                                                'es.escuela'),
        //                                          'ASC'));
            

    $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters')); 
    $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
    
    $this->redirect_session = new Zend_Session_Namespace('redirect_session');  

    $this->_params['redirect'] = $this->redirect_session->params;
    $this->SwapBytes_Crud_Action->addCustum('<button id="btnRegreso" onclick="regresar()"  class="ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only" name="btnRegreso" role="button" aria-disabled="false">
                                                Regreso
                                                </button>');   



//      BOTONES DE ACCIONES
                
    $this->SwapBytes_Crud_Action->setDisplay(true, false, true, false, false, false);
    $this->SwapBytes_Crud_Action->setEnable(true, false, true, false, false, false);
    $this->SwapBytes_Crud_Search->setDisplay(true); 


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
        $this->view->info                  = $this->masterInfo();
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
        $this->SwapBytes_Ajax_Action       = new SwapBytes_Ajax_Action();
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();

    } 

    private function masterInfo(){

            $lineainvestigacion = $this->_params['redirect']['cod'];
            $escuela = $this->_params['redirect']['escuela'];

            $info = $this->tesis->getAtributoPK($escuela);
            
            $properties = array('width' => '700',
                                'align' => 'center');


            $styles = array(array('style' => 'text-align:center;font-size:16px')
                      );

            $data[] = array('<b>Escuela:</b> '.$info[0]['valor']);
            

            $html  = $this->SwapBytes_Html->table($properties, $data, $styles);

            return $html;
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




    public function listAction(){
           if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();

                $datos = $this->_getAllParams();
                $filtro = $this->SwapBytes_Uri->queryToArray($datos['filters']);

                $lineainvestigacion = $this->_params['redirect']['cod'];
                

                $rows = $this->tesis->getTesisTema($lineainvestigacion,$filtro['txtBuscar']);
               
                
                

                if(isset($rows) && count($rows) > 0) {

        				$table = array('class' => 'tableData',
        	                           'width' => '600px');

  	            $columns = array(array('column'  => 'pk_atributo',
  	                                   'primary' => true,
  	                                   'hide'    => true),
  	                             array('name'    => 'Tema',
  	                                       'width'   => '500px',
  	                                       'column'  => 'tema',
  	                                       'rows'    => array('style' => 'text-align:justify'))
  	                          
  	                                );



	            $HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VUD',$other);
	            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);

            }else{

                    $HTML = $this->SwapBytes_Html_Message->alert("No Existen Temas Cargados");

                    $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));        

        }

    }


    public function addoreditloadAction() {
      if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();
        $datos = $this->_getAllParams();



        $pk_linea = $this->_params['redirect']['cod'];
        $escuela = $this->_params['redirect']['escuela'];

        $lineainvestigacion = $this->tesis->getAtributoPK($pk_linea);

        $dataRow['escuela'] = $escuela;


        if(!empty($datos['id'])){//editar
          $tema = $this->tesis->getAtributoPK($datos['id']);

          $dataRow['tema'] = $tema[0]['valor'];
          $dataRow['pk_atributo'] = $datos['id'];

          $title = 'Editar Tema';
        }else{
          $title = 'Agregar Tema';
        }

        $this->SwapBytes_Crud_Form->setProperties($this->view->form_tema,$dataRow, $title);

        $this->SwapBytes_Crud_Form->fillSelectBox('fk_lineainvestigacion', $lineainvestigacion, 'pk_atributo', 'valor'); 
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();  
        

      }

    } 



    public function addoreditconfirmAction() {
      if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();
        
        $this->SwapBytes_Crud_Form->setProperties($this->view->form_tema, $this->_params['modal']);  

        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
        

      }

    } 


    public function addoreditresponseAction() {
      if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();

        $datos = $this->_getAllParams();
        $pk_linea = $this->_params['redirect']['cod'];
        $escuela = $this->_params['redirect']['escuela'];
        $dataRow = $this->SwapBytes_Uri->queryToArray($datos['data']);
        
        
        $atributolinea = $this->tesis->getAtributoLinea();

          
        if(empty($dataRow['pk_atributo'])){//agregar

          $this->tesis->addTemaInvestigacionAtributo($dataRow['tema']);

          $pk_tema = $this->tesis->getAtributo($dataRow['tema']);

          if(!empty($pk_tema)){
            $this->tesis->addLineaInvestigacion($pk_linea,$pk_tema,$escuela);  
          }

        }else{//editar
          $this->tesis->updateLineaInvestigacionAtributo($dataRow['pk_atributo'],$dataRow['tema']);
        }
        
        $this->SwapBytes_Crud_Form->getAddOrEditEnd();
      }

    }        

    public function deleteloadAction() {
      if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();
        $datos = $this->_getAllParams();
        
        
        $permit = true;

        $datos = $this->_getAllParams();



        $pk_linea = $this->_params['redirect']['cod'];
        $escuela = $this->_params['redirect']['escuela'];

        $lineainvestigacion = $this->tesis->getAtributoPK($pk_linea);

        $dataRow['escuela'] = $escuela;

        if(!empty($datos['id'])){//editar

          $tema = $this->tesis->getAtributoPK($datos['id']);

          $dataRow['tema'] = $tema[0]['valor'];
          $dataRow['pk_atributo'] = $datos['id'];
          

        }


        
        $this->SwapBytes_Crud_Form->setProperties($this->view->form_tema,$dataRow, 'Eliminar Tema de investigacion');
        $this->SwapBytes_Crud_Form->fillSelectBox('fk_lineainvestigacion', $lineainvestigacion, 'pk_atributo', 'valor'); 
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->getDeleteLoad($permit);

      }

    }

    public function deletefinishAction(){
      if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();

        $datos = $this->_getAllParams();

        $dataRow = $this->SwapBytes_Uri->queryToArray($datos['data']);
        
        if(!empty($dataRow['pk_atributo'])){

          $this->tesis->deletetemainvestigacion($dataRow['pk_atributo']);
          $this->tesis->deleteAtributo($dataRow['pk_atributo']);
        }

        $this->SwapBytes_Crud_Form->getDeleteFinish();

      }

    }



  public function regresoAction() {
     
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();

      $data = array( 'module'=>'transactions',
                     'controller'=>'lineainvestigacion',
                      'params'=>array('set' => 'true')
              );
 
        
        $json[] = $this->CmcBytes_Redirector->getRedirect($data);
        $this->getResponse()->setBody(Zend_Json::encode($json));
    }

  }


  public function viewAction(){
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $datos = $this->_getAllParams();

      $pk_linea = $this->_params['redirect']['cod'];
      $escuela = $this->_params['redirect']['escuela'];

      $lineainvestigacion = $this->tesis->getAtributoPK($pk_linea);

      $dataRow['escuela'] = $escuela;


      if(!empty($datos['id'])){
        $tema = $this->tesis->getAtributoPK($datos['id']);

        $dataRow['tema'] = $tema[0]['valor'];
        $dataRow['pk_atributo'] = $datos['id'];

      }

      $this->SwapBytes_Crud_Form->setProperties($this->view->form_tema,$dataRow, 'Ver Tema de investigacion');
      $this->SwapBytes_Crud_Form->fillSelectBox('fk_lineainvestigacion', $lineainvestigacion, 'pk_atributo', 'valor'); 
      $this->SwapBytes_Crud_Form->setJson($json); 
      $this->SwapBytes_Crud_Form->getView();
    }

  }


}