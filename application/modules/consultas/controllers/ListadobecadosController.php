<?php

/**
* @author: DDTI Septiembre 2016
* 
*
*/

class Consultas_ListadobecadosController extends Zend_Controller_Action {


    /*Funcion donde se inicializan las librerias*/
    public function init() {

        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Une_Filtros');  
        Zend_Loader::loadClass('Models_DbTable_Periodos');      
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_Profit');
        Zend_Loader::loadClass('Une_Filtros');

        Zend_Loader::loadClass('Models_DbTable_Reinscripciones');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbView_Escuelas'); 
        Zend_Loader::loadClass('Models_DbView_Sedes');


        $this->Une_Filtros              = new Une_Filtros();
        $this->usuarios                 = new Models_DbTable_Usuarios();
        $this->grupo                    = new Models_DbTable_UsuariosGrupos();
        $this->recordacademico          = new Models_DbTable_Recordsacademicos();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->escuelas                 = new Models_DbTable_EstructurasEscuelas();
        $this->profit                   = new Models_DbTable_Profit();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->periodos                 = new Models_DbTable_Periodos();

        $this->reinscripciones         = new Models_DbTable_Reinscripciones();
        $this->inscripciones           = new Models_DbTable_Inscripciones();
        $this->escuela                 = new Models_DbView_Escuelas();
        $this->sede                    = new Models_DbView_Sedes();



        /*Filtros*/
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));

        $this->Une_Filtros->setDisplay(true, true, true);
        $this->Une_Filtros->setRecursive(true, true, true);
         /*Botones de Acciones*/
        $this->SwapBytes_Crud_Action->setDisplay(true,true);
        $this->SwapBytes_Crud_Action->setEnable(true,true);
        $this->SwapBytes_Crud_Search->setDisplay(false);

    } 

    //Comentamos preDispatch para que no moleste al probar el codigo

    function preDispatch() {

         if (!Zend_Auth::getInstance()->hasIdentity()) {  
             $this->_helper->redirector('index', 'login', 'default');
         }
    
         if (!$this->grupo->haveAccessToModule()) {
             $this->_helper->redirector('accesserror', 'profile', 'default');
         }

    }


    public function indexAction() {
        $this->view->title = "Consultas \ Becados";
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
            $json = array();
            $this->SwapBytes_Ajax->setHeader();
            $params  = $this->Une_Filtros->getParams();
            $periodosBecados = $this->periodos->periodosBecados();
            $this->SwapBytes_Ajax_Action->fillSelect($periodosBecados);
        }

    }

    public function sedeAction() {
        $dataSedes = $this->sede->getSedes();
        array_unshift($dataSedes, array("pk_atributo"=>"0","sede"=>"Todas")); //se anade el campo de "todas" para seleccionar todas las sedes
        $this->SwapBytes_Ajax_Action->fillSelect($dataSedes);        
    }

    public function escuelaAction() {
        if ($this->Request->getParam('sede')==0) {
          $dataRows = $this->escuela->getEscuelas();
          array_unshift($dataRows, array("pk_atributo"=>"0","escuela"=>"Todas")); //se anade el campo de "todas" para seleccionar todas las escuelas
          $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
        }else{
          $dataRows = $this->escuelas->getSelect($this->Request->getParam('sede'));
          array_unshift($dataRows, array("pk_atributo"=>"0","escuela"=>"Todas")); //se anade el campo de "todas" para seleccionar todas las escuelas
          $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
        }
    }

    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $searchData     = $this->_getParam('buscar');
            $this->usuarios->setSearch($searchData);
            $periodo        = $this->_params['filters']['selPeriodo'];
            $sede           = $this->_params['filters']['selSede'];  
            $escuela        = $this->_params['filters']['selEscuela'];          
            $json           = array();
            //Traemos datos de profit
            $profit_table = $this->profit->getBecaEstudiantes($periodo);
            // CORTAMOS LOS ESPACIOS EN BLANCO DE PROFIT
            foreach ($profit_table as $key => $value) {
              $trim[$key]["co_cli"]=rtrim($value["co_cli"]);
              $trim[$key]["co_art"]=rtrim($value["co_art"]);
            }
            foreach ($profit_table as $key => $value) {
              $stringtrim = $stringtrim.rtrim($value["co_cli"]).',';
            }
            $cedulas_string = rtrim($stringtrim,',');

            $tabla_profit_trim = $this->usuarios->getEstudiantesBecadosEscuela($cedulas_string,$periodo, $sede, $escuela, $itemPerPage, $offsetValue);

            foreach ($tabla_profit_trim as $key1 => $value1) {

              foreach ($trim as $key2 => $value2) {

                if ($value1["cedula"]==$value2["co_cli"]) {
                  ($tabla_profit_trim[$key1]["co_art"]= $value2["co_art"]);        
                }

              }

            }

            //SI QUEREMOS PAGINACION, DESCOMENTAR ESTO
            //$pageNumber       = $this->_getParam('page', 1);        
            //$itemPerPage      =  10;
            //$pageRange        =  10;
            //$offsetValue      = ($pageNumber-1) * $itemPerPage;     
            //$cheat            = $this->usuarios->getEstudiantesBecadosEscuela($tabla_profit_trim, $periodo, $escuela);//             
            //$estudiantesCount =count($cheat);        
                       
            foreach ($tabla_profit_trim as $key => $value) {

              $this->ultimoperiodo = $periodo;
              $this->ultimoperiodocursado = $this->recordacademico->getPeriodoAnteriorInscrito($value["cedula"],$this->ultimoperiodo);
              // Si el estudiante es nuevo ingreso el ultimoperiodo va a llegar NULL se lleva a 0 con un operador ternario para que no de error el query
              $this->ultimoperiodocursado = ($this->ultimoperiodocursado == NULL) ?  0 : $this->ultimoperiodocursado;
              $this->pensum = $this->inscripciones->getPensumInscripcion($value["cedula"],$this->ultimoperiodo)[0]['fk_pensum']; 
              $this->ultimaEscuela = $this->recordacademico->getUltimaEscuelaCursada($value["cedula"])[0]['pk_atributo'];
              $this->iuc = $this->reinscripciones->indicePeriodo($value["cedula"],$this->ultimoperiodocursado,$this->ultimaEscuela,$this->pensum)[0]['iiap'];
              //var_dump($this->iuc);die;
              $Pensumc = $this->recordacademico->getCodigopropietario($this->pensum);
              $iia = $this->recordacademico->getIAAEscuelaPensumArticulado($value["cedula"], $this->ultimaEscuela, $this->ultimoperiodocursado,$Pensumc);

              array_push($tabla_profit_trim[$key], $this->iuc);
              array_push($tabla_profit_trim[$key], $iia);
             
               if (strstr($tabla_profit_trim[$key]["co_art"], 'TOTAL')) {
                   array_push($tabla_profit_trim[$key], 'TOTAL');
               }
               elseif (strstr($tabla_profit_trim[$key]["co_art"], 'MEDIA')) {
                   array_push($tabla_profit_trim[$key], 'MEDIA');
               }
               elseif (strstr($tabla_profit_trim[$key]["co_art"], 'HATILLO')) {
                   array_push($tabla_profit_trim[$key], 'HATILLO');
               }
               elseif (strstr($tabla_profit_trim[$key]["co_art"], 'ULTIMA')) {
                   array_push($tabla_profit_trim[$key], 'ÚLTIMA CUOTA');
               }
               elseif (strstr($tabla_profit_trim[$key]["co_art"], 'SUCRE')) {
                   array_push($tabla_profit_trim[$key], 'FUNDASUCRE');
               }
               else{
                   array_push($tabla_profit_trim[$key], 'BECA');
               }
            }  
            //var_dump($tabla_profit_trim);die;
            // Definimos las propiedades de la tabla.
            if (!empty($tabla_profit_trim)) {

              $ra_property_table = array('class'  => 'tableData',
                                         'width'  => '976px',
                                         'column' => 'disponible');
              $ra_property_column = array(array('column'  => 'cedula',
                                                 'primary' => true,
                                                 'hide'    => true),
                                           array('name'     => '#',
                                                 'function' => 'rownum',
                                                 'width'    => '20px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'C.I.',
                                                 'column'   => 'cedula',
                                                 'width'    => '125px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Nombre',
                                                 'column'   => 'nombre',
                                                 'width'    => '175px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Apellido',
                                                 'column'   => 'apellido',
                                                 'width'    => '175px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Email',
                                                 'column'   => 'correo',
                                                 'width'    => '175px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Sede',
                                                 'column'   => 'sede',
                                                 'width'    => '100px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Escuela',
                                                 'column'   => 'escuela',
                                                 'width'    => '125px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Beca',
                                                 'column'   => '2',
                                                 'width'    => '125px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Artículo',
                                                 'column'   => 'co_art',
                                                 'width'    => '125px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Índice P.',
                                                 'column'   => '0',
                                                 'width'    => '87px',
                                                 'rows'     => array('style' => 'text-align:center')),
                                           array('name'     => 'Índice A.',
                                                 'column'   => '1',
                                                 'width'    => '87px',
                                                 'rows'     => array('style' => 'text-align:center'))
                           
                                           );                        
              // Generamos la lista.  

              //SI QUEREMOS PAGINACION DESCOMENTAR ESTO Y COMENTAR EL DE ABAJO
             // $HTML   = $this->SwapBytes_Crud_List->fillWithPaginator($ra_property_table, $tabla_profit_trim , $ra_property_column, $itemPerPage, $pageNumber, $pageRange, $estudiantesCount);
    
              //SI NO QUEREMOS PAGINACION UTILIZAR ESTE CODIGO
              $HTML   = $this->SwapBytes_Crud_List->fill($ra_property_table, $tabla_profit_trim, $ra_property_column);
              $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
              
            }else{

              $HTML =  $this->SwapBytes_Html_Message->alert("No existen Registros.");
              $json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));
          }
    }
}
