<?php
class Reports_CoincidenciaController extends Zend_Controller_Action {
    private $Title = 'Reportes / Fusión De Materias';
    private $FormTitle_Detalle = 'Ver los datos de la materia inscrita del estudiante';
    private $FormTitle_Modificar = 'Modifica los datos de la materia inscrita del estudiante';
    private $FormTitle_Info = 'Informaci&oacute;n';
    
    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Models_DbTable_Asignaturas');
        Zend_Loader::loadClass('Models_DbTable_Materiasestados');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Forms_Recordacademico');
        Zend_Loader::loadClass('Forms_Agregarmateria');
        Zend_Loader::loadClass('Models_DbView_Semestres');

        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');

        $this->asignaciones = new Models_DbTable_Asignaciones();
        $this->inscripciones = new Models_DbTable_Inscripciones();
        $this->usuarios = new Models_DbTable_Usuarios();
        $this->asignaturas = new Models_DbTable_Asignaturas();
        $this->materiasestados = new Models_DbTable_Materiasestados();
        $this->periodos = new Models_DbTable_Periodos();
        $this->sedes = new Models_DbTable_Estructuras();
        $this->escuelas = new Models_DbTable_EstructurasEscuelas();
        $this->recordacademico = new Models_DbTable_Recordsacademicos();
        $this->grupo = new Models_DbTable_UsuariosGrupos();
        $this->filtros = new Une_Filtros();
        $this->vw_semestres = new Models_DbView_Semestres();

        $this->SwapBytes_Date = new SwapBytes_Date();
        $this->SwapBytes_Uri = new SwapBytes_Uri();
        $this->SwapBytes_Form = new SwapBytes_Form();
        $this->SwapBytes_Form_Agregar = new SwapBytes_Form();
        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search = new SwapBytes_Crud_Search();
        $this->SwapBytes_Html = new SwapBytes_Html();
        $this->SwapBytes_Html_Message = new SwapBytes_Html_Message();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask = new SwapBytes_Jquery_Mask();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();

        $this->SwapBytes_Ajax->setView($this->view);

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        /*
         * Mandamos a crear el formulario para ser utilizado mediante el AJAX.
         */
        $this->view->form = new Forms_Recordacademico();
        $this->view->form_agregar = new Forms_Agregarmateria();

        $this->SwapBytes_Form->set($this->view->form);
        $this->SwapBytes_Form->fillSelectBox('estado', $this->materiasestados->getSelect("'N/A'"), 'id', 'valor');


        $this->view->form = $this->SwapBytes_Form->get();
        //$this->view->form_agregar = $this->SwapBytes_Form->get();

        // $this->filtros->setDisplay(true, true, true, false, false, false, false, false, false);
        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);

        $this->SwapBytes_Crud_Action->setDisplay(true, true, false);
        $this->SwapBytes_Crud_Action->setEnable(true, true, false);
        //$this->SwapBytes_Crud_Action->addCustum('&nbsp;<select id="selEstado" name="selEstado" style="width:100px"></select>');

        $this->_params['modal'] = $this->SwapBytes_Crud_Form->getParams();

         $this->logger = Zend_Registry::get('logger');

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
                                                       'ee.fk_estructura = ##Sede##'),//'fk_estructura = 7','fk_estructura = ##sede##',
                                                 Array('ee.fk_atributo',
                                                       'es.escuela'),
                                                 'ASC'));
       $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));

        //var_dump($this->_params);
        $this->SwapBytes_Form_Agregar->set($this->view->form_agregar);
        $this->SwapBytes_Form_Agregar->fillSelectBox('estado', $this->materiasestados->getSelect("'N/A'"), 'id', 'valor');
        $semestres = $this->vw_semestres->get();
        $this->SwapBytes_Form_Agregar->fillSelectBox('fk_semestre', $semestres, 'pk_atributo', 'id');
        $this->SwapBytes_Crud_Search->setDisplay(false);
        
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    function preDispatch() {
       if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if (!$this->grupo->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

    /**
     * Crea la estructura base de la pagina principal.
     */
    public function indexAction() {
        $this->view->filters = $this->filtros;
        $this->view->title = $this->Title;
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();

        $this->view->SwapBytes_Ajax = $this->SwapBytes_Ajax;
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Jquery_Ui_Form = $this->SwapBytes_Jquery_Ui_Form;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
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


    /**
     * Accion que llena el objeto HTML de tipo SELECT con los datos del "sede"
     * y es usado como filtro de la lista.
     */
    public function sedeAction() {
        $this->filtros->getAction();
    }

    /**
     * Accion que llena el objeto HTML de tipo SELECT con los datos del "escuela"
     * y es usado como filtro de la lista.
     */
    public function escuelaAction() {
        $this->filtros->getAction();
    }

   
    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
           
             
            $queryString = $this->_getParam('filters');
            $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);
            $periodo = $queryArray['Periodo'];
            $sede = $queryArray['Sede'];
            $escuela = $queryArray['Escuela'];
            $pageNumber = $this->_getParam('page', 1);
            $itemPerPage = 10;
            $pageRange = 10;
           
           //Definimos los valores
           $this->asignaciones->setSearch($searchData);
           $paginatorCount = $this->asignaciones->getSQLCount($periodo,$sede,$escuela);
           $rows =  $this->asignaciones->getCoincidenciaMateria($periodo,$sede,$escuela,$itemPerPage,$pageNumber);
           
               
               // Definimos las propiedades de la tabla.
            if (isset($rows) && count($rows) > 0) {
            $table = array('class' => 'tableData',
                'width' => '1130px');

            // Creamos la lista de elementos que provienen de la consulta.
            $columns = array(array('name'  => 'Sem',
                                   'column'  => 'semestre',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'width'   => '10px',
                                   'primary' => true,
                                   'hide'   => false ),
                
                
                             array('name'    => 'Materia',
                                   'rows'    => array('style' => 'text-align:left'),
                                   'width'   => '5px',
                                   'column'  => 'materia'
                                 ),
                                   
                
                             array('name'    => 'Sec.',
                                  'rows'    => array('style' => 'text-align:center'),
                                   'width'   => '20px',
                                   'column'  => 'seccion'
                                 ),
                
                            array('name'    => 'Pensum',
                                   'rows'    => array('style' => 'text-align:center'),
                                   'width'   => '20px',
                                   'column'  => 'pensum'),
                            
                            array('name'    => 'Escuela',
                                   'width'   => '20px',
                                   'column'  => 'escuelas'), 
                            
                            array('name'    => 'Profesor',
                                   'width'   => '5px',
                                   'column'  => 'profesor'), 
                            
                           array('name' => 'día',
				'width' => '20px',
				'column' => 'dia',
				'rows' => array('style' => 'text-align:left')), 
                                             
                            
                            array('name' => 'Horario',
				'width' => '60px',
				'column' => 'horario',
				'rows' => array('style' => 'text-align:center')),
                            
                            array('name'    => 'Edf.',
                                   'width'   => '15px',
                                   'column'  => 'edificio'),
                            
                            array('name'    => 'Aula',
                                  'rows' => array('style' => 'text-align:center'),
                                   'width'   => '15px',
                                   'column'  => 'salones'),
                           array('name' => 'cupos',
				'width' => '10px',
				'column' => 'cupos',
                                'rows' => array('style' => 'text-align:center'))
                );
            // Generamos la lista.
            $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange,$paginatorCount);

            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            
            //$json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkRecordAcademico');

            $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }
    }

    
   
    
   
}