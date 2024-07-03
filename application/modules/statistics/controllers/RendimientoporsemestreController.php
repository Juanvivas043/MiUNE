<?php

/**
* @author: eugenioloxi@gmail.com
* 
*
*/

class Statistics_RendimientoporsemestreController extends Zend_Controller_Action {

    /*Funcion donde se inicializan las librerias*/
    public function init() {

        Zend_Loader::loadClass('Une_Filtros');       
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');

        $this->Une_Filtros              = new Une_Filtros();
        $this->recordsacademicos 		    = new Models_DbTable_Recordsacademicos();
        $this->grupo                    = new Models_DbTable_UsuariosGrupos();

        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        /*Filtros*/
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));

        $this->Une_Filtros->setDisplay(true, true, true);
        $this->Une_Filtros->setRecursive(true, true, true);
         /*Botones de Acciones*/
        $this->SwapBytes_Crud_Action->setDisplay(true,true);
       /* $this->SwapBytes_Crud_Action->addCustum("<button id=\"btnGraficos\" class=\"ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\" name=\"btnGraficos\" role=\"button\" aria-disabled=\"false\">Gráficos</button>");*/
        $this->SwapBytes_Crud_Action->setEnable(true,true);
        $this->SwapBytes_Crud_Search->setDisplay(false);

    } 

    function preDispatch() {

         if (!Zend_Auth::getInstance()->hasIdentity()) {  
             $this->_helper->redirector('index', 'login', 'default');
         }
    
         if (!$this->grupo->haveAccessToModule()) {
             $this->_helper->redirector('accesserror', 'profile', 'default');
         }

    }

    public function indexAction() {
        $this->view->title = "Estadísticas / Rendimiento Académico Por Semestre";
        $this->view->filters = $this->Une_Filtros;
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;   

    }

    public function periodoAction() {        
        if ($this->_request->isXmlHttpRequest()) {
           $this->Une_Filtros->getAction();
        }

    }

    public function sedeAction() {
		if ($this->_request->isXmlHttpRequest()) {
           $this->Une_Filtros->getAction();
        }      
    }

    public function escuelaAction() {
		if ($this->_request->isXmlHttpRequest()) {
           $this->Une_Filtros->getAction();
        }
    }
    
    /*public function graficosAction(){
    	
    }*/

    public function listAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $periodo        = $this->_params['filters']['selPeriodo'];
            $sede           = $this->_params['filters']['selSede'];  
            $escuela        = $this->_params['filters']['selEscuela'];  

            $json           = array();
        
            $Rendimiento    = $this->recordsacademicos->getRendimientoporsemestre($periodo,$sede,$escuela);

            $this->ren_universidad = array(array_shift($Rendimiento));
           	$this->ren_escuela     = array(array_shift($Rendimiento));

            $this->ren_universidad[0]["aprobadas"] .= ' ('.$this->ren_universidad[0]["apro"].'%)';
            $this->ren_universidad[0]["aplazadas"] .= ' ('.$this->ren_universidad[0]["apla"].'%)';
            $this->ren_universidad[0]["retirados"] .= ' ('.$this->ren_universidad[0]["ret"].'%)';
            $this->ren_escuela[0]["aprobadas"]     .= ' ('.$this->ren_escuela[0]["apro"].'%)';
            $this->ren_escuela[0]["aplazadas"]     .= ' ('.$this->ren_escuela[0]["apla"].'%)';
            $this->ren_escuela[0]["retirados"]     .= ' ('.$this->ren_escuela[0]["ret"].'%)';
           	//var_dump($this->ren_universidad,$this->ren_escuela);die;	
           	//var_dump($Rendimiento,$ren_escuela);die;

            // Definimos las propiedades de la tabla.
            if (!empty($Rendimiento)) {

              $tabla_estudiantes = array('class'  => 'tableData',
                                         'width'  => '976px',
                                         'column' => 'disponible');
              $columnas_estudiantes = array(array('name'     => 'Semestre',
                                                 'column' 	=> 'detalle',
                                                 'width'    => '100px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'inscritos',
                                                 'column'   => 'inscritas',
                                                 'width'    => '125px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Aprobadas',
                                                 'column'   => 'aprobadas',
                                                 'width'    => '175px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Aplazadas',
                                                 'column'   => 'aplazadas',
                                                 'width'    => '175px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'retirados',
                                                 'column'   => 'retirados',
                                                 'width'    => '175px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'aprobados(%)',
                                                 'column'   => 'apro',
                                                 'width'    => '100px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'aplazados(%)',
                                                 'column'   => 'apla',
                                                 'width'    => '125px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'retirados(%)',
                                                 'column'   => 'ret',
                                                 'width'    => '125px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'promedio',
                                                 'column'   => 'califprom',
                                                 'width'    => '125px',
                                                 'rows'     => array('style' => 'text-align:center'))
                                           );

              $tabla_mini = array('class'  => 'tableData',
                                  'column' => 'disponible');
              $columnas_mini = array(array('name'     => 'Aprobados',
                                           'column'   => 'aprobadas',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'Reprobados',
                                           'column'   => 'aplazadas',
                                           'rows'     => array('style' => 'text-align:center')),
                                     array('name'     => 'retirados',
                                           'column'   => 'retirados',
                                           'rows'     => array('style' => 'text-align:center'))
                                     );

              // Generamos la lista.  
              $HTML_estudiantes = $this->SwapBytes_Crud_List->fill($tabla_estudiantes, $Rendimiento, $columnas_estudiantes);
              $HTML_universidad = $this->SwapBytes_Crud_List->fill($tabla_mini,$this->ren_universidad,$columnas_mini);
              $HTML_escuela     = $this->SwapBytes_Crud_List->fill($tabla_mini,$this->ren_escuela,$columnas_mini);

              $json[] = $this->SwapBytes_Jquery->setHtml('tblUniversidad', $HTML_universidad);
              $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML_estudiantes);
              $json[] = $this->SwapBytes_Jquery->setHtml('tblEscuela',$HTML_escuela);
              $json[] = $this->SwapBytes_Jquery->setHtml('ins_univ' ,$this->ren_universidad[0]["inscritas"]);
              $json[] = $this->SwapBytes_Jquery->setHtml('ins_esc'  ,$this->ren_escuela[0]["inscritas"]);
              $json[] = $this->SwapBytes_Jquery->setHtml('prom_univ',$this->ren_universidad[0]["califprom"]);
              $json[] = $this->SwapBytes_Jquery->setHtml('prom_esc' ,$this->ren_escuela[0]["califprom"]);
              $json[] = '$("#tblContainer").removeClass();';
              //var_dump($json);

            }else{

              $HTML =  $this->SwapBytes_Html_Message->alert("No existen Registros.");
              $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);

            }

            $this->getResponse()->setBody(Zend_Json::encode($json));

          }
    }
}
