<?php
class Transactions_SolicitudgradobibliotecaController extends Zend_Controller_Action{
	
	//Definicion de Constantes del documento
	private static $_Title = "Transacciones / Solicitud de Grado Biblioteca";
	private static $_Documento = "Solicitud de Grado";
	private static $_Requisito = "Entrega de tomo en biblioteca";
	private static $_Aprobado   = "Aprobado";
    private static $_Solicitado = "Solicitado";
    private static $_DocumentoTipo = "Documentos";
    private static $_RequisitoTipo = "Requisitos de solicitud grado";
    private static $_EstadosTipo = "Estado de Solicitudes";
    

	public function init() {
	
		/*Loader de classes ZEND php*/
		Zend_Loader::loadClass('Une_Filtros');
		Zend_Loader::loadClass('Models_DbTable_Solicitudgrado');
		Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos'); 
		/*Cargando objeto Filtros*/
		$this->filtros = new Une_Filtros();
		/*Cargando Atributos Modelo*/
		$this->Documentos = new Models_DbTable_Solicitudgrado();
		/*Cargando librerias para las peticiones ajax*/
		$this->SwapBytes_Ajax = new SwapBytes_Ajax();
		$this->SwapBytes_Ajax_Html = new SwapBytes_Ajax_Html();
		$this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
		$this->SwapBytes_Crud_Action = new SwapBytes_Crud_Action();
		$this->SwapBytes_Crud_List = new SwapBytes_Crud_List();
		$this->SwapBytes_Crud_Search = new SwapBytes_Crud_Search();
		$this->SwapBytes_Uri = new SwapBytes_Uri();
		$this->SwapBytes_Jquery = new SwapBytes_Jquery();
		$this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
		$this->SwapBytes_Crud_Form = new SwapBytes_Crud_Form();
		 $this->grupo            = new Models_DbTable_UsuariosGrupos();
		/*Carga de objeto request con la peticion */
		$this->Request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_params['filters'] = $this->filtros->getParams();
		/*habilitando los filtros*/
		$this->filtros->setDisplay(true, true, false, false, false, false, false, false, false);
		$this->filtros->setDisabled(false, true, true, true, true, true, true, true, true);
		$this->filtros->setRecursive(true, true, true, false, false, false, false, false, false);
		//$this->filtros->setType('seccion', FILTER_TYPE_SECCION_PADRES);
		/* Habilitando las acciones del controlador*/
		$this->SwapBytes_Crud_Action->setDisplay(true, true);
		$this->SwapBytes_Crud_Action->setEnable(true, true);
		$this->SwapBytes_Crud_Search->setDisplay(false);
		/*variable log y session*/
        $this->logger = Zend_Registry::get('logger');
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
	}
	
	public function indexAction() {
		/*inicia la vista */
		$this->view->title = $thi->$_Title;
		$this->view->filters = $this->filtros;
		$this->view->SwapBytes_Crud_Form = $this->SwapBytes_Crud_Form;
		$this->view->module = $this->Request->getModuleName();
		$this->view->controller = $this->Request->getControllerName();
		$this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
		$this->view->SwapBytes_Ajax = $this->SwapBytes_Ajax;
		$this->view->SwapBytes_Ajax->setView($this->view);
		$this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
		$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
	
	}
	
	/* Funcion de seguridad */
	function preDispatch() {
		 if (!Zend_Auth::getInstance()->hasIdentity()) {
                 $this->_helper->redirector('index', 'login', 'default');
             }
         if (!$this->grupo->haveAccessToModule()) {
         	$this->_helper->redirector('accesserror', 'profile', 'default');
         }
	}
	
	/*Funciones cargan los contenidos de los filtros*/
	public function periodoAction(){
		$this->filtros->getAction();
	}
	public function sedeAction(){
		$this->filtros->getAction();
	}
	public function escuelaAction(){
		$this->filtros->getAction();
	}
	
	public function listAction() {
		if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();
			/*Buscando datos para la tabla*/
			$DocumentoTipo = $this->Documentos->getPkDocumentoTipoPorNombre($this::$_DocumentoTipo);
			$RequisitoTipo = $this->Documentos->getPkDocumentoTipoPorNombre($this::$_RequisitoTipo);
			$EstadoTipo = $this->Documentos->getPkDocumentoTipoPorNombre($this::$_EstadosTipo);
			$Documento = $this->Documentos->getPkDocumentoPorNombre($this::$_Documento,$DocumentoTipo);
			$Aprobado = $this->Documentos->getEstadoAtributo($this::$_Aprobado,$EstadoTipo);
			$Requisito = $this->Documentos->getPkRequisitoPorNombre($this::$_Requisito,$RequisitoTipo);
			$Periodo = $this->_params['filters']['periodo'];
			$sede = $this->_params['filters']['sede'];
			$Solicitudes = $this->Documentos->getSolicitudesPorDocumentoPeriodo($Documento, $Periodo,$Aprobado,$Requisito,$sede,true);
			// Definimos las propiedades de la tabla.
			$ra_property_table = array('class' => 'tableData','width' => '1100px','column' => 'disponible');
			/*Definimos las propiedades de las columnas */
			$ra_property_column = array(array('column'   => 'pkdocumento',
					'primary'  => true,
					'hide'     => true),
					array('name'     => '#',
							'width'    => '20px',
							'function' => 'rownum',
							'rows'     => array('style' => 'text-align:center')),
					array('name'     => 'C.I.',
							'column'   => 'ci',
							'width'    => '70px',
							'rows'     => array('style' => 'text-align:center')),
					array('name'     => 'Nombre',
							'column'   => 'nombre',
							'width'    => '300px',
							'rows'     => array('style' => 'text-align:center')),
					array('name'     => 'Apellido',
							'column'   => 'apellido',
							'width'    => '200px',
							'rows'     => array('style' => 'text-align:center')),
					array('name'     => 'Estado',
							'column'   => 'estado',
							'width'    => '250px',
							'rows'     => array('style' => 'text-align:center')),
			);
			/* Definimos la columna de accion custom cambia la etiqueta del accion*/
			$other = array(array('actionName'  => 'estado',
					'action'      => 'estado(##pk##)',
					'label'       => 'Aprobar',
					'column'      => 'estado',
					'validate'    => 'true',
					'intrue'      => $this::$_Solicitado,
					'intruelabel' => 'Desaprobar'));
	
			/*Creamos el html de la tabla*/
			$HTML = $this->SwapBytes_Crud_List->fill($ra_property_table, $Solicitudes, $ra_property_column, 'O', $other);
			/*Creamos el arreglo json para responder la peticion en la variable tblsolicitudes*/
			$json[] = $this->SwapBytes_Jquery->setHtml('tblSolicitudes', $HTML);
			/*Responder Peticion*/
			$this->getResponse()->setBody(Zend_Json::encode($json));
		}
	
	
	}
	
	//Accion  Create Delete dependiendo del estado del requisito
        public function estadoAction(){
        	if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();
        	$id = $this->_getParam('id');
        	$DocumentoTipo = $this->Documentos->getPkDocumentoTipoPorNombre($this::$_DocumentoTipo);
        	$EstadoTipo = $this->Documentos->getPkDocumentoTipoPorNombre($this::$_EstadosTipo);
        	$RequisitoTipo = $this->Documentos->getPkDocumentoTipoPorNombre($this::$_RequisitoTipo);


        	$Documento = $this->Documentos->getPkDocumentoPorNombre($this::$_Documento,$DocumentoTipo);
        	$Requisito = $this->Documentos->getPkRequisitoPorNombre($this::$_Requisito,$RequisitoTipo);
        	
        	
        	//CAMBIAR NUMERO POR $DOCUMENTO GET TIPO
			$Solicitud = $this->Documentos->getValorEstadoSolicitud($id,$Requisito,$Tipo);
			
			
			if($Solicitud){
				
				$this->Desaprobar($Solicitud);
				
        	}else{

        		$this->Aprobar($id,NULL);
        	}
        	
        	}	
        
        }
        
        //Delete al requisito
        private function Desaprobar($Requisito){
        	//define si fue aprobado por secretaria
        	$Emitido =  $this->Documentos->GetDocumentoExistente($Requisito);
            
            if(!$Emitido){
        	   $this->Documentos->DeleteRequisito($Requisito);	
            }else{
                //alert : no puede desaprobar por que ya esto fue aprobado y el documento existe

                $message = 'No se puede desaprobar el requisito por que el documento ya fue emitido.';
                 //mensaje en modal
                $this->SwapBytes_Crud_Form->getDialog('Advertencia', $message);

            }		
        }
        
        
        //Inserta El requisito como Aprobado
        private function Aprobar($Solicitud,$Tipo){
        	
  			$EstadoTipo = $this->Documentos->getPkDocumentoTipoPorNombre($this::$_EstadosTipo);
  			$RequisitoTipo = $this->Documentos->getPkDocumentoTipoPorNombre($this::$_RequisitoTipo);

        	$PKRequisito = $this->Documentos->getPkRequisitoPorNombre($this::$_Requisito,$RequisitoTipo);
        	$Aprobado = $this->Documentos->getEstadoAtributo($this::$_Aprobado,$EstadoTipo);
        	
        		//var_dump($Solicitud,$PKRequisito,$Aprobado,$Tipo);DIE;
        	$this->Documentos->InsertarRequisito($Solicitud,$PKRequisito,$Aprobado);
        }
        
}
