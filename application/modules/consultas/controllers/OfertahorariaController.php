<?php

class Consultas_OfertahorariaController extends Zend_Controller_Action {
    
    public function init() {
          Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Grupos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Une_Filtros');

        $this->Une_Filtros              = new Une_Filtros();
        $this->seguridad                = new Models_DbTable_UsuariosGrupos();
        $this->inscripciones            = new Models_DbTable_Inscripciones();
        $this->recordacademico         = new Models_DbTable_Recordsacademicos();
        $this->sedes                    = new Models_DbTable_Estructuras();
        $this->Usuarios                 = new Models_DbTable_Usuarios();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();




        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->cedulas                  = new Zend_Session_Namespace('Zend_Auth');
        $this->current_user             = new Zend_Session_Namespace('Zend_Auth');
        /*Filtros*/


        $this->Une_Filtros->setDisplay(false, true);
        $this->Une_Filtros->setRecursive(true, true);
        /*Botones de Acciones*/
        $this->SwapBytes_Crud_Action->setDisplay(false,false);
        $this->SwapBytes_Crud_Action->setEnable(false,false);
        $this->SwapBytes_Crud_Search->setDisplay(false);
    }

    function preDispatch() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
           $this->_helper->redirector('index', 'login', 'default');
        }
        if (!$this->seguridad->haveAccessToModule()) {
           $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

    //Acciones referidas al index
    public function indexAction() {
        $this->view->title = "Consultas \ Oferta de horario";
        $this->view->filters = $this->Une_Filtros;
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;

    }



    public function generarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json = array();
            $ci = $this->current_user->userId;
            $this->ci = $this->user['pk_usuario'];
            $periodo = $this->recordacademico->getUltimoPeriodocursado($ci)[0]['fn_xrxx_reinscripcion_upc'];
            $pensum = $this->inscripciones->getpensum($ci, $periodo);
            $escuela = $this->inscripciones->getUltimaEscuelapk($ci);
            $sede = $this->inscripciones->getsedeperiodo($ci,$periodo);
            $data = $this->Usuarios->ofertahoraria($periodo, $pensum, $escuela, $ci, $sede);

            $HTML .= "<table align=center id = tblCantidad><tr><th> Materia</th><th> UC</th><th> Semestre </th><th> Turno </th><th> Seccion </th><th> Nota </th><th> Profesor </th><th> Hora </th><th> Aula </th><th> Dia </th></tr>";
              
             foreach ($data as $key => $resultado) {
                  
                  
                  
                  if ($materia == $resultado['materia'] and $seccion == $resultado['seccion'] and $nota == $resultado['nota']) {
                    $HTML .= "<tr  class=\"px850 \" >";
                    $HTML .= "<td class=\"px30 \" colspan=\"7\"  ></td>";
                    $HTML .= "<td class=\"px50\"  >".$resultado['hora']."</td>";
                    $HTML .= "<td class=\"px130\"  >".$resultado['ubicacion']."</td>";
                    $HTML .= "<td class=\"px70\"  >".$resultado['dia']."</td>";
                    
                  }else{
                    $HTML .= "<tr  class=\"px850 \" >";
                    $HTML .= "<td class=\"px130 \"  >".$resultado['materia']."</td>";
                    $HTML .= "<td class=\"px30 \"  >".$resultado['uc']."</td>";
                    $HTML .= "<td class=\"px90 \"  >".$resultado['semestre']."</td>";
                    $HTML .= "<td class=\"px80 \"  >".$resultado['turno']."</td>";
                    $HTML .= "<td class=\"px30 \"  >".$resultado['seccion']."</td>";
                    $HTML .= "<td class=\"px130 \"  >".$resultado['nota']."</td>";
                    $HTML .= "<td class=\"px150 \"  >".$resultado['profesor']."</td>";
                    $HTML .= "<td class=\"px55 \"  >".$resultado['hora']."</td>";
                    $HTML .= "<td class=\"px130 \"  >".$resultado['ubicacion']."</td>";
                    $HTML .= "<td class=\"px70 \"  >".$resultado['dia']."</td>"; 
                    $materia=$resultado['materia'];
                    $seccion=$resultado['seccion'];
                    $nota= $resultado['nota'];
                }

                $HTML .= "</tr>"; 
                
              }                          
            // Generamos la lista.
            //        
            $json[].= $this->SwapBytes_Jquery->setHtml('tblCantidad', $HTML);
            // Generamos la lista. 
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    
}

?>