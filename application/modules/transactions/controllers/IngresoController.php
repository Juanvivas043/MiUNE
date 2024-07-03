<?php

class Transactions_IngresoController extends Zend_Controller_Action {


    private $Title = 'Transacciones \ Recibos de Pago';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Forms_Changepass');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Horarios');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbView_Secciones');
        Zend_Loader::loadClass('Forms_Ingreso');


        $this->usuarios        = new Models_DbTable_Usuarios();
        $this->secciones       = new Models_DbView_Secciones();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->filtros         = new Une_Filtros();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->inscripciones   = new Models_DbTable_Inscripciones();
        $this->horarios        = new Models_DbTable_Horarios();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();

        $this->CmcBytes_Profit = new CmcBytes_Profit();
        $this->CmcBytes_Redirect = new CmcBytes_Redirect();

        $this->logger = Zend_Registry::get('logger');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        //$this->redirect_session = new Zend_Session_Namespace('redirect_session');

        //filtro
        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(true, false, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);

        $regresar = "<button id='btnAsignar' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnReturn' role='button' aria-disabled='false'>Asignar</button>";

        $this->SwapBytes_Crud_Action->addCustum($regresar);

        $this->SwapBytes_Crud_Search->setDisplay(true);

        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, false, false, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, false, false, false, false, false);



        $this->view->form = new Forms_Ingreso();
        $this->SwapBytes_Form->set($this->view->form);
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
        $this->_params['redirect'] = $this->redirect_session->params;

        //$this->custom = $this->inscripciones->getPruebasDiagnostico();

        $this->tmptbl_cursos = "dblink('dbname=Moodle_CGV port=5432 host=192.168.1.10 user=Moodle_CGV password=c4r4c4s',
                               '
                               select c.id, c.fullname
                               FROM mdl_course c
                               WHERE c.category = 184
                               order by 1 DESC
                               ') as q(id integer,name varchar)
                                ";

        $this->tmptbl_pruebas = "dblink('dbname=Moodle_CGV port=5432 host=192.168.1.10 user=Moodle_CGV password=c4r4c4s',
                                '
                                select distinct  q.id, q.name, c.id
                                from mdl_quiz q
                                JOIN mdl_course c ON c.id = q.course
                                ') as q(id integer,name varchar, cid integer)";


        $this->tablas = Array('Curso'  =>   Array($this->tmptbl_cursos,
                                                  null,
                                                  Array('q.id','q.name'),//columnas
                                                  '1 DESC'
                                                  ),
                              'Prueba'  =>   Array($this->tmptbl_pruebas,
                                                  Array('q.cid  = ##Curso##'),
                                                  Array('q.id','q.name'),//columnas
                                                  '1 DESC'
                                                  ),
            
                              'Periodo'  => Array('tbl_periodos',
                                                  null,
                                                  Array('pk_periodo',
                                                        'lpad(pk_periodo::text, 4, \'0\') || \', \' || to_char(fechainicio, \'MM-yyyy\') || \' / \' ||  to_char(fechafin, \'MM-yyyy\')'),
                                                  'DESC'),

                              'Sede'     => Array('vw_sedes',
                                                  null,
                                                  Array('pk_estructura','nombre'),
                                                  '1 ASC'
                                                  ),
                              'Escuela'    => Array(Array('tbl_pensums pe',
                                                       'tbl_asignaturas ag',
                                                       'tbl_asignaciones aon',
                                                       'tbl_estructuras est',
                                                       'tbl_estructuras est1',
                                                       'vw_escuelas esc'),
                                                 Array('ag.fk_pensum = pe.pk_pensum',
                                                       'aon.fk_asignatura = ag.pk_asignatura',
                                                       'est.pk_estructura = aon.fk_estructura',
                                                       'est1.pk_estructura = est.fk_estructura',
                                                       'esc.pk_atributo = pe.fk_escuela',
                                                       'aon.fk_periodo = ##Periodo##',
                                                       'est1.fk_estructura = ##Sede##'),
                                                 Array('pe.fk_escuela','esc.escuela'),
                                                 '1 ASC'

                                                ),
                              
                              'Pensum'  => Array('tbl_pensums',
                                                 Array('fk_escuela = ##Escuela##'),
                                                  Array('pk_pensum',
                                                        'nombre'),
                                                  '1 DESC'),
                              'Semestre'  => Array(Array('tbl_pensums pe',
                                                         'tbl_asignaturas ag',
                                                         'vw_semestres sem'),
                                                  Array('ag.fk_pensum = pe.pk_pensum',
                                                        'sem.pk_atributo = ag.fk_semestre',
                                                        'pe.pk_pensum = ##Pensum##'),
                                                  Array('sem.pk_atributo',
                                                        'sem.id'),
                                                  '1 ASC'),
                              'Secciones' => Array(Array('tbl_asignaciones aon',
                                                         'tbl_asignaturas ag',
                                                         'tbl_pensums pe',
                                                         'vw_secciones sec'),
                                                   Array('ag.pk_asignatura = aon.fk_asignatura',
                                                         'pe.pk_pensum = ag.fk_pensum',
                                                         'sec.pk_atributo = aon.fk_seccion',
                                                         'pe.pk_pensum = ##Pensum##',
                                                         'aon.fk_semestre = ##Semestre##',
                                                         'aon.fk_periodo = ##Periodo##'),
                                                   Array('sec.pk_atributo',
                                                         'sec.valor'),
                                                   '1 ASC'
                                                  ),
                              'Materias'  => Array(Array('vw_materias ma','tbl_asignaturas ag','tbl_asignaciones aon','tbl_pensums pe','vw_secciones sec'),
                                                 Array('ag.fk_materia = ma.pk_atributo',
                                                       'aon.fk_asignatura = ag.pk_asignatura',
                                                       'pe.pk_pensum = ag.fk_pensum',
                                                       'sec.pk_atributo = ##Secciones##',
                                                       'pe.pk_pensum = ##Pensum##',
                                                       'ag.fk_semestre = ##Semestre##',
                                                       'aon.fk_periodo = ##Periodo##'),
                                                  Array('max(pk_asignacion)',
                                                        'ma.materia'),
                                                  '2 ASC',
                                                  'GROUP BY ma.materia'),



            );


       $this->needsChangePass = $this->usuarios->samePassword($this->authSpace->userId);

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

            $this->view->title   = $this->Title;
            $this->view->filters = $this->filtros;
            $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
            $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
            $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
            $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
            $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
            $this->view->search_span           = 2;
            $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
            $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();




        }


        public function asignarAction(){
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();

                $Data = $this->_getParam('data');
                $filterData = $this->_getParam('filter');
                $Filter = $this->SwapBytes_Uri->queryToArray($filterData);
                $Rows = $this->SwapBytes_Uri->queryToArray($Data);
                //var_dump($Filter);
                $seccion = $this->secciones->getSeccion($Filter['Secciones']);

                $materia = $this->horarios->getNombreMateria($Filter['Materias']);
                //var_dump($materia);
                $cedulas = implode(',', $Rows['chkUsuario']);
                $cantidad = count($Rows['chkUsuario']);
                $dataRow['cedulas'] = $cedulas;

                if($cantidad > 0){

                     $json[] = "$('#frmModal').find('#id').after('Se inscribira la materia: " . $materia[0]['materia'] . " en la seccion ". $seccion[0]['valor'] ." a ".$cantidad. " personas.');";
                     $json[] = "$('#frmModal').parent().find('button:contains(\'Guardar\')').children().html('Continuar')";
                     $json[] = "$('#frmModal').parent().find('button:contains(\'Continuar\')').show();";

                }else{

                    $json[] = "$('#frmModal').find('#id').after('No ha seleccionado ningun alumno para inscribir.');";
                    $json[] = "$('#frmModal').parent().find('button:contains(\'Guardar\')').children().html('Continuar')";
                    $json[] = "$('#frmModal').parent().find('button:contains(\'Continuar\')').show();";
                    $json[] = "$('#frmModal').parent().find('button:contains(\'Continuar\')').hide();";

                }



                $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter('frmModal');
                $this->SwapBytes_Crud_Form->setJson($json);
                //$this->SwapBytes_Crud_Form->setWidthLeft('120px');
                $this->SwapBytes_Crud_Form->getAddOrEditLoad();
            }
        }



        public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

                $dataRow = $this->_params["modal"];

                $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
                $this->SwapBytes_Crud_Form->getAddOrEditConfirm();

		}
	}

        public function addoreditresponseAction() {
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();

                        $dataRow = $this->_params['modal'];
                        $filtro = $this->_params['filters'];
                        $result = $this->inscripciones->inscribirMateriaMultiplesUsuarios($dataRow['cedulas'],$filtro['Materias']);

                        $json[] = "jQuery('#frmModal').parent().hideLoading();";
                        $this->SwapBytes_Crud_Form->setJson($json);
			$this->SwapBytes_Crud_Form->getAddOrEditEnd();

		}
        }



        public function filterAction(){
            $this->SwapBytes_Ajax->setHeader();
            $select = $this->_getParam('select');
            $values = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));

            if(!$select || !$values){
                $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,null,1,null);
                $json[] = "$('#select_filters').children().children(':eq(0)').children().last().after('<td class=\'label\'><label>Indice < </label></td>');";
                $json[] = "$('#select_filters').children().children(':eq(1)').children().last().after('<td class=\'select\'><input value=\'14\' size=\'3\' id=\'Nota\' name=\'Nota\'></input></td>');";
                $json[] = "$('#select_filters').children().children(':eq(0)').children().last().after('<td class=\'label\'><label>Prueba <</label></td>');";
                $json[] = "$('#select_filters').children().children(':eq(1)').children().last().after('<td class=\'select\'><input value=\'12\' size=\'3\' id=\'Notaprueba\' name=\'Notaprueba\'></input></td>');";

            }else{
                $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,$values,null,$select);
            }

            if(isset($this->custom)){

                $json[] = $this->CmcBytes_Filtros->addCustom('Prueba', $this->custom);
            }

             $this->getResponse()->setBody(Zend_Json::encode($json));
        }

        public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json   = array();
            $searchData = $this->_getParam('buscar');

            //$this->usuvehiculossorteos->setSearch($searchData);

            $data = $this->_params['filters'];


            $rows = $this->inscripciones->getNuevoIngresoporNota($data['Periodo'], $data['Escuela'], $data['Nota'], $data['Sede'], $data['Prueba'], $data['Notaprueba'],$data['Materias']);
            //$rows = $this->inscripciones->getNuevoIngresoporNota($data['Periodo'], $data['Escuela'], $data['Nota']);
            //var_dump($rows);
            

            //$preloded = $this->recibos->getall($this->authSpace->userId);


            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'fk_usuario',
                                       'primary' => true,
                                        'hide' => true),
                                 array('name'    => array('control' => array('tag'        => 'input',
                                                                         'type'       => 'checkbox',
                                                                         'name'       => 'chkSelectDeselect')),
                                   'column'  => 'nc',
                                   'width'   => '20px',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'control' => array('tag'   => 'input',
                                                      'type'  => 'checkbox',
                                                      'name'  => 'chkUsuario',
                                                      'value' => '##fk_usuario##')),
                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'function' => 'rownum',
                                       'rows'    => array('style' => 'text-align:right')),
                                 array('name'    => 'Cedula',
                                       'width'   => '30px',
                                       'column'  => 'fk_usuario',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Nombre',
                                       'width'   => '230px',
                                       'column'  => 'nombre',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Apellido',
                                       'width'   => '230px',
                                       'column'  => 'apellido',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Indice',
                                       'width'   => '50px',
                                       'column'  => 'nota',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Nota Prueba',
                                       'width'   => '50px',
                                       'column'  => 'nota',
                                       'rows'    => array('style' => 'text-align:left')),
                                 

                                 );



                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML) . ";";
                $this->logger->log($rows,ZEND_LOG::INFO);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkUsuario');


            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen inscritos en el sorteo para este turno.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }
            
            $this->getResponse()->setBody(Zend_Json::encode($json));

        }

    }


}


?>

