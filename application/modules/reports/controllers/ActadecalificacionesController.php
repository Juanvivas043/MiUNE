<?php

class Reports_ActadecalificacionesController extends Zend_Controller_Action {
    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Une_Cde_Reportes_ActaDeCalificaciones');
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Une_Cde_transactions_CalificacionesParciales');
        $this->filtros              = new Une_Filtros();
        $this->seguridad            = new Models_DbTable_UsuariosGrupos();
        $this->asignaciones         = new Models_DbTable_Asignaciones();
        $this->periodos             = new Models_DbTable_Periodos();
        $this->RecordAcademico      = new Models_DbTable_Recordsacademicos();
        $this->actadecalificaciones = new Une_Cde_Reportes_ActaDeCalificaciones();
        $this->SwapBytes_Ajax         = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html    = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action  = new SwapBytes_Ajax_Action();
    		$this->SwapBytes_Crud_Action  = new SwapBytes_Crud_Action();
    		$this->SwapBytes_Crud_Search  = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri          = new SwapBytes_Uri();
        $this->SwapBytes_Crud_List    = new SwapBytes_Crud_List();
        $this->SwapBytes_Jquery       = new SwapBytes_Jquery();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->calificacionesParciales =
            new Une_Cde_Transactions_CalificacionesParciales(
            $this->RecordAcademico,
            $this->asignaciones,
            $this->SwapBytes_Jquery
        );
    		$this->_params['filters'] = $this->filtros->getParams();
    		$this->filtros->setDisplay(true, true, true);
    		$this->filtros->setDisabled();
    		$this->filtros->setRecursive(false, true, true);
    		$this->SwapBytes_Crud_Action->setDisplay(true, true);
    		$this->SwapBytes_Crud_Action->setEnable(true, true);
    		$this->SwapBytes_Crud_Search->setDisplay(false);
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * sl no ser asi, redirecciona a al modulo de login.
     */
    function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if(!$this->seguridad->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
        }
	}

    public function indexAction() {
        $this->view->title      = "Reportes \ Acta de Calificaciones";
		$this->view->filters    = $this->filtros;
        $this->view->module     = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
		$this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
		$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
		$this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
		$this->view->SwapBytes_Ajax->setView($this->view);
		$this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
    }

    public function sedeAction() {
        $this->filtros->getAction();
    }

    public function escuelaAction() {
        $this->filtros->getAction(array('sede'));
    }

    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json = array();
            $Params['periodo'] = $this->_params['filters']['periodo'];//$this->periodos->getUltimo();
            $Params['sede']    = $this->_params['filters']['sede'];
            $Params['escuela'] = $this->_params['filters']['escuela'];
            $this->asignaciones->setData($Params, array('periodo', 'sede', 'escuela'));
            $rows = $this->asignaciones->getConsignacionesEstados();
            // Definimos las propiedades de la tabla.
            $url = $this->SwapBytes_Ajax->getUrlAjax();
            $table = array('class' => 'tableData',
                           'width' => '820px');
            // Creamos la lista de elementos que provienen de la consulta.
            $columns = array(array('column'  => 'pk_asignacion',
                                   'primary' => true,
                                   'hide'    => true),
                             array('name'    => 'semestre',
                                   'width'   => '60px',
                                   'column'  => 'semestre',
                                   'rows'    => array('style' => 'text-align:center')),
                             array('name'    => 'codigo',
                                   'width'   => '50px',
                                   'column'  => 'codigopropietario',
                                   'rows'    => array('style' => 'text-align:center')),
                             array('name'    => 'materia',
                                   'width'   => '250px',
                                   'column'  => 'materia',
                                   'rows'    => array('style' => 'text-align:left')),
                             array('name'    => 'seccion',
                                   'width'   => '60px',
                                   'column'  => 'seccion',
                                   'rows'    => array('style' => 'text-align:center')),
                             array('name'    => 'nombre',
                                   'width'   => '200px',
                                   'column'  => 'nombre',
                                   'rows'    => array('style' => 'text-align:left')),
                             array('name'    => 'apellido',
                                   'width'   => '200px',
                                   'column'  => 'apellido',
                                   'rows'    => array('style' => 'text-align:left')),
                             array('name'    => 'Estado',
                                   'column'  => 'estado',
                                   'width'   => '200px',
                                   'rows'    => array('style'      => 'text-align:left'),
                                   'control' => array('tag'        => 'label',
                                                      'name'       => 'lnk##pk_asignacion##',
                                                      'html'       => '##estado##',
                                                      'id'         => 'lnk##pk_asignacion##',
                                                      'conditions' =>
                                                      array(array('equal'      => 'Por imprimir',
                                                        'properties' => array('tag'  => 'a',
                                                        'href' => "{$url}descargar/asignacion/##pk_asignacion##")))
                                                    )
                                                )
                                    );


            $other = array(
                array(
                    'actionName' => 'listaverde',
                    'label' => 'Listado')
                );
            $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'O', $other);
            $json[] = $this->SwapBytes_Jquery->setHtml('tblAsignaciones', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
    public function listaverdeAction () {
       $this->SwapBytes_Ajax->setHeader();
       $id = $this->_getParam('id');
       $data = $this->asignaciones->getRow($id);
       $params = array ('sede' => $data['pk_sede'],
                        'escuela' => $data['fk_escuela'],
                        'periodo' => $data['fk_periodo'],
                        'semestre' => $data['fk_semestre'],
                        'materia' => $data['fk_materia'],
                        'seccion' => $data['fk_seccion'],
                        'pensum' => $data['fk_pensum']
                    );
       $outstream = $this->calificacionesParciales->generarReporte($params);
       $this->getResponse()->setBody(base64_decode($outstream));
    }

    public function descargarAction() {
        $Asignacion = $this->_getParam('asignacion');
        $Asignaciones = $this->asignaciones->getAsignacionSimilar($Asignacion);
        foreach ($Asignaciones as $Asig){
          $this->asignaciones->updateRow($Asig['pk_asignacion'], array('fk_estado' => '1256'));
		    }
		    $estados = array('862','1699');
        $Data = $this->RecordAcademico->getListaRoja($Asignacion, $estados);
        $this->actadecalificaciones->generar($Data);
    }

}
