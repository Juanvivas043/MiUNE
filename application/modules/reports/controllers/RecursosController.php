<?php

class Reports_RecursosController extends Zend_Controller_Action {

    private $Title = 'Reportes \ Vista de Cronogramas-Recursos';

    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Clases');
        Zend_Loader::loadClass('Models_DbTable_Recursos');
        Zend_Loader::loadClass('Models_DbView_Estrategias');
        Zend_Loader::loadClass('Models_DbView_Evaluaciones');
        Zend_Loader::loadClass('Forms_Cronograma');

        $this->Asignaciones    = new Models_DbTable_Asignaciones();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->Clases          = new Models_DbTable_Clases();
        $this->Recursos        = new Models_DbTable_Recursos();
        $this->vw_estrategias  = new Models_DbView_Estrategias();
        $this->vw_evaluaciones = new Models_DbView_Evaluaciones();
        $this->filtros         = new Une_Filtros();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
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
		$this->SwapBytes_Crud_Action->setDisplay(true, true, true, true, true, true);
		$this->SwapBytes_Crud_Action->setEnable(true, true, true, true, true, true);

        /*
         * Mandamos a crear el formulario para ser utilizado mediante el AJAX.
         */
        $this->view->form = new Forms_Cronograma();

        $this->SwapBytes_Form->set($this->view->form);
        $this->SwapBytes_Form->fillSelectBox('fk_tipoestrategia', $this->vw_estrategias->get() , 'pk_atributo', 'valor');
        $this->SwapBytes_Form->fillSelectBox('fk_tipoevaluacion', $this->vw_evaluaciones->get(), 'pk_atributo', 'valor');

        $this->view->form = $this->SwapBytes_Form->get();

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
        
        $this->session = new Zend_Session_Namespace('Recursos');
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
		$this->filtros->getAction(array('usuario'));
    }

    public function sedeAction() {
		$this->filtros->getAction(array('usuario', 'periodo'));
    }

    public function escuelaAction() {
		$this->filtros->getAction(array('usuario', 'periodo', 'sede'));
    }

    public function pensumAction() {
        $this->filtros->getAction(array('usuario', 'periodo', 'sede', 'escuela'));
    }

    public function semestreAction() {
        $this->filtros->getAction(array('usuario', 'periodo', 'sede', 'escuela', 'pensum'));
    }

    public function materiaAction() {
        $this->filtros->getAction(array('usuario', 'periodo', 'sede', 'escuela', 'pensum', 'semestre'));
    }

    public function turnoAction() {
        $this->filtros->getAction(array('usuario', 'periodo', 'sede', 'escuela', 'semestre', 'materia'));
    }

    public function seccionAction() {
        $this->filtros->getAction(array('usuario', 'periodo', 'sede', 'escuela', 'semestre', 'materia', 'seccion'));
    }
    
    public function buscarAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $Data   = $this->filtros->getParams();

            var_dump($Data);
            
            $this->Asignaciones->setData($this->_params['filters'], array('periodo','sede','escuela','pensum','semestre','materia','turno','seccion','usuario'));

            $asignacion = $this->Asignaciones->getPK();
            
            $this->session->asig =  $asignacion;
            echo $this->session->asig;
        }
        
    }
    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $Data   = $this->filtros->getParams();
            $json   = array();

            $Data['usuario']  = $this->authSpace->userId;

            $this->Clases->setData($Data);
            
            $rows = $this->Clases->getCronogramas();

            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_clase',
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
                                                          'value' => '##pk_clase##')),
                                 array('name'     => '#',
                                       'width'    => '20px',
                                       'column'   => 'numero',
                                       'rows'    => array('style' => 'text-align:right')),
                                 array('name'    => 'Fecha',
                                       'width'   => '70px',
                                       'column'  => 'fecha',
                                       'rows'    => array('style' => 'text-align:center')),
                                 array('name'    => 'Descripción',
                                       'width'   => '300px',
                                       'column'  => 'descripcion'),
                                 array('name'    => 'Contenido',
                                       'width'   => '300px',
                                       'column'  => 'contenido'),
                                 array('name'    => 'T. Estra.',
                                       'width'   => '280px',
                                       'column'  => 'tipo_estrategia'),
                                 array('name'    => 'T. Evalu.',
                                       'width'   => '280px',
                                       'column'  => 'tipo_evaluacion'),
                                 array('name'    => 'Puntos',
                                       'width'   => '50px',
                                       'column'  => 'puntaje',
                                       'rows'    => array('style' => 'text-align:center')));
                
                $this->SwapBytes_Crud_List->addFooter('Puntos', 'Total', SQL_FUNCTION_SUM);

                $other = array(
                   array('actionName' => 'recursos',
                         'label'      => 'Recursos'));

                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'VUO', $other);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkClase');
            } else {
                $HTML   = $this->SwapBytes_Html_Message->alert("No existen cronogramas cargados.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function addoreditloadAction() {
		if(is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
			$dataRow       = $this->Clases->getRow($this->_params['modal']['id']);
			$dataRow['id'] = $this->_params['modal']['id'];
		}

		$dataRow['fecha'] = $this->SwapBytes_Date->convertToForm($dataRow['fecha']);
		$json[]           = $this->SwapBytes_Jquery_Ui->setDatepicker('fecha');
		
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Editar Recurso');
        $this->SwapBytes_Crud_Form->getAddOrEditLoad();
    }

	public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

			$json[] = $this->SwapBytes_Jquery_Ui->setDatepicker('fecha');

			$this->SwapBytes_Crud_Form->setJson($json);
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
            $this->Asignaciones->setData($this->_params['filters'], array('periodo','sede','escuela','pensum','semestre', 'materia', 'turno','seccion'));
			
			$filtro                   = $this->Asignaciones->getPK();
			$dataRow                  = $this->_params['modal'];
			$id                       = $dataRow['id'];
			$dataRow['id']            = null;
			$dataRow['filtro']        = null;
			$dataRow['fecha']         = $this->SwapBytes_Date->convertToDataBase($dataRow['fecha']);
			$dataRow['fk_asignacion'] = $filtro;
                        

			if(is_numeric($id) && $id > 0) {
				$this->Clases->updateRow($id, $dataRow);
			} else if(is_numeric($filtro) && $filtro > 0) {
				$this->Clases->addRow($dataRow);
			}
			
			$this->SwapBytes_Crud_Form->getAddOrEditEnd();
		}
    }

    public function viewAction() {
        $dataRow          = $this->Clases->getRow($this->_params['modal']['id']);
        $dataRow['fecha'] = $this->SwapBytes_Date->convertToForm($dataRow['fecha']);

        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver Recurso');
        $this->SwapBytes_Crud_Form->getView();
    }
    
    public function recursosAction() {
        
        $this->session->id = $this->_getParam('id');
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');         
        $redirector->gotoUrl("/transactions/recursos/");


    }

    public function deleteloadAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
			
            $Params = $this->_params['modal'];

			if(isset($Params['chkClase'])) {
				if(is_array($Params['chkClase'])) {
					foreach($Params['chkClase'] as $clase) {
						$this->Clases->deleteRow($clase);
					}
				} else {
					$this->Clases->deleteRow($Params['chkClase']);
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
        
        public function generarAction(){
            if(empty($this->session->asig))
                    $this->session->asig= 19071;
            $controllerName = 'recursos';
            $pkasig = $this->session->asig;
//            echo $pkasig;
            $Contenidos = $this->Recursos->getReporteContenidos($pkasig);
            if(empty($Contenidos))
                $Contenidos = $this->Recursos->getReporteSINContenidos($pkasig);
            Zend_Layout::getMvcInstance()->disableLayout();
            $html = "
                    <div class=\"logo_une\"></div>
                    <div id=\"wrapper\">
                    <div id=\"header\">
                        <div id=\"logo\">
                                <h1><span class='title'><a>{$Contenidos[0]['materia']}</a></span></h1>
                                <!--<p>{$Contenidos[0]['nombre_prof']} {$Contenidos[0]['apellido_prof']}</p>-->
                                <p>&nbsp;</p>
                                <div class=\"filtros\">Cátedra del profesor <span class=\"dato\">{$Contenidos[0]['nombre_prof']}</span> <span class=\"dato\">{$Contenidos[0]['apellido_prof']}</span>, 
                                en la Sede <span class=\"dato\">{$Contenidos[0]['sede']}</span> de la escuela de <span class=\"dato\">{$Contenidos[0]['escuela']}</span>, ubicada en el <span class=\"dato\">{$Contenidos[0]['semestre']}</span> del turno <span class=\"dato\">{$Contenidos[0]['turno']}</span>, secci&oacuten <span class=\"dato\">{$Contenidos[0]['seccion']}</span>.</div>
                                <!--<p>De la escuela de {$Contenidos[0]['escuela']} {$Contenidos[0]['semestre']} secci&oacuten {$Contenidos[0]['seccion']} en el turno de la {$Contenidos[0]['turno']} </p>-->
                        </div>
                    </div>
                    <div id=\"menu\">
                         <ul><li class=\"current_page_item\"><a>Período {$Contenidos[0]['pk_periodo']}</a></li></ul>
                    </div>
                    <div id=\"page\">
                    <div id=\"page-bgtop\">
                    <div id=\"page-bgbtm\">
                    <div id=\"content\">
                    ";
            foreach ($Contenidos as $key => $Contenido) {
                if(!empty($Contenido['pk_clase'])){
                //Inicio
                if($key == 0){
                    $Clase = $Contenido['pk_clase'];
                    $html .="<div class=\"post\" id=\"clase{$Contenido['numero']}\"><h2 class=\"title\"><a>Clase {$Contenido['numero']}: <span style=\"font-size: 19px;\">{$Contenido['descripcion_cl']}</span></a></h2>
                    <p class=\"meta\">
                    <span class=\"date\">{$Contenido['fecha']}</span>
                    <!--<span class=\"posted\">&nbsp;</span>-->
                    </p><span class=\"descripcion\"><p><span class=\"conte\">Contenido:</span> {$Contenido['contenido_cl']}</p>";
                        if(!empty($Contenido['tipo_estrategia']) && $Contenido['tipo_estrategia'] != 'N/A'){
                    $html .= "<p class=\"estra\"><span class=\"dato\">Tipo de Estrategia:</span> {$Contenido['tipo_estrategia']}</p>";
                        }
                        if(!empty($Contenido['tipo_evaluacion']) && $Contenido['tipo_evaluacion'] != 'N/A'){
                    $html .= "<p class=\"evalu\"><span class=\"dato\">Tipo de Evaluación:</span> {$Contenido['tipo_evaluacion']}</p>";
                        }
                        if(!empty($Contenido['puntaje'])){
                    $html .= "<p class=\"puntaje\"><span class=\"dato\">Puntaje:</span> {$Contenido['puntaje']}</p>";
                        }
                    $html .= "</span>
                    <div class =\"entry\">";
                }
                If($Clase != $Contenido['pk_clase']){
                    $Clase = $Contenido['pk_clase'];
                    $html .= "</div></div><div class=\"post\" id=\"clase{$Contenido['numero']}\"><h2 class=\"title\"><a>Clase {$Contenido['numero']}: <span style=\"font-size: 19px;\">{$Contenido['descripcion_cl']}</span></a></h2>
                    <p class=\"meta\">
                    <span class=\"date\">{$Contenido['fecha']}</span>
                    <!--<span class=\"posted\">&nbsp;</span>-->
                    </p><span class=\"descripcion\"><p><span class=\"conte\">Contenido:</span> {$Contenido['contenido_cl']}</p>";
                        if(!empty($Contenido['tipo_estrategia']) && $Contenido['tipo_estrategia'] != 'N/A'){
                    $html .= "<p class=\"estra\"><span class=\"dato\">Tipo de Estrategia:</span> {$Contenido['tipo_estrategia']}</p>";
                        }
                        if(!empty($Contenido['tipo_evaluacion']) && $Contenido['tipo_evaluacion'] != 'N/A'){
                    $html .= "<p class=\"evalu\"><span class=\"dato\">Tipo de Evaluación:</span> {$Contenido['tipo_evaluacion']}</p>";
                        }
                        if(!empty($Contenido['puntaje'])){
                    $html .= "<p class=\"puntaje\"><span class=\"dato\">Puntaje:</span> {$Contenido['puntaje']}</p>";
                        }
                    $html .= "</span>
                    <div class =\"entry\">";

                    
                }
                
                //Contenidos
                if($Contenido['pk_recurso']){
//                    $html .= "<p id=\"{$Contenido['pk_recurso']}\">Contenido {$Contenido['ordinal']} -> {$Contenido['pk_recurso']}  ";
                    switch ($Contenido['fk_tipo']) {
                        case 1719:
                            $Contenido['contenido_html'] = html_entity_decode($Contenido['contenido_html']);
                            $Contenido['contenido_html'] = pg_escape_string($Contenido['contenido_html']);
                            $html .= "<div class=\"{$Contenido['tipo_recurso']}\"><p class='descripcion_conte'>{$Contenido['descripcion']}</p><div class='contenido'> {$Contenido['contenido_html']}</div></div>";
                            $html .= "<div style=\"clear: both;\">&nbsp;</div>";
                            break;
                        case 1724:
                            $html .= "<div class=\"{$Contenido['tipo_recurso']}\"><center>
                                    <a
                                        href=\"{$Contenido['dir_archivo']}\"
                                        class=\"player\"
                                        style=\"display:block;width:425px;height:80px;\"
                                        id=\"player{$Contenido['pk_recurso']}\">
                                    </a></center>
                                    <p class='other_desc'>{$Contenido['descripcion']}</p></div>
                                    ";
                            $html .= "<div style=\"clear: both;\">&nbsp;</div>";
                            break;
                        case 1723:
                            $html .= "<div class=\"{$Contenido['tipo_recurso']}\"><center>
                                    <a
                                        href=\"{$Contenido['dir_archivo']}\"
                                        class=\"player\"
                                        style=\"display:block;width:425px;height:300px;\"
                                        id=\"player{$Contenido['pk_recurso']}\">
                                    </a></center>
                                        <p class='other_desc'>{$Contenido['descripcion']}</p></div>
                                    ";
                            $html .= "<div style=\"clear: both;\">&nbsp;</div>";
                            break;
                        case 1720:
                        $filename = (substr($Contenido['dir_archivo'], strrpos($Contenido['dir_archivo'], '/')+1));
                        $ext = substr($filename, strrpos($filename , '.')+1);
                        $html .=  "<div aling=\"center\" class=\"{$Contenido['tipo_recurso']}\"><span class=\"imagen\"><a href=\"{$this->view->baseUrl()}/reports/{$controllerName}/download/id/{$Contenido['pk_recurso']}\">
                                    <object heigth=\"480\" width=\"640\" data=\"../../uploads/{$this->session->id}/{$filename}\">
                                    <img class=\"alingleft\" src=\"../../images/{$ext}.png\"></object></a></span>";
                        $html .=  "<p class=\"download_desc\">{$Contenido['descripcion']}</p></div>";
                            break;
                        case 1736:
                            $html .= "<div class=\"{$Contenido['tipo_recurso']}\">
                                        <p>{$Contenido['descripcion']}</p>
                                      </div>
                                    ";
                            break;
                        default:
                        $filename = (substr($Contenido['dir_archivo'], strrpos($Contenido['dir_archivo'], '/')+1));
                        $ext = substr($filename, strrpos($filename , '.')+1);
                        $html .=  "<div class=\"{$Contenido['tipo_recurso']}\"><span class=\"download\"><a href=\"{$this->view->baseUrl()}/reports/{$controllerName}/download/id/{$Contenido['pk_recurso']}\">
                                    <object data=\"../../uploads/{$this->session->id}/thumbs/{$filename}\">
                                    <img class=\"alingleft\" src=\"../../images/{$ext}.png\"></object></a></span>";
                        $html .=  "<p class=\"download_desc\">{$Contenido['descripcion']}</p></div>";
                            break;
//                        default:
//                            $this->controller = Zend_Controller_Front::getInstance();
//                            $controllerName  = $this->controller->getRequest()->getControllerName();
//                            $filename = (substr($Contenido['dir_archivo'], strrpos($Contenido['dir_archivo'], '/')+1));
//                            $html .= "<p>{$Contenido['descripcion']}
//                                    <a href=\"{$this->view->baseUrl()}/reports/{$controllerName}/download/id/{$Contenido['pk_recurso']}\">{$filename}</a></p>";
//                            break;
                    }
                }
                
                
                //Final
                if($Contenidos[$key+1]['pk_clase']){
//                    if($Clase != $Contenidos[$key+1]['pk_clase']){
//                        $html .= "</div>";
//                    }
                }else{
                    $html .= "</div></div>";
                }
            }else{
                $html .="<div class=\"post\" id=\"clase\"><h2 class=\"title\"><a>No posee Cronogramas Cargados</a></h2>
                    <p class=\"meta\">
                    <span class=\"date\">&nbsp;</span>
                    <!--<span class=\"posted\">&nbsp;</span>-->
                    </div><div class =\"entry\">&nbsp;</div></div>";
            }
            }//Fin foreach
            $html .= '
                <div style="clear: both;">&nbsp;</div>
                </div>
                
                <!--<div id="sidebar">
                    <ul><li><div id="search"></div><div style="clear: both;">&nbsp;</div>
                    <li><h2>Profesor</h2><p>'.$Contenidos[0]['nombre_prof'].' '.$Contenidos[0]['apellido_prof'].'</p></li></ul>
                </div>-->
                <div style="clear: both;">&nbsp;</div>
                </div>
                </div>
                </div>
                </div>
                <div id="footer">
                    <p>Power By: DDTI</p>
                </div>
                ';
            echo $html;
            
        }
        
        public function downloadAction(){
            $id = $this->_getParam('id');

            
            $Recurso = $this->Recursos->getRow($id);

            if(!empty($Recurso['dir_archivo'])){
                  $filename = (substr($Recurso['dir_archivo'], strrpos($Recurso['dir_archivo'], '/')+1));
                  $ext      = substr($filename, strrpos($filename, '.'));
                  $mime     = $this->Recursos->getMime($ext);
                  if(!empty($mime)){
                      Zend_Layout::getMvcInstance()->disableLayout();
                      Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                      Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "{$mime[0]['header']}");
                      Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}" );
                      readfile("uploads/".$Recurso['fk_clase'].'/'.$filename);
                  }else{
                      echo 'El archivo solicitado no es soportado';
                  }
	    // disable layout and view
	    $this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
            }else{
                echo 'Archivo no encontrado';
            }
            
        }
}
