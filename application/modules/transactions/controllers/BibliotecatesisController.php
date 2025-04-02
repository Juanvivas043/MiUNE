<?php
/**
 * @todo Ocultar el boton "Ocultar" del formulario Ver.
 * @todo Ocultar el boton "Eliminar" del formulario Eliminar, solo cuando no se
 *       permita.
 */

//problema con las tesis que no tienen autores sale dos veces
//validaciones al agregar
//descargar el archivo
//modal al subir un archivo

class Transactions_BibliotecatesisController extends Zend_Controller_Action
{

    private $Title = 'Gestion Repositorio';
    private $periodo = '127';

    public function init()
    {
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Bibliotecatesis');
        Zend_Loader::loadClass('Forms_Bibliotecatesis');
        Zend_Loader::loadClass('Models_DbTable_Periodos');


        $this->periodos         = new Models_DbTable_Periodos();
        $this->agregar = new Models_DbTable_Bibliotecatesis();
        $this->usuario = new Models_DbTable_Usuarios();
        $this->grupo = new Models_DbTable_UsuariosGrupos();
        $this->filtros = new Une_Filtros();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();
        $this->SwapBytes_Date = new SwapBytes_Date();
        $this->SwapBytes_Uri = new SwapBytes_Uri();
        $this->SwapBytes_Form = new SwapBytes_Form();
        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
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
        $this->CmcBytes_profit = new CmcBytes_Profit();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->_params['modal'] = $this->SwapBytes_Crud_Form->getParams();
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->view->form = new Forms_Bibliotecatesis();
        $this->SwapBytes_Form->set($this->view->form);

        $this->tablas = array(

            'Publicacion' =>array('vw_estadospublicacionestesis', null,
                array('pk_atributo','estado'),'1 ASC'),
            
            'Modalidad' => array(
                'vw_tiporecurso',
                null,
                array('pk_atributo', 'tipo'),
                'DESC'
            ),

            'Periodo' => array(
                'tbl_periodos',
                null,
                array('pk_periodo', 'lpad(pk_periodo::text, 4, \'0\') || \', \' || to_char(fechainicio, \'MM-yyyy\') || \' / \' ||  to_char(fechafin, \'MM-yyyy\')'),
                'DESC'
            ),

            'Sede' => array(
                'vw_sedes',
                null,
                array('pk_estructura', 'nombre'),
                'DESC'
            ),

            'Escuela' => array(
                array('tbl_estructurasescuelas ee', 'vw_escuelas es'),
                array('ee.fk_atributo = es.pk_atributo', 'ee.fk_estructura = ##Sede##'),//'fk_estructura = 7','fk_estructura = ##sede##',
                array('ee.fk_atributo', 'es.escuela'),
                'ASC'
            ),              
        );

        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));


        $cedula = $this->authSpace->userId;
        $acceso = $this->agregar->getUsuariogrupo($cedula, $this->agregar->getAtributoAgregarTesisBiblioteca());

        if (!empty($acceso)) {
            $permiso = true;
        } else {
            $permiso = false;
        }
        $this->SwapBytes_Crud_Action->addCustum($Información);

        $this->SwapBytes_Crud_Action->setDisplay(true, true, $permiso, false, false, false, true);
        $this->SwapBytes_Crud_Action->setEnable(true, true, $permiso, false, false, false, true);

        $this->SwapBytes_Form->fillSelectBox('fk_sede', $this->agregar->getsedeform(), 'pk_estructura', 'sede');
        $this->SwapBytes_Form->fillSelectBox('fk_escuela', $this->agregar->get_escuela(), 'pk_atributo', 'escuela');
        $this->SwapBytes_Form->fillSelectBox('fk_jurado', $this->agregar->get_jurado(), 'pk_usuario', 'nombre');
        $this->SwapBytes_Form->fillSelectBox('fk_tutor', $this->agregar->get_jurado(), 'pk_usuario', 'nombre');
        $this->SwapBytes_Form->fillSelectBox('fk_institucion', $this->agregar->get_institucion(), 'pk_atributo', 'institucion');
        $this->SwapBytes_Form->fillSelectBox('fk_publicado', $this->agregar->get_publicacion(), 'pk_atributo', 'estado');
        $this->SwapBytes_Form->fillSelectBox('fk_tiporecurso', $this->agregar->get_tiporecurso(), 'pk_atributo', 'tipo');
        $this->SwapBytes_Form->fillSelectBox('fk_periodo', $this->periodos->getSelect() , 'pk_periodo', 'nombre');


        for ($x = 20; $x >= 0; $x--) {
            $this->view->form->fk_calificacion->addMultiOption($x, $x);

        }

    }


    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */

    function preDispatch()
    {
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
    public function indexAction()
    {
        $this->view->title = $this->Title;
        $this->view->filters = $this->filtros;
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();



    }


    public function filterAction()
    {
        $this->SwapBytes_Ajax->setHeader();
        $select = $this->_getParam('select');
        $values = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));

        if (!$select || !$values) {
            $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas, null, 1, null);
        } else {
            $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas, $values, null, $select);
        }
        $this->getResponse()->setBody(Zend_Json::encode($json));
    }

    public function listAction()
    {

        // Verificamos si es una llamada de tipo AJAX.
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();


            // Obtenemos los parametros necesarios que se esta pasando por POST, y
            // los valores necesarios de las variables que se utilizaran mas adelante.
            $pageNumber = $this->_getParam('page', 1);
            $searchData = $this->_getParam('buscar');

            // echo '<pre>';
            // var_dump($searchData);
            // echo '</pre>';
            // die();   
    

            $itemPerPage = 15;
            $pageRange = 10;
            $this->agregar->setSearch($searchData);
            

            $periodo = $this->_params['filters']['Periodo'];
            $sede = $this->_params['filters']['Sede'];
            $escuela = $this->_params['filters']['Escuela'];
            $estado = $this->_params['filters']['Publicacion'];
            $modalidad = $this->_params['filters']['Modalidad'];

            // Definimos los valores

            if ($periodo == null && $escuela == null && $sede == null && $estado == null && $modalidad == null) {
                $paginatorCount = $this->agregar->getSQLCount();
                $rows = $this->agregar->get_tesis($itemPerPage, $pageNumber);
                $rows = $this->mejorar_data($rows);

            } else if ($modalidad == null && $periodo == null && $escuela == null && $sede == null && !$estado == null) {

                $paginatorCount = $this->agregar->getSQLCountFilteredByEstado($estado);
                $rows = $this->agregar->get_tesisFilteredByEstado($itemPerPage, $pageNumber, $estado);
                $rows = $this->mejorar_data($rows);

            } else if ($periodo == null && $escuela == null && $sede == null && !$estado == null && !$modalidad == null) {

                $paginatorCount = $this->agregar->getSQLCountFilteredByEstadoxModalidad($estado, $modalidad);
                $rows = $this->agregar->get_tesisFilteredByEstadoxModalidad($itemPerPage, $pageNumber, $estado, $modalidad);
                $rows = $this->mejorar_data($rows);

            }  else if ($escuela == null && $sede == null && !$estado == null && !$modalidad == null && !$periodo == null) {

                $paginatorCount = $this->agregar->getSQLCountFilteredByEstadoxModalidadxPeriodo($estado, $modalidad, $periodo);
                $rows = $this->agregar->get_tesisFilteredByEstadoxModalidadxPeriodo($itemPerPage, $pageNumber, $estado, $modalidad, $periodo);
                $rows = $this->mejorar_data($rows);

            }  else if ($escuela == null && !$estado == null && !$modalidad == null && !$periodo == null && !$sede == null) {

                $paginatorCount = $this->agregar->getSQLCountFilteredByEstadoxModalidadxPeriodoxSede($estado, $modalidad, $periodo, $sede);
                $rows = $this->agregar->get_tesisFilteredByEstadoxModalidadxPeriodoxSede($itemPerPage, $pageNumber, $estado, $modalidad, $periodo, $sede);
                $rows = $this->mejorar_data($rows);

            } else {

                $paginatorCount = $this->agregar->getSQLCountFiltered($periodo, $sede, $escuela, $estado, $modalidad);
                $rows = $this->agregar->get_tesisFiltered($itemPerPage, $pageNumber, $periodo, $sede, $escuela, $estado, $modalidad);
                $rows = $this->mejorar_data($rows);

            }

            // Definimos las propiedades de la tabla.
            $table = array(
                'class' => 'tableData',
                'width' => '1300px'
            );

            $columns = array(
                array(
                    'column' => 'pk_tesis',
                    'primary' => true,
                    'hide' => true
                ),

                array(
                    'column' => 'palabrasclaves',
                    'hide' => true
                ),

                array(
                    'name' => 'COTA',
                    'width' => '50px',
                    'column' => 'cota',
                    'rows' => array('style' => 'text-align:right')
                ),

                array(
                    'name' => 'Titulo',
                    'width' => '400px',
                    'column' => 'titulo',
                    'rows' => array('style' => 'text-align:center')
                ),

                array(
                    'name' => 'Autor',
                    'width' => '200px',
                    'column' => 'autor',
                    'rows' => array('style' => 'text-align:center')
                ),

                array(
                    'name' => 'Tutor',
                    'width' => '200px',
                    'column' => 'tutor',
                    'rows' => array('style' => 'text-align:center')
                ),

                array(
                    'name' => 'Escuela',
                    'width' => '200px',
                    'column' => 'escuela',
                    'rows' => array('style' => 'text-align:center')
                ),

                array(
                    'name' => 'Jurado',
                    'width' => '260px',
                    'column' => 'jurado',
                    'rows' => array('style' => 'text-align:center')
                ),

                array(
                    'name' => 'Calificacion',
                    'width' => '20px',
                    'column' => 'calificacion',
                    'rows' => array('style' => 'text-align:center')
                ),

                array(
                    'name' => 'Publicacion',
                    'width' => '30px',
                    'column' => 'estado',
                    'rows' => array('style' => 'text-align:center')
                ),


            );




            // Generamos la lista.
            $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'VUR');
            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }


    public function viewAction()
    {
        // Obtenemos los parametros que se esperan recibir.

        $tesis = $this->getData($this->_params['modal']['id']);

        $autor = $this->agregar->getNumAutores($this->_params['modal']['id']);
        $jurado = $this->agregar->getRowJurado($this->_params['modal']['id']);

        // recreamos el formulario
        $cantidad_autores = count($autor);
        $cantidad_jurado = count($jurado);

        $pos_jurado = 9 + ($cantidad_autores) * 2;
        $pos_autor = 8;
        $this->SwapBytes_Form->fillSelectBox('fk_autor', $this->agregar->getAutor($this->periodo, $tesis[0]['fk_escuela']), 'pk_usuario', 'nombre');

        $this->crearCampo($cantidad_autores - 1, 'autor', $pos_autor, $tesis[0]['fk_escuela']); // creamos el autor
        $this->crearCampo($cantidad_jurado - 1, 'jurado', $pos_jurado, $escuela); // creamos el jurado

        // llenamos el formulario.

        //autores
        $pos = -1;
        foreach ($autor as $au) {
            if ($pos < 0) {
                $dataRow['fk_autor'] = $au['fk_autor'];
            } else {
                $dataRow['fk_autor' . $pos] = $au['fk_autor'];
            }
            $pos++;
        }

        $pos = -1;
        foreach ($jurado as $jut) {
            if ($pos < 0) {
                $dataRow['fk_jurado'] = $jut['fk_jurado'];
            } else {
                $dataRow['fk_jurado' . $pos] = $jut['fk_jurado'];
            }
            $pos++;
        }


        $dataRow['id'] = $this->_params['modal']['id'];
        $dataRow['cota'] = $tesis[0]['cota'];
        $dataRow['fecha'] = $tesis[0]['fecha'];
        $dataRow['fk_sede'] = $tesis[0]['fk_sede'];
        $dataRow['fk_periodo'] = $tesis[0]['fk_periodo'];
        $dataRow['serialrecurso'] = $tesis[0]['serialrecurso'];
        $dataRow['titulo'] = $tesis[0]['titulo'];
        $dataRow['resumen'] = $tesis[0]['resumen'];
        $dataRow['palabrasclaves'] = $tesis[0]['palabrasclaves'];
        $dataRow['fk_tutor'] = $tesis[0]['fk_tutor'];
        $dataRow['fk_escuela'] = $tesis[0]['fk_escuela'];
        $dataRow['fk_tiporecurso'] = $tesis[0]['fk_tiporecurso'];
        $dataRow['fk_institucion'] = $tesis[0]['fk_institucion'];
        $dataRow['fk_calificacion'] = $tesis[0]['calificacion'];
        $dataRow['fk_publicado'] = $tesis[0]['fk_publicado'];
        $dataRow['ubicacion'] = $tesis[0]['ubicacion'];
        $dataRow['pagina'] = $tesis[0]['pagina'];
        $dataRow['observacion'] = $tesis[0]['observacion'];
        $dataRow['archivo'] = $tesis[0]['archivo'];

        if ($dataRow['archivo'] == null) {
            $json[] = "document.getElementById('archivo-nombre').textContent = 'No Tiene Archivo Adjunto';";
        } else {
            $json[] = "document.getElementById('archivo-nombre').textContent = '" . basename($dataRow['archivo']) . "'";
        }

        $json[] = "document.getElementById('boton').style.display = 'none';";
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Tesis');
        $this->SwapBytes_Crud_Form->getView();


    }


    public function addoreditloadAction() {

        if (is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {

            $tesis = $this->getData($this->_params['modal']['id']);

            $autor = $this->agregar->getNumAutores($this->_params['modal']['id']);
            $jurado = $this->agregar->getRowJurado($this->_params['modal']['id']);

            // recreamos el formulario
            $cantidad_autores = count($autor);
            $cantidad_jurado = count($jurado);

            $pos_jurado = 9 + ($cantidad_autores) * 2;
            $pos_autor = 8;

            $this->SwapBytes_Form->fillSelectBox('fk_autor', $this->agregar->getAutor(), 'pk_usuario', 'nombre');


            if ($cantidad_autores > 1) {
                $this->crearCampo($cantidad_autores - 1, 'autor', $pos_autor, $tesis[0]['fk_escuela']); // creamos el autor  
            }

            if ($cantidad_jurado > 1) {
              $this->crearCampo($cantidad_jurado - 1, 'jurado', $pos_jurado, $escuela); // creamos el jurado  
            }

            // llenamos el formulario.
            //autores
            $pos = -1;
            foreach ($autor as $au) {
                if ($pos < 0) {
                    $dataRow['fk_autor'] = $au['fk_autor'];
                } else {
                    $dataRow['fk_autor' . $pos] = $au['fk_autor'];
                }
                $pos++;
            }

            $pos = -1;
            foreach ($jurado as $jut) {
                if ($pos < 0) {
                    $dataRow['fk_jurado'] = $jut['fk_jurado'];
                } else {
                    $dataRow['fk_jurado' . $pos] = $jut['fk_jurado'];
                }
                $pos++;
            }

            $dataRow['id'] = $this->_params['modal']['id'];
            $dataRow['cota'] = $tesis[0]['cota'];
            $dataRow['fk_sede'] = $tesis[0]['fk_sede'];
            $dataRow['fecha'] = $tesis[0]['fecha'];
            $dataRow['fk_periodo'] = $tesis[0]['fk_periodo'];
            $dataRow['serialrecurso'] = $tesis[0]['serialrecurso'];
            $dataRow['titulo'] = $tesis[0]['titulo'];
            $dataRow['resumen'] = $tesis[0]['resumen'];
            $dataRow['palabrasclaves'] = $tesis[0]['palabrasclaves'];
            $dataRow['fk_tutor'] = $tesis[0]['fk_tutor'];
            $dataRow['fk_escuela'] = $tesis[0]['fk_escuela'];
            $dataRow['fk_tiporecurso'] = $tesis[0]['fk_tiporecurso'];
            $dataRow['fk_institucion'] = $tesis[0]['fk_institucion'];
            $dataRow['fk_calificacion'] = $tesis[0]['calificacion'];
            $dataRow['fk_publicado'] = $tesis[0]['fk_publicado'];
            $dataRow['ubicacion'] = $tesis[0]['ubicacion'];
            $dataRow['pagina'] = $tesis[0]['pagina'];
            $dataRow['observacion'] = $tesis[0]['observacion']; 
            $dataRow['archivo'] = $tesis[0]['archivo'];

            if ($dataRow['archivo'] == null) {
                $json[] = "document.getElementById('archivo-nombre').textContent = 'No Tiene Archivo Adjunto';";
            } else {
                $json[] = "document.getElementById('archivo-nombre').textContent = '" . basename($dataRow['archivo']) . "'";
            }
            
            $title = 'Editar Tesis';

        } else {

            //obtenemos la ultima cota
            $ultimacota = $this->agregar->getUltimaCota();
            $ultimacota = str_replace('TG', '', $ultimacota);
            $ultimacota = $ultimacota + 1;
            $cota = 'TG' . $ultimacota;
            $dataRow['cota'] = $cota;
            
            $this->SwapBytes_Form->fillSelectBox('fk_autor', $this->agregar->getAutor(), 'pk_usuario', 'nombre');
            
            $title = 'Agregar Tesis';


        }

        $json[] = '$("#fecha").datepicker({dateFormat: "yy-mm-dd"});';   
        $json[] = "startFileUploader()";

        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();


    }

    public function addoreditconfirmAction()
    {

        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $this->_fillSelectsRecursive($this->_params['modal']);
            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
            $this->SwapBytes_Crud_Form->getAddOrEditConfirm();


        }
    }


    public function addoreditresponseAction()
    {

        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            // Obtenemos los parametros que se esperan recibir.
            $dataRow = $this->_params['modal'];
            
            $id = $this->_params['modal']['id'];
            $dataRow = $this->validar_comilla($dataRow);   

            $autor = $this->crear_array($dataRow, 'fk_autor');
            $jurado = $this->crear_array($dataRow, 'fk_jurado');
            $tutor = $this->crear_array($dataRow, 'fk_tutor');

            $cont = 0;

            // echo '<pre>';
            // var_dump($dataRow);
            // echo '</pre>';
            // die();


            if (is_numeric($id) && $id > 0) {
                // UPDATE

                $pk_datotesis = $this->agregar->getPkDatotesis($id);
                $pk_usuariogrupott = $this->agregar->getUsuariogrupo($tutor[0], 854);

                $autor_data = $this->agregar->getNumAutores($id);
                $jurado_data = $this->agregar->getRowJurado($id);
                $tutor_data = $this->agregar->getRowTutor($id);

                $this->agregar->UpdateTutor($tutor_data[0]['pk_tutortesis'], $pk_usuariogrupott, $pk_datotesis);
                $this->agregar->UpdateTitulo($pk_datotesis, $dataRow['titulo']);
                $this->agregar->Updatetesis(
                    $id,
                    $dataRow['fk_sede'],
                    $dataRow['fk_periodo'],
                    $dataRow['fecha'],
                    $dataRow['cota'],
                    $dataRow['serialrecurso'],
                    $dataRow['resumen'],
                    $dataRow['palabrasclaves'],
                    $dataRow['fk_escuela'],
                    $dataRow['fk_tiporecurso'],
                    $dataRow['fk_institucion'],
                    $dataRow['fk_calificacion'],
                    $dataRow['pagina'],
                    $dataRow['fk_publicado'],
                    $dataRow['ubicacion'],
                    $dataRow['observacion']
                );

                // bloque autor
                if (count($autor_data) == count($autor)) {// es un update

                //Revisar la logica de listar, corregir como se manejan los nulos

                    if ($autor_data[0]['pk_autortesis'] == null) {

                        $cant = count($autor);

                        for ($i = 0; $i < $cant; $i++) {
                            $periodo = $this->agregar->getPeriodoActual();
                            $pk_usuariogrupo = $this->agregar->getUsuariogrupo($autor[$i], 855);
                            $this->agregar->AddTesista($pk_datotesis, $pk_usuariogrupo, $periodo);
                        }
                        
                    } else {

                        foreach ($autor_data as $db) {
                            $pk_usuariogrupo = $this->agregar->getUsuariogrupo($autor[$cont], 855);
                            $this->agregar->UpdateAutor($db['pk_autortesis'], $pk_usuariogrupo, $pk_datotesis);
                            $cont++;
                        }

                    }   
                }

                if (count($autor_data) < count($autor)) { // es un insert

                    $autores_db = array_map(function ($item) {
                        return $item['fk_autor']; // Asumiendo que 'fk_autor' es la clave que necesitas
                    }, $autor_data);
                    $nuevos_autores = array_diff($autor, $autores_db);
                    $nuevos_autores = array_values($nuevos_autores);
                    $cant = count($nuevos_autores);
                    for ($i = 0; $i < $cant; $i++) {// proceso de insertar
                        $periodo = $this->agregar->getPeriodoActual();
                        $pk_usuariogrupo = $this->agregar->getUsuariogrupo($nuevos_autores[$i], 855);
                        $this->agregar->AddTesista($pk_datotesis, $pk_usuariogrupo, $periodo);
                    }
                }

                if (count($autor_data) > count($autor)) { // es un delete

                    $autores_db = array_map(function ($item) {
                        return $item['fk_autor']; // Asumiendo que 'fk_autor' es la clave que necesitas
                    }, $autor_data);
                    $nuevos_autores = array_diff($autores_db, $autor);
                    $nuevos_autores = array_values($nuevos_autores);
                    $cant = count($nuevos_autores);

                    for ($i = 0; $i < $cant; $i++) {// proceso de insertar
                        $pk_usuariogrupo = $this->agregar->getUsuariogrupo($nuevos_autores[$i], 855);
                        $pk_autortesis = $this->agregar->getPKAutorTesis($pk_usuariogrupo, $pk_datotesis);
                        $this->agregar->DeleteAutor($pk_autortesis[0]['pk_autortesis']);
                    }
                }

                // Bloque jurado
                $cont = 0;
                if (count($jurado_data) == count($jurado)) {// es un update


                    if ($jurado_data[0]['pk_evaluadortesis'] == null) {

                        $cant = count($jurado);

                        for ($i = 0; $i < $cant; $i++) {
                            $periodo = $this->agregar->getPeriodoActual();
                            $pk_usuariogrupo = $this->agregar->getUsuariogrupo($jurado[$i], 854);
                            $this->agregar->addJurado($pk_datotesis, $pk_usuariogrupo, $periodo);
                        }
                        
                     } else {

                        foreach ($jurado_data as $db) {
                            $pk_usuariogrupo = $this->agregar->getUsuariogrupo($jurado[$cont], 854);
                            $this->agregar->UpdateJurado($db['pk_evaluadortesis'], $pk_usuariogrupo, $pk_datotesis);
                            $cont++;
                        }

                    }   

                }

                if (count($jurado_data) < count($jurado)) { // es un insert

                    $jurados_db = array_map(function ($item) {
                        return $item['fk_jurado']; // Asumiendo que 'fk_jurado' es la clave que necesitas
                    }, $jurado_data);
                    $nuevos_jurados = array_diff($jurado, $jurados_db);
                    $nuevos_jurados = array_values($nuevos_jurados);
                    $cant = count($nuevos_jurados);
                    for ($i = 0; $i < $cant; $i++) {// proceso de insertar
                        $periodo = $this->agregar->getPeriodoActual();
                        $pk_usuariogrupo = $this->agregar->getUsuariogrupo($nuevos_jurados[$i], 854);
                        $this->agregar->addJurado($pk_datotesis, $pk_usuariogrupo, $periodo);
                    }
                }

                if (count($jurado_data) > count($jurado)) { // es un delete

                    $jurados_db = array_map(function ($item) {
                        return $item['fk_jurado']; // Asumiendo que 'fk_jurado' es la clave que necesitas
                    }, $jurado_data);
                    $nuevos_jurados = array_diff($jurados_db, $jurado);
                    $nuevos_jurados = array_values($nuevos_jurados);
                    $cant = count($nuevos_jurados);
                    for ($i = 0; $i < $cant; $i++) {// proceso de insertar
                        $pk_usuariogrupo = $this->agregar->getUsuariogrupo($nuevos_jurados[$i], 854);
                        $pk_evaluadortesis = $this->agregar->getPKEvaluadorTesis($pk_usuariogrupo, $pk_datotesis);
                        $this->agregar->DeleteJurado($pk_evaluadortesis[0]['pk_evaluadortesis']);
                    }
                }

            } else {//agregar tesis

                $this->agregar->addDatoTesis($dataRow['titulo']);
                $pk_datotesis = $this->agregar->getTesisByTitulo($dataRow['titulo']);
                $this->agregar->addMencionTesis($pk_datotesis);

                // Agregar Autor
                foreach ($autor as $valor) {
                    $pk_usuariogrupo = $this->agregar->getUsuariogrupo($valor, 855);
                    $this->agregar->addTesista($pk_datotesis, $pk_usuariogrupo, $dataRow['fk_periodo']);
                }

                // Agregar Jurado
                foreach ($jurado as $valor) {
                    $pk_usuariogrupo = $this->agregar->getUsuariogrupo($valor, 854);
                    $this->agregar->addEvaluadoresTesis($pk_datotesis, $pk_usuariogrupo, $dataRow['fk_periodo']);
                }

                //Agregar Tutor
                $pk_usuariogrupo = $this->agregar->getUsuariogrupo($dataRow['fk_tutor']);
                $this->agregar->addTutor($pk_datotesis, $pk_usuariogrupo, $dataRow['fk_periodo']);

                //AGREGAR TESIS
                $this->agregar->addTesisBiblioteca($pk_datotesis, $dataRow['fk_periodo'], $dataRow['fecha'] ,$dataRow['fk_tiporecurso'], $dataRow['resumen'], $dataRow['serialrecurso'], $dataRow['palabrasclaves'], $dataRow['fk_institucion'],  $dataRow['fk_calificacion'], $dataRow['ubicacion'], $dataRow['pagina'], $dataRow['fk_publicado'], $dataRow['observacion'], $dataRow['cota'], $dataRow['fk_sede'], $dataRow['fk_escuela']);


            }

            $this->getResponse()->setBody(Zend_Json::encode($json));
            $this->SwapBytes_Crud_Form->getAddOrEditEnd();

        }
    }

    public function uploadAction(){
        if ($this->_request->isXmlHttpRequest()) {

        $this->SwapBytes_Ajax->setHeader();
        $this->session->filename = null;

        $response = array(
            'success' => true,
            'message' => 'Se subió el archivo con éxito'
        );
        
        $this->getResponse()->setBody(Zend_Json::encode($response));

        $allowedExtensions = array('pdf');
        $sizeLimit = 10 * 1024 * 1024;

        $cota = $this->_request->getParam('cota');
        $pk_tesis = $this->_request->getParam('id');

        $uploader = new SwapBytes_FileUploader_qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUploadTesis("/var/www/http/MiUNE2/public/uploads/recursos/", true, false, $cota);

        $ruta_archivo = '/var/www/http/MiUNE2/public/uploads/recursos/' . $cota . '.' . $result['fileext'];
        $this->agregar->updateArchivoTesis($ruta_archivo, $pk_tesis);


        }
    }

    public function downloadAction()
    {
        $pk_tesis = $this->_request->getParam('id');
        $tesis = $this->getData($pk_tesis);
    
        if (!empty($tesis[0]['archivo'])) {
            $filePath = $tesis[0]['archivo']; 
            $fileExt = ltrim(substr($filePath, strrpos($filePath, '.')), '.');
    
            if (!file_exists($filePath)) {
                $this->SwapBytes_Crud_Form->getDialog('Error', 'No se encontro el archivo en el servidor');

            }
            


            $mime = $this->agregar->getMime($fileExt);
            
            if (!empty($mime)) {
                Zend_Layout::getMvcInstance()->disableLayout();
                Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    
                $response = Zend_Controller_Front::getInstance()->getResponse();
                $response->setHeader('Content-Description', 'File Transfer')
                    ->setHeader('Content-Type', $mime[0]['mime_type'])
                    ->setHeader('Content-Disposition', 'attachment; filename="' . basename($filePath) . '"')
                    ->setHeader('Content-Length', filesize($filePath));
                
                readfile($filePath);
            } else {

                $this->SwapBytes_Crud_Form->getDialog('Error', 'Error al descargar el archivo');
                
            }
        } else {

            $this->SwapBytes_Crud_Form->getDialog('Error', 'Esta tesis no tiene archivo adjunto');


        }
    
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }
    
    public function infoAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            

            $this->SwapBytes_Crud_Form->getDialog('Información', 'Este modulo esta en desarrollo');

        }
    }
    
    public function existsAction() 
    {

        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $queryString = $this->_getParam('data');
            $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);

            $tesis = $this->agregar->getDatatesis($queryArray['cota']);

            if (empty($tesis)) {
                die;
            }

            $autor = $this->agregar->getNumAutores($tesis[0]['pk_tesis']);
            $jurado = $this->agregar->getRowJurado($tesis[0]['pk_tesis']);
            $cantidad_autores = count($autor);
            $cantidad_jurado = count($jurado);

            $pos_jurado = 6 + ($cantidad_autores) * 2;
            $pos_autor = 6;
            $this->SwapBytes_Form->fillSelectBox('fk_autor', $this->agregar->getAutor($this->periodo, $tesis[0]['fk_escuela']), 'pk_usuario', 'nombre'); // falta el periodo

            $this->crearCampo($cantidad_autores - 1, 'autor', $pos_autor, $tesis[0]['fk_escuela']); // creamos el autor
            $this->crearCampo($cantidad_jurado - 1, 'jurado', $pos_jurado, $escuela); // creamos el jurado

            // llenamos el formulario.
            //autores
            $pos = -1;

            foreach ($autor as $au) {
                if ($pos < 0) {
                    $dataRow['fk_autor'] = $au['fk_autor'];
                } else {
                    $dataRow['fk_autor' . $pos] = $au['fk_autor'];
                }
                $pos++;
            }
            //jurados
            $pos = -1;
            foreach ($jurado as $jut) {
                if ($pos < 0) {
                    $dataRow['fk_jurado'] = $jut['fk_jurado'];
                } else {
                    $dataRow['fk_jurado' . $pos] = $jut['fk_jurado'];
                }
                $pos++;
            }





            //$dataRow['id']                = $this->_params['modal']['id'];
            $dataRow['cota'] = $tesis[0]['cota'];
            $dataRow['titulo'] = $tesis[0]['titulo'];
            $dataRow['palabrasclaves'] = $tesis[0]['palabrasclaves'];
            $dataRow['fk_escuela'] = $tesis[0]['fk_escuela'];
            $dataRow['fk_tutor'] = $tesis[0]['fk_tutor'];
            $dataRow['fk_institucion'] = $tesis[0]['fk_institucion'];
            $dataRow['fk_calificacion'] = $tesis[0]['calificacion'];
            $dataRow['fk_resumen'] = $tesis[0]['fk_resumen'];
            $dataRow['ubicacion'] = $tesis[0]['ubicacion'];
            $dataRow['pagina'] = $tesis[0]['pagina'];
            $dataRow['fk_publicado'] = $tesis[0]['fk_publicado'];
            $dataRow['observacion'] = $tesis[0]['observacion'];
            $dataRow['fk_sede'] = $tesis[0]['fk_sede'];
            //$this->SwapBytes_Form->fillSelectBox('fk_sede', $this->agregar->getsedeform($tesis[0]['fk_sede']), 'pk_estructura', 'sede');
            //$this->SwapBytes_Form->fillSelectBox('fk_publicado', $this->agregar->getRowTesis($this->_params['modal']['id']), 'pk_atributo', 'estado');


            if (isset($dataRow)) {

                $this->view->form->populate($dataRow);
            }

            $html = $this->SwapBytes_Html_Message->alert("Cota existente ");
            $html .= $this->SwapBytes_Ajax->render($this->view->form);
            $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');

            if ($tesis[0]['fk_estado'] == 19970 || empty($dataRow['fk_tutor'])) {

                $html = $this->SwapBytes_Html_Message->alert("Cota existente sin tutor aprobado");
                $html .= $this->SwapBytes_Ajax->render($this->view->form);
                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
            } 


            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }

    private function getData($id)
    {
        $dataRow = $this->agregar->getRowTesis($id);
        $dataRow = $this->mejorar_data($dataRow);
        return $dataRow;
    }


    public function autoraddAction()
    {

        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $queryString = $this->_getParam('data');
            $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);


            $cantidad_autores = $this->cont_element($queryArray, 'fk_autor');
            $cantidad_jurado = $this->cont_element($queryArray, 'fk_jurado');

            $pos_jurado = 10 + ($cantidad_autores) * 2;
            $pos_autor = 8;

            $this->crearCampo($cantidad_autores, 'autor', $pos_autor, $queryArray['fk_escuela']); // creamos el autor
            $this->crearCampo($cantidad_jurado - 1, 'jurado', $pos_jurado, $escuela); // re creamos las jurado

            $this->addoreditloadAction($queryArray);
        }

    }

    public function juradoaddAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $queryString = $this->_getParam('data');
            $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);
            $cantidad_autores = $this->cont_element($queryArray, 'fk_autor');

            $pos = 10 + ($cantidad_autores - 1) * 2;

            $cantidad_jurado = $this->cont_element($queryArray, 'fk_jurado');


            $this->crearCampo($cantidad_autores - 1, 'autor', 8, $queryArray['fk_escuela']);   // re-creamos los autores se coloca el -1 para no crear un nuevo autor
            $this->crearCampo($cantidad_jurado, 'jurado', $pos, $escuela); // creamos la materias

            $this->addoreditloadAction($queryArray);


        }

    }

    public function cont_element($arreglo, $campo)
    {

        $datakey = array_keys($arreglo);
        $cant = 0;
        foreach ($datakey as $key) {

            $cadena = preg_replace('/[0-9]/', '', $key);
            if ($cadena == $campo) {
                $cant++;
            }

        }
        return $cant;

    }

    public function crearCampo($cantidad, $campo, $pos, $escuela)
    {

        if ($campo == "autor") {
            for ($i = 0; $i < $cantidad; $i++) {
                $this->view->form->addAutor($i, $pos);
                $this->SwapBytes_Form->fillSelectBox('fk_autor' . $i, $this->agregar->getAutor($this->periodo, $escuela), 'pk_usuario', 'nombre'); // llenado de los autoresprincipal 
                $pos = $pos + 2;
            }
        } else {

            for ($i = 0; $i < $cantidad; $i++) {
                $this->view->form->addJurado($i, $pos);
                $this->SwapBytes_Form->fillSelectBox('fk_jurado' . $i, $this->agregar->get_jurado(), 'pk_usuario', 'nombre');
                $pos = $pos + 2;
            }

        }
    }

    public function deleteautorAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $id = $this->_getParam('id');

            $autor = 'fk_autor' . $id;
            $butom = 'eliminar_autor' . $id;

            $json[] = "$('#$autor-element').remove()";
            $json[] = "$('#$autor-label').remove()";

            $json[] = "$('#$butom').remove()";

            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }

    public function deleteallautoresAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $queryString = $this->_getParam('data');
            $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);
            $cantidad_autores = $this->cont_element($queryArray, 'fk_autor');

            for ($id = 0; $id < $cantidad_autores; $id++) {
                $autor = 'fk_autor' . $id;
                $butom = 'eliminar_autor' . $id;

                $json[] = "$('#$autor-element').remove()";
                $json[] = "$('#$autor-label').remove()";

                $json[] = "$('#$butom').remove()";
            }


            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function deletejuradoAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $id = $this->_getParam('id');

            $jurado = 'fk_jurado' . $id;
            $butom = 'eliminar_jurado' . $id;

            $json[] = "$('#$jurado-element').remove()";
            $json[] = "$('#$jurado-label').remove()";

            $json[] = "$('#$butom').remove()";

            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }

    public function mejorar_data($rows)
    {

        $cantidad = count($rows);

        for ($i = 0; $i < $cantidad; $i++) {

            $rows[$i]['autor_principal'] = str_replace("}", " ", $rows[$i]['autor_principal']);
            $rows[$i]['autor_principal'] = str_replace("{", " ", $rows[$i]['autor_principal']);
            $rows[$i]['autor_otro'] = str_replace("}", " ", $rows[$i]['autor_otro']);
            $rows[$i]['autor_otro'] = str_replace("{", " ", $rows[$i]['autor_otro']);

            $rows[$i]['cota'] = str_replace("~", " ", $rows[$i]['cota']);
            $rows[$i]['titulo'] = str_replace("~", " ", $rows[$i]['titulo']);
            $rows[$i]['ubicacion'] = str_replace("~", " ", $rows[$i]['ubicacion']);
            $rows[$i]['pagina'] = str_replace("~", " ", $rows[$i]['pagina']);
            $rows[$i]['observacion'] = str_replace("~", " ", $rows[$i]['observacion']);

        }

        return $rows;
    }

    public function cescuelaAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $dataRow = $this->agregar->getAutor($this->periodo, $this->_getParam('escuela'));
            echo '<pre>';
            var_dump($dataRow);
            echo '</pre>';
            die();
            $this->SwapBytes_Ajax_Action->fillSelect($dataRow);
        }

    }

    private function _fillSelectsRecursive($RowData)
    {

        if ($this->_params['modal']['id'] <> 0 && is_array($RowData) && count($RowData) > 0) {
            // Modificar
            $autor = $this->agregar->getAutor($this->periodo, $this->_params['modal']['fk_escuela']);
        } else if ($this->_params['modal']['id'] == 0) {

            $autor = $this->agregar->getAutor($this->periodo, $this->_params['modal']['fk_escuela']);
        }

        $this->SwapBytes_Form->fillSelectBox('fk_autor', $autor, 'pk_usuario', 'nombre');

    }

    public function llenar_form($arreglo)
    {
        $datakey = array_keys($arreglo);
        $pos = 0;

        foreach ($arreglo as $valor) {
            $json[] = "$('#$datakey[$pos]').val('$valor')";
            $pos++;
        }
        return $json;

    }

    public function crear_array($data, $campo)
    {
        $arreglo = array();
        $pos = array();
        $datakey = array_keys($data);
        $i = 0;
        $j = 0;

        foreach ($datakey as $key) {
            $cadena = preg_replace('/[0-9]/', '', $key);
            if ($cadena == $campo) {
                array_push($pos, $i);
            }
            $i = $i + 1;
        }

        foreach ($pos as $lugar) {
            $j = 0;
            foreach ($data as $valor) {
                if ($j === $lugar) {
                    array_push($arreglo, $valor);
                }
                $j++;
            }
        }
        return $arreglo;
    }

    public function validar_comilla($data)
    {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();


            $data['cota'] = str_replace("'", "~", $data['cota']);
            $data['titulo'] = str_replace("'", "~", $data['titulo']);
            $data['ubicacion'] = str_replace("'", "~", $data['ubicacion']);
            $data['pagina'] = str_replace("'", "~", $data['pagina']);
            $data['observacion'] = str_replace("'", "~", $data['observacion']);
            return $data;


        }

    }

    public function deleteloadAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $tesis = $this->getData($this->_params['modal']['id']);
            $autor = $this->agregar->getNumAutores($this->_params['modal']['id']);
            $jurado = $this->agregar->getRowJurado($this->_params['modal']['id']);
            // recreamos el formulario
            $cantidad_autores = count($autor);
            $cantidad_jurado = count($jurado);

            $pos_jurado = 9 + ($cantidad_autores) * 2;
            $pos_autor = 9;
            $this->SwapBytes_Form->fillSelectBox('fk_autor', $this->agregar->getAutor($this->periodo, $tesis[0]['fk_escuela']), 'pk_usuario', 'nombre');  // flata periodo 

            $this->crearCampo($cantidad_autores - 1, 'autor', $pos_autor, $tesis[0]['fk_escuela']); // creamos el autor
            $this->crearCampo($cantidad_jurado - 1, 'jurado', $pos_jurado, $escuela); // creamos el jurado

            // llenamos el formulario.

            //autores
            $pos = -1;

            foreach ($autor as $au) {
                if ($pos < 0) {
                    $dataRow['fk_autor'] = $au['fk_autor'];
                } else {
                    $dataRow['fk_autor' . $pos] = $au['fk_autor'];
                }

                $pos++;
            }

            $pos = -1;

            foreach ($jurado as $jut) {
                if ($pos < 0) {
                    $dataRow['fk_jurado'] = $jut['fk_jurado'];

                } else {
                    $dataRow['fk_jurado' . $pos] = $jut['fk_jurado'];

                }

                $pos++;
            }


            // $dataRow  = $this->transformarNull($dataRow);
            $dataRow['id'] = $this->_params['modal']['id'];
            $dataRow['cota'] = $tesis[0]['cota'];
            $dataRow['titulo'] = $tesis[0]['titulo'];
            $dataRow['palabrasclaves'] = $tesis[0]['palabrasclaves'];
            $dataRow['fk_escuela'] = $tesis[0]['fk_escuela'];
            $dataRow['fk_tutor'] = $tesis[0]['fk_tutor'];
            $dataRow['fk_institucion'] = $tesis[0]['fk_institucion'];
            $dataRow['fk_calificacion'] = $tesis[0]['calificacion'];
            $dataRow['ubicacion'] = $tesis[0]['ubicacion'];
            $dataRow['pagina'] = $tesis[0]['pagina'];
            $dataRow['fk_publicado'] = $tesis[0]['fk_publicado'];
            $dataRow['observacion'] = $tesis[0]['observacion'];
            $this->SwapBytes_Form->fillSelectBox('fk_sede', $this->agregar->getsedeform($tesis[0]['fk_sede']), 'pk_estructura', 'sede');
            //$this->SwapBytes_Form->fillSelectBox('fk_publicado', $this->agregar->getRowTesis($this->_params['modal']['id']), 'pk_atributo', 'estado');


            $message = 'Seguro desea elmimnar esto?:';
            $permit = true;



            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar Tesis', $message);
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->getDeleteLoad($permit, $msg);


        }
    }

    public function deletefinishAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();


            $this->agregar->deleteRow($this->_params['modal']['id']);



            $this->SwapBytes_Crud_Form->setProperties($this->view->form);
            $this->SwapBytes_Crud_Form->getDeleteFinish();

        }
    }

}
