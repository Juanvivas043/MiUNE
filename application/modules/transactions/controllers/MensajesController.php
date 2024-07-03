<?php

class Transactions_MensajesController extends Zend_Controller_Action {

    private $Title = 'Mensajes';

    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Clases');
        Zend_Loader::loadClass('Models_DbTable_Recursos');
        Zend_Loader::loadClass('Models_DbTable_Mensajes');
        Zend_Loader::loadClass('Models_DbView_Estrategias');
        Zend_Loader::loadClass('Models_DbView_Evaluaciones');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Forms_Mensaje');

        $this->Asignaciones    = new Models_DbTable_Asignaciones();
        $this->Usuarios    = new Models_DbTable_Usuarios();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->Clases          = new Models_DbTable_Clases();
        $this->Recursos        = new Models_DbTable_Recursos();
        $this->Mensajes        = new Models_DbTable_Mensajes();
        $this->vw_estrategias  = new Models_DbView_Estrategias();
        $this->vw_evaluaciones = new Models_DbView_Evaluaciones();
        $this->filtros         = new Une_Filtros();

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
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();

        /*
         * Configuramos los filtros.
         */
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->session = new Zend_Session_Namespace('Mensajes');

        $this->filtros->setDisplay(false, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);
        $this->filtros->setParam('usuario', $this->authSpace->userId);

        /*
         * Configuramos los botones.
         */
		$this->SwapBytes_Crud_Action->setDisplay(false, false, true, true, false, false);
		$this->SwapBytes_Crud_Action->setEnable(true, true, true, true, false, false);
//                $this->SwapBytes_Crud_Action->addCustum("<button id=\"btnBackCrono\" class=\"ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\" 
////                                                        name=\"btnBackCrono\" role=\"button\" aria-disabled=\"false\">Volver a Cronogramas</button>");
//                $this->SwapBytes_Crud_Action->addCustum("<button id=\"btnVerRecurso\" class=\"ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\" 
//                                                        name=\"btnVerRecurso\" role=\"button\" aria-disabled=\"false\">Ver Reporte</button>");
                $this->SwapBytes_Crud_Action->addJavaScript("$('#btnVerRecurso').click(function(){
                                                                $.getJSON(urlAjax + \"buscar/filters/\"+escape($('#tblFiltros').find(':input').serialize())+\"\", function(d){executeCmdsFromJSON(d)});
                                                            });");
                $this->SwapBytes_Crud_Search->setDisplay(false);

        /*
         * Mandamos a crear el formulario para ser utilizado mediante el AJAX.
         */
        $this->view->form = new Forms_Mensaje();

        $this->SwapBytes_Form->set($this->view->form);
//        $this->SwapBytes_Form->fillSelectBox('fk_tipo', $this->Recursos->getTiposRecursos() , 'pk_atributo', 'valor');
        // $this->SwapBytes_Form->fillSelectBox('fk_tipoevaluacion', $this->vw_evaluaciones->get(), 'pk_atributo', 'valor');

        $this->view->form = $this->SwapBytes_Form->get();
        
//        $this->view->formview = new Forms_Recursoview();
//
//        $this->SwapBytes_Form->set($this->view->formview);
//        $this->SwapBytes_Form->fillSelectBox('fk_tipo', $this->Recursos->getTiposRecursos() , 'pk_atributo', 'valor');
//        $this->view->formview = $this->SwapBytes_Form->get();
        
        /*
         * Mandamos a crear el formulario para ser utilizado para los 
         * Contenidos mediante el AJAX.
         */
        // $this->view->formcontenidos = new Forms_Contenido();
        // $this->SwapBytes_Form->set($this->view->formcontenidos);
        // $this->view->formcontenidos = $this->SwapBytes_Form->get();
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/tiny_mce/tiny_mce.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/file-uploader/client/fileuploader.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jquery.elementReady.js');
//        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/jquery.wijmo-open.1.3.0.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/js/file-uploader/client/fileuploader.mensajes.css');

		/*
		 * Obtiene los parametros de los filtros y del modal.
		 */
		$this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
		$this->_params['filters'] = $this->filtros->getParams();
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

    public function indexAction() {
        $this->view->title   = $this->Title;
        $this->view->filters = $this->filtros;
        $this->session->msj_filtros = null;
        $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->view->SwapBytes_Ajax->setView($this->view);
    }

    private function prepareRowAutocomplete($vals, $label, $cols) {
      $return = array();
      if(!is_array($vals))
          return;
      if(!is_array($label))
          return;
      if(!is_array($cols))
          return;
      switch(count($label)){
          case 2:
              foreach($vals as $val){
                  array_push($return, array($label[0] => $val[$cols[0]], $label[1] => $val[$cols[1]]));
              }
              break;
          case 3:
              foreach($vals as $val){
                  array_push($return, array($label[0] => $val[$cols[0]], $label[1] => $val[$cols[1]], $label[2] => $val[$cols[2]]));
              }
              break;
          case 4:
              foreach($vals as $val){
                  array_push($return, array($label[0] => $val[$cols[0]], $label[1] => $val[$cols[1]], $label[2] => $val[$cols[2]], $label[3] => $val[$cols[3]]));
              }
              break;
          default:
              return null;
          break;
      }
      return $return;
    }
    
    public function checkpermisosanuncioAction(){
        $this->SwapBytes_Ajax->setHeader();
        $grupos = $this->grupo->getGrupos();

        if(!($this->buscarValorenMatriz(array(905, 854), $grupos))){
            $json[] = "$('#tweetForm').remove()";
        }
        $this->getResponse()->setBody(Zend_Json::encode($json));
    }

    
    public function usuariosAction(){
        $this->SwapBytes_Ajax->setHeader();
        $grupos = $this->grupo->getGrupos();
        $valores = null;
        foreach ($grupos as $key => $grupo) {
            if($key == 0)
                $coma = '';
            $valores .= $coma.$grupo['pk_grupo'];
            $coma = ', ';
        }
        
        $dataRows = $this->Mensajes->getListaUsuarios($valores);  
        $values = $this->prepareRowAutocomplete($dataRows, array('nombre', 'cedula'), array('nombre', 'pk_usuario'));
        $this->getResponse()->setBody(Zend_Json::encode($values));
    }
    
    public function gruposAction(){
        $this->SwapBytes_Ajax->setHeader();
        $grupos = $this->grupo->getGrupos();
        $valores = null;
        foreach ($grupos as $key => $grupo) {
            if($key == 0)
                $coma = '';
            $valores .= $coma.$grupo['pk_grupo'];
            $coma = ', ';
        }
                $loggerFB = Zend_Registry::get('loggerFB');
                $loggerFB->log('Prueba!!', Zend_Log::INFO);
        
        if(!(strpos($grupos, 854) === 'false')){
                $valores .= ", 855"; 
                $loggerFB->log('Profesor', Zend_Log::INFO);
        }
        if(!(strpos($grupos, 905) === 'false')){
                $valores .= ", 855, 854"; 
                $loggerFB->log('Director de Escuela', Zend_Log::INFO);
        }
        $dataRows = $this->Mensajes->getListaGrupos($valores);
        $values = $this->prepareRowAutocomplete($dataRows, array('nombre', 'id'), array('grupo', 'fk_grupo'));
        $this->getResponse()->setBody(Zend_Json::encode($values));
    }
    
    public function asignacionesAction(){
        $this->SwapBytes_Ajax->setHeader();
        $grupos = $this->grupo->getGrupos();
        $loggerFB = Zend_Registry::get('loggerFB');
        
        if($this->session->msj_filtros){
            $dataRows = $this->session->msj_filtros;
        }else{
            if($this->buscarValorenMatriz(array(905), $grupos)){
            $loggerFB->log('Director de Escuela', Zend_Log::INFO);
            $dataRows = $this->Mensajes->getFiltrosDirEscuela();
            }else{
            $dataRows = $this->Mensajes->getFiltros(121);
            }
            $this->session->msj_filtros = $dataRows;
        }
        
        $values = $this->prepareRowAutocomplete($dataRows, array('materia', 'id', 'codigo', 'seccion' ), array('materia', 'pk_seccion', 'codigo', 'seccion'));
        $this->getResponse()->setBody(Zend_Json::encode($values));
    }
    
    public function sedeAction() {
      if($this->session->msj_filtros){
          $parametros = $this->session->msj_filtros;
      }else{
          $parametros = $this->Mensajes->getFiltros(121);
          $this->session->msj_filtros = $parametros;
      }
      $dataRows = array();
      foreach($parametros as $parametro){
          $needle = array($parametro['pk_sede'], $parametro['sede']);
          if(!in_array($needle, $dataRows))
            array_push($dataRows, $needle);
      }
      $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    public function escuelaAction() {
      $parametros = $this->session->msj_filtros;
      $dataRows = array();
      foreach($parametros as $parametro){
          if($this->_getParam('sede') == $parametro['pk_sede']){
              $needle = array($parametro['pk_escuela'], $parametro['escuela']);
              if(!in_array($needle, $dataRows))
                array_push($dataRows, $needle);
          }
      }
      $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

//    public function pensumAction() {
//      $parametros = $this->Clases->getFiltrosClase( );
//      $this->SwapBytes_Ajax_Action->fillSelect($parametros[0]['pk_pensum'], $parametros[0]['pensum']);
//    }

    public function semestreAction() {
      $parametros = $this->session->msj_filtros;
      $dataRows = array();
      foreach($parametros as $parametro){
          if(($this->_getParam('sede') == $parametro['pk_sede'])
           &&($this->_getParam('escuela') == $parametro['pk_escuela'])      
          ){
              $needle = array($parametro['pk_semestre'], $parametro['semestre']);
              if(!in_array($needle, $dataRows))
                array_push($dataRows, $needle);
          }
      }
      $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    public function materiaAction() {
      $parametros = $this->session->msj_filtros;
      $dataRows = array();
      foreach($parametros as $parametro){
          if(($this->_getParam('sede') == $parametro['pk_sede'])
           &&($this->_getParam('escuela') == $parametro['pk_escuela'])      
           &&($this->_getParam('semestre') == $parametro['pk_semestre'])      
          ){
              $needle = array($parametro['pk_materia'], $parametro['materia']);
              if(!in_array($needle, $dataRows))
                array_push($dataRows, $needle);
          }
      }
      $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

//    public function turnoAction() {
//      $parametros = $this->Clases->getFiltrosClase( );
//      $this->SwapBytes_Ajax_Action->fillSelect($parametros[0]['pk_turno'], $parametros[0]['turno']);
//    }

    public function seccionAction() {
      $parametros = $this->session->msj_filtros;
      $dataRows = array();
      foreach($parametros as $parametro){
          if(($this->_getParam('sede') == $parametro['pk_sede'])
           &&($this->_getParam('escuela') == $parametro['pk_escuela'])      
           &&($this->_getParam('semestre') == $parametro['pk_semestre'])      
           &&($this->_getParam('materia') == $parametro['pk_materia'])      
          ){
              $needle = array($parametro['pk_seccion'], $parametro['seccion']);
              if(!in_array($needle, $dataRows))
                array_push($dataRows, $needle);
          }
      }
      $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

//    public function carpetasAction() {
//       $this->SwapBytes_Ajax->setHeader();
//       $rows = $this->Mensajes->getCarpetas();
////       $json = '[';
////       foreach($rows as $key => $row){
////            $json[] = "<option value=\"{$rows['pk_carpeta']}\" style=\"text-align: center; font-weight: bolder;\">{$rows['nombre']}</option>";
////            $json .= "{optionValue:{$rows['pk_carpeta']}, optionDisplay:{$rows['nombre']}}";
////       }
////       $json .= ']';
////       $this->SwapBytes_Form->fillSelectBox();
//       $this->SwapBytes_Ajax_Action->fillSelect($rows[0]['pk_carpeta'], $parametros[0]['nombre']);
//       $this->getResponse()->setBody(Zend_Json::encode($json));
//    }

    public function addanuncioAction(){
       $this->SwapBytes_Ajax->setHeader();
        if(ini_get('magic_quotes_gpc'))
        $_POST['inputField']=stripslashes($_POST['inputField']);
        
        $_POST['inputField'] = pg_escape_string($_POST['inputField']);
        
//       var_dump($_POST['inputField']);
//        if(mb_strlen($_POST['inputField']) < 1 || mb_strlen($_POST['inputField'])>140)
//        die("0");

       $dataRow['titulo'] = $_POST['inputField'];
       $dataRow['fk_tipo'] = 1727;
       $this->Mensajes->addNuevoMensaje($dataRow);
       $usuario = $this->Usuarios->getRow($this->authSpace->userId);
//        mysql_query("INSERT INTO demo_twitter_timeline SET tweet='".$_POST['inputField']."',dt=NOW()");

//        if(mysql_affected_rows($link)!=1)
//        die("0");

        echo $this->formatTweet($_POST['inputField'],time(),$usuario['nombre']);
       
       
       
       
       
    }
    
    public function anuncioAction(){
       $this->SwapBytes_Ajax->setHeader();

               // remove tweets older than 1 hour to prevent spam
//        mysql_query("DELETE FROM demo_twitter_timeline WHERE id>1 AND dt<SUBTIME(NOW(),'0 1:0:0')");

        //fetch the timeline
//        $q = mysql_query("SELECT * FROM demo_twitter_timeline ORDER BY ID DESC");
        $rows = $this->Mensajes->getAnuncios();

        $timeline='';
        foreach($rows as $row){
                $timeline.=$this->formatTweet($row['titulo'],$row['fecha_creacion'],$row['nombre_emisor']);
        }

        // fetch the latest tweet
        $lastTweet = '';
        $anuncios = $this->Mensajes->getAnunciosUsuario();
        $lastTweet = $anuncios[0]['titulo'];

        if(!$lastTweet) $lastTweet = "No tienes anuncios todavia!";
//       $json = array();
//           $html = "<div id=\"anuncios\">";
//       foreach($rows as $key => $row){
//           $html .= "<div id=\"an{$row['pk_mensaje']}\">";
//           $html .= "<span class=\"emisor\">{$row['nombre_emisor']} dice: ";
//           $html .= "</span>";
//           $html .= "<p>{$row['titulo']}";
//           $html .= "</p>";
//           $html .= "</div>";
//       }
//           $html .= "</div>";
       $json[] = $this->SwapBytes_Jquery->setHtml('timeline', $timeline);
//       
//       $this->Mensajes->addNuevoMensaje($dataRow);
//       
//       $this->getResponse()->setBody($timeline);
       if ($this->_request->isXmlHttpRequest()) {
           $this->getResponse()->setBody(Zend_Json::encode($timeline));
       }else{
           echo ($timeline);
       }
    }
    
    public function buscarAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
//            $Data   = $this->filtros->getParams();


            
//            $this->Asignaciones->setData($this->_params['filters'], array('periodo','sede','escuela','pensum','semestre','materia','turno','seccion'));
            $asignacion = $this->Recursos->getFKAsignacion($this->session->id);
//            $asignacion = $this->Asignaciones->getPK();
//            echo $asignacion;
            
            $this->session->asig =  $asignacion;
//            echo $this->session->asig;
        }
        
    }

    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $carpeta = $this->_getParam('carpeta');

            if(isset($carpeta)){
                $this->session->carpeta = $carpeta;
            }else{
                $carpeta = $this->session->carpeta;
            }
            
            // $rows = $this->Clases->getCronogramas();
            switch ($carpeta) {
                case 1:
                    $rows = $this->Mensajes->getMensajesReceptor($this->authSpace->userId);
                    $columns = array(array('column'  => 'pk_mensaje',
                                           'primary' => true,
                                           'hide'    => true),
                                     array('name'    => array('control' => array('tag'        => 'input',
                                                                                 'type'       => 'checkbox',
                                                                                 'name'       => 'chkSelectDeselect')),
                                           'column'  => 'nc',
                                           'width'   => '20px',
                                           'rows'    => array('style' => 'text-align:center'),
                                           'control' => array('tag'   => 'input',
                                                              'type'  => 'checkbox',
                                                              'name'  => 'chkMensaje',
                                                              'value' => '##pk_mensaje##')),
                                     array('name'    => 'De parte de:',
                                           'width'   => '200px',
                                           'column'  => 'nombre_emisor'),
                                     array('name'    => 'Tipo',
                                           'width'   => '80px',
                                           'column'  => 'tipo_mensaje'),
                                     array('name' => 'Recibido',
                                           'width'=> '50px',
                                           'column' => 'carpeta_fecha'),
                                     array('name' => 'Titulo',
                                           'width'=> '250px',
                                           'column' => 'titulo'),
                                     array('name' => 'Contenido',
                                           'width'=> '250px',
                                           'column' => 'contenido'));
                $table = array('class' => 'tableData');
                
                $other = array(
                   array('actionName' => '',
                         'label'      => 'Responder',
                         'action'     => 'responder(##pk##);'));

                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VO', $other);
                    break;
                case 3:
                case 2:
                    $rows = $this->Mensajes->getMensajesEmisor($this->authSpace->userId, $this->session->carpeta);
                    $columns = array(array('column'  => 'pk_mensaje',
                                           'primary' => true,
                                           'hide'    => true),
                                     array('name'    => array('control' => array('tag'        => 'input',
                                                                                 'type'       => 'checkbox',
                                                                                 'name'       => 'chkSelectDeselect')),
                                           'column'  => 'nc',
                                           'width'   => '20px',
                                           'rows'    => array('style' => 'text-align:center'),
                                           'control' => array('tag'   => 'input',
                                                              'type'  => 'checkbox',
                                                              'name'  => 'chkMensaje',
                                                              'value' => '##pk_mensaje##')),
                                     array('name'    => 'Para:',
                                           'width'   => '200px',
                                           'column'  => 'receptores'),
                                     array('name'    => 'Tipo',
                                           'width'   => '80px',
                                           'column'  => 'tipo_mensaje'),
                                    array('name' => 'Creado',
                                           'width'=> '50px',
                                           'column' => 'fecha_creacion'),
                                     array('name' => 'Titulo',
                                           'width'=> '250px',
                                           'column' => 'titulo'),
                                     array('name' => 'Contenido',
                                           'width'=> '250px',
                                           'column' => 'contenido'));
                $table = array('class' => 'tableData');
                if($carpeta == 2){
                $other = array(
                   array('actionName' => '',
                         'label'      => 'Reenviar',
                         'action'     => 'reenviar(##pk##);'));
                }else if($carpeta == 3){
                $other = array(
                   array('actionName' => '',
                         'label'      => 'Enviar',
                         'action'     => 'enviar(##pk##);'));
                    
                }
                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VO', $other);
                    break;
                default:
                $table = array('class' => 'tableData');

                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'V');
                    break;
            }
            
            if(isset($rows) && count($rows) > 0) {

//                $table = array('class' => 'tableData');
//
//                $other = array(
//                   array('actionName' => '',
//                         'label'      => 'Responder'));
//                
//                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VR', $other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkMensaje');
            } else {
                $HTML   = $this->SwapBytes_Html_Message->alert("No tiene Mensajes.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }
                
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function tipomensajeAction(){
        $grupos = $this->grupo->getGrupos();
        $json[] = "$(\"#btnTSiguiente\").click(function (){ 
                        $.getJSON(urlAjax + \"addoreditload/fk_tipo/\" + $(\"#selTipoMsj\").val(), function(d){
                            executeCmdsFromJSON(d)
                        });             
                    })";
        $json[] = "boton = $(\"#btnTSiguiente\")";
        $json[] = "$(\".ui-dialog-buttonset\").find(\"#btnTSiguiente\").remove()";
        $json[] = "$(\"#btnTSiguiente\").remove()";
        $json[] = "$(\".ui-dialog-buttonset\").prepend(boton)";
        $html  = "<div class=\"zend_form\">";
        $html .= "<div id=\"tmsj_filtros\">";
        $html .= "<dt>Tipo de Mensaje:";
        $html .= "</dt>";
        $html .= "<dd><select id=\"selTipoMsj\"  style=\"width:200;\" name=\"tipo_msj\">
                            <option value=\"1726\">Normal</option>";
        if($this->buscarValorenMatriz(array(855, 854), $grupos))   
                 $html .= "<option value=\"1729\">Pregunta</option>";
        if($this->buscarValorenMatriz(array(854), $grupos))   
                 $html .= "<option value=\"1728\">Entrega Online</option>";
//        $html .= "</select> </div>";
        $html .= "</select></dd>";
//        $html .= "</td></tr>";

        $html .= "<button id=\"btnTSiguiente\"
                          class=\"ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\" 
                          name=\"btnTSiguiente\" 
                          role=\"button\" aria-disabled=\"false\"><span class=\"ui-button-text\">Siguiente</span>
                 </button>";
        $html .= "<dt id=\"selTPsedeL\">Sede:";
        $html .= "</dt>";
        $html .= "<dd id=\"selTPsedeI\"><select id=\"selSede\" style=\"width:200;\" disabled=\"disabled\"><select>";
        $html .= "</dd>";
        $html .= "<dt id=\"selTPescuelaL\">Escuela:";
        $html .= "</dt>";
        $html .= "<dd id=\"selTPescuelaI\"><select id=\"selEscuela\"  style=\"width:200;\" disabled=\"disabled\"><select>";
        $html .= "</dd>";
        $html .= "<dt id=\"selTPsemestreL\">Semestre:";
        $html .= "</dt>";
        $html .= "<dd id=\"selTPsemestreI\"><select id=\"selSemestre\"  style=\"width:200;\" disabled=\"disabled\"><select>";
        $html .= "</dd>";
        $html .= "<dt id=\"selTPmateriaL\">Materia:";
        $html .= "</dt>";
        $html .= "<dd id=\"selTPmateriaI\"><select id=\"selMateria\"  style=\"width:200;\" disabled=\"disabled\"><select>";
        $html .= "</dd>";
        $html .= "<dt id=\"selTPseccionL\">Seccion:";
        $html .= "</dt>";
        $html .= "<dd id=\"selTPseccionI\"><select id=\"selSeccion\"  style=\"width:200;\" name=\"asignacion\" disabled=\"disabled\"><select>";
        $html .= "</dd>";
        $html .= "<dt id=\"selTEfechaL\">Fecha de Entrega:";
        $html .= "</dt>";
        $html .= "<dd id=\"selTEfechaI\"><select id=\"selTEfecha\"  style=\"width:200;\">
                      <option value=\"1\">Día de Entrega</option>  
                      <option value=\"2\">Rango de Entrega:</option>  
                  <select>";
        $html .= "</dd>";
        $html .= "<dt id=\"TEdiaL\">Día: ";
        $html .= "</dt>";
        $html .= "<dd id=\"TEdiaI\"><input id=\"TEdia\" class\"hasDatepicker\" type=\"text\" maxlength=\"10\" size=\"11\" value=\"\" name=\"TEdia\">";
        $html .= "</dd>";
        $html .= "<dt id=\"TErangoSL\">Desde: ";
        $html .= "</dt>";
        $html .= "<dd id=\"TErangoSI\"><input id=\"TErangoS\" class\"hasDatepicker\" type=\"text\" maxlength=\"10\" size=\"11\" value=\"\" name=\"TErangoS\">";
        $html .= "</dd>";
        $html .= "<dt id=\"TErangoIL\">Hasta: ";
        $html .= "</dt>";
        $html .= "<dd id=\"TErangoII\"><input id=\"TErangoI\" class\"hasDatepicker\" type=\"text\" maxlength=\"10\" size=\"11\" value=\"\" name=\"TErangoI\">";
        $html .= "</dd>";
        $html .= "</div>";
        $html .= "</div>";
        $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('TEdia');
        $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('TErangoS');
        $json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('TErangoI');
        $json[] = "checkTmsj()";
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, null, 'Nuevo Mensaje');
//        $this->SwapBytes_Crud_Form->setSize(800, 2000);
        $this->SwapBytes_Crud_Form->getView($html);
    }
    
    private function buscarValorenMatriz($needles, $haystacks){
        if(is_array($haystacks)){
            $found = array();
                
            foreach ($haystacks as $haystack) {
                if(is_array($needles)){
                    foreach ($needles as $needle) {
                        if(in_array($needle, $haystack)){
                            array_push($found, $needle);
                        }
                    }
                }else{
                    if(in_array($needles, $haystack)){
                        array_push($found, $needles);
                    }                 
                }
            }
            return $found;
        }else{
            return null;
        }
    }
    
    private function buscarpermisosDestino(){
        $grupos = $this->grupo->getGrupos();
        $json = array();   
        if(!($this->buscarValorenMatriz(array(855, 854, 905), $grupos))){
            $json[]="$('#usuarios-label').hide()";
            $json[]="$('#usuarios-element').hide()";
        }else{
            $json[]="destino_usuarios()";
        }
        if(!($this->buscarValorenMatriz(array(854, 905), $grupos))){
            $json[]="$('#grupos-label').hide()";
            $json[]="$('#grupos-element').hide()";
            $json[]="$('#asignaciones-label').hide()";
            $json[]="$('#asignaciones-element').hide()";
        }else{
            $json[]="destino_grupos()";
            $json[]="destino_asignaciones()";
        }
        return $json;
    }
    
    public function addoreditloadAction() {
    if ($this->_request->isXmlHttpRequest()) {
    $this->SwapBytes_Ajax->setHeader();
        $loggerFB = Zend_Registry::get('loggerFB');
        if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
                $dataRow       = $this->Mensajes->getRow($this->_params['modal']['id']);
                $dataRow['id'] = $this->_params['modal']['id'];
        }
        
        $json[] = "$(\".ui-dialog-buttonset\").find(\"#btnTSiguiente\").remove()";
        $fk_tipo = $this->_getParam('tipo_msj');
        $fk_mensaje = $this->_getParam('fk_mensaje');
        $pk_mensaje = $this->_getParam('pk_mensaje');
        $reenviar = $this->_getParam('reenviar');
        $grupos = $this->grupo->getGrupos();
        
        if(isset($fk_mensaje)){
           $dataRow       = $this->Mensajes->getRow($fk_mensaje);
           $dataRow['id'] = $fk_mensaje;
           $json[]="$('#usuarios-label').hide()";
           $json[]="$('#usuarios-element').hide()";
           $json[]="$('#grupos-label').hide()";
           $json[]="$('#grupos-element').hide()";
           $json[]="$('#asignaciones-label').hide()";
           $json[]="$('#asignaciones-element').hide()";
           $json[]="$('#fk_mensaje').val({$fk_mensaje})";
           $json[] = "startFileUploader()";
           $titulo = "Responder Mensaje";
        }
        if(isset($pk_mensaje)){
           $dataRow       = $this->Mensajes->getRow($pk_mensaje);
           $dataRow['id'] = $pk_mensaje;
           $permisos = $this->buscarpermisosDestino();
           $json = array_merge($json, $permisos);
           $loggerFB->log($json, Zend_Log::INFO);
           $json[]="$('#pk_mensaje').val({$pk_mensaje})";
           $json[]="$('#reenviar').val({$reenviar})";
           $json[] = "startFileUploader()";
           if($reenviar == 1){
               $titulo = "ReEnviar Mensaje";
           }else{
               $titulo = "Enviar Mensaje";
           }
        }
        if(isset($fk_tipo)){
        $dataRow['tipo_msj'] = $this->_getParam('fk_tipo');
        $dataRow[0]['fk_tipo'] = $fk_tipo;
        
        
        switch ($fk_tipo) {
            case 1729:
               $json[]="$('#usuarios-label').hide()";
               $json[]="$('#usuarios-element').hide()";
               $json[]="$('#grupos-label').hide()";
               $json[]="$('#grupos-element').hide()";
               $json[]="$('#asignaciones-label').hide()";
               $json[]="$('#asignaciones-element').hide()";
               $json[]="$('#asignacion').val({$this->_getParam('asignacion')})";
               $titulo = "Nueva Pregunta";
               $json[] = "startFileUploader()";
                break;
            case 1728:
               $json[]="$('#usuarios-label').hide()";
               $json[]="$('#usuarios-element').hide()";
               $json[]="$('#grupos-label').hide()";
               $json[]="$('#grupos-element').hide()";
               $json[]="$('#asignaciones-label').hide()";
               $json[]="$('#asignaciones-element').hide()";
               $json[]="$('#asignacion').val({$this->_getParam('asignacion')})";
               $json[]="$('#dia').val(\"{$this->_getParam('TEdia')}\")";
               $json[]="$('#fecha1').val(\"{$this->_getParam('TErangoI')}\")";
               $json[]="$('#fecha2').val(\"{$this->_getParam('TErangoS')}\")";
               $titulo = "Nueva Entrega Online";
               $json[] = "startFileUploader()";
                break;

            default:
                $titulo = "Nuevo Mensaje";
                $json[] = "startFileUploader()";
                $permisos = $this->buscarpermisosDestino();
                $json = array_merge($json, $permisos);
                if($this->buscarValorenMatriz(array(854), $grupos)){

                }else if($this->buscarValorenMatriz(array(854), $grupos)){

                }  
                break;
        }
        }
//        $dataRow['fecha'] = $this->SwapBytes_Date->convertToForm($dataRow['fecha']);
//        $json[]           = $this->SwapBytes_Jquery_Ui->setDatepicker('fecha');
		
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow[0], $titulo);
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();
    }
////        $this->SwapBytes_Ajax->setHeader();
//        $this->session->json = null;
//		if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
//			$dataRow       = $this->Recursos->getRow($this->_params['modal']['id']);
//			$dataRow['id'] = $this->_params['modal']['id'];
//                        $dataRow['publico']  = $this->SwapBytes_Form->setValueToBoolean($dataRow['publico']);
//                        $dataRow['contenido_html'] = html_entity_decode($dataRow['contenido_html']);
//
//		}
//        $this->session->chtml = null;
//        $this->session->filename = null;; 
//        
//        $json = array();
//        $json[] = "$(\"#frmModal\").parent().find('#fk_tipo').change(function(){
//            num = $(\"#frmModal\").parent().find('#fk_tipo').val();
//            id  = $(\"#frmModal\").parent().find('#id').val();
//            if(num == 1719){
//                    $(\"#file-uploader\").hide();
//                    startTinyMCE();
//                    $('#contenido_html-element').show();
//                    $('#contenido_html-label').show();
//                    click = $('#frmModal').parent().find(\"button:contains('Guardar')\").data('events').mouseover.length;
//                    if(click < 3){
//                    $('#frmModal').parent().find(\"button:contains('Guardar')\").mouseover(function(){
//            $('#contenido_html').text(tinyMCE.get('contenido_html').getContent());
//            chtml = $('#contenido_html').text();
//            $.post('{$this->view->baseUrl()}/transactions/recursos/savecontenido/', {'chtml' : chtml});
//                    });}
//            }
//            if(num == 1720){
//                    $('#contenido_html-element').hide();
//                    $('#contenido_html-label').hide();
//                    if(!id){
//                        exts = ['jpg', 'jpeg', 'gif', 'png'];
//                        startFileUploader(exts);
//                        $(\"#file-uploader\").show();
//                    }
//            }
//            if(num == 1721){
//                    $('#contenido_html-element').hide();
//                    $('#contenido_html-label').hide();
//                    if(!id){
//                        exts = ['zip', 'rar', '7z'];
//                        startFileUploader(exts);
//                        $(\"#file-uploader\").show();
//                    }
//            }
//            if(num == 1723){
//                    $('#contenido_html-element').hide();
//                    $('#contenido_html-label').hide();
//                    if(!id){
//                        exts = ['mp4'];
//                        startFileUploader(exts);
//                        $(\"#file-uploader\").show();
//                    }
//            }
//            if(num == 1724){
//                    $('#contenido_html-element').hide();
//                    $('#contenido_html-label').hide();
//                    if(!id){
//                        exts = ['mp3'];
//                        startFileUploader(exts);
//                        $(\"#file-uploader\").show();
//                    }
//            }
//            if(num == 1722){
//                    $('#contenido_html-element').hide();
//                    $('#contenido_html-label').hide();
//                    if(!id){
//                        exts = ['doc', 'docx', 'ppt', 'pptx', 'txt', 'pdf', 'dot', 'rtf', 'xml', 'xlsx', 'xls', 'csv'];
//                        startFileUploader(exts);
//                        $(\"#file-uploader\").show();
//                    }
//            }
//            })";
//
//        $json[] = "$(\"#frmModal\").parent().find('#fk_tipo').change()";
//        if(!empty($dataRow))
//            $json[] = "$(\"#frmModal\").parent().find('#fk_tipo').attr('disabled', 'disabled')";
//        $this->session->json = $json;
//        $this->SwapBytes_Crud_Form->setJson($json);
//        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Editar Cronograma');
//        $this->SwapBytes_Crud_Form->getAddOrEditLoad();
    }

	public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
                        $this->SwapBytes_Crud_Form->setJson($this->session->json);
			$this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
			$this->SwapBytes_Crud_Form->getAddOrEditConfirm();
		}
	}

    /**
     * Permite guardar el contenido de un determinado registro mediante una serie
     * de datos que fueron capturados por un formulario modal.
     */
    public function addoreditresponseAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
			// Obtenemos los parametros que se esperan recibir.
//             $this->Asignaciones->setData($this->_params['filters'], array('periodo','sede','escuela','pensum','semestre', 'materia', 'turno','seccion'));
			
                        $dataRow  = $this->_params['modal'];
                        $loggerFB = Zend_Registry::get('loggerFB');
                        $loggerFB->log($dataRow, Zend_Log::INFO);
                        $this->Mensajes->addNuevoMensaje($dataRow);
			$this->SwapBytes_Crud_Form->getAddOrEditEnd();
//			
//			
//                        $this->Mensajes->addNuevoMensaje($dataRow);
//			$id                        = $dataRow['id'];
//			$dataRow['id']             = null;
//			$dataRow['filtro']         = null;
//			$dataRow['fk_clase']       = $this->session->id;
//                        if(isset($this->session->chtml)){
//                            $dataRow['contenido_html'] = $this->session->chtml;
//                            $dataRow['contenido_html'] = htmlentities($dataRow['contenido_html'], ENT_QUOTES);
//                            $dataRow['contenido_html'] = nl2br($dataRow['contenido_html']);
//                            $dataRow['contenido_html'] = pg_escape_string($dataRow['contenido_html']);
//                        }
//                        if(isset($this->session->filename)) $dataRow['dir_archivo'] = $this->session->filename;
//                        
//			if(is_numeric($id) && $id > 0) {
//				$this->Recursos->updateRow($id, $dataRow);
//			} else if(is_numeric($this->session->id) && $this->session->id > 0) {
//                                    if(empty($dataRow['fk_tipo'])){
//                                $dataRow['fk_tipo'] = $dataRow['fk_tipo_alt'];
//                                $dataRow['fk_tipo_alt'] = null;
//                                    }
//				$this->Recursos->addRow($dataRow);
//			}
//			$this->session->chtml = null;
//			$this->session->filename = null;
//                        if($dataRow['fk_tipo'] == 1720)
//                            $this->createThumbs("uploads/{$this->session->id}/", "uploads/{$this->session->id}/thumbs/", 100);
		}
    }

    public function viewAction() {
       
        $dataRows = $this->Mensajes->getRow($this->_params['modal']['id']);
//        $dataRow['contenido_html'] = html_entity_decode($dataRow['contenido_html']);
//        $dataRow['contenido_html'] = pg_escape_string($dataRow['contenido_html']);
//        $this->SwapBytes_Crud_Form->addJS('rendertinyMCE();');
//        $this->SwapBytes_Crud_Form->addJS('startFileUploader();');
        if(!empty($dataRows)){  
            $num = count($dataRows);
            $html = "<dl class=\"zend_form\">";
            
            $html .= "<dt>Emisor:";
            $html .= "</dt>";
            $html .= "<dd>{$dataRows[0]['emisor']}";
            $html .= "</dd>";
            
            $html .= "<dt>Titulo:";
            $html .= "</dt>";
            $html .= "<dd>{$dataRows[0]['titulo']}";
            $html .= "</dd>";
            
            $html .= "<dt>Contenido:";
            $html .= "</dt>";
            $html .= "<dd>{$dataRows[0]['contenido']}";
            $html .= "</dd>";
            
            if(!empty($dataRows[0]['pk_adjunto'])){
                $html .= "<dt>Adjuntos:";
                $html .= "</dt>";
                if($num > 1){
                    foreach ($dataRows as $dataRow) {
                        if(!empty($dataRow['pk_adjunto'])){
                            $html .= "<dd><a href=\"{$this->view->baseURL()}/transactions/mensajes/download/id/{$dataRow['pk_adjunto']}\">{$dataRow['tipo_adjunto']}</a>{$dataRow['descripcion_adjunto']}";
//                            $html .= "<dd><a href=\"download/id/{$dataRow['pk_adjunto']}\">{$dataRow['Imagen']}</a>";
                            $html .= "</dd>";
                        }
                    }
                }else{
                    $html .= "<dd><a href=\"{$this->view->baseURL()}/transactions/mensajes/download?id={$dataRows[0]['pk_adjunto']}\">{$dataRows[0]['tipo_adjunto']}</a>{$dataRows[0]['descripcion_adjunto']}";
                    $html .= "</dd>";
                }
            }
            $html .= "</dl>";
        } 
//    foreach($dataRows as $dataRow){
//        
//    }
//    switch ($dataRow['fk_tipo']) {
//        case 1726:
//            break;
//        case 1720:
//        $html  = "<table>";
//        $html .= "<tr><td><img width=\"800\" height=\"600\" src=\"{$dataRow['dir_archivo']}\"/></td></tr>";
//        $html .= "</table>";    
//            break;
//        case 1722:
//        case 1721:
//        $html  = "<table>";
//        $html .= "<tr><td>
//<a href=\"download/id/{$dataRow['pk_recurso']}\">Descargar</a></td></tr>";
//        $html .= "</table>";    
//            break;
//        case 1724:
//        case 1723:
//        $html = "
//            <table>
//                <tr><td>
//                    <a
//                        href=\"{$dataRow['dir_archivo']}\"
//                        style=\"display:block;width:425px;height:300px;\"
//                        id=\"player\">
//                    </a>
//                </td></tr>
//            </table>
//        ";
//        $json = array();
//        $json[] = "flowplayer(\"player\", \"../../flowplayer-3.2.7.swf\")";
//        $this->SwapBytes_Crud_Form->setJson($json);
//            break;
//
//        default:
//        $html  = "<table>";
//        $html .= '<tr><td><img src="../../images/upload/339411/itsmeee.jpg"/></td></tr>';
//        $html .= "</table>";
//
//            break;
//    }
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Mensaje');
        $this->SwapBytes_Crud_Form->getView($html);
    }
    
    public function deleteloadAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $Params = $this->_params['modal'];
                if(isset($Params['chkMensaje'])) {
                    if(is_array($Params['chkMensaje'])) {
                        foreach($Params['chkMensaje'] as $msj) {
                            $this->Mensajes->deleteMensaje($msj, $Params['carpeta']);
                        }
                    }else{
                        $this->Mensajes->deleteMensaje($Params['chkMensaje'], $Params['carpeta']);
                    }
                        $this->SwapBytes_Crud_Form->getDeleteFinish();
                }
            }
    }

	public function copyAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

			$Params = $this->_params['modal'];

			$this->authSpace->copyItems = $Params['chkClase'];
		}
	}

	public function pasteAction() {
	    if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

			if(!isset($this->authSpace->copyItems)) { return; }

//            $Data['usuario']  = $this->authSpace->userId;

			$this->Asignaciones->setData($this->_params['filters'], array('periodo','sede','escuela','pensum','semestre','materia','turno','seccion','usuario'));

			$asignacion = $this->Asignaciones->getPK();
			$clases     = (is_array($this->authSpace->copyItems))? implode(',', $this->authSpace->copyItems) : $this->authSpace->copyItems;

			$this->Clases->copyRow($clases, $asignacion);

			$this->SwapBytes_Crud_Form->getRefresh();
		}
	}
        
        public function uploadAction(){
            if ($this->_request->isXmlHttpRequest()) {
            $this->session->filename = null;
            $this->SwapBytes_Ajax->setHeader();
            // list of valid extensions, ex. array("jpeg", "xml", "bmp")
            $allowedExtensions = array('jpg', 'jpeg', 'gif', 'png');
            // max file size in bytes
            $sizeLimit = 10 * 1024 * 1024;

            $uploader = new SwapBytes_FileUploader_qqFileUploader($allowedExtensions, $sizeLimit);
            $result = $uploader->handleUpload("/Users/nieldm/Sites/MiUNECDE/public/uploads/mensajes/{$this->authSpace->userId}/");
            
            // to pass data through iframe you will need to encode all html tags
            if($result['success'])
//                $this->session->filename = "../../uploads/{$this->session->id}/{$result['filename']}.{$result['fileext']}";
//                $this->createThumbs("uploads/{$this->session->id}/", "uploads/{$this->session->id}/thumbs/", 100);
            echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
            }
        }
        
        
        public function downloadAction(){
            $id = $this->_getParam('id');
//            echo $id;
//            if(is_numeric($id) && $id > 0) return;
            
            $Recurso = $this->Mensajes->getAdjunto($id);
//            var_dump($Recurso);
//            var_dump($Recurso);
           $Recurso['dir_archivo'] = $Recurso[0]['dir_archivo'];
//            if($Recurso['fk_clase'] == $this-session-id){
            if(!empty($Recurso['dir_archivo'])){
                  $filename = $Recurso['dir_archivo'];
//                  $ext      = strrpos($filename, '.');
                  $ext      = substr($filename, strrpos($filename, '.'));
//                  echo $ext;
//                  echo $filename;
                  $mime     = $this->Recursos->getMime($ext);
//                  echo $filename.' -> ';
//                  echo $ext.' -> ';
//                  var_dump($mime);
//                  echo $mime[0]['header'].' -> ';
                  if(!empty($mime)){
                      Zend_Layout::getMvcInstance()->disableLayout();
                      Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                      Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$mime[0]['header']}");
                      Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}" );
//                      header("Content-Type: {$mime[0]['header']}");
//                      header("Content-Disposition: attachment; filename=$filename");
                      readfile("uploads/mensajes/{$this->authSpace->userId}/$filename");
//                      echo "uploads/mensajes/{$this->authSpace->userId}/$filename";
                  }else{
                      echo 'El archivo solicitado no es soportado';
                  }
	    // disable layout and view
	    $this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
            }else{
                echo 'Archivo no encontrado';
            }
            
//            }
        }
        
                    
        
        private function relativeTime($dt,$precision=2)
        {
                $times=array(	365*24*60*60	=> "year",
                                        30*24*60*60		=> "month",
                                        7*24*60*60		=> "week",
                                        24*60*60		=> "day",
                                        60*60			=> "hour",
                                        60				=> "minute",
                                        1				=> "second");

                $passed=time()-$dt;

                if($passed<5)
                {
                        $output='less than 5 seconds ago';
                }
                else
                {
                        $output=array();
                        $exit=0;
                        foreach($times as $period=>$name)
                        {
                                if($exit>=$precision || ($exit>0 && $period<60)) 	break;
                                $result = floor($passed/$period);

                                if($result>0)
                                {
                                        $output[]=$result.' '.$name.($result==1?'':'s');
                                        $passed-=$result*$period;
                                        $exit++;
                                }

                                else if($exit>0) $exit++;

                        }
                        $output=implode(' and ',$output).' ago';
                }

                return $output;
        }

        private function formatTweet($tweet,$dt,$author = 'demo')
        {
                if(is_string($dt)) $dt=strtotime($dt);

                $tweet=htmlspecialchars(stripslashes($tweet));
                $author = strtolower($author);
                return'
                <li>
                <a href="#"><img class="avatar" src="../images/avatar.jpg" width="48" height="48" alt="avatar" /></a>
                <div class="tweetTxt">
                <strong><a href="#">'.$author.'</a></strong> '. preg_replace('/((?:http|https|ftp):\/\/(?:[A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?[^\s\"\']+)/i','<a href="$1" rel="nofollow" target="blank">$1</a>',$tweet).'
                <div class="date">'.$this->relativeTime($dt).'</div>
                </div>
                <div class="clear"></div>
                </li>';
                }


}
