<?php
class Transactions_RepositorioController extends Zend_Controller_Action {


    private $_Title   = 'Transacciones \ Repositorio';

    public function init() {


      //Cargas de la clases
      Zend_Loader::loadClass('Models_DbTable_Repositorio');
      Zend_Loader::loadClass('Forms_Repositorio');
      Zend_Loader::loadClass('Models_DbTable_Atributos');



      //Instancia de clases
      $this->SwapBytes_Crud_Form = new SwapBytes_Crud_Form();
      $this->SwapBytes_Form      = new SwapBytes_Form();
      $this->repositorio         = new Models_DbTable_Repositorio();
      $this->view->form          = new Forms_Repositorio();
      $this->atributo            = new Models_DbTable_Atributos();


      $this->SwapBytes_Form->set($this->view->form);
      $this->view->form = $this->SwapBytes_Form->get();
      $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();

      //Rellena los select de los formularios de coleccion y tipo de recurso
      $this->SwapBytes_Form->fillSelectBox('fk_tutor', $this->grupo->getTutores() , 'pk_usuario', 'nombre');
      $this->SwapBytes_Form->fillSelectBox('fk_tiporecurso', $this->atributo->getTipes(110, null) , 'pk_atributo', 'valor');
      $this->SwapBytes_Form->fillSelectBox('fk_coleccion', $this->atributo->getTipes(3, 920) , 'pk_atributo', 'valor');
      $this->SwapBytes_Form->fillSelectBox('fk_estado', $this->atributo->getTipes(111, null) , 'pk_atributo', 'valor');



      Zend_Loader::loadClass('Une_Filtros');
      Zend_Loader::loadClass('CmcBytes_Filtros');
      Zend_Loader::loadClass('Models_DbTable_Usuarios');
      Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
      Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
      Zend_Loader::loadClass('Models_DbTable_Periodos');
      Zend_Loader::loadClass('Models_DbTable_Inscripciones');
      Zend_Loader::loadClass('Models_DbTable_Profit');
      Zend_Loader::loadClass('Models_DbTable_Usuariosdatos');
      Zend_Loader::loadClass('Models_DbView_Grupos');
      Zend_Loader::loadClass('Models_DbView_Sedes');
      Zend_Loader::loadClass('Models_DbView_Escuelas');
      Zend_Loader::loadClass('Models_DbTable_Inscripciones');
      Zend_Loader::loadClass('Models_DbTable_Pensums');
      Zend_Loader::loadClass('Models_DbTable_Cargacarnets');
      $this->Usuarios         = new Models_DbTable_Usuarios();
      $this->usuariosdatos    = new Models_DbTable_Usuariosdatos();
      $this->grupo            = new Models_DbTable_UsuariosGrupos();
      $this->recordacademico  = new Models_DbTable_Recordsacademicos();
      $this->filtros          = new Une_Filtros();
      $this->CmcBytes_Filtros = new CmcBytes_Filtros();
      $this->vw_grupos        = new Models_DbView_Grupos();
      $this->vw_escuelas      = new Models_DbView_Escuelas();
      $this->profit           = new Models_DbTable_Profit();
      $this->inscripciones    = new Models_DbTable_Inscripciones();
      $this->vw_sedes         = new Models_DbView_Sedes();
      $this->periodos         = new Models_DbTable_Periodos();
      $this->inscripciones    = new Models_DbTable_Inscripciones();
      $this->pensum           = new Models_DbTable_Pensums();
      $this->CargaCarnets     = new Models_DbTable_CargaCarnets();
      $this->Request = Zend_Controller_Front::getInstance()->getRequest();
      $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
	    $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
	    $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
	    $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
	    $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
	    $this->SwapBytes_Date           = new SwapBytes_Date();
	    $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
	    $this->SwapBytes_Uri            = new SwapBytes_Uri();
	    $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
	    $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
      $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
      $this->Request = Zend_Controller_Front::getInstance()->getRequest();
      $this->AuthSpace                = new Zend_Session_Namespace('Zend_Auth');
      $this->CmcBytes_Filtros = new CmcBytes_Filtros();
      $this->tablas = Array('Recurso' =>Array('vw_tiporecursos', null,
                                        Array('pk_atributo', 'tiporecurso'), '1 DESC'),

                            'Escuela' =>Array('vw_escuelas', 'pk_atributo<>920',
                                        Array('pk_atributo','escuela'),'1 ASC'),
                            );


      $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));

      //Botones para las transacciones

        //SwapBytes_Crud_Action es una clase para manejar los botones dentro de los modulos

          //SetDisplay oculta o muestra los botones pasando por parametros booleanos
          $this->SwapBytes_Crud_Action->setDisplay(true, true, true, false, false, false);

          //SetEnable habilita o desahabilita los botones pasando por parametros booleanos
	        $this->SwapBytes_Crud_Action->setEnable(true, true, true, false, false, false);

        //SwapBytes_Crud_Search es una clase para manejar el buscador dentro de los modulos
          $this->SwapBytes_Crud_Search->setDisplay(true);

    }

    function preDispatch() {

    $is_egresado = $this->grupo->isEgresado();
    $is_bibliotecaria = $this->grupo->isBibliotecaria();

        if($is_egresado == true || $is_bibliotecaria == true){
          $this->SwapBytes_Crud_Action->setDisplay(true, true, true, false, false, false);
          $this->SwapBytes_Crud_Action->setEnable(true, true, true, false, false, false);
        }

        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
        if (!$this->grupo->haveAccessToModule()) {
      	  $this->_helper->redirector('accesserror', 'profile', 'default');
      	}
    }

    //Crea la estructura base de la pagina principal.

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


      public function listAction(){

        if ($this->_request->isXmlHttpRequest()) {

            $this->SwapBytes_Ajax->setHeader();

            //recibe los valores de los filtros
              $coleccion = $this->_params['filters']['Escuela'];
              $tiporecurso = $this->_params['filters']['Recurso'];

            //Buscador
              //Recibe los valores del buscador
              $searchData  = $this->_getParam('buscar');
              //Guarda en un atributo (searchData) de la clase repositorio
              $this->repositorio->setSearch($searchData);

            //Prepara los valores de la paginacion
              $pageNumber  = $this->_getParam('page', 1);
              $itemPerPage = 15;
              $pageRange   = 10;

            //Se declara una variable en donde se enviara la tabla
              $json = array();

            //Se agrega la logica para listar segun los valores que lleguen de los filtros
              if($coleccion == null && $tiporecurso == null){

                $paginatorCount = $this->repositorio->getCountRecursos();
                $rows = $this->repositorio->getRecursos($itemPerPage, $pageNumber);

              } else if ($coleccion == null) {

                $paginatorCount = $this->repositorio->getCountRecursosTipo($tiporecurso);
                $rows = $this->repositorio->getRecursosTipo($itemPerPage, $pageNumber, $tiporecurso);

              } else {

                $paginatorCount = $this->repositorio->getCountRecursosTipoEscuela($tiporecurso, $coleccion);
                $rows = $this->repositorio->getRecursosTipoEscuela($itemPerPage, $pageNumber, $tiporecurso, $coleccion);

              }

            //Pregunto si $rows existe y si es mayor a 0
              if(isset($rows) && count($rows) > 0) {

              //Si existe y es mayor a 0 se crea la tabla

              //Se define la tabla y se definen los atributos
                $table = array('class' => 'tableData',
                             'width' => '1300px');

              //Se definen las columnas y los atributos
                $columns = array(array('column'  => 'pk_recurso',
                                       'primary' => true,
                                       'hide'    => true,
                                       'name' => 'pk_recurso'),
                                 array('name'    => 'titulo',
                                           'width'   => '500px',
                                           'column'  => 'titulo',
                                           'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'cedula',
                                       'width'   => '100px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'cedula'),
                                 array('name'    => 'nombre',
                                       'width'   => '150px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'nombre'),
                                 array('name'    => 'tipo de recurso',
                                       'width'   => '80px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'tiporecurso'),
                                 array('name'    => 'escuela',
                                       'width'   => '80px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'coleccion'),
                                       array('name'    => 'estado',
                                       'width'   => '80px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'estado')

                    );

              //Se define la variable $HTML y llama a un metodo fillwithPaginator de la clase que construye la tabla
                $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount[0]["count"], 'VUDR');

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkRecurso');

            } else {
            $HTML  = $this->SwapBytes_Html_Message->alert("No existen recursos cargados.");
            $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);

            }

        //Manda la respuesta en formato json, sea la tabla con el contenido o la alerta
          $this->getResponse()->setBody(Zend_Json::encode($json));
        }
      }


  public function viewAction() {

      $dataRow = $this->repositorio->getRow($this->_params['modal']['id']);

      $dataRow['fk_usuariogrupo'] = $this->repositorio->getCedula($this->_params['modal']['id']);

      #$json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Eliminar');
      #$json[] = $this->SwapBytes_Jquery_Ui_Form->displayError('frmModal', 'Me gustan los penes');
      #$json[] = $this->SwapBytes_Jquery_Ui_Form->setCenter('frmModal');
      #$json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal', 1000);

      $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver recurso');
      $this->SwapBytes_Crud_Form->getView();

  }

public function addoreditloadAction() {

  if ($this->_request->isXmlHttpRequest()){

    if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {

      $title = 'Editar Recurso';

      $this->SwapBytes_Form->readOnlyElement('fk_usuariogrupo', true);

      $dataRow = $this->repositorio->getRow($this->_params['modal']['id']);
      $dataRow['fk_usuariogrupo'] = $this->repositorio->getCedula($this->_params['modal']['id']);

    } else {

      $title = 'Agregar Recurso';

	  }

  $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
	$this->SwapBytes_Crud_Form->getAddOrEditLoad();

  }


}

public function addoreditconfirmAction() {

  if ($this->_request->isXmlHttpRequest()) {

    $this->SwapBytes_Ajax->setHeader();

    // $dataRow = $this->_params['modal'];
    // $exists = $this->repositorio->isUserEstudiante($dataRow['fk_usuariogrupo']);

    //  $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript("console.log('$exists');");

    // $this->SwapBytes_Crud_Form->setJson($json);



    // $error = $this->view->form->getMessages('fk_usuariogrupo');
    // $errorJson = Zend_Json::encode($error);
    // $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript("console.log('$errorJson');");


    $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
    $this->SwapBytes_Crud_Form->getAddOrEditConfirm();

  }
}

  // Editamos o Agregamos
  public function addoreditresponseAction() {

    if ($this->_request->isXmlHttpRequest()) {

      $this->SwapBytes_Ajax->setHeader();

      $dataRow = $this->_params['modal'];

      if(isset($dataRow['pk_recurso']) && $dataRow['pk_recurso'] > 0) {

        $dataRow['fk_usuariogrupo'] = $this->grupo->getPK($dataRow['fk_usuariogrupo']);
        $this->repositorio->updateRow($dataRow['pk_recurso'], $dataRow);



      } else {

        $dataRow['pk_recurso'] = (int)$dataRow['pk_recurso'];
        $dataRow['rutarecurso'] = '/var/www/http/MiUNE/public/repositorio/recursos/pasantia' . $dataRow['pk_recurso'] . '.pdf';
        $dataRow['fk_usuariogrupo'] = $this->grupo->getPK($dataRow['fk_usuariogrupo']);

        $this->repositorio->addRow($dataRow);



      }
    
    $this->SwapBytes_Crud_Form->getAddOrEditEnd();
     
    }
  }


  public function deleteloadAction() {

    $message = 'Estas seguro de eliminar este recurso?';
    $permit = true;

    $dataRow = $this->repositorio->getRow($this->_params['modal']['id']);
    $dataRow['fk_usuariogrupo'] = $this->repositorio->getCedula($this->_params['modal']['id']);


    $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Eliminar recurso', $message);
    $this->SwapBytes_Crud_Form->setWidthLeft('120px');
    $this->SwapBytes_Crud_Form->getDeleteLoad($permit);

  }

  public function deletefinishAction() {


    $this->repositorio->deleteRow($this->_params['modal']['pk_recurso']);
    $this->SwapBytes_Crud_Form->setProperties($this->view->form);
    $this->SwapBytes_Crud_Form->getDeleteFinish();



  }
  
  public function downloadAction() {
    echo 'hola';
  }

  }
