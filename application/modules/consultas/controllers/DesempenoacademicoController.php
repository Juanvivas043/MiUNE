<?php

class Consultas_DesempenoacademicoController extends Zend_Controller_Action {

    public $title = 'Desempeño Académico';
    private $primerLapso = 20054;

    private $estados = array(
      array('value'  => '0', 'label' => 'Todos'),
      array('value'  => '1', 'label' => 'Incompleta'),
      array('value'  => '2', 'label' => 'Completa'),
      array('value'  => '3', 'label' => 'Vacio')
    );

	/* Initialize action controller here */
    public function init() {

        /* Carga de Recursos */
      Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
      Zend_Loader::loadClass('Models_DbTable_CalificacionesParciales');
      Zend_Loader::loadClass('Une_Filtros');
      /*Inicializo los Modelos*/
      $this->grupo           			    = new Models_DbTable_UsuariosGrupos();
      $this->calificaciones           = new Models_DbTable_CalificacionesParciales();
      /* Inicializo las librerias*/
      $this->Request = Zend_Controller_Front::getInstance()->getRequest();
      $this->filtros         			    = new Une_Filtros();
      $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
      $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
      $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
      $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
      $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
      $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
      $this->SwapBytes_Uri            = new SwapBytes_Uri();
      $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
      /*Filtros*/
      $this->filtros->setDisplay(true, true, true, true, true);
      $this->filtros->setDisabled(false, false, false);
      $this->filtros->setRecursive(true, true, true, true, true);
      /*Botones de Acciones*/
      $this->SwapBytes_Crud_Action->setDisplay(true, true);
      $this->SwapBytes_Crud_Action->setEnable(true, true);
      $this->SwapBytes_Crud_Search->setDisplay(false);
      $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
      $lapsos = $this->calificaciones->getLapsosAcademicos();

      $customFilters = array(
        array(
          'id' => 'Lapso',
          'name' => 'selLapso',
          'label' => 'Lapso',
          'recursive' => false,
          'options' =>  $lapsos
        ),
        array(
          'id' => 'Estado',
          'name' => 'selEstado',
          'recursive' => false,
          'label' => 'Estado',
          'options' => $this->estados
        )
      );

      $this->filtros->addCustom($customFilters);
    }

	/**
	 * Se inicia antes del metodo indexAction, y valida si esta autentificado,
	 * si no es asi, redirecciona a al modulo de login.
	 */
    public function preDispatch() {

  		if(!Zend_Auth::getInstance()->hasIdentity()) {
  			$this->_helper->redirector('index', 'login', 'default');
  		}

  		if(!$this->grupo->haveAccessToModule()) {
  			$this->_helper->redirector('accesserror', 'profile', 'default');
      }
    }

    /** Accion de Carga de la Vista **/
    public function indexAction() {
      $this->view->title            = $this->title;
      $this->view->filters          = $this->filtros;
      $this->view->SwapBytes_Ajax         = $this->SwapBytes_Ajax;
      $this->view->SwapBytes_Ajax->setView($this->view);
      $this->view->SwapBytes_Jquery       = $this->SwapBytes_Jquery;
      $this->view->SwapBytes_Crud_Action  = $this->SwapBytes_Crud_Action;
      $this->view->SwapBytes_Crud_Search  = $this->SwapBytes_Crud_Search;
    }

	public function periodoAction() {
    $this->filtros->getAction(array('regimen' => true));
  }

	public function sedeAction() {
		$this->filtros->getAction(array('periodo'));
	}

	public function escuelaAction() {
		$this->filtros->getAction(array('periodo', 'sede'));
	}

	public function pensumAction() {
		$this->filtros->getAction(array('periodo', 'sede', 'escuela'));
  }

	public function semestreAction() {
        $this->filtros->getAction(array('periodo', 'sede', 'escuela','pensum'),
            function ($rows) {
                array_unshift($rows, array('id'=>'','pk_atributo' => 'Todos'));
                return $rows;
            }
        );
	}

    public function lapsoAction() {

      if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();
      }
    }

    public function estadoAction() {

      if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();
      }
    }

    public function listAction() {

		if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();
            $json = array();
            $datos = $this->_getAllParams();
            $filtros = $this->SwapBytes_Uri->queryToArray($datos['filters']);
            $lapso = $filtros['selLapso'] != $this->primerLapso ? NULL: $this->primerLapso;
            $semestre = $filtros['selSemestre'] == 'Todos' ? NULL: $filtros['selSemestre'];
            if (intval($filtros['selEstado']) <= sizeof($this->estados))
              $estado = $this->estados[intval($filtros['selEstado'])]['label'];

            $rows = $this->calificaciones->getDesempenoAcademico(
              $filtros['selPeriodo'],
              $filtros['selSede'],
              $filtros['selEscuela'],
              $lapso,
              $semestre,
              $filtros['selPensum'],
              $estado
            );

            if (count($rows)  > 0 && $rows) {
                $table = array('class' => 'tableData');
                $columns = array(

                    array(
                        'name' => '#',
                        'width' => '10px',
                        'function'  => 'rownum',
                        'rows'    => array('style' => 'text-align:center')
                    ),
                    array(
                        'name'    => 'Materia',
                        'column'  => 'unidadc',
                        'rows'    => array('style' => 'text-align:center')
                    ),
                    array(
                        'name'    => 'Profesor',
                        'column'  => 'profesor',
                        'rows'    => array('style' => 'text-align:center')
                    ),
                    array(
                        'name'     => 'Sección',
                        'width'    => '20px',
                        'column'   => 'seccion',
                        'rows'    => array('style' => 'text-align:center')
                    ),
                    array(
                        'name'    => 'Ins',
                        'width'   => '10px',
                        'column'  => 'inscritos',
                        'rows'    => array('style' => 'text-align:center')
                    ),
                    array(
                        'name'    => 'Ret',
                        'width'   => '10px',
                        'column'  => 'retirados',
                        'rows'    => array('style' => 'text-align:center')
                    ),
                    array(
                        'name'    => 'Neto',
                        'width'   => '10px',
                        'column'  => 'inscritosneto',
                        'rows'    => array('style' => 'text-align:center')
                    ),
                    array(
                        'name'    => 'Asisten',
                        'width'   => '60px',
                        'column'  => 'asisten',
                        'rows'    => array('style' => 'text-align:center')
                    ),
                    array(
                        'name'    => '>25%',
                        'width'   => '10px',
                        'column'  => 'aplazadoinasist',
                        'rows'    => array('style' => 'text-align:center')
                    ),
                    array(
                        'name'    => 'Aprobados',
                        'width'   => '10px',
                        'column'  => 'cantidadaprobados',
                        'rows'    => array('style' => 'text-align:center')
                    ),
                    array(
                        'name'    => 'Reprobados',
                        'width'   => '10px',
                        'column'  => 'cantidadreprobados',
                        'rows'    => array('style' => 'text-align:center')
                    ),
                    array(
                        'name'    => '%Aprobados',
                        'width'   => '10px',
                        'column'  => 'porcentajeaprobado',
                        'rows'    => array('style' => 'text-align:center')
                    ),
                    array(
                        'name'    => '%Reprobados',
                        'width'   => '10px',
                        'column'  => 'porcentajereprobado',
                        'rows'    => array('style' => 'text-align:center')
                    ),
                    array(
                        'name'    => 'Promedio',
                        'width'   => '10px',
                        'column'  => 'calipromedio',
                        'rows'    => array('style' => 'text-align:center')
                    ),
                );

                if ($estado == 'Todos') {
                  array_push($columns,
                    array(
                      'name'    => 'Estado',
                      'width'   => '10px',
                      'column'  => 'estadototal',
                      'rows'    => array('style' => 'text-align:center')
                    )
                  );
                }

                $HTML   = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen Unidades Curriculares.");
            }

            $json[] = $this->SwapBytes_Jquery->setHtml('tblDesempeno', $HTML);
			      $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }
}
