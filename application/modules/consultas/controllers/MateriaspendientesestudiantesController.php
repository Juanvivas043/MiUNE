<?php

class Consultas_MateriaspendientesestudiantesController extends Zend_Controller_Action {
    
    public function init() {

        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_Pensums');
        Zend_Loader::loadClass('Une_Filtros');

        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbView_Sedes');
        Zend_Loader::loadClass('Models_DbView_Escuelas');

        $this->Une_Filtros              = new Une_Filtros();
        $this->usuarios                 = new Models_DbTable_Usuarios();
        $this->seguridad                = new Models_DbTable_UsuariosGrupos();
        $this->inscripciones            = new Models_DbTable_Inscripciones();
        $this->recordacademico          = new Models_DbTable_Recordsacademicos();

        $this->periodos                 = new Models_DbTable_Periodos();
        $this->sedes                    = new Models_DbView_Sedes();
        $this->escuelas                 = new Models_DbView_Escuelas();
        $this->pensums                  = new Models_DbTable_Pensums();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Jquery->endLine(true);
    }

    function preDispatch() {
       if (!Zend_Auth::getInstance()->hasIdentity()) {
           $this->_helper->redirector('index', 'login', 'default');
       }
    
       if (!$this->seguridad->haveAccessToModule()) {
           $this->_helper->redirector('index', 'login', 'default');
       }
    }

    //Acciones referidas al index
    public function indexAction() {
        $this->view->title                  = "Consultas \ Materias Pendientes";
        $this->view->filters                = $this->Une_Filtros;
        $this->view->module                 = $this->Request->getModuleName();
        $this->view->controller             = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery       = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax         = $this->SwapBytes_Ajax;
        $this->view->SwapBytes_Crud_Action  = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search  = $this->SwapBytes_Crud_Search;       
    }

   public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {

            $this->SwapBytes_Ajax->setHeader();

            $json[]     = $this->SwapBytes_Jquery->setHtml('tableData', '');
            $json[]     = $this->SwapBytes_Jquery->setHtml('plantilla', '');  
            $this->ci = $this->_getParam('cedula');

            if ($this->ci == "" || $this->ci == NULL) {

                $HTML =   $this->SwapBytes_Html_Message->alert("El campo cédula esta vacio");    
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);

                $this->getResponse()->setBody(Zend_Json::encode($json));

            }elseif($this->usuarios->getUsuario($this->ci) == NULL){

                $HTML =   $this->SwapBytes_Html_Message->alert("Usuario invalido.");    
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);

                $this->getResponse()->setBody(Zend_Json::encode($json));

            }else{

                $this->usuario        = $this->usuarios->getUsuario($this->ci);
                $this->nombre         = $this->usuario['primer_nombre'].' '.$this->usuario['segundo_nombre'].' '.$this->usuario['primer_apellido'].' '.$this->usuario['segundo_apellido'];





                $this->sede           = $this->inscripciones->getUltimaSede($this->ci);
                $this->sedeN          = $this->sedes->getSedeName($this->sede);
                $this->escuela        = $this->recordacademico->getUltimaEscuelaCursada($this->ci)[0]['pk_atributo'];
                $this->ultimaEscuelaN = $this->escuelas->getEscuelaName($this->escuela);
                $this->ultimoperiodo =  $this->recordacademico->getUltimoPeriodoInscrito($this->ci)[0]['periodo'];

                //var_dump($periodos);die;
                $this->sedes      =     $this->sedes->getSelectSede($this->ci,$this->ultimoperiodo);
                $this->escuelas   =     $this->escuelas->getEscuelaInscrita($this->ci,$this->ultimoperiodo);
               // var_dump($this->escuelas);die;
                $this->pensums    =     $this->pensums->getPensumInscrito($this->ci,$this->ultimoperiodo,$this->escuelas[0]['pk_atributo']);
                //var_dump($this->pensums);die;
                //var_dump($this->usuarios->getUsuario($ci));die;
                $this->pensumN        = $this->recordacademico->getPensum($this->pensums[0]['fk_pensum'])[0]['nombre'];

                $this->cursadas       = $this->recordacademico->materiasCursadas($this->ci, $this->pensums[0]['fk_pensum'])[0]['materias'];
                $this->porCursar      = $this->recordacademico->materiasPorCursar($this->ci, $this->pensums[0]['fk_pensum'])[0]['materias'];
                $this->uca            = $this->recordacademico->getUnidadesDeCreditoAprobadas($this->ci,$this->pensums[0]['fk_pensum'],$this->escuela,$this->sede)[0]["uc"];
                $data = $this->usuarios->getMateriasPendientes($this->ci,$this->ultimoperiodo,$this->sedes[0]["fk_estructura"],$this->escuelas[0]['pk_atributo'],$this->pensums[0]["fk_pensum"]);
                
                foreach ($data as $key => $value) {
                
                    if($value['estado'] == 'Pendiente') {                         
                      array_push($data[$key], "$(this).parent().addClass('bck_white');");

                    }
                    if($value['estado'] == 'Inscrita') { 
                      array_push($data[$key],"$(this).parent().addClass(\"bck_green\");"); 
                    }
                    if($value['estado'] == 'Retirada' || $value['estado'] == 'Reprobada') { 
                        array_push($data[$key],"$(this).parent().addClass(\"bck_red\");");  
                    }                   
                }

                $HTML = "<table  id='tblFiltro' align='margin-left'><div class='datosEstudiante' id='datosEstudiante' align='margin-left'><div>Universidad Nueva Esparta</div><div>Caracas - Venezuela</div><div>Estudiante: <strong class='textGray strong' id='est_nombre'>".$this->nombre."</strong></div><div>C.I.: <strong class='textGray strong'align='margin-left' id='est_ci'>".$this->ci."</strong></div><div class='strong'>Ultima sede asociada: <span class='textGray' id='est_sede'>".$this->sedeN."</span></div><div class='strong'>Ultima escuela asociada: <span class='textGray' id='est_escuela'>".$this->ultimaEscuelaN."</span></div><div class='strong'>Período Actual: <span class='textGray' id='est_escuela'>".$this->ultimoperiodo."</span></div><div class='strong'>Pensum cursando: <span class='textBlue strong' id='est_pensumN'>".$this->pensumN."</span></div><div class='strong'>Total de asignaturas cursadas: <span class='textBlue strong' id='est_cursadas'>".$this->cursadas."</span></div><div class='strong'>Total de asignaturas faltantes por cursar: <span class='textRed strong' id='est_porcursar'>".$this->porCursar."</span></div><div class='strong'>Total unidades de credito aprobadas: <span class='textBlue strong' id='est_uca'>".$this->uca."</span></div></div><div id='tableData'align='margin-left'>&nbsp;</div></table>";        
                $json[] = '$("#plantilla").html("'.$HTML.'");';

                if(isset($data) && count($data) > 0) {

                  $property_table = array('class'  => 'tableData',
                                             'width'  => '870px',
                                             'column' => 'disponible');
                  $property_column = array(array('name'     => 'Codigo',
                                                     'column'   => 'codigo',
                                                     'width'    => '75px',
                                                     'rows'     => array('style' => 'text-align:center')),
                                               array('name'     => 'Asignatura',
                                                     'column'   => 'materia',
                                                     'width'    => '185px',
                                                     'rows'     => array('style' => 'text-align:center')),
                                               array('name'     => 'Estado',
                                                     'column'   => 'estado',
                                                     'width'    => '75px',
                                                     'rows'     => array('style' => 'text-align:center','class' => 'estado')),
                                               array('name'     => 'Periodo',
                                                     'column'   => 'valor',
                                                     'width'    => '90px',
                                                     'rows'     => array('style' => 'text-align:center')),
                                               array('name'     => 'UC',
                                                     'column'   => 'uc',
                                                     'width'    => '50px',
                                                     'rows'     => array('style' => 'text-align:center')),
                                               array('name'     => 'Prelaciones',
                                                     'column'   => 'prelacion',
                                                     'width'    => '185px',
                                                     'rows'     => array('style' => 'text-align:center')),
                                               array('name'     => 'UC Necesarios',
                                                     'column'   => 'prelacionuc',
                                                     'width'    => '37px',
                                                     'rows'     => array('style' => 'text-align:center')),
                                               );
                    // Generamos la lista.
                    $HTML   = $this->SwapBytes_Crud_List->fill($property_table, $data, $property_column);
                    $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                      
                }else {

                    $HTML =  $this->SwapBytes_Html_Message->alert("Esta persona ya se graduó.");
                    $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);   

                }
                     $this->getResponse()->setBody(Zend_Json::encode($json));

            }
        }
    }
}

?>