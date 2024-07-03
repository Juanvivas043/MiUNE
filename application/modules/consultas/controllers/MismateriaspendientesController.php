<?php

class Consultas_MismateriaspendientesController extends Zend_Controller_Action {
    
    public function init() {

        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Pensums');
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');

        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbView_Sedes');
        Zend_Loader::loadClass('Models_DbView_Escuelas');

        $this->Une_Filtros              = new Une_Filtros();
        $this->usuarios                 = new Models_DbTable_Usuarios();
        $this->seguridad                = new Models_DbTable_UsuariosGrupos();
        $this->inscripciones            = new Models_DbTable_Inscripciones();
        $this->periodos                 = new Models_DbTable_Periodos();
        $this->sedes                     = new Models_DbView_Sedes();
        $this->escuelas                 = new Models_DbView_Escuelas();
        $this->pensums                  = new Models_DbTable_Pensums();
        $this->recordacademico          = new Models_DbTable_Recordsacademicos();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->cedulas = new Zend_Session_Namespace('Zend_Auth');

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Jquery->endLine(true);
        $this->ci             = $this->usuarios->getUsuario($this->cedulas->userId)['pk_usuario'];
        $this->trampa = $this->ci.' and i.fk_pensum not in (26,27,28,29,30,31,32,33,34,35,36,37) limit 1';
        $this->usuario        = $this->usuarios->getUsuario($this->ci);
        $this->nombre         = $this->usuario['primer_nombre'].' '.$this->usuario['segundo_nombre'].' '.$this->usuario['primer_apellido'].' '.$this->usuario['segundo_apellido'];
        $this->periodo        = $this->recordacademico->getUltimoPeriodoInscrito($this->ci)[0]['periodo'];
        $this->periodoA       = $this->periodos->getPeriodoActual();
        $this->sede           = $this->inscripciones->getUltimaSede($this->ci);
        $this->sedeN          = $this->sedes->getSedeName($this->sede);
        $this->escuela        = $this->recordacademico->getUltimaEscuelaCursada($this->ci)[0]['pk_atributo'];
        $this->ultimaEscuelaN = $this->escuelas->getEscuelaName($this->escuela);
        $this->pensum         = $this->inscripciones->getPensumInscripcion($this->trampa,$this->periodo)[0]['fk_pensum'];
        $this->pensumN        = $this->recordacademico->getPensum($this->pensum)[0]['nombre'];
        $this->cursadas       = $this->recordacademico->materiasCursadas($this->ci, $this->pensum)[0]['materias'];
        $this->porCursar      = $this->recordacademico->materiasPorCursar($this->ci, $this->pensum)[0]['materias'];
        $this->uca            = $this->recordacademico->getUnidadesDeCreditoAprobadas($this->ci,$this->pensum,$this->escuela,$this->sede)[0]["uc"];        
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

        $this->view->ci                     = number_format($this->ci, 0, ',' ,'.');
        $this->view->nombre                 = $this->nombre;
        $this->view->cursadas               = $this->cursadas;
        $this->view->porCursar              = $this->porCursar;
        $this->view->uca                    = $this->uca;
        $this->view->pensumN                = $this->pensumN;
        $this->view->sedeN                  = $this->sedeN;
        $this->view->ultimaEscuelaN         = $this->ultimaEscuelaN;
        $this->view->periodoA               = $this->periodoA;

        $this->view->title                  = "Consultas \ Materias Pendientes";
        $this->view->filters                = $this->Une_Filtros;
        $this->view->module                 = $this->Request->getModuleName();
        $this->view->controller             = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery       = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax         = $this->SwapBytes_Ajax;
        $this->view->SwapBytes_Crud_Action  = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search  = $this->SwapBytes_Crud_Search;
        
        $this->view->SwapBytes_Ajax->setView($this->view);
       
    }
 
   public function tablaAction() {
        if ($this->_request->isXmlHttpRequest()) {

            $this->SwapBytes_Ajax->setHeader();   

            $data = $this->usuarios->getMateriasPendientes($this->ci,$this->periodoA,$this->sede,$this->escuela,$this->pensum);
            
            //var_dump($data);die;  
            //var_dump($ci,$this->nombre,$this->periodoA,$this->sedeN,$this->ultimaEscuelaN,$this->pensumN,$this->cursadas,$this->porCursar,$this->uca);die;
            $json[]     = $this->SwapBytes_Jquery->setHtml('tableData', ''); 

            if(isset($data) && count($data) > 0) {

                $property_table = array('class'  => 'tableData',
                                           'width'  => '900px',
                                           'column' => 'disponible');
                $property_column = array(array('name'     => 'Código',
                                                   'column'   => 'codigo',
                                                   'width'    => '60px',
                                                   'rows'     => array('style' => 'text-align:center')),
                                             array('name'     => 'Asignatura',
                                                   'column'   => 'materia',
                                                   'width'    => '185px',
                                                   'rows'     => array('style' => 'text-align:center')),
                                             array('name'     => 'Estado',
                                                   'column'   => 'estado',
                                                   'width'    => '75px',
                                                   'rows'     => array('style' => 'text-align:center','class' => 'estado')),
                                             array('name'     => 'Período',
                                                   'column'   => 'valor',
                                                   'width'    => '90px',
                                                   'rows'     => array('style' => 'text-align:center')),
                                             array('name'     => 'UC',
                                                   'column'   => 'uc',
                                                   'width'    => '20px',
                                                   'rows'     => array('style' => 'text-align:center')),
                                             array('name'     => 'Prelaciones',
                                                   'column'   => 'prelacion',
                                                   'width'    => '185px',
                                                   'rows'     => array('style' => 'text-align:center')),
                                             array('name'     => 'UC Prelado',
                                                   'column'   => 'prelacionuc',
                                                   'width'    => '70px',
                                                   'rows'     => array('style' => 'text-align:center')),
                                             );
                // Generamos la lista.
                $HTML   = $this->SwapBytes_Crud_List->fill($property_table, $data, $property_column);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);                      
                //var_dump($json);die;
            }else {

              $HTML =  $this->SwapBytes_Html_Message->alert("Este usuario no tiene materias pendientes.");
              $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);   

            }

        $this->getResponse()->setBody(Zend_Json::encode($json));

        }
       
    }
}

?>