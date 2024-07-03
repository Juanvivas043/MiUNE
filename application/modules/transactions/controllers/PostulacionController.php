<?php
class Transactions_PostulacionController extends Zend_Controller_Action {

  private $Title          = ' Listado Vacantes';
  private $FormTitle_View = 'Postulacion';
  private $msg_success    = 'Se registro su postulacion exitosamente';
  private $msg_error      = 'Ocurrio un error al registrar su postulacion, por favor intente mas tarde';

  public function init() {
    /* Initialize action controller here */
    Zend_Loader::loadClass('Models_DbTable_Usuarios');  
    Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
    Zend_Loader::loadClass('Models_DbTable_Instituciones');
    Zend_Loader::loadClass('Models_DbTable_Vacantes');
    Zend_Loader::loadClass('Models_DbTable_Postulaciones');
    Zend_Loader::loadClass('Models_DbTable_Atributos');
    Zend_Loader::loadClass('Models_DbTable_Usuariosarchivos');
    Zend_Loader::loadClass('Forms_Agregarpostulacion');
    Zend_Loader::loadClass('Une_Filtros');

    $this->usuario                  = new Models_DbTable_Usuarios();
    $this->grupo                    = new Models_DbTable_UsuariosGrupos();
    $this->instituciones            = new Models_DbTable_Instituciones();
    $this->vacantes                 = new Models_DbTable_Vacantes();
    $this->postulaciones            = new Models_DbTable_Postulaciones();
    $this->atributos                = new Models_DbTable_Atributos();
    $this->usuariosarchivos         = new Models_DbTable_Usuariosarchivos();
    $this->Une_Filtros              = new Une_Filtros();
    $this->current_user             = new Zend_Session_Namespace('Zend_Auth');

    $this->Request                  = Zend_Controller_Front::getInstance()->getRequest();

    $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
    $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
    $this->SwapBytes_Html           = new SwapBytes_Html();
    $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
    $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
    $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
    $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
    $this->SwapBytes_Form           = new SwapBytes_Form();
    $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
    $this->SwapBytes_Date           = new SwapBytes_Date();
    $this->SwapBytes_Uri            = new SwapBytes_Uri();
    $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
    $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
    $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
    $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
    $this->Swapbytes_array          = new SwapBytes_Array();
    $this->CmcBytes_Filtros         = new CmcBytes_Filtros();

    $this->Une_Filtros->setDisplay(false);
    $this->Une_Filtros->setRecursive(false);
    $this->SwapBytes_Crud_Action->setDisplay(true,true);
    $this->SwapBytes_Crud_Action->setEnable(true,true);
    $this->SwapBytes_Crud_Search->setDisplay(true);

    $this->view->form               = new Forms_Agregarpostulacion();
    $this->SwapBytes_Form->set($this->view->form);
    $this->view->form = $this->SwapBytes_Form->get();
    $params = $this->Une_Filtros->getParams();
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
    $this->view->titulo                   = $this->Title;
    $this->view->filters                  = $this->Une_Filtros;
    $this->view->SwapBytes_Ajax           = $this->SwapBytes_Ajax;
    $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
    $this->view->SwapBytes_Jquery_Mask    = $this->SwapBytes_Jquery_Mask;
    $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
    $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
    $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
    $this->view->SwapBytes_Jquery_Ui_Form = $this->SwapBytes_Jquery_Ui_Form;
    $this->view->SwapBytes_Ajax->setView($this->view);
    //Carga 
    if(isset($_GET['txtBuscar']) and isset($_GET['selDateDesde']) and isset($_GET['selDateHasta']) and isset($_GET['id'])){
      $this->view->search = $_GET['txtBuscar'];
      $this->view->desde  = $_GET['selDateDesde'];
      $this->view->hasta  = $_GET['selDateHasta'];
      $this->view->id     = $_GET['id'];
    }
  }

  public function getpictureAction(){
        $this->SwapBytes_Ajax->setHeader();
        $empresa = $this->_getAllParams();

        if(isset($empresa['id'])){
            $id = $empresa['id'];
            $foto = $this->instituciones->getPicture($id);
            if(empty($foto)){
                $foto = file_get_contents(APPLICATION_PATH . '/../public/images/empresa-not-found.jpg');
            }
            $this->getResponse()
                     ->setHeader('Content-type', 'image/jpeg')
                     ->setBody($foto);
        }
    }

  public function listAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $pageNumber  = $this->_getParam('page', 1);
      $searchData  = $this->_getParam('buscar');
      $itemPerPage = 10;
      $pageRange   = 10;
      $params      = $this->Une_Filtros->getParams(null,array('empresa','vacante','selDateDesde','selDateHasta'));
      // Definimos los valores de la Busqueda
      $this->vacantes->setSearch($searchData);
      // Arreglamos fechas
      if(!empty($params["selDateDesde"]) or !empty($params["selDateHasta"])) {
        if(empty($params["selDateDesde"])) {
          $params["selDateDesde"] = date("Y-m-d");
        }
        if(empty($params["selDateHasta"])) {
          $params["selDateHasta"] = date("Y-m-d");
        }
        if($params["selDateDesde"] > $params["selDateHasta"]) {
          $params["selDateDesde"] = $params["selDateHasta"];
        }
      }
      //Datos
      $paginatorCount = $this->vacantes->getDateCount($params['selDateDesde'],$params['selDateHasta'], null);
      $rows           = $this->vacantes->getVacantes($itemPerPage, $pageNumber,$params['selDateDesde'],$params['selDateHasta']);
      $HTML = "";
      //Vacantes
      foreach ($rows as $row) {
        $id          = $row['pk_vacante'];
        $institucion = $row['fk_institucion'];
        $HTML .= '<article style="" class="col-xs-12"><a href="#" id="'.$id.'" class="more" onclick="setModal(this.id)"><span class="glyphicon glyphicon-search" data-toggle="tooltip" data-placement="right" title="Mas informacion"></span></a><div class="body"><div class="col-xs-12 col-sm-4 col-md-3 picture"><img src="../images/empresa-not-found.jpg" id="'.$id.'" alt="PICTURE" class="img-responsive pic '.$institucion.'"></div><div class="col-xs-12 col-sm-8 col-md-9 caption"><h4>Empresa <strong>'.$row['empresa'].'</strong> solicita: </h4><h3>'.$row['title'].'</h3><em class="text-muted">'.$row['publicacion'].' - '.$row['culminacion'].'</em><p>'.substr($row['descripcion'], 0,255).'. . .'.'</p></div></div><div class="clear"></div></article>';
      }
      //Contador de Paginas
      $count    = round($paginatorCount/$pageRange) + 1;
      if($paginatorCount < ($count * 10) - 10){
        $count = $count - 1;
      }
      elseif($paginatorCount > ($count * 10)){
        $count = $count + 1;
      }
      $next     = $pageNumber + 1;
      $previous = $pageNumber - 1;
      //Paginator
      if($paginatorCount > $pageRange){
        $HTML .= '<div class="pagination col-xs-12">';
        for ($i = 1; $i <= $count; $i++) { 
          //Botones Inicio y Anterior
          if($i == 1 and $pageNumber == 1){
            $HTML .= '<span class="disabled"><span class="glyphicon glyphicon-chevron-left"></span>Inicio</span><span class="disabled"><span class="glyphicon glyphicon-menu-left"></span> Anterior</span>';
          }
          elseif ($i == 1 and $pageNumber != 1) {
            $HTML .= '<a href="#" onclick="goPage(1)"><span class="glyphicon glyphicon-chevron-left"></span>Inicio</a><a href="#" onclick="goPage('.$previous.')"><span class="glyphicon glyphicon-menu-left"></span> Anterior</a>';
          }
          //Pagination
          if($i == $pageNumber){
            $HTML .= "<span class=\"current\">{$pageNumber}</span><input id=\"page\" name=\"page\" value=\"{$pageNumber}\" type=\"hidden\" />";
          }
          else {
            $HTML .= "<a href=\"#\" onclick=\"goPage({$i})\">{$i}</a>";
          }
          //Botones Siguiente y Fin
          if($i == $count and $pageNumber == $count){
            $HTML .= '<span class="disabled">Siguiente <span class="glyphicon glyphicon-menu-right"></span></span><span class="disabled">Fin<span class="glyphicon glyphicon-chevron-right"></span></span>';
          }
          elseif ($i == $count and $pageNumber != $count) {
            $HTML .= '<a href="#" onclick="goPage('.$next.')">Siguiente <span class="glyphicon glyphicon-menu-right"></span></a><a href="#" onclick="goPage('.$count.')">Fin<span class="glyphicon glyphicon-chevron-right"></span></a>';
          }

        }
        $HTML .= "<div class=\"info\"><span class=\"glyphicon glyphicon-folder-open\"></span>Pagina: <strong>{$pageNumber}</strong> de {$count} - Total: <strong>{$paginatorCount}</strong></div></div>";
      }
      $json[] = $this->SwapBytes_Jquery->setHtml('tblVacantes', $HTML);
      $json[] = 'var clases = [];
                $(".pic").each(function(){
                  var obj = this.className.split(" "); 
                  for(i = 0; i < obj.length; i++){
                    var subobj = Number(obj[i]);
                    if(!isNaN(subobj) && $.inArray(subobj,clases) < 0){
                      clases.push(subobj);
                      $("." + subobj.toString()).attr("src",urlAjax + "getPicture/id/" + subobj);
                    }
                  }
                });';
      $this->getResponse()->setBody(Zend_Json::encode($json));
    }
  }

  private function getData($id) {
    $dataRow = $this->vacantes->getVacante($id);
    if(isset($dataRow)) {
      return $dataRow[0];
    }
  }

  public function viewAction() {
    $this->SwapBytes_Ajax->setHeader();
    $data            = $this->_getAllParams();
    $id              = intval($data['id']);
    $dataRow         = $this->getData($id);
    $sexo            = $this->atributos->getByPk($dataRow['fk_sexo']);
    $dataRow['sexo'] = $sexo[0]['valor'];
    $empresa         = $dataRow['id'];
    $url             = $_SERVER['HTTP_HOST'].$cv_path;
    $alm             = "../profile/cv".$data['url'];
    //Count CV
    $cv              = $this->usuariosarchivos->countCV();
    if($cv > 0){
      //Import CV
      $data          = $this->usuariosarchivos->getDocument(20117,$this->current_user->userId);
      $route         = substr($data[0]['ruta'], 2);
      $cv_path       = $url.$route;
      $user          = intval($this->current_user->userId);
      $postulacion = $this->postulaciones->getPostulado($user,$dataRow['id_vacante']);
      if($postulacion > 0){
        $postulado     = $this->postulaciones->getPostulacionCedula($user,$dataRow['id_vacante']);
        $content       = '<div><p style="max-width: 325px; margin: 5px auto; font-size: 120%;">Usted ya se postulo el <strong>'.$postulado['fecha'].'</strong></p></div>';
        $buttons       = "buttons: {
                            \"Ver CV\": function(){
                               if(!$(\".invalid\").length){
                                window.location.href = \"{$alm}\";
                              }
                            },
                            \"Cancelar\": function(){
                              if(!$(\".invalid\").length){
                                $(\"#frmDialog\").dialog(\"close\");
                              }
                            }
                          }";
      }
      else {
        $content       = '<div><p style="max-width: 325px; margin: 5px auto; font-size: 120%;">Usted ya posee un CV en el Sistema actualizado en la fecha <strong>'.$data[0]['fecha'].'</strong> Puede actualizarlo o postularse con este</p></div>';
        $buttons       = "buttons: {
                            \"Ver / Actualizar\": function(){
                               if(!$(\".invalid\").length){
                                window.location.href = \"{$alm}\";
                                $(\"#frmDialog\").dialog(\"close\");
                              }
                            },
                            \"Postularse\": function(){
                              if(!$(\".invalid\").length){
                                post();
                                $(\"#frmDialog\").dialog(\"close\");
                                $(\"#frmModal\").dialog(\"close\");
                              }
                            },
                            \"Cancelar\": function(){
                              if(!$(\".invalid\").length){
                                $(\"#frmDialog\").dialog(\"close\");
                              }
                            }
                          }";
      }
    }
    else{
      $content       = '<div><p style="max-width: 325px; margin: 5px auto; font-size: 120%;">Usted no posee un CV en el Sistema, por favor carguelo para postularse</p></div>';
      $buttons       = "buttons: {
                          \"Cargar\": function(){
                            if(!$(\".invalid\").length){
                              window.location.href = \"{$alm}\";
                              $(\"#frmDialog\").dialog(\"close\");
                            }
                          },
                          \"Cancelar\": function(){
                            if(!$(\".invalid\").length){
                              $(\"#frmDialog\").dialog(\"close\");
                            }
                          }
                        }";
    }
    $this->SwapBytes_Crud_Form->setJson(
      array(
        "$(\"#correo\").removeAttr('disabled'); $(\"#telefono\").removeAttr('disabled'); $(\"#celular\").removeAttr('disabled'); $(\"#cv_btn\").removeAttr('disabled'); ",
        "$(\"#frmModal\").dialog(\"option\",\"hide\", { effect: \"clip\", duration: 1000 } );
         $(\"#frmModal\").dialog({ 
            buttons: [
              { 
                text: \"Postularse\",
                \"class\": \"btn_cv\",
                click: function(){
                  $(\"#frmDialog\").dialog(\"open\");
                  $(\"#frmDialog\").dialog(\"option\",\"hide\", { effect: \"clip\", duration: 1000 } );
                  $(\"#frmDialog\").dialog({ {$buttons} });
                  $(\"#frmDialog\").html('$content');
                  $(\"#frmDialog\").dialog({title: \"Curriculum Vitae\"});
                  $(\"#frmDialog\").dialog('option','position','center');
                }
              },
              {
                text: \"Cancelar\",
                click: function(){ 
                  if(!$(\".invalid\").length){ $(\"#frmModal\").dialog(\"close\"); }
                }
              }
            ]
          });",
        "$(\"#postularse\").attr(\"disabled\", \"disabled\"); $(\"#postularse\").hide();",
        "$(\"#foto\").attr('src',urlAjax + 'getPicture/id/' + ".$empresa.");"
      )
    );
    $this->SwapBytes_Crud_Form->setProperties($this->view->form,$dataRow,$this->FormTitle_View);
    $this->SwapBytes_Crud_Form->setWidthLeft('130px');
    $this->SwapBytes_Crud_Form->getView();
  }

  public function agregarAction(){
    $this->SwapBytes_Ajax->setHeader();
    $data        = $this->_getAllParams();
    $id          = intval($data['id']);
    $user        = intval($this->current_user->userId);
    $postulacion = $this->postulaciones->getPostulado($user,$id);
    $arrayPost   = array(
      'fk_vacante' => $id,
      'fk_usuario' => $user,
      'fecha'      => date("Y-m-d")
    );
    if($postulacion < 1){
      //Si no esta Postulado los Registro
      $this->postulaciones->addRow($arrayPost);
      $postulacion = $this->postulaciones->getPostulado($user,$id);
      if($postulacion){
        $json[] = 'sweetAlert({title: "Exito", text: "'.$this->msg_success.'", type: "success",  showCancelButton: false, confirmButtonColor: "#00787A", closeOnConfirm: true},function(){});';
      }
      else {
        $json[] = 'sweetAlert("Error", "'.$this->msg_error.'", "error");';
      }
    }
    echo Zend_Json::encode($json); 
  }

}
?>