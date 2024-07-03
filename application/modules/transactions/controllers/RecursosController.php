<?php

class Transactions_RecursosController extends Zend_Controller_Action {

    private $Title = 'Transacciones \ Carga de Recursos';

    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Clases');
        Zend_Loader::loadClass('Models_DbTable_Recursos');
        Zend_Loader::loadClass('Models_DbView_Estrategias');
        Zend_Loader::loadClass('Models_DbView_Evaluaciones');
        Zend_Loader::loadClass('Forms_Recurso');
//        Zend_Loader::loadClass('Forms_Recursoview');

        $this->Asignaciones    = new Models_DbTable_Asignaciones();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->Clases          = new Models_DbTable_Clases();
        $this->Recursos        = new Models_DbTable_Recursos();
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
        $this->session = new Zend_Session_Namespace('Recursos');

        $this->filtros->setDisplay(true, true, true, true, true, true, true, true, false);
        $this->filtros->setDisabled(true, true, true, true, true, true, true, true, false);
        $this->filtros->setRecursive(true, true, true, true, true, true, true, true, false);
        $this->filtros->setParam('usuario', $this->authSpace->userId);

        /*
         * Configuramos los botones.
         */
		$this->SwapBytes_Crud_Action->setDisplay(false, false, true, true, false, false);
		$this->SwapBytes_Crud_Action->setEnable(true, true, true, true, false, false);
//                $this->SwapBytes_Crud_Action->addCustum("<button id=\"btnBackCrono\" class=\"ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\" 
//                                                        name=\"btnBackCrono\" role=\"button\" aria-disabled=\"false\">Volver a Cronogramas</button>");
                $this->SwapBytes_Crud_Action->addCustum("<button id=\"btnVerRecurso\" class=\"ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\" 
                                                        name=\"btnVerRecurso\" role=\"button\" aria-disabled=\"false\">Aula Virtual</button>");
                $this->SwapBytes_Crud_Action->addJavaScript("$('#btnVerRecurso').click(function(){
                                                                $.getJSON(urlAjax + \"buscar/filters/\"+escape($('#tblFiltros').find(':input').serialize())+\"\", function(d){executeCmdsFromJSON(d)});
                                                                newwindow=window.open('{$this->view->baseUrl()}/reports/recursos/generar','_newtab__' ,'scrollbars=1,toolbar=0,status=0,fullscreen=yes');
                                                                if (window.focus) {newwindow.focus()}
                                                                return false;
                                                            });");
                $this->SwapBytes_Crud_Search->setDisplay(false);

        /*
         * Mandamos a crear el formulario para ser utilizado mediante el AJAX.
         */
        $this->view->form = new Forms_Recurso();

        $this->SwapBytes_Form->set($this->view->form);
        $this->SwapBytes_Form->fillSelectBox('fk_tipo', $this->Recursos->getTiposRecursos() , 'pk_atributo', 'valor');
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
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/js/file-uploader/client/fileuploader.css');

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
        $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->view->SwapBytes_Ajax->setView($this->view);
    }

    public function periodoAction() {
      $parametros = $this->Clases->getFiltrosClase($this->session->id);
      $this->SwapBytes_Ajax_Action->fillSelect($parametros[0]['pk_periodo'], $parametros[0]['periodo']);
    }

    public function sedeAction() {
      $parametros = $this->Clases->getFiltrosClase($this->session->id);
      $this->SwapBytes_Ajax_Action->fillSelect($parametros[0]['pk_sede'], $parametros[0]['sede']);
    }

    public function escuelaAction() {
      $parametros = $this->Clases->getFiltrosClase($this->session->id);
      $this->SwapBytes_Ajax_Action->fillSelect($parametros[0]['pk_escuela'], $parametros[0]['escuela']);
    }

    public function pensumAction() {
      $parametros = $this->Clases->getFiltrosClase($this->session->id);
      $this->SwapBytes_Ajax_Action->fillSelect($parametros[0]['pk_pensum'], $parametros[0]['pensum']);
    }

    public function semestreAction() {
      $parametros = $this->Clases->getFiltrosClase($this->session->id);
      $this->SwapBytes_Ajax_Action->fillSelect($parametros[0]['pk_semestre'], $parametros[0]['semestre']);
    }

    public function materiaAction() {
      $parametros = $this->Clases->getFiltrosClase($this->session->id);
      $this->SwapBytes_Ajax_Action->fillSelect($parametros[0]['pk_materia'], $parametros[0]['materia']);
    }

    public function turnoAction() {
      $parametros = $this->Clases->getFiltrosClase($this->session->id);
      $this->SwapBytes_Ajax_Action->fillSelect($parametros[0]['pk_turno'], $parametros[0]['turno']);
    }

    public function seccionAction() {
      $parametros = $this->Clases->getFiltrosClase($this->session->id);
      $this->SwapBytes_Ajax_Action->fillSelect($parametros[0]['pk_seccion'], $parametros[0]['seccion']);
    }

    public function infoclaseAction(){
       $this->SwapBytes_Ajax->setHeader();
       $parametros = $this->Clases->getFiltrosClase($this->session->id);

//       $HTML  = "<table border=\"1px\">";
       $HTML  = "<tr><th colspan=\"2\" style=\"font-size: 16px;\">Datos de la Clase</th></tr>";
       $HTML .= "<tr><td rowspan=\"2\"><label><b style=\"font-size: 14px;\">Número: </b>{$parametros[0]['numero']}</label></td>";
       $HTML .= "<td style=\"text-align:right\"><label><b style=\"font-size: 14px;\">Fecha:</b></label></td></tr>";
//       $HTML .= "<tr><td></td>";
       $HTML .= "<td style=\"text-align:right\"><label>{$parametros[0]['fecha']}</label></td></tr>";
       $HTML .= "<tr><td colspan=\"2\"><label><b style=\"font-size: 14px;\">Descripción: </b>{$parametros[0]['descripcion']}</label><tr><td>";
       $HTML .= "<tr><td colspan=\"2\"><label><b style=\"font-size: 14px;\">Contenido: </b>{$parametros[0]['contenido']}</label><tr><td>";
//       $HTML .= "<tr><td><label>Tipo de Estrategia: {$parametros[0]['tipo_estrategia']}</label><tr><td>";
//       $HTML .= "<tr><td><label>Tipo de Evaluación: {$parametros[0]['tipo_evaluacion']}</label><tr><td>";
//       $HTML .= "<tr><td><label>Puntaje: {$parametros[0]['puntaje']}</label><tr><td>";
//       $HTML .= "</table>";

       $json[] = $this->SwapBytes_Jquery->setHtml('infoclase', $HTML);
       $this->getResponse()->setBody(Zend_Json::encode($json));
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

            $Data   = $this->filtros->getParams();
            $json   = array();

            // $Data['usuario']  = $this->authSpace->userId;
            $Data['pk_clase'] = $this->session->id;

            $this->Recursos->setData($Data);
            
            // $rows = $this->Clases->getCronogramas();
            $rows = $this->Recursos->getRecursos($this->session->id);
            
            foreach($rows as $key => $row){
                
                switch ($row['fk_tipo']) {
                    case 8223:
                        $filename = (substr($row['dir_archivo'], strrpos($row['dir_archivo'], '/')+1));
                        $ext = substr($filename, strrpos($filename , '.')+1);
                        $rows[$key]['preview'] =  "<object data=\"../../uploads/{$this->session->id}/thumbs/{$filename}\"><img src=\"../../images/{$ext}.png\"></object>";
                        break;
                    case 8225:
                        $rows[$key]['preview'] =  "<center><img src=\"../../images/html.png\"></center>";
                        break;
                    case 8224:
                        $rows[$key]['preview'] =  "<center><label><b>etiqueta</b></label></center>";
                        break;
                    default:
                        $filename = (substr($row['dir_archivo'], strrpos($row['dir_archivo'], '/')+1));
                        $ext = substr($filename, strrpos($filename , '.')+1);
//                        $rows[$key]['preview'] =  $ext;
                        $rows[$key]['preview'] =  "<center> <img src=\"../../images/{$ext}.png\"></center>";
                        break;
                }

            }
//                $loggerFB->log($rows, Zend_Log::WARN);

            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_recurso',
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
                                                          'name'  => 'chkClase',
                                                          'value' => '##pk_recurso##')),
                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'column'   => 'ordinal',
                                       'rows'    => array('style' => 'text-align:right')),
                                 array('name'    => 'Tipo',
                                       'width'   => '100px',
                                       'column'  => 'valor'),
                                 array('name'    => 'Descripción',
                                       'width'   => '300px',
                                       'column'  => 'descripcion'),
                                 array('name' => 'Dir. Archivo',
                                       'width'=> '300px',
                                       'column' => 'dir_archivo'),
                                 array('name' => 'Publicar',
                                       'width'=> '50px',
                                       'column' => 'publico',
                                       'rows'    => array('style' => 'text-aling:center'))
                                        
                                ,array('name'    => 'Preview',
                                       'column'  => 'preview',
                                       'width'   => '20px')
                                                               );
                
                // $this->SwapBytes_Crud_List->addFooter('Puntos', 'Total', SQL_FUNCTION_SUM);

                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VU');
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkClase');
            } else {
                $HTML   = $this->SwapBytes_Html_Message->alert("No existen recursos cargados.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function addoreditloadAction() {
//        $this->SwapBytes_Ajax->setHeader();
        $this->session->json = null;
		if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
			$dataRow       = $this->Recursos->getRow($this->_params['modal']['id']);
			$dataRow['id'] = $this->_params['modal']['id'];
                        $dataRow['publico']  = $this->SwapBytes_Form->setValueToBoolean($dataRow['publico']);
                        $dataRow['contenido_html'] = html_entity_decode($dataRow['contenido_html']);

		}
        $this->session->chtml = null;
        $this->session->filename = null;; 
        
        $json = array();
        $json[] = "$(\"#frmModal\").parent().find('#fk_tipo').change(function(){
            num = $(\"#frmModal\").parent().find('#fk_tipo').val();
            id  = $(\"#frmModal\").parent().find('#id').val();
            if(num == 8225){
                    $(\"#file-uploader\").hide();
                    startTinyMCE();
                    $('#contenido_html-element').show();
                    $('#contenido_html-label').show();
                    click = $('#frmModal').parent().find(\"button:contains('Guardar')\").data('events').mouseover.length;
                    if(click < 3){
                    $('#frmModal').parent().find(\"button:contains('Guardar')\").mouseover(function(){
            $('#contenido_html').text(tinyMCE.get('contenido_html').getContent());
            chtml = $('#contenido_html').text();
            $.post('{$this->view->baseUrl()}/transactions/recursos/savecontenido/', {'chtml' : chtml});
                    });}
            }
            if(num == 8224){
                    $(\"#file-uploader\").hide();

                    $('#contenido_html-element').hide();
                    $('#contenido_html-label').hide();

            }
            if(num == 8223){
                    $('#contenido_html-element').hide();
                    $('#contenido_html-label').show();
                    $('#contenido_html-label').css(\"height\", \"120px\");
                    if(!id){
                        exts = ['jpg', 'jpeg', 'gif', 'png'];
                        startFileUploader(exts);
                        $(\"#file-uploader\").show();
                    }
            }
            if(num == 8221){
                    $('#contenido_html-element').hide();
                    $('#contenido_html-label').show();
                    $('#contenido_html-label').css(\"height\", \"120px\");
                    if(!id){
                        exts = ['zip', 'rar', '7z'];
                        startFileUploader(exts);
                        $(\"#file-uploader\").show();
                    }
            }
            if(num == 8220){
                    $('#contenido_html-element').hide();
                    $('#contenido_html-label').show();
                    $('#contenido_html-label').css(\"height\", \"120px\");
                    if(!id){
                        exts = ['mp4'];
                        startFileUploader(exts);
                        $(\"#file-uploader\").show();
                    }
            }
            if(num == 8219){
                    $('#contenido_html-element').hide();
                    $('#contenido_html-label').show();
                    $('#contenido_html-label').css(\"height\", \"120px\");
                    if(!id){
                        exts = ['mp3'];
                        startFileUploader(exts);
                        $(\"#file-uploader\").show();
                    }
            }
            if(num == 8222){
                    $('#contenido_html-element').hide();
                    $('#contenido_html-label').show();
                    $('#contenido_html-label').css(\"height\", \"120px\");
                    if(!id){
                        exts = ['doc', 'docx', 'ppt', 'pptx', 'txt', 'pdf', 'dot', 'rtf', 'xml', 'xlsx', 'xls', 'csv'];
                        startFileUploader(exts);
                        $(\"#file-uploader\").show();
                    }
            }
            })";

        $json[] = "$(\"#frmModal\").parent().find('#fk_tipo').change()";
        if(!empty($dataRow)){
            $json[] = "$(\"#frmModal\").parent().find('#fk_tipo').attr('disabled', 'disabled')";
        switch($dataRow['fk_tipo']){
            case 8224:
                break;
            case 8223:
                break;
            case 8225:
                break;
              default:
        $json[] = "$('#contenido_html-label').hide()";         
                break;
        }
        }
       // $this->session->json = $json;
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Editar Recurso');
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();
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
            // $this->Asignaciones->setData($this->_params['filters'], array('periodo','sede','escuela','pensum','semestre', 'materia', 'turno','seccion'));
			$dataRow                   = $this->_params['modal'];
			$id                        = $dataRow['id'];
			$dataRow['id']             = null;
			$dataRow['filtro']         = null;
			$dataRow['fk_clase']       = $this->session->id;
                        if(isset($this->session->chtml)){
                            $dataRow['contenido_html'] = $this->session->chtml;
                            $dataRow['contenido_html'] = htmlentities($dataRow['contenido_html'], ENT_QUOTES);
//                            $dataRow['contenido_html'] = nl2br($dataRow['contenido_html']);
                            $dataRow['contenido_html'] = pg_escape_string($dataRow['contenido_html']);
                        }
                        if(isset($this->session->filename)) $dataRow['dir_archivo'] = $this->session->filename;
                        
			if(is_numeric($id) && $id > 0) {
				$this->Recursos->updateRow($id, $dataRow);
			} else if(is_numeric($this->session->id) && $this->session->id > 0) {
                                    if(empty($dataRow['fk_tipo'])){
                                $dataRow['fk_tipo'] = $dataRow['fk_tipo_alt'];
                                $dataRow['fk_tipo_alt'] = null;
                                    }
				$this->Recursos->addRow($dataRow);
			}
			$this->session->chtml = null;
			$this->session->filename = null;
                        if($dataRow['fk_tipo'] == 8223)
                            $this->createThumbs("uploads/{$this->session->id}/", "uploads/{$this->session->id}/thumbs/", 100);
			$this->SwapBytes_Crud_Form->getAddOrEditEnd();
		}
    }

    public function viewAction() {
       
        $dataRow = $this->Recursos->getRow($this->_params['modal']['id']);
        $dataRow['contenido_html'] = html_entity_decode($dataRow['contenido_html']);
        $dataRow['contenido_html'] = pg_escape_string($dataRow['contenido_html']);
//        $this->SwapBytes_Crud_Form->addJS('rendertinyMCE();');
//        $this->SwapBytes_Crud_Form->addJS('startFileUploader();');
    // $loggerFB = Zend_Registry::get('logger');
    // $loggerFB->log($dataRow, Zend_Log::INFO);
    
    switch ($dataRow['fk_tipo']) {
        case 8225:
        $html = $dataRow['contenido_html'];
            break;
        case 8223:
        $html  = "<table>";
        $html .= "<tr><td><img width=\"800\" height=\"600\" src=\"{$dataRow['dir_archivo']}\"/></td></tr>";
        $html .= "</table>";    
            break;
        case 8224:
        $html  = "<table>";
        $html .= "<tr><td><label>{$dataRow['descripcion']}</label></td></tr>";
        $html .= "</table>";    
            break;
        case 8222:
        case 8221:
        $html  = "<table>";
        $html .= "<tr><td>
                    <label>Puedes descargar el archivo en el siguiente enlace: </label><a href=\"download/id/{$dataRow['pk_recurso']}\">Descargar</a></td></tr>";
        $html .= "</table>";    
            break;
        case 8219:
        case 8220:
        $html = "
            <table>
                <tr><td>
                    <a
                        href=\"{$dataRow['dir_archivo']}\"
                        style=\"display:block;width:425px;height:300px;\"
                        id=\"player\">
                    </a>
                </td></tr>
            </table>
        ";
        $json = array();
        $json[] = "flowplayer(\"player\", \"../../flowplayer-3.2.7.swf\")";
        $this->SwapBytes_Crud_Form->setJson($json);
            break;

        default:
        $html  = "<table>";
        $html .= '<tr><td><img src="../../images/upload/339411/itsmeee.jpg"/></td></tr>';
        $html .= "</table>";

            break;
    }
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Recurso');
        $this->SwapBytes_Crud_Form->getView($html);
    }
    
    public function deleteloadAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $basepath = 'Users/nieldm/Sites/MiUNECDE/public/uploads/';	
            $Params = $this->_params['modal'];
            $loggerFB = Zend_Registry::get('loggerFB');
            $loggerFB->log($Params, Zend_Log::WARN);
			if(isset($Params['chkClase'])) {
				if(is_array($Params['chkClase'])) {
					foreach($Params['chkClase'] as $clase) {
//                                                $loggerFB->log($clase, Zend_Log::INFO);
                                                $Recurso = $this->Recursos->getRow((int)$clase);
//                                                $loggerFB->log($Recurso, Zend_Log::INFO);
						if($this->Recursos->deleteRow($clase)){
                                                    if(!empty($Recurso['dir_archivo'])){
                                                        $filename = (substr($Recurso['dir_archivo'], strrpos($Recurso['dir_archivo'], '/')+1));
                                                        unlink("uploads/".$Recurso['fk_clase'].'/'.$filename);                                                   
                                                    }
                                                }
					}
				} else {
                                        
                                      $Recurso = $this->Recursos->getRow($Params['chkClase']);
                                      if($this->Recursos->deleteRow($Params['chkClase'])){
                                          if(!empty($Recurso['dir_archivo'])){
                                              $filename = (substr($Recurso['dir_archivo'], strrpos($Recurso['dir_archivo'], '/')+1));
                                              unlink("uploads/".$Recurso['fk_clase'].'/'.$filename);                                               
                                          }
                                      }
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
            $allowedExtensions = array();
            // max file size in bytes
            $sizeLimit = 15 * 1024 * 1024;

            $uploader = new SwapBytes_FileUploader_qqFileUploader($allowedExtensions, $sizeLimit);
            $result = $uploader->handleUpload("/Users/nieldm/Sites/MiUNECDE/public/uploads/{$this->session->id}/");
            
            // to pass data through iframe you will need to encode all html tags
            if($result['success'])
                $this->session->filename = "../../uploads/{$this->session->id}/{$result['filename']}.{$result['fileext']}";
                $this->createThumbs("uploads/{$this->session->id}/", "uploads/{$this->session->id}/thumbs/", 100);
            echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
            }
        }
        
        public function savecontenidoAction(){
            $this->session->chtml = $_POST['chtml'];
        }
        
        public function downloadAction(){
            $id = $this->_getParam('id');
//            echo $id;
//            if(is_numeric($id) && $id > 0) return;
            
            $Recurso = $this->Recursos->getRow($id);
//            var_dump($Recurso);
//            if($Recurso['fk_clase'] == $this-session-id){
            if(!empty($Recurso['dir_archivo'])){
                  $filename = (substr($Recurso['dir_archivo'], strrpos($Recurso['dir_archivo'], '/')+1));
//                  $ext      = strrpos($filename, '.');
                  $ext      = substr($filename, strrpos($filename, '.'));
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
                      readfile("uploads/".$Recurso['fk_clase'].'/'.$filename);
                  }else{
                      echo 'El archivo solicitado no es soportado';
                  }
	    // disable layout and view
	    $this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
//                  $loggerFB = Zend_Registry::get('loggerFB');
//                  $loggerFB->log($filename, Zend_Log::ERR);
//                  $loggerFB->log($ext, Zend_Log::ERR);
            }else{
                echo 'Archivo no encontrado';
            }
            
//            }
        }
        
        public function viewfotoAction(){
            //           $this->Recursos->viewArchivo();
            $conn  = pg_connect("user=MiUNE password=dama16 dbname=MiUNE host=localhost");
            $query = pg_query($conn, "SELECT archivo FROM tbl_recursos where pk_recurso = 99");
            $row   = pg_fetch_row($query);
            pg_close($conn);
            if(!isset($row[0])){

                return 'ERROR';


            }else {
                    Zend_Layout::getMvcInstance()->disableLayout();
                    Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                    Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "image/jpeg");
//                    Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Transfer-Encoding:', 'binary');
//                    Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Description', 'File Transfer');
                    Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename=deathwing01-1290x80070.jpg" );

                $image = pg_unescape_bytea($row[0]);
            //        echo pg_unescape_bytea($row[0]);
//                    echo base64_decode($image);       
                echo $image;
                echo $row[0];

            }
                    }
                    
        private function createThumbs( $pathToImages, $pathToThumbs, $thumbWidth ) 
        {
          // open the directory
          $dir = opendir( $pathToImages );

          // loop through it, looking for any/all JPG files:
          while (false !== ($fname = readdir( $dir ))) {
            // parse path for the extension
            $info = pathinfo($pathToImages . $fname);
            // continue only if this is a JPEG image
            if ( strtolower($info['extension']) == 'jpg' || strtolower($info['extension']) == 'jpeg' ) 
            {
//              echo "Creating thumbnail for {$fname} <br />";

              // load image and get image size
              $img = imagecreatefromjpeg( "{$pathToImages}{$fname}" );
              $width = imagesx( $img );
              $height = imagesy( $img );

              // calculate thumbnail size
              $new_width = $thumbWidth;
              $new_height = floor( $height * ( $thumbWidth / $width ) );

              // create a new temporary image
              $tmp_img = imagecreatetruecolor( $new_width, $new_height );

              // copy and resize old image into new image 
              imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

              // save thumbnail into a file
              imagejpeg( $tmp_img, "{$pathToThumbs}{$fname}" );
            }
          }
          // close the directory
          closedir( $dir );
        }
                    

}
