<?php
class Transactions_MateriasingresoController extends Zend_Controller_Action {

 private $Title = 'Transacciones \ Materias Ingreso';

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Horarios');
        Zend_Loader::loadClass('Models_DbView_Secciones');
        Zend_Loader::loadClass('Forms_Materiasingreso');


        $this->horarios        = new Models_DbTable_Horarios();
        $this->secciones       = new Models_DbView_Secciones();
        $this->usuarios        = new Models_DbTable_Usuarios();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->filtros         = new Une_Filtros();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->inscripciones   = new Models_DbTable_Inscripciones();

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
        $this->filtros->setDisabled(true, true, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);



        $this->SwapBytes_Crud_Search->setDisplay(true);

        //botones
        $this->SwapBytes_Crud_Action->setDisplay(true, false, false, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(true, false, false, false, false, false);

        $regresar = "<button id='btnAsignar' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnReturn' role='button' aria-disabled='false'>Asignar</button>";

        $this->SwapBytes_Crud_Action->addCustum($regresar);

        $this->view->form = new Forms_Materiasingreso();

        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
        $this->_params['redirect'] = $this->redirect_session->params;

        //$this->custom = $this->inscripciones->getPruebasDiagnostico();
        $this->tablas = Array('periodo'  => Array('tbl_periodos',
                                                  null,
                                                  Array('pk_periodo',
                                                        'lpad(pk_periodo::text, 4, \'0\') || \', \' || to_char(fechainicio, \'MM-yyyy\') || \' / \' ||  to_char(fechafin, \'MM-yyyy\')'),
                                                  'DESC'),
                               'sede'    => Array('vw_sedes',
                                                  null,
                                                  Array('pk_estructura',
                                                        'nombre'),
                                                  ),
                               'escuela' => Array('vw_escuelas',
                                                 'pk_atributo <> 920',
                                                 Array('pk_atributo',
                                                       'escuela'),
                                                 '1 ASC'),
                               'pensum'  => Array('tbl_pensums',
                                                 Array('fk_escuela = ##escuela##'),
                                                  Array('pk_pensum',
                                                        'nombre'),
                                                  '1 DESC'),
                               'seccion' => Array(Array('vw_secciones','tbl_asignaciones aon','tbl_asignaturas'),
                                                 Array("length(valor) = 1 ",
                                                       "pk_atributo = fk_seccion",
                                                       //"fk_periodo = ##periodo##",
                                                       "aon.fk_asignatura = pk_asignatura",
                                                       "aon.fk_semestre = 873"),
                                                 Array('pk_atributo',
                                                       'valor'),
                                                 '2 ASC')
                                );



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

                $cantidad = count($Rows['chkUsuario']);
                $seccion = $this->secciones->getSeccion($Filter['seccion']);
                $cupos = $this->horarios->getCuposRestantes($Filter['periodo'], $Filter['sede'], $Filter['escuela'], 873, $Filter['seccion']);


                $cedulas = implode(',', $Rows['chkUsuario']);
                if(empty($cedulas)){
                    $cedulas = $Rows['chkUsuario'];
                }

                $dataRow['id'] = $cantidad;
                $dataRow['cedulas'] = $cedulas;

                if($cantidad > 0){

                    foreach($cupos as $cup){

                    if ($cup['disp'] - $cantidad < 0){
                        $succes = false;
                        $materia = $cup['materia'];
                        break;
                    }else{
                        $succes = true;
                    }

                    }

                        if($succes == true){

                            $json[] = "$('#frmModal').find('#id').after('Se inscribiran todas las materias de primer semestre en la seccion: " . $seccion[0]['valor'] . " a ".$cantidad. " personas.');";
                            $json[] = "$('#frmModal').parent().find('button:contains(\'Guardar\')').children().html('Continuar')";
                            $json[] = "$('#frmModal').parent().find('button:contains(\'Continuar\')').show();";


                        }else{
                            $json[] = "$('#frmModal').find('#id').after('No existen cupos suficientes para inscribir a todos los alumnos seleccionados en la materia: <b>" . $materia ."</b>');";
                            $json[] = "$('#frmModal').parent().find('button:contains(\'Guardar\')').children().html('Continuar')";
                            $json[] = "$('#frmModal').parent().find('button:contains(\'Continuar\')').show();";
                            $json[] = "$('#frmModal').parent().find('button:contains(\'Continuar\')').hide();";
                        }
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
            $dataRow['id'] = 1;
            $periodo = $this->_params['filters']['periodo'];
            $sede = $this->_params['filters']['sede'];
            $escuela = $this->_params['filters']['escuela'];
            $seccion = $this->secciones->getSeccion($this->_params['filters']['seccion']);
            $horariovalido = $this->horarios->validarSecciones($periodo, 873, $escuela, $sede, $seccion[0]['valor']);
            $dataRow['id'] = $this->_params['modal']['id'];
            $dataRow['cedulas'] = $this->_params['modal']['cedulas'];

            if(isset($horariovalido[0])){

                foreach($horariovalido as $key => $hor){

                    if($key < 1){

                        $mat = $hor['materia'];
                    }else{

                        $mat .= ', ' . $hor['materia'];
                    }

                }

                $message = "existe un problema en la distribuciond de secciones para la(s) materias: " . $mat;
                $this->SwapBytes_Crud_Form->getDialog('Error al intentar asignar materias', $message, swOkOnly);
            }else{
                $this->SwapBytes_Crud_Form->setProperties($this->view->form,$dataRow);
                $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
            }

          }else{
              //echo 'sdsdsd';
              //$this->SwapBytes_Crud_Form->getAddOrEditConfirm();
          }
	}


        public function addoreditresponseAction() {
            if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader();

                $periodo = $this->_params['filters']['periodo'];
                $sede = $this->_params['filters']['sede'];
                $escuela = $this->_params['filters']['escuela'];
                $seccion = $this->secciones->getSeccion($this->_params['filters']['seccion']);
                $dataRow['id'] = $this->_params['modal']['id'];
                $dataRow['cedulas'] = $this->_params['modal']['cedulas'];
                //var_dump($dataRow['id']);

                $lasced = "ARRAY[" . $dataRow['cedulas'] . "]";

                $this->inscripciones->actualizarSemestre($sede,$seccion[0]['valor'],$escuela,$periodo,$dataRow['id'],$dataRow['cedulas']);
                $test = $this->inscripciones->agregarMateriasPrimerSemestre($sede,$seccion[0]['valor'],$escuela,$periodo,$dataRow['id'],$lasced, NULL);
                //var_dump($test);

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

            //var_dump($data);

            $materias = $this->horarios->getCuposMaterias($data['periodo'], $data['sede'], $data['escuela'], 873, $data['seccion'],$data['pensum']);
            $rows = $this->inscripciones->getNuevoAllNuevoIngreso($data['periodo'],$data['sede'],$data['escuela']);

            //var_dump($materias);
            //$preloded = $this->recibos->getall($this->authSpace->userId);

            if(isset($materias) && count($materias) > 0){
                //var_dump($materias);
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_usuario',
                                       'primary' => true,
                                        'hide' => true),
                                 array('name'    => 'Materia',
                                       'width'   => '300px',
                                       'column'  => 'materia',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Seccion',
                                       'width'   => '50px',
                                       'column'  => 'valor',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Inscritos',
                                       'width'   => '50px',
                                       'column'  => 'inscritos',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Cupos',
                                       'width'   => '50px',
                                       'column'  => 'cupos',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Restantes',
                                       'width'   => '50px',
                                       'column'  => 'restantes',
                                       'rows'    => array('style' => 'text-align:center')),

                                 );



                $HTML1   = $this->SwapBytes_Crud_List->fill($table, $materias, $columns);

                $json[] = $this->SwapBytes_Jquery->setHtml('tableDatasec', $HTML1) . ";";
                $json[] = "$('#btnAsignar').attr('disabled','');";
                $json[] = "$('#btnAsignar').show();";
            }else{
                $HTML1  = $this->SwapBytes_Html_Message->alert("<center>No existen materias para la seccion seleccionada,<br> por lo que no podra inscribir en esta seccion.</center>");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableDatasec', $HTML1);
                $json[] = "$('#btnAsignar').attr('disabled','disabled');";
                $json[] = "$('#btnAsignar').hide();";
            }


            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'cedula',
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
                                                      'value' => '##cedula##')),
                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'function' => 'rownum',
                                       'rows'    => array('style' => 'text-align:right')),
                                 array('name'    => 'Cedula',
                                       'width'   => '30px',
                                       'column'  => 'cedula',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Nombre',
                                       'width'   => '230px',
                                       'column'  => 'nombre',
                                       'rows'    => array('style' => 'text-align:left')),
                                 array('name'    => 'Apellido',
                                       'width'   => '230px',
                                       'column'  => 'apellido',
                                       'rows'    => array('style' => 'text-align:left'))

                                 );



                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML) . ";";
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkUsuario');


            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen alumnos de ingreso sin materias asignadas en el periodo.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));

        }

    }


}


?>
