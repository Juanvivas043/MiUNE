<?php

class Transactions_VerdefensasController extends Zend_Controller_Action {

private $_Title   = 'Transacciones \ Ver Defensas (Evaluador)';
	

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
        Zend_Loader::loadClass('Models_DbTable_Horarios');
        
        
        $this->Usuarios         = new Models_DbTable_Usuarios();
        $this->Pasantes         = new Models_DbTable_Pasantes();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->recordacademico  = new Models_DbTable_Recordsacademicos();
        $this->tesis           = new Models_DbTable_Tesis();
        $this->Horario = new Models_DbTable_Horarios();
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
        $this->SwapBytes_Jquery_Ui = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->AuthSpace                = new Zend_Session_Namespace('Zend_Auth');  
        $this->CmcBytes_Redirector          = new CmcBytes_Redirect();
        
        $this->redirect_session = new Zend_Session_Namespace('redirect_session');  

        $this->_params['redirect'] = $this->redirect_session->params;




//      BOTONES DE ACCIONES
                
        $this->SwapBytes_Crud_Action->setDisplay(false, false, false, false, false, false);
        $this->SwapBytes_Crud_Action->setEnable(false, false, false, false, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(false);

        $this->SwapBytes_Crud_Action->addCustum('<button id="btnRegresar" onclick="regresar()" class="ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only" name="btnRegresar" role="button" aria-disabled="false">
                                                Regresar
                                                </button>');
   

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


    public function listAction(){ 
           if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();

                $cedula = $this->AuthSpace->userId;
                $periodo = $this->tesis->getPeriodoActual();



                $rows = $this->tesis->getEvaluadorTesisparaDefensa($periodo,$cedula);


                if(isset($rows) && count($rows) > 0) {

          				$table = array('class' => 'tableData',
          	                           'width' => '1200px');

          	            $columns = array(array('column'  => 'pk_datotesis',
          	                                   'primary' => true,
          	                                   'hide'    => true),
          	                             array('name'    => 'Titulo',
          	                                       'width'   => '450px',
          	                                       'column'  => 'titulo',
          	                                       'rows'    => array('style' => 'text-align:center')),
          	                             array('name'    => 'Cedula(s)',
          	                                   'width'   => '150px',
          	                                   'rows'    => array('style' => 'text-align:center'),
          	                                   'column'  => 'cedula'),
          	                             array('name'    => 'Autor',
          	                                   'width'   => '150px',
          	                                   'rows'    => array('style' => 'text-align:center'),
          	                                   'column'  => 'autor'),
                                          array('name'    => 'Tutor',
                                                 'width'   => '150px',
                                                 'rows'    => array('style' => 'text-align:center'),
                                                 'column'  => 'tutor'),
                                          array('name'    => 'Otro Evaluador',
                                                 'width'   => '150px',
                                                 'rows'    => array('style' => 'text-align:center'),
                                                 'column'  => 'otro_evaluador'),
                                          array('name'    => 'Fecha',
                                                 'width'   => '100px',
                                                 'rows'    => array('style' => 'text-align:center'),
                                                 'column'  => 'fecha'),
                                          array('name'    => 'Horario',
                                                 'width'   => '100px',
                                                 'rows'    => array('style' => 'text-align:center'),
                                                 'column'  => 'horario'),
                                          array('name'    => 'Edificio',
                                                 'width'   => '50px',
                                                 'rows'    => array('style' => 'text-align:center'),
                                                 'column'  => 'edificio'),
                                          array('name'    => 'Aula',
                                                 'width'   => '50px',
                                                 'rows'    => array('style' => 'text-align:center'),
                                                 'column'  => 'aula')
          	                          
          	                                );

          	            $HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, null,null);
          	            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);

            }else{

                    $HTML = $this->SwapBytes_Html_Message->alert("No tiene asignada Defensa(s)");

                    $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));        

        }

    }


    public function regresoAction() {
     
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();

      $data = array( 'module'=>null,
                     'controller'=>'inicio',
                      'params'=>array('set' => 'true')
              );
 
        
        $json[] = $this->CmcBytes_Redirector->getRedirect($data);
        $this->getResponse()->setBody(Zend_Json::encode($json));
    }

  }



}