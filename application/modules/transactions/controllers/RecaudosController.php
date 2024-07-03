<?php

class Transactions_RecaudosController extends Zend_Controller_Action {

    private $Title = 'Transacciones \ Carga de Recaudos';

    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Recaudos');
        Zend_Loader::loadClass('Models_DbTable_Inscripcionespasantias');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Forms_Recaudo');
        


        $this->Asignaciones         = new Models_DbTable_Asignaciones();
        $this->grupo                = new Models_DbTable_UsuariosGrupos();
        $this->Recaudos             = new Models_DbTable_Recaudos();
        $this->Inscripcionpasantias = new Models_DbTable_Inscripcionespasantias();
        $this->Inscripciones        = new Models_DbTable_Inscripciones();
        $this->Periodos             = new Models_DbTable_Periodos();
        $this->filtros              = new Une_Filtros();


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
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        /*
         * Configuramos los filtros.
         */
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->session = new Zend_Session_Namespace('Recaudos');

        /*
         * Configuramos los botones.
         */
		$this->SwapBytes_Crud_Action->setDisplay(true, false, true, true, false, false);
		$this->SwapBytes_Crud_Action->setEnable(true, true, true, true, false, false);
                $this->SwapBytes_Crud_Search->setDisplay(true);

                
        /*
         * Mandamos a crear el formulario para ser utilizado mediante el AJAX.
         */
        $this->view->form = new Forms_Recaudo();

        $this->SwapBytes_Form->set($this->view->form);
        $this->SwapBytes_Form->fillSelectBox('fk_nombre_recaudo', $this->Recaudos->gettipoderecaudo() , 'pk_atributo', 'valor');
        

        $this->view->form = $this->SwapBytes_Form->get();
        
        
        /*
         * Mandamos a crear el formulario para ser utilizado para los 
         * Contenidos mediante el AJAX.
         */
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
    
    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $Data   = $this->filtros->getParams();
            $json   = array();

            $periodo = $this->Periodos->getUltimo();         
            
            $rows = $this->Recaudos->getRecaudos($this->authSpace->userId, $periodo);
            
            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData');

                $columns = array(array('column'  => 'pk_recaudo',
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
                                                          'name'  => 'chkRecaudo',
                                                          'value' => '##pk_recaudo##')),
                                 array('name'    => 'C.I.',
                                       'width'   => '70px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'ci'),
                                 array('name'    => 'Estudiante',
                                       'width'   => '300px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'estudiante'),
                                 array('name'    => 'Nombre del Recaudo',
                                       'width'   => '150px',
                                       'rows'    => array('style' => 'text-align:center'),
                                       'column'  => 'valor'));
                
                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkRecaudo');
            } else {
                $HTML   = $this->SwapBytes_Html_Message->alert("No existen recaudos cargados.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function addoreditloadAction() {
//        $this->SwapBytes_Ajax->setHeader();
        $this->session->json = null;
        $periodo = $this->Periodos->getUltimo();
        $pasante = $this->Inscripciones->getCountEstudianteInscripcionPasantias($this->authSpace->userId, $periodo);
        
      if ($pasante >= 1) {  
        if (is_numeric($this->_params['modal']['id']) && $this->_params['modal']['id'] > 0) {
            $dataRow = $this->Recaudos->getRow($this->_params['modal']['id']);
            $dataRow['id'] = $this->_params['modal']['id'];
            $dataRow['fk_inscripcion'] =$this->Inscripciones->getInscripcionpasantia($this->authSpace->userId, $dataRow['periodo']);
            $title = 'Editar Recaudo';
        }
        if ($this->Recaudos->getrecaudoscargados($this->authSpace->userId, $periodo) < 1){
        $title = 'Agregar Recaudo';
        $json = array();
        $json[] = "exts = ['doc', 'docx', 'ppt', 'pptx', 'txt', 'pdf', 'dot', 'rtf', 'xml', 'xlsx', 'xls', 'csv', 'jpg', 'jpeg', 'gif', 'png'];
                        startFileUploader(exts);
                        $(\"#file-uploader\").show();";
        }  else {
          $message = "<b>Usted Ya tiene un recaudo cargado, Debe eliminar el recaudo cargado para poder cargar otro.</b>";
          $this->SwapBytes_Crud_Form->getDialog('No se puede Consignar Recaudo', $message);
        }
      } else {
          $message = "<b>Usted No se encuentra inscrito en las Pasantías</b>";
          $this->SwapBytes_Crud_Form->getDialog('No se puede Consignar Recaudo', $message);
      }
      

        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, $title);
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
			
            
                        $dataRow                   = $this->_params['modal'];
                        $periodo = $this->Periodos->getUltimo();
			$id                     = $dataRow['id'];
                        $dataRow['dir_archivo'] = $dataRow['recaudo'];
                        $dataRow['fk_inscripcion']= $this->Inscripciones->getInscripcionpasantia($this->authSpace->userId, $periodo);
			$dataRow['id']             = null;
			$dataRow['filtro']         = null;
                        $dataRow['recaudo']        = null;
			
                       
                        
                        
                        if(is_numeric($id) && $id > 0) {
				$this->Recaudos->updateRow($id, $dataRow);
			} else if(is_numeric($this->authSpace->userId) && $this->authSpace->userId > 0) {
                                    if(empty($dataRow['fk_nombre_recaudo'])){
                                $dataRow['fk_nombre_recaudo'] = $dataRow['fk_nombre_recaudo_alt'];
                                $dataRow['fk_nombre_recaudo_alt'] = null;
                                    }
				$this->Recaudos->addRow($dataRow);
			}
			$this->session->filename = null;
			$this->SwapBytes_Crud_Form->getAddOrEditEnd();
		}
    }
    
public function deleteloadAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
			
            $Params = $this->_params['modal'];

			if(isset($Params['chkRecaudo'])) {
				if(is_array($Params['chkRecaudo'])) {
					foreach($Params['chkRecaudo'] as $recaudo) {
						$this->Recaudos->deleteRow($recaudo);
					}
				} else {
					$this->Recaudos->deleteRow($Params['chkRecaudo']);
				}

				$this->SwapBytes_Crud_Form->getDeleteFinish();
			}
		}
    }  

        
        public function uploadAction(){
            if ($this->_request->isXmlHttpRequest()) {
            $this->session->filename = null;
            $this->SwapBytes_Ajax->setHeader();
            
            $allowedExtensions = array();
            
            $sizeLimit = 15 * 1024 * 1024;

            $uploader = new SwapBytes_FileUploader_qqFileUploader($allowedExtensions, $sizeLimit);
//            $result = $uploader->handleUpload("/var/www". Zend_Controller_Front::getInstance()->getBaseUrl() ."/public/uploads/pasantias_laborales/{$this->session->id}", false, true);
            $result = $uploader->handleUpload(APPLICATION_PATH ."/../public/uploads/pasantias_laborales/{$this->session->id}", false, true);
            // to pass data through iframe you will need to encode all html tags
            if($result['success'])
                $this->session->filename = "../../uploads/pasantias_laborales{$this->session->id}/{$result['filename']}.{$result['fileext']}";
                //$this->createThumbs("uploads/{$this->session->id}/", "uploads/{$this->session->id}/thumbs/", 100);
                
                return $this->_helper->json(array_merge($result, array('ruta' => $this->session->filename)));
            }
        }
        
   }
