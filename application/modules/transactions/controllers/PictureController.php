<?php
class Transactions_PictureController extends Zend_Controller_Action {


    private $_Title                 = 'Transacciones \ Picture';
    private $msg_success_picture    = "Se actualizo correctamente la Foto.";
    private $msg_error_picture      = "No se pudo actualizar la Foto.";

    public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbView_Grupos');
        Zend_Loader::loadClass('Forms_Usuariofoto');
        Zend_Loader::loadClass('Une_Sweetalert');

        $this->Usuarios         = new Models_DbTable_Usuarios();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->recordacademico  = new Models_DbTable_Recordsacademicos();
        $this->filtros          = new Une_Filtros();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();
        $this->vw_grupos        = new Models_DbView_Grupos();
        $this->sweetalert               = new Une_Sweetalert();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->session                  = new Zend_Session_Namespace('session');
        $this->cel                      = new Zend_Session_Namespace('cel');
        $this->val                      = new Zend_Session_Namespace('val');
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->Request                  = Zend_Controller_Front::getInstance()->getRequest();
        $this->AuthSpace                = new Zend_Session_Namespace('Zend_Auth');
        $this->tablas                   = Array('Perfil'  => Array('fn_xrxx_perfil_principal_usuario('. $this->AuthSpace->userId.') AS (pk_grupo bigint , grupo character varying)',
                                           null,
                                           Array('pk_grupo',
                                                 'grupo'),
                                                 'DESC'));

        $this->_params['filters']       = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
        $this->_params['modal']         = $this->SwapBytes_Crud_Form->getParams();
        $this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);
        $this->SwapBytes_Crud_Action->setEnable(true, true, false, false, false, false);
        $this->SwapBytes_Crud_Action->addCustum("<button id=\"btnQuitar\" class=\"ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\"
                                                        name=\"btnQuitar\" role=\"button\" aria-disabled=\"false\">Quitar Perfil</button>");
        $this->SwapBytes_Crud_Action->addJavaScript("$('#btnQuitar').hide();");
        $this->SwapBytes_Crud_Action->addJavaScript("$('#btnQuitar').click(function(){
                                                                var arreglo = [];
                                                                if ($(':checkbox:checked').val() != undefined) {
                                                                $(':checkbox:checked').each(function (){
                                                                arreglo.push($(this).val());
                                                                });};
                                                                $.getJSON(urlAjax + \"quitar/cedulas/\"+escape(arreglo)+\"/perfil/\"+escape($('#Perfil').val())+\"\", function(d){executeCmdsFromJSON(d)});
                                                            });");
        $this->SwapBytes_Crud_Search->setDisplay(true);

        $this->view->form = new Forms_Usuariofoto();
        $this->SwapBytes_Form->set($this->view->form);
        $this->view->form = $this->SwapBytes_Form->get();

        if (isset($this->_params['filters']['Perfil'])){
            $this->view->form->getElement('perfil')->setValue($this->_params['filters']['Perfil']) ;
        }
    }

    /*function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
        if (!$this->grupo->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
        }    
    }*/

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

    public function listAction($filters = null) {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            if ($filters != NULL){
                if ($this->_getParam('buscar') == NULL ){
                    $searchData = "";
                    $where = array (
                        Perfil => $filters,
                        txtBuscar => ""
                    );
                }
            }else {
                $searchData  = $this->_getParam('buscar');
                $where = $this->_params['filters'];
            }

            $pageNumber  = $this->_getParam('page', 1);
            $itemPerPage = 15;
            $pageRange   = 10;
            $json   = array();
            $this->Usuarios->setSearch($searchData);
            $this->Usuarios->setData($where, array('Perfil'));
            $paginatorCount = $this->Usuarios->totalusuarios();
            $rows = $this->Usuarios->getUsuarios($itemPerPage, $pageNumber,true);

            if(isset($rows) && count($rows) > 0) {

                $table = array('class' => 'tableData',
                           'width' => '600px');

                $columns = array(array('column'  => 'pk_usuario',
                                       'primary' => true,
                                       'hide'    => true),
                                 array('name'    => 'C.I.',
                                           'width'   => '70px',
                                           'column'  => 'ci',
                                           'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Nombre',
                                       'width'   => '200px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'nombre'),
                                 array('name'    => 'Apellido',
                                       'width'   => '200px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'apellido'));

                    $HTML = $this->SwapBytes_Crud_List->fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $paginatorCount, 'U');
                    $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);

                    // $this->_params['filters']['Perfil'];
                } else {
                    $HTML  = $this->SwapBytes_Html_Message->alert("No existen Usuario cargados.");
                    $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function uploadAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $id = intval($this->_getParam('id'));
            if(isset($_FILES['file']) and $_FILES['file']['size'] > 0){
                $this->Usuarios->uploadPicture($_FILES['file'],$id);
                $json[] = $this->sweetalert->setFunctionAlert("success","Actualizado",$this->msg_success_picture,"location.reload(true);",'confirmButtonColor: "#00787A"');
            }
            else{
                $json[] = $this->sweetalert->setBasicAlert("error","Actualizado",$this->$msg_error_picture);
            }
            echo Zend_Json::encode($json);
        }
    }

    public function existsAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json        = array();
            $Status      = true;
            $html        = '';
            $queryString = $this->_getParam('data');
            $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);
            $pk_usuario  = $queryArray['pk_usuario'];
            $grupo  = $queryArray['perfil'];

            if(is_numeric($pk_usuario) && !empty($pk_usuario)) {
                // Buscamos si el nuevo usuario tiene un grupo asignado.
                $usuariogrupo = $this->grupo->getCount($pk_usuario, ' AND fk_grupo =' . $grupo);
                $usuario    = $this->Usuarios->getCount($pk_usuario);
                $dataRow    = $this->Usuarios->getRow($pk_usuario);
                // Verificamos si el Usuario existe, al ser asi evitamos que
                // se pueda agregar, de lo contrario lo permitimos.
                if($usuariogrupo == 1 && $usuario == 1) {
                    $Status = true;
                    $html   = $this->SwapBytes_Html_Message->alert('Este Numero de Cedula ya se encuentra en el sistema y ya tiene el perfil asignado.');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                } else if($usuariogrupo == 0 && $usuario == 0) {
                    $json[] = var_dump($dataRow);
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                } else if($usuariogrupo == 0 && $usuario == 1) {
                    $Status = true;
                    $dataRow['id'] = $pk_usuario;
                    $GrupoInactivo = $this->Usuarios->getPkGrupo('Inactivo');
                    $GrupoAdmin = $this->Usuarios->getPkGrupo('Administrativo');
                    $Inactivo = $this->Usuarios->getUsuarioGrupo($pk_usuario,$GrupoInactivo);
                  if (($Inactivo[0]['pk_usuariogrupo'] != NULL ) && ($grupo == $GrupoAdmin)){// condicional si el usuario tiene grupo inactiv0
                        $html   = $this->SwapBytes_Html_Message->alert('El usuario ya existe en el sistema y anteriormente tenia perfil administrativo, ¿Desea devolver al perfil?');
                   }
                   else{
                       $html   = $this->SwapBytes_Html_Message->alert('El usuario ya existe en el sistema, ¿Desea agregarlo al perfil?');
                   }
                     $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                     $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                }
                // Creamos el frmModal con los datos necesarios.
                //  $dataRow['pk_usuario'] = $pk_usuario;
                $this->SwapBytes_Form->set($this->view->form);
                if(isset($dataRow)) {
                    $dataRow['nacionalidad'] = (empty($dataRow['nacionalidad']))? 't' : 'f';
                    $dataRow['sexo']         = (empty($dataRow['sexo']))?         'f' : 't';
                    $this->view->form->populate($dataRow);
                }

                // Definimos el acceso a los controles del frmModal.
                $this->SwapBytes_Form->readOnlyElement('primer_nombre'   , $Status);
                $this->SwapBytes_Form->readOnlyElement('segundo_nombre'  , $Status);
                $this->SwapBytes_Form->readOnlyElement('primer_apellido' , $Status);
                $this->SwapBytes_Form->readOnlyElement('segundo_apellido', $Status);
                $this->view->form = $this->SwapBytes_Form->get();

                // Preparamos el frmModal para ser enviado por AJAX.
                $html  .= $this->SwapBytes_Ajax->render($this->view->form);
                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
                $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$pk_usuario}'");
                $this->getResponse()->setBody(Zend_Json::encode($json));
            } 
            else {
                $html  .= $this->SwapBytes_Ajax->render($this->view->form);
                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
                $this->getResponse()->setBody(Zend_Json::encode($json));
            }
        }
    }

    public function photoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $id    = $this->_getParam('id', 0);
        $image = $this->Usuarios->getPhoto($id);

        $this->getResponse()
             ->setHeader('Content-type', 'image/jpeg')
             ->setBody($image);
    }

    public function addoreditloadAction() {
        if ($this->_request->isXmlHttpRequest()){
            if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
                $title = 'Editar Usuario';
                $dataRow = $this->Usuarios->getRow($this->_params['modal']['id']);
                $dataRow['id'] = $this->_params['modal']['id'];
                $dataRow['pk_usuario']  = $this->_params['modal']['id'];
                $this->SwapBytes_Form->readOnlyElement('pk_usuario', True);
                $dataRow['perfil'] = $this->_params['filters']['Perfil'];
            } 
            $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$dataRow['pk_usuario']}'");
            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->setWidthLeft('120px');
            $this->SwapBytes_Crud_Form->getAddOrEditLoad();
        }
    }

    public function addoreditconfirmAction() {

        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
            $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
        }
    }

    public function addoreditresponseAction() {

        if ($this->_request->isXmlHttpRequest()) {

            if ($this->session->val == "perfil"){
                $usuariogrupos = split(",",$this->session->cel);
                foreach ($usuariogrupos as $usuariogrupo ){
                    if($usuariogrupo != ""){
                       $this->Usuarios->removeAdministrativo($usuariogrupo);
                    }
                };
                $this->session->val = NULL;
                $this->session->cel = NULL;
                $this->SwapBytes_Crud_Form->getAddOrEditEnd();
            }
            else {
                $this->SwapBytes_Ajax->setHeader();
                $dataRow            = $this->_params['modal'];
                $id                 = $dataRow['id'];
                $grupo              =  $this->_params['filters']['Perfil'];

                $dataRow['pk_usuario']      = (isset($dataRow['pk_usuario']))? $dataRow['pk_usuario'] : $dataRow['id'];
                $usuario                    = $this->Usuarios->getCount($dataRow['pk_usuario']);
                $gruposcount                = $this->grupo->getCount($id, ' AND fk_grupo = ' . $grupo);
                $dataRow['nombre']          = $dataRow['primer_nombre'] . ' '. $dataRow['segundo_nombre'];
                $dataRow['apellido']        = $dataRow['primer_apellido'] . ' '. $dataRow['segundo_apellido'];
                $dataRow['id']              = null;
                $dataRow['perfil']          = null;
                $this->_params['modal']['perfil']        = null;

                if(empty($dataRow['segundo_nombre'])){
                  $dataRow['segundo_nombre'] = ' ';
                }

                if(empty($dataRow['segundo_apellido'])){
                  $dataRow['segundo_apellido'] = ' ';
                }

                if($gruposcount >= 1 && $usuario == 1 && isset($id) && $id > 0) {
                    $this->Usuarios->updateRow($id, $dataRow);
                } 
                // No existe el registro, se agrega.
                else if($gruposcount == 0 && $usuario == 0 && empty($id)) {
                  $dataRow['passwordhash']    = md5($dataRow['pk_usuario']);
                  $this->Usuarios->addRow($dataRow); //add usuario
                  $this->Usuarios->namestocomplete($dataRow['pk_usuario']);
                  $this->grupo->addRow($dataRow['pk_usuario'], $grupo); //add grupo
                  $afinidad = $this->Usuarios->CheckAfinidad($dataRow['pk_usuario']); //busca el usuario en tabla usuarioafinidades
                    if ($afinidad == 0){  // revisa si el usuario existe en usuarioafinidades
                        $this->Usuarios->SetAfinidad($dataRow['pk_usuario'],$this->grupo->getPkafinidad($grupo));// si no tiene afinidad  se añade
                    }
                } 
                else if($gruposcount == 0 && $usuario == 1 && isset($id)) {
                    $GrupoInactivo = $this->Usuarios->getPkGrupo('Inactivo');
                    $GrupoAdmin = $this->Usuarios->getPkGrupo('Administrativo');
                    $Inactivo = $this->Usuarios->getUsuarioGrupo($id,$GrupoInactivo);
                    if (($Inactivo['0']['pk_usuariogrupo'] != 0 ) && ($grupo == $GrupoAdmin)){ //update grupo inactivo para activar administrativos
                        $this->Usuarios->namestocomplete($dataRow['pk_usuario']);
                        $this->Usuarios->activarAdministrativo($Inactivo['0']['pk_usuariogrupo']); //si el perfil inactivo existe update administrativo
                    }
                    else {
                        $this->Usuarios->namestocomplete($dataRow['pk_usuario']);
                        $this->grupo->addRow($id, $grupo);
                    }
                }
                $this->SwapBytes_Crud_Form->getAddOrEditEnd();
            }
        }
    }

}

?>