<?php

class Transactions_PreinscripcionesController extends Zend_Controller_Action {

    private $_Title   = 'Transacciones \ Preinscripciones';

	public function init(){
        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Reinscripciones');
		Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');

        $this->filtros          		= new Une_Filtros();
        $this->CmcBytes_Filtros 		= new CmcBytes_Filtros();
		$this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
		$this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
		$this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
		$this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
		$this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
		$this->SwapBytes_Jquery         = new SwapBytes_Jquery();
		$this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
		$this->SwapBytes_Html_Message	= new SwapBytes_Html_Message();
		$this->grupo = new Models_DbTable_UsuariosGrupos();

        $this->SwapBytes_Crud_Action->setDisplay(true, false, false, false, false, false);
		$this->SwapBytes_Crud_Action->setEnable(true, false, false, false, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(false);  

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->Preinscripciones 		= new Models_DbTable_Reinscripciones;

        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
        $ci = $this->AuthSpace->userId;
        // $this->AuthSpace->userId = 21290353;
        $this->infoEst 					= $this->Preinscripciones->infoEst($ci);
	}

	public function preDispatch() {

		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_helper->redirector('index', 'login', 'default');
		}

		if (!$this->grupo->haveAccessToModule()) {
			$this->_helper->redirector('accesserror', 'profile', 'default');
		}
	}

	public function indexAction(){
        $this->view->title                 = $this->_Title;
        $this->view->filters               = $this->filtros;
        $this->view->datos 				   = $this->infoEst;
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
        $this->SwapBytes_Ajax_Action       = new SwapBytes_Ajax_Action();
		$this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
		$this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
		$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
	}

	public function listAction() {
		if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();

			$ci = $this->infoEst[0]['ci'];
			$escuela = $this->infoEst[0]['fk_escuela'];
			$periodo = $this->infoEst[0]['periodo'];

			$periodo_actual = $this->Preinscripciones->periodoEnCurso();
			$periodo_actual = $periodo_actual[0]['pk_periodo'];

			if($periodo <> $periodo_actual){
				$HTML  = $this->SwapBytes_Html_Message->alert("La Preinscripción es solo para los estudiantes inscritos en el período {$periodo_actual}");
				$json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->setHide('btnPreinscribir');
                $json[] = $this->SwapBytes_Jquery->setHide('tdDatos');
				$this->getResponse()->setBody(Zend_Json::encode($json));
				return null;
			}

			$check_preinscripcion = $this->Preinscripciones->revisarPreinscrito($ci, $escuela, $periodo);
			if($check_preinscripcion[0]['check']){
				$perido_preinscripcion = $periodo +1;
				$HTML  = $this->SwapBytes_Html_Message->alert("Usted ya realizó la Preinscripcion para la planificación del Período {$perido_preinscripcion}, cabe acotar que dicha preinscripción no reserva cupos. No se garantiza que las materias que usted señale puedan ser ofertadas para su conveniencia.");
				$json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->setHide('btnPreinscribir');
                $json[] = $this->SwapBytes_Jquery->setHide('tdDatos');
				$this->getResponse()->setBody(Zend_Json::encode($json));
				return null;
			}


			if(isset($_POST['materia']) && !empty($_POST['materia']))
				$materias = implode(',', $_POST['materia']);
			if(isset($materias)){
				$rows = $this->Preinscripciones->listarMateriaPreinscripcion($ci, $escuela, $periodo, $materias);
			}else{
				$rows = $this->Preinscripciones->listarMateriaPreinscripcion($ci, $escuela, $periodo);
			}

			foreach ($rows as $pos => $row) {
				if($row['estado'] == 862){
					$rows[$pos]['valor'] = '<span style="color: red;"><b>Reprobada</b></span>';
				}
				if($row['estado'] == 863){
					$rows[$pos]['valor'] = '<span style="color: green;"><b>Retirada</b></span>';
				}
				if($row['valor'] == 'Cursada'){
					$rows[$pos]['valor'] = '<span style="color: red;"><b>Reprobada</b></span>';
				}
				$rows[$pos]['prelacion'] = str_replace('{', '', $row['prelacion']);
				$rows[$pos]['prelacion'] = str_replace('}', '', $rows[$pos]['prelacion']);
				if($rows[$pos]['prelacion'] == '0000')
					$rows[$pos]['prelacion'] = '';
			}

            if(isset($rows) && count($rows) > 0) {
                $table = array('class' => 'tableData',
					            'zebra' => array('column' => 'new_estado',
				                'colors' => array('odd' => 'A9D0F5',
			                    'even' => 'EFF5FB')));

	            $columns = array(array('column'  => 'pk_asignatura',
	                                   'primary' => true,
	                                   'hide'    => true),
	                             array('name'    => 'Codigo Propietario',
	                                       'width'   => '70px',
	                                       'column'  => 'codigopropietario',
	                                       'rows'    => array('style' => 'text-align:center')),
	                             array('name'    => 'Materia',
	                                   'width'   => '300px',
	                                   'rows'    => array('style' => 'text-align:center'),
	                                   'column'  => 'materia'),
	                             array('name'    => 'Sem.',
	                                   'width'   => '50px',
	                                   'rows'    => array('style' => 'text-align:center'),
	                                   'column'  => 'semestre'),
	                             array('name'    => 'U.C.',
	                                   'width'   => '50px',
	                                   'rows'    => array('style' => 'text-align:center'),
	                                   'column'  => 'unidadcredito'),
	                             array('name'    => 'Prelación',
	                                   'width'   => '100px',
	                                   'rows'    => array('style' => 'text-align:center'),
	                                   'column'  => 'prelacion'),
	                             array('name'    => 'Prelación U.C.',
	                                   'width'   => '100px',
	                                   'rows'    => array('style' => 'text-align:center'),
	                                   'column'  => 'uc'),
	                             array('name'    => 'Estado',
	                                   'width'   => '80px',
	                                   'rows'    => array('style' => 'text-align:center'),
	                                   'column'  => 'valor'),
                                 array('name' => 'Seleccionar',
                                        'width' => '30px',
                                        'column' => 'pk_asignatura',
                                        'rows' => array('style' => 'text-align:center'),
                                        'control' => array('tag' => 'input',
                                            'type' => 'checkbox',
                                            'name' => 'chkRecordAcademico',
                                            'id' => 'chkRecordAcademico',
                                            'onClick' => 'chkMaterias();',
                                            'value' => '##pk_asignatura##')),
	                             );

                $HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns);
       
                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $json[] = $this->SwapBytes_Jquery->setShow('btnPreinscribir');
                $json[] = $this->SwapBytes_Jquery->setShow('tdDatos');
                $json[] = "$('input:checkbox').unbind('click')";
                $json[] = "$('input:checkbox').click(function(){
								checkMaterias();
						    	$('#btnPreinscribir').addClass('ui-state-disabled')
							})";
                // $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkUsuario');
            } else {
                $HTML  = $this->SwapBytes_Html_Message->alert("No existen materias.");

                $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

           $this->getResponse()->setBody(Zend_Json::encode($json));
		}
	}

	function checkmateriasAction(){
		if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();

			$ci = $this->infoEst[0]['ci'];
			$escuela = $this->infoEst[0]['fk_escuela'];
			$periodo = $this->infoEst[0]['periodo'];

			if(isset($_POST['materia']) && !empty($_POST['materia']))
			{
				$materias = implode(',', $_POST['materia']);
				$rows = $this->Preinscripciones->revisarMateriaPreinscripcion($ci, $escuela, $periodo, $materias);
			}else{
				$rows = $this->Preinscripciones->revisarMateriaPreinscripcion($ci, $escuela, $periodo, 0);
			}
			$uc_select = 0;
			$cant_mat = 0;
			foreach ($rows as $pos => $row) {
				$uc_select += $row['sum'];
				$cant_mat += $row['cant_materia'];
			}
			if(isset($rows[0]['uc_limit'])){
				$uc_limit = $rows[0]['uc_limit'];
			}else{
				$uc_limit = 0;
			}
			if(isset($rows[0]['semestre'])){
				$sem_ubic = $rows[0]['semestre'];
			}else{
				$sem_ubic = 0;
			}

			$response = array(
				'uc_select' => $uc_select,
				'cant_mat'	=> $cant_mat,
				'uc_limit'	=> $uc_limit,
				'sem_ubic'	=> $sem_ubic,
				);

           $this->getResponse()->setBody(Zend_Json::encode($response));
		}
	}

	function preinscribirAction(){
		if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();

			$ci = $this->infoEst[0]['ci'];
			$escuela = $this->infoEst[0]['fk_escuela'];
			$periodo = $this->infoEst[0]['periodo'];

			if(isset($_POST['materia']) && !empty($_POST['materia']))
				$materias = implode(',', $_POST['materia']);
			if(isset($materias)){
				$rows = $this->Preinscripciones->listarMateriaPreinscripcion($ci, $escuela, $periodo, $materias);
			}

			if(isset($materias) && !empty($materias)){
				$rows_check = $this->Preinscripciones->revisarMateriaPreinscripcion($ci, $escuela, $periodo, $materias);
				$uc_select = 0;
				$cant_mat = 0;
				foreach ($rows_check as $pos => $row) {
					$uc_select += $row['sum'];
					$cant_mat += $row['cant_materia'];
				}
				if(isset($rows_check[0]['uc_limit'])){
					$uc_limit = $rows_check[0]['uc_limit'];
				}else{
					$uc_limit = 0;
				}
				if(isset($rows_check[0]['semestre'])){
					$sem_ubic = $rows_check[0]['semestre'];
				}else{
					$sem_ubic = 0;
				}

				$response = array(
					'uc_select' => $uc_select,
					'cant_mat'	=> $cant_mat,
					'uc_limit'	=> $uc_limit,
					'sem_ubic'	=> $sem_ubic,
					);

				$materias_puede = array();
				foreach ($rows as $pos => $materia) {
					array_push($materias_puede, $materia['pk_asignatura']);
				}

				if(is_array($materias)){
					foreach ($materias as $pos => $check_materia) {
						if(!in_array($check_materia, $materias_puede)){
							unset($materias[$pos]);
						}
					}
				}else{
					if(!in_array($materias, $materias_puede)){
						unset($materias);
					}
				}

				if($response['uc_select'] <= $response['uc_limit'] && isset($materias)){
					$results = $this->Preinscripciones->insertarPreinscripcion($ci, $escuela, $periodo, $materias);
					// array_push($response, array('estado' => 1));
					$response['estado'] = 1;
				}else{
					// array_push($response, array('estado' => 0));
					$response = array(
						'uc_select' => 0,
						'cant_mat'	=> 0,
						'uc_limit'	=> 0,
						'sem_ubic'	=> 0,
						);
					$response['estado'] = 0;
				}

			$this->getResponse()->setBody(Zend_Json::encode($response));
			}
		}
	}
}
