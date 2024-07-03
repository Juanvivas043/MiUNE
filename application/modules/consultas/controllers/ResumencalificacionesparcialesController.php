
<?php

class Consultas_ResumenCalificacionesParcialesController extends Zend_Controller_Action {

    public $title = 'Resumen Calificaciones Parciales';
    /* Initialize action controller here */
    public function init() {

        /* Carga de Recursos */
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_CalificacionesParciales');
        Zend_Loader::loadClass('Models_DbTable_Certificadocompetencia');
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Une_Cde_transactions_CalificacionesParciales');
        /*Inicializo los Modelos*/
        $this->grupo           			= new Models_DbTable_UsuariosGrupos();
        $this->calificaciones           = new Models_DbTable_CalificacionesParciales();
        $this->Certificadocompentencia = new Models_DbTable_Certificadocompetencia();
        $this->asignaciones    			= new Models_DbTable_Asignaciones();
        $this->recordAcademico 			= new Models_DbTable_Recordsacademicos();
        /* Inicializo las librerias*/
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->filtros         			= new Une_Filtros();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        /* Libreria de Calififcaciones Parciales */
        $this->calificacionesParciales = new Une_Cde_Transactions_CalificacionesParciales (
            $this->recordAcademico, $this->asignaciones, $this->SwapBytes_Jquery
        );
        /*Filtros*/
        $this->filtros->setDisplay(true, true, true, true, true, true, false, true);
        $this->filtros->setDisabled(false, false, false, false, false, true, false);
        $this->filtros->setRecursive(true, true, true, true, true, true, false, true);
        /*Botones de Acciones*/
        $this->SwapBytes_Crud_Action->setDisplay(true, true);
        $this->SwapBytes_Crud_Action->setEnable(true, true);
        $this->SwapBytes_Crud_Search->setDisplay(false);
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * si no es asi, redirecciona a al modulo de login.
     */
    public function preDispatch() {

        if(!Zend_Auth::getInstance()->hasIdentity()) {
        $this->_helper->redirector('index', 'login', 'default');
        }

        if(!$this->grupo->haveAccessToModule()) {
                $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

    /** Accion de Carga de la Vista **/
    public function indexAction() {

        $this->view->title                  = $this->title;
        $this->view->filters                = $this->filtros;
        $this->view->SwapBytes_Ajax         = $this->SwapBytes_Ajax;
        $this->view->SwapBytes_Jquery       = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Crud_Action  = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search  = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax->setView($this->view);
    }

    public function periodoAction() {
        $this->filtros->getAction(array('usuario' => $this->AuthSpace->userId,'regimen'=>'true'));
    }
    public function sedeAction() {
        $this->filtros->getAction(array('periodo'));
    }

    public function pensumAction() {
        $this->filtros->getAction(array( 'periodo', 'sede', 'escuela'));
    }

    public function semestreAction() {
        $this->filtros->getAction(array('periodo', 'sede', 'escuela', 'pensum'));
    }

    public function materiaAction() {
        $this->filtros->getAction(array('periodo', 'sede', 'escuela', 'pensum', 'semestre'));
    }

    public function seccionAction() {
        $this->filtros->getAction(array('periodo', 'sede', 'escuela', 'pensum', 'semestre', 'materia'));
    }

    public function escuelaAction() {
        $this->filtros->getAction(array('periodo', 'sede'));
    }

    public function listAction() {

        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json     = array();
            $filtros  = $this->filtros->getParams();
            $estudiantes     = $this->recordAcademico->getEstudiantes($filtros);
            if(isset($estudiantes) && count($estudiantes) > 0) {
                // Existen Estudiantes
                // Definimos las propiedades de la tabla.
                $table = array('class'  => 'tableData',
                    'width'  => 'auto',
                    'column' => 'estado',
                    /*Colores de las filas dependen de el estado de el record'*/
                    'rows'   => array('conditions' => array( 863 => array('equal'=> '863',
                    'properties' => array('class' => 'retirado')),
                    862 => array('equal'      => '862',
                    'properties' => array('style' => 'background-color:#99FF99;color:#666666;')),
                    864 => array('equal'      => '864',
                    'properties' => array('style' => 'background-color:#FFF;color:#000000;')),
                    1699 => array('equal'      => '1699',
                    'properties' => array('style' => 'background-color:#99FF99;color:#666666;')),
                    1266 => array('equal'      => '1266',
                    'properties' => array('style' => 'background-color:#99FF99;color:#666666;')),
                )
            )
        );

                $columns = array(
                    array('column'  => 'pk_recordacademico',
                    'primary' => true,
                    'hide'    => true),
                    array('name'     => '#',
                    'width'    => '30px',
                    'function' => "rownum",
                    'rows'    => array('style' => 'text-align:right')),
                    array('name'    => 'C.I.',
                    'width'   => '60px',
                    'column'  => 'ci',
                    'rows'    => array('style' => 'text-align:center')),
                    array('name'    => 'Apellido',
                    'width'   => '150px',
                    'column'  => 'apellido'),
                    array('name'    => 'Nombre',
                    'width'   => '150px',
                    'column'  => 'nombre'),
                    array('name'    => 'Estado',
                    'width'   => '50px',
                    'column'  => 'estado',
                    'hide'=>'true',
                    'rows'    => array('style' => 'text-align:center')
                )
            );
                /* Interceptamos las evaluaciones del regimen
                 * con las calificaciones */
                $evaluaciones = $this->recordAcademico->getEvaluacionesParciales($filtros);
                $colors = array (20054 => '#DFF0ED', 20055 => '#E3FAF6' );
                $pkinasist = array();
                foreach ($evaluaciones as $evaluacion) {

						if (!$evaluacion['evaluable']) {
							array_push($pkinasist, $evaluacion['pk_atributo']);
						}
                    $abrev  = $evaluacion['evaluable'] ? " ({$evaluacion['maximo']})":'';
                    $columns[] = array('name'    => "{$evaluacion['abrev']}{$abrev}",
                        'column'  => "{$evaluacion['abrev']}",
                        'width'   => 'auto',
                        'rows'    => array('style' => 'text-align:center;'. "background:{$colors[$evaluacion['fk_lapso']]}"),
                    );
                }

                if (!empty($pkinasist)) {
                    $clases = $this->asignaciones->get_clases_asignacion_feriado($filtros);
                    
                    /* La siguiente línea se comenta para que no se tome en cuenta las inasistencias en este módulo.  
                    */

                    //$max = ceil($clases * Une_Cde_Transactions_CalificacionesParciales::$regla_insasistencias/100);
                    
                    /* La siguiente línea se descomenta para que no se tome en cuenta las inasistencias en este módulo.  
                    */
                    
                    $max = 100.0;
                    //die(var_dump($max));
                    if($clases && isset($clases) && count($clases) > 0) {
                        //Existen Clases
                        $json[] = $this->SwapBytes_Jquery->setHtml('clases', $clases);
                        $json[] = $this->SwapBytes_Jquery->setHtml('inasistencias', $max);
                        $json[] = $this->SwapBytes_Jquery->setShow('extra');

                        $columns[] = array (

                            'name'    => 'T.Inasist',
                            'column'  => 'tinasist',
                            'width'   => '20px',
                            'rows'    => array('style' => 'text-align:center;font-color:#000 !important;padding:0px !important;'),
                            'control' => array( 'tag' => 'div',
                            'html'       => '##tinasist##',
                            'conditions' => array(
                                array('callBack' => function ($value) use ($max) {return $value >= $max; },
                                    'properties' => array('style' => 'background-color:#FF8888; height:100%;padding:4px;')
                                )
                            )
                        )
                    );
                    }
                }
                $columns[] = array (

                        'name'    => 'Acu',
                        'column'  => 'total',
                        'width'   => '20px',
                        'rows'    => array('style' => 'text-align:center')
                );
                $columns[] = array (

                        'name'    => 'Final',
                        'column'  => 'final',
                        'width'   => '20px',
                        'rows'    => array('style' => 'text-align:center')
                );
                $rows = $this->calificaciones->getDinamicList($filtros, $evaluaciones, $max);
                $json[] = $this->SwapBytes_Jquery->setShow('divLeyenda');
                $HTML = $this->SwapBytes_Crud_List->fill(
                    $table, $rows, $columns
                );

            } else {
                $HTML = $this->SwapBytes_Html_Message->alert('No existen estudiantes inscritos.');
            }
            $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
}
