<?php

class Transactions_AprobartituloController extends Zend_Controller_Action {

    private $_Title   = 'Transacciones \ Aprobar Titulo'; 
    private $FormTitle_Observacion = 'Agregar Observacion';
    private $FormTitle_Info = 'Ver Tesis';

    /*ESTADOS DE UN TITULO

    14145 aprobado
    19685 por abrobar
    19684 no aprobado*/

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios'); 
        Zend_Loader::loadClass('Models_DbTable_Pasantes'); 
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Tesis');
        Zend_Loader::loadClass('Models_DbTable_Materiasestados');
        Zend_Loader::loadClass('Models_DbView_Grupos');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Forms_Observaciones');
        
        
        $this->Usuarios         = new Models_DbTable_Usuarios();
        $this->Pasantes         = new Models_DbTable_Pasantes();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->recordacademico  = new Models_DbTable_Recordsacademicos();
        $this->tesis           = new Models_DbTable_Tesis();
        $this->materiasestados = new Models_DbTable_Materiasestados();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->filtros          = new Une_Filtros();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();
        $this->vw_grupos        = new Models_DbView_Grupos();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->AuthSpace                = new Zend_Session_Namespace('Zend_Auth');  

        $this->view->form = new Forms_Observaciones();
        $this->SwapBytes_Form->set($this->view->form);
        $this->view->form = $this->SwapBytes_Form->get();


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
                                                       'ee.fk_estructura = ##Sede##'),
                                                 Array('ee.fk_atributo',
                                                       'es.escuela'),
                                                 'ASC'),
                                'Estado'  => Array('tbl_atributos',
                                                 array('fk_atributotipo = (select distinct pk_atributotipo from tbl_atributostipos where nombre ilike \'Estado Tesis\')'),
                                                 array('pk_atributo',
                                                       'valor')

                                                )
                                                            );
            

    $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters')); 

//      BOTONES DE ACCIONES
                
    $this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);
    $this->SwapBytes_Crud_Action->setEnable(true, true, false, true, false, false);
    $this->SwapBytes_Crud_Search->setDisplay(true); 
    $this->SwapBytes_Crud_Action->addCustum('&nbsp;<select id="selEstado" name="selEstado" style="width:100px"></select>');


}

function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
       if (!$this->grupo->haveAccessToModule()) {
           $this->_helper->redirector('accesserror', 'profile', 'default');
         }    
    }
    
  
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
    
    public function estadoAction() {
    
    $dataRows = $this->tesis->getEstadosTesis();
    
    $this->SwapBytes_Ajax_Action->fillSelect($dataRows, "Estado");
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

                $datos = $this->_getAllParams();
                $filtro = $this->SwapBytes_Uri->queryToArray($datos['filters']); 


                $rows = $this->tesis->getTesisEScuelaPeriodo($filtro['Escuela'],$filtro['Periodo'],$filtro['Estado'],$filtro['txtBuscar'],$filtro['Sede']);

                if(isset($rows) && count($rows) > 0) {

                        $table = array('class' => 'tableData',
                               'width' => '1300px');

                        $columns = array(array('column'  => 'pk_datotesis',
                                               'primary' => true,
                                               'hide'    => true),
                                         array('name' => array('control' => array('tag' => 'input',
                                                        'type' => 'checkbox',
                                                        'name' => 'chkSelectDeselect')),
                                                'column' => 'action',
                                                'width' => '30px',
                                                'rows' => array('style' => 'text-align:center'),
                                                'control' => array('tag' => 'input',
                                                    'type' => 'checkbox',
                                                    'name' => 'chkDatoTesis',
                                                    'id' => 'chkDatoTesis',
                                                    'value' => '##pk_datotesis##')),
                                         array('name'    => 'Cedula(s)',
                                                   'width'   => '70px',
                                                   'column'  => 'cedula',
                                                   'rows'    => array('style' => 'text-align:center')),
                                         array('name'    => 'Autor(es)',
                                               'width'   => '200px',
                                               'rows'    => array('style' => 'text-align:center'),
                                               'column'  => 'autor'),
                                         array('name'    => 'Titulo',
                                               'width'   => '400px',
                                               'rows'    => array('style' => 'text-align:center'),
                                               'column'  => 'titulo')
                                         );

                        $other = array(
                          array('actionName' => '',
                                'action'     => 'ver(##pk##)'  ,
                                'label'      => 'ver',
                                'column' => 'acciones'),
                          array('actionName' => '',
                                'action'     => 'observaciones(##pk##)'  ,
                                'label'      => 'observaciones',
                                'column' => 'acciones')
                          
                                );

                        $HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'O',$other);
                        $HTML = $HTML;
                        $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                        $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkDatoTesis');

            }else{

                    $HTML = $this->SwapBytes_Html_Message->alert("No Existen Tesis Cargadas");

                    $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));        

        }

    }

    public function cambiarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json = array();
            $Data = $this->_getParam('data');
            $Rows = $this->SwapBytes_Uri->queryToArray($Data);

            if(isset($Rows['chkDatoTesis'])){
                if (is_array($Rows['chkDatoTesis'])) {

                    foreach($Rows['chkDatoTesis'] as $tesis){
                        $pk = $tesis;
                        $up_data['fk_estado'] = $Rows['selEstado'];
                        $update = $this->tesis->updateRow($pk, $up_data);
                    }

                }else{

                    $pk = $Rows['chkDatoTesis'];
                    $up_data['fk_estado'] = $Rows['selEstado'];
                    $update = $this->tesis->updateRow($pk, $up_data);
                }
                
            }

            $json[] = $this->SwapBytes_Jquery->getJSON('list', null, array('buscar' => $this->SwapBytes_Jquery->getValEncodedUri('txtBuscar'),
                                        'filters' => $this->SwapBytes_Jquery->serializeForm()

                                    ));
            $json[] = $this->SwapBytes_Jquery->setValSelectOption('selEstado', 0);
            $this->getResponse()->setBody(Zend_Json::encode($json));

        }
    }


    public function addoreditloadAction() {
        // Obtenemos los parametros que se esperan recibir.
       if ($this->_request->isXmlHttpRequest()) {

            
            $this->SwapBytes_Ajax->setHeader();

            $datos = $this->_getAllParams();

            $queryString = $this->_getParam('filters');
            $queryArray = $this->SwapBytes_Uri->queryToArray($queryString);
            $dataRow = $dataRow[0];
            $observaciones = $this->tesis->getTesisObservaciones($datos['cod']);
            $dataRow['pk_datotesis'] = $datos['cod'];
            if(!empty($observaciones)){

               $dataRow['pk_pasotesis'] = $observaciones[0]['pk_pasotesis'];
               $dataRow['observaciones'] = $observaciones[0]['observaciones'];

            }

            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $this->FormTitle_Observacion);
            $this->SwapBytes_Crud_Form->enableElements(true);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');

            //para evitar que presionen enter
            $json[] = "$('#frmModal').keypress(function(e){   
                        if(e.which == 13){
                          return false;
                        }
                      });";

            $this->SwapBytes_Crud_Form->setJson($json);

            $this->SwapBytes_Crud_Form->getAddOrEditLoad();

                   
           
           
       }
    }

    public function addoreditconfirmAction() {
        
       if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            

            $queryString = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));
            
            $datos = $this->SwapBytes_Uri->queryToArray($this->_getParam('data'));
            
            if(empty($datos['pk_pasotesis'])){
                
                $cedula = $this->AuthSpace->userId;
                $usuariogrupo = $this->tesis->getUsuariogrupo($cedula,905); 
                $periodo = $queryString['Periodo'];
                
                $this->tesis->addObservacionesTesis($datos['pk_datotesis'],$periodo,$usuariogrupo,$datos['observaciones']);

            }else{
                $this->tesis->updateObservacionesTesis($datos['pk_pasotesis'],$datos['observaciones']);
            }

            $this->SwapBytes_Crud_Form->getAddOrEditEnd(); 
       }
    }

    public function viewAction(){
      if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();
            $id = $this->_getParam('cod');

            $lineatematesis = $this->tesis->getLineaTemaTesis($id, NULL);
            $json = array();
            $data = array();

            $properties = array('width' => '550','align' => 'center');
            $styles = array(array('style' => 'text-align:right;font-size:14px;font-weight:bold;vertical-align:top'),array('style' => 'text-align:left;font-size:14px'));
            $data[] = array('Linea:', $lineatematesis[0]['linea']);
            $data[] = array('Tema:', $lineatematesis[0]['tema']);
            $data[] = array('Titulo:', $lineatematesis[0]['titulo']);

            $html .= $this->SwapBytes_Html->table($properties, $data, $styles);
            $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 420);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal', 580);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle('frmModal', $this->FormTitle_Info);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->open('frmModal');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Proceder');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Proceder');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');

            $this->getResponse()->setBody(Zend_Json::encode($json));


      }

    }
 

}